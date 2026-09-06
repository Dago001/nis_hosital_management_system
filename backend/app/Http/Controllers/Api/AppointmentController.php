<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Staff;
use App\Models\Department;
use App\Models\Patient;
use App\Models\AppointmentRequest;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Appointment::with(['patient', 'doctor', 'department']);

        if ($request->has('date')) {
            $query->whereDate('appointment_date', $request->date);
        } else {
            $query->whereDate('appointment_date', Carbon::today());
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('staff_id')) {
            $query->where('staff_id', $request->staff_id);
        }

        $appointments = $query->orderBy('queue_number', 'asc')->get();

        return response()->json([
            'appointments' => $appointments
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'staff_id' => 'nullable|exists:staff,id', // Doctor is optional before vitals
            'department_id' => 'required|exists:departments,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required',
            'notes' => 'nullable|string'
        ]);

        // Calculate queue number for the selected doctor and date
        $queryQueue = Appointment::whereDate('appointment_date', $request->appointment_date);
        if ($request->staff_id) {
            $queryQueue->where('staff_id', $request->staff_id);
        } else {
            $queryQueue->whereNull('staff_id');
        }
        $maxQueue = $queryQueue->max('queue_number') ?? 0;

        $validated['queue_number'] = $maxQueue + 1;
        $validated['status'] = 'pending';

        $appointment = Appointment::create($validated);

        return response()->json([
            'message' => 'Appointment booked successfully.',
            'appointment' => $appointment->load(['patient', 'doctor', 'department'])
        ], 201);
    }

    public function checkIn(int $id)
    {
        $appointment = Appointment::find($id);

        if (!$appointment) {
            return response()->json(['message' => 'Appointment not found.'], 404);
        }

        $appointment->status = 'checked_in';
        $appointment->save();

        // Create a pending Visit record with null vitals so it appears in the Nurse vitals queue
        \App\Models\Visit::create([
            'patient_id' => $appointment->patient_id,
            'department_id' => $appointment->department_id,
            'staff_id' => $appointment->staff_id, // initial requested doctor
        ]);

        return response()->json([
            'message' => 'Patient checked in successfully and queued for vitals capture.',
            'appointment' => $appointment
        ]);
    }

    public function cancel(int $id)
    {
        $appointment = Appointment::find($id);

        if (!$appointment) {
            return response()->json(['message' => 'Appointment not found.'], 404);
        }

        $appointment->status = 'cancelled';
        $appointment->save();

        return response()->json([
            'message' => 'Appointment cancelled successfully.',
            'appointment' => $appointment
        ]);
    }

    /**
     * Mark a booked appointment as a no-show (patient did not attend).
     */
    public function markNoShow(int $id)
    {
        $appointment = Appointment::find($id);
        if (!$appointment) {
            return response()->json(['message' => 'Appointment not found.'], 404);
        }

        if (in_array($appointment->status, ['checked_in', 'completed'])) {
            return response()->json(['message' => 'This patient already attended; cannot mark as no-show.'], 422);
        }

        $appointment->status = 'no_show';
        $appointment->no_show_at = now();
        $appointment->save();

        return response()->json(['message' => 'Appointment marked as no-show.', 'appointment' => $appointment]);
    }

    /**
     * Record that a reminder was sent for a single appointment. (An SMS/email
     * gateway can be plugged in here; for now we stamp the reminder time so the
     * front desk can see who has already been reminded.)
     */
    public function sendReminder(int $id)
    {
        $appointment = Appointment::with(['patient', 'doctor'])->find($id);
        if (!$appointment) {
            return response()->json(['message' => 'Appointment not found.'], 404);
        }

        $appointment->reminder_sent_at = now();
        $appointment->save();

        return response()->json([
            'message' => 'Reminder logged for ' . $appointment->patient?->full_name . '.',
            'appointment' => $appointment,
        ]);
    }

    /**
     * Bulk-send reminders for all still-pending appointments on a target date
     * (defaults to tomorrow) that have not already been reminded.
     */
    public function sendDueReminders(Request $request)
    {
        $date = $request->input('date', Carbon::tomorrow()->toDateString());

        $due = Appointment::whereDate('appointment_date', $date)
            ->where('status', 'pending')
            ->whereNull('reminder_sent_at')
            ->get();

        foreach ($due as $appointment) {
            $appointment->reminder_sent_at = now();
            $appointment->save();
        }

        return response()->json([
            'message' => "Reminders sent for {$due->count()} appointment(s) on {$date}.",
            'count' => $due->count(),
            'date' => $date,
        ]);
    }

    /**
     * No-show and reminder statistics for a date range (defaults to last 30 days).
     */
    public function attendanceStats(Request $request)
    {
        $from = $request->input('from', Carbon::today()->subDays(30)->toDateString());
        $to = $request->input('to', Carbon::today()->toDateString());

        $base = Appointment::whereBetween('appointment_date', [$from, $to]);

        $total = (clone $base)->count();
        $noShows = (clone $base)->where('status', 'no_show')->count();
        $attended = (clone $base)->whereIn('status', ['checked_in', 'completed'])->count();
        $cancelled = (clone $base)->where('status', 'cancelled')->count();

        return response()->json([
            'from' => $from,
            'to' => $to,
            'total' => $total,
            'attended' => $attended,
            'no_shows' => $noShows,
            'cancelled' => $cancelled,
            'no_show_rate' => $total > 0 ? round($noShows / $total * 100, 1) : 0,
        ]);
    }

    public function getDoctors()
    {
        // Get all staff who belong to clinical departments and have a doctor/consultant role
        $doctors = Staff::whereHas('user.roles', function($q) {
            $q->whereIn('name', ['doctor', 'consultant']);
        })->where('status', 'active')->get();

        return response()->json([
            'doctors' => $doctors
        ]);
    }

    public function requestAppointment(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:150',
            'phone' => 'required|string|max:20',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required',
            'immigration_service_number' => 'nullable|string',
            'notes' => 'nullable|string'
        ]);

        $validated['status'] = 'pending';
        $appointmentRequest = AppointmentRequest::create($validated);

        return response()->json([
            'message' => 'Appointment request submitted successfully to the Front Desk for confirmation.',
            'request' => $appointmentRequest
        ], 201);
    }

    public function listRequests(Request $request)
    {
        $status = $request->input('status', 'pending');
        $requests = AppointmentRequest::where('status', $status)->orderBy('created_at', 'desc')->get();

        return response()->json([
            'requests' => $requests
        ]);
    }

    public function confirmRequest(Request $request, $id)
    {
        $aptRequest = AppointmentRequest::find($id);

        if (!$aptRequest) {
            return response()->json(['message' => 'Appointment request not found.'], 404);
        }

        $validated = $request->validate([
            'staff_id' => 'nullable|exists:staff,id', // Doctor is optional
            'department_id' => 'required|exists:departments,id',
        ]);

        try {
            \DB::beginTransaction();

            // Find or create patient
            $patient = null;
            if (!empty($aptRequest->immigration_service_number)) {
                $patient = Patient::where('immigration_service_number', $aptRequest->immigration_service_number)->first();
            }

            if (!$patient) {
                // Generate unique civilian code if not provided
                $service = app(\App\Services\PatientService::class);
                $hospitalCode = $service->generateHospitalCode();

                $patient = Patient::create([
                    'first_name' => $aptRequest->first_name,
                    'last_name' => $aptRequest->last_name,
                    'email' => $aptRequest->email,
                    'phone' => $aptRequest->phone,
                    'address' => 'Federal Capital Territory, Abuja',
                    'gender' => 'Other', // default placeholder
                    'date_of_birth' => '1990-01-01', // default placeholder
                    'immigration_service_number' => $hospitalCode
                ]);
            }

            // Calculate queue number
            $queryQueue = Appointment::whereDate('appointment_date', $aptRequest->appointment_date);
            if (!empty($validated['staff_id'])) {
                $queryQueue->where('staff_id', $validated['staff_id']);
            } else {
                $queryQueue->whereNull('staff_id');
            }
            $maxQueue = $queryQueue->max('queue_number') ?? 0;

            // Create Appointment
            $appointment = Appointment::create([
                'patient_id' => $patient->id,
                'staff_id' => $validated['staff_id'] ?? null,
                'department_id' => $validated['department_id'],
                'appointment_date' => $aptRequest->appointment_date,
                'appointment_time' => $aptRequest->appointment_time,
                'queue_number' => $maxQueue + 1,
                'status' => 'pending',
                'notes' => $aptRequest->notes
            ]);

            // Update Request
            $aptRequest->status = 'confirmed';
            $aptRequest->save();

            \DB::commit();

            $doctorName = 'Pending Vitals Capture (unassigned)';
            if (!empty($validated['staff_id'])) {
                $doctor = Staff::find($validated['staff_id']);
                if ($doctor) {
                    $doctorName = "Dr. {$doctor->first_name} {$doctor->last_name}";
                }
            }

            // Log Simulated Email Notice
            $emailBody = "DEAR {$patient->first_name} {$patient->last_name},\n\nYour appointment booking request has been CONFIRMED by the Front Desk!\n\nDetails:\n- Hospital Code: {$patient->immigration_service_number}\n- Date: {$appointment->appointment_date}\n- Time: {$appointment->appointment_time}\n- Doctor: {$doctorName}\n\nPlease proceed to triage vitals capturing upon arrival.\n\nWarm regards,\nNigeria Immigration Service Hospital, Abuja.";
            \Illuminate\Support\Facades\Log::info("EMAIL SENT TO {$patient->email}:\n{$emailBody}");

            return response()->json([
                'message' => 'Appointment request confirmed successfully! Roster appointment created.',
                'appointment' => $appointment,
                'patient' => $patient,
                'simulated_email' => $emailBody
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['message' => 'Confirmation failed: ' . $e->getMessage()], 500);
        }
    }

    public function rejectRequest(int $id)
    {
        $aptRequest = AppointmentRequest::find($id);

        if (!$aptRequest) {
            return response()->json(['message' => 'Appointment request not found.'], 404);
        }

        $aptRequest->status = 'rejected';
        $aptRequest->save();

        // Log Simulated Rejection Notice
        $emailBody = "DEAR {$aptRequest->first_name} {$aptRequest->last_name},\n\nWe regret to inform you that your appointment request for {$aptRequest->appointment_date} at {$aptRequest->appointment_time} could not be confirmed at this time.\n\nPlease contact the Support Desk or submit a new booking request.\n\nWarm regards,\nNigeria Immigration Service Hospital, Abuja.";
        \Illuminate\Support\Facades\Log::info("REJECTION EMAIL SENT TO {$aptRequest->email}:\n{$emailBody}");

        return response()->json([
            'message' => 'Appointment request rejected. Rejection notice simulated.',
            'simulated_email' => $emailBody
        ]);
    }
}
