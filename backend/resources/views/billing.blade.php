@extends('layouts.app')

@section('title', 'Billing & Cashier - NIS Medical Services Portal')

@section('content')
<div class="space-y-6">
    <!-- Title -->
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-white flex items-center gap-2">
                <i data-lucide="credit-card" class="text-emerald-600"></i> Patient Invoices & Cashier Desk
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Cashier portal to receive payments (Cash, POS, Transfer, Insurance) and issue governmental receipts</p>
        </div>
        <div class="flex items-center gap-2">
            <select id="invoice-status-filter" onchange="loadInvoices()" class="text-xs px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                <option value="pending" selected>Pending Invoices</option>
                <option value="paid">Paid Invoices</option>
            </select>
        </div>
    </div>

    <!-- Invoices List Card -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-200 border-b border-slate-100 dark:border-slate-800">
                    <tr>
                        <th class="py-3.5 px-6 font-bold">Invoice Date</th>
                        <th class="py-3.5 px-6 font-bold">Patient Name</th>
                        <th class="py-3.5 px-6 font-bold">Hospital Code</th>
                        <th class="py-3.5 px-6 font-bold">Total Bill</th>
                        <th class="py-3.5 px-6 font-bold">Amount Paid</th>
                        <th class="py-3.5 px-6 font-bold">Status</th>
                        <th class="py-3.5 px-6 font-bold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="invoices-table-body" class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-355">
                    <tr>
                        <td colSpan="7" class="py-8 text-center text-slate-800 dark:text-slate-200 text-sm">
                            <div class="w-6 h-6 border-2 border-emerald-600 border-t-transparent rounded-full animate-spin mx-auto"></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Process Payment Modal -->
<div id="payment-modal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 w-full max-w-lg shadow-2xl relative my-8">
        <h3 class="text-base font-bold text-slate-800 dark:text-white mb-2 flex items-center gap-2">
            <i data-lucide="credit-card" class="text-emerald-500"></i> Capture Payment Collection
        </h3>
        <p class="text-[10px] text-slate-500 mb-4" id="pay-patient-name"></p>
        
        <form id="payment-form" onsubmit="handlePaymentSubmit(event)" class="space-y-4">
            <!-- Invoice breakdown summary -->
            <div class="bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-850 p-4 rounded-xl space-y-2 text-xs text-slate-800 dark:text-slate-200">
                <h4 class="font-bold text-slate-900 dark:text-white mb-1 uppercase tracking-wider text-[10px]">Invoice Breakdown</h4>
                <div class="divide-y divide-slate-100 dark:divide-slate-800" id="invoice-items-list">
                    <!-- Items injected -->
                </div>
                <div class="pt-2 flex justify-between font-bold text-slate-900 dark:text-white border-t border-slate-200">
                    <span>Outstanding Balance:</span>
                    <span id="invoice-total-balance">₦0.00</span>
                </div>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1">Amount to Collect (₦) *</label>
                <input type="number" step="0.01" id="pay-amount" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1">Payment Method *</label>
                    <select id="pay-method" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        <option>Cash</option>
                        <option>POS Card</option>
                        <option>Bank Transfer</option>
                        <option>Insurance Scheme</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1">Transaction Ref / ID</label>
                    <input type="text" id="pay-ref" placeholder="e.g. TXN-998877" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800/80">
                <button type="button" onclick="closePaymentModal()" class="px-4 py-2 text-xs font-semibold text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition">Cancel</button>
                <button type="submit" id="submit-btn" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 text-xs font-bold rounded-xl transition shadow-md">Complete Payment</button>
            </div>
        </form>
    </div>
</div>

