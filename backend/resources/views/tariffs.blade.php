@extends('layouts.app')

@section('title', 'Service Tariffs | NIS Medical Services Portal')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-white flex items-center gap-2">
                <i data-lucide="tags" class="text-emerald-600 shrink-0"></i> Service Tariffs &amp; Price Catalogue
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">Configure prices for consultations, procedures, tests, and beds used across billing.</p>
        </div>
        <button onclick="openTariffModal()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-xl text-xs font-semibold flex items-center gap-2 shadow-lg shadow-emerald-600/10 transition self-start sm:self-auto shrink-0">
            <i data-lucide="plus" class="w-4 h-4"></i> Add Tariff
        </button>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-200 border-b border-slate-100 dark:border-slate-800">
                    <tr>
                        <th class="py-3 px-4 font-bold">Code</th>
                        <th class="py-3 px-4 font-bold">Service</th>
                        <th class="py-3 px-4 font-bold">Category</th>
                        <th class="py-3 px-4 font-bold">Price (₦)</th>
                        <th class="py-3 px-4 font-bold">Status</th>
                        <th class="py-3 px-4 font-bold text-right">Action</th>
                    </tr>
                </thead>
                <tbody id="tariff-body" class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    <tr><td colspan="6" class="py-8 text-center text-slate-400">Loading…</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="tariff-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 w-full max-w-md shadow-2xl">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-bold text-slate-800 dark:text-white" id="tariff-modal-title">Add Tariff</h3>
            <button onclick="closeTariffModal()" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form id="tariff-form" onsubmit="submitTariff(event)" class="space-y-3">
            <input type="hidden" id="t-id">
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider mb-1 text-slate-700 dark:text-slate-300">Code *</label>
                <input id="t-code" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs">
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider mb-1 text-slate-700 dark:text-slate-300">Service Name *</label>
                <input id="t-name" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider mb-1 text-slate-700 dark:text-slate-300">Category *</label>
                    <select id="t-category" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs">
                        <option value="consultation">Consultation</option>
                        <option value="procedure">Procedure</option>
                        <option value="lab">Lab</option>
                        <option value="radiology">Radiology</option>
                        <option value="bed">Bed</option>
                        <option value="registration">Registration</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider mb-1 text-slate-700 dark:text-slate-300">Price (₦) *</label>
                    <input id="t-price" type="number" step="0.01" min="0" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs">
                </div>
            </div>
            <div id="t-active-wrap" class="hidden">
                <label class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300"><input type="checkbox" id="t-active" checked> Active</label>
            </div>
            <div id="t-alert" class="hidden text-xs font-semibold rounded-xl px-3 py-2"></div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeTariffModal()" class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl">Cancel</button>
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 text-xs font-bold rounded-xl shadow-md">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const catColors = {consultation:'bg-emerald-100 text-emerald-700',procedure:'bg-blue-100 text-blue-700',lab:'bg-amber-100 text-amber-700',radiology:'bg-violet-100 text-violet-700',bed:'bg-rose-100 text-rose-700',registration:'bg-slate-200 text-slate-700',other:'bg-slate-100 text-slate-600'};
    let editingId = null;

    async function loadTariffs() {
        const body = document.getElementById('tariff-body');
        try {
            const res = await api.get('/tariffs');
            const list = res.tariffs || [];
            body.innerHTML = list.map(t => `
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30">
                    <td class="py-3 px-4 font-mono font-bold text-slate-800 dark:text-white">${t.code}</td>
                    <td class="py-3 px-4">${t.name}</td>
                    <td class="py-3 px-4"><span class="px-2 py-0.5 rounded text-[10px] font-bold ${catColors[t.category]||'bg-slate-100'}">${t.category}</span></td>
                    <td class="py-3 px-4 font-bold">₦${Number(t.price).toLocaleString()}</td>
                    <td class="py-3 px-4">${t.is_active ? '<span class="text-emerald-600 font-bold">Active</span>' : '<span class="text-slate-400">Inactive</span>'}</td>
                    <td class="py-3 px-4 text-right">
                        <button onclick='editTariff(${JSON.stringify(t)})' class="text-emerald-600 hover:underline font-bold">Edit</button>
                    </td>
                </tr>`).join('') || '<tr><td colspan="6" class="py-8 text-center text-slate-400">No tariffs.</td></tr>';
        } catch (e) {
            body.innerHTML = '<tr><td colspan="6" class="py-8 text-center text-red-500">Failed to load.</td></tr>';
        }
    }

    function openTariffModal() {
        editingId = null;
        document.getElementById('tariff-modal-title').innerText = 'Add Tariff';
        document.getElementById('tariff-form').reset();
        document.getElementById('t-code').disabled = false;
        document.getElementById('t-active-wrap').classList.add('hidden');
        document.getElementById('t-alert').classList.add('hidden');
        document.getElementById('tariff-modal').classList.remove('hidden');
    }
    function editTariff(t) {
        editingId = t.id;
        document.getElementById('tariff-modal-title').innerText = 'Edit Tariff';
        document.getElementById('t-id').value = t.id;
        document.getElementById('t-code').value = t.code;
        document.getElementById('t-code').disabled = true;
        document.getElementById('t-name').value = t.name;
        document.getElementById('t-category').value = t.category;
        document.getElementById('t-price').value = t.price;
        document.getElementById('t-active').checked = !!t.is_active;
        document.getElementById('t-active-wrap').classList.remove('hidden');
        document.getElementById('t-alert').classList.add('hidden');
        document.getElementById('tariff-modal').classList.remove('hidden');
    }
    function closeTariffModal() { document.getElementById('tariff-modal').classList.add('hidden'); }

    async function submitTariff(e) {
        e.preventDefault();
        const alert = document.getElementById('t-alert');
        const payload = {
            name: document.getElementById('t-name').value,
            category: document.getElementById('t-category').value,
            price: parseFloat(document.getElementById('t-price').value),
        };
        try {
            if (editingId) {
                payload.is_active = document.getElementById('t-active').checked;
                await api.put(`/tariffs/${editingId}`, payload);
            } else {
                payload.code = document.getElementById('t-code').value;
                await api.post('/tariffs', payload);
            }
            closeTariffModal();
            loadTariffs();
        } catch (err) {
            alert.className = 'text-xs font-semibold rounded-xl px-3 py-2 bg-red-50 text-red-700';
            alert.innerText = err.message || 'Failed to save.';
            alert.classList.remove('hidden');
        }
    }

    document.addEventListener('DOMContentLoaded', loadTariffs);
</script>
@endsection
