<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChatSession;
use App\Models\ChatMessage;
use App\Models\Staff;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class SupportChatController extends Controller
{
    // Visitor: Initialize session
    public function initSession(Request $request)
    {
        $validated = $request->validate([
            'visitor_name' => 'required|string|max:100'
        ]);

        $token = Str::random(40);
        $session = ChatSession::create([
            'visitor_name' => $validated['visitor_name'],
            'visitor_token' => $token,
            'status' => 'active'
        ]);

        // Send a welcome message automatically
        ChatMessage::create([
            'chat_session_id' => $session->id,
            'sender' => 'staff',
            'message' => "Hello {$validated['visitor_name']}! Welcome to Nigeria Immigration Service Hospital Customer Support. How can we assist you today?"
        ]);

        return response()->json([
            'session' => $session,
            'token' => $token
        ]);
    }

    // Visitor: Fetch messages
    public function getVisitorMessages(Request $request)
    {
        $token = $request->header('X-Visitor-Token') ?: $request->input('token');

        if (!$token) {
            return response()->json(['message' => 'Visitor session token required.'], 400);
        }

        $session = ChatSession::where('visitor_token', $token)->first();
        if (!$session) {
            return response()->json(['message' => 'Session not found.'], 404);
        }

        $messages = ChatMessage::where('chat_session_id', $session->id)->orderBy('created_at', 'asc')->get();

        return response()->json([
            'status' => $session->status,
            'messages' => $messages
        ]);
    }

    // Visitor: Send message
    public function sendVisitorMessage(Request $request)
    {
        $token = $request->header('X-Visitor-Token') ?: $request->input('token');
        $validated = $request->validate([
            'message' => 'required|string'
        ]);

        if (!$token) {
            return response()->json(['message' => 'Visitor session token required.'], 400);
        }

        $session = ChatSession::where('visitor_token', $token)->first();
        if (!$session) {
            return response()->json(['message' => 'Session not found.'], 404);
        }

        if ($session->status === 'closed') {
            return response()->json(['message' => 'Session is closed.'], 400);
        }

        $message = ChatMessage::create([
            'chat_session_id' => $session->id,
            'sender' => 'visitor',
            'message' => $validated['message']
        ]);

        return response()->json([
            'message' => $message
        ], 201);
    }

    // Staff: List sessions
    public function listSessions(Request $request)
    {
        $sessions = ChatSession::where('status', 'active')
            ->withCount(['messages' => function ($q) {
                $q->where('is_read', false)->where('sender', 'visitor');
            }])
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json([
            'sessions' => $sessions
        ]);
    }

    // Staff: Fetch specific session messages
    public function getSessionMessages(int $id)
    {
        $session = ChatSession::find($id);
        if (!$session) {
            return response()->json(['message' => 'Session not found.'], 404);
        }

        // Mark incoming messages as read
        ChatMessage::where('chat_session_id', $session->id)
            ->where('sender', 'visitor')
            ->update(['is_read' => true]);

        $messages = ChatMessage::where('chat_session_id', $session->id)->orderBy('created_at', 'asc')->get();

        return response()->json([
            'session' => $session,
            'messages' => $messages
        ]);
    }

    // Staff: Send reply
    public function sendStaffReply(Request $request, int $id)
    {
        $session = ChatSession::find($id);
        if (!$session) {
            return response()->json(['message' => 'Session not found.'], 404);
        }

        if ($session->status === 'closed') {
            return response()->json(['message' => 'Session is closed.'], 400);
        }

        $validated = $request->validate([
            'message' => 'required|string'
        ]);

        // Find staff record associated with authenticated user
        $user = Auth::user();
        $staff = Staff::where('user_id', $user->id)->first();

        $message = ChatMessage::create([
            'chat_session_id' => $session->id,
            'sender' => 'staff',
            'staff_id' => $staff ? $staff->id : null,
            'message' => $validated['message']
        ]);

        // Touch parent session to update 'updated_at' timestamp (so it sorts correctly in the staff list)
        $session->touch();

        return response()->json([
            'message' => $message
        ], 201);
    }

    // Staff: Close session
    public function closeSession(int $id)
    {
        $session = ChatSession::find($id);
        if (!$session) {
            return response()->json(['message' => 'Session not found.'], 404);
        }

        $session->status = 'closed';
        $session->save();

        ChatMessage::create([
            'chat_session_id' => $session->id,
            'sender' => 'staff',
            'message' => "This support chat session has been closed. Thank you for contacting Nigeria Immigration Service Hospital!"
        ]);

        return response()->json([
            'message' => 'Session closed successfully.'
        ]);
    }
}
