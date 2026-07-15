@extends('layouts.app')

@section('title', 'Triage Vitals Entry - NIS Medical Services Portal')

@section('content')
<div class="space-y-6 max-w-2xl mx-auto">
    <div>
        <h1 class="text-xl font-bold text-slate-800 dark:text-white flex items-center gap-2">
            <i data-lucide="activity" class="text-emerald-600"></i> Vital Signs Monitoring Log
        </h1>
        <p class="text-xs text-slate-800 dark:text-slate-200">Nurse portal to capture primary triage variables and assign patients to doctor consultation queues</p>
    </div>

    <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <form id="vitals-form" onsubmit="handleSaveVitals(event)" class="space-y-6">
            <!-- Patient Search Bar -->
            <div class="bg-slate-50 dark:bg-slate-950/40 p-4 border border-slate-200 dark:border-slate-800 rounded-2xl mb-4 space-y-4">
                <div class="flex flex-col sm:flex-row items-end gap-3">
                    <div class="flex-grow">
                        <label class="block text-[10px] font-bold text-slate-800 dark:text-slate-205 uppercase tracking-wider mb-1.5">Search Patient by Hospital Code</label>
                        <div class="relative">
                            <input type="text" id="patient_search_code" placeholder="Enter Hospital Code e.g. NIS/PAT/000001" 
                                   class="w-full pl-10 pr-4 py-2.5 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 rounded-xl text-xs text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500 font-mono tracking-wide">
                            <i data-lucide="search" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400 w-4 h-4"></i>
                        </div>
                    </div>
                    <button type="button" onclick="handleSearchPatient()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 h-10 rounded-xl text-xs font-bold transition shadow-md flex items-center gap-1.5 shrink-0">
                        <i data-lucide="search" class="w-4 h-4"></i> Find Patient
                    </button>
                </div>

                <!-- Patient Card Preview -->
                <div id="patient-card-preview" class="hidden p-4 rounded-xl border border-emerald-500/20 bg-emerald-500/5 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="p-2 rounded-xl bg-emerald-500/10 text-emerald-600">
                            <i data-lucide="user" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-black text-slate-850 dark:text-white" id="preview-patient-name">Name</h4>
                            <p class="text-[10px] text-slate-500 dark:text-slate-400">Gender: <span id="preview-patient-gender"></span> | DOB: <span id="preview-patient-dob"></span></p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="text-[9px] bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 font-mono font-bold px-2.5 py-1 rounded" id="preview-patient-code">Code</span>
                    </div>
                </div>

                <!-- Hidden Input to store the active patient_id -->
                <input type="hidden" id="patient_id" required>
            </div>

            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Assigned Consultation Doctor *</label>
                    <select id="doctor_id" required class="w-full mt-1.5 px-3 py-2.5 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 rounded-xl text-xs text-slate-800 dark:text-slate-100 focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                        <option value="">Select Doctor...</option>
                    </select>
                </div>
            </div>

            <div class="border-t border-slate-100 dark:border-slate-800 pt-4 space-y-4">
                <h3 class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Triage Measurements</h3>
                
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="text-[10px] font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1">
                            <i data-lucide="heart" class="text-red-500 w-3 h-3"></i> Blood Pressure (mmHg) *
                        </label>
                        <input type="text" id="vitals_blood_pressure" required placeholder="e.g. 120/80"
                               class="w-full mt-1 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 rounded-xl text-xs text-slate-800 dark:text-slate-100">
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1">
                            <i data-lucide="thermometer" class="text-blue-500 w-3 h-3"></i> Temperature (°C) *
                        </label>
                        <input type="number" step="0.1" id="vitals_temperature" required placeholder="e.g. 36.8"
                               class="w-full mt-1 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 rounded-xl text-xs text-slate-800 dark:text-slate-100">
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-slate-800 dark:text-slate-200">Pulse Rate (bpm) *</label>
                        <input type="number" id="vitals_pulse_rate" required placeholder="e.g. 72"
                               class="w-full mt-1 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 rounded-xl text-xs text-slate-800 dark:text-slate-100">
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-slate-800 dark:text-slate-200">Respiratory Rate (cpm)</label>
                        <input type="number" id="vitals_respiratory_rate" placeholder="e.g. 18"
                               class="w-full mt-1 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 rounded-xl text-xs text-slate-800 dark:text-slate-100">
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-slate-800 dark:text-slate-200">Weight (kg)</label>
                        <input type="number" step="0.1" id="vitals_weight" placeholder="e.g. 75.4"
                               class="w-full mt-1 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 rounded-xl text-xs text-slate-800 dark:text-slate-100">
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-slate-800 dark:text-slate-200">Height (cm)</label>
                        <input type="number" id="vitals_height" placeholder="e.g. 175"
                               class="w-full mt-1 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 rounded-xl text-xs text-slate-800 dark:text-slate-100">
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-4 border-t border-slate-100 dark:border-slate-800">
                <button type="submit" id="submit-btn" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-xl text-xs font-semibold shadow-lg shadow-emerald-600/10 transition cursor-pointer">
                    Record Vital Signs
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    async function handleSearchPatient() {
        const query = document.getElementById('patient_search_code').value.trim();
        const card = document.getElementById('patient-card-preview');
        const hiddenId = document.getElementById('patient_id');
        
        if (query.length < 3) {
            alert('Please enter a valid unique Hospital Code (at least 3 characters).');
            return;
        }

        try {
            const res = await api.get(`/patients?search=${encodeURIComponent(query)}`);
            const list = res.patients;
            
            if (list.length > 0) {
                // Find exact match (case insensitive)
                const pat = list.find(p => p.immigration_service_number.toLowerCase() === query.toLowerCase()) || list[0];
                
                hiddenId.value = pat.id;
                document.getElementById('preview-patient-name').innerText = pat.full_name;
                document.getElementById('preview-patient-gender').innerText = pat.gender;
                document.getElementById('preview-patient-dob').innerText = pat.date_of_birth;
                document.getElementById('preview-patient-code').innerText = pat.immigration_service_number;
                
                card.classList.remove('hidden');
                lucide.createIcons();
            } else {
                hiddenId.value = '';
                card.classList.add('hidden');
                alert('No patient record found for the provided Hospital Code.');
            }
        } catch (err) {
            hiddenId.value = '';
            card.classList.add('hidden');
            alert('Failed to look up patient: ' + (err.message || 'error'));
        }
    }

    async function loadSetupData() {
        try {
            // Load doctors list
            const docRes = await api.get('/appointments/doctors');
            const docSelect = document.getElementById('doctor_id');
            docSelect.innerHTML = '<option value="">Select Doctor...</option>' + docRes.doctors.map(d => {
                const name = d.full_name || `${d.first_name} ${d.last_name}`;
                return `<option value="${d.id}">Dr. ${name} (${d.department?.name || 'GOPD'})</option>`;
            }).join('');
        } catch (err) {
            console.error('Failed to load triage setup databases:', err);
        }
    }

    async function handleSaveVitals(e) {
        e.preventDefault();
        
        const patientId = document.getElementById('patient_id').value;
        if (!patientId) {
            alert('Please search and verify a patient by their Hospital Code before recording vitals.');
            return;
        }

        const submitBtn = document.getElementById('submit-btn');
        submitBtn.disabled = true;
        submitBtn.innerText = 'Saving...';

        const payload = {
            patient_id: parseInt(patientId),
            department_id: 1, // GOPD Unit
            vitals_blood_pressure: document.getElementById('vitals_blood_pressure').value,
            vitals_temperature: parseFloat(document.getElementById('vitals_temperature').value),
            vitals_pulse_rate: parseInt(document.getElementById('vitals_pulse_rate').value),
            vitals_respiratory_rate: document.getElementById('vitals_respiratory_rate').value ? parseInt(document.getElementById('vitals_respiratory_rate').value) : null,
            vitals_weight: document.getElementById('vitals_weight').value ? parseFloat(document.getElementById('vitals_weight').value) : null,
            vitals_height: document.getElementById('vitals_height').value ? parseFloat(document.getElementById('vitals_height').value) : null,
            doctor_id: parseInt(document.getElementById('doctor_id').value)
        };

        try {
            await api.post('/clinical/vitals', payload);
            alert('Patient vital signs captured and forwarded to doctor consultation queue!');
            
            // Reset form
            document.getElementById('vitals-form').reset();
            document.getElementById('patient_id').value = '';
            document.getElementById('patient-card-preview').classList.add('hidden');
        } catch (err) {
            alert(err.message || 'Failed to save vital signs. Check input fields.');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerText = 'Record Vital Signs';
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        loadSetupData();
    });
</script>
@endsection
