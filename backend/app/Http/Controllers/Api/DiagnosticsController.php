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
        $queue = LabRequest::with(['patient', 'doctor', 'result', 'invoice'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($req) {
                // Flatten result fields onto the row so the worklist / print
                // report can read result_value, ranges and the computed flag.
                $arr = $req->toArray();
                $arr['result_value'] = $req->result?->result_value;
                $arr['normal_range_min'] = $req->result?->normal_range_min;
                $arr['normal_range_max'] = $req->result?->normal_range_max;
                $arr['unit'] = $req->result?->unit;
                $arr['flag'] = $req->result?->flag;
                // Payment gate: the cashier must settle the lab invoice before
                // the sample is processed.
                $arr['invoice_id'] = $req->invoice_id;
                $arr['payment_status'] = $req->invoice?->status ?? ($req->invoice_id ? 'unpaid' : 'n/a');
                $arr['is_paid'] = $req->isPaid();
                $arr['bill_amount'] = $req->invoice
                    ? (float) $req->invoice->total_amount - (float) $req->invoice->discount_amount
                    : null;
                return $arr;
            });

        return response()->json(['lab_requests' => $queue]);
    }

    public function collectSample(int $requestId)
    {
        $request = LabRequest::with('invoice')->find($requestId);
        if (!$request) {
            return response()->json(['message' => 'Lab request not found.'], 404);
        }

        if (!$request->isPaid()) {
            return response()->json(['message' => 'Payment for this lab test is not yet confirmed by the cashier.'], 402);
        }

        $request->status = 'sample_collected';
        $request->save();

        return response()->json(['message' => 'Sample collected successfully.', 'lab_request' => $request]);
    }

    public function submitLabResult(Request $request, int $requestId)
    {
        $labRequest = LabRequest::with('invoice')->find($requestId);
        if (!$labRequest) {
            return response()->json(['message' => 'Lab request not found.'], 404);
        }

        if (!$labRequest->isPaid()) {
            return response()->json(['message' => 'Payment for this lab test is not yet confirmed by the cashier.'], 402);
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

        // Look up the catalogue entry for this test to backfill reference
        // ranges / unit and to auto-flag the value against the range.
        $catalogue = \App\Models\LabTest::matchByName($labRequest->test_name);

        $rangeMin = $validated['normal_range_min'] ?? ($catalogue?->ref_low !== null ? (string) $catalogue->ref_low : null);
        $rangeMax = $validated['normal_range_max'] ?? ($catalogue?->ref_high !== null ? (string) $catalogue->ref_high : null);
        $unit = $validated['unit'] ?? $catalogue?->unit;

        $flag = $catalogue?->flagFor($validated['result_value']);

        $result = LabResult::updateOrCreate(
            ['lab_request_id' => $labRequest->id],
            [
                'scientist_id' => $scientistId,
                'result_value' => $validated['result_value'],
                'normal_range_min' => $rangeMin,
                'normal_range_max' => $rangeMax,
                'unit' => $unit,
                'flag' => $flag,
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
