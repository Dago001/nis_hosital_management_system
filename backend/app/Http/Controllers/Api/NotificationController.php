<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NotificationController extends Controller
{
    /**
     * Return real-time notifications for the authenticated user.
     * Aggregates: new appointment bookings, new support messages,
     * patient assignments, lab/pharmacy orders – based on role.
     */
    public function index(Request $request)
    {
        $user  = $request->user();
        $role  = $user->roles()->first()?->name ?? '';
        $since = Carbon::now()->subHours(48); // Show last 48h notifications

        $notifications = collect();

        // ── 1. Appointment booking notifications (receptionist, admin) ──────
        if (in_array($role, [
            'super_admin', 'hospital_admin', 'receptionist', 'records_officer',
            'medical_director', 'chief_medical_officer'
        ])) {
            $appointments = DB::table('appointments')
                ->where('created_at', '>=', $since)
                ->where('status', 'pending')
                ->orderByDesc('created_at')
                ->limit(10)
                ->get();

            foreach ($appointments as $appt) {
                $notifications->push([
                    'id'         => 'appt_' . $appt->id,
                    'type'       => 'appointment',
                    'title'      => 'New Appointment Request',
                    'message'    => ($appt->patient_name ?? 'A patient') . ' requested an appointment' .
                                   ($appt->preferred_date ? ' for ' . Carbon::parse($appt->preferred_date)->format('M d') : ''),
                    'is_read'    => false,
                    'created_at' => $appt->created_at,
                ]);
            }
        }

        // ── 2. Support chat messages (receptionist, admin) ──────────────────
        if (in_array($role, [
            'super_admin', 'hospital_admin', 'receptionist', 'records_officer',
            'medical_director', 'chief_medical_officer', 'ict_admin'
        ])) {
            $chatMessages = DB::table('chat_messages')
                ->join('chat_sessions', 'chat_messages.session_id', '=', 'chat_sessions.id')
                ->where('chat_messages.created_at', '>=', $since)
                ->where('chat_messages.sender_type', 'visitor')
                ->whereNull('chat_messages.read_at')
                ->orderByDesc('chat_messages.created_at')
                ->limit(5)
                ->select('chat_messages.*', 'chat_sessions.visitor_name')
                ->get();

            foreach ($chatMessages as $msg) {
                $notifications->push([
                    'id'         => 'chat_' . $msg->id,
                    'type'       => 'message',
                    'title'      => 'New Support Message',
                    'message'    => ($msg->visitor_name ?? 'Visitor') . ': ' . \Illuminate\Support\Str::limit($msg->message, 60),
                    'is_read'    => false,
                    'created_at' => $msg->created_at,
                ]);
            }
        }

        // ── 3. Patient assigned to doctor / nurse (clinical staff) ───────────
        if (in_array($role, ['doctor', 'consultant', 'nurse', 'ward_manager',
            'dental_officer', 'eye_clinic_officer', 'physiotherapist', 'theatre_manager'])) {

            $visits = DB::table('visits')
                ->join('patients', 'visits.patient_id', '=', 'patients.id')
                ->where('visits.created_at', '>=', $since)
                ->where('visits.status', 'waiting')
                ->orderByDesc('visits.created_at')
                ->limit(5)
                ->select('visits.id', 'visits.created_at', 'patients.first_name', 'patients.last_name', 'patients.immigration_service_number')
                ->get();

            foreach ($visits as $visit) {
                $notifications->push([
                    'id'         => 'visit_' . $visit->id,
                    'type'       => 'patient',
                    'title'      => 'Patient Assigned to Queue',
                    'message'    => $visit->first_name . ' ' . $visit->last_name . ' (' . $visit->immigration_service_number . ') is waiting',
                    'is_read'    => false,
                    'created_at' => $visit->created_at,
                ]);
            }
        }

        // ── 4. Lab results ready (doctors) ───────────────────────────────────
        if (in_array($role, ['doctor', 'consultant', 'super_admin', 'medical_director'])) {
            $labs = DB::table('lab_requests')
                ->where('created_at', '>=', $since)
                ->where('status', 'completed')
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();

            foreach ($labs as $lab) {
                $notifications->push([
                    'id'         => 'lab_' . $lab->id,
                    'type'       => 'lab',
                    'title'      => 'Lab Result Ready',
                    'message'    => 'A laboratory result has been completed and is available for review.',
                    'is_read'    => false,
                    'created_at' => $lab->created_at,
                ]);
            }
        }

        // ── 5. Low stock alerts (pharmacist / inventory) ─────────────────────
        if (in_array($role, [
            'super_admin', 'pharmacist', 'store_officer', 'inventory_officer', 'procurement_officer'
        ])) {
            $lowStock = DB::table('medicine_inventories')
                ->whereColumn('quantity_in_stock', '<=', 'reorder_level')
                ->where('quantity_in_stock', '>', 0)
                ->limit(5)
                ->get();

            foreach ($lowStock as $item) {
                $notifications->push([
                    'id'         => 'stock_' . $item->id,
                    'type'       => 'pharmacy',
                    'title'      => 'Low Stock Alert',
                    'message'    => ($item->name ?? 'Item') . ' is below reorder level (' . ($item->quantity_in_stock ?? 0) . ' remaining)',
                    'is_read'    => false,
                    'created_at' => now()->toDateTimeString(),
                ]);
            }
        }

        // Sort newest first
        $sorted = $notifications->sortByDesc('created_at')->values();

        return response()->json([
            'notifications' => $sorted,
            'unread_count'  => $sorted->where('is_read', false)->count(),
        ]);
    }

    /**
     * Mark a single notification as read (client-side state – no DB required
     * since notifications are aggregated; just return success).
     */
    public function markRead(Request $request, $id)
    {
        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllRead(Request $request)
    {
        return response()->json(['success' => true]);
    }
}
