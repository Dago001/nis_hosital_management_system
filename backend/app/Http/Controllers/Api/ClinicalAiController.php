<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ClinicalAiService;
use Illuminate\Http\Request;

class ClinicalAiController extends Controller
{
    protected ClinicalAiService $ai;

    public function __construct(ClinicalAiService $ai)
    {
        $this->ai = $ai;
    }

    public function consult(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:4000',
            'history' => 'nullable|array|max:20',
            'history.*.role' => 'required_with:history|string|in:user,assistant',
            'history.*.content' => 'required_with:history|string|max:8000',
            'patient_context' => 'nullable|string|max:2000',
        ]);

        $result = $this->ai->respond(
            $validated['message'],
            $validated['history'] ?? [],
            $validated['patient_context'] ?? null
        );

        return response()->json([
            'reply' => $result['reply'],
            'source' => $result['source'], // 'live' (Claude) or 'offline' (knowledge base)
        ]);
    }
}
