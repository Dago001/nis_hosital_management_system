@extends('layouts.app')

@section('title', 'Consultation - NIS Medical Services Portal')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">
    <!-- Title -->
    <div>
        <h1 class="text-xl font-bold text-slate-800 dark:text-white flex items-center gap-2">
            <i data-lucide="stethoscope" class="text-emerald-600"></i> Clinical Encounter Consultation SOAP Log
        </h1>
        <p class="text-xs text-slate-850 dark:text-slate-200">Doctor portal to log clinical findings, record SOAP notes, prescribe medications, and order lab diagnostics</p>
    </div>

    <!-- Active Visit Selector -->
    <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider block">Assigned Doctor Queue (Direct Postings)</label>
                <select id="visit-selector" onchange="handleVisitSelect(this.value)" class="w-full mt-1.5 px-3 py-2.5 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 rounded-xl text-xs text-slate-800 dark:text-slate-100 focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                    <option value="">Choose Patient Waiting File...</option>
                </select>
            </div>
            <div>
                <label class="text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider block">Unlock Any Patient File by Hospital Code</label>
                <div class="flex gap-2 mt-1.5">
                    <input type="text" id="consult-patient-search" placeholder="Enter Hospital Code e.g. NIS/PAT/000001" 
                           class="flex-grow bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2.5 text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-emerald-500 font-mono tracking-wide">
                    <button type="button" onclick="unlockPatientFile()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-1 shrink-0 cursor-pointer">
                        Unlock File
                    </button>
                </div>
            </div>
        </div>

        <!-- Triage Vitals Display Panel -->
        <div id="vitals-display-panel" class="hidden p-4 bg-emerald-500/5 border border-emerald-500/10 rounded-xl grid grid-cols-2 sm:grid-cols-6 gap-4">
            <div>
                <span class="text-[9px] font-bold text-slate-800 dark:text-slate-200 uppercase block">Blood Pressure</span>
                <span class="text-xs font-bold text-slate-900 dark:text-white" id="vit-bp">·</span>
            </div>
            <div>
                <span class="text-[9px] font-bold text-slate-800 dark:text-slate-200 uppercase block">Temperature</span>
                <span class="text-xs font-bold text-slate-900 dark:text-white" id="vit-temp">·</span>
            </div>
            <div>
                <span class="text-[9px] font-bold text-slate-800 dark:text-slate-200 uppercase block">Pulse Rate</span>
                <span class="text-xs font-bold text-slate-900 dark:text-white" id="vit-pulse">·</span>
            </div>
            <div>
                <span class="text-[9px] font-bold text-slate-800 dark:text-slate-200 uppercase block">Resp. Rate</span>
                <span class="text-xs font-bold text-slate-900 dark:text-white" id="vit-resp">·</span>
            </div>
            <div>
                <span class="text-[9px] font-bold text-slate-800 dark:text-slate-200 uppercase block">Weight</span>
                <span class="text-xs font-bold text-slate-900 dark:text-white" id="vit-weight">·</span>
            </div>
            <div>
                <span class="text-[9px] font-bold text-slate-800 dark:text-slate-200 uppercase block">Height</span>
                <span class="text-xs font-bold text-slate-900 dark:text-white" id="vit-height">·</span>
            </div>
        </div>
    </div>

    <!-- 3-Column Layout: Left Form (col-span-2) & Right AI Chat (col-span-1) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        <!-- Left Column: SOAP Encounter Form (Direct Form Grid Item) -->
        <form id="encounter-form" onsubmit="handleConsultSubmit(event)" class="lg:col-span-2 space-y-6 hidden">
        
        <!-- Completed Diagnostics Display Panel -->
        <div id="diagnostics-display-panel" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 rounded-xl bg-violet-500/10 text-violet-600">
                        <i data-lucide="clipboard-list" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-slate-850 dark:text-white">Patient Completed Diagnostic Results</h3>
                        <p class="text-[9px] text-slate-500">Approved laboratory findings & radiologist reports</p>
                    </div>
                </div>
                <span class="text-[9px] bg-violet-500/10 text-violet-600 dark:bg-violet-500/20 font-bold px-2.5 py-1 rounded-xl" id="diag-count">0 Approved Results</span>
            </div>
            
            <div id="diagnostics-list-container" class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-72 overflow-y-auto pr-1">
                <div class="col-span-2 text-center py-6 text-slate-400 text-[10px]">
                    No approved diagnostic test reports found for this patient.
                </div>
            </div>
        </div>

        <!-- SOAP Notes -->
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
            <h3 class="text-xs font-bold text-slate-800 dark:text-white uppercase tracking-wider mb-2">Subjective, Objective, Assessment & Planning (SOAP)</h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1">Chief Complaint *</label>
                    <input type="text" id="chief_complaint" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1">History of Present Illness (HPI)</label>
                    <textarea id="history" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500" rows="2"></textarea>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1">Subjective (Patient symptoms summary) *</label>
                    <textarea id="soap_notes_subjective" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500" rows="3"></textarea>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1">Objective (Physical examination findings) *</label>
                    <textarea id="soap_notes_objective" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500" rows="3"></textarea>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1">Assessment (Clinical assessment/severity) *</label>
                    <textarea id="soap_notes_assessment" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500" rows="3"></textarea>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1">Plan (Medical orders planning) *</label>
                    <textarea id="soap_notes_plan" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500" rows="3"></textarea>
                </div>
            </div>
        </div>

        <!-- Diagnosis Codes -->
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
            <h3 class="text-xs font-bold text-slate-800 dark:text-white uppercase tracking-wider mb-2">Diagnoses & ICD-10 Coding</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1">ICD-10 Code *</label>
                    <input type="text" id="diagnosis_icd10" placeholder="e.g. B50.9" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1">Diagnosis Description *</label>
                    <input type="text" id="diagnosis_description" placeholder="e.g. Plasmodium falciparum malaria, unspecified" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>
            </div>
        </div>

        <!-- Diagnostic Lab Tests Order -->
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
            <h3 class="text-xs font-bold text-slate-800 dark:text-white uppercase tracking-wider mb-2">Order Lab / Radiology Tests</h3>
            
            <div class="flex gap-3">
                <input type="text" id="lab_test_name" placeholder="e.g. Malaria Parasite (MP) or Chest X-Ray" class="flex-1 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                <button type="button" onclick="handleAddLabTest()" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold px-4 py-2 rounded-xl transition">
                    Add Test
                </button>
            </div>

            <!-- Lab tests ordered table -->
            <div class="mt-3 overflow-hidden rounded-xl border border-slate-100 dark:border-slate-800 hidden" id="lab-table-container">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-950">
                        <tr>
                            <th class="p-3 font-bold">Requested Diagnostic Test</th>
                            <th class="p-3 font-bold text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody id="lab-items-body" class="divide-y divide-slate-100 dark:divide-slate-800">
                        <!-- Injected -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Prescriptions Order -->
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
            <h3 class="text-xs font-bold text-slate-800 dark:text-white uppercase tracking-wider mb-2">Order Prescription Medications</h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-6 gap-3">
                <div class="sm:col-span-2">
                    <label class="block text-[9px] font-bold text-slate-800 dark:text-slate-200">Drug Name</label>
                    <input type="text" id="drug_name" placeholder="e.g. Coartem" class="w-full mt-1 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-2.5 py-1.5 text-xs text-slate-800 dark:text-slate-200">
                </div>
                <div>
                    <label class="block text-[9px] font-bold text-slate-800 dark:text-slate-200">Dosage</label>
                    <input type="text" id="dosage" placeholder="e.g. 20/120mg" class="w-full mt-1 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-2.5 py-1.5 text-xs text-slate-800 dark:text-slate-200">
                </div>
                <div>
                    <label class="block text-[9px] font-bold text-slate-800 dark:text-slate-200">Frequency</label>
                    <input type="text" id="frequency" placeholder="e.g. Twice daily" class="w-full mt-1 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-2.5 py-1.5 text-xs text-slate-800 dark:text-slate-200">
                </div>
                <div>
                    <label class="block text-[9px] font-bold text-slate-800 dark:text-slate-200">Duration (Days)</label>
                    <input type="number" id="duration" placeholder="3" class="w-full mt-1 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-2.5 py-1.5 text-xs text-slate-800 dark:text-slate-200">
                </div>
                <div>
                    <label class="block text-[9px] font-bold text-slate-800 dark:text-slate-200">Total Qty</label>
                    <input type="number" id="qty" placeholder="24" class="w-full mt-1 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-2.5 py-1.5 text-xs text-slate-800 dark:text-slate-200">
                </div>
                <div class="sm:col-span-5">
                    <label class="block text-[9px] font-bold text-slate-800 dark:text-slate-200">Special Instructions</label>
                    <input type="text" id="drug_instructions" placeholder="Take with fatty meal" class="w-full mt-1 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-2.5 py-1.5 text-xs text-slate-800 dark:text-slate-200">
                </div>
                <div class="flex items-end">
                    <button type="button" onclick="handleAddPrescription()" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold py-2 rounded-xl transition">
                        Add Drug
                    </button>
                </div>
            </div>

            <!-- Prescription Items Table -->
            <div class="mt-3 overflow-hidden rounded-xl border border-slate-100 dark:border-slate-800 hidden" id="prescription-table-container">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-950">
                        <tr>
                            <th class="p-3 font-bold">Drug Details</th>
                            <th class="p-3 font-bold">Dosage & Frequency</th>
                            <th class="p-3 font-bold">Duration</th>
                            <th class="p-3 font-bold">Total Qty</th>
                            <th class="p-3 font-bold text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody id="prescription-items-body" class="divide-y divide-slate-100 dark:divide-slate-800">
                        <!-- Injected -->
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <button type="submit" id="submit-btn" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 text-xs font-bold rounded-xl shadow-lg shadow-emerald-600/10 transition cursor-pointer">
                Complete Encounter Consultation
            </button>
        </div>
    </form>

