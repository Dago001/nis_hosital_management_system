<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LabRequest;
use App\Models\LabResult;
use App\Models\RadiologyRequest;
use App\Models\RadiologyResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DiagnosticsController extends Controller
{
    // ==========================================
    // LABORATORY ENDPOINTS
    // ==========================================

    public function getLabQueue()
    {
        $queue = LabRequest::with(['patient', 'doctor'])->orderBy('created_at', 'desc')->get();
        return response()->json(['lab_requests' => $queue]);
    }

    public function collectSample(int $requestId)
    {
        $request = LabRequest::find($requestId);
        if (!$request) {
            return response()->json(['message' => 'Lab request not found.'], 404);
        }

        $request->status = 'sample_collected';
        $request->save();

        return response()->json(['message' => 'Sample collected successfully.', 'lab_request' => $request]);
    }

    public function submitLabResult(Request $request, int $requestId)
    {
        $labRequest = LabRequest::find($requestId);
        if (!$labRequest) {
            return response()->json(['message' => 'Lab request not found.'], 404);
        }

        $validated = $request->validate([
            'result_value' => 'required|string',
            'normal_range_min' => 'nullable|string',
            'normal_range_max' => 'nullable|string',
            'unit' => 'nullable|string',
            'remarks' => 'nullable|string'
        ]);

        $scientist = Auth::user()->staff;
        $scientistId = $scientist ? $scientist->id : null;

        $result = LabResult::updateOrCreate(
            ['lab_request_id' => $labRequest->id],
            [
                'scientist_id' => $scientistId,
                'result_value' => $validated['result_value'],
                'normal_range_min' => $validated['normal_range_min'] ?? null,
                'normal_range_max' => $validated['normal_range_max'] ?? null,
                'unit' => $validated['unit'] ?? null,
                'remarks' => $validated['remarks'] ?? null,
                'status' => 'draft'
            ]
        );

        $labRequest->status = 'completed';
        $labRequest->save();

        return response()->json([
            'message' => 'Laboratory result submitted as draft.',
            'result' => $result
        ]);
    }

    public function approveLabResult(int $requestId)
    {
        $result = LabResult::where('lab_request_id', $requestId)->first();
        if (!$result) {
            return response()->json(['message' => 'Lab result details not found.'], 404);
        }

        $result->status = 'approved';
        $result->approved_at = now();
        $result->save();

        return response()->json([
            'message' => 'Laboratory report approved and signed off.',
            'result' => $result
        ]);
    }

    // ==========================================
    // RADIOLOGY ENDPOINTS
    // ==========================================

    public function getRadiologyQueue()
    {
        $queue = RadiologyRequest::with(['patient', 'doctor'])->orderBy('created_at', 'desc')->get();
        return response()->json(['radiology_requests' => $queue]);
    }

    public function submitRadiologyResult(Request $request, int $requestId)
    {
        $radRequest = RadiologyRequest::find($requestId);
        if (!$radRequest) {
            return response()->json(['message' => 'Radiology request not found.'], 404);
        }

        $validated = $request->validate([
            'report_text' => 'required|string',
            'image_path' => 'nullable|string', // Mocked path or S3 url
        ]);

        $radiographer = Auth::user()->staff;
        $radiographerId = $radiographer ? $radiographer->id : null;

        $result = RadiologyResult::updateOrCreate(
            ['radiology_request_id' => $radRequest->id],
            [
                'radiographer_id' => $radiographerId,
                'image_path' => $validated['image_path'] ?? null,
                'report_text' => $validated['report_text'],
                'status' => 'draft'
            ]
        );

        $radRequest->status = 'completed';
        $radRequest->save();

        return response()->json([
            'message' => 'Radiology report submitted as draft.',
            'result' => $result
        ]);
    }

    public function approveRadiologyResult(int $requestId)
    {
        $result = RadiologyResult::where('radiology_request_id', $requestId)->first();
        if (!$result) {
            return response()->json(['message' => 'Radiology report details not found.'], 404);
        }

        $result->status = 'approved';
        $result->approved_at = now();
        $result->save();

        return response()->json([
            'message' => 'Radiology report approved and signed off.',
            'result' => $result
        ]);
    }
}
