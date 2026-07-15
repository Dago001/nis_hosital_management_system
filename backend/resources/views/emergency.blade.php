@extends('layouts.app')

@section('title', 'Emergency Management – NIS Medical Services Portal')

@section('content')
<div class="space-y-6">

    <!-- Page Header -->
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div class="flex items-center gap-3">
            <div class="p-2.5 bg-red-100 dark:bg-red-500/10 text-red-600 rounded-xl">
                <i data-lucide="alert-circle" class="w-5 h-5"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold text-slate-800 dark:text-white">Emergency Department</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Real-time ER triage, patient tracking & rapid response</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <select id="er-status-filter" onchange="loadEmergencies()" class="text-xs px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-red-500">
                <option value="active">Active Cases</option>
                <option value="all">All Cases</option>
                <option value="waiting">Waiting</option>
                <option value="in_treatment">In Treatment</option>
                <option value="discharged">Discharged</option>
            </select>
            <button onclick="loadEmergencies()" class="text-xs font-semibold px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center gap-1 transition">
                <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i> Refresh
            </button>
            <button onclick="openNewERModal()" class="bg-red-600 hover:bg-red-700 text-white text-xs font-bold px-4 py-2 rounded-xl flex items-center gap-1.5 transition shadow-md shadow-red-200 dark:shadow-none">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i> Register Emergency
            </button>
        </div>
    </div>

    <!-- ER Stats -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
        <div class="col-span-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 text-center">
            <p class="text-2xl font-black text-slate-800 dark:text-white" id="er-stat-today">–</p>
            <p class="text-[10px] text-slate-500 uppercase tracking-wider mt-1">Today Total</p>
        </div>
        <div class="col-span-1 bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 rounded-2xl p-4 text-center">
            <p class="text-2xl font-black text-amber-700 dark:text-amber-400" id="er-stat-waiting">–</p>
            <p class="text-[10px] text-amber-600 uppercase tracking-wider mt-1">Waiting</p>
        </div>
        <div class="col-span-1 bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/20 rounded-2xl p-4 text-center">
            <p class="text-2xl font-black text-blue-700 dark:text-blue-400" id="er-stat-treatment">–</p>
            <p class="text-[10px] text-blue-600 uppercase tracking-wider mt-1">In Treatment</p>
        </div>
        <div class="col-span-1 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 rounded-2xl p-4 text-center">
            <p class="text-2xl font-black text-red-700 dark:text-red-400" id="er-stat-critical">–</p>
            <p class="text-[10px] text-red-600 uppercase tracking-wider mt-1">Critical (Red)</p>
        </div>
        <div class="col-span-1 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 rounded-2xl p-4 text-center">
            <p class="text-2xl font-black text-emerald-700 dark:text-emerald-400" id="er-stat-discharged">–</p>
            <p class="text-[10px] text-emerald-600 uppercase tracking-wider mt-1">Discharged Today</p>
        </div>
    </div>

    <!-- Emergency Cards Grid -->
    <div id="er-cards" class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4">
        <div class="col-span-3 text-center py-10 text-slate-400 text-xs">
            <i data-lucide="loader" class="w-6 h-6 mx-auto animate-spin mb-2"></i> Loading emergency cases...
        </div>
    </div>
</div>