<!-- Right Column: Clinical AI Advisor Chat Widget -->
<div class="lg:col-span-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 shadow-sm space-y-4 lg:sticky lg:top-6">
     <div class="flex items-center gap-2 pb-3 border-b border-slate-100 dark:border-slate-800">
          <div class="p-2 rounded-xl bg-emerald-500/10 text-emerald-600">
               <i data-lucide="bot" class="w-5 h-5"></i>
          </div>
          <div>
               <h3 class="text-xs font-bold text-slate-850 dark:text-white">Clinical AI Advisor</h3>
               <p class="text-[9px] text-slate-500">Real-time medical CDSS & dosage support</p>
          </div>
     </div>

     <!-- Predefined quick chips -->
     <div class="space-y-1.5">
          <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Quick Queries</span>
          <div class="flex flex-wrap gap-1.5">
               <button type="button" onclick="askAi('Pediatric Paracetamol dosage guide')" class="px-2 py-1 bg-slate-50 dark:bg-slate-950 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 text-slate-600 dark:text-slate-400 hover:text-emerald-600 border border-slate-200 dark:border-slate-805 hover:border-emerald-500/20 rounded-lg text-[9px] transition font-medium">Pediatric Dosage</button>
               <button type="button" onclick="askAi('Check NSAID and ACE Inhibitor drug interactions')" class="px-2 py-1 bg-slate-50 dark:bg-slate-950 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 text-slate-600 dark:text-slate-400 hover:text-emerald-600 border border-slate-200 dark:border-slate-805 hover:border-emerald-500/20 rounded-lg text-[9px] transition font-medium">Drug Interactions</button>
               <button type="button" onclick="askAi('ICD-10 coding cheat sheet')" class="px-2 py-1 bg-slate-50 dark:bg-slate-950 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 text-slate-600 dark:text-slate-400 hover:text-emerald-600 border border-slate-200 dark:border-slate-805 hover:border-emerald-500/20 rounded-lg text-[9px] transition font-medium">ICD-10 Finder</button>
               <button type="button" onclick="askAi('WHO malaria treatment protocol')" class="px-2 py-1 bg-slate-50 dark:bg-slate-950 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 text-slate-600 dark:text-slate-400 hover:text-emerald-600 border border-slate-200 dark:border-slate-805 hover:border-emerald-500/20 rounded-lg text-[9px] transition font-medium">Malaria Protocol</button>
          </div>
     </div>

     <!-- Chat Log Area -->
     <div id="ai-chat-log" class="h-80 overflow-y-auto border border-slate-100 dark:border-slate-850 bg-slate-50/50 dark:bg-slate-950 rounded-2xl p-3.5 space-y-3 scrollbar-thin">
          <div class="flex gap-2 items-start text-[11px] bg-white dark:bg-slate-900 p-2.5 rounded-xl border border-slate-150 dark:border-slate-850">
               <div class="p-1 rounded-lg bg-emerald-500/10 text-emerald-600 shrink-0">
                    <i data-lucide="bot" class="w-3.5 h-3.5"></i>
               </div>
               <p class="text-slate-600 dark:text-slate-350 leading-relaxed">
                    Hello. I am your Clinical AI Advisor. Ask me about drug dosages, clinical interactions, diagnostic findings, or ICD-10 codes.
               </p>
          </div>
     </div>

     <!-- Input Chat Form -->
     <form id="ai-chat-form" onsubmit="handleAiSubmit(event)" class="flex gap-2">
          <input type="text" id="ai-query-input" placeholder="Ask AI Advisor..." required 
                 class="flex-1 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 rounded-xl text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-emerald-500 placeholder-slate-400">
          <button type="submit" id="ai-send-btn" class="bg-emerald-600 hover:bg-emerald-700 text-white p-2 rounded-xl transition shadow-md shrink-0 flex items-center justify-center">
               <i data-lucide="send" class="w-4 h-4"></i>
          </button>
     </form>
