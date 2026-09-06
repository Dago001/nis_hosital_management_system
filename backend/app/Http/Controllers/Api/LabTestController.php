<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LabTest;
use Illuminate\Http\Request;

/**
 * Laboratory test catalogue: reference ranges + critical thresholds used to
 * auto-flag results at entry.
 */
class LabTestController extends Controller
{
    public function index()
    {
        return response()->json([
            'tests' => LabTest::orderBy('category')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->rules($request, true);
        $validated['code'] = strtoupper($validated['code']);

        $test = LabTest::create($validated);

        return response()->json(['message' => 'Test added to catalogue.', 'test' => $test], 201);
    }

    public function update(Request $request, $id)
    {
        $test = LabTest::findOrFail($id);
        $validated = $this->rules($request, false);
        $test->update($validated);

        return response()->json(['message' => 'Catalogue entry updated.', 'test' => $test]);
    }

    private function rules(Request $request, bool $creating): array
    {
        return $request->validate([
            'code' => ($creating ? 'required' : 'sometimes') . '|string|max:30|regex:/^[A-Za-z0-9_\-]+$/' . ($creating ? '|unique:lab_tests,code' : ''),
            'name' => ($creating ? 'required' : 'sometimes') . '|string|max:150',
            'category' => 'nullable|string|max:60',
            'unit' => 'nullable|string|max:30',
            'ref_low' => 'nullable|numeric',
            'ref_high' => 'nullable|numeric',
            'critical_low' => 'nullable|numeric',
            'critical_high' => 'nullable|numeric',
            'is_active' => 'sometimes|boolean',
        ]);
    }
}
