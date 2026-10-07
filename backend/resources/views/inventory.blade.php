@extends('layouts.app')

@section('title', 'Store Issuance | NIS Medical Services Portal')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-white flex items-center gap-2">
                <i data-lucide="package" class="text-emerald-600 shrink-0"></i> Central Store &amp; Drug Issuance
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">Issue drugs, injections and consumables from the store to the pharmacy dispensary, and keep a full issuance record.</p>
        </div>
        <button id="issue-btn" onclick="openIssueModal()" class="hidden bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-xl text-xs font-semibold flex items-center gap-2 shadow-lg shadow-emerald-600/10 transition self-start sm:self-auto shrink-0">
            <i data-lucide="plus" class="w-4 h-4"></i> Issue Stock
        </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach([
            ['id'=>'stat-total','label'=>'Total Issuances','icon'=>'clipboard-list','color'=>'emerald'],
            ['id'=>'stat-today','label'=>'Issued Today','icon'=>'calendar-check','color'=>'blue'],
            ['id'=>'stat-units','label'=>'Units Issued Today','icon'=>'boxes','color'=>'violet'],
        ] as $s)
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 flex items-center gap-4">
            <div class="p-3 rounded-xl bg-{{ $s['color'] }}-50 dark:bg-{{ $s['color'] }}-500/10 text-{{ $s['color'] }}-600 shrink-0">
                <i data-lucide="{{ $s['icon'] }}" class="w-5 h-5"></i>
            </div>
            <div>
                <div class="text-xl font-black text-slate-800 dark:text-white" id="{{ $s['id'] }}">0</div>
                <div class="text-[10px] text-slate-500 dark:text-slate-400 font-semibold uppercase tracking-wide">{{ $s['label'] }}</div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Issuance records -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
        <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center gap-3 sm:justify-between">
            <h3 class="text-sm font-bold text-slate-800 dark:text-white">Issuance Records</h3>
            <input id="issuance-search" oninput="debouncedLoad()" type="text" placeholder="Search by reference or drug…" class="w-full sm:w-64 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-1 focus:ring-emerald-500">
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-200 border-b border-slate-100 dark:border-slate-800">
                    <tr>
                        <th class="py-3 px-4 font-bold">Reference</th>
                        <th class="py-3 px-4 font-bold">Drug / Item</th>
                        <th class="py-3 px-4 font-bold">Qty</th>
                        <th class="py-3 px-4 font-bold">Batch / Expiry</th>
                        <th class="py-3 px-4 font-bold">Issued By</th>
                        <th class="py-3 px-4 font-bold">Received By</th>
                        <th class="py-3 px-4 font-bold">Date</th>
                    </tr>
                </thead>
                <tbody id="issuance-body" class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    <tr><td colspan="7" class="py-8 text-center text-slate-400">Loading…</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Issue Stock Modal -->
