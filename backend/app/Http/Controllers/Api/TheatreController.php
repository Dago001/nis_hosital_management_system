<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Models\Surgery;
use App\Models\Theatre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Operating-theatre scheduling with double-booking prevention.
 */
class TheatreController extends Controller
{
    public function theatres()
    {
        return response()->json(['theatres' => Theatre::where('is_active', true)->orderBy('name')->get()]);
    }

    public function surgeons()
    {
        $surgeons = Staff::whereHas('user.roles', function ($q) {
            $q->whereIn('name', ['doctor', 'consultant', 'theatre_manager']);
        })->where('status', 'active')->get(['id', 'first_name', 'last_name']);

        return response()->json([
            'surgeons' => $surgeons->map(fn ($s) => ['id' => $s->id, 'full_name' => trim($s->first_name . ' ' . $s->last_name)]),
        ]);
    }

    public function index(Request $request)
    {
        $query = Surgery::with(['patient:id,first_name,last_name,immigration_service_number', 'surgeon:id,first_name,last_name', 'theatre:id,name']);

        if ($request->filled('date')) {
            $query->whereDate('scheduled_start', $request->date);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $surgeries = $query->orderBy('scheduled_start')->limit(200)->get();

        return response()->json([
            'surgeries' => $surgeries->map(fn ($s) => [
                'id' => $s->id,
                'patient' => trim($s->patient?->first_name . ' ' . $s->patient?->last_name),
                'hospital_code' => $s->patient?->immigration_service_number,
                'surgeon' => $s->surgeon ? trim($s->surgeon->first_name . ' ' . $s->surgeon->last_name) : null,
                'theatre' => $s->theatre?->name,
                'procedure_name' => $s->procedure_name,
                'scheduled_start' => $s->scheduled_start?->toDateTimeString(),
                'scheduled_end' => $s->scheduled_end?->toDateTimeString(),
                'status' => $s->status,
                'notes' => $s->notes,
            ]),
            'stats' => [
                'today' => Surgery::whereDate('scheduled_start', now()->toDateString())->whereNot('status', 'cancelled')->count(),
                'scheduled' => Surgery::where('status', 'scheduled')->count(),
                'in_progress' => Surgery::where('status', 'in_progress')->count(),
                'completed' => Surgery::where('status', 'completed')->count(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'surgeon_id' => 'nullable|exists:staff,id',
            'theatre_id' => 'required|exists:theatres,id',
            'procedure_name' => 'required|string|max:200',
            'scheduled_start' => 'required|date',
            'scheduled_end' => 'required|date|after:scheduled_start',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Prevent double-booking the same theatre for an overlapping window.
        $clash = Surgery::where('theatre_id', $validated['theatre_id'])
            ->whereNot('status', 'cancelled')
            ->where('scheduled_start', '<', $validated['scheduled_end'])
            ->where('scheduled_end', '>', $validated['scheduled_start'])
            ->exists();

        if ($clash) {
            return response()->json(['message' => 'This theatre is already booked for an overlapping time slot.'], 422);
        }

        $validated['status'] = 'scheduled';
        $validated['created_by'] = Auth::id();
        $surgery = Surgery::create($validated);

        return response()->json(['message' => 'Surgery scheduled.', 'surgery' => $surgery], 201);
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:scheduled,in_progress,completed,cancelled',
        ]);

        $surgery = Surgery::findOrFail($id);
        $surgery->status = $validated['status'];
        $surgery->save();

        return response()->json(['message' => 'Surgery status updated.', 'surgery' => $surgery]);
    }
}
