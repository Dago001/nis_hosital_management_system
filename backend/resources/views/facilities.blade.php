@extends('layouts.app')

@section('title', 'Facilities | NIS Medical Services Portal')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-white flex items-center gap-2">
                <i data-lucide="hospital" class="text-emerald-600 shrink-0"></i> Facilities
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">Registry of NIS medical facilities. Patient and clinical records are scoped to a facility.</p>
        </div>
        <button onclick="openFacilityModal()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-xl text-xs font-semibold flex items-center gap-2 shadow-lg shadow-emerald-600/10 transition self-start sm:self-auto shrink-0">
            <i data-lucide="plus" class="w-4 h-4"></i> Add Facility
        </button>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-200 border-b border-slate-100 dark:border-slate-800"><tr>
                    <th class="py-3 px-4 font-bold">Code</th><th class="py-3 px-4 font-bold">Name</th>
                    <th class="py-3 px-4 font-bold">Type</th><th class="py-3 px-4 font-bold">State</th>
                    <th class="py-3 px-4 font-bold">Patients</th><th class="py-3 px-4 font-bold">Status</th>
                    <th class="py-3 px-4 font-bold text-right">Action</th>
                </tr></thead>
                <tbody id="fac-body" class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    <tr><td colspan="7" class="py-8 text-center text-slate-400">Loading…</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="fac-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 w-full max-w-md shadow-2xl">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-bold text-slate-800 dark:text-white" id="fac-title">Add Facility</h3>
            <button onclick="closeFacilityModal()" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form onsubmit="submitFacility(event)" class="space-y-3">
            <div class="grid grid-cols-2 gap-3">
                <input id="f-code" required placeholder="Code *" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs font-mono">
                <select id="f-type" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs">
                    <option value="hospital">Hospital</option><option value="clinic">Clinic</option><option value="health_post">Health Post</option>
                </select>
            </div>
            <input id="f-name" required placeholder="Facility name *" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs">
            <input id="f-state" placeholder="State" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs">
            <input id="f-address" placeholder="Address" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs">
            <input id="f-phone" placeholder="Phone" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs">
            <div id="f-active-wrap" class="hidden"><label class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300"><input type="checkbox" id="f-active" checked> Active</label></div>
            <div id="f-alert" class="hidden text-xs font-semibold rounded-xl px-3 py-2 bg-red-50 text-red-700"></div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeFacilityModal()" class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl">Cancel</button>
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 text-xs font-bold rounded-xl">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let editingId = null;
    const typeLabels = {hospital:'Hospital',clinic:'Clinic',health_post:'Health Post'};

    async function loadFacilities() {
        const body = document.getElementById('fac-body');
        try {
            const res = await api.get('/facilities');
            body.innerHTML = (res.facilities||[]).map(f => `
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30">
                    <td class="py-3 px-4 font-mono font-bold text-slate-800 dark:text-white">${f.code}</td>
                    <td class="py-3 px-4">${f.name}</td>
                    <td class="py-3 px-4">${typeLabels[f.type]||f.type}</td>
                    <td class="py-3 px-4">${f.state||'—'}</td>
                    <td class="py-3 px-4 font-bold">${f.patients}</td>
                    <td class="py-3 px-4">${f.is_active ? '<span class="text-emerald-600 font-bold">Active</span>' : '<span class="text-slate-400">Inactive</span>'}</td>
                    <td class="py-3 px-4 text-right"><button onclick='editFacility(${JSON.stringify(f)})' class="text-emerald-600 hover:underline font-bold">Edit</button></td>
                </tr>`).join('') || '<tr><td colspan="7" class="py-8 text-center text-slate-400">No facilities.</td></tr>';
        } catch (e) { body.innerHTML = '<tr><td colspan="7" class="py-8 text-center text-red-500">Failed to load.</td></tr>'; }
    }

    function openFacilityModal() {
        editingId = null;
        document.getElementById('fac-title').innerText = 'Add Facility';
        document.querySelector('#fac-modal form').reset();
        document.getElementById('f-code').disabled = false;
        document.getElementById('f-active-wrap').classList.add('hidden');
        document.getElementById('f-alert').classList.add('hidden');
        document.getElementById('fac-modal').classList.remove('hidden');
    }
    function editFacility(f) {
        editingId = f.id;
        document.getElementById('fac-title').innerText = 'Edit Facility';
        document.getElementById('f-code').value = f.code;
        document.getElementById('f-code').disabled = true;
        document.getElementById('f-name').value = f.name;
        document.getElementById('f-type').value = f.type;
        document.getElementById('f-state').value = f.state || '';
        document.getElementById('f-address').value = f.address || '';
        document.getElementById('f-phone').value = f.phone || '';
        document.getElementById('f-active').checked = !!f.is_active;
        document.getElementById('f-active-wrap').classList.remove('hidden');
        document.getElementById('f-alert').classList.add('hidden');
        document.getElementById('fac-modal').classList.remove('hidden');
    }
    function closeFacilityModal() { document.getElementById('fac-modal').classList.add('hidden'); }

    async function submitFacility(e) {
        e.preventDefault();
        const alertEl = document.getElementById('f-alert');
        const payload = {
            name: document.getElementById('f-name').value,
            type: document.getElementById('f-type').value,
            state: document.getElementById('f-state').value || null,
            address: document.getElementById('f-address').value || null,
            phone: document.getElementById('f-phone').value || null,
        };
        try {
            if (editingId) {
                payload.is_active = document.getElementById('f-active').checked;
                await api.put(`/facilities/${editingId}`, payload);
            } else {
                payload.code = document.getElementById('f-code').value;
                await api.post('/facilities', payload);
            }
            closeFacilityModal(); loadFacilities();
        } catch (err) { alertEl.innerText = err.message || 'Failed to save.'; alertEl.classList.remove('hidden'); }
    }

    document.addEventListener('DOMContentLoaded', loadFacilities);
</script>
@endsection
