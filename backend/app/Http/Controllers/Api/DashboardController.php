<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\Visit;
use App\Models\Admission;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PharmacyItem;
use App\Models\Staff;
use App\Models\Bed;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $role = $user->roles()->first()?->name;

        // Common metrics
        $patientCount = Patient::count();
        $todayAppointments = Appointment::whereDate('appointment_date', Carbon::today())->count();
        $activeAdmissions = Admission::where('status', 'active')->count();

        // 1. Executive / Admin Dashboard
        if (in_array($role, ['super_admin', 'medical_director', 'hospital_admin', 'chief_medical_officer'])) {
            $totalRevenue = (float) Payment::sum('amount');
            $todayRevenue = (float) Payment::whereDate('created_at', Carbon::today())->sum('amount');
            
            $monthlyRevenue = Payment::selectRaw('SUM(amount) as amount, DATE_FORMAT(created_at, "%Y-%m") as month')
                ->groupBy('month')
                ->orderBy('month', 'asc')
                ->get();
                
            $medicineStock = PharmacyItem::sum('quantity_in_stock');
            $criticalStockAlerts = PharmacyItem::whereRaw('quantity_in_stock <= reorder_level')->count();

            // Bed occupancy rate
            $totalBeds = Bed::count();
            $occupiedBeds = Bed::where('status', 'occupied')->count();
            $occupancyRate = $totalBeds > 0 ? round(($occupiedBeds / $totalBeds) * 100, 1) : 0;

            $totalMalePatients = Patient::where('gender', 'Male')->count();
            $totalFemalePatients = Patient::where('gender', 'Female')->count();
            $totalStaffOnboarded = Staff::count();

            return response()->json([
                'role' => $role,
                'metrics' => [
                    'total_patients' => $patientCount,
                    'active_admissions' => $activeAdmissions,
                    'today_appointments' => $todayAppointments,
                    'total_revenue' => $totalRevenue,
                    'today_revenue' => $todayRevenue,
                    'medicine_stock' => $medicineStock,
                    'critical_stock_alerts' => $criticalStockAlerts,
                    'bed_occupancy_rate' => $occupancyRate,
                    'total_male_patients' => $totalMalePatients,
                    'total_female_patients' => $totalFemalePatients,
                    'total_staff_onboarded' => $totalStaffOnboarded
                ],
                'revenue_trend' => $monthlyRevenue,
                'recent_admissions' => Admission::with(['patient', 'bed.ward'])->orderBy('created_at', 'desc')->take(5)->get()
            ]);
        }

        // 2. Doctor / Consultant Dashboard
        if (in_array($role, ['doctor', 'consultant'])) {
            $staff = $user->staff;
            $doctorId = $staff ? $staff->id : null;

            $myAppointments = Appointment::with(['patient'])
                ->where('staff_id', $doctorId)
                ->whereDate('appointment_date', Carbon::today())
                ->orderBy('queue_number', 'asc')
                ->get();

            $consultedToday = Visit::where('staff_id', $doctorId)
                ->whereDate('created_at', Carbon::today())
                ->count();

            return response()->json([
                'role' => $role,
                'metrics' => [
                    'my_appointments_today' => $myAppointments->count(),
                    'consulted_today' => $consultedToday,
                    'active_admissions' => $activeAdmissions
                ],
                'queue' => $myAppointments
            ]);
        }

        // 3. Nurse Dashboard
        if ($role === 'nurse') {
            $occupiedBeds = Bed::where('status', 'occupied')->get()->count();
            $availableBeds = Bed::where('status', 'available')->get()->count();

            return response()->json([
                'role' => $role,
                'metrics' => [
                    'active_admissions' => $activeAdmissions,
                    'occupied_beds' => $occupiedBeds,
                    'available_beds' => $availableBeds,
                    'today_appointments' => $todayAppointments
                ],
                'recent_visits_for_vitals' => Visit::with('patient')
                    ->whereNull('vitals_blood_pressure')
                    ->orderBy('created_at', 'desc')
                    ->take(5)
                    ->get()
            ]);
        }

        // 4. Pharmacist & Inventory Dashboard
        if (in_array($role, ['pharmacist', 'inventory_officer', 'store_officer', 'procurement_officer'])) {
            $expiredDrugs = PharmacyItem::whereDate('expiry_date', '<', Carbon::today())->count();
            $expiringSoon = PharmacyItem::whereBetween('expiry_date', [Carbon::today(), Carbon::today()->addMonths(6)])->count();
            $lowStock = PharmacyItem::whereRaw('quantity_in_stock <= reorder_level')->get();

            return response()->json([
                'role' => $role,
                'metrics' => [
                    'total_medicine_items' => PharmacyItem::count(),
                    'expired_drugs' => $expiredDrugs,
                    'expiring_soon' => $expiringSoon,
                    'low_stock_count' => $lowStock->count()
                ],
                'low_stock_list' => $lowStock
            ]);
        }

        // 5. Cashier Dashboard
        if ($role === 'cashier') {
            $todayPayments = Payment::whereDate('created_at', Carbon::today())->sum('amount');
            $pendingInvoicesCount = Invoice::whereIn('status', ['unpaid', 'partially_paid'])->count();

            return response()->json([
                'role' => $role,
                'metrics' => [
                    'today_revenue' => (float)$todayPayments,
                    'pending_invoices' => $pendingInvoicesCount,
                    'total_patients' => $patientCount
                ]
            ]);
        }

        // Generic fallback dashboard
        return response()->json([
            'role' => $role,
            'metrics' => [
                'total_patients' => $patientCount,
                'today_appointments' => $todayAppointments,
                'active_admissions' => $activeAdmissions
            ]
        ]);
    }
}
