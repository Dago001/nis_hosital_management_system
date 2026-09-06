<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceTariff;
use Illuminate\Http\Request;

class TariffController extends Controller
{
    public function index()
    {
        return response()->json([
            'tariffs' => ServiceTariff::orderBy('category')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:service_tariffs,code|regex:/^[A-Za-z0-9_\-]+$/',
            'name' => 'required|string|max:255',
            'category' => 'required|in:consultation,procedure,lab,radiology,bed,registration,other',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:500',
        ]);
        $validated['code'] = strtoupper($validated['code']);

        $tariff = ServiceTariff::create($validated);

        return response()->json(['message' => 'Tariff created.', 'tariff' => $tariff], 201);
    }

    public function update(Request $request, $id)
    {
        $tariff = ServiceTariff::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'category' => 'sometimes|in:consultation,procedure,lab,radiology,bed,registration,other',
            'price' => 'sometimes|numeric|min:0',
            'is_active' => 'sometimes|boolean',
            'description' => 'nullable|string|max:500',
        ]);

        $tariff->update($validated);

        return response()->json(['message' => 'Tariff updated.', 'tariff' => $tariff]);
    }
}