<div id="issue-modal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 w-full max-w-lg shadow-2xl relative my-8">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-bold text-slate-800 dark:text-white">Issue Stock to Pharmacy</h3>
            <button onclick="closeIssueModal()" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form id="issue-form" onsubmit="submitIssue(event)" class="space-y-4">
            <div>
                <label class="block text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1">Drug / Item *</label>
                <select id="f-item" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <option value="">Select item…</option>
                </select>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1">Quantity *</label>
                    <input id="f-qty" type="number" min="1" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1">Receiving Pharmacist</label>
                    <select id="f-pharm" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        <option value="">Pharmacy (unspecified)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1">Batch Number</label>
                    <input id="f-batch" type="text" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1">Expiry Date</label>
                    <input id="f-expiry" type="date" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1">Notes</label>
                <textarea id="f-notes" rows="2" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-1 focus:ring-emerald-500"></textarea>
            </div>
            <div id="issue-alert" class="hidden text-xs font-semibold rounded-xl px-3 py-2"></div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeIssueModal()" class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl">Cancel</button>
                <button type="submit" id="issue-submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 text-xs font-bold rounded-xl shadow-md">Issue Stock</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const canIssue = (() => {
        const perms = (user.roles || []).flatMap(r => (r.permissions || []).map(p => p.name));
        const role = user.roles && user.roles[0] ? user.roles[0].name : '';
        return ['super_admin','ict_admin','hospital_admin','inventory_officer'].includes(role) || perms.includes('issue_stock');
    })();

    let searchTimer = null;
    function debouncedLoad() { clearTimeout(searchTimer); searchTimer = setTimeout(loadIssuances, 350); }

    async function loadIssuances() {
        const body = document.getElementById('issuance-body');
        const search = document.getElementById('issuance-search').value.trim();
        try {
            const res = await api.get('/inventory/issuances' + (search ? ('?search=' + encodeURIComponent(search)) : ''));
            document.getElementById('stat-total').innerText = res.stats?.total_issuances ?? 0;
            document.getElementById('stat-today').innerText = res.stats?.issued_today ?? 0;
            document.getElementById('stat-units').innerText = res.stats?.units_today ?? 0;
            const rows = res.issuances || [];
            if (!rows.length) {
                body.innerHTML = `<tr><td colspan="7" class="py-8 text-center text-slate-400">No issuance records yet.</td></tr>`;
                return;
            }
            body.innerHTML = rows.map(x => `
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30">
                    <td class="py-3 px-4 font-mono font-bold text-slate-800 dark:text-white">${x.reference}</td>
                    <td class="py-3 px-4">${x.item_name || '—'}<div class="text-[10px] text-slate-400">${x.category || ''}</div></td>
                    <td class="py-3 px-4 font-bold">${x.quantity}</td>
                    <td class="py-3 px-4">${x.batch_number || '—'}<div class="text-[10px] text-slate-400">${x.expiry_date || ''}</div></td>
                    <td class="py-3 px-4">${x.issued_by || '—'}</td>
                    <td class="py-3 px-4">${x.received_by || 'Pharmacy'}</td>
                    <td class="py-3 px-4 text-slate-500">${x.issued_at || ''}</td>
                </tr>`).join('');
        } catch (e) {
            body.innerHTML = `<tr><td colspan="7" class="py-8 text-center text-red-500">Failed to load records.</td></tr>`;
        }
    }

    async function openIssueModal() {
        // populate items + pharmacists
        try {
            const [items, pharms] = await Promise.all([api.get('/inventory/items'), api.get('/inventory/pharmacists')]);
            const itemSel = document.getElementById('f-item');
            itemSel.innerHTML = '<option value="">Select item…</option>' + (items.items || []).map(i =>
                `<option value="${i.id}">${i.name} (in stock: ${i.quantity_in_stock})</option>`).join('');
            const pharmSel = document.getElementById('f-pharm');
            pharmSel.innerHTML = '<option value="">Pharmacy (unspecified)</option>' + (pharms.pharmacists || []).map(p =>
                `<option value="${p.id}">${p.name}</option>`).join('');
        } catch (e) {}
        document.getElementById('issue-modal').classList.remove('hidden');
    }
    function closeIssueModal() { document.getElementById('issue-modal').classList.add('hidden'); }

    async function submitIssue(e) {
        e.preventDefault();
        const btn = document.getElementById('issue-submit');
        const alert = document.getElementById('issue-alert');
        btn.disabled = true; btn.innerText = 'Issuing…';
        const payload = {
            pharmacy_item_id: document.getElementById('f-item').value,
            quantity: parseInt(document.getElementById('f-qty').value, 10),
            received_by_staff_id: document.getElementById('f-pharm').value || null,
            batch_number: document.getElementById('f-batch').value || null,
            expiry_date: document.getElementById('f-expiry').value || null,
            notes: document.getElementById('f-notes').value || null,
        };
        try {
            const res = await api.post('/inventory/issue', payload);
            alert.className = 'text-xs font-semibold rounded-xl px-3 py-2 bg-emerald-50 text-emerald-700';
            alert.innerText = `${res.message} (Ref: ${res.issuance?.reference || ''})`;
            alert.classList.remove('hidden');
            document.getElementById('issue-form').reset();
            loadIssuances();
            setTimeout(closeIssueModal, 1200);
        } catch (err) {
            alert.className = 'text-xs font-semibold rounded-xl px-3 py-2 bg-red-50 text-red-700';
            alert.innerText = err.message || 'Failed to issue stock.';
            alert.classList.remove('hidden');
        } finally {
            btn.disabled = false; btn.innerText = 'Issue Stock';
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (canIssue) document.getElementById('issue-btn').classList.remove('hidden');
        loadIssuances();
    });
</script>
@endsection
