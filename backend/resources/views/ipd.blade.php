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

<!-- Ward Care Modal (observations + MAR) -->
<div id="care-modal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-start justify-center p-4 bg-slate-950/60 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl w-full max-w-3xl shadow-2xl my-8">
        <!-- Header -->
        <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-start justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-800 dark:text-white" id="care-patient">Ward Care</h3>
                <p class="text-[11px] text-slate-500 dark:text-slate-400" id="care-meta"></p>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="printDischargeSummary()" id="care-print-btn" class="hidden text-[10px] font-bold text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 px-3 py-1.5 rounded-lg hover:bg-slate-50">Print Discharge Summary</button>
                <button onclick="closeCareModal()" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
        </div>
        <div id="care-allergy" class="hidden mx-5 mt-4 p-2.5 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 text-[11px] font-bold flex items-center gap-2"></div>

        <div class="p-5 grid grid-cols-1 lg:grid-cols-2 gap-5 max-h-[70vh] overflow-y-auto">
            <!-- Observations -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <h4 class="text-xs font-black text-slate-800 dark:text-white uppercase tracking-wide">Observations</h4>
                    <button onclick="toggleObsForm()" id="obs-toggle" class="text-[10px] font-bold text-emerald-600 hover:underline">+ Record</button>
                </div>
                <form id="obs-form" onsubmit="submitObs(event)" class="hidden bg-slate-50 dark:bg-slate-950/40 rounded-xl p-3 mb-3 space-y-2">
                    <div class="grid grid-cols-3 gap-2">
                        <input id="ob-bp" placeholder="BP" class="ipd-input">
                        <input id="ob-temp" type="number" step="0.1" placeholder="Temp °C" class="ipd-input">
                        <input id="ob-pulse" type="number" placeholder="Pulse" class="ipd-input">
                        <input id="ob-resp" type="number" placeholder="Resp" class="ipd-input">
                        <input id="ob-spo2" type="number" placeholder="SpO₂ %" class="ipd-input">
                        <input id="ob-news" type="number" placeholder="NEWS" class="ipd-input">
                    </div>
                    <textarea id="ob-notes" rows="2" placeholder="Nursing notes…" class="ipd-input resize-none"></textarea>
                    <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold py-2 rounded-lg">Save Observation</button>
                </form>
                <div id="obs-list" class="space-y-2 text-[11px]"></div>
            </div>

            <!-- MAR -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <h4 class="text-xs font-black text-slate-800 dark:text-white uppercase tracking-wide">Medication Administration (MAR)</h4>
                    <button onclick="toggleMarForm()" id="mar-toggle" class="text-[10px] font-bold text-emerald-600 hover:underline">+ Administer</button>
                </div>
                <form id="mar-form" onsubmit="submitMar(event)" class="hidden bg-slate-50 dark:bg-slate-950/40 rounded-xl p-3 mb-3 space-y-2">
                    <select id="mar-drug" class="ipd-input"><option value="">Select prescribed drug or type below…</option></select>
                    <input id="mar-drug-manual" placeholder="Drug name (if not listed)" class="ipd-input">
                    <div class="grid grid-cols-3 gap-2">
                        <input id="mar-dose" placeholder="Dose" class="ipd-input">
                        <input id="mar-route" placeholder="Route (IV/Oral)" class="ipd-input">
                        <select id="mar-status" class="ipd-input">
                            <option value="given">Given</option>
                            <option value="missed">Missed</option>
                            <option value="held">Held</option>
                            <option value="refused">Refused</option>
                        </select>
                    </div>
                    <textarea id="mar-notes" rows="1" placeholder="Notes…" class="ipd-input resize-none"></textarea>
                    <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold py-2 rounded-lg">Record Administration</button>
                </form>
                <div id="mar-list" class="space-y-2 text-[11px]"></div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden printable discharge summary -->
<div id="discharge-print" class="print-only" style="display:none;padding:24px;color:#0f172a;">
    <div style="text-align:center;border-bottom:1px solid #cbd5e1;padding-bottom:10px;margin-bottom:14px;">
        <img src="/images/nis_logo.jpg" style="height:60px;width:60px;object-fit:contain;margin:0 auto 6px;">
        <h2 style="font-weight:800;text-transform:uppercase;">Nigeria Immigration Service</h2>
        <p style="font-size:12px;color:#475569;">Hospital Medical Services Portal, Abuja | Discharge Summary</p>
    </div>
    <div id="discharge-print-body" style="font-size:13px;line-height:1.7;"></div>
</div>

@endsection

