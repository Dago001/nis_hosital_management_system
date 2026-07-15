<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Referral;
use App\Models\Patient;
use App\Models\Staff;
use App\Models\Visit;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReferralController extends Controller
{
    /**
     * List all referrals
     */
    public function index(Request $request)
    {
        $type = $request->get('type', 'all'); // all, outgoing, incoming
        $status = $request->get('status', 'all');
        $priority = $request->get('priority', 'all');

        $query = Referral::with([
            'patient:id,first_name,last_name,immigration_service_number,gender',
            'referringDoctor:id,name',
        ])->latest('referred_at');

        if ($type !== 'all') $query->where('referral_type', $type);
        if ($status !== 'all') $query->where('status', $status);
        if ($priority !== 'all') $query->where('priority', $priority);

        $referrals = $query->paginate(20);

        $data = $referrals->getCollection()->map(function ($ref) {
            return [
                'id' => $ref->id,
                'patient_name' => $ref->patient 
                    ? trim("{$ref->patient->first_name} {$ref->patient->last_name}") 
                    : 'Unknown',
                'hospital_number' => $ref->patient?->immigration_service_number ?? 'N/A',
                'patient_gender' => $ref->patient?->gender ?? 'N/A',
                'referring_doctor' => $ref->referringDoctor?->name ?? 'Unknown',
                'referring_facility' => $ref->referring_facility,
                'receiving_facility' => $ref->receiving_facility,
                'receiving_doctor' => $ref->receiving_doctor,
                'referral_type' => $ref->referral_type,
                'priority' => $ref->priority,
                'reason' => $ref->reason,
                'clinical_summary' => $ref->clinical_summary,
                'special_instructions' => $ref->special_instructions,
                'status' => $ref->status,
                'referred_at' => $ref->referred_at?->format('d M Y H:i'),
                'accepted_at' => $ref->accepted_at?->format('d M Y H:i'),
                'completed_at' => $ref->completed_at?->format('d M Y H:i'),
            ];
        });

        // Stats
        $stats = [
            'total' => Referral::count(),
            'pending' => Referral::where('status', 'pending')->count(),
            'in_transit' => Referral::where('status', 'in_transit')->count(),
            'completed_today' => Referral::where('status', 'completed')
                ->whereDate('completed_at', Carbon::today())->count(),
            'emergency_priority' => Referral::where('priority', 'emergency')
                ->whereNotIn('status', ['completed', 'cancelled'])->count(),
        ];

        return response()->json([
            'referrals' => $data,
            'pagination' => [
                'current_page' => $referrals->currentPage(),
                'last_page' => $referrals->lastPage(),
                'total' => $referrals->total(),
            ],
            'stats' => $stats,
        ]);
    }

    /**
     * Create a new referral
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'visit_id' => 'nullable|exists:visits,id',
            'emergency_id' => 'nullable|exists:emergencies,id',
            'referring_doctor_id' => 'required|exists:staff,id',
            'receiving_facility' => 'required|string|max:255',
            'receiving_doctor' => 'nullable|string|max:255',
            'referral_type' => 'required|in:outgoing,incoming',
            'priority' => 'required|in:routine,urgent,emergency',
            'reason' => 'required|string|max:500',
            'clinical_summary' => 'required|string|min:20',
            'special_instructions' => 'nullable|string|max:1000',
        ]);

        $referral = Referral::create([
            ...$validated,
            'referring_facility' => 'NIS Medical Services Portal (NIS-MSP)',
            'status' => 'pending',
            'referred_at' => now(),
        ]);

        return response()->json([
            'message' => 'Referral created successfully.',
            'referral' => $referral->load(['patient', 'referringDoctor']),
        ], 201);
    }

    /**
     * Get referral details
     */
    public function show($id)
    {
        $referral = Referral::with(['patient', 'referringDoctor', 'visit', 'emergency'])->findOrFail($id);
        return response()->json(['referral' => $referral]);
    }

    /**
     * Update referral status
     */
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,accepted,in_transit,arrived,completed,cancelled',
        ]);

        $referral = Referral::findOrFail($id);
        $updates = ['status' => $validated['status']];

        // Track timestamps
        if ($validated['status'] === 'accepted' && !$referral->accepted_at) {
            $updates['accepted_at'] = now();
        }
        if (in_array($validated['status'], ['completed', 'arrived']) && !$referral->completed_at) {
            $updates['completed_at'] = now();
        }

        $referral->update($updates);

        return response()->json([
            'message' => 'Referral status updated.',
            'referral' => $referral->fresh(['patient', 'referringDoctor']),
        ]);
    }

    /**
     * Get available doctors for referral form
     */
    public function getDoctors()
    {
        $doctors = Staff::with('department:id,name')
            ->whereHas('user.roles', fn($q) => $q->whereIn('name', ['doctor', 'consultant', 'medical_director']))
            ->get(['id', 'first_name', 'last_name', 'department_id'])
            ->map(fn($s) => [
                'id' => $s->id,
                'name' => $s->full_name,
                'department' => $s->department?->name ?? 'General',
            ]);
        return response()->json(['doctors' => $doctors]);
    }
}
