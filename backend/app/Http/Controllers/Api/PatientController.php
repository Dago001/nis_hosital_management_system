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
        $query = Patient::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%$search%")
                  ->orWhere('last_name', 'like', "%$search%")
                  ->orWhere('immigration_service_number', 'like', "%$search%")
                  ->orWhere('nin', 'like', "%$search%")
                  ->orWhere('phone', 'like', "%$search%");
            });
        }

        $patients = $query->paginate(15);

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
            'last_name' => 'required|string|max:255',
            'gender' => 'required|string|in:Male,Female,Other',
            'date_of_birth' => 'required|date',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'email' => 'nullable|email|max:255',
            'immigration_service_number' => 'nullable|string|unique:patients,immigration_service_number',
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
        $patient = Patient::with(['appointments.doctor', 'visits.doctor', 'invoices', 'admissions'])->find($id);

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

        // Sort timeline descending by date
        usort($timeline, fn($a, $b) => strcmp($b['date'], $a['date']));

        return response()->json([
            'patient' => new PatientResource($patient),
            'timeline' => $timeline
        ]);
    }
}
