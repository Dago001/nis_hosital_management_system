@extends('layouts.app')

@section('title', 'IPD Management – NIS Medical Services Portal')

@section('content')
<div class="space-y-6">

    <!-- Page Header -->
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-white">IPD & Bed Management</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">In-patient department · admissions, wards & bed census</p>
        </div>
        <div class="flex gap-2 flex-wrap">
            <button id="tab-admissions" class="ipd-tab-btn text-xs font-bold px-4 py-2 rounded-xl transition bg-emerald-600 text-white border-emerald-600">Active Admissions</button>
            <button id="tab-wards"      class="ipd-tab-btn text-xs font-bold px-4 py-2 rounded-xl transition border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">Wards & Beds</button>
            <button id="admit-open-btn" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-4 py-2 rounded-xl flex items-center gap-1.5 transition">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i> Admit Patient
            </button>
        </div>
    </div>

    <!-- IPD Stats -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 text-center">
            <p class="text-2xl font-black text-slate-800 dark:text-white" id="ipd-total-active">–</p>
            <p class="text-[10px] text-slate-500 uppercase tracking-wider mt-1">Active Admissions</p>
        </div>
        <div class="bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/20 rounded-2xl p-4 text-center">
            <p class="text-2xl font-black text-blue-700 dark:text-blue-400" id="ipd-admitted-today">–</p>
            <p class="text-[10px] text-blue-600 uppercase tracking-wider mt-1">Admitted Today</p>
        </div>
        <div class="bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 rounded-2xl p-4 text-center">
            <p class="text-2xl font-black text-emerald-700 dark:text-emerald-400" id="ipd-discharged-today">–</p>
            <p class="text-[10px] text-emerald-600 uppercase tracking-wider mt-1">Discharged Today</p>
        </div>
        <div class="bg-violet-50 dark:bg-violet-500/10 border border-violet-200 dark:border-violet-500/20 rounded-2xl p-4 text-center">
            <p class="text-2xl font-black text-violet-700 dark:text-violet-400" id="ipd-avg-los">–</p>
            <p class="text-[10px] text-violet-600 uppercase tracking-wider mt-1">Avg. Length of Stay</p>
        </div>
    </div>

    <!-- Admissions Section -->
    <div id="section-admissions">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-slate-800">
                <span class="text-sm font-bold text-slate-800 dark:text-white">Current IPD Census</span>
                <div class="flex gap-2">
                    <select id="ward-filter" class="text-xs px-2 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 text-slate-700 dark:text-slate-200 focus:outline-none">
                        <option value="">All Wards</option>
                    </select>
                    <select id="admission-status-filter" class="text-xs px-2 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 text-slate-700 dark:text-slate-200 focus:outline-none">
                        <option value="active">Active</option>
                        <option value="discharged">Discharged</option>
                    </select>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-700">
                            <th class="text-left px-4 py-3 font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wide">Patient</th>
                            <th class="text-left px-4 py-3 font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wide">Hospital No.</th>
                            <th class="text-left px-4 py-3 font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wide">Ward / Bed</th>
                            <th class="text-left px-4 py-3 font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wide">Doctor</th>
                            <th class="text-left px-4 py-3 font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wide">Admitted</th>
                            <th class="text-left px-4 py-3 font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wide">LOS</th>
                            <th class="text-left px-4 py-3 font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wide">Type</th>
                            <th class="text-left px-4 py-3 font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wide">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="admissions-tbody">
                        <tr>
                            <td colspan="8" class="py-10 text-center text-slate-400 text-xs">
                                <i data-lucide="loader" class="w-5 h-5 mx-auto animate-spin mb-2"></i>
                                Loading admissions...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Wards Section -->
    <div id="section-wards" class="hidden">
        <div id="wards-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 text-center text-slate-400 text-xs col-span-3">
                <i data-lucide="loader" class="w-5 h-5 mx-auto animate-spin mb-2"></i> Loading wards...
            </div>
        </div>
    </div>
