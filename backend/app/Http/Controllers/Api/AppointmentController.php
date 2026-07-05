<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Staff;
use App\Models\Department;
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
            'staff_id' => 'required|exists:staff,id', // Doctor
            'department_id' => 'required|exists:departments,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required',
            'notes' => 'nullable|string'
        ]);

        // Calculate queue number for the selected doctor and date
        $maxQueue = Appointment::where('staff_id', $request->staff_id)
            ->whereDate('appointment_date', $request->appointment_date)
            ->max('queue_number') ?? 0;

        $validated['queue_number'] = $maxQueue + 1;
        $validated['status'] = 'pending';

        $appointment = Appointment::create($validated);

        return response()->json([
            'message' => 'Appointment booked successfully.',
            'appointment' => $appointment->load(['patient', 'doctor', 'department'])
        ], 210);
    }

    public function checkIn(int $id)
    {
        $appointment = Appointment::find($id);

        if (!$appointment) {
            return response()->json(['message' => 'Appointment not found.'], 404);
        }

        $appointment->status = 'checked_in';
        $appointment->save();

        return response()->json([
            'message' => 'Patient checked in successfully.',
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
}
