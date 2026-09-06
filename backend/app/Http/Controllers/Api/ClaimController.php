<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Claim;
use App\Models\ClaimItem;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * NHIS / HMO claims: batch covered (NHIS-eligible) invoices into claims and
 * track them through submitted -> paid / rejected.
 */
class ClaimController extends Controller
{
    /**
     * NHIS-eligible invoices not yet attached to a claim.
     */
    public function eligible()
    {
        $claimedInvoiceIds = ClaimItem::pluck('invoice_id');

        $invoices = Invoice::with('patient:id,first_name,last_name,immigration_service_number,sponsor_service_number')
            ->whereNotIn('id', $claimedInvoiceIds)
            ->whereHas('patient', function ($q) {
                $q->whereNotNull('sponsor_service_number')
                  ->orWhere(function ($q2) {
                      $q2->whereNotNull('immigration_service_number')
                         ->where('immigration_service_number', 'not like', '%/PAT/%');
                  });
            })
            ->latest('created_at')
            ->limit(200)
            ->get()
            ->map(fn ($inv) => [
                'id' => $inv->id,
                'patient' => $inv->patient?->first_name . ' ' . $inv->patient?->last_name,
                'hospital_code' => $inv->patient?->immigration_service_number,
                'amount' => (float) $inv->total_amount,
                'status' => $inv->status,
                'date' => $inv->created_at?->toDateString(),
            ]);

        return response()->json(['invoices' => $invoices]);
    }

    public function index()
    {
        $claims = Claim::withCount('items')->with('creator:id,name')->latest()->paginate(20);

        return response()->json([
            'claims' => $claims->map(fn ($c) => [
                'id' => $c->id,
                'claim_number' => $c->claim_number,
                'provider' => $c->provider,
                'status' => $c->status,
                'total_amount' => (float) $c->total_amount,
                'items' => $c->items_count,
                'period' => $c->period_start && $c->period_end ? ($c->period_start->toDateString() . ' → ' . $c->period_end->toDateString()) : null,
                'created_by' => $c->creator?->name,
                'submitted_at' => $c->submitted_at?->toDateString(),
                'settled_at' => $c->settled_at?->toDateString(),
                'created_at' => $c->created_at?->toDateString(),
            ]),
            'pagination' => [
                'total' => $claims->total(), 'per_page' => $claims->perPage(),
                'current_page' => $claims->currentPage(), 'last_page' => $claims->lastPage(),
            ],
            'stats' => [
                'draft' => Claim::where('status', 'draft')->count(),
                'submitted' => Claim::where('status', 'submitted')->count(),
                'paid_value' => (float) Claim::where('status', 'paid')->sum('total_amount'),
                'outstanding_value' => (float) Claim::whereIn('status', ['draft', 'submitted'])->sum('total_amount'),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'provider' => 'required|string|max:100',
            'invoice_ids' => 'required|array|min:1',
            'invoice_ids.*' => 'integer|exists:invoices,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Ignore any invoice already claimed.
        $alreadyClaimed = ClaimItem::whereIn('invoice_id', $validated['invoice_ids'])->pluck('invoice_id')->all();
        $invoiceIds = array_values(array_diff($validated['invoice_ids'], $alreadyClaimed));
        if (empty($invoiceIds)) {
            return response()->json(['message' => 'All selected invoices are already claimed.'], 422);
        }

        $claim = DB::transaction(function () use ($invoiceIds, $validated) {
            $invoices = Invoice::whereIn('id', $invoiceIds)->get();
            $total = 0;
            $dates = $invoices->pluck('created_at');

            $claim = Claim::create([
                'claim_number' => 'CLM-' . now()->format('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3))),
                'provider' => $validated['provider'],
                'period_start' => $dates->min()?->toDateString(),
                'period_end' => $dates->max()?->toDateString(),
                'status' => 'draft',
                'notes' => $validated['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            foreach ($invoices as $inv) {
                ClaimItem::create([
                    'claim_id' => $claim->id,
                    'invoice_id' => $inv->id,
                    'patient_id' => $inv->patient_id,
                    'amount' => $inv->total_amount,
                    'description' => 'Invoice #' . $inv->id,
                ]);
                $total += (float) $inv->total_amount;
            }
            $claim->update(['total_amount' => $total]);

            return $claim;
        });

        return response()->json(['message' => 'Claim created.', 'claim' => $claim->load('items')], 201);
    }

    public function show($id)
    {
        $claim = Claim::with(['items.patient:id,first_name,last_name,immigration_service_number', 'creator:id,name'])->findOrFail($id);

        return response()->json([
            'claim' => $claim,
            'items' => $claim->items->map(fn ($it) => [
                'invoice_id' => $it->invoice_id,
                'patient' => $it->patient?->first_name . ' ' . $it->patient?->last_name,
                'hospital_code' => $it->patient?->immigration_service_number,
                'amount' => (float) $it->amount,
            ]),
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:draft,submitted,paid,rejected',
        ]);

        $claim = Claim::findOrFail($id);
        $claim->status = $validated['status'];
        if ($validated['status'] === 'submitted') $claim->submitted_at = now();
        if (in_array($validated['status'], ['paid', 'rejected'])) $claim->settled_at = now();
        $claim->save();

        return response()->json(['message' => 'Claim status updated.', 'claim' => $claim]);
    }
}
