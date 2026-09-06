<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Emergency;
use App\Models\Patient;
use App\Models\Staff;
use App\Models\Admission;
use App\Models\Bed;
use App\Models\Referral;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EmergencyController extends Controller
{
    /**
     * Get all active emergencies (ER dashboard)
     */
    public function index(Request $request)
    {
        $status = $request->get('status', 'active');

        $query = Emergency::with([
            'patient:id,first_name,last_name,hospital_number,gender,date_of_birth,blood_group,phone',
            'doctor:id,first_name,last_name',
            'triageNurse:id,first_name,last_name',
        ]);

        if ($status === 'active') {
            $query->whereNotIn('status', ['discharged', 'deceased', 'transferred']);
        } elseif ($status !== 'all') {
            $query->where('status', $status);
        }

        // Sort: red first, then yellow, then green, then black; then by arrival time.
        // Driver-agnostic CASE ordering (MySQL FIELD() is not portable to pgsql/sqlite).
        $emergencies = $query->orderByRaw(
                "CASE triage_level WHEN 'red' THEN 1 WHEN 'yellow' THEN 2 WHEN 'green' THEN 3 WHEN 'black' THEN 4 ELSE 5 END"
            )
            ->orderBy('arrived_at')
            ->get()
            ->map(function ($em) {
                $waitMins = $em->arrived_at ? $em->arrived_at->diffInMinutes(now()) : 0;
                return [
                    'id' => $em->id,
                    'patient_id' => $em->patient_id,
                    'patient_name' => $em->patient 
                        ? trim("{$em->patient->first_name} {$em->patient->last_name}") 
                        : 'Unknown / Walk-in',
                    'hospital_number' => $em->patient?->hospital_number ?? 'UNKNOWN',
                    'gender' => $em->patient?->gender ?? 'N/A',
                    'age' => $em->patient?->date_of_birth 
                        ? Carbon::parse($em->patient->date_of_birth)->age 
                        : 'N/A',
                    'blood_group' => $em->patient?->blood_group ?? 'Unknown',
                    'phone' => $em->patient?->phone ?? '',
                    'triage_level' => $em->triage_level,
                    'chief_complaint' => $em->chief_complaint,
                    'presenting_symptoms' => $em->presenting_symptoms,
                    'vitals' => [
                        'bp' => $em->vitals_bp,
                        'temp' => $em->vitals_temp,
                        'pulse' => $em->vitals_pulse,
                        'spo2' => $em->vitals_spo2,
                        'gcs' => $em->vitals_gcs,
                    ],
                    'mode_of_arrival' => $em->mode_of_arrival,
                    'status' => $em->status,
                    'treatment_notes' => $em->treatment_notes,
                    'disposition_notes' => $em->disposition_notes,
                    'attending_doctor' => $em->doctor?->name ?? 'Awaiting Assignment',
                    'triaged_by' => $em->triageNurse?->name ?? 'Not Yet Triaged',
                    'arrived_at' => $em->arrived_at?->format('d M Y H:i:s'),
                    'triaged_at' => $em->triaged_at?->format('d M Y H:i'),
                    'treatment_started_at' => $em->treatment_started_at?->format('d M Y H:i'),
                    'wait_minutes' => $waitMins,
                ];
            });

        // ER Stats
        $stats = [
            'waiting' => Emergency::whereNotIn('status', ['discharged', 'deceased', 'transferred'])->where('status', 'waiting')->count(),
            'in_treatment' => Emergency::where('status', 'in_treatment')->count(),
            'critical_red' => Emergency::where('triage_level', 'red')->whereNotIn('status', ['discharged', 'deceased', 'transferred'])->count(),
            'today_total' => Emergency::whereDate('arrived_at', Carbon::today())->count(),
            'today_discharged' => Emergency::where('status', 'discharged')->whereDate('arrived_at', Carbon::today())->count(),
        ];

        return response()->json([
            'emergencies' => $emergencies,
            'stats' => $stats,
        ]);
    }

    /**
     * Register a new emergency patient
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'nullable|exists:patients,id',
            'triage_level' => 'required|in:red,yellow,green,black',
            'chief_complaint' => 'required|string|max:500',
            'presenting_symptoms' => 'nullable|string|max:2000',
            'vitals_bp' => 'nullable|string|max:20',
            'vitals_temp' => 'nullable|numeric|min:30|max:45',
            'vitals_pulse' => 'nullable|integer|min:0|max:300',
            'vitals_spo2' => 'nullable|integer|min:0|max:100',
            'vitals_gcs' => 'nullable|integer|min:3|max:15',
            'mode_of_arrival' => 'required|in:walk-in,ambulance,police,referred',
            'staff_id' => 'nullable|exists:staff,id',
            'triaged_by' => 'nullable|exists:staff,id',
        ]);

        $emergency = Emergency::create([
            ...$validated,
            'status' => 'waiting',
            'arrived_at' => now(),
            'triaged_at' => $validated['triaged_by'] ? now() : null,
        ]);

        return response()->json([
            'message' => 'Emergency case registered successfully.',
            'emergency' => $emergency->load(['patient', 'doctor', 'triageNurse']),
        ], 201);
    }

    /**
     * Update emergency case (triage, assign doctor, treatment notes)
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'triage_level' => 'sometimes|in:red,yellow,green,black',
            'status' => 'sometimes|in:waiting,in_treatment,admitted,discharged,deceased,transferred',
            'staff_id' => 'nullable|exists:staff,id',
            'triaged_by' => 'nullable|exists:staff,id',
            'treatment_notes' => 'nullable|string|max:5000',
            'disposition_notes' => 'nullable|string|max:2000',
            'vitals_bp' => 'nullable|string|max:20',
            'vitals_temp' => 'nullable|numeric',
            'vitals_pulse' => 'nullable|integer',
            'vitals_spo2' => 'nullable|integer',
            'vitals_gcs' => 'nullable|integer',
        ]);

        $emergency = Emergency::findOrFail($id);

        // Auto-set timestamps
        if (isset($validated['status'])) {
            if ($validated['status'] === 'in_treatment' && !$emergency->treatment_started_at) {
                $validated['treatment_started_at'] = now();
            }
            if (in_array($validated['status'], ['discharged', 'deceased', 'transferred']) && !$emergency->resolved_at) {
                $validated['resolved_at'] = now();
            }
        }
        if (isset($validated['triaged_by']) && !$emergency->triaged_at) {
            $validated['triaged_at'] = now();
        }

        $emergency->update($validated);

        return response()->json([
            'message' => 'Emergency case updated.',
            'emergency' => $emergency->fresh(['patient', 'doctor', 'triageNurse']),
        ]);
    }

    /**
     * Admit emergency patient to ward
     */
    public function admitToWard(Request $request, $id)
    {
        $validated = $request->validate([
            'bed_id' => 'required|exists:beds,id',
            'staff_id' => 'required|exists:staff,id',
            'diagnosis_on_admission' => 'required|string|max:1000',
            'ward_notes' => 'nullable|string|max:2000',
        ]);

        $emergency = Emergency::findOrFail($id);

        $bed = Bed::findOrFail($validated['bed_id']);
        if ($bed->status !== 'available') {
            return response()->json(['message' => 'Selected bed is not available.'], 422);
        }

        DB::transaction(function () use ($emergency, $validated, $bed, &$admission) {
            $admission = Admission::create([
                'patient_id' => $emergency->patient_id,
                'emergency_id' => $emergency->id,
                'bed_id' => $validated['bed_id'],
                'staff_id' => $validated['staff_id'],
                'admission_type' => 'emergency',
                'diagnosis_on_admission' => $validated['diagnosis_on_admission'],
                'ward_notes' => $validated['ward_notes'] ?? null,
                'admitted_at' => now(),
                'status' => 'active',
            ]);

            $bed->update(['status' => 'occupied']);
            $emergency->update(['status' => 'admitted']);
        });

        return response()->json([
            'message' => 'Emergency patient admitted to ward.',
            'admission' => $admission->load(['patient', 'bed.ward', 'doctor']),
        ]);
    }

    /**
     * Get staff (doctors/nurses) for ER assignment
     */
    public function getStaff()
    {
        $staff = Staff::with('user.roles')
            ->get()
            ->filter(fn($s) => $s->user && $s->user->roles->whereIn('name', [
                'doctor', 'consultant', 'nurse', 'ambulance_officer'
            ])->count() > 0)
            ->map(fn($s) => [
                'id' => $s->id,
                'name' => $s->full_name,
                'role' => $s->user?->roles?->first()?->name ?? 'staff',
            ])
            ->values();

        return response()->json(['staff' => $staff]);
    }

    /**
     * Get available beds for ER-to-Ward admission
     */
    public function getAvailableBeds()
    {
        $beds = Bed::with('ward:id,name')
            ->where('status', 'available')
            ->get()
            ->map(fn($b) => [
                'id' => $b->id,
                'label' => "{$b->ward?->name} - Bed {$b->bed_number}",
                'ward' => $b->ward?->name,
                'bed_number' => $b->bed_number,
            ]);
        return response()->json(['beds' => $beds]);
    }
}
