<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Visit;
use App\Models\Patient;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class QueueController extends Controller
{
    /**
     * Get today's active queue (outpatient waiting list)
     */
    public function index(Request $request)
    {
        $date = $request->get('date', Carbon::today()->toDateString());
        $departmentId = $request->get('department_id');
        $statusFilter = $request->get('status', 'all'); // all, pending, checked_in, in_consultation, completed

        // 1. Fetch appointments for this date
        $apptQuery = Appointment::with([
            'patient:id,first_name,last_name,immigration_service_number,phone',
            'doctor:id,first_name,last_name',
            'department:id,name',
        ])
        ->whereDate('appointment_date', $date)
        ->orderBy('queue_number');

        if ($departmentId) {
            $apptQuery->where('department_id', $departmentId);
        }

        $appointments = $apptQuery->get();

        // 2. Fetch active visits created on this date with vitals but pending consult
        $visitQuery = Visit::with([
            'patient:id,first_name,last_name,immigration_service_number,phone',
            'doctor:id,first_name,last_name',
            'department:id,name'
        ])
        ->whereDate('created_at', $date)
        ->whereNotNull('vitals_blood_pressure')
        ->whereNull('chief_complaint');

        if ($departmentId) {
            $visitQuery->where('department_id', $departmentId);
        }

        $activeVisits = $visitQuery->get();

        // 3. Build Unified Queue List
        $queueItems = collect();

        // Process scheduled appointments
        foreach ($appointments as $appt) {
            $matchingVisit = $activeVisits->firstWhere('patient_id', $appt->patient_id);
            
            $status = $appt->status;
            // If vitals are recorded but consult is pending, show as waiting (checked_in)
            if ($matchingVisit && $status === 'pending') {
                $status = 'checked_in';
            }

            $queueItems->push([
                'id' => (string) $appt->id,
                'queue_number' => (string) $appt->queue_number,
                'patient_id' => $appt->patient_id,
                'patient_name' => $appt->patient 
                    ? trim("{$appt->patient->first_name} {$appt->patient->last_name}") 
                    : 'Unknown',
                'hospital_number' => $appt->patient->immigration_service_number ?? 'N/A',
                'patient_phone' => $appt->patient->phone ?? '',
                'doctor' => $appt->doctor->full_name ?? 'Not Assigned',
                'department' => $appt->department->name ?? 'General',
                'appointment_time' => $appt->appointment_time,
                'status' => $status,
                'notes' => $appt->notes,
                'visit_id' => $matchingVisit?->id,
                'has_vitals' => $matchingVisit ? true : false,
                'wait_time_minutes' => $this->calculateWaitTime($appt),
            ]);
        }

        // Add walk-in visits that don't have a matching appointment today
        foreach ($activeVisits as $visit) {
            $hasAppt = $appointments->contains('patient_id', $visit->patient_id);
            if (!$hasAppt) {
                $queueItems->push([
                    'id' => 'V-' . $visit->id, // prefixed for walk-ins
                    'queue_number' => 'W-' . $visit->id,
                    'patient_id' => $visit->patient_id,
                    'patient_name' => $visit->patient 
                        ? trim("{$visit->patient->first_name} {$visit->patient->last_name}") 
                        : 'Unknown',
                    'hospital_number' => $visit->patient->immigration_service_number ?? 'N/A',
                    'patient_phone' => $visit->patient->phone ?? '',
                    'doctor' => $visit->doctor->full_name ?? 'Not Assigned',
                    'department' => $visit->department->name ?? 'General',
                    'appointment_time' => $visit->created_at->format('H:i'),
                    'status' => 'checked_in', // default to checked-in since vitals are recorded
                    'notes' => 'Walk-in Triage Vitals',
                    'visit_id' => $visit->id,
                    'has_vitals' => true,
                    'wait_time_minutes' => max(0, (int) $visit->created_at->diffInMinutes(Carbon::now())),
                ]);
            }
        }

        // Apply status filtering
        if ($statusFilter && $statusFilter !== 'all') {
            $queueItems = $queueItems->filter(fn($item) => $item['status'] === $statusFilter);
        } else {
            $queueItems = $queueItems->filter(fn($item) => $item['status'] !== 'cancelled');
        }

        $stats = [
            'total' => $queueItems->count(),
            'waiting' => $queueItems->where('status', 'pending')->count(),
            'checked_in' => $queueItems->where('status', 'checked_in')->count(),
            'completed' => $queueItems->where('status', 'completed')->count(),
            'avg_wait_mins' => round($queueItems->where('status', 'checked_in')->avg('wait_time_minutes') ?? 0),
        ];

        return response()->json([
            'queue' => $queueItems->values(),
            'stats' => $stats,
            'date' => $date,
        ]);
    }

    /**
     * Call next patient / change queue status
     */
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,checked_in,in_consultation,completed,no_show,cancelled',
            'notes' => 'nullable|string|max:500',
        ]);

        if (str_starts_with($id, 'V-')) {
            // It's a walk-in visit status update
            $visitId = str_replace('V-', '', $id);
            $visit = Visit::findOrFail($visitId);
            
            if ($validated['status'] === 'completed') {
                $visit->update([
                    'chief_complaint' => 'Attended by Doctor (Walk-in)',
                    'soap_notes_assessment' => 'Completed',
                    'diagnosis_icd10' => 'Z00.0',
                    'diagnosis_description' => 'General medical examination'
                ]);
            }
            return response()->json([
                'message' => 'Walk-in queue status updated successfully.',
            ]);
        }

        $appointment = Appointment::findOrFail($id);
        $appointment->update($validated);

        if ($validated['status'] === 'completed') {
            // Also close active visit if present
            $visit = Visit::where('patient_id', $appointment->patient_id)
                ->whereNull('chief_complaint')
                ->first();
            if ($visit) {
                $visit->update([
                    'chief_complaint' => 'Attended by Doctor',
                    'soap_notes_assessment' => 'Completed',
                    'diagnosis_icd10' => 'Z00.0',
                    'diagnosis_description' => 'General medical examination'
                ]);
            }
        }

        return response()->json([
            'message' => 'Queue status updated successfully.',
            'appointment' => $appointment->load(['patient:id,first_name,last_name', 'department:id,name']),
        ]);
    }

    /**
     * Get departments list for filter
     */
    public function getDepartments()
    {
        $departments = \App\Models\Department::select('id', 'name')->get();
        return response()->json(['departments' => $departments]);
    }

    /**
     * Get real-time queue summary stats
     */
    public function stats()
    {
        $today = Carbon::today()->toDateString();

        $byStatus = Appointment::whereDate('appointment_date', $today)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $byDepartment = Appointment::with('department:id,name')
            ->whereDate('appointment_date', $today)
            ->whereNotIn('status', ['cancelled'])
            ->selectRaw('department_id, count(*) as count')
            ->groupBy('department_id')
            ->get()
            ->map(fn($r) => [
                'department' => $r->department->name ?? 'General',
                'count' => $r->count,
            ]);

        return response()->json([
            'today' => $today,
            'by_status' => $byStatus,
            'by_department' => $byDepartment,
        ]);
    }

    private function calculateWaitTime(Appointment $appt): int
    {
        if ($appt->status !== 'pending' && $appt->status !== 'checked_in') {
            return 0;
        }
        $scheduled = Carbon::today()->setTimeFromTimeString($appt->appointment_time);
        return max(0, (int) $scheduled->diffInMinutes(Carbon::now(), false) * -1);
    }
}