<!-- Register Emergency Modal -->
<div id="er-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 w-full max-w-lg shadow-2xl overflow-y-auto max-h-[90vh]">
        <div class="flex items-center gap-3 mb-5">
            <div class="p-2 bg-red-100 dark:bg-red-500/10 text-red-600 rounded-xl"><i data-lucide="alert-circle" class="w-4 h-4"></i></div>
            <h3 class="text-base font-bold text-slate-800 dark:text-white">Register Emergency Case</h3>
        </div>
        <form id="er-form" onsubmit="submitEmergency(event)" class="space-y-4">
            <!-- Patient Search & Verification -->
            <div class="bg-slate-50 dark:bg-slate-950/40 p-4 border border-slate-200 dark:border-slate-800 rounded-2xl space-y-3">
                <label class="block text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Link Patient Record <span class="text-slate-400 text-[9px]">(optional, leave blank for unknown/walk-in)</span></label>
                <div class="flex gap-2">
                    <input type="text" id="er-patient-search-code" placeholder="Enter Hospital Code or Patient ID" 
                           class="flex-grow bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-850 dark:text-slate-205 focus:outline-none focus:ring-1 focus:ring-emerald-500 font-mono tracking-wide">
                    <button type="button" onclick="lookupEmergencyPatient()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1 shrink-0 cursor-pointer">
                        Lookup
                    </button>
                </div>
                <!-- Patient Card Preview -->
                <div id="er-patient-preview" class="hidden p-3 rounded-xl border border-red-500/20 bg-red-500/5 flex items-center justify-between">
                    <div>
                        <h4 class="text-xs font-black text-slate-800 dark:text-white" id="er-preview-name">Name</h4>
                        <p class="text-[10px] text-slate-500 dark:text-slate-400">Gender: <span id="er-preview-gender"></span> | DOB: <span id="er-preview-dob"></span></p>
                    </div>
                    <span class="text-[9px] bg-red-500/20 text-red-700 dark:text-red-400 font-mono font-bold px-2 py-0.5 rounded" id="er-preview-code">Code</span>
                </div>
                <!-- Hidden Patient ID -->
                <input type="hidden" id="er-patient-id">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="er-label">Triage Level <span class="text-red-500">*</span></label>
                    <select id="er-triage" required class="er-input w-full">
                        <option value="red">🔴 Red – Critical / Immediate</option>
                        <option value="yellow">🟡 Yellow – Urgent</option>
                        <option value="green">🟢 Green – Minor / Non-urgent</option>
                        <option value="black">⚫ Black – Expectant / Deceased</option>
                    </select>
                </div>
                <div>
                    <label class="er-label">Mode of Arrival <span class="text-red-500">*</span></label>
                    <select id="er-arrival" required class="er-input w-full">
                        <option value="walk-in">Walk-in</option>
                        <option value="ambulance">Ambulance</option>
                        <option value="police">Police</option>
                        <option value="referred">Referred</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="er-label">Chief Complaint <span class="text-red-500">*</span></label>
                <input type="text" id="er-complaint" required placeholder="Main presenting complaint" class="er-input w-full">
            </div>
            <div>
                <label class="er-label">Presenting Symptoms</label>
                <textarea id="er-symptoms" rows="2" placeholder="Describe symptoms in detail..." class="er-input w-full resize-none"></textarea>
            </div>

            <!-- Vitals -->
            <div class="border border-slate-200 dark:border-slate-700 rounded-xl p-4">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-3">Initial Vitals & Assignment</p>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="er-label">BP (mmHg)</label>
                        <input type="text" id="er-bp" placeholder="120/80" class="er-input w-full">
                    </div>
                    <div>
                        <label class="er-label">Temp (°C)</label>
                        <input type="number" id="er-temp" step="0.1" placeholder="36.5" class="er-input w-full">
                    </div>
                    <div>
                        <label class="er-label">Pulse (bpm)</label>
                        <input type="number" id="er-pulse" placeholder="72" class="er-input w-full">
                    </div>
                    <div>
                        <label class="er-label">SpO2 (%)</label>
                        <input type="number" id="er-spo2" placeholder="98" class="er-input w-full">
                    </div>
                    <div>
                        <label class="er-label">GCS (3-15)</label>
                        <input type="number" id="er-gcs" min="3" max="15" placeholder="15" class="er-input w-full">
                    </div>
                    <div class="col-span-3">
                        <label class="er-label font-bold text-slate-800 dark:text-white">Attending Doctor / Staff</label>
                        <select id="er-staff-id" class="er-input w-full">
                            <option value="">– Select Staff –</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeERModal()" class="flex-1 px-4 py-2 text-xs font-semibold border border-slate-200 dark:border-slate-700 rounded-xl text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition">Cancel</button>
                <button type="submit" class="flex-1 bg-red-600 hover:bg-red-700 text-white text-xs font-bold px-4 py-2 rounded-xl transition">Register Emergency</button>
            </div>
        </form>
    </div>
</div>

