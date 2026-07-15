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
use App\Models\LabRequest;
use App\Models\RadiologyRequest;
use App\Models\Emergency;
use App\Models\AuditLog;
use App\Models\User;
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

        // 2. Doctor / Consultant & Allied Health Dashboard
        if (in_array($role, ['doctor', 'consultant', 'dental_officer', 'eye_clinic_officer', 'physiotherapist', 'theatre_manager'])) {
            $staff = $user->staff;
            $doctorId = $staff ? $staff->id : null;

            // Load active visits assigned to this doctor where vitals have been captured but consult is not yet completed
            $myQueue = Visit::with(['patient'])
                ->where('staff_id', $doctorId)
                ->whereNull('chief_complaint')
                ->orderBy('created_at', 'asc')
                ->get();

            $consultedToday = Visit::where('staff_id', $doctorId)
                ->whereNotNull('chief_complaint')
                ->whereDate('created_at', Carbon::today())
                ->count();

            return response()->json([
                'role' => $role,
                'metrics' => [
                    'my_appointments_today' => $myQueue->count(),
                    'consulted_today' => $consultedToday,
                    'active_admissions' => $activeAdmissions
                ],
                'queue' => $myQueue
            ]);
        }

        // 3. Nurse / Ward Manager Dashboard
        if ($role === 'nurse' || $role === 'ward_manager') {
            $occupiedBeds = Bed::where('status', 'occupied')->count();
            $availableBeds = Bed::where('status', 'available')->count();

            return response()->json([
                'role' => $role,
                'metrics' => [
                    'active_admissions' => $activeAdmissions,
                    'occupied_beds' => $occupiedBeds,
                    'available_beds' => $availableBeds,
                    'today_appointments' => $todayAppointments
                ],
                'occupied_beds' => $occupiedBeds,
                'available_beds' => $availableBeds,
                'recent_visits_for_vitals' => Visit::with('patient')
                    ->whereNull('vitals_blood_pressure')
                    ->orderBy('created_at', 'desc')
                    ->take(8)
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

        // 5. Cashier / Account Dashboard
        if (in_array($role, ['cashier', 'account_officer'])) {
            $todayPayments = Payment::whereDate('created_at', Carbon::today())->sum('amount');
            $pendingInvoicesCount = Invoice::whereIn('status', ['unpaid', 'partially_paid'])->count();

            return response()->json([
                'role' => $role,
                'metrics' => [
                    'today_revenue' => (float)$todayPayments,
                    'outstanding_invoices_count' => $pendingInvoicesCount,
                    'pending_invoices' => $pendingInvoicesCount,
                    'total_patients' => $patientCount
                ]
            ]);
        }

        // 6. Receptionist Dashboard
        if (in_array($role, ['receptionist'])) {
            return response()->json([
                'role' => $role,
                'metrics' => [
                    'today_appointments' => $todayAppointments,
                    'total_patients' => $patientCount
                ]
            ]);
        }

        // 7. Laboratory Dashboard
        if ($role === 'lab_scientist') {
            $pendingRequests = LabRequest::where('status', 'pending')->count();
            $completedToday = LabRequest::whereIn('status', ['completed', 'approved'])
                                ->whereDate('updated_at', Carbon::today())->count();
            return response()->json([
                'role' => $role,
                'metrics' => [
                    'pending_requests' => $pendingRequests,
                    'completed_today' => $completedToday,
                    'total_patients' => $patientCount
                ]
            ]);
        }

        // 8. Radiology Dashboard
        if ($role === 'radiographer') {
            $pendingRequests = RadiologyRequest::where('status', 'pending')->count();
            $completedToday = RadiologyRequest::whereIn('status', ['completed', 'approved'])
                                ->whereDate('updated_at', Carbon::today())->count();
            return response()->json([
                'role' => $role,
                'metrics' => [
                    'pending_requests' => $pendingRequests,
                    'completed_today' => $completedToday,
                    'total_patients' => $patientCount
                ]
            ]);
        }

        // 9. HR Officer Dashboard
        if ($role === 'hr_officer') {
            $totalStaff = Staff::count();
            $totalUsers = User::count();
            return response()->json([
                'role' => $role,
                'metrics' => [
                    'total_staff' => $totalStaff,
                    'total_users' => $totalUsers
                ]
            ]);
        }

        // 10. ICT Admin Dashboard
        if ($role === 'ict_admin') {
            $totalUsers = User::count();
            $recentAuditLogs = AuditLog::whereDate('created_at', Carbon::today())->count();
            return response()->json([
                'role' => $role,
                'metrics' => [
                    'total_users' => $totalUsers,
                    'audit_logs_today' => $recentAuditLogs,
                    'system_health' => '100%'
                ]
            ]);
        }

        // 11. Ambulance Officer Dashboard
        if ($role === 'ambulance_officer') {
            $activeEmergencies = Emergency::where('status', 'active')->count();
            $emergenciesToday = Emergency::whereDate('created_at', Carbon::today())->count();
            return response()->json([
                'role' => $role,
                'metrics' => [
                    'active_emergencies' => $activeEmergencies,
                    'emergencies_today' => $emergenciesToday
                ]
            ]);
        }

        // 12. Health Information Manager (Records) Dashboard
        if (in_array($role, ['health_info_officer', 'records_officer'])) {
            $visitsToday = Visit::whereDate('created_at', Carbon::today())->count();
            return response()->json([
                'role' => $role,
                'metrics' => [
                    'total_patients' => $patientCount,
                    'visits_today' => $visitsToday,
                    'active_admissions' => $activeAdmissions
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

