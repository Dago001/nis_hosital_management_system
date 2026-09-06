<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\DTOs\RegisterPatientDTO;
use App\Services\PatientService;
use App\Http\Resources\PatientResource;
use App\Models\Patient;
use App\Repositories\Contracts\AuditLogRepositoryInterface;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;

class PatientController extends Controller
{
    protected PatientService $patientService;
    protected AuditLogRepositoryInterface $auditLog;

    public function __construct(PatientService $patientService, AuditLogRepositoryInterface $auditLog)
    {
        $this->patientService = $patientService;
        $this->auditLog = $auditLog;
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
        $like = '%' . mb_strtolower($search) . '%';
        $patients = Patient::where(function ($q) use ($like) {
            // LOWER(...) LIKE gives case-insensitive matching consistently across
            // PostgreSQL (case-sensitive LIKE), MySQL and SQLite.
            foreach (['immigration_service_number', 'sponsor_service_number', 'nin', 'phone', 'first_name', 'middle_name', 'last_name'] as $i => $col) {
                $method = $i === 0 ? 'whereRaw' : 'orWhereRaw';
                $q->{$method}("LOWER($col) LIKE ?", [$like]);
            }
            $q->orWhereRaw("LOWER(first_name || ' ' || last_name) LIKE ?", [$like]);
        })
            ->when($request->filled('facility_id'), fn ($q) => $q->where('facility_id', $request->facility_id))
            ->latest('created_at')
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

    /**
     * Patients assigned to the authenticated doctor (i.e. patients the doctor is
     * the consulting clinician for). Doctors can also call up ANY patient record
     * via search()/show() — this is their focused working list.
     */
    public function assignedToMe(Request $request)
    {
        $staff = $request->user()->staff;

        $empty = [
            'patients' => [],
            'pagination' => ['total' => 0, 'per_page' => 15, 'current_page' => 1, 'last_page' => 1],
        ];

        if (! $staff) {
            return response()->json($empty);
        }

        $staffId = $staff->id;

        $patients = Patient::whereHas('visits', fn ($q) => $q->where('staff_id', $staffId))
            ->withCount(['visits as encounters_count' => fn ($q) => $q->where('staff_id', $staffId)])
            ->withMax(['visits as last_seen_at' => fn ($q) => $q->where('staff_id', $staffId)], 'created_at')
            ->orderByDesc('last_seen_at')
            ->paginate(15);

        return response()->json([
            'patients' => $patients->map(fn ($p) => [
                'id' => $p->id,
                'full_name' => $p->full_name,
                'immigration_service_number' => $p->immigration_service_number,
                'gender' => $p->gender,
                'age' => $p->age,
                'encounters_count' => $p->encounters_count,
                'last_seen_at' => $p->last_seen_at ? \Illuminate\Support\Carbon::parse($p->last_seen_at)->toDateString() : null,
            ]),
            'pagination' => [
                'total' => $patients->total(),
                'per_page' => $patients->perPage(),
                'current_page' => $patients->currentPage(),
                'last_page' => $patients->lastPage(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        // Names: letters only (plus spaces, hyphens, apostrophes, periods).
        $nameRule = ['regex:/^[A-Za-z][A-Za-z\s\'.\-]*$/'];
        // Phone: digits only, optional leading +, 7-15 digits (E.164-ish).
        $phoneRule = ['regex:/^\+?[0-9]{7,15}$/'];

        $validated = $request->validate([
            'first_name' => array_merge(['required', 'string', 'max:255'], $nameRule),
            'middle_name' => array_merge(['nullable', 'string', 'max:255'], $nameRule),
            'last_name' => array_merge(['required', 'string', 'max:255'], $nameRule),
            'gender' => 'required|string|in:Male,Female,Other',
            'marital_status' => 'required|string|in:Single,Married,Divorced,Widowed,Separated',
            'occupation' => 'nullable|string|max:255',
            'religion' => 'nullable|string|in:Christianity,Islam,Traditional,Other',
            'place_of_birth' => 'nullable|string|max:255',
            'tribe' => array_merge(['nullable', 'string', 'max:255'], $nameRule),
            'date_of_birth' => 'required|date|before_or_equal:today',
            'phone' => array_merge(['required', 'string'], $phoneRule),
            'address' => 'required|string|max:500',
            'state' => array_merge(['nullable', 'string', 'max:255'], $nameRule),
            'lga' => array_merge(['nullable', 'string', 'max:255'], $nameRule),
            'city' => 'nullable|string|max:255',
            'next_of_kin_name' => array_merge(['nullable', 'string', 'max:255'], $nameRule),
            'next_of_kin_relationship' => array_merge(['nullable', 'string', 'max:255'], $nameRule),
            'next_of_kin_address' => 'nullable|string|max:500',
            'email' => 'nullable|email|max:255',
            'immigration_service_number' => ['nullable', 'string', 'max:50', 'regex:/^[A-Za-z0-9\/\-]+$/', 'unique:patients,immigration_service_number'],
            'sponsor_service_number' => ['nullable', 'string', 'max:50', 'regex:/^[A-Za-z0-9\/\-]+$/'],
            'relationship_to_sponsor' => array_merge(['nullable', 'string', 'max:255'], $nameRule),
            'nin' => ['nullable', 'string', 'regex:/^[0-9]{11}$/', 'unique:patients,nin'],
            'allergies' => 'nullable|string',
            'blood_group' => 'nullable|string|max:5',
            'genotype' => 'nullable|string|max:5',
            'disability' => 'nullable|string',
            'facility_id' => 'nullable|exists:facilities,id',
        ], [
            'first_name.regex' => 'First name may only contain letters.',
            'middle_name.regex' => 'Middle name may only contain letters.',
            'last_name.regex' => 'Last name may only contain letters.',
            'state.regex' => 'State may only contain letters.',
            'lga.regex' => 'LGA may only contain letters.',
            'relationship_to_sponsor.regex' => 'Relationship may only contain letters.',
            'phone.regex' => 'Phone number must contain digits only (7-15 digits, optional leading +).',
            'nin.regex' => 'NIN must be exactly 11 digits.',
            'immigration_service_number.regex' => 'Service number may only contain letters, numbers, / and -.',
            'sponsor_service_number.regex' => 'Sponsor service number may only contain letters, numbers, / and -.',
        ]);

        $dto = RegisterPatientDTO::fromRequest($validated);
        $patient = $this->patientService->register($dto);

        return response()->json([
            'message' => 'Patient registered successfully.',
            'patient' => new PatientResource($patient)
        ], 201); // 201 Created
    }

    public function show(Request $request, int $id)
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

        // Accountability: record every access to a patient's confidential file (PHI).
        $this->auditLog->log(
            userId: $request->user()?->id,
            action: 'view_patient_record',
            auditableType: Patient::class,
            auditableId: $patient->id,
            payload: ['hospital_code' => $patient->immigration_service_number],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

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

    /**
     * Printable hospital ID card payload: demographics + a scannable QR code
     * (rendered as an inline SVG data-URI so it needs no external service).
     */
    public function idCard(Request $request, int $id)
    {
        $patient = Patient::find($id);
        if (! $patient) {
            return response()->json(['message' => 'Patient not found.'], 404);
        }

        $this->auditLog->log(
            userId: $request->user()?->id,
            action: 'generate_id_card',
            auditableType: Patient::class,
            auditableId: $patient->id,
            payload: ['hospital_code' => $patient->immigration_service_number],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        // What a scanner reads: a compact verification payload keyed to the file.
        $payload = implode('|', [
            'NISHMS',
            'CODE:' . ($patient->immigration_service_number ?? ''),
            'NAME:' . $patient->full_name,
            'DOB:' . ($patient->date_of_birth ?? ''),
            'REF:' . ($patient->qr_code_data ?? ('PAT-' . $patient->id)),
        ]);

        $qr = new QrCode(data: $payload, size: 240, margin: 8);
        $svg = (new SvgWriter())->write($qr)->getString();
        $qrDataUri = 'data:image/svg+xml;base64,' . base64_encode($svg);

        return response()->json([
            'card' => [
                'full_name' => $patient->full_name,
                'hospital_code' => $patient->immigration_service_number,
                'barcode' => $patient->barcode_data,
                'date_of_birth' => $patient->date_of_birth,
                'age' => $patient->age,
                'gender' => $patient->gender,
                'blood_group' => $patient->blood_group,
                'genotype' => $patient->genotype,
                'phone' => $patient->phone,
                'allergies' => $patient->allergies,
                'nhis' => $patient->isNhis(),
                'photo_url' => $patient->passport_photograph_path ? asset('storage/' . $patient->passport_photograph_path) : null,
                'issued_on' => now()->toDateString(),
            ],
            'qr' => $qrDataUri,
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
            $existing = \App\Models\Patient::where('sponsor_service_number', $serviceNum)->get(['id', 'relationship_to_sponsor']);
            return response()->json([
                'found' => true,
                'type' => 'staff',
                'surname' => $staff->last_name,
                'first_name' => $staff->first_name,
                'phone' => $staff->phone ?? 'N/A',
                'full_name' => "{$staff->first_name} {$staff->last_name} (" . ($staff->rank ?? 'Officer') . ")",
                'existing_dependants' => $existing
            ]);
        }

        // 2. Search in patients table
        $patient = \App\Models\Patient::where('immigration_service_number', $serviceNum)->first();
        if ($patient) {
            $existing = \App\Models\Patient::where('sponsor_service_number', $serviceNum)->get(['id', 'relationship_to_sponsor']);
            return response()->json([
                'found' => true,
                'type' => 'patient',
                'surname' => $patient->last_name,
                'first_name' => $patient->first_name,
                'phone' => $patient->phone ?? 'N/A',
                'full_name' => "{$patient->first_name} {$patient->last_name} (Patient File: {$patient->immigration_service_number})",
                'existing_dependants' => $existing
            ]);
        }

        return response()->json([
            'found' => false,
            'message' => 'No active officer or patient records match the provided service number.'
        ]);
    }
}
