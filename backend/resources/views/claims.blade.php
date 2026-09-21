@extends('layouts.app')

@section('title', 'NHIS Claims | NIS Medical Services Portal')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-xl font-bold text-slate-800 dark:text-white flex items-center gap-2">
            <i data-lucide="file-check-2" class="text-emerald-600 shrink-0"></i> NHIS / HMO Claims
        </h1>
        <p class="text-xs text-slate-500 dark:text-slate-400">Batch covered patient invoices into claims and track submission &amp; settlement.</p>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach([['id'=>'c-draft','l'=>'Draft','c'=>'slate'],['id'=>'c-submitted','l'=>'Submitted','c'=>'blue'],['id'=>'c-paid','l'=>'Paid Value (₦)','c'=>'emerald'],['id'=>'c-out','l'=>'Outstanding (₦)','c'=>'amber']] as $s)
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4">
            <div class="text-xl font-black text-{{ $s['c'] }}-600" id="{{ $s['id'] }}">0</div>
            <div class="text-[10px] text-slate-500 dark:text-slate-400 font-semibold uppercase tracking-wide">{{ $s['l'] }}</div>
        </div>
        @endforeach
    </div>

    <!-- Build a claim from eligible invoices -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
        <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center gap-3 sm:justify-between">
            <h3 class="text-sm font-bold text-slate-800 dark:text-white">Eligible Covered Invoices</h3>
            <div class="flex items-center gap-2">
                <input id="claim-provider" value="NHIS" class="bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs w-28" placeholder="Provider">
                <button onclick="createClaim()" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-4 py-2 rounded-xl">Create Claim from Selected</button>
            </div>
        </div>
        <div class="overflow-x-auto max-h-72 overflow-y-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-950 text-slate-700 dark:text-slate-300 sticky top-0"><tr>
                    <th class="py-2 px-4"><input type="checkbox" id="chk-all" onclick="toggleAll(this)"></th>
                    <th class="py-2 px-4 font-bold">Patient</th><th class="py-2 px-4 font-bold">Code</th>
                    <th class="py-2 px-4 font-bold">Amount</th><th class="py-2 px-4 font-bold">Date</th>
                </tr></thead>
                <tbody id="eligible-body" class="divide-y divide-slate-100 dark:divide-slate-800"><tr><td colspan="5" class="py-6 text-center text-slate-400">Loading…</td></tr></tbody>
            </table>
        </div>
    </div>

    <!-- Claims list -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
        <div class="p-4 border-b border-slate-100 dark:border-slate-800"><h3 class="text-sm font-bold text-slate-800 dark:text-white">Claims</h3></div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-950 text-slate-700 dark:text-slate-300"><tr>
                    <th class="py-2.5 px-4 font-bold">Claim No.</th><th class="py-2.5 px-4 font-bold">Provider</th>
                    <th class="py-2.5 px-4 font-bold">Items</th><th class="py-2.5 px-4 font-bold">Total</th>
                    <th class="py-2.5 px-4 font-bold">Period</th><th class="py-2.5 px-4 font-bold">Status</th>
                    <th class="py-2.5 px-4 font-bold text-right">Action</th>
                </tr></thead>
                <tbody id="claims-body" class="divide-y divide-slate-100 dark:divide-slate-800"><tr><td colspan="7" class="py-6 text-center text-slate-400">Loading…</td></tr></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const stColors = {draft:'bg-slate-200 text-slate-700',submitted:'bg-blue-100 text-blue-700',paid:'bg-emerald-100 text-emerald-700',rejected:'bg-red-100 text-red-700'};

    async function loadEligible() {
        const body = document.getElementById('eligible-body');
        try {
            const res = await api.get('/claims/eligible');
            const list = res.invoices || [];
            body.innerHTML = list.length ? list.map(i => `
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30">
                    <td class="py-2 px-4"><input type="checkbox" class="elig-chk" value="${i.id}"></td>
                    <td class="py-2 px-4 font-semibold text-slate-800 dark:text-white">${i.patient}</td>
                    <td class="py-2 px-4 font-mono">${i.hospital_code||'—'}</td>
                    <td class="py-2 px-4 font-bold">₦${Number(i.amount).toLocaleString()}</td>
                    <td class="py-2 px-4 text-slate-500">${i.date}</td>
                </tr>`).join('') : '<tr><td colspan="5" class="py-6 text-center text-slate-400">No eligible unclaimed invoices.</td></tr>';
        } catch (e) { body.innerHTML = '<tr><td colspan="5" class="py-6 text-center text-red-500">Failed to load.</td></tr>'; }
    }

    function toggleAll(cb) { document.querySelectorAll('.elig-chk').forEach(c => c.checked = cb.checked); }

    async function createClaim() {
        const ids = [...document.querySelectorAll('.elig-chk:checked')].map(c => parseInt(c.value));
        if (!ids.length) { alert('Select at least one invoice.'); return; }
        try {
            await api.post('/claims', { provider: document.getElementById('claim-provider').value || 'NHIS', invoice_ids: ids });
            document.getElementById('chk-all').checked = false;
            loadEligible(); loadClaims();
        } catch (e) { alert(e.message); }
    }

    async function loadClaims() {
        const body = document.getElementById('claims-body');
        try {
            const res = await api.get('/claims');
            document.getElementById('c-draft').innerText = res.stats.draft;
            document.getElementById('c-submitted').innerText = res.stats.submitted;
            document.getElementById('c-paid').innerText = '₦' + Number(res.stats.paid_value).toLocaleString();
            document.getElementById('c-out').innerText = '₦' + Number(res.stats.outstanding_value).toLocaleString();
            const list = res.claims || [];
            body.innerHTML = list.length ? list.map(c => `
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30">
                    <td class="py-2.5 px-4 font-mono font-bold text-slate-800 dark:text-white">${c.claim_number}</td>
                    <td class="py-2.5 px-4">${c.provider}</td>
                    <td class="py-2.5 px-4">${c.items}</td>
                    <td class="py-2.5 px-4 font-bold">₦${Number(c.total_amount).toLocaleString()}</td>
                    <td class="py-2.5 px-4 text-slate-500">${c.period||'—'}</td>
                    <td class="py-2.5 px-4"><span class="px-2 py-0.5 rounded text-[10px] font-bold ${stColors[c.status]}">${c.status}</span></td>
                    <td class="py-2.5 px-4 text-right">
                        ${c.status==='draft' ? `<button onclick="setStatus(${c.id},'submitted')" class="text-blue-600 hover:underline font-bold">Submit</button>` : ''}
                        ${c.status==='submitted' ? `<button onclick="setStatus(${c.id},'paid')" class="text-emerald-600 hover:underline font-bold mr-2">Mark Paid</button><button onclick="setStatus(${c.id},'rejected')" class="text-red-600 hover:underline font-bold">Reject</button>` : ''}
                        ${['paid','rejected'].includes(c.status) ? '<span class="text-slate-400">—</span>' : ''}
                    </td>
                </tr>`).join('') : '<tr><td colspan="7" class="py-6 text-center text-slate-400">No claims yet.</td></tr>';
        } catch (e) { body.innerHTML = '<tr><td colspan="7" class="py-6 text-center text-red-500">Failed to load.</td></tr>'; }
    }

    async function setStatus(id, status) {
        try { await api.post(`/claims/${id}/status`, { status }); loadClaims(); } catch (e) { alert(e.message); }
    }

    document.addEventListener('DOMContentLoaded', () => { loadEligible(); loadClaims(); });
</script>
@endsection