<!-- Update ER Status Modal -->
<div id="er-update-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 w-full max-w-md shadow-2xl overflow-y-auto max-h-[90vh]">
        <h3 class="text-base font-bold text-slate-800 dark:text-white mb-4">Update Emergency Case</h3>
        <input type="hidden" id="er-update-id">
        <div class="space-y-3">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="er-label">Triage Level</label>
                    <select id="er-update-triage" class="er-input w-full">
                        <option value="red">🔴 Red – Critical</option>
                        <option value="yellow">🟡 Yellow – Urgent</option>
                        <option value="green">🟢 Green – Minor</option>
                        <option value="black">⚫ Black – Expectant</option>
                    </select>
                </div>
                <div>
                    <label class="er-label">Status</label>
                    <select id="er-update-status" class="er-input w-full">
                        <option value="waiting">Waiting</option>
                        <option value="in_treatment">In Treatment</option>
                        <option value="admitted">Admitted</option>
                        <option value="discharged">Discharged</option>
                        <option value="deceased">Deceased</option>
                        <option value="transferred">Transferred</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="er-label">Treatment Notes</label>
                <textarea id="er-update-treatment" rows="3" placeholder="Document treatment actions..." class="er-input w-full resize-none"></textarea>
            </div>
            <div>
                <label class="er-label">Disposition Notes</label>
                <textarea id="er-update-disposition" rows="2" placeholder="Disposition decision..." class="er-input w-full resize-none"></textarea>
            </div>
        </div>
        <div class="flex gap-3 mt-5">
            <button onclick="closeERUpdateModal()" class="flex-1 px-4 py-2 text-xs font-semibold border border-slate-200 dark:border-slate-700 rounded-xl text-slate-700 dark:text-slate-300 hover:bg-slate-50 transition">Cancel</button>
            <button onclick="submitERUpdate()" class="flex-1 bg-red-600 hover:bg-red-700 text-white text-xs font-bold px-4 py-2 rounded-xl transition">Save Update</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<style>
    .er-input {
        font-size: 0.75rem;
        padding: 0.5rem 0.75rem;
        border-radius: 0.75rem;
        border: 1px solid #e2e8f0;
        background-color: #f8fafc;
        color: #1e293b;
        outline: none;
        transition: all 0.2s;
    }
    .dark .er-input {
        border-color: #1e293b;
        background-color: #020617;
        color: #e2e8f0;
    }
    .er-input:focus {
        border-color: #ef4444;
        box-shadow: 0 0 0 1px #ef4444;
    }
    .er-label {
        display: block;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #1e293b;
        margin-bottom: 0.25rem;
    }
    .dark .er-label {
        color: #e2e8f0;
    }
