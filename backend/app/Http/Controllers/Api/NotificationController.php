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
        $user = $request->user();

        $notifications = $this->buildNotifications($user);

        // Apply persisted per-user read state.
        $readKeys = DB::table('notification_reads')
            ->where('user_id', $user->id)
            ->pluck('notification_key')
            ->flip();

        $sorted = $notifications
            ->map(function ($n) use ($readKeys) {
                $n['is_read'] = isset($readKeys[$n['id']]);
                return $n;
            })
            ->sortByDesc('created_at')
            ->values();

        return response()->json([
            'notifications' => $sorted,
            'unread_count'  => $sorted->where('is_read', false)->count(),
        ]);
    }

    /**
     * Aggregate the live notifications visible to the given user, based on role.
     */
    protected function buildNotifications($user): \Illuminate\Support\Collection
    {
        $role  = $user->roles()->first()?->name ?? '';
        $since = Carbon::now()->subHours(48); // Show last 48h notifications

        $notifications = collect();

        // ── 1. Appointment booking notifications (receptionist, admin) ──────
        if (in_array($role, [
            'super_admin', 'hospital_admin', 'receptionist', 'records_officer',
            'medical_director', 'chief_medical_officer'
        ])) {
            $appointments = DB::table('appointment_requests')
                ->where('created_at', '>=', $since)
                ->where('status', 'pending')
                ->orderByDesc('created_at')
                ->limit(10)
                ->get();

            foreach ($appointments as $appt) {
                $patientName = trim(($appt->first_name ?? '') . ' ' . ($appt->last_name ?? '')) ?: 'A patient';
                $notifications->push([
                    'id'         => 'appt_' . $appt->id,
                    'type'       => 'appointment',
                    'title'      => 'New Appointment Request',
                    'message'    => $patientName . ' requested an appointment' .
                                   ($appt->appointment_date ? ' for ' . Carbon::parse($appt->appointment_date)->format('M d') : ''),
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
                ->join('chat_sessions', 'chat_messages.chat_session_id', '=', 'chat_sessions.id')
                ->where('chat_messages.created_at', '>=', $since)
                ->where('chat_messages.sender', 'visitor')
                ->where('chat_messages.is_read', false)
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

            // A visit "waiting" for a doctor = triage vitals captured but no consultation yet.
            $visits = DB::table('visits')
                ->join('patients', 'visits.patient_id', '=', 'patients.id')
                ->where('visits.created_at', '>=', $since)
                ->whereNotNull('visits.vitals_blood_pressure')
                ->whereNull('visits.chief_complaint')
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
            $lowStock = DB::table('pharmacy_items')
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

        return $notifications;
    }

    /**
     * Persist a single notification as read for the current user.
     */
    public function markRead(Request $request, $id)
    {
        DB::table('notification_reads')->updateOrInsert(
            ['user_id' => $request->user()->id, 'notification_key' => (string) $id],
            ['read_at' => now(), 'updated_at' => now(), 'created_at' => now()]
        );

        return response()->json(['success' => true]);
    }

    /**
     * Persist all currently visible notifications as read for the current user.
     */
    public function markAllRead(Request $request)
    {
        $user = $request->user();
        $now = now();

        $rows = $this->buildNotifications($user)->map(fn ($n) => [
            'user_id' => $user->id,
            'notification_key' => (string) $n['id'],
            'read_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        foreach ($rows as $row) {
            DB::table('notification_reads')->updateOrInsert(
                ['user_id' => $row['user_id'], 'notification_key' => $row['notification_key']],
                ['read_at' => $row['read_at'], 'updated_at' => $row['updated_at'], 'created_at' => $row['created_at']]
            );
        }

        return response()->json(['success' => true, 'marked' => count($rows)]);
    }
}
