<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PharmacyItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Procurement: suppliers, purchase orders and goods-received notes (GRN) that
 * post received quantities back into pharmacy stock.
 */
class ProcurementController extends Controller
{
    // ── Suppliers ──────────────────────────────────────────────────────
    public function suppliers()
    {
        return response()->json(['suppliers' => Supplier::orderBy('name')->get()]);
    }

    public function storeSupplier(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'contact_person' => 'nullable|string|max:120',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:150',
            'address' => 'nullable|string|max:255',
        ]);

        $supplier = Supplier::create($validated);

        return response()->json(['message' => 'Supplier added.', 'supplier' => $supplier], 201);
    }

    // Pharmacy catalogue for PO line items.
    public function catalogueItems()
    {
        return response()->json([
            'items' => PharmacyItem::orderBy('name')->get(['id', 'name', 'code', 'price_per_unit', 'quantity_in_stock']),
        ]);
    }

    // ── Purchase orders ────────────────────────────────────────────────
    public function index()
    {
        $orders = PurchaseOrder::with('supplier:id,name')->withCount('items')->latest()->paginate(25);

        return response()->json([
            'orders' => $orders->map(fn ($o) => [
                'id' => $o->id,
                'po_number' => $o->po_number,
                'supplier' => $o->supplier?->name,
                'status' => $o->status,
                'total_amount' => $o->total_amount,
                'items' => $o->items_count,
                'expected_date' => $o->expected_date?->toDateString(),
                'created_at' => $o->created_at?->toDateString(),
            ]),
            'stats' => [
                'draft' => PurchaseOrder::where('status', 'draft')->count(),
                'approved' => PurchaseOrder::where('status', 'approved')->count(),
                'open_value' => (float) PurchaseOrder::whereIn('status', ['draft', 'approved', 'partially_received'])->sum('total_amount'),
                'suppliers' => Supplier::count(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'expected_date' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.pharmacy_item_id' => 'nullable|exists:pharmacy_items,id',
            'items.*.description' => 'required|string|max:200',
            'items.*.quantity_ordered' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        $order = DB::transaction(function () use ($validated) {
            $po = PurchaseOrder::create([
                'po_number' => 'PO-' . now()->format('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3))),
                'supplier_id' => $validated['supplier_id'],
                'status' => 'draft',
                'expected_date' => $validated['expected_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            $total = 0;
            foreach ($validated['items'] as $line) {
                $lineTotal = $line['quantity_ordered'] * $line['unit_cost'];
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'pharmacy_item_id' => $line['pharmacy_item_id'] ?? null,
                    'description' => $line['description'],
                    'quantity_ordered' => $line['quantity_ordered'],
                    'unit_cost' => $line['unit_cost'],
                    'line_total' => $lineTotal,
                ]);
                $total += $lineTotal;
            }
            $po->update(['total_amount' => $total]);

            return $po;
        });

        return response()->json(['message' => 'Purchase order created.', 'order' => $order->load('items')], 201);
    }

    public function show($id)
    {
        $po = PurchaseOrder::with(['supplier', 'items.pharmacyItem:id,name', 'creator:id,name'])->findOrFail($id);

        return response()->json([
            'order' => [
                'id' => $po->id,
                'po_number' => $po->po_number,
                'supplier' => $po->supplier?->name,
                'status' => $po->status,
                'total_amount' => $po->total_amount,
                'expected_date' => $po->expected_date?->toDateString(),
                'notes' => $po->notes,
                'created_by' => $po->creator?->name,
            ],
            'items' => $po->items->map(fn ($it) => [
                'id' => $it->id,
                'description' => $it->description,
                'pharmacy_item' => $it->pharmacyItem?->name,
                'quantity_ordered' => $it->quantity_ordered,
                'quantity_received' => $it->quantity_received,
                'unit_cost' => $it->unit_cost,
                'line_total' => $it->line_total,
            ]),
        ]);
    }

    public function approve($id)
    {
        $po = PurchaseOrder::findOrFail($id);
        if ($po->status !== 'draft') {
            return response()->json(['message' => 'Only draft orders can be approved.'], 422);
        }
        $po->update(['status' => 'approved', 'approved_by' => Auth::id(), 'approved_at' => now()]);

        return response()->json(['message' => 'Purchase order approved.', 'order' => $po]);
    }

    /**
     * Goods-received note: accept received quantities per line, post them into
     * pharmacy stock, and advance the PO status.
     */
    public function receive(Request $request, $id)
    {
        $po = PurchaseOrder::with('items')->findOrFail($id);

        if (!in_array($po->status, ['approved', 'partially_received'])) {
            return response()->json(['message' => 'Only approved orders can receive goods.'], 422);
        }

        $validated = $request->validate([
            'receipts' => 'required|array|min:1',
            'receipts.*.item_id' => 'required|integer',
            'receipts.*.quantity' => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($po, $validated) {
            $byId = $po->items->keyBy('id');

            foreach ($validated['receipts'] as $r) {
                $item = $byId->get($r['item_id']);
                if (!$item || $r['quantity'] <= 0) {
                    continue;
                }

                // Do not receive beyond the outstanding quantity.
                $outstanding = $item->quantity_ordered - $item->quantity_received;
                $accept = min($r['quantity'], max($outstanding, 0));
                if ($accept <= 0) {
                    continue;
                }

                $item->quantity_received += $accept;
                $item->save();

                // Post into pharmacy stock when the line is linked to an item.
                if ($item->pharmacy_item_id) {
                    PharmacyItem::where('id', $item->pharmacy_item_id)
                        ->increment('quantity_in_stock', $accept);
                }
            }

            $po->refresh()->load('items');
            $fullyReceived = $po->items->every(fn ($i) => $i->quantity_received >= $i->quantity_ordered);
            $anyReceived = $po->items->contains(fn ($i) => $i->quantity_received > 0);

            $po->status = $fullyReceived ? 'received' : ($anyReceived ? 'partially_received' : $po->status);
            if ($fullyReceived) {
                $po->received_at = now();
            }
            $po->save();
        });

        return response()->json(['message' => 'Goods received and stock updated.', 'order' => $po->fresh()->load('items')]);
    }
}
