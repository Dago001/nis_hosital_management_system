@extends('layouts.app')

@section('title', 'Procurement | NIS Medical Services Portal')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-xl font-bold text-slate-800 dark:text-white flex items-center gap-2">
            <i data-lucide="truck" class="text-emerald-600 shrink-0"></i> Procurement &amp; Supply
        </h1>
        <p class="text-xs text-slate-500 dark:text-slate-400">Suppliers, purchase orders and goods-received notes that post directly into pharmacy stock.</p>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach([['id'=>'p-draft','l'=>'Draft POs','c'=>'slate'],['id'=>'p-approved','l'=>'Approved','c'=>'blue'],['id'=>'p-open','l'=>'Open Value (₦)','c'=>'amber'],['id'=>'p-suppliers','l'=>'Suppliers','c'=>'emerald']] as $s)
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4">
            <div class="text-xl font-black text-{{ $s['c'] }}-600" id="{{ $s['id'] }}">0</div>
            <div class="text-[10px] text-slate-500 dark:text-slate-400 font-semibold uppercase tracking-wide">{{ $s['l'] }}</div>
        </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Suppliers -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
            <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-800 dark:text-white">Suppliers</h3>
                <button onclick="openSupplierModal()" class="text-emerald-600 hover:underline text-xs font-bold flex items-center gap-1"><i data-lucide="plus" class="w-3.5 h-3.5"></i> Add</button>
            </div>
            <div class="max-h-80 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800" id="suppliers-list">
                <p class="p-4 text-xs text-slate-400">Loading…</p>
            </div>
        </div>

        <!-- Purchase orders -->
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
            <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-800 dark:text-white">Purchase Orders</h3>
                <button onclick="openPoModal()" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-3 py-2 rounded-xl flex items-center gap-1"><i data-lucide="plus" class="w-3.5 h-3.5"></i> New PO</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-950 text-slate-700 dark:text-slate-300"><tr>
                        <th class="py-2.5 px-4 font-bold">PO No.</th><th class="py-2.5 px-4 font-bold">Supplier</th>
                        <th class="py-2.5 px-4 font-bold">Total</th><th class="py-2.5 px-4 font-bold">Status</th>
                        <th class="py-2.5 px-4 font-bold text-right">Action</th>
                    </tr></thead>
                    <tbody id="po-body" class="divide-y divide-slate-100 dark:divide-slate-800"><tr><td colspan="5" class="py-6 text-center text-slate-400">Loading…</td></tr></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Supplier modal -->
<div id="supplier-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 w-full max-w-md shadow-2xl">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-bold text-slate-800 dark:text-white">Add Supplier</h3>
            <button onclick="closeSupplierModal()" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form onsubmit="submitSupplier(event)" class="space-y-3">
            <input id="s-name" required placeholder="Supplier name *" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs">
            <input id="s-contact" placeholder="Contact person" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs">
            <div class="grid grid-cols-2 gap-3">
                <input id="s-phone" placeholder="Phone" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs">
                <input id="s-email" placeholder="Email" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs">
            </div>
            <input id="s-address" placeholder="Address" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs">
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeSupplierModal()" class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl">Cancel</button>
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 text-xs font-bold rounded-xl">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- New PO modal -->
<div id="po-modal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 w-full max-w-2xl shadow-2xl my-8">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-bold text-slate-800 dark:text-white">New Purchase Order</h3>
            <button onclick="closePoModal()" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form onsubmit="submitPo(event)" class="space-y-3">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider mb-1 text-slate-700 dark:text-slate-300">Supplier *</label>
                    <select id="po-supplier" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs"></select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider mb-1 text-slate-700 dark:text-slate-300">Expected Date</label>
                    <input id="po-expected" type="date" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs">
                </div>
            </div>

            <div class="border border-slate-200 dark:border-slate-800 rounded-xl p-3">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Line Items</span>
                    <button type="button" onclick="addPoLine()" class="text-emerald-600 hover:underline text-xs font-bold">+ Add line</button>
                </div>
                <div id="po-lines" class="space-y-2"></div>
                <div class="text-right mt-2 text-xs font-bold text-slate-700 dark:text-slate-200">Total: ₦<span id="po-total">0</span></div>
            </div>

            <textarea id="po-notes" rows="2" placeholder="Notes" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs"></textarea>
            <div id="po-alert" class="hidden text-xs font-semibold rounded-xl px-3 py-2 bg-red-50 text-red-700"></div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closePoModal()" class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl">Cancel</button>
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 text-xs font-bold rounded-xl">Create PO</button>
            </div>
        </form>
    </div>
</div>

