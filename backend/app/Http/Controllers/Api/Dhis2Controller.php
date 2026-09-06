<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\Appointment;
use App\Models\Emergency;
use App\Models\LabRequest;
use App\Models\Patient;
use App\Models\Referral;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * DHIS2 aggregate export: monthly facility indicators packaged as a DHIS2
 * dataValueSet (JSON) or CSV for upload into a national HMIS instance.
 */
class Dhis2Controller extends Controller
{
    /**
     * Data elements exported. Keys are stable DHIS2 dataElement codes; the
     * closure computes the aggregate value for the given month window.
     */
    private function elements(Carbon $start, Carbon $end): array
    {
        return [
            'OPD_ATTENDANCE' => ['label' => 'OPD Attendance', 'value' => Visit::whereBetween('created_at', [$start, $end])->count()],
            'IPD_ADMISSIONS' => ['label' => 'Inpatient Admissions', 'value' => Admission::whereBetween('admitted_at', [$start, $end])->count()],
            'NEW_REGISTRATIONS' => ['label' => 'New Patient Registrations', 'value' => Patient::whereBetween('created_at', [$start, $end])->count()],
            'APPOINTMENTS_BOOKED' => ['label' => 'Appointments Booked', 'value' => Appointment::whereBetween('created_at', [$start, $end])->count()],
            'LAB_TESTS' => ['label' => 'Laboratory Tests Requested', 'value' => LabRequest::whereBetween('created_at', [$start, $end])->count()],
            'EMERGENCY_CASES' => ['label' => 'Emergency Cases', 'value' => Emergency::whereBetween('created_at', [$start, $end])->count()],
            'REFERRALS_OUT' => ['label' => 'Referrals', 'value' => Referral::whereBetween('created_at', [$start, $end])->count()],
        ];
    }

    private function window(Request $request): array
    {
        $year = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);
        $month = max(1, min(12, $month));

        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = (clone $start)->endOfMonth();

        return [$year, $month, $start, $end];
    }

    private function orgUnit(Request $request): string
    {
        // DHIS2 org-unit identifier for this facility (overridable per export).
        return $request->get('org_unit', 'NIS_MEDICAL_HQ');
    }

    public function indicators(Request $request)
    {
        [$year, $month, $start, $end] = $this->window($request);

        $elements = $this->elements($start, $end);

        $topDiagnoses = Visit::whereBetween('created_at', [$start, $end])
            ->whereNotNull('diagnosis_description')
            ->selectRaw('diagnosis_icd10, diagnosis_description, count(*) as count')
            ->groupBy('diagnosis_icd10', 'diagnosis_description')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        return response()->json([
            'period' => sprintf('%04d%02d', $year, $month),
            'period_label' => $start->format('F Y'),
            'org_unit' => $this->orgUnit($request),
            'indicators' => collect($elements)->map(fn ($e, $code) => [
                'code' => $code,
                'label' => $e['label'],
                'value' => $e['value'],
            ])->values(),
            'top_diagnoses' => $topDiagnoses,
        ]);
    }

    /**
     * DHIS2 dataValueSet JSON payload.
     */
    public function export(Request $request)
    {
        [$year, $month, $start, $end] = $this->window($request);
        $period = sprintf('%04d%02d', $year, $month);
        $orgUnit = $this->orgUnit($request);

        $dataValues = collect($this->elements($start, $end))
            ->map(fn ($e, $code) => [
                'dataElement' => $code,
                'period' => $period,
                'orgUnit' => $orgUnit,
                'value' => (string) $e['value'],
            ])->values();

        return response()->json([
            'dataValueSet' => [
                'dataSet' => 'NIS_MONTHLY_HMIS',
                'completeDate' => now()->toDateString(),
                'period' => $period,
                'orgUnit' => $orgUnit,
                'dataValues' => $dataValues,
            ],
        ]);
    }

    /**
     * Same aggregates as CSV (dataElement,period,orgUnit,value).
     */
    public function exportCsv(Request $request)
    {
        [$year, $month, $start, $end] = $this->window($request);
        $period = sprintf('%04d%02d', $year, $month);
        $orgUnit = $this->orgUnit($request);

        $lines = ['dataElement,period,orgUnit,value'];
        foreach ($this->elements($start, $end) as $code => $e) {
            $lines[] = "{$code},{$period},{$orgUnit},{$e['value']}";
        }

        $csv = implode("\n", $lines) . "\n";

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=dhis2_export_{$period}.csv",
        ]);
    }
}