</div>
</div>
</div>
@endsection

@section('scripts')
<script>
    let activeVisits = [];
    let prescribedDrugs = [];
    let orderedLabs = [];

    async function loadAssignedVisits() {
        try {
            // Load dashboard queue which returns doctor's assigned queue
            const res = await api.get('/dashboard');
            activeVisits = res.queue || [];

            const selector = document.getElementById('visit-selector');
            selector.innerHTML = '<option value="">Choose Patient Waiting File...</option>' + activeVisits.map(v => `
                <option value="${v.id}">${v.patient.first_name} ${v.patient.last_name} (${v.patient.immigration_service_number})</option>
            `).join('');

            // Pre-select visit if visit_id passed in URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            const visitIdParam = urlParams.get('visit_id');
            if (visitIdParam) {
                selector.value = visitIdParam;
                handleVisitSelect(visitIdParam);
            }
        } catch (err) {
            console.error('Failed to load active waiting queue:', err);
        }
    }

    async function unlockPatientFile() {
        const code = document.getElementById('consult-patient-search').value.trim();
        if (code.length < 3) {
            alert('Please enter a valid Patient Hospital Code (at least 3 characters).');
            return;
        }

        try {
            // 1. Search patient by code
            const searchRes = await api.get(`/patients?search=${encodeURIComponent(code)}`);
            const list = searchRes.patients || [];
            const patient = list.find(p => p.immigration_service_number.toLowerCase() === code.toLowerCase()) || list[0];

            if (!patient) {
                alert('No patient found matching the entered Hospital Code.');
                return;
            }

            // 2. Fetch active waiting consult file (visit with vitals but no consult notes)
            const visitRes = await api.get(`/clinical/active-visit/${patient.id}`);
            const visit = visitRes.visit;

            if (visit) {
                // Add to activeVisits list if not already there
                if (!activeVisits.find(v => v.id === visit.id)) {
                    activeVisits.push(visit);
                    
                    const selector = document.getElementById('visit-selector');
                    const opt = document.createElement('option');
                    opt.value = visit.id;
                    opt.textContent = `[UNLOCKED] ${visit.patient.first_name} ${visit.patient.last_name} (${visit.patient.immigration_service_number})`;
                    selector.appendChild(opt);
                }

                document.getElementById('visit-selector').value = visit.id;
                handleVisitSelect(visit.id);
                alert(`File for patient ${visit.patient.first_name} ${visit.patient.last_name} has been successfully unlocked!`);
            }
        } catch (err) {
            alert(err.message || 'Failed to unlock file. Ensure the patient has had their vitals recorded at Vitals Entry.');
        }
    }

    function handleVisitSelect(visitId) {
        const vitalsPanel = document.getElementById('vitals-display-panel');
        const form = document.getElementById('encounter-form');

        if (!visitId) {
            vitalsPanel.classList.add('hidden');
            form.classList.add('hidden');
            return;
        }

        const visit = activeVisits.find(v => v.id === parseInt(visitId));
        if (visit) {
            // Populate vitals
            document.getElementById('vit-bp').innerText = visit.vitals_blood_pressure || '·';
            document.getElementById('vit-temp').innerText = visit.vitals_temperature ? `${visit.vitals_temperature} °C` : '·';
            document.getElementById('vit-pulse').innerText = visit.vitals_pulse_rate ? `${visit.vitals_pulse_rate} bpm` : '·';
            document.getElementById('vit-resp').innerText = visit.vitals_respiratory_rate ? `${visit.vitals_respiratory_rate} cpm` : '·';
            document.getElementById('vit-weight').innerText = visit.vitals_weight ? `${visit.vitals_weight} kg` : '·';
            document.getElementById('vit-height').innerText = visit.vitals_height ? `${visit.vitals_height} cm` : '·';

            vitalsPanel.classList.remove('hidden');
            form.classList.remove('hidden');

            loadPatientDiagnostics(visit.patient_id);
        }
    }

    async function loadPatientDiagnostics(patientId) {
        const container = document.getElementById('diagnostics-list-container');
        const countBadge = document.getElementById('diag-count');

        try {
            const res = await api.get(`/patients/${patientId}`);
            const list = res.diagnostics || [];

            countBadge.innerText = `${list.length} Approved Report${list.length === 1 ? '' : 's'}`;

            if (list.length === 0) {
                container.innerHTML = `
                    <div class="col-span-2 text-center py-6 text-slate-400 text-[10px]">
                        No approved diagnostic test reports found for this patient.
                    </div>`;
                return;
            }

            container.innerHTML = list.map(d => {
                const isLab = d.type === 'lab';
                const typeBadge = isLab 
                    ? 'bg-violet-100 text-violet-850 dark:bg-violet-500/20 dark:text-violet-400 font-bold border border-violet-500/10' 
                    : 'bg-blue-100 text-blue-850 dark:bg-blue-500/20 dark:text-blue-400 font-bold border border-blue-500/10';

                return `
                    <div class="p-4 rounded-2xl border border-slate-150 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/20 space-y-2 text-xs">
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="px-2 py-0.5 rounded text-[9px] font-bold ${typeBadge}">
                                    ${d.type.toUpperCase()}
                                </span>
                                <h4 class="font-bold text-slate-850 dark:text-white mt-1.5">${d.test_name}</h4>
                            </div>
                            <span class="text-[9px] text-slate-400 font-mono">${d.completed_at}</span>
                        </div>
                        <div class="pt-1.5 border-t border-slate-100 dark:border-slate-800 space-y-1.5">
                            <p><span class="font-bold text-slate-500">Outcome:</span> <span class="text-slate-800 dark:text-slate-200 font-semibold">${d.result_value}</span></p>
                            ${isLab ? `<p><span class="font-bold text-slate-500">Ref Range:</span> <span class="text-slate-750 dark:text-slate-350 font-mono">${d.normal_range}</span></p>` : ''}
                            <p><span class="font-bold text-slate-500">Requested By:</span> <span class="text-slate-750 dark:text-slate-350">${d.doctor_name}</span></p>
                            <p><span class="font-bold text-slate-500">Signed Off By:</span> <span class="text-slate-750 dark:text-slate-350 font-semibold">${d.scientist_name}</span></p>
                            ${d.remarks && d.remarks !== 'No remarks' && d.remarks !== 'N/A' ? `<div class="bg-white dark:bg-slate-900/50 p-2 rounded-lg border border-slate-100 dark:border-slate-800/80 mt-1"><span class="font-bold text-slate-500">Remarks:</span> <span class="text-slate-650 dark:text-slate-400 italic">${d.remarks}</span></div>` : ''}
                        </div>
                    </div>`;
            }).join('');

            lucide.createIcons();
        } catch (err) {
            console.error('Failed to load patient diagnostics:', err);
            container.innerHTML = `
                <div class="col-span-2 text-center py-6 text-red-500 text-[10px]">
                    Failed to load diagnostic outcomes history.
                </div>`;
        }
    }

    // Prescription list updates
    function handleAddPrescription() {
        const drug = document.getElementById('drug_name').value;
        const dose = document.getElementById('dosage').value;
        const freq = document.getElementById('frequency').value;
        const dur = document.getElementById('duration').value;
        const qty = document.getElementById('qty').value;
        const inst = document.getElementById('drug_instructions').value || '';

        if (!drug || !dose || !freq || !dur || !qty) {
            alert('Please populate all required medication fields.');
            return;
        }

        prescribedDrugs.push({
            drug_name: drug,
            dosage: dose,
            frequency: freq,
            duration_days: parseInt(dur),
            quantity_prescribed: parseInt(qty),
            instructions: inst
        });

        // Reset inputs
        document.getElementById('drug_name').value = '';
        document.getElementById('dosage').value = '';
        document.getElementById('frequency').value = '';
        document.getElementById('duration').value = '';
        document.getElementById('qty').value = '';
        document.getElementById('drug_instructions').value = '';

        renderPrescriptionsTable();
    }

    function removePrescription(index) {
        prescribedDrugs.splice(index, 1);
        renderPrescriptionsTable();
    }

    function renderPrescriptionsTable() {
        const container = document.getElementById('prescription-table-container');
        const tbody = document.getElementById('prescription-items-body');

        if (prescribedDrugs.length > 0) {
            container.classList.remove('hidden');
            tbody.innerHTML = prescribedDrugs.map((d, index) => `
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                    <td class="p-3 font-bold text-slate-800 dark:text-white">
                        ${d.drug_name}
                        <div class="text-[9px] text-slate-500 font-normal">Inst: ${d.instructions || 'None'}</div>
                    </td>
                    <td class="p-3">${d.dosage} | ${d.frequency}</td>
                    <td class="p-3">${d.duration_days} days</td>
                    <td class="p-3 font-bold">${d.quantity_prescribed} units</td>
                    <td class="p-3 text-right">
                        <button type="button" onclick="removePrescription(${index})" class="text-red-500 hover:text-red-650 font-bold hover:underline">Delete</button>
                    </td>
                </tr>
            `).join('');
        } else {
            container.classList.add('hidden');
        }
    }

    // Diagnostic tests updates
    function handleAddLabTest() {
        const test = document.getElementById('lab_test_name').value;
        if (!test) return;

        orderedLabs.push({ test_name: test });
        document.getElementById('lab_test_name').value = '';

        renderLabsTable();
    }

    function removeLabTest(index) {
        orderedLabs.splice(index, 1);
        renderLabsTable();
    }

    function renderLabsTable() {
        const container = document.getElementById('lab-table-container');
        const tbody = document.getElementById('lab-items-body');

        if (orderedLabs.length > 0) {
            container.classList.remove('hidden');
            tbody.innerHTML = orderedLabs.map((l, index) => `
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                    <td class="p-3 font-bold text-slate-850 dark:text-white">${l.test_name}</td>
                    <td class="p-3 text-right">
                        <button type="button" onclick="removeLabTest(${index})" class="text-red-500 hover:text-red-650 font-bold hover:underline">Delete</button>
                    </td>
                </tr>
            `).join('');
        } else {
            container.classList.add('hidden');
        }
    }

    // Form submit
    async function runDrugSafetyCheck(patientId) {
        if (!prescribedDrugs.length || !patientId) return true;
        try {
            const res = await api.post('/clinical/drug-safety-check', {
                patient_id: patientId,
                drugs: prescribedDrugs.map(d => d.drug_name),
            });
            if (!res.has_alerts) return true;
            const lines = [];
            (res.allergy_alerts || []).forEach(a => lines.push('⚠ ALLERGY: ' + a.message));
            (res.interaction_alerts || []).forEach(i => lines.push('⚠ INTERACTION (' + i.severity + '): ' + i.message));
            return confirm('DRUG SAFETY ALERTS\n\n' + lines.join('\n\n') + '\n\nProceed with prescription anyway?');
        } catch (e) {
            return true; // never block on a check failure
        }
    }

    async function handleConsultSubmit(e) {
        e.preventDefault();
        const visitId = document.getElementById('visit-selector').value;
        if (!visitId) return;

        // Advisory drug-safety check before saving the prescription.
        const visit = activeVisits.find(v => v.id === parseInt(visitId));
        const proceed = await runDrugSafetyCheck(visit?.patient_id);
        if (!proceed) return;

        const submitBtn = document.getElementById('submit-btn');
        submitBtn.disabled = true;
        submitBtn.innerText = 'Completing encounter...';

        const payload = {
            chief_complaint: document.getElementById('chief_complaint').value,
            history: document.getElementById('history').value || null,
            soap_notes_subjective: document.getElementById('soap_notes_subjective').value,
            soap_notes_objective: document.getElementById('soap_notes_objective').value,
            soap_notes_assessment: document.getElementById('soap_notes_assessment').value,
            soap_notes_plan: document.getElementById('soap_notes_plan').value,
            diagnosis_icd10: document.getElementById('diagnosis_icd10').value,
            diagnosis_description: document.getElementById('diagnosis_description').value,
            prescriptions: prescribedDrugs,
            lab_tests: orderedLabs
        };

        try {
            await api.post(`/clinical/consult/${visitId}`, payload);
            alert('Consultation note saved successfully! Billing consultation fees & prescription costing alerts triggered.');
            
            // Reset page state
            prescribedDrugs = [];
            orderedLabs = [];
            renderPrescriptionsTable();
            renderLabsTable();
            document.getElementById('encounter-form').reset();
            document.getElementById('vitals-display-panel').classList.add('hidden');
            document.getElementById('encounter-form').classList.add('hidden');

            // Refresh visits dropdown
            loadAssignedVisits();
        } catch (err) {
            alert(err.message || 'Failed to submit consultation notes.');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerText = 'Complete Encounter Consultation';
        }
    }

    // Clinical AI Advisor chat logic
    async function askAi(queryText) {
        document.getElementById('ai-query-input').value = queryText;
        handleAiSubmit(new Event('submit'));
    }

    async function handleAiSubmit(e) {
        if (e) e.preventDefault();
        const input = document.getElementById('ai-query-input');
        const query = input.value.trim();
        if (!query) return;

        input.value = '';
        const chatLog = document.getElementById('ai-chat-log');
        const sendBtn = document.getElementById('ai-send-btn');
        sendBtn.disabled = true;

        // Append User Message
        chatLog.innerHTML += `
            <div class="flex gap-2 items-start text-[11px] justify-end">
                <div class="bg-emerald-605 text-white p-2.5 rounded-2xl max-w-[85%] shadow-sm">
                    <p class="leading-relaxed font-semibold">${htmlEntities(query)}</p>
                </div>
            </div>
        `;
        chatLog.scrollTop = chatLog.scrollHeight;

        // Append Loading bubble
        const loaderId = 'ai-loader-' + Date.now();
        chatLog.innerHTML += `
            <div class="flex gap-2 items-start text-[11px]" id="${loaderId}">
                <div class="p-1 rounded-lg bg-emerald-500/10 text-emerald-600 shrink-0">
                    <i data-lucide="bot" class="w-3.5 h-3.5"></i>
                </div>
                <div class="bg-white dark:bg-slate-900 border border-slate-150 dark:border-slate-850 p-2.5 rounded-xl flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 bg-emerald-600 rounded-full animate-bounce"></span>
                    <span class="w-1.5 h-1.5 bg-emerald-600 rounded-full animate-bounce [animation-delay:0.2s]"></span>
                    <span class="w-1.5 h-1.5 bg-emerald-600 rounded-full animate-bounce [animation-delay:0.4s]"></span>
                </div>
            </div>
        `;
        chatLog.scrollTop = chatLog.scrollHeight;
        lucide.createIcons();

        try {
            const res = await api.post('/clinical/ai-chat', { message: query });
            
            // Remove Loader
            const loader = document.getElementById(loaderId);
            if (loader) loader.remove();

            // Append AI Reply
            chatLog.innerHTML += `
                <div class="flex gap-2 items-start text-[11px]">
                    <div class="p-1 rounded-lg bg-emerald-500/10 text-emerald-600 shrink-0 mt-0.5">
                        <i data-lucide="bot" class="w-3.5 h-3.5"></i>
                    </div>
                    <div class="bg-white dark:bg-slate-900 border border-slate-150 dark:border-slate-850 p-3 rounded-2xl max-w-[85%] shadow-sm text-slate-700 dark:text-slate-350 space-y-1 overflow-x-auto">
                        ${formatMarkdown(res.reply)}
                    </div>
                </div>
            `;
            chatLog.scrollTop = chatLog.scrollHeight;
            lucide.createIcons();
        } catch (err) {
            // Remove Loader
            const loader = document.getElementById(loaderId);
            if (loader) loader.remove();

            // Append Error
            chatLog.innerHTML += `
                <div class="flex gap-2 items-start text-[11px]">
                    <div class="p-1 rounded-lg bg-red-500/10 text-red-500 shrink-0">
                        <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                    </div>
                    <div class="bg-red-500/5 border border-red-500/10 p-2.5 rounded-xl text-red-600 font-bold">
                        Failed to connect to AI Advisor. Please verify connectivity.
                    </div>
                </div>
            `;
            chatLog.scrollTop = chatLog.scrollHeight;
            lucide.createIcons();
        } finally {
            sendBtn.disabled = false;
        }
    }

    function formatMarkdown(text) {
        let html = text;
        // Replace Headings
        html = html.replace(/### (.*?)\n/g, '<h4 class="text-xs font-black text-emerald-650 dark:text-emerald-500 uppercase mt-2.5 mb-1.5">$1</h4>');
        // Replace bold
        html = html.replace(/\*\*(.*?)\*\*/g, '<strong class="font-extrabold text-slate-855 dark:text-white">$1</strong>');
        // Replace italic
        html = html.replace(/\*(.*?)\*/g, '<em class="italic text-slate-700 dark:text-slate-300">$1</em>');
        // Replace blockquotes
        html = html.replace(/> \* (.*?)\*/g, '<div class="p-2 border-l-2 border-amber-500 bg-amber-500/5 text-amber-600 text-[10px] rounded-r-lg my-2">$1</div>');
        // Replace code blocks
        html = html.replace(/`([^`]+)`/g, '<code class="font-mono font-bold text-[9px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 px-1 rounded">$1</code>');
        // Replace list items
        html = html.replace(/- (.*?)\n/g, '<li class="ml-3 list-disc text-slate-600 dark:text-slate-350 my-1">$1</li>');
        // Replace tables
        if (html.includes('|')) {
            const lines = html.split('\n');
            let inTable = false;
            let tableHtml = '<table class="w-full text-left text-[9px] border-collapse mt-2 mb-2"><tbody class="divide-y divide-slate-200 dark:divide-slate-800">';
            for (let i = 0; i < lines.length; i++) {
                const line = lines[i].trim();
                if (line.startsWith('|') && line.endsWith('|')) {
                    if (line.includes(':---') || line.includes('---:')) {
                        continue; // Skip separator line
                    }
                    inTable = true;
                    const cols = line.split('|').map(c => c.trim()).filter(c => c !== '');
                    const rowContent = cols.map(c => `<td class="py-1 px-1.5 border-b border-slate-100 dark:border-slate-800/40">${c}</td>`).join('');
                    tableHtml += `<tr>${rowContent}</tr>`;
                } else {
                    if (inTable) {
                        tableHtml += '</tbody></table>';
                        lines[i] = tableHtml + '\n' + lines[i];
                        inTable = false;
                        tableHtml = '<table class="w-full text-left text-[9px] border-collapse mt-2 mb-2"><tbody class="divide-y divide-slate-200 dark:divide-slate-800">';
                    }
                }
            }
            html = lines.join('\n');
        }
        // Replace double newlines
        html = html.replace(/\n\n/g, '<div class="h-2"></div>');
        // Replace single newlines
        html = html.replace(/\n/g, '<br/>');
        return html;
    }

    function htmlEntities(str) {
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    document.addEventListener('DOMContentLoaded', () => {
        loadAssignedVisits();
    });
</script>
@endsection
