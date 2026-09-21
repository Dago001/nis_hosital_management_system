<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\Patient;
use Illuminate\Http\Request;

/**
 * Facilities registry for multi-facility deployments. Records are scoped to a
 * facility via facility_id (patients, visits, admissions, appointments).
 */
class FacilityController extends Controller
{
    public function index()
    {
        $facilities = Facility::orderBy('name')->get()->map(fn ($f) => [
            'id' => $f->id,
            'name' => $f->name,
            'code' => $f->code,
            'type' => $f->type,
            'state' => $f->state,
            'address' => $f->address,
            'phone' => $f->phone,
            'is_active' => $f->is_active,
            'patients' => Patient::where('facility_id', $f->id)->count(),
        ]);

        return response()->json(['facilities' => $facilities]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'required|string|max:30|unique:facilities,code|regex:/^[A-Za-z0-9_\-]+$/',
            'type' => 'required|in:hospital,clinic,health_post',
            'state' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:30',
        ]);
        $validated['code'] = strtoupper($validated['code']);

        $facility = Facility::create($validated);

        return response()->json(['message' => 'Facility added.', 'facility' => $facility], 201);
    }

    public function update(Request $request, $id)
    {
        $facility = Facility::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:150',
            'type' => 'sometimes|in:hospital,clinic,health_post',
            'state' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:30',
            'is_active' => 'sometimes|boolean',
        ]);

        $facility->update($validated);

        return response()->json(['message' => 'Facility updated.', 'facility' => $facility]);
    }
}
