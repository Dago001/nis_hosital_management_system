<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PharmacyItem;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PharmacyController extends Controller
{
    /**
     * Get Pharmacy Inventory
     */
    public function getInventory(Request $request)
    {
        $search = $request->query('search', '');
        
        $query = PharmacyItem::query();
        
        if (!empty($search)) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('generic_name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
        }

        $items = $query->orderBy('name', 'asc')->get();

        return response()->json([
            'inventory' => $items
        ]);
    }

    /**
     * Add new Pharmacy Item
     */
    public function addInventory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'generic_name' => 'nullable|string|max:255',
            'code' => 'required|string|unique:pharmacy_items,code',
            'category' => 'required|string',
            'batch_number' => 'nullable|string',
            'expiry_date' => 'nullable|date',
            'quantity_in_stock' => 'required|integer|min:0',
            'reorder_level' => 'required|integer|min:0',
            'price_per_unit' => 'required|numeric|min:0',
        ]);

        $item = PharmacyItem::create($validated);

        return response()->json([
            'message' => 'Pharmacy item added successfully',
            'item' => $item
        ], 201);
    }

    /**
     * Update existing Pharmacy Item
     */
    public function updateInventory(Request $request, $id)
    {
        $item = PharmacyItem::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'generic_name' => 'nullable|string|max:255',
            'code' => 'required|string|unique:pharmacy_items,code,' . $id,
            'category' => 'required|string',
            'batch_number' => 'nullable|string',
            'expiry_date' => 'nullable|date',
            'quantity_in_stock' => 'required|integer|min:0',
            'reorder_level' => 'required|integer|min:0',
            'price_per_unit' => 'required|numeric|min:0',
        ]);

        $item->update($validated);

        return response()->json([
            'message' => 'Pharmacy item updated successfully',
            'item' => $item
        ]);
    }

    /**
     * Get all prescriptions for dispensary
     */
    public function getPrescriptions(Request $request)
    {
        $status = $request->query('status', ''); // 'pending' or 'dispensed'

        $query = Prescription::with(['patient', 'doctor', 'items']);

        if (!empty($status)) {
            $query->where('status', $status);
        }

        $prescriptions = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'prescriptions' => $prescriptions
        ]);
    }

    /**
     * Cost a prescription and forward to Cashier (generates an Invoice)
     */
    public function costPrescription(Request $request, $id)
    {
        $prescription = Prescription::with(['items', 'patient', 'visit'])->findOrFail($id);

        if (in_array($prescription->status, ['costed', 'dispensed'])) {
            return response()->json(['message' => 'Prescription is already costed or dispensed'], 400);
        }

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.prescription_item_id' => 'required|exists:prescription_items,id',
            'items.*.pharmacy_item_id' => 'required|exists:pharmacy_items,id',
            'items.*.price_per_unit' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            $totalAmount = 0;
            $invoiceItemsData = [];

            foreach ($validated['items'] as $itemData) {
                $pItem = PrescriptionItem::findOrFail($itemData['prescription_item_id']);
                $pharmacyItem = PharmacyItem::findOrFail($itemData['pharmacy_item_id']);
                
                $itemTotal = (float)$itemData['price_per_unit'] * (int)$itemData['quantity'];
                $totalAmount += $itemTotal;

                $invoiceItemsData[] = [
                    'item_name' => "Medication: " . $pharmacyItem->name . " (" . $pItem->dosage . ")",
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['price_per_unit'],
                    'total_price' => $itemTotal,
                ];

                $pItem->update([
                    'quantity_dispensed' => $itemData['quantity']
                ]);
            }

            // Create the Invoice
            $invoice = Invoice::create([
                'patient_id' => $prescription->patient_id,
                'visit_id' => $prescription->visit_id,
                'total_amount' => $totalAmount,
                'discount_amount' => 0.00,
                'paid_amount' => 0.00,
                'status' => 'unpaid'
            ]);

            // Create the Invoice Items
            foreach ($invoiceItemsData as $invItem) {
                $invItem['invoice_id'] = $invoice->id;
                InvoiceItem::create($invItem);
            }

            // Update the Prescription status & link the invoice
            $prescription->update([
                'status' => 'costed',
                'invoice_id' => $invoice->id
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Prescription costed successfully and forwarded to cashier.',
                'invoice' => $invoice,
                'prescription' => $prescription
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Dispense a prescription
     */
    public function dispensePrescription(Request $request, $id)
    {
        $prescription = Prescription::with(['items', 'invoice'])->findOrFail($id);

        if ($prescription->status === 'dispensed') {
            return response()->json(['message' => 'Prescription is already dispensed'], 400);
        }

        // Verify payment status!
        if ($prescription->invoice_id && (!$prescription->invoice || $prescription->invoice->status !== 'paid')) {
            return response()->json(['message' => 'Prescription cannot be dispensed until payment is verified by the cashier.'], 400);
        }

        // We expect an array of items to dispense with their corresponding inventory item IDs
        // format: [ { prescription_item_id: 1, pharmacy_item_id: 5, quantity: 10 } ]
        $dispensedItems = $request->input('dispensed_items', []);

        DB::beginTransaction();

        try {
            foreach ($dispensedItems as $dItem) {
                $pItem = PrescriptionItem::findOrFail($dItem['prescription_item_id']);
                
                if ($pItem->prescription_id !== $prescription->id) {
                    throw new \Exception("Invalid prescription item ID.");
                }

                $pharmacyItem = PharmacyItem::findOrFail($dItem['pharmacy_item_id']);
                $qtyToDispense = (int) $dItem['quantity'];

                if ($pharmacyItem->quantity_in_stock < $qtyToDispense) {
                    throw new \Exception("Insufficient stock for {$pharmacyItem->name}. Available: {$pharmacyItem->quantity_in_stock}, Requested: {$qtyToDispense}");
                }

                // Deduct stock
                $pharmacyItem->quantity_in_stock -= $qtyToDispense;
                $pharmacyItem->save();

                // Update prescription item
                $pItem->quantity_dispensed = $qtyToDispense;
                $pItem->status = 'dispensed';
                $pItem->save();
            }

            // Mark the prescription as fully dispensed
            $prescription->status = 'dispensed';
            $prescription->save();

            DB::commit();

            return response()->json([
                'message' => 'Prescription dispensed successfully and inventory updated.'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
