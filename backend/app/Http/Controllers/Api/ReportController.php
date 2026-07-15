<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Admission;
use App\Models\Visit;
use App\Models\Patient;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Prescription;
use App\Models\LabRequest;
use App\Models\Emergency;
use App\Models\Referral;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Executive summary / overview dashboard analytics
     */
    public function executive(Request $request)
    {
        $period = $request->get('period', 'month'); // today, week, month, quarter, year
        [$start, $end] = $this->getPeriodDates($period);

        // Core KPIs
        $totalPatients = Patient::count();
        $newPatients = Patient::whereBetween('created_at', [$start, $end])->count();
        $totalVisits = Visit::whereBetween('created_at', [$start, $end])->count();
        $totalEmergencies = Emergency::whereBetween('arrived_at', [$start, $end])->count();
        $totalAdmissions = Admission::whereBetween('admitted_at', [$start, $end])->count();
        $totalReferrals = Referral::whereBetween('referred_at', [$start, $end])->count();

        // Revenue
        $totalRevenue = Payment::whereBetween('created_at', [$start, $end])->sum('amount');
        $pendingRevenue = Invoice::where('status', 'unpaid')
            ->whereBetween('created_at', [$start, $end])->sum('total_amount');

        // Appointment stats
        $appointmentsTotal = Appointment::whereBetween('appointment_date', [
            $start->toDateString(), $end->toDateString()
        ])->count();
        $appointmentsCompleted = Appointment::where('status', 'completed')
            ->whereBetween('appointment_date', [
                $start->toDateString(), $end->toDateString()
            ])->count();

        // Lab stats
        $labTests = LabRequest::whereBetween('created_at', [$start, $end])->count();
        $pendingLabs = LabRequest::where('status', 'pending')
            ->whereBetween('created_at', [$start, $end])->count();

        // Emergency triage breakdown
        $emergencyByTriage = Emergency::whereBetween('arrived_at', [$start, $end])
            ->selectRaw('triage_level, count(*) as count')
            ->groupBy('triage_level')
            ->pluck('count', 'triage_level');

        // Admissions by ward
        $admissionsByWard = Admission::with('bed.ward')
            ->whereBetween('admitted_at', [$start, $end])
            ->get()
            ->groupBy(fn($a) => $a->bed?->ward?->name ?? 'Unknown')
            ->map->count();

        // Calculate Best Doctor
        $bestDoctorRow = Visit::whereBetween('created_at', [$start, $end])
            ->whereNotNull('staff_id')
            ->selectRaw('staff_id, count(*) as count')
            ->groupBy('staff_id')
            ->orderByDesc('count')
            ->first();

        $bestDoctor = 'N/A';
        if ($bestDoctorRow && $bestDoctorRow->doctor) {
            $bestDoctor = "Dr. {$bestDoctorRow->doctor->name} ({$bestDoctorRow->count} Consults)";
        }

        return response()->json([
            'period' => $period,
            'date_range' => ['start' => $start->format('d M Y'), 'end' => $end->format('d M Y')],
            'kpis' => [
                'total_patients' => $totalPatients,
                'new_patients' => $newPatients,
                'total_visits' => $totalVisits,
                'total_emergencies' => $totalEmergencies,
                'total_admissions' => $totalAdmissions,
                'total_referrals' => $totalReferrals,
                'total_revenue' => $totalRevenue,
                'pending_revenue' => $pendingRevenue,
                'appointments_total' => $appointmentsTotal,
                'appointments_completed' => $appointmentsCompleted,
                'appointment_completion_rate' => $appointmentsTotal > 0 
                    ? round(($appointmentsCompleted / $appointmentsTotal) * 100, 1) 
                    : 0,
                'lab_tests' => $labTests,
                'pending_labs' => $pendingLabs,
                'best_doctor' => $bestDoctor,
            ],
            'emergency_by_triage' => $emergencyByTriage,
            'admissions_by_ward' => $admissionsByWard,
        ]);
    }

    /**
     * Patient flow trends (time series)
     */
    public function patientFlow(Request $request)
    {
        $period = $request->get('period', 'month');
        [$start, $end] = $this->getPeriodDates($period);

        // Group by day for short periods, by week for longer ones
        $groupBy = $period === 'today' ? 'hour' : ($period === 'year' ? 'month' : 'day');

        $visits = Visit::whereBetween('created_at', [$start, $end])
            ->selectRaw($this->dateGroupQuery($groupBy, 'created_at') . ' as period, count(*) as count')
            ->groupByRaw($this->dateGroupQuery($groupBy, 'created_at'))
            ->orderByRaw($this->dateGroupQuery($groupBy, 'created_at'))
            ->pluck('count', 'period');

        $appointments = Appointment::whereBetween('created_at', [$start, $end])
            ->selectRaw($this->dateGroupQuery($groupBy, 'created_at') . ' as period, count(*) as count')
            ->groupByRaw($this->dateGroupQuery($groupBy, 'created_at'))
            ->pluck('count', 'period');

        $emergencies = Emergency::whereBetween('arrived_at', [$start, $end])
            ->selectRaw($this->dateGroupQuery($groupBy, 'arrived_at') . ' as period, count(*) as count')
            ->groupByRaw($this->dateGroupQuery($groupBy, 'arrived_at'))
            ->pluck('count', 'period');

        return response()->json([
            'labels' => $visits->keys()->merge($appointments->keys())->merge($emergencies->keys())->unique()->sort()->values(),
            'visits' => $visits,
            'appointments' => $appointments,
            'emergencies' => $emergencies,
        ]);
    }

    /**
     * Revenue report
     */
    public function revenue(Request $request)
    {
        $period = $request->get('period', 'month');
        [$start, $end] = $this->getPeriodDates($period);

        $groupBy = $period === 'today' ? 'hour' : ($period === 'year' ? 'month' : 'day');

        $payments = Payment::whereBetween('created_at', [$start, $end])
            ->selectRaw($this->dateGroupQuery($groupBy, 'created_at') . ' as period, sum(amount) as total, count(*) as count')
            ->groupByRaw($this->dateGroupQuery($groupBy, 'created_at'))
            ->orderByRaw($this->dateGroupQuery($groupBy, 'created_at'))
            ->get();

        $invoicesByStatus = Invoice::whereBetween('created_at', [$start, $end])
            ->selectRaw('status, count(*) as count, sum(total_amount) as total')
            ->groupBy('status')
            ->get()
            ->mapWithKeys(fn($r) => [$r->status => ['count' => $r->count, 'total' => $r->total]]);

        return response()->json([
            'timeline' => $payments,
            'by_status' => $invoicesByStatus,
            'total_collected' => $payments->sum('total'),
        ]);
    }

    /**
     * Top diagnoses report
     */
    public function diagnoses(Request $request)
    {
        $period = $request->get('period', 'month');
        [$start, $end] = $this->getPeriodDates($period);

        $topDiagnoses = Visit::whereBetween('created_at', [$start, $end])
            ->whereNotNull('diagnosis_description')
            ->selectRaw('diagnosis_icd10, diagnosis_description, count(*) as count')
            ->groupBy('diagnosis_icd10', 'diagnosis_description')
            ->orderByDesc('count')
            ->limit(15)
            ->get();

        return response()->json(['diagnoses' => $topDiagnoses]);
    }

    /**
     * Bed occupancy report
     */
    public function bedOccupancy()
    {
        $wards = \App\Models\Ward::with('beds')->get()->map(function ($ward) {
            $beds = $ward->beds;
            $activeAdmissions = Admission::where('status', 'active')
                ->whereHas('bed', fn($q) => $q->where('ward_id', $ward->id))
                ->count();
            return [
                'ward' => $ward->name,
                'total_beds' => $beds->count(),
                'available' => $beds->where('status', 'available')->count(),
                'occupied' => $beds->where('status', 'occupied')->count(),
                'cleaning' => $beds->where('status', 'cleaning')->count(),
                'maintenance' => $beds->where('status', 'maintenance')->count(),
                'active_admissions' => $activeAdmissions,
                'occupancy_rate' => $beds->count() > 0 
                    ? round(($beds->where('status', 'occupied')->count() / $beds->count()) * 100) 
                    : 0,
            ];
        });

        return response()->json(['wards' => $wards]);
    }

    /**
     * Staff performance report
     */
    public function staffPerformance(Request $request)
    {
        $period = $request->get('period', 'month');
        [$start, $end] = $this->getPeriodDates($period);

        $doctors = Visit::whereBetween('created_at', [$start, $end])
            ->with('doctor:id,name')
            ->selectRaw('staff_id, count(*) as consultations')
            ->groupBy('staff_id')
            ->orderByDesc('consultations')
            ->limit(10)
            ->get()
            ->map(fn($r) => [
                'doctor' => $r->doctor?->name ?? 'Unknown',
                'consultations' => $r->consultations,
            ]);

        return response()->json(['top_doctors' => $doctors]);
    }

    // --- Helpers ---
    private function getPeriodDates(string $period): array
    {
        return match($period) {
            'today'   => [Carbon::today(),            Carbon::now()],
            'week'    => [Carbon::now()->startOfWeek(),Carbon::now()],
            'month'   => [Carbon::now()->startOfMonth(),Carbon::now()],
            'quarter' => [Carbon::now()->startOfQuarter(),Carbon::now()],
            'year'    => [Carbon::now()->startOfYear(),Carbon::now()],
            default   => [Carbon::now()->startOfMonth(),Carbon::now()],
        };
    }

    private function dateGroupQuery(string $groupBy, string $col): string
    {
        return match($groupBy) {
            'hour'  => "DATE_FORMAT($col, '%Y-%m-%d %H:00')",
            'day'   => "DATE($col)",
            'month' => "DATE_FORMAT($col, '%Y-%m')",
            default => "DATE($col)",
        };
    }
}
