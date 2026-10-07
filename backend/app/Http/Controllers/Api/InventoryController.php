<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PharmacyItem;
use App\Models\StockIssuance;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Central-store inventory operations. The Inventory Officer issues drugs,
 * injections and consumables from the main store to the pharmacy dispensary,
 * and every issuance is recorded as an auditable ledger entry.
 */
class InventoryController extends Controller
{
    /**
     * Current pharmacy/store items (used to populate the issue form).
     */
    public function items()
    {
        $items = PharmacyItem::orderBy('name')->get([
            'id', 'name', 'generic_name', 'code', 'category', 'quantity_in_stock',
            'reorder_level', 'batch_number', 'expiry_date', 'price_per_unit',
        ]);

        return response()->json(['items' => $items]);
    }

    /**
     * Pharmacists available to receive issued stock.
     */
    public function pharmacists()
    {
        $pharmacists = Staff::whereHas('user.roles', fn ($q) => $q->where('name', 'pharmacist'))
            ->get(['id', 'first_name', 'last_name'])
            ->map(fn ($s) => ['id' => $s->id, 'name' => trim($s->first_name . ' ' . $s->last_name)]);

        return response()->json(['pharmacists' => $pharmacists]);
    }

    /**
     * Issue stock from the store to the pharmacy dispensary.
     * Increments the dispensary's stock and records the issuance.
     */
    public function issue(Request $request)
    {
        $validated = $request->validate([
            'pharmacy_item_id' => 'required|exists:pharmacy_items,id',
            'quantity' => 'required|integer|min:1',
            'batch_number' => 'nullable|string|max:100',
            'expiry_date' => 'nullable|date|after:today',
            'received_by_staff_id' => 'nullable|exists:staff,id',
            'received_by_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $issuance = DB::transaction(function () use ($validated) {
            $item = PharmacyItem::lockForUpdate()->findOrFail($validated['pharmacy_item_id']);

            // Issuing into the dispensary tops up its available stock.
            $item->quantity_in_stock += $validated['quantity'];
            if (!empty($validated['batch_number'])) {
                $item->batch_number = $validated['batch_number'];
            }
            if (!empty($validated['expiry_date'])) {
                $item->expiry_date = $validated['expiry_date'];
            }
            $item->save();

            $receiverName = $validated['received_by_name'] ?? null;
            if (empty($receiverName) && !empty($validated['received_by_staff_id'])) {
                $staff = Staff::find($validated['received_by_staff_id']);
                $receiverName = $staff ? trim($staff->first_name . ' ' . $staff->last_name) : null;
            }

            return StockIssuance::create([
                'reference' => 'ISS-' . now()->format('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3))),
                'pharmacy_item_id' => $item->id,
                'quantity' => $validated['quantity'],
                'batch_number' => $validated['batch_number'] ?? null,
                'expiry_date' => $validated['expiry_date'] ?? null,
                'issued_by' => Auth::id(),
                'received_by_staff_id' => $validated['received_by_staff_id'] ?? null,
                'received_by_name' => $receiverName,
                'notes' => $validated['notes'] ?? null,
                'issued_at' => now(),
            ]);
        });

        return response()->json([
            'message' => 'Stock issued to pharmacy successfully.',
            'issuance' => $issuance->load(['item', 'issuer', 'receiver']),
        ], 201);
    }

    /**
     * Issuance ledger (history of drugs issued to the pharmacy).
     */
    public function issuances(Request $request)
    {
        $query = StockIssuance::with(['item:id,name,code,category', 'issuer:id,name', 'receiver:id,first_name,last_name'])
            ->latest('issued_at');

        if ($request->filled('search')) {
            $s = '%' . mb_strtolower(trim($request->search)) . '%';
            $query->where(function ($q) use ($s) {
                $q->whereRaw('LOWER(reference) LIKE ?', [$s])
                    ->orWhereHas('item', fn ($i) => $i->whereRaw('LOWER(name) LIKE ?', [$s]));
            });
        }

        $issuances = $query->paginate(20);

        return response()->json([
            'issuances' => $issuances->map(fn ($x) => [
                'id' => $x->id,
                'reference' => $x->reference,
                'item_name' => $x->item?->name,
                'category' => $x->item?->category,
                'quantity' => $x->quantity,
                'batch_number' => $x->batch_number,
                'expiry_date' => optional($x->expiry_date)->toDateString(),
                'issued_by' => $x->issuer?->name ?? 'System',
                'received_by' => $x->received_by_name ?? ($x->receiver ? trim($x->receiver->first_name . ' ' . $x->receiver->last_name) : 'Pharmacy'),
                'notes' => $x->notes,
                'issued_at' => $x->issued_at?->toDateTimeString(),
            ]),
            'pagination' => [
                'total' => $issuances->total(),
                'per_page' => $issuances->perPage(),
                'current_page' => $issuances->currentPage(),
                'last_page' => $issuances->lastPage(),
            ],
            'stats' => [
                'total_issuances' => StockIssuance::count(),
                'issued_today' => StockIssuance::whereDate('issued_at', today())->count(),
                'units_today' => (int) StockIssuance::whereDate('issued_at', today())->sum('quantity'),
            ],
        ]);
    }
}
