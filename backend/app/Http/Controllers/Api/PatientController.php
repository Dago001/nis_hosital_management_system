<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\DTOs\RegisterPatientDTO;
use App\Services\PatientService;
use App\Http\Resources\PatientResource;
use App\Models\Patient;

class PatientController extends Controller
{
    protected PatientService $patientService;

    public function __construct(PatientService $patientService)
    {
        $this->patientService = $patientService;
    }

    public function index(Request $request)
    {
        if (!$request->has('search') || strlen(trim($request->search)) < 3) {
            return response()->json([
                'patients' => [],
                'pagination' => ['total' => 0, 'per_page' => 15, 'current_page' => 1, 'last_page' => 1]
            ]);
        }

        $search = trim($request->search);
        $patients = Patient::where('immigration_service_number', 'like', "%$search%")
            ->paginate(15);

        return response()->json([
            'patients' => PatientResource::collection($patients),
            'pagination' => [
                'total' => $patients->total(),
                'per_page' => $patients->perPage(),
                'current_page' => $patients->currentPage(),
                'last_page' => $patients->lastPage()
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'gender' => 'required|string|in:Male,Female,Other',
            'date_of_birth' => 'required|date',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'state' => 'nullable|string|max:255',
            'lga' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'immigration_service_number' => 'nullable|string|unique:patients,immigration_service_number',
            'sponsor_service_number' => 'nullable|string|max:255',
            'relationship_to_sponsor' => 'nullable|string|max:255',
            'nin' => 'nullable|string|size:11|unique:patients,nin',
            'allergies' => 'nullable|string',
            'blood_group' => 'nullable|string|max:5',
            'genotype' => 'nullable|string|max:5',
            'disability' => 'nullable|string',
        ]);

        $dto = RegisterPatientDTO::fromRequest($validated);
        $patient = $this->patientService->register($dto);

        return response()->json([
            'message' => 'Patient registered successfully.',
            'patient' => new PatientResource($patient)
        ], 210); // 201 Created
    }

    public function show(int $id)
    {
        $patient = Patient::with([
            'appointments.doctor', 
            'visits.doctor', 
            'invoices', 
            'admissions', 
            'dependants', 
            'sponsor', 
            'prescriptions.items', 
            'prescriptions.doctor',
            'labRequests.result.scientist',
            'labRequests.doctor',
            'radiologyRequests.result.radiographer',
            'radiologyRequests.doctor'
        ])->find($id);

        if (!$patient) {
            return response()->json(['message' => 'Patient not found.'], 404);
        }

        // Build a structured historical clinical timeline for the patient card view
        $timeline = [];

        foreach ($patient->appointments as $apt) {
            $timeline[] = [
                'type' => 'appointment',
                'date' => $apt->appointment_date . ' ' . $apt->appointment_time,
                'title' => 'Appointment Booked',
                'description' => "Scheduled with Dr. {$apt->doctor?->full_name} in {$apt->department?->name}",
                'status' => $apt->status
            ];
        }

        foreach ($patient->visits as $visit) {
            $timeline[] = [
                'type' => 'consultation',
                'date' => $visit->created_at->toDateTimeString(),
                'title' => 'Doctor Consultation',
                'description' => "Consulted by Dr. {$visit->doctor?->full_name}. Diagnosis: {$visit->diagnosis_description} (ICD10: {$visit->diagnosis_icd10})",
                'details' => [
                    'bp' => $visit->vitals_blood_pressure,
                    'temp' => $visit->vitals_temperature,
                    'complaints' => $visit->chief_complaint,
                    'soap' => [
                        'S' => $visit->soap_notes_subjective,
                        'O' => $visit->soap_notes_objective,
                        'A' => $visit->soap_notes_assessment,
                        'P' => $visit->soap_notes_plan
                    ]
                ]
            ];
        }

        foreach ($patient->admissions as $adm) {
            $timeline[] = [
                'type' => 'admission',
                'date' => $adm->admitted_at,
                'title' => 'Hospital Admission',
                'description' => "Admitted to {$adm->bed?->ward?->name} (Bed: {$adm->bed?->bed_number}) by Dr. {$adm->doctor?->full_name}",
                'status' => $adm->status
            ];
        }

        $diagnostics = [];

        foreach ($patient->labRequests as $req) {
            if ($req->status === 'completed' && $req->result && $req->result->status === 'approved') {
                $completedAt = $req->result->approved_at 
                    ? \Illuminate\Support\Carbon::parse($req->result->approved_at)->toDateTimeString()
                    : $req->result->updated_at->toDateTimeString();

                $timeline[] = [
                    'type' => 'lab_result',
                    'date' => $completedAt,
                    'title' => 'Lab Test Outcome: ' . $req->test_name,
                    'description' => "Result: {$req->result->result_value} (Range: {$req->result->normal_range_min}-{$req->result->normal_range_max} {$req->result->unit}). Ordered by Dr. {$req->doctor?->full_name}. Approved by {$req->result->scientist?->full_name}.",
                    'status' => 'approved'
                ];

                $diagnostics[] = [
                    'id' => $req->id,
                    'type' => 'lab',
                    'test_name' => $req->test_name,
                    'doctor_name' => $req->doctor?->full_name ?? 'System',
                    'completed_at' => $completedAt,
                    'result_value' => $req->result->result_value,
                    'normal_range' => ($req->result->normal_range_min || $req->result->normal_range_max) 
                        ? "{$req->result->normal_range_min} - {$req->result->normal_range_max} {$req->result->unit}" 
                        : "N/A",
                    'scientist_name' => $req->result->scientist?->full_name ?? 'Laboratory Scientist',
                    'remarks' => $req->result->remarks ?? 'No remarks'
                ];
            }
        }

        foreach ($patient->radiologyRequests as $req) {
            if ($req->status === 'completed' && $req->result && $req->result->status === 'approved') {
                $completedAt = $req->result->approved_at 
                    ? \Illuminate\Support\Carbon::parse($req->result->approved_at)->toDateTimeString()
                    : $req->result->updated_at->toDateTimeString();

                $timeline[] = [
                    'type' => 'radiology_result',
                    'date' => $completedAt,
                    'title' => 'Radiology Scan Outcome: ' . $req->scan_type,
                    'description' => "Findings: {$req->result->report_text}. Ordered by Dr. {$req->doctor?->full_name}. Approved by {$req->result->radiographer?->full_name}.",
                    'status' => 'approved'
                ];

                $diagnostics[] = [
                    'id' => $req->id,
                    'type' => 'radiology',
                    'test_name' => "{$req->scan_type} ({$req->body_part})",
                    'doctor_name' => $req->doctor?->full_name ?? 'System',
                    'completed_at' => $completedAt,
                    'result_value' => $req->result->report_text,
                    'normal_range' => 'N/A',
                    'scientist_name' => $req->result->radiographer?->full_name ?? 'Radiographer',
                    'remarks' => 'N/A'
                ];
            }
        }

        // Sort timeline descending by date
        usort($timeline, fn($a, $b) => strcmp($b['date'], $a['date']));

        return response()->json([
            'patient' => new PatientResource($patient),
            'timeline' => $timeline,
            'diagnostics' => $diagnostics
        ]);
    }

    public function lookupSponsor(Request $request)
    {
        $request->validate([
            'service_number' => 'required|string|min:3'
        ]);

        $serviceNum = trim($request->service_number);

        // 1. Search in staff table
        $staff = \App\Models\Staff::where('service_number', $serviceNum)->first();
        if ($staff) {
            return response()->json([
                'found' => true,
                'type' => 'staff',
                'surname' => $staff->last_name,
                'first_name' => $staff->first_name,
                'full_name' => "{$staff->first_name} {$staff->last_name} (" . ($staff->rank ?? 'Officer') . ")"
            ]);
        }

        // 2. Search in patients table
        $patient = \App\Models\Patient::where('immigration_service_number', $serviceNum)->first();
        if ($patient) {
            return response()->json([
                'found' => true,
                'type' => 'patient',
                'surname' => $patient->last_name,
                'first_name' => $patient->first_name,
                'full_name' => "{$patient->first_name} {$patient->last_name} (Patient File: {$patient->immigration_service_number})"
            ]);
        }

        return response()->json([
            'found' => false,
            'message' => 'No active officer or patient records match the provided service number.'
        ]);
    }
}
