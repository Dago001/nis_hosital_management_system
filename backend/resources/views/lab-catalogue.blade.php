@extends('layouts.app')

@section('title', 'Lab Test Catalogue | NIS Medical Services Portal')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-white flex items-center gap-2">
                <i data-lucide="flask-conical" class="text-emerald-600 shrink-0"></i> Laboratory Test Catalogue
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">Reference ranges &amp; critical thresholds. Results auto-flag High / Low / Critical against these values.</p>
        </div>
        <button onclick="openTestModal()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-xl text-xs font-semibold flex items-center gap-2 shadow-lg shadow-emerald-600/10 transition self-start sm:self-auto shrink-0">
            <i data-lucide="plus" class="w-4 h-4"></i> Add Test
        </button>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-200 border-b border-slate-100 dark:border-slate-800">
                    <tr>
                        <th class="py-3 px-4 font-bold">Code</th>
                        <th class="py-3 px-4 font-bold">Test</th>
                        <th class="py-3 px-4 font-bold">Category</th>
                        <th class="py-3 px-4 font-bold">Unit</th>
                        <th class="py-3 px-4 font-bold">Reference</th>
                        <th class="py-3 px-4 font-bold">Critical</th>
                        <th class="py-3 px-4 font-bold">Status</th>
                        <th class="py-3 px-4 font-bold text-right">Action</th>
                    </tr>
                </thead>
                <tbody id="test-body" class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    <tr><td colspan="8" class="py-8 text-center text-slate-400">Loading…</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="test-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 w-full max-w-md shadow-2xl">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-bold text-slate-800 dark:text-white" id="test-modal-title">Add Test</h3>
            <button onclick="closeTestModal()" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form id="test-form" onsubmit="submitTest(event)" class="space-y-3">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider mb-1 text-slate-700 dark:text-slate-300">Code *</label>
                    <input id="x-code" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs">
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider mb-1 text-slate-700 dark:text-slate-300">Unit</label>
                    <input id="x-unit" placeholder="e.g. g/dL" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs">
                </div>
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider mb-1 text-slate-700 dark:text-slate-300">Test Name *</label>
                <input id="x-name" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs">
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider mb-1 text-slate-700 dark:text-slate-300">Category</label>
                <input id="x-category" placeholder="e.g. Haematology" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider mb-1 text-slate-700 dark:text-slate-300">Reference Low</label>
                    <input id="x-ref-low" type="number" step="any" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs">
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider mb-1 text-slate-700 dark:text-slate-300">Reference High</label>
                    <input id="x-ref-high" type="number" step="any" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs">
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider mb-1 text-slate-700 dark:text-slate-300">Critical Low</label>
                    <input id="x-crit-low" type="number" step="any" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs">
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider mb-1 text-slate-700 dark:text-slate-300">Critical High</label>
                    <input id="x-crit-high" type="number" step="any" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs">
                </div>
            </div>
            <div id="x-active-wrap" class="hidden">
                <label class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300"><input type="checkbox" id="x-active" checked> Active</label>
            </div>
            <div id="x-alert" class="hidden text-xs font-semibold rounded-xl px-3 py-2"></div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeTestModal()" class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl">Cancel</button>
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 text-xs font-bold rounded-xl shadow-md">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let editingId = null;
    const fmt = (v) => (v === null || v === undefined || v === '') ? '–' : v;

    async function loadTests() {
        const body = document.getElementById('test-body');
        try {
            const res = await api.get('/lab-tests');
            const list = res.tests || [];
            body.innerHTML = list.map(t => `
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30">
                    <td class="py-3 px-4 font-mono font-bold text-slate-800 dark:text-white">${t.code}</td>
                    <td class="py-3 px-4">${t.name}</td>
                    <td class="py-3 px-4">${t.category || '–'}</td>
                    <td class="py-3 px-4">${t.unit || '–'}</td>
                    <td class="py-3 px-4">${fmt(t.ref_low)} – ${fmt(t.ref_high)}</td>
                    <td class="py-3 px-4 text-red-600">${fmt(t.critical_low)} / ${fmt(t.critical_high)}</td>
                    <td class="py-3 px-4">${t.is_active ? '<span class="text-emerald-600 font-bold">Active</span>' : '<span class="text-slate-400">Inactive</span>'}</td>
                    <td class="py-3 px-4 text-right">
                        <button onclick='editTest(${JSON.stringify(t).replace(/'/g, "&#39;")})' class="text-emerald-600 hover:underline font-bold">Edit</button>
                    </td>
                </tr>`).join('') || '<tr><td colspan="8" class="py-8 text-center text-slate-400">No tests.</td></tr>';
        } catch (e) {
            body.innerHTML = '<tr><td colspan="8" class="py-8 text-center text-red-500">Failed to load.</td></tr>';
        }
    }

    function openTestModal() {
        editingId = null;
        document.getElementById('test-modal-title').innerText = 'Add Test';
        document.getElementById('test-form').reset();
        document.getElementById('x-code').disabled = false;
        document.getElementById('x-active-wrap').classList.add('hidden');
        document.getElementById('x-alert').classList.add('hidden');
        document.getElementById('test-modal').classList.remove('hidden');
    }
    function editTest(t) {
        editingId = t.id;
        document.getElementById('test-modal-title').innerText = 'Edit Test';
        document.getElementById('x-code').value = t.code;
        document.getElementById('x-code').disabled = true;
        document.getElementById('x-name').value = t.name;
        document.getElementById('x-category').value = t.category || '';
        document.getElementById('x-unit').value = t.unit || '';
        document.getElementById('x-ref-low').value = t.ref_low ?? '';
        document.getElementById('x-ref-high').value = t.ref_high ?? '';
        document.getElementById('x-crit-low').value = t.critical_low ?? '';
        document.getElementById('x-crit-high').value = t.critical_high ?? '';
        document.getElementById('x-active').checked = !!t.is_active;
        document.getElementById('x-active-wrap').classList.remove('hidden');
        document.getElementById('x-alert').classList.add('hidden');
        document.getElementById('test-modal').classList.remove('hidden');
    }
    function closeTestModal() { document.getElementById('test-modal').classList.add('hidden'); }

    const numOrNull = (id) => { const v = document.getElementById(id).value; return v === '' ? null : parseFloat(v); };

    async function submitTest(e) {
        e.preventDefault();
        const alertEl = document.getElementById('x-alert');
        const payload = {
            name: document.getElementById('x-name').value,
            category: document.getElementById('x-category').value || null,
            unit: document.getElementById('x-unit').value || null,
            ref_low: numOrNull('x-ref-low'),
            ref_high: numOrNull('x-ref-high'),
            critical_low: numOrNull('x-crit-low'),
            critical_high: numOrNull('x-crit-high'),
        };
        try {
            if (editingId) {
                payload.is_active = document.getElementById('x-active').checked;
                await api.put(`/lab-tests/${editingId}`, payload);
            } else {
                payload.code = document.getElementById('x-code').value;
                await api.post('/lab-tests', payload);
            }
            closeTestModal();
            loadTests();
        } catch (err) {
            alertEl.className = 'text-xs font-semibold rounded-xl px-3 py-2 bg-red-50 text-red-700';
            alertEl.innerText = err.message || 'Failed to save.';
            alertEl.classList.remove('hidden');
        }
    }

    document.addEventListener('DOMContentLoaded', loadTests);
</script>
@endsection