<!-- Receipt Printing Modal -->
<div id="receipt-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 w-full max-w-lg shadow-2xl relative">
        <!-- Receipt Content Printable Area -->
        <div id="printable-receipt-area" class="bg-white text-slate-900 p-6 border border-slate-100 rounded-2xl space-y-4 font-sans">
            <!-- Receipt Header -->
            <div class="text-center space-y-1 pb-3 border-b border-slate-200">
                <h2 class="text-base font-black uppercase tracking-wider text-slate-800">Nigeria Immigration Service</h2>
                <h3 class="text-xs font-bold text-slate-600">Hospital Medical Services Portal, Abuja</h3>
                <p class="text-[9px] text-slate-400">Official Electronic Transaction Voucher</p>
            </div>
            <!-- Patient / Invoice Metadata -->
            <div class="grid grid-cols-2 gap-2 text-[10px] text-slate-600">
                <div>
                    <span class="font-bold block text-slate-450 uppercase text-[8px]">Patient Name:</span>
                    <span id="rec-patient-name" class="text-slate-800 font-semibold"></span>
                </div>
                <div>
                    <span class="font-bold block text-slate-450 uppercase text-[8px]">Hospital Code:</span>
                    <span id="rec-patient-code" class="text-slate-850 font-semibold font-mono"></span>
                </div>
                <div>
                    <span class="font-bold block text-slate-450 uppercase text-[8px]">Invoice ID / Ref:</span>
                    <span id="rec-invoice-ref" class="text-slate-850 font-semibold font-mono"></span>
                </div>
                <div>
                    <span class="font-bold block text-slate-450 uppercase text-[8px]">Date Issued:</span>
                    <span id="rec-date" class="text-slate-800 font-semibold"></span>
                </div>
            </div>
            <!-- Items table -->
            <div class="border border-slate-200 rounded-xl overflow-hidden mt-3">
                <table class="w-full text-left text-[10px]">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-700">
                        <tr>
                            <th class="p-2 font-bold">Item Description</th>
                            <th class="p-2 font-bold text-right">Price</th>
                        </tr>
                    </thead>
                    <tbody id="rec-items-body" class="divide-y divide-slate-100 text-slate-700">
                        <!-- Injected -->
                    </tbody>
                </table>
            </div>
            <!-- Financial breakdown -->
            <div class="space-y-1 text-[10px] border-t border-slate-200 pt-3 text-slate-700">
                <div class="flex justify-between">
                    <span>Subtotal:</span>
                    <span id="rec-subtotal" class="font-bold">₦0.00</span>
                </div>
                <div class="flex justify-between text-emerald-600">
                    <span>NHIS Co-pay Coverage (15%):</span>
                    <span id="rec-discount" class="font-bold">-₦0.00</span>
                </div>
                <div class="flex justify-between font-black text-slate-900 border-t border-slate-100 pt-1 text-xs">
                    <span>Total Amount Billed (85%):</span>
                    <span id="rec-total" class="text-slate-950">₦0.00</span>
                </div>
                <div class="flex justify-between text-blue-600 font-bold">
                    <span>Amount Paid Today:</span>
                    <span id="rec-paid">₦0.00</span>
                </div>
                <div class="flex justify-between text-red-600 border-t border-slate-100 pt-1 font-bold">
                    <span>Balance Due:</span>
                    <span id="rec-balance">₦0.00</span>
                </div>
            </div>
            <!-- Footer declaration -->
            <div class="text-center text-[8px] text-slate-400 pt-2 border-t border-slate-100">
                <p>Thank you for your transaction. Keep this receipt safe for medical claim validations.</p>
                <p class="font-mono mt-1" id="rec-stamp">Verified by NIS HMS System Administration</p>
            </div>
        </div>
        
        <!-- Print Modal Actions -->
        <div class="flex gap-3 mt-5">
            <button onclick="closeReceiptModal()" class="flex-grow px-4 py-2 text-xs font-semibold border border-slate-200 dark:border-slate-700 rounded-xl text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition">Close</button>
            <button onclick="printReceiptVoucher()" class="flex-grow bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-4 py-2 rounded-xl transition flex items-center justify-center gap-1">
                Print Receipt
            </button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let activeInvoices = [];
    let selectedInvoice = null;

    async function loadInvoices() {
        const tbody = document.getElementById('invoices-table-body');
        const status = document.getElementById('invoice-status-filter').value;
        try {
            const res = await api.get(`/billing/invoices/pending?status=${status}`);
            activeInvoices = res.invoices || [];

            if (activeInvoices.length > 0) {
                tbody.innerHTML = activeInvoices.map(inv => {
                    let statusClass = 'bg-slate-100 text-slate-700';
                    if (inv.status === 'paid') statusClass = 'bg-emerald-500/10 text-emerald-600 border border-emerald-500/20';
                    else if (inv.status === 'unpaid') statusClass = 'bg-red-500/10 text-red-500 border border-red-500/20';
                    else if (inv.status === 'partially_paid') statusClass = 'bg-amber-500/10 text-amber-600 border border-amber-500/20';

                    const date = new Date(inv.created_at).toLocaleDateString();
                    
                    let actions = '';
                    if (inv.status !== 'paid') {
                        actions += `
                            <button onclick="openPaymentModal(${inv.id})" class="bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] font-bold px-2.5 py-1.5 rounded-xl transition cursor-pointer mr-1">
                                Collect
                            </button>
                        `;
                    }
                    actions += `
                        <button onclick="openReceiptModal(${inv.id})" class="bg-slate-800 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 text-[10px] font-bold px-2.5 py-1.5 rounded-xl transition cursor-pointer">
                            Receipt
                        </button>
                    `;

                    return `
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/20 transition">
                            <td class="py-3.5 px-6 text-slate-800 dark:text-slate-200">${date}</td>
                            <td class="py-3.5 px-6 font-bold text-slate-900 dark:text-white">${inv.patient.first_name} ${inv.patient.last_name}</td>
                            <td class="py-3.5 px-6 font-mono font-bold text-slate-700 dark:text-slate-350">${inv.patient.immigration_service_number}</td>
                            <td class="py-3.5 px-6 font-bold text-slate-800 dark:text-slate-200 text-right">₦${Number(inv.total_amount).toLocaleString()}</td>
                            <td class="py-3.5 px-6 text-slate-600 dark:text-slate-350 text-right">₦${Number(inv.paid_amount).toLocaleString()}</td>
                            <td class="py-3.5 px-6">
                                <span class="px-2.5 py-0.5 text-[9px] font-bold rounded-full uppercase ${statusClass}">
                                    ${inv.status.replace('_', ' ')}
                                </span>
                            </td>
                            <td class="py-3.5 px-6 text-right">${actions}</td>
                        </tr>
                    `;
                }).join('');
            } else {
                tbody.innerHTML = `<tr><td colspan="7" class="py-8 text-center text-slate-500 text-xs">No invoices found for this filter.</td></tr>`;
            }
        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="7" class="py-8 text-center text-red-500 text-xs">Failed to load invoices.</td></tr>`;
        }
    }

    async function openPaymentModal(id) {
        try {
            const res = await api.get(`/billing/invoices/${id}`);
            selectedInvoice = res.invoice;

            document.getElementById('pay-patient-name').innerText = `Patient: ${selectedInvoice.patient.first_name} ${selectedInvoice.patient.last_name} | Code: ${selectedInvoice.patient.immigration_service_number}`;

            // Populate invoice breakdown items
            const list = document.getElementById('invoice-items-list');
            list.innerHTML = selectedInvoice.items.map(item => `
                <div class="py-2 flex justify-between">
                    <span>${item.item_name} (x${item.quantity})</span>
                    <span class="font-bold">₦${Number(item.total_price).toLocaleString()}</span>
                </div>
            `).join('');

            const outstanding = selectedInvoice.total_amount - selectedInvoice.discount_amount - selectedInvoice.paid_amount;
            document.getElementById('invoice-total-balance').innerText = `₦${Number(outstanding).toLocaleString()}`;
            document.getElementById('pay-amount').value = outstanding;
            document.getElementById('pay-method').value = 'Cash';
            document.getElementById('pay-ref').value = '';

            document.getElementById('payment-modal').classList.remove('hidden');
        } catch (err) {
            alert('Failed to load invoice items details.');
        }
    }

    function closePaymentModal() {
        document.getElementById('payment-modal').classList.add('hidden');
    }

    async function handlePaymentSubmit(e) {
        e.preventDefault();
        if (!selectedInvoice) return;

        const submitBtn = document.getElementById('submit-btn');
        submitBtn.disabled = true;
        submitBtn.innerText = 'Processing...';

        const payload = {
            amount: parseFloat(document.getElementById('pay-amount').value),
            payment_method: document.getElementById('pay-method').value,
            transaction_reference: document.getElementById('pay-ref').value || null
        };

        try {
            await api.post(`/billing/invoices/${selectedInvoice.id}/pay`, payload);
            alert('Payment recorded successfully! Governmental receipt generated.');
            closePaymentModal();
            loadInvoices();
            openReceiptModal(selectedInvoice.id);
        } catch (err) {
            alert(err.message || 'Payment collection failed.');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerText = 'Complete Payment';
        }
    }

    async function openReceiptModal(id) {
        try {
            const res = await api.get(`/billing/invoices/${id}`);
            const inv = res.invoice;

            document.getElementById('rec-patient-name').innerText = `${inv.patient.first_name} ${inv.patient.last_name}`;
            document.getElementById('rec-patient-code').innerText = inv.patient.immigration_service_number;
            document.getElementById('rec-invoice-ref').innerText = `INV-${inv.id.toString().padStart(6, '0')}`;
            document.getElementById('rec-date').innerText = new Date(inv.created_at).toLocaleString();

            const itemsBody = document.getElementById('rec-items-body');
            itemsBody.innerHTML = inv.items.map(item => `
                <tr>
                    <td class="p-2">${item.item_name} (x${item.quantity})</td>
                    <td class="p-2 text-right">₦${Number(item.total_price).toLocaleString()}</td>
                </tr>
            `).join('');

            const outstanding = inv.total_amount - inv.discount_amount - inv.paid_amount;

            document.getElementById('rec-subtotal').innerText = `₦${Number(inv.total_amount).toLocaleString()}`;
            document.getElementById('rec-discount').innerText = `-₦${Number(inv.discount_amount).toLocaleString()}`;
            document.getElementById('rec-total').innerText = `₦${Number(inv.total_amount - inv.discount_amount).toLocaleString()}`;
            document.getElementById('rec-paid').innerText = `₦${Number(inv.paid_amount).toLocaleString()}`;
            document.getElementById('rec-balance').innerText = `₦${Number(outstanding).toLocaleString()}`;
            
            document.getElementById('rec-stamp').innerText = `Verified Stamp: NIS-HMS-${inv.id}-${new Date(inv.created_at).getTime()}`;

            document.getElementById('receipt-modal').classList.remove('hidden');
        } catch (err) {
            alert('Failed to generate printable receipt: ' + err.message);
        }
    }

    function closeReceiptModal() {
        document.getElementById('receipt-modal').classList.add('hidden');
    }

    function printReceiptVoucher() {
        window.print();
    }

    document.addEventListener('DOMContentLoaded', () => {
        loadInvoices();
    });
</script>

<style>
    @media print {
        header, footer, aside, nav, button, select, h1, p, .space-y-6 > div:not(#receipt-modal), #payment-modal {
            display: none !important;
        }
        body {
            background: white !important;
            color: black !important;
        }
        #receipt-modal {
            position: absolute !important;
            left: 0 !important;
            top: 0 !important;
            width: 100% !important;
            height: auto !important;
            display: block !important;
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        #receipt-modal > div {
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            margin: 0 !important;
            max-width: 100% !important;
        }
        #printable-receipt-area {
            border: none !important;
            padding: 0 !important;
            width: 100% !important;
        }
        button {
            display: none !important;
        }
    }
</style>
@endsection