</div>

<!-- Admit Patient Modal -->
<div id="admit-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 w-full max-w-lg shadow-2xl overflow-y-auto max-h-[90vh]">
        <h3 class="text-base font-bold text-slate-800 dark:text-white mb-5">Admit Patient to Ward</h3>
        <form id="admit-form" class="space-y-4">
            <!-- Patient Search & Verification -->
            <div class="bg-slate-50 dark:bg-slate-950/40 p-4 border border-slate-200 dark:border-slate-800 rounded-2xl space-y-3">
                <label class="block text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Search Patient by Hospital Code</label>
                <div class="flex gap-2">
                    <input type="text" id="admit-patient-search-code" placeholder="Enter Hospital Code e.g. NIS/PAT/000001" 
                           class="flex-grow bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-850 dark:text-slate-205 focus:outline-none focus:ring-1 focus:ring-emerald-500 font-mono tracking-wide">
                    <button type="button" id="admit-patient-lookup-btn" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1 shrink-0 cursor-pointer">
                        Lookup
                    </button>
                </div>
                <!-- Patient Card Preview -->
                <div id="admit-patient-preview" class="hidden p-3 rounded-xl border border-emerald-500/20 bg-emerald-500/5 flex items-center justify-between">
                    <div>
                        <h4 class="text-xs font-black text-slate-800 dark:text-white" id="admit-preview-name">Name</h4>
                        <p class="text-[10px] text-slate-500 dark:text-slate-400">Gender: <span id="admit-preview-gender"></span> | DOB: <span id="admit-preview-dob"></span></p>
                    </div>
                    <span class="text-[9px] bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 font-mono font-bold px-2 py-0.5 rounded" id="admit-preview-code">Code</span>
                </div>
                <!-- Hidden Patient ID -->
                <input type="hidden" id="admit-patient-id" required>
            </div>

            <div class="grid grid-cols-1 gap-3">
                <div>
                    <label class="ipd-label font-bold text-slate-800 dark:text-white">Attending Doctor <span class="text-red-500">*</span></label>
                    <select id="admit-staff-id" required class="ipd-input w-full bg-white dark:bg-slate-950">
                        <option value="">– Select Doctor –</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="ipd-label">Select Bed <span class="text-red-500">*</span></label>
                <select id="admit-bed-id" required class="ipd-input w-full">
                    <option value="">– Select a bed –</option>
                </select>
            </div>
            <div>
                <label class="ipd-label">Admission Type <span class="text-red-500">*</span></label>
                <select id="admit-type" required class="ipd-input w-full">
                    <option value="elective">Elective</option>
                    <option value="emergency">Emergency</option>
                    <option value="transfer">Transfer</option>
                </select>
            </div>
            <div>
                <label class="ipd-label">Diagnosis on Admission <span class="text-red-500">*</span></label>
                <textarea id="admit-diagnosis" required rows="2" placeholder="Primary diagnosis..." class="ipd-input w-full resize-none"></textarea>
            </div>
            <div>
                <label class="ipd-label">Ward Notes</label>
                <textarea id="admit-notes" rows="2" placeholder="Additional notes..." class="ipd-input w-full resize-none"></textarea>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" id="admit-cancel-btn" class="flex-1 px-4 py-2 text-xs font-semibold border border-slate-200 dark:border-slate-700 rounded-xl text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition">Cancel</button>
                <button type="button" id="admit-submit-btn" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-4 py-2 rounded-xl transition">Admit Patient</button>
            </div>
        </form>
    </div>
</div>

