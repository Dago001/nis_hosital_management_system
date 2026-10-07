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
            'message' => 'required|string|max:2000',
        ]);

        $result = $this->ai->respond($validated['message']);

        return response()->json([
            'reply' => $result['reply'],
            'source' => $result['source'], // 'live' (Claude) or 'offline' (knowledge base)
        ]);
    }
}
