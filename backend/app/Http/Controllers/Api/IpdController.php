<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\Bed;
use App\Models\Ward;
use App\Models\Patient;
use App\Models\Visit;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class IpdController extends Controller
{
    /**
     * Get all wards with bed availability stats
     */
    public function getWards()
    {
        $wards = Ward::with(['beds' => function ($q) {
            $q->select('id', 'ward_id', 'bed_number', 'status');
        }])->get()->map(function ($ward) {
            $beds = $ward->beds;
            return [
                'id' => $ward->id,
                'name' => $ward->name,
                'description' => $ward->description,
                'total_beds' => $beds->count(),
                'available_beds' => $beds->where('status', 'available')->count(),
                'occupied_beds' => $beds->where('status', 'occupied')->count(),
                'cleaning_beds' => $beds->where('status', 'cleaning')->count(),
                'maintenance_beds' => $beds->where('status', 'maintenance')->count(),
                'occupancy_rate' => $beds->count() > 0 
                    ? round(($beds->where('status', 'occupied')->count() / $beds->count()) * 100) 
                    : 0,
                'beds' => $beds->map(fn($b) => [
                    'id' => $b->id,
                    'bed_number' => $b->bed_number,
                    'status' => $b->status,
                ]),
            ];
        });

        return response()->json(['wards' => $wards]);
    }

    /**
     * Get all active admissions (IPD census)
     */
    public function getAdmissions(Request $request)
    {
        $status = $request->get('status', 'active');
        $wardId = $request->get('ward_id');

        $query = Admission::with([
            'patient:id,first_name,last_name,immigration_service_number,gender,date_of_birth,phone',
            'bed:id,bed_number,ward_id',
            'bed.ward:id,name',
            'doctor:id,name',
        ])
        ->where('status', $status);

        if ($wardId) {
            $query->whereHas('bed', fn($q) => $q->where('ward_id', $wardId));
        }

        $admissions = $query->orderByDesc('admitted_at')->get()->map(function ($adm) {
            $los = $adm->admitted_at 
                ? $adm->admitted_at->diffInDays($adm->discharged_at ?? Carbon::now()) 
                : 0;
            return [
                'id' => $adm->id,
                'patient_id' => $adm->patient_id,
                'patient_name' => $adm->patient 
                    ? trim("{$adm->patient->first_name} {$adm->patient->last_name}") 
                    : 'Unknown',
                'hospital_number' => $adm->patient->immigration_service_number ?? 'N/A',
                'gender' => $adm->patient->gender ?? 'N/A',
                'age' => $adm->patient?->date_of_birth 
                    ? Carbon::parse($adm->patient->date_of_birth)->age 
                    : 'N/A',
                'phone' => $adm->patient->phone ?? '',
                'ward' => $adm->bed?->ward?->name ?? 'Unknown Ward',
                'bed_number' => $adm->bed?->bed_number ?? 'N/A',
                'doctor' => $adm->doctor?->name ?? 'Unassigned',
                'admission_type' => $adm->admission_type ?? 'elective',
                'diagnosis_on_admission' => $adm->diagnosis_on_admission,
                'ward_notes' => $adm->ward_notes,
                'status' => $adm->status,
                'admitted_at' => $adm->admitted_at?->format('d M Y H:i'),
                'discharged_at' => $adm->discharged_at?->format('d M Y H:i'),
                'length_of_stay_days' => $los,
                'discharge_summary' => $adm->discharge_summary,
            ];
        });

        $stats = [
            'total_active' => Admission::where('status', 'active')->count(),
            'discharged_today' => Admission::where('status', 'discharged')
                ->whereDate('discharged_at', Carbon::today())->count(),
            'admitted_today' => Admission::where('status', 'active')
                ->whereDate('admitted_at', Carbon::today())->count(),
            'avg_los' => round(Admission::where('status', 'discharged')
                ->whereNotNull('discharged_at')
                ->get()
                ->avg(fn($a) => $a->admitted_at->diffInDays($a->discharged_at)) ?? 0, 1),
        ];

        return response()->json([
            'admissions' => $admissions,
            'stats' => $stats,
        ]);
    }

    /**
     * Admit a patient to a bed
     */
    public function admit(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'visit_id' => 'nullable|exists:visits,id',
            'bed_id' => 'required|exists:beds,id',
            'staff_id' => 'required|exists:staff,id',
            'admission_type' => 'required|in:elective,emergency,transfer',
            'diagnosis_on_admission' => 'required|string|max:1000',
            'ward_notes' => 'nullable|string|max:2000',
            'emergency_id' => 'nullable|exists:emergencies,id',
        ]);

        // Check bed availability
        $bed = Bed::findOrFail($validated['bed_id']);
        if ($bed->status !== 'available') {
            return response()->json(['message' => 'Selected bed is not available.'], 422);
        }

        // Check if patient already has active admission
        $existing = Admission::where('patient_id', $validated['patient_id'])
            ->where('status', 'active')->first();
        if ($existing) {
            return response()->json(['message' => 'Patient already has an active admission.'], 422);
        }

        DB::transaction(function () use (&$admission, $validated, $bed) {
            $admission = Admission::create([
                ...$validated,
                'admitted_at' => now(),
                'status' => 'active',
            ]);

            // Mark bed as occupied
            $bed->update(['status' => 'occupied']);
        });

        return response()->json([
            'message' => 'Patient admitted successfully.',
            'admission' => $admission->load(['patient', 'bed.ward', 'doctor']),
        ], 201);
    }

    /**
     * Update ward notes or reassign bed
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'ward_notes' => 'nullable|string|max:5000',
            'diagnosis_on_admission' => 'nullable|string|max:1000',
            'staff_id' => 'nullable|exists:staff,id',
            'bed_id' => 'nullable|exists:beds,id',
        ]);

        $admission = Admission::findOrFail($id);

        // Handle bed transfer
        if (isset($validated['bed_id']) && $validated['bed_id'] != $admission->bed_id) {
            $newBed = Bed::findOrFail($validated['bed_id']);
            if ($newBed->status !== 'available') {
                return response()->json(['message' => 'Target bed is not available.'], 422);
            }
            DB::transaction(function () use ($admission, $newBed, $validated) {
                // Free old bed
                if ($admission->bed_id) {
                    Bed::where('id', $admission->bed_id)->update(['status' => 'cleaning']);
                }
                // Occupy new bed
                $newBed->update(['status' => 'occupied']);
                $admission->update($validated);
            });
        } else {
            $admission->update($validated);
        }

        return response()->json([
            'message' => 'Admission updated successfully.',
            'admission' => $admission->fresh(['patient', 'bed.ward', 'doctor']),
        ]);
    }

    /**
     * Discharge a patient
     */
    public function discharge(Request $request, $id)
    {
        $validated = $request->validate([
            'discharge_summary' => 'required|string|min:10',
        ]);

        $admission = Admission::findOrFail($id);
        if ($admission->status === 'discharged') {
            return response()->json(['message' => 'Patient is already discharged.'], 422);
        }

        DB::transaction(function () use ($admission, $validated) {
            $admission->update([
                'status' => 'discharged',
                'discharged_at' => now(),
                'discharge_summary' => $validated['discharge_summary'],
            ]);

            // Free the bed
            if ($admission->bed_id) {
                Bed::where('id', $admission->bed_id)->update(['status' => 'cleaning']);
            }
        });

        return response()->json([
            'message' => 'Patient discharged successfully.',
            'admission' => $admission->fresh(['patient', 'bed.ward', 'doctor']),
        ]);
    }

    /**
     * Manage wards (CRUD)
     */
    public function createWard(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:wards',
            'description' => 'nullable|string|max:500',
        ]);
        $ward = Ward::create($validated);
        return response()->json(['message' => 'Ward created.', 'ward' => $ward], 201);
    }

    public function createBed(Request $request)
    {
        $validated = $request->validate([
            'ward_id' => 'required|exists:wards,id',
            'bed_number' => 'required|string|max:50',
        ]);

        // Check uniqueness
        $exists = Bed::where('ward_id', $validated['ward_id'])
            ->where('bed_number', $validated['bed_number'])->exists();
        if ($exists) {
            return response()->json(['message' => 'Bed number already exists in this ward.'], 422);
        }

        $bed = Bed::create([...$validated, 'status' => 'available']);
        return response()->json(['message' => 'Bed created.', 'bed' => $bed], 201);
    }

    public function updateBedStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:available,occupied,cleaning,maintenance',
        ]);
        $bed = Bed::findOrFail($id);
        $bed->update($validated);
        return response()->json(['message' => 'Bed status updated.', 'bed' => $bed]);
    }

    /**
     * Get available beds (for admission form)
     */
    public function getAvailableBeds(Request $request)
    {
        $wardId = $request->get('ward_id');
        $query = Bed::with('ward:id,name')->where('status', 'available');
        if ($wardId) $query->where('ward_id', $wardId);
        return response()->json(['beds' => $query->get()]);
    }
}
