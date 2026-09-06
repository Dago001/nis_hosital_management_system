@extends('layouts.app')

@section('title', 'Theatre Schedule | NIS Medical Services Portal')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-white flex items-center gap-2">
                <i data-lucide="scissors" class="text-emerald-600 shrink-0"></i> Operating Theatre Schedule
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">Book surgeries, assign surgeons and theatres — with double-booking prevention.</p>
        </div>
        <button id="new-surg-btn" onclick="openSurgeryModal()" class="hidden bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-xl text-xs font-semibold flex items-center gap-2 shadow-lg shadow-emerald-600/10 transition self-start sm:self-auto shrink-0">
            <i data-lucide="plus" class="w-4 h-4"></i> Schedule Surgery
        </button>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach([['id'=>'th-today','l'=>'Today','c'=>'emerald'],['id'=>'th-scheduled','l'=>'Scheduled','c'=>'blue'],['id'=>'th-progress','l'=>'In Theatre','c'=>'amber'],['id'=>'th-completed','l'=>'Completed','c'=>'slate']] as $s)
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4">
            <div class="text-xl font-black text-{{ $s['c'] }}-600" id="{{ $s['id'] }}">0</div>
            <div class="text-[10px] text-slate-500 dark:text-slate-400 font-semibold uppercase tracking-wide">{{ $s['l'] }}</div>
        </div>
        @endforeach
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
        <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex items-center gap-3">
            <h3 class="text-sm font-bold text-slate-800 dark:text-white mr-auto">Surgery Board</h3>
            <input type="date" id="th-date-filter" onchange="loadSurgeries()" class="bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs">
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-950 text-slate-700 dark:text-slate-300"><tr>
                    <th class="py-2.5 px-4 font-bold">Time</th><th class="py-2.5 px-4 font-bold">Patient</th>
                    <th class="py-2.5 px-4 font-bold">Procedure</th><th class="py-2.5 px-4 font-bold">Surgeon</th>
                    <th class="py-2.5 px-4 font-bold">Theatre</th><th class="py-2.5 px-4 font-bold">Status</th>
                    <th class="py-2.5 px-4 font-bold text-right">Action</th>
                </tr></thead>
                <tbody id="surg-body" class="divide-y divide-slate-100 dark:divide-slate-800"><tr><td colspan="7" class="py-6 text-center text-slate-400">Loading…</td></tr></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Schedule surgery modal -->