<!-- Receive (GRN) modal -->
<div id="grn-modal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 w-full max-w-lg shadow-2xl my-8">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-bold text-slate-800 dark:text-white">Goods Received — <span id="grn-po-no"></span></h3>
            <button onclick="closeGrnModal()" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <p class="text-[10px] text-slate-500 mb-3">Enter quantities received now. Linked items post straight into pharmacy stock.</p>
        <div id="grn-lines" class="space-y-2 max-h-72 overflow-y-auto"></div>
        <div class="flex justify-end gap-3 pt-4">
            <button type="button" onclick="closeGrnModal()" class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl">Cancel</button>
            <button type="button" onclick="submitGrn()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 text-xs font-bold rounded-xl">Post Receipt</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let suppliers = [], catalogue = [], grnOrderId = null;
    const stCls = {draft:'bg-slate-200 text-slate-700',approved:'bg-blue-100 text-blue-700',partially_received:'bg-amber-100 text-amber-700',received:'bg-emerald-100 text-emerald-700',cancelled:'bg-red-100 text-red-700'};

    async function loadSuppliers() {
        try {
            const res = await api.get('/procurement/suppliers');
            suppliers = res.suppliers || [];
            document.getElementById('suppliers-list').innerHTML = suppliers.length ? suppliers.map(s => `
                <div class="p-3 text-xs">
                    <b class="text-slate-800 dark:text-white block">${s.name}</b>
                    <span class="text-slate-500">${s.contact_person || '—'} ${s.phone ? '· '+s.phone : ''}</span>
                </div>`).join('') : '<p class="p-4 text-xs text-slate-400">No suppliers yet.</p>';
            const sel = document.getElementById('po-supplier');
            sel.innerHTML = suppliers.map(s => `<option value="${s.id}">${s.name}</option>`).join('');
        } catch (e) { document.getElementById('suppliers-list').innerHTML = '<p class="p-4 text-xs text-red-500">Failed to load.</p>'; }
    }

    async function loadCatalogue() {
        try { const res = await api.get('/procurement/catalogue-items'); catalogue = res.items || []; } catch (e) { catalogue = []; }
    }

    async function loadOrders() {
        const body = document.getElementById('po-body');
        try {
            const res = await api.get('/procurement/orders');
            document.getElementById('p-draft').innerText = res.stats.draft;
            document.getElementById('p-approved').innerText = res.stats.approved;
            document.getElementById('p-open').innerText = '₦' + Number(res.stats.open_value).toLocaleString();
            document.getElementById('p-suppliers').innerText = res.stats.suppliers;
            const list = res.orders || [];
            body.innerHTML = list.length ? list.map(o => `
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30">
                    <td class="py-2.5 px-4 font-mono font-bold text-slate-800 dark:text-white">${o.po_number}</td>
                    <td class="py-2.5 px-4">${o.supplier || '—'}</td>
                    <td class="py-2.5 px-4 font-bold">₦${Number(o.total_amount).toLocaleString()}</td>
                    <td class="py-2.5 px-4"><span class="px-2 py-0.5 rounded text-[10px] font-bold ${stCls[o.status]||''}">${o.status.replace('_',' ')}</span></td>
                    <td class="py-2.5 px-4 text-right">
                        ${o.status==='draft' ? `<button onclick="approvePo(${o.id})" class="text-blue-600 hover:underline font-bold">Approve</button>` : ''}
                        ${['approved','partially_received'].includes(o.status) ? `<button onclick="openGrn(${o.id})" class="text-emerald-600 hover:underline font-bold">Receive</button>` : ''}
                        ${['received','cancelled'].includes(o.status) ? '<span class="text-slate-400">—</span>' : ''}
                    </td>
                </tr>`).join('') : '<tr><td colspan="5" class="py-6 text-center text-slate-400">No purchase orders.</td></tr>';
        } catch (e) { body.innerHTML = '<tr><td colspan="5" class="py-6 text-center text-red-500">Failed to load.</td></tr>'; }
    }

    // Suppliers modal
    function openSupplierModal() { document.getElementById('supplier-modal').classList.remove('hidden'); }
    function closeSupplierModal() { document.getElementById('supplier-modal').classList.add('hidden'); }
    async function submitSupplier(e) {
        e.preventDefault();
        try {
            await api.post('/procurement/suppliers', {
                name: document.getElementById('s-name').value,
                contact_person: document.getElementById('s-contact').value || null,
                phone: document.getElementById('s-phone').value || null,
                email: document.getElementById('s-email').value || null,
                address: document.getElementById('s-address').value || null,
            });
            e.target.reset(); closeSupplierModal(); loadSuppliers(); loadOrders();
        } catch (err) { alert(err.message || 'Failed to save supplier.'); }
    }

    // PO modal
    function openPoModal() {
        if (!suppliers.length) { alert('Add a supplier first.'); return; }
        document.getElementById('po-lines').innerHTML = '';
        document.getElementById('po-notes').value = '';
        document.getElementById('po-expected').value = '';
        document.getElementById('po-alert').classList.add('hidden');
        addPoLine();
        document.getElementById('po-modal').classList.remove('hidden');
        if (window.lucide) lucide.createIcons();
    }
    function closePoModal() { document.getElementById('po-modal').classList.add('hidden'); }

    function addPoLine() {
        const wrap = document.createElement('div');
        wrap.className = 'grid grid-cols-12 gap-2 items-center po-line';
        const opts = ['<option value="">— free text —</option>'].concat(catalogue.map(c => `<option value="${c.id}" data-price="${c.price_per_unit}">${c.name}</option>`)).join('');
        wrap.innerHTML = `
            <select class="col-span-4 line-item bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg px-2 py-1.5 text-[11px]" onchange="onLineItem(this)">${opts}</select>
            <input class="col-span-3 line-desc bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg px-2 py-1.5 text-[11px]" placeholder="Description *">
            <input type="number" min="1" value="1" class="col-span-2 line-qty bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg px-2 py-1.5 text-[11px]" oninput="recalcPo()" placeholder="Qty">
            <input type="number" min="0" step="0.01" value="0" class="col-span-2 line-cost bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg px-2 py-1.5 text-[11px]" oninput="recalcPo()" placeholder="Unit ₦">
            <button type="button" onclick="this.closest('.po-line').remove();recalcPo()" class="col-span-1 text-red-500 hover:text-red-700 text-center">✕</button>`;
        document.getElementById('po-lines').appendChild(wrap);
    }
    function onLineItem(sel) {
        const line = sel.closest('.po-line');
        const opt = sel.options[sel.selectedIndex];
        if (sel.value) {
            line.querySelector('.line-desc').value = opt.textContent;
            line.querySelector('.line-cost').value = opt.dataset.price || 0;
        }
        recalcPo();
    }
    function recalcPo() {
        let total = 0;
        document.querySelectorAll('.po-line').forEach(l => {
            const q = parseFloat(l.querySelector('.line-qty').value) || 0;
            const c = parseFloat(l.querySelector('.line-cost').value) || 0;
            total += q * c;
        });
        document.getElementById('po-total').innerText = total.toLocaleString();
    }
    async function submitPo(e) {
        e.preventDefault();
        const items = [];
        document.querySelectorAll('.po-line').forEach(l => {
            const desc = l.querySelector('.line-desc').value.trim();
            const qty = parseInt(l.querySelector('.line-qty').value) || 0;
            const cost = parseFloat(l.querySelector('.line-cost').value) || 0;
            const pid = l.querySelector('.line-item').value;
            if (desc && qty > 0) items.push({ pharmacy_item_id: pid ? parseInt(pid) : null, description: desc, quantity_ordered: qty, unit_cost: cost });
        });
        if (!items.length) { alert('Add at least one line item.'); return; }
        try {
            await api.post('/procurement/orders', {
                supplier_id: parseInt(document.getElementById('po-supplier').value),
                expected_date: document.getElementById('po-expected').value || null,
                notes: document.getElementById('po-notes').value || null,
                items,
            });
            closePoModal(); loadOrders();
        } catch (err) {
            const a = document.getElementById('po-alert'); a.innerText = err.message || 'Failed.'; a.classList.remove('hidden');
        }
    }

    async function approvePo(id) {
        if (!confirm('Approve this purchase order?')) return;
        try { await api.post(`/procurement/orders/${id}/approve`, {}); loadOrders(); } catch (e) { alert(e.message); }
    }

    // GRN
    async function openGrn(id) {
        grnOrderId = id;
        try {
            const res = await api.get(`/procurement/orders/${id}`);
            document.getElementById('grn-po-no').innerText = res.order.po_number;
            const outstanding = res.items.filter(i => i.quantity_received < i.quantity_ordered);
            document.getElementById('grn-lines').innerHTML = outstanding.length ? outstanding.map(i => {
                const rem = i.quantity_ordered - i.quantity_received;
                return `<div class="grid grid-cols-12 gap-2 items-center grn-line" data-item="${i.id}">
                    <div class="col-span-7 text-[11px]"><b class="text-slate-800 dark:text-white">${i.description}</b><span class="text-slate-500 block">ordered ${i.quantity_ordered}, received ${i.quantity_received}${i.pharmacy_item ? ' · stocks '+i.pharmacy_item : ''}</span></div>
                    <div class="col-span-3 text-[10px] text-slate-500 text-right">outstanding ${rem}</div>
                    <input type="number" min="0" max="${rem}" value="${rem}" class="col-span-2 grn-qty bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg px-2 py-1.5 text-[11px]">
                </div>`;
            }).join('') : '<p class="text-xs text-slate-400">Nothing outstanding to receive.</p>';
            document.getElementById('grn-modal').classList.remove('hidden');
        } catch (e) { alert(e.message || 'Failed to open receipt.'); }
    }
    function closeGrnModal() { document.getElementById('grn-modal').classList.add('hidden'); }
    async function submitGrn() {
        const receipts = [];
        document.querySelectorAll('.grn-line').forEach(l => {
            const qty = parseInt(l.querySelector('.grn-qty').value) || 0;
            if (qty > 0) receipts.push({ item_id: parseInt(l.dataset.item), quantity: qty });
        });
        if (!receipts.length) { alert('Enter at least one received quantity.'); return; }
        try { await api.post(`/procurement/orders/${grnOrderId}/receive`, { receipts }); closeGrnModal(); loadOrders(); }
        catch (e) { alert(e.message || 'Failed to post receipt.'); }
    }

    document.addEventListener('DOMContentLoaded', async () => {
        await loadCatalogue();
        loadSuppliers();
        loadOrders();
    });
</script>
@endsection