<!-- Discharge Modal -->
<div id="discharge-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 w-full max-w-md shadow-2xl">
        <h3 class="text-base font-bold text-slate-800 dark:text-white mb-4">Discharge Patient</h3>
        <input type="hidden" id="discharge-admission-id">
        <div>
            <label class="ipd-label">Discharge Summary <span class="text-red-500">*</span></label>
            <textarea id="discharge-summary" rows="4" placeholder="Provide a detailed discharge summary..." class="ipd-input w-full resize-none"></textarea>
        </div>
        <div class="flex gap-3 mt-5">
            <button type="button" id="discharge-cancel-btn" class="flex-1 px-4 py-2 text-xs font-semibold border border-slate-200 dark:border-slate-700 rounded-xl text-slate-700 dark:text-slate-300 hover:bg-slate-50 transition">Cancel</button>
            <button type="button" id="discharge-submit-btn" class="flex-1 bg-red-600 hover:bg-red-700 text-white text-xs font-bold px-4 py-2 rounded-xl transition">Confirm Discharge</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<style>
    .ipd-input { display:block; width:100%; font-size:.75rem; padding:.625rem .75rem; border-radius:.75rem; border:1px solid #e2e8f0; background:#f8fafc; color:#1e293b; outline:none; }
    .dark .ipd-input { border-color:#334155; background:#020617; color:#e2e8f0; }
    .ipd-input:focus { border-color:#10b981; box-shadow:0 0 0 1px #10b981; }
    .ipd-label { display:block; font-size:.625rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#475569; margin-bottom:.25rem; }
    .dark .ipd-label { color:#94a3b8; }
</style>
<script>
(function () {
    'use strict';

    const admTypeColors = {
        elective:  'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400',
        emergency: 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400',
        transfer:  'bg-violet-100 text-violet-700 dark:bg-violet-500/20 dark:text-violet-400',
    };

    let currentTab = 'admissions';

    /* ── Tab switching ────────────────────────────── */
    function showTab(tab) {
        currentTab = tab;
        ['admissions', 'wards'].forEach(t => {
            const section = document.getElementById('section-' + t);
            const btn     = document.getElementById('tab-' + t);
            if (!section || !btn) return;
            section.classList.toggle('hidden', t !== tab);
            if (t === tab) {
                btn.className = 'ipd-tab-btn text-xs font-bold px-4 py-2 rounded-xl transition bg-emerald-600 text-white border-emerald-600';
            } else {
                btn.className = 'ipd-tab-btn text-xs font-bold px-4 py-2 rounded-xl transition border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800';
            }
        });
        if (tab === 'wards') loadWards();
    }

    /* ── Load admissions ─────────────────────────── */
    async function loadAdmissions() {
        const statusEl = document.getElementById('admission-status-filter');
        const wardEl   = document.getElementById('ward-filter');
        const tbody    = document.getElementById('admissions-tbody');
        const status   = statusEl ? statusEl.value : 'active';
        const ward     = wardEl   ? wardEl.value   : '';

        let qs = '?status=' + status;
        if (ward) qs += '&ward_id=' + ward;

        tbody.innerHTML = `<tr><td colspan="8" class="py-10 text-center text-slate-400 text-xs">
            <i data-lucide="loader" class="w-5 h-5 mx-auto animate-spin mb-2"></i>Loading...</td></tr>`;
        if (typeof lucide !== 'undefined') lucide.createIcons();

        try {
            const data  = await window.api.get('/ipd/admissions' + qs);
            const list  = data.admissions || [];
            const stats = data.stats      || {};

            const setEl = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
            setEl('ipd-total-active',     stats.total_active        ?? 0);
            setEl('ipd-admitted-today',   stats.admitted_today       ?? 0);
            setEl('ipd-discharged-today', stats.discharged_today     ?? 0);
            setEl('ipd-avg-los',          (stats.avg_los ?? 0) + 'd');

            if (!list.length) {
                tbody.innerHTML = `<tr><td colspan="8" class="py-12 text-center text-slate-400 text-xs">
                    <i data-lucide="bed" class="w-8 h-8 mx-auto mb-2 opacity-40"></i>
                    <p class="font-semibold">No admissions found for the selected filters.</p></td></tr>`;
                if (typeof lucide !== 'undefined') lucide.createIcons();
                return;
            }

            tbody.innerHTML = list.map(a => `
                <tr class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition">
                    <td class="px-4 py-3">
                        <p class="font-semibold text-slate-800 dark:text-white">${a.patient_name}</p>
                        <p class="text-slate-400 text-[10px]">${a.gender}, ${a.age}y</p>
                    </td>
                    <td class="px-4 py-3 font-mono text-[10px] text-slate-600 dark:text-slate-300">${a.hospital_number}</td>
                    <td class="px-4 py-3 text-slate-600 dark:text-slate-300">
                        ${a.ward}<br><span class="text-[10px] text-slate-400">Bed ${a.bed_number}</span>
                    </td>
                    <td class="px-4 py-3 text-slate-600 dark:text-slate-300">${a.doctor}</td>
                    <td class="px-4 py-3 text-slate-500 text-[10px]">${a.admitted_at ?? '–'}</td>
                    <td class="px-4 py-3 font-bold text-slate-700 dark:text-slate-200">${a.length_of_stay_days}d</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 rounded-full text-[10px] font-bold ${admTypeColors[a.admission_type] || 'bg-slate-100 text-slate-600'}">${a.admission_type}</span>
                    </td>
                    <td class="px-4 py-3">
                        ${a.status === 'active'
                            ? `<button class="discharge-btn text-red-500 hover:text-red-700 text-[10px] font-bold underline underline-offset-2" data-id="${a.id}">Discharge</button>`
                            : '<span class="text-[10px] text-slate-400">Discharged</span>'}
                    </td>
                </tr>`).join('');

            tbody.querySelectorAll('.discharge-btn').forEach(btn => {
                btn.addEventListener('click', () => openDischargeModal(btn.dataset.id));
            });
            if (typeof lucide !== 'undefined') lucide.createIcons();
        } catch (e) {
            tbody.innerHTML = `<tr><td colspan="8" class="py-10 text-center text-red-400 text-xs">Error: ${e.message}</td></tr>`;
        }
    }

    /* ── Load wards ──────────────────────────────── */
    async function loadWards() {
        const grid = document.getElementById('wards-grid');
        grid.innerHTML = `<div class="col-span-3 text-center text-slate-400 text-xs py-8">
            <i data-lucide="loader" class="w-5 h-5 mx-auto animate-spin mb-2"></i>Loading wards...</div>`;
        if (typeof lucide !== 'undefined') lucide.createIcons();

        try {
            const data  = await window.api.get('/ipd/wards');
            const wards = data.wards || [];
            if (!wards.length) {
                grid.innerHTML = `<div class="col-span-3 text-center text-slate-400 text-xs py-8">No wards configured.</div>`;
                return;
            }
            grid.innerHTML = wards.map(w => {
                const pct      = w.occupancy_rate;
                const barColor = pct > 85 ? 'bg-red-500' : pct > 60 ? 'bg-amber-500' : 'bg-emerald-500';
                const badgeCol = pct > 85
                    ? 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400'
                    : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400';
                return `<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-bold text-slate-800 dark:text-white text-sm">${w.name}</h3>
                        <span class="text-[10px] font-bold px-2 py-1 rounded-full ${badgeCol}">${pct}% Full</span>
                    </div>
                    <div class="grid grid-cols-3 gap-2 text-center text-[10px] mb-3">
                        <div class="bg-emerald-50 dark:bg-emerald-500/10 rounded-lg py-2">
                            <p class="font-black text-emerald-700 dark:text-emerald-400 text-base">${w.available_beds}</p>
                            <p class="text-slate-500">Available</p>
                        </div>
                        <div class="bg-red-50 dark:bg-red-500/10 rounded-lg py-2">
                            <p class="font-black text-red-700 dark:text-red-400 text-base">${w.occupied_beds}</p>
                            <p class="text-slate-500">Occupied</p>
                        </div>
                        <div class="bg-slate-50 dark:bg-slate-800 rounded-lg py-2">
                            <p class="font-black text-slate-700 dark:text-slate-300 text-base">${w.total_beds}</p>
                            <p class="text-slate-500">Total</p>
                        </div>
                    </div>
                    <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2">
                        <div class="${barColor} h-2 rounded-full" style="width:${pct}%"></div>
                    </div>
                    ${w.description ? `<p class="text-[10px] text-slate-400 mt-2">${w.description}</p>` : ''}
                </div>`;
            }).join('');
        } catch (e) {
            grid.innerHTML = `<div class="col-span-3 text-center text-red-400 text-xs py-8">Failed to load wards: ${e.message}</div>`;
        }
    }

    /* ── Available beds for admit form ──────────── */
    async function loadAvailableBeds() {
        try {
            const data = await window.api.get('/ipd/beds/available');
            const sel  = document.getElementById('admit-bed-id');
            sel.innerHTML = '<option value="">– Select a bed –</option>';
            (data.beds || []).forEach(b => {
                const opt = document.createElement('option');
                opt.value = b.id;
                opt.textContent = `${(b.ward && b.ward.name) ? b.ward.name : 'Ward'} – Bed ${b.bed_number}`;
                sel.appendChild(opt);
            });
        } catch (_) {}
    }

    async function loadWardFilter() {
        try {
            const data = await window.api.get('/ipd/wards');
            const sel  = document.getElementById('ward-filter');
            if (!sel) return;
            (data.wards || []).forEach(w => {
                const opt = document.createElement('option');
                opt.value = w.id; opt.textContent = w.name;
                sel.appendChild(opt);
            });
        } catch (_) {}
    }

    async function loadIpdDoctors() {
        try {
            const res = await window.api.get('/referrals/doctors');
            const sel = document.getElementById('admit-staff-id');
            sel.innerHTML = '<option value="">– Select Doctor –</option>';
            (res.doctors || []).forEach(d => {
                const opt = document.createElement('option');
                opt.value = d.id;
                opt.textContent = `${d.name} (${d.department})`;
                sel.appendChild(opt);
            });
        } catch(e) {
            console.error('Failed to load doctors for IPD admission:', e);
        }
    }

    async function lookupAdmitPatient() {
        const code = document.getElementById('admit-patient-search-code').value.trim();
        const preview = document.getElementById('admit-patient-preview');
        const hiddenId = document.getElementById('admit-patient-id');
        
        if (code.length < 3) {
            alert('Please enter a valid Hospital Code (at least 3 characters).');
            return;
        }

        try {
            const res = await window.api.get(`/patients?search=${encodeURIComponent(code)}`);
            const list = res.patients || [];
            if (list.length > 0) {
                const pat = list.find(p => p.immigration_service_number && p.immigration_service_number.toLowerCase() === code.toLowerCase()) || list[0];
                hiddenId.value = pat.id;
                document.getElementById('admit-preview-name').innerText = pat.full_name;
                document.getElementById('admit-preview-gender').innerText = pat.gender;
                document.getElementById('admit-preview-dob').innerText = pat.date_of_birth;
                document.getElementById('admit-preview-code').innerText = pat.immigration_service_number;
                preview.classList.remove('hidden');
            } else {
                alert('No patient found matching the entered Hospital Code.');
                preview.classList.add('hidden');
                hiddenId.value = '';
            }
        } catch (e) {
            alert('Failed to lookup patient: ' + e.message);
        }
    }

    /* ── Modal helpers ───────────────────────────── */
    function openAdmitModal() {
        document.getElementById('admit-form').reset();
        document.getElementById('admit-patient-preview').classList.add('hidden');
        document.getElementById('admit-patient-id').value = '';
        
        loadAvailableBeds();
        loadIpdDoctors();
        document.getElementById('admit-modal').classList.remove('hidden');
    }
    function closeAdmitModal() {
        document.getElementById('admit-modal').classList.add('hidden');
    }

    function openDischargeModal(id) {
        document.getElementById('discharge-admission-id').value = id;
        document.getElementById('discharge-summary').value      = '';
        document.getElementById('discharge-modal').classList.remove('hidden');
    }
    function closeDischargeModal() {
        document.getElementById('discharge-modal').classList.add('hidden');
    }

    /* ── Submit Admit ─────────────────────────────── */
    async function submitAdmit() {
        const btn = document.getElementById('admit-submit-btn');
        const payload = {
            patient_id:             document.getElementById('admit-patient-id').value,
            staff_id:               document.getElementById('admit-staff-id').value,
            bed_id:                 document.getElementById('admit-bed-id').value,
            admission_type:         document.getElementById('admit-type').value,
            diagnosis_on_admission: document.getElementById('admit-diagnosis').value,
            ward_notes:             document.getElementById('admit-notes').value,
        };

        if (!payload.patient_id || !payload.staff_id || !payload.bed_id || !payload.diagnosis_on_admission) {
            alert('Please fill in all required fields.'); return;
        }
        btn.disabled = true; btn.textContent = 'Admitting…';
        try {
            await window.api.post('/ipd/admit', payload);
            closeAdmitModal();
            await loadAdmissions();
            alert('Patient admitted successfully!');
        } catch (e) {
            alert('Error admitting patient: ' + e.message);
        } finally {
            btn.disabled = false; btn.textContent = 'Admit Patient';
        }
    }

    /* ── Submit Discharge ─────────────────────────── */
    async function submitDischarge() {
        const id      = document.getElementById('discharge-admission-id').value;
        const summary = document.getElementById('discharge-summary').value.trim();
        const btn     = document.getElementById('discharge-submit-btn');
        if (!summary) { alert('Please provide a discharge summary.'); return; }
        btn.disabled = true; btn.textContent = 'Discharging…';
        try {
            await window.api.post(`/ipd/admissions/${id}/discharge`, { discharge_summary: summary });
            closeDischargeModal();
            await loadAdmissions();
            alert('Patient discharged successfully!');
        } catch (e) {
            alert('Error discharging patient: ' + e.message);
        } finally {
            btn.disabled = false; btn.textContent = 'Confirm Discharge';
        }
    }

    /* ── Bootstrap ───────────────────────────────── */
    document.addEventListener('DOMContentLoaded', function () {
        /* Wire tabs */
        const tabAdm = document.getElementById('tab-admissions');
        const tabWrd = document.getElementById('tab-wards');
        if (tabAdm) tabAdm.addEventListener('click', () => showTab('admissions'));
        if (tabWrd) tabWrd.addEventListener('click', () => showTab('wards'));

        /* Wire admit modal */
        const openBtn   = document.getElementById('admit-open-btn');
        const cancelBtn = document.getElementById('admit-cancel-btn');
        const submitBtn = document.getElementById('admit-submit-btn');
        const lookupBtn = document.getElementById('admit-patient-lookup-btn');
        if (openBtn)   openBtn.addEventListener('click',   openAdmitModal);
        if (cancelBtn) cancelBtn.addEventListener('click', closeAdmitModal);
        if (submitBtn) submitBtn.addEventListener('click', submitAdmit);
        if (lookupBtn) lookupBtn.addEventListener('click', lookupAdmitPatient);

        /* Wire discharge modal */
        const dCancelBtn = document.getElementById('discharge-cancel-btn');
        const dSubmitBtn = document.getElementById('discharge-submit-btn');
        if (dCancelBtn) dCancelBtn.addEventListener('click', closeDischargeModal);
        if (dSubmitBtn) dSubmitBtn.addEventListener('click', submitDischarge);

        /* Wire filters */
        const admStatusSel = document.getElementById('admission-status-filter');
        const wardSel      = document.getElementById('ward-filter');
        if (admStatusSel) admStatusSel.addEventListener('change', loadAdmissions);
        if (wardSel)      wardSel.addEventListener('change',      loadAdmissions);

        /* Initial data load */
        loadAdmissions();
        loadWardFilter();
    });
})();
</script>
@endsection