@section('scripts')
<style>
    .ipd-input { display:block; width:100%; font-size:.75rem; padding:.625rem .75rem; border-radius:.75rem; border:1px solid #e2e8f0; background:#f8fafc; color:#1e293b; outline:none; }
    .dark .ipd-input { border-color:#334155; background:#020617; color:#e2e8f0; }
    .ipd-input:focus { border-color:#10b981; box-shadow:0 0 0 1px #10b981; }
    .ipd-label { display:block; font-size:.625rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#475569; margin-bottom:.25rem; }
    @media print {
        body * { visibility: hidden !important; }
        #discharge-print, #discharge-print * { visibility: visible !important; }
        #discharge-print { display:block !important; position:absolute; left:0; top:0; width:100%; }
        @page { margin: 12mm; }
    }
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
                        <div class="flex items-center gap-3">
                            <button class="care-btn text-emerald-600 hover:text-emerald-700 text-[10px] font-bold underline underline-offset-2" data-id="${a.id}">Ward Care</button>
                            ${a.status === 'active'
                                ? `<button class="discharge-btn text-red-500 hover:text-red-700 text-[10px] font-bold underline underline-offset-2" data-id="${a.id}">Discharge</button>`
                                : '<span class="text-[10px] text-slate-400">Discharged</span>'}
                        </div>
                    </td>
                </tr>`).join('');

            tbody.querySelectorAll('.discharge-btn').forEach(btn => {
                btn.addEventListener('click', () => openDischargeModal(btn.dataset.id));
            });
            tbody.querySelectorAll('.care-btn').forEach(btn => {
                btn.addEventListener('click', () => openCareModal(btn.dataset.id));
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

    /* ── Ward Care (observations + MAR) ─────────────── */
    let careAdmissionId = null;
    let careData = null;

    window.openCareModal = async function (id) {
        careAdmissionId = id;
        document.getElementById('care-modal').classList.remove('hidden');
        document.getElementById('obs-list').innerHTML = '<p class="text-slate-400">Loading…</p>';
        document.getElementById('mar-list').innerHTML = '';
        try {
            careData = await window.api.get(`/ipd/admissions/${id}/care`);
            renderCare();
        } catch (e) {
            document.getElementById('obs-list').innerHTML = `<p class="text-red-500">${e.message}</p>`;
        }
    };
    window.closeCareModal = function () {
        document.getElementById('care-modal').classList.add('hidden');
        document.getElementById('obs-form').classList.add('hidden');
        document.getElementById('mar-form').classList.add('hidden');
    };
    window.toggleObsForm = () => document.getElementById('obs-form').classList.toggle('hidden');
    window.toggleMarForm = () => document.getElementById('mar-form').classList.toggle('hidden');

    function renderCare() {
        const a = careData.admission;
        document.getElementById('care-patient').innerText = `${a.patient_name} · ${a.hospital_code || ''}`;
        document.getElementById('care-meta').innerText = `${a.ward || ''} · Bed ${a.bed || ''} · Dr. ${a.doctor || '—'} · ${a.diagnosis || ''} · Admitted ${a.admitted_at || ''}`;
        const allergy = document.getElementById('care-allergy');
        if (a.allergies && a.allergies.toLowerCase() !== 'none' && a.allergies.trim() !== '') {
            allergy.innerHTML = `<i data-lucide="alert-triangle" class="w-4 h-4"></i> ALLERGIES: ${a.allergies}`;
            allergy.classList.remove('hidden');
        } else { allergy.classList.add('hidden'); }

        // discharged admissions are read-only
        const active = a.status === 'active';
        document.getElementById('obs-toggle').style.display = active ? '' : 'none';
        document.getElementById('mar-toggle').style.display = active ? '' : 'none';
        document.getElementById('care-print-btn').classList.toggle('hidden', !a.discharge_summary);

        // prescribed drug picker
        const drugSel = document.getElementById('mar-drug');
        drugSel.innerHTML = '<option value="">Select prescribed drug or type below…</option>' +
            (careData.prescribed_drugs || []).map(d => `<option value="${d.drug_name}" data-id="${d.prescription_item_id}">${d.drug_name} — ${d.dosage} ${d.frequency}</option>`).join('');

        document.getElementById('obs-list').innerHTML = (careData.observations || []).length
            ? careData.observations.map(o => `
                <div class="border border-slate-200 dark:border-slate-800 rounded-xl p-2.5">
                    <div class="flex justify-between"><span class="font-bold text-slate-800 dark:text-white">${o.recorded_at}</span>
                    ${o.news_score != null ? `<span class="px-1.5 rounded ${o.news_score>=5?'bg-red-100 text-red-700':o.news_score>=3?'bg-amber-100 text-amber-700':'bg-emerald-100 text-emerald-700'} text-[10px] font-bold">NEWS ${o.news_score}</span>` : ''}</div>
                    <div class="text-slate-600 dark:text-slate-300 mt-1">BP ${o.blood_pressure||'—'} · T ${o.temperature||'—'}°C · P ${o.pulse_rate||'—'} · R ${o.respiratory_rate||'—'} · SpO₂ ${o.spo2||'—'}%</div>
                    ${o.notes ? `<div class="text-slate-500 mt-1 italic">${o.notes}</div>` : ''}
                    <div class="text-[9px] text-slate-400 mt-1">by ${o.recorded_by}</div>
                </div>`).join('')
            : '<p class="text-slate-400">No observations recorded yet.</p>';

        const statusColors = {given:'bg-emerald-100 text-emerald-700',missed:'bg-red-100 text-red-700',held:'bg-amber-100 text-amber-700',refused:'bg-slate-200 text-slate-700'};
        document.getElementById('mar-list').innerHTML = (careData.medications || []).length
            ? careData.medications.map(m => `
                <div class="border border-slate-200 dark:border-slate-800 rounded-xl p-2.5 flex justify-between items-start">
                    <div><span class="font-bold text-slate-800 dark:text-white">${m.drug_name}</span> <span class="text-slate-500">${m.dose||''} ${m.route||''}</span>
                    ${m.notes?`<div class="text-slate-500 italic">${m.notes}</div>`:''}
                    <div class="text-[9px] text-slate-400 mt-0.5">${m.administered_at} · ${m.administered_by}</div></div>
                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold ${statusColors[m.status]||'bg-slate-100'}">${m.status}</span>
                </div>`).join('')
            : '<p class="text-slate-400">No medications administered yet.</p>';

        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    window.submitObs = async function (e) {
        e.preventDefault();
        const payload = {
            blood_pressure: document.getElementById('ob-bp').value || null,
            temperature: parseFloat(document.getElementById('ob-temp').value) || null,
            pulse_rate: parseInt(document.getElementById('ob-pulse').value) || null,
            respiratory_rate: parseInt(document.getElementById('ob-resp').value) || null,
            spo2: parseInt(document.getElementById('ob-spo2').value) || null,
            news_score: document.getElementById('ob-news').value !== '' ? parseInt(document.getElementById('ob-news').value) : null,
            notes: document.getElementById('ob-notes').value || null,
        };
        try {
            await window.api.post(`/ipd/admissions/${careAdmissionId}/observations`, payload);
            document.getElementById('obs-form').reset();
            document.getElementById('obs-form').classList.add('hidden');
            openCareModal(careAdmissionId);
        } catch (err) { alert(err.message); }
    };

    window.submitMar = async function (e) {
        e.preventDefault();
        const drug = document.getElementById('mar-drug-manual').value.trim() || document.getElementById('mar-drug').value;
        if (!drug) { alert('Select or type a drug name.'); return; }
        const opt = document.getElementById('mar-drug').selectedOptions[0];
        const payload = {
            prescription_item_id: (opt && opt.dataset.id) ? parseInt(opt.dataset.id) : null,
            drug_name: drug,
            dose: document.getElementById('mar-dose').value || null,
            route: document.getElementById('mar-route').value || null,
            status: document.getElementById('mar-status').value,
            notes: document.getElementById('mar-notes').value || null,
        };
        try {
            await window.api.post(`/ipd/admissions/${careAdmissionId}/medications`, payload);
            document.getElementById('mar-form').reset();
            document.getElementById('mar-form').classList.add('hidden');
            openCareModal(careAdmissionId);
        } catch (err) { alert(err.message); }
    };

    window.printDischargeSummary = function () {
        if (!careData) return;
        const a = careData.admission;
        document.getElementById('discharge-print-body').innerHTML = `
            <p><b>Patient:</b> ${a.patient_name} (${a.hospital_code || ''})</p>
            <p><b>Ward / Bed:</b> ${a.ward || ''} / ${a.bed || ''}</p>
            <p><b>Attending Doctor:</b> ${a.doctor || '—'}</p>
            <p><b>Diagnosis on Admission:</b> ${a.diagnosis || '—'}</p>
            <p><b>Admitted:</b> ${a.admitted_at || '—'} &nbsp; <b>Discharged:</b> ${a.discharged_at || '—'}</p>
            <hr style="margin:10px 0;border-color:#e2e8f0;">
            <p><b>Discharge Summary</b></p><p>${(a.discharge_summary || '—').replace(/</g,'&lt;')}</p>`;
        window.print();
    };
})();
</script>
@endsection
