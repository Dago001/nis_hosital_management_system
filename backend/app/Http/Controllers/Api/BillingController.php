<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class BillingController extends Controller
{
    public function getPendingInvoices()
    {
        $invoices = Invoice::with(['patient', 'items'])
            ->whereIn('status', ['unpaid', 'partially_paid'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['invoices' => $invoices]);
    }

    public function collectPayment(Request $request, int $invoiceId)
    {
        $invoice = Invoice::find($invoiceId);
        if (!$invoice) {
            return response()->json(['message' => 'Invoice not found.'], 404);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|string|in:Cash,POS,Bank Transfer,Insurance',
            'transaction_reference' => 'nullable|string'
        ]);

        $cashier = Auth::user()->staff;
        $cashierId = $cashier ? $cashier->id : null;

        DB::transaction(function () use ($invoice, $validated, $cashierId) {
            // 1. Create the payment record
            Payment::create([
                'invoice_id' => $invoice->id,
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'transaction_reference' => $validated['transaction_reference'] ?? null,
                'cashier_id' => $cashierId
            ]);

            // 2. Update the invoice paid amount
            $invoice->paid_amount = (float)$invoice->paid_amount + (float)$validated['amount'];
            
            // Calculate final balance
            $outstanding = (float)$invoice->total_amount - (float)$invoice->discount_amount - (float)$invoice->paid_amount;
            
            if ($outstanding <= 0) {
                $invoice->status = 'paid';
            } else {
                $invoice->status = 'partially_paid';
            }
            
            $invoice->save();
        });

        return response()->json([
            'message' => 'Payment received and recorded successfully.',
            'invoice' => $invoice->load('payments')
        ]);
    }

    public function showInvoice(int $id)
    {
        $invoice = Invoice::with(['patient', 'items', 'payments.cashier'])->find($id);
        if (!$invoice) {
            return response()->json(['message' => 'Invoice not found.'], 404);
        }

        return response()->json(['invoice' => $invoice]);
    }
}