<div id="surgery-modal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 w-full max-w-lg shadow-2xl my-8">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-bold text-slate-800 dark:text-white">Schedule Surgery</h3>
            <button onclick="closeSurgeryModal()" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form onsubmit="submitSurgery(event)" class="space-y-3">
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider mb-1 text-slate-700 dark:text-slate-300">Patient *</label>
                <div class="flex gap-2">
                    <input id="sg-search" placeholder="Search name or hospital code" class="flex-1 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs">
                    <button type="button" onclick="searchSgPatients()" class="bg-slate-800 dark:bg-slate-700 text-white px-3 py-2 rounded-xl text-xs font-bold">Find</button>
                </div>
                <select id="sg-patient" class="mt-2 w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs"><option value="">— select patient —</option></select>
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider mb-1 text-slate-700 dark:text-slate-300">Procedure *</label>
                <input id="sg-procedure" required placeholder="e.g. Appendectomy" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider mb-1 text-slate-700 dark:text-slate-300">Surgeon</label>
                    <select id="sg-surgeon" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs"><option value="">— unassigned —</option></select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider mb-1 text-slate-700 dark:text-slate-300">Theatre *</label>
                    <select id="sg-theatre" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs"></select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider mb-1 text-slate-700 dark:text-slate-300">Start *</label>
                    <input id="sg-start" type="datetime-local" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs">
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider mb-1 text-slate-700 dark:text-slate-300">End *</label>
                    <input id="sg-end" type="datetime-local" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs">
                </div>
            </div>
            <textarea id="sg-notes" rows="2" placeholder="Notes" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs"></textarea>
            <div id="sg-alert" class="hidden text-xs font-semibold rounded-xl px-3 py-2 bg-red-50 text-red-700"></div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeSurgeryModal()" class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl">Cancel</button>
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 text-xs font-bold rounded-xl">Book</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const stCls = {scheduled:'bg-blue-100 text-blue-700',in_progress:'bg-amber-100 text-amber-700',completed:'bg-emerald-100 text-emerald-700',cancelled:'bg-red-100 text-red-700'};
    const canManage = () => { const r = user.roles && user.roles[0] ? user.roles[0].name : ''; return ['super_admin','hospital_admin','medical_director','doctor','consultant','theatre_manager','nurse'].includes(r); };
    const fmtTime = (s) => s ? new Date(s.replace(' ','T')).toLocaleString([], {month:'short',day:'numeric',hour:'2-digit',minute:'2-digit'}) : '—';

    async function loadRefs() {
        try {
            const [th, sg] = await Promise.all([api.get('/theatre/theatres'), api.get('/theatre/surgeons')]);
            document.getElementById('sg-theatre').innerHTML = (th.theatres||[]).map(t => `<option value="${t.id}">${t.name}</option>`).join('');
            document.getElementById('sg-surgeon').innerHTML = '<option value="">— unassigned —</option>' + (sg.surgeons||[]).map(s => `<option value="${s.id}">Dr. ${s.full_name}</option>`).join('');
        } catch (e) {}
    }

    async function loadSurgeries() {
        const body = document.getElementById('surg-body');
        const date = document.getElementById('th-date-filter').value;
        try {
            const res = await api.get('/theatre/surgeries' + (date ? `?date=${date}` : ''));
            document.getElementById('th-today').innerText = res.stats.today;
            document.getElementById('th-scheduled').innerText = res.stats.scheduled;
            document.getElementById('th-progress').innerText = res.stats.in_progress;
            document.getElementById('th-completed').innerText = res.stats.completed;
            const list = res.surgeries || [];
            body.innerHTML = list.length ? list.map(s => `
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30">
                    <td class="py-2.5 px-4 whitespace-nowrap"><b class="text-slate-800 dark:text-white">${fmtTime(s.scheduled_start)}</b><div class="text-[9px] text-slate-500">to ${fmtTime(s.scheduled_end)}</div></td>
                    <td class="py-2.5 px-4">${s.patient}<div class="text-[9px] text-slate-500 font-mono">${s.hospital_code||''}</div></td>
                    <td class="py-2.5 px-4 font-semibold text-slate-800 dark:text-white">${s.procedure_name}</td>
                    <td class="py-2.5 px-4">${s.surgeon ? 'Dr. '+s.surgeon : '—'}</td>
                    <td class="py-2.5 px-4">${s.theatre||'—'}</td>
                    <td class="py-2.5 px-4"><span class="px-2 py-0.5 rounded text-[10px] font-bold ${stCls[s.status]||''}">${s.status.replace('_',' ')}</span></td>
                    <td class="py-2.5 px-4 text-right whitespace-nowrap">
                        ${canManage() && s.status==='scheduled' ? `<button onclick="setStatus(${s.id},'in_progress')" class="text-amber-600 hover:underline font-bold mr-2">Start</button><button onclick="setStatus(${s.id},'cancelled')" class="text-red-500 hover:underline font-bold">Cancel</button>` : ''}
                        ${canManage() && s.status==='in_progress' ? `<button onclick="setStatus(${s.id},'completed')" class="text-emerald-600 hover:underline font-bold">Complete</button>` : ''}
                        ${['completed','cancelled'].includes(s.status) ? '<span class="text-slate-400">—</span>' : ''}
                    </td>
                </tr>`).join('') : '<tr><td colspan="7" class="py-6 text-center text-slate-400">No surgeries scheduled.</td></tr>';
        } catch (e) { body.innerHTML = '<tr><td colspan="7" class="py-6 text-center text-red-500">Failed to load.</td></tr>'; }
    }

    async function setStatus(id, status) {
        if (status === 'cancelled' && !confirm('Cancel this surgery?')) return;
        try { await api.post(`/theatre/surgeries/${id}/status`, { status }); loadSurgeries(); } catch (e) { alert(e.message); }
    }

    function openSurgeryModal() {
        document.getElementById('sg-alert').classList.add('hidden');
        document.getElementById('sg-patient').innerHTML = '<option value="">— select patient —</option>';
        ['sg-search','sg-procedure','sg-start','sg-end','sg-notes'].forEach(i => document.getElementById(i).value = '');
        document.getElementById('surgery-modal').classList.remove('hidden');
    }
    function closeSurgeryModal() { document.getElementById('surgery-modal').classList.add('hidden'); }

    async function searchSgPatients() {
        const q = document.getElementById('sg-search').value.trim();
        if (q.length < 3) { alert('Type at least 3 characters.'); return; }
        try {
            const res = await api.get('/patients?search=' + encodeURIComponent(q));
            const sel = document.getElementById('sg-patient');
            sel.innerHTML = '<option value="">— select patient —</option>' + (res.patients||[]).map(p => `<option value="${p.id}">${p.full_name} (${p.immigration_service_number||'—'})</option>`).join('');
            if (!res.patients || !res.patients.length) alert('No patients found.');
        } catch (e) { alert(e.message || 'Search failed.'); }
    }

    async function submitSurgery(e) {
        e.preventDefault();
        const alertEl = document.getElementById('sg-alert');
        const pid = document.getElementById('sg-patient').value;
        if (!pid) { alertEl.innerText = 'Select a patient.'; alertEl.classList.remove('hidden'); return; }
        try {
            await api.post('/theatre/surgeries', {
                patient_id: parseInt(pid),
                surgeon_id: document.getElementById('sg-surgeon').value ? parseInt(document.getElementById('sg-surgeon').value) : null,
                theatre_id: parseInt(document.getElementById('sg-theatre').value),
                procedure_name: document.getElementById('sg-procedure').value,
                scheduled_start: document.getElementById('sg-start').value.replace('T',' ') + ':00',
                scheduled_end: document.getElementById('sg-end').value.replace('T',' ') + ':00',
                notes: document.getElementById('sg-notes').value || null,
            });
            closeSurgeryModal(); loadSurgeries();
        } catch (err) { alertEl.innerText = err.message || 'Failed to schedule.'; alertEl.classList.remove('hidden'); }
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (canManage()) document.getElementById('new-surg-btn').classList.remove('hidden');
        loadRefs();
        loadSurgeries();
    });
</script>
@endsection
