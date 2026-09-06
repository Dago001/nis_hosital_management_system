<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ChatbotService;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    protected ChatbotService $bot;

    public function __construct(ChatbotService $bot)
    {
        $this->bot = $bot;
    }

    /**
     * Public landing-page assistant endpoint.
     * Stateless: the client echoes back the `state` object each turn.
     */
    public function chat(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
            'state' => 'nullable|array',
        ]);

        $result = $this->bot->handle($validated['message'], $validated['state'] ?? []);

        return response()->json([
            'reply' => $result['reply'],
            'state' => $result['state'],
            'booked' => $result['booked'] ?? false,
            'source' => $result['source'] ?? 'offline',
        ]);
    }
}
