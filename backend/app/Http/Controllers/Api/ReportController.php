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
            ->with('doctor:id,first_name,last_name')
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

    /**
     * Consolidated clinical analytics: top diagnoses, busiest clinicians,
     * diagnostics throughput and consultation volume for the period.
     */
    public function clinical(Request $request)
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

        $topDoctors = Visit::whereBetween('created_at', [$start, $end])
            ->with('doctor:id,first_name,last_name')
            ->selectRaw('staff_id, count(*) as consultations')
            ->groupBy('staff_id')
            ->orderByDesc('consultations')
            ->limit(10)
            ->get()
            ->map(fn ($r) => [
                'doctor' => $r->doctor?->full_name ?? 'Unknown',
                'consultations' => $r->consultations,
            ]);

        return response()->json([
            'period' => $period,
            'total_consultations' => Visit::whereBetween('created_at', [$start, $end])->whereNotNull('chief_complaint')->count(),
            'lab_requests' => \App\Models\LabRequest::whereBetween('created_at', [$start, $end])->count(),
            'radiology_requests' => \App\Models\RadiologyRequest::whereBetween('created_at', [$start, $end])->count(),
            'admissions' => Admission::whereBetween('created_at', [$start, $end])->count(),
            'diagnoses' => $topDiagnoses,
            'top_doctors' => $topDoctors,
        ]);
    }

    // --- Helpers ---
    /**
     * Daily cash reconciliation: payments taken on a date, broken down by
     * method and by cashier, for end-of-day cash-up.
     */
    public function cashReconciliation(Request $request)
    {
        $date = $request->get('date', Carbon::today()->toDateString());
        $start = Carbon::parse($date)->startOfDay();
        $end = Carbon::parse($date)->endOfDay();

        $byMethod = Payment::whereBetween('payments.created_at', [$start, $end])
            ->selectRaw('payment_method, count(*) as count, sum(amount) as total')
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->get();

        $byCashier = Payment::whereBetween('payments.created_at', [$start, $end])
            ->leftJoin('staff', 'payments.cashier_id', '=', 'staff.id')
            ->selectRaw("COALESCE(staff.first_name || ' ' || staff.last_name, 'Unattributed') as cashier, count(*) as count, sum(amount) as total")
            ->groupBy('cashier')
            ->orderByDesc('total')
            ->get();

        return response()->json([
            'date' => $date,
            'total_collected' => (float) Payment::whereBetween('created_at', [$start, $end])->sum('amount'),
            'transactions' => Payment::whereBetween('created_at', [$start, $end])->count(),
            'by_method' => $byMethod,
            'by_cashier' => $byCashier,
        ]);
    }

    /**
     * Revenue by clinical department for a period (payments attributed through
     * invoice -> visit -> department; invoices with no visit fall under
     * "Registration / Other").
     */
    public function revenueByDepartment(Request $request)
    {
        $period = $request->get('period', 'month');
        [$start, $end] = $this->getPeriodDates($period);

        $rows = Payment::whereBetween('payments.created_at', [$start, $end])
            ->join('invoices', 'payments.invoice_id', '=', 'invoices.id')
            ->leftJoin('visits', 'invoices.visit_id', '=', 'visits.id')
            ->leftJoin('departments', 'visits.department_id', '=', 'departments.id')
            ->selectRaw("COALESCE(departments.name, 'Registration / Other') as department, sum(payments.amount) as total, count(*) as count")
            ->groupBy('department')
            ->orderByDesc('total')
            ->get();

        return response()->json([
            'period' => $period,
            'departments' => $rows,
            'total' => (float) $rows->sum('total'),
        ]);
    }

    /**
     * Debtor aging: outstanding balances on unpaid / part-paid invoices,
     * bucketed by how long they have been outstanding.
     */
    public function debtorAging()
    {
        $invoices = Invoice::with('patient:id,first_name,last_name,immigration_service_number')
            ->whereIn('status', ['unpaid', 'partially_paid'])
            ->get();

        $buckets = ['0-30' => 0.0, '31-60' => 0.0, '61-90' => 0.0, '90+' => 0.0];
        $debtors = [];

        foreach ($invoices as $inv) {
            $outstanding = (float) $inv->total_amount - (float) $inv->discount_amount - (float) $inv->paid_amount;
            if ($outstanding <= 0) {
                continue;
            }

            $ageDays = $inv->created_at->diffInDays(now());
            $bucket = $ageDays <= 30 ? '0-30' : ($ageDays <= 60 ? '31-60' : ($ageDays <= 90 ? '61-90' : '90+'));
            $buckets[$bucket] += $outstanding;

            $pid = $inv->patient_id;
            if (!isset($debtors[$pid])) {
                $debtors[$pid] = [
                    'patient' => trim($inv->patient?->first_name . ' ' . $inv->patient?->last_name),
                    'hospital_code' => $inv->patient?->immigration_service_number,
                    'outstanding' => 0.0,
                    'invoices' => 0,
                    'oldest_days' => 0,
                ];
            }
            $debtors[$pid]['outstanding'] += $outstanding;
            $debtors[$pid]['invoices']++;
            $debtors[$pid]['oldest_days'] = max($debtors[$pid]['oldest_days'], $ageDays);
        }

        usort($debtors, fn ($a, $b) => $b['outstanding'] <=> $a['outstanding']);

        return response()->json([
            'buckets' => $buckets,
            'total_outstanding' => array_sum($buckets),
            'debtors' => array_slice(array_values($debtors), 0, 30),
        ]);
    }

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

    /**
     * Build a driver-aware date grouping expression (MySQL / PostgreSQL / SQLite).
     */
    private function dateGroupQuery(string $groupBy, string $col): string
    {
        $driver = \DB::connection()->getDriverName();

        return match ($driver) {
            'pgsql' => match ($groupBy) {
                'hour'  => "to_char($col, 'YYYY-MM-DD HH24:00')",
                'day'   => "to_char($col, 'YYYY-MM-DD')",
                'month' => "to_char($col, 'YYYY-MM')",
                default => "to_char($col, 'YYYY-MM-DD')",
            },
            'sqlite' => match ($groupBy) {
                'hour'  => "strftime('%Y-%m-%d %H:00', $col)",
                'day'   => "strftime('%Y-%m-%d', $col)",
                'month' => "strftime('%Y-%m', $col)",
                default => "strftime('%Y-%m-%d', $col)",
            },
            default => match ($groupBy) { // mysql / mariadb
                'hour'  => "DATE_FORMAT($col, '%Y-%m-%d %H:00')",
                'day'   => "DATE($col)",
                'month' => "DATE_FORMAT($col, '%Y-%m')",
                default => "DATE($col)",
            },
        };
    }
}