</style>
<script>
    const triageConfig = {
        red:    { label: '🔴 Critical',  card: 'border-red-400 dark:border-red-500/60',    badge: 'bg-red-600 text-white',    border: 'border-l-4 border-red-500' },
        yellow: { label: '🟡 Urgent',    card: 'border-amber-400 dark:border-amber-500/60', badge: 'bg-amber-500 text-white',   border: 'border-l-4 border-amber-500' },
        green:  { label: '🟢 Minor',     card: 'border-emerald-400 dark:border-emerald-500/60', badge: 'bg-emerald-600 text-white', border: 'border-l-4 border-emerald-500' },
        black:  { label: '⚫ Expectant', card: 'border-slate-500 dark:border-slate-600',   badge: 'bg-slate-700 text-white',   border: 'border-l-4 border-slate-600' },
    };

    const erStatusColors = {
        waiting:      'bg-amber-100 text-amber-800 dark:bg-amber-500/20 dark:text-amber-400',
        in_treatment: 'bg-blue-100 text-blue-800 dark:bg-blue-500/20 dark:text-blue-400',
        admitted:     'bg-violet-100 text-violet-800 dark:bg-violet-500/20 dark:text-violet-400',
        discharged:   'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-400',
        deceased:     'bg-slate-300 text-slate-700 dark:bg-slate-700 dark:text-slate-400',
        transferred:  'bg-teal-100 text-teal-800 dark:bg-teal-500/20 dark:text-teal-400',
    };

    async function loadEmergencies() {
        const status = document.getElementById('er-status-filter').value;
        const grid   = document.getElementById('er-cards');
        grid.innerHTML = `<div class="col-span-3 text-center py-10 text-slate-400 text-xs"><i data-lucide="loader" class="w-6 h-6 mx-auto animate-spin mb-2"></i>Loading...</div>`;
        lucide.createIcons();

        try {
            const data = await window.api.get(`/emergencies?status=${status}`);
            const list  = data.emergencies || [];
            const stats = data.stats       || {};

            document.getElementById('er-stat-today').textContent     = stats.today_total       ?? 0;
            document.getElementById('er-stat-waiting').textContent   = stats.waiting           ?? 0;
            document.getElementById('er-stat-treatment').textContent = stats.in_treatment      ?? 0;
            document.getElementById('er-stat-critical').textContent  = stats.critical_red      ?? 0;
            document.getElementById('er-stat-discharged').textContent= stats.today_discharged  ?? 0;

            if (!list.length) {
                grid.innerHTML = `<div class="col-span-3 text-center py-14 text-slate-400 text-xs"><i data-lucide="check-circle-2" class="w-10 h-10 mx-auto mb-2 opacity-30 text-emerald-500"></i><p class="font-bold text-slate-600 dark:text-slate-300 text-sm">No active emergencies</p><p>The ER is currently clear.</p></div>`;
                lucide.createIcons(); return;
            }

            grid.innerHTML = list.map(em => {
                const tc = triageConfig[em.triage_level] || triageConfig.green;
                const vitalsList = [
                    em.vitals?.bp    ? `BP: ${em.vitals.bp}` : '',
                    em.vitals?.temp  ? `Temp: ${em.vitals.temp}°C` : '',
                    em.vitals?.pulse ? `Pulse: ${em.vitals.pulse}` : '',
                    em.vitals?.spo2  ? `SpO₂: ${em.vitals.spo2}%` : '',
                    em.vitals?.gcs   ? `GCS: ${em.vitals.gcs}` : '',
                ].filter(Boolean).join(' · ');

                return `<div class="bg-white dark:bg-slate-900 border ${tc.card} rounded-2xl shadow-sm overflow-hidden ${tc.border}">
                    <div class="px-5 pt-4 pb-2 flex items-start justify-between">
                        <div>
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-black ${tc.badge}">${tc.label}</span>
                            <h3 class="font-bold text-slate-800 dark:text-white text-sm mt-2">${em.patient_name}</h3>
                            <p class="text-[10px] text-slate-400 font-mono">${em.hospital_number} · ${em.gender} · ${em.age}y</p>
                        </div>
                        <span class="px-2 py-1 rounded-full text-[10px] font-bold shrink-0 ${erStatusColors[em.status] || ''}">${em.status.replace(/_/g,' ')}</span>
                    </div>
                    <div class="px-5 pb-3 space-y-1.5 border-t border-slate-100 dark:border-slate-800 pt-3">
                        <p class="text-xs"><span class="font-bold text-slate-500">Complaint: </span><span class="text-slate-700 dark:text-slate-300">${em.chief_complaint}</span></p>
                        <p class="text-xs"><span class="font-bold text-slate-500">Doctor: </span><span class="text-slate-700 dark:text-slate-300">${em.attending_doctor}</span></p>
                        <p class="text-xs"><span class="font-bold text-slate-500">Arrival: </span><span class="text-slate-600 dark:text-slate-400">${em.arrived_at} · ${em.mode_of_arrival}</span></p>
                        ${vitalsList ? `<p class="text-[10px] text-slate-400 mt-1">${vitalsList}</p>` : ''}
                        <p class="text-[10px] text-slate-400">Wait: <span class="font-bold text-amber-600">${em.wait_minutes}m</span></p>
                    </div>
                    <div class="px-5 pb-4 flex gap-2">
                        <button onclick="openERUpdateModal(${em.id}, '${em.triage_level}', '${em.status}')" class="flex-1 text-[10px] font-bold border border-red-300 dark:border-red-500/30 text-red-600 dark:text-red-400 py-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-500/10 transition">Update Case</button>
                    </div>
                </div>`;
            }).join('');
            lucide.createIcons();
        } catch(e) {
            grid.innerHTML = `<div class="col-span-3 text-center text-red-400 text-xs py-10">${e.message}</div>`;
        }
    }

    async function loadERStaff() {
        try {
            const res = await window.api.get('/emergencies/staff');
            const sel = document.getElementById('er-staff-id');
            sel.innerHTML = '<option value="">– Select Staff –</option>';
            (res.staff || []).forEach(s => {
                const opt = document.createElement('option');
                opt.value = s.id;
                opt.textContent = `${s.name} (${s.role})`;
                sel.appendChild(opt);
            });
        } catch(e) {
            console.error('Failed to load ER staff:', e);
        }
    }

    async function lookupEmergencyPatient() {
        const input = document.getElementById('er-patient-search-code').value.trim();
        const preview = document.getElementById('er-patient-preview');
        const hiddenId = document.getElementById('er-patient-id');

        if (!input) {
            alert('Please enter a Hospital Code or Patient ID first.');
            return;
        }

        preview.classList.add('hidden');
        hiddenId.value = '';

        try {
            let patient = null;

            // 1. If input is purely numeric, try loading by ID
            if (/^\d+$/.test(input)) {
                try {
                    const res = await window.api.get(`/patients/${input}`);
                    if (res && res.patient) {
                        patient = res.patient;
                    }
                } catch (err) {
                    // fall back to search
                }
            }

            // 2. Fallback to code/general search
            if (!patient) {
                const res = await window.api.get(`/patients?search=${encodeURIComponent(input)}`);
                const list = res.patients || [];
                if (list.length > 0) {
                    patient = list.find(p => p.immigration_service_number && p.immigration_service_number.toLowerCase() === input.toLowerCase()) || list[0];
                }
            }

            if (patient) {
                hiddenId.value = patient.id;
                document.getElementById('er-preview-name').innerText = patient.full_name;
                document.getElementById('er-preview-gender').innerText = patient.gender;
                document.getElementById('er-preview-dob').innerText = patient.date_of_birth;
                document.getElementById('er-preview-code').innerText = patient.immigration_service_number;
                preview.classList.remove('hidden');
            } else {
                alert('No patient record found matching the input.');
            }
        } catch (e) {
            alert('Lookup failed: ' + e.message);
        }
    }

    function openNewERModal()  { 
        loadERStaff();
        document.getElementById('er-form').reset();
        document.getElementById('er-patient-preview').classList.add('hidden');
        document.getElementById('er-patient-id').value = '';
        document.getElementById('er-modal').classList.remove('hidden'); 
    }
    function closeERModal()    { document.getElementById('er-modal').classList.add('hidden'); }
    
    function openERUpdateModal(id, triage, status) {
        document.getElementById('er-update-id').value          = id;
        document.getElementById('er-update-triage').value      = triage;
        document.getElementById('er-update-status').value      = status;
        document.getElementById('er-update-treatment').value   = '';
        document.getElementById('er-update-disposition').value = '';
        document.getElementById('er-update-modal').classList.remove('hidden');
    }
    function closeERUpdateModal() { document.getElementById('er-update-modal').classList.add('hidden'); }

    async function submitEmergency(e) {
        e.preventDefault();
        const payload = {
            triage_level:      document.getElementById('er-triage').value,
            mode_of_arrival:   document.getElementById('er-arrival').value,
            chief_complaint:   document.getElementById('er-complaint').value,
            presenting_symptoms: document.getElementById('er-symptoms').value,
            vitals_bp:   document.getElementById('er-bp').value    || null,
            vitals_temp: document.getElementById('er-temp').value  || null,
            vitals_pulse:document.getElementById('er-pulse').value || null,
            vitals_spo2: document.getElementById('er-spo2').value  || null,
            vitals_gcs:  document.getElementById('er-gcs').value   || null,
            staff_id:    document.getElementById('er-staff-id').value || null,
        };
        const patId = document.getElementById('er-patient-id').value;
        if (patId) payload.patient_id = patId;

        try {
            await window.api.post('/emergencies', payload);
            closeERModal();
            loadEmergencies();
            alert('Emergency case registered!');
        } catch(e) { alert('Error: ' + e.message); }
    }

    async function submitERUpdate() {
        const id = document.getElementById('er-update-id').value;
        const payload = {
            triage_level:       document.getElementById('er-update-triage').value,
            status:             document.getElementById('er-update-status').value,
            treatment_notes:    document.getElementById('er-update-treatment').value,
            disposition_notes:  document.getElementById('er-update-disposition').value,
        };
        try {
            await window.api.put(`/emergencies/${id}`, payload);
            closeERUpdateModal();
            loadEmergencies();
        } catch(e) { alert('Error: ' + e.message); }
    }

    // Auto-refresh every 30 seconds for live ER tracking
    document.addEventListener('DOMContentLoaded', () => {
        loadEmergencies();
        setInterval(loadEmergencies, 30000);
    });
</script>
@endsection
