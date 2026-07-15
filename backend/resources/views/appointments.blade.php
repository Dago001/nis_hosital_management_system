@extends('layouts.app')

@section('title', 'Appointments Queue - NIS Medical Services Portal')

@section('content')
<div class="space-y-6">
    <!-- Title & Action -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-white font-sans font-black">Appointments & Scheduling Board</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">Book outpatient consults, verify online booking requests, and dispatch email notices.</p>
        </div>
        <div id="book-btn-container" class="hidden">
            <button onclick="openBookModal()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-xl text-xs font-semibold flex items-center gap-2 shadow-lg shadow-emerald-600/10 transition cursor-pointer">
                <i data-lucide="plus" class="w-4 h-4"></i> Book Appointment
            </button>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="flex items-center gap-4 border-b border-slate-200 dark:border-slate-800 pb-2">
        <button id="tab-active-btn" onclick="switchTab('active')" class="text-xs font-bold pb-2 border-b-2 border-emerald-600 text-emerald-600 transition px-2">
            Active Appointments Queue
        </button>
        <button id="tab-requests-btn" onclick="switchTab('requests')" class="text-xs font-semibold pb-2 border-b-2 border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 transition px-2 flex items-center gap-1.5">
            Public Booking Requests
            <span id="requests-badge" class="hidden bg-emerald-600 text-white text-[9px] px-1.5 py-0.5 rounded-full">0</span>
        </button>
    </div>

    <!-- Active Appointments Queue View -->
    <div id="active-appointments-wrapper" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-200 border-b border-slate-100 dark:border-slate-800">
                    <tr>
                        <th class="py-3.5 px-6 font-bold">Queue No.</th>
                        <th class="py-3.5 px-6 font-bold">Patient Details</th>
                        <th class="py-3.5 px-6 font-bold">Assigned Doctor</th>
                        <th class="py-3.5 px-6 font-bold">Date & Time</th>
                        <th class="py-3.5 px-6 font-bold">Status</th>
                        <th class="py-3.5 px-6 font-bold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="appointments-table-body" class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-350">
                    <tr>
                        <td colSpan="6" class="py-8 text-center text-slate-800 dark:text-slate-200 text-sm">
                            <div class="w-6 h-6 border-2 border-emerald-600 border-t-transparent rounded-full animate-spin mx-auto"></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Public Booking Requests View -->
    <div id="public-requests-wrapper" class="hidden bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-200 border-b border-slate-100 dark:border-slate-800">
                    <tr>
                        <th class="py-3.5 px-6 font-bold">Requested Slot</th>
                        <th class="py-3.5 px-6 font-bold">Patient Name</th>
                        <th class="py-3.5 px-6 font-bold">Contact Info</th>
                        <th class="py-3.5 px-6 font-bold">Hospital Code</th>
                        <th class="py-3.5 px-6 font-bold">Chief Complaint</th>
                        <th class="py-3.5 px-6 font-bold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="requests-table-body" class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-350">
                    <tr>
                        <td colSpan="6" class="py-8 text-center text-slate-800 dark:text-slate-200 text-sm">
                            <div class="w-6 h-6 border-2 border-emerald-600 border-t-transparent rounded-full animate-spin mx-auto"></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Book Appointment Modal -->
<div id="book-modal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 w-full max-w-lg shadow-2xl relative my-8">
        <h3 class="text-base font-bold text-slate-800 dark:text-white mb-4">Book New Outpatient Consult</h3>
        
        <form id="book-form" onsubmit="handleBookSubmit(event)" class="space-y-4">
            <!-- Hospital Code Lookup Input -->
            <div>
                <label class="block text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1">Lookup Patient by Hospital Code</label>
                <div class="flex gap-2">
                    <input type="text" id="book-patient-search" placeholder="Enter Hospital Code e.g. NIS/PAT/123456" 
                           class="flex-grow bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500 font-mono tracking-wide">
                    <button type="button" onclick="handleBookPatientLookup()" class="bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-850 dark:text-white px-3 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1 shrink-0 cursor-pointer">
                        <i data-lucide="search" class="w-3.5 h-3.5"></i> Lookup
                    </button>
                </div>
            </div>

            <!-- Verified Patient Card Preview -->
            <input type="hidden" id="patient_id">
            <div id="book-patient-verify-card" class="hidden p-3.5 rounded-xl border border-slate-205 dark:border-slate-850 bg-slate-50 dark:bg-slate-950/40 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600">
                        <i data-lucide="user-check" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-800 dark:text-white" id="book-pat-name">John Doe</h4>
                        <p class="text-[9px] text-slate-500 dark:text-slate-400"><span id="book-pat-gender">Male</span> — DOB: <span id="book-pat-dob">1990-01-01</span></p>
                    </div>
                </div>
                <span class="text-[8px] bg-emerald-500/10 text-emerald-600 border border-emerald-500/20 font-bold px-2 py-0.5 rounded uppercase tracking-wider">Verified</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1">Target Clinic Department *</label>
                    <select id="department_id" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        <option value="">Choose Department...</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1">Assign Doctor (optional)</label>
                    <select id="doctor_id" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500 font-bold text-slate-600">
                        <option value="">-- Pending Vitals Triage --</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1">Appointment Date</label>
                    <input type="date" id="appointment_date" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1">Appointment Time</label>
                    <input type="time" id="appointment_time" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1">Encounter Notes</label>
                <textarea id="notes" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500" rows="3" placeholder="Chief complaints or comments..."></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800/80">
                <button type="button" onclick="closeBookModal()" class="px-4 py-2 text-xs font-semibold text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition">Cancel</button>
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 text-xs font-semibold rounded-xl transition shadow-md">Book Consult</button>
            </div>
        </form>
    </div>
</div>

<!-- Confirm Request Modal -->
<div id="confirm-request-modal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 w-full max-w-lg shadow-2xl relative my-8">
        <h3 class="text-base font-bold text-slate-800 dark:text-white mb-4">Confirm Appointment Request</h3>
        
        <form id="confirm-request-form" onsubmit="handleConfirmRequestSubmit(event)" class="space-y-4">
            <input type="hidden" id="confirm-request-id">
            
            <div class="bg-slate-50 dark:bg-slate-950 p-4 rounded-2xl border border-slate-100 dark:border-slate-800 text-xs space-y-2">
                <div>
                    <span class="font-bold text-slate-400 uppercase text-[9px] block">Patient Name</span>
                    <span id="confirm-patient-name" class="font-bold text-slate-800 dark:text-white"></span>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <span class="font-bold text-slate-400 uppercase text-[9px] block">Preferred Date</span>
                        <span id="confirm-requested-date" class="font-semibold text-slate-850 dark:text-slate-200"></span>
                    </div>
                    <div>
                        <span class="font-bold text-slate-400 uppercase text-[9px] block">Preferred Time</span>
                        <span id="confirm-requested-time" class="font-semibold text-slate-850 dark:text-slate-200"></span>
                    </div>
                </div>
                <div>
                    <span class="font-bold text-slate-400 uppercase text-[9px] block">Chief Complaint</span>
                    <span id="confirm-complaint" class="text-slate-600 dark:text-slate-400"></span>
                </div>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1">Target Clinic Department *</label>
                <select id="confirm_department_id" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <option value="">Choose Department...</option>
                </select>
                <p class="text-[9px] text-amber-600 dark:text-amber-400 font-semibold mt-1">Note: Patient vitals must be captured first. Doctor assignment occurs at Vitals Desk.</p>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800/80">
                <button type="button" onclick="closeConfirmRequestModal()" class="px-4 py-2 text-xs font-semibold text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition">Cancel</button>
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 text-xs font-semibold rounded-xl transition shadow-md">Confirm & Dispatch Email</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let doctorsList = [];
    let publicRequests = [];
    let currentTab = 'active';

    function switchTab(tab) {
        currentTab = tab;
        const activeBtn = document.getElementById('tab-active-btn');
        const reqBtn = document.getElementById('tab-requests-btn');
        const activeWrapper = document.getElementById('active-appointments-wrapper');
        const reqWrapper = document.getElementById('public-requests-wrapper');

        if (tab === 'active') {
            activeBtn.className = 'text-xs font-bold pb-2 border-b-2 border-emerald-600 text-emerald-600 transition px-2';
            reqBtn.className = 'text-xs font-semibold pb-2 border-b-2 border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 transition px-2 flex items-center gap-1.5';
            activeWrapper.classList.remove('hidden');
            reqWrapper.classList.add('hidden');
            loadAppointments();
        } else {
            reqBtn.className = 'text-xs font-bold pb-2 border-b-2 border-emerald-600 text-emerald-600 transition px-2 flex items-center gap-1.5';
            activeBtn.className = 'text-xs font-semibold pb-2 border-b-2 border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 transition px-2';
            activeWrapper.classList.add('hidden');
            reqWrapper.classList.remove('hidden');
            loadPublicRequests();
        }
    }

    async function loadAppointments() {
        const tbody = document.getElementById('appointments-table-body');
        try {
            const res = await api.get('/appointments');
            const list = res.appointments;
            const role = user.roles && user.roles[0] ? user.roles[0].name : '';

            if (list.length > 0) {
                tbody.innerHTML = list.map(apt => {
                    let statusClass = 'bg-slate-100 text-slate-700';
                    if (apt.status === 'checked_in') statusClass = 'bg-emerald-500/10 text-emerald-600 border border-emerald-500/20';
                    else if (apt.status === 'cancelled') statusClass = 'bg-red-500/10 text-red-550 border border-red-500/20';
                    else if (apt.status === 'pending') statusClass = 'bg-amber-500/10 text-amber-600 border border-amber-500/20';

                    const canManage = ['super_admin', 'records_officer', 'receptionist', 'hospital_admin'].includes(role);
                    let actionHTML = '';

                    if (canManage && apt.status === 'pending') {
                        actionHTML = `
                            <button onclick="checkInAppointment(${apt.id})" class="text-emerald-600 hover:text-emerald-700 font-bold mr-3 hover:underline">Check In</button>
                            <button onclick="cancelAppointment(${apt.id})" class="text-red-500 hover:text-red-600 font-bold hover:underline">Cancel</button>
                        `;
                    } else if (apt.status === 'checked_in') {
                        actionHTML = `<span class="text-slate-800 dark:text-slate-200 font-semibold text-[10px]">Triage Queue</span>`;
                    } else {
                        actionHTML = `<span class="text-slate-800 dark:text-slate-200 text-[10px]">—</span>`;
                    }

                    return `
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/20 transition">
                            <td class="py-3.5 px-6 font-mono font-bold text-slate-900 dark:text-white">#${apt.queue_number}</td>
                            <td class="py-3.5 px-6">
                                <span class="font-bold text-slate-850 dark:text-white">${apt.patient.first_name} ${apt.patient.last_name}</span>
                                <div class="text-[9px] text-slate-500">Code: ${apt.patient.immigration_service_number}</div>
                            </td>
                            <td class="py-3.5 px-6">
                                <span class="font-semibold text-slate-800 dark:text-slate-200">Dr. ${apt.doctor ? apt.doctor.full_name : 'Staff'}</span>
                                <div class="text-[9px] text-slate-500">${apt.department ? apt.department.name : 'Outpatient'}</div>
                            </td>
                            <td class="py-3.5 px-6">
                                <span class="font-medium">${apt.appointment_date}</span>
                                <div class="text-[9px] text-slate-500">${apt.appointment_time}</div>
                            </td>
                            <td class="py-3.5 px-6">
                                <span class="px-2.5 py-0.5 text-[9px] font-bold rounded-full uppercase ${statusClass}">
                                    ${apt.status.replace('_', ' ')}
                                </span>
                            </td>
                            <td class="py-3.5 px-6 text-right">${actionHTML}</td>
                        </tr>
                    `;
                }).join('');
            } else {
                tbody.innerHTML = `<tr><td colspan="6" class="py-8 text-center text-slate-500 text-xs">No appointments booked for today.</td></tr>`;
            }
        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="6" class="py-8 text-center text-red-500 text-xs">Failed to load appointments roster.</td></tr>`;
        }
    }

    async function loadPublicRequests() {
        const tbody = document.getElementById('requests-table-body');
        try {
            const res = await api.get('/appointments/requests?status=pending');
            publicRequests = res.requests;

            // Update Badge
            const badge = document.getElementById('requests-badge');
            if (publicRequests.length > 0) {
                badge.innerText = publicRequests.length;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }

            if (publicRequests.length > 0) {
                tbody.innerHTML = publicRequests.map(req => {
                    return `
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/20 transition">
                            <td class="py-3.5 px-6 font-medium text-slate-900 dark:text-white">
                                <div>${req.appointment_date}</div>
                                <div class="text-[9px] text-slate-500">${req.appointment_time}</div>
                            </td>
                            <td class="py-3.5 px-6">
                                <span class="font-bold text-slate-850 dark:text-white">${req.first_name} ${req.last_name}</span>
                            </td>
                            <td class="py-3.5 px-6">
                                <div>${req.phone}</div>
                                <div class="text-[9px] text-slate-550">${req.email}</div>
                            </td>
                            <td class="py-3.5 px-6 font-mono">${req.immigration_service_number || '<span class="text-slate-400">N/A (New Patient)</span>'}</td>
                            <td class="py-3.5 px-6 text-slate-600 dark:text-slate-400">${req.notes || '<span class="text-slate-400 italic">No notes</span>'}</td>
                            <td class="py-3.5 px-6 text-right">
                                <button onclick="openConfirmRequestModal(${req.id})" class="text-emerald-600 hover:text-emerald-700 font-bold mr-3 hover:underline">Confirm</button>
                                <button onclick="rejectRequest(${req.id})" class="text-red-500 hover:text-red-655 font-bold hover:underline">Reject</button>
                            </td>
                        </tr>
                    `;
                }).join('');
            } else {
                tbody.innerHTML = `<tr><td colspan="6" class="py-8 text-center text-slate-500 text-xs">No pending online appointment requests.</td></tr>`;
            }
        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="6" class="py-8 text-center text-red-500 text-xs">Failed to load public requests.</td></tr>`;
        }
    }

    async function loadSetupData() {
        try {
            // Load Doctors
            const docRes = await api.get('/appointments/doctors');
            doctorsList = docRes.doctors || [];
            
            const docSelect = document.getElementById('doctor_id');
            docSelect.innerHTML = '<option value="">-- Pending Vitals Triage (No Doctor) --</option>' + doctorsList.map(doc => `
                <option value="${doc.id}">Dr. ${doc.full_name} (${doc.department?.name || 'Department'})</option>
            `).join('');

            // Load Clinics / Departments
            const deptRes = await api.get('/queue/departments');
            const departments = deptRes.departments || [];

            const deptSelect = document.getElementById('department_id');
            deptSelect.innerHTML = '<option value="">Choose Department...</option>' + departments.map(d => `
                <option value="${d.id}">${d.name}</option>
            `).join('');

            const confirmDeptSelect = document.getElementById('confirm_department_id');
            confirmDeptSelect.innerHTML = '<option value="">Choose Department...</option>' + departments.map(d => `
                <option value="${d.id}">${d.name}</option>
            `).join('');

        } catch (err) {
            console.error('Setup data load failed:', err);
        }
    }

    let bookPatientVerified = false;

    async function handleBookPatientLookup() {
        const searchInput = document.getElementById('book-patient-search');
        if (!searchInput) return;
        const code = searchInput.value.trim();
        const statusCard = document.getElementById('book-patient-verify-card');
        const nameLabel = document.getElementById('book-pat-name');
        const genderLabel = document.getElementById('book-pat-gender');
        const dobLabel = document.getElementById('book-pat-dob');
        const hiddenInput = document.getElementById('patient_id');

        if (code.length < 3) {
            alert('Please enter a valid Hospital Code (at least 3 characters).');
            return;
        }

        try {
            const res = await api.get(`/patients?search=${encodeURIComponent(code)}`);
            const list = res.patients;
            if (list.length > 0) {
                const pat = list[0];
                bookPatientVerified = true;
                
                if (hiddenInput) hiddenInput.value = pat.id;
                if (nameLabel) nameLabel.innerText = `${pat.first_name} ${pat.middle_name ? pat.middle_name + ' ' : ''}${pat.last_name}`;
                if (genderLabel) genderLabel.innerText = pat.gender;
                if (dobLabel) dobLabel.innerText = pat.date_of_birth;
                
                if (statusCard) statusCard.classList.remove('hidden');
                lucide.createIcons();
            } else {
                bookPatientVerified = false;
                if (hiddenInput) hiddenInput.value = '';
                if (statusCard) statusCard.classList.add('hidden');
                alert('No patient found with the provided Hospital Code.');
            }
        } catch (err) {
            bookPatientVerified = false;
            if (hiddenInput) hiddenInput.value = '';
            if (statusCard) statusCard.classList.add('hidden');
            alert('Lookup failed: ' + (err.message || 'connection error'));
        }
    }

    function openBookModal() {
        document.getElementById('book-form').reset();
        bookPatientVerified = false;
        const statusCard = document.getElementById('book-patient-verify-card');
        if (statusCard) statusCard.classList.add('hidden');
        const hiddenInput = document.getElementById('patient_id');
        if (hiddenInput) hiddenInput.value = '';

        const today = new Date().toISOString().split('T')[0];
        document.getElementById('appointment_date').value = today;
        document.getElementById('book-modal').classList.remove('hidden');
    }

    function closeBookModal() {
        document.getElementById('book-modal').classList.add('hidden');
    }

    function openConfirmRequestModal(id) {
        const req = publicRequests.find(r => r.id == id);
        if (!req) return;

        document.getElementById('confirm-request-id').value = id;
        document.getElementById('confirm-patient-name').innerText = `${req.first_name} ${req.last_name}`;
        document.getElementById('confirm-requested-date').innerText = req.appointment_date;
        document.getElementById('confirm-requested-time').innerText = req.appointment_time;
        document.getElementById('confirm-complaint').innerText = req.notes || 'No comments';

        document.getElementById('confirm-request-modal').classList.remove('hidden');
    }

    function closeConfirmRequestModal() {
        document.getElementById('confirm-request-modal').classList.add('hidden');
    }

    async function handleBookSubmit(e) {
        e.preventDefault();
        if (!bookPatientVerified) {
            alert('Please look up and verify a patient by Hospital Code first.');
            return;
        }
        const patId = parseInt(document.getElementById('patient_id').value);
        if (!patId) {
            alert('Invalid patient selected.');
            return;
        }
        const deptId = parseInt(document.getElementById('department_id').value);
        if (!deptId) {
            alert('Please select a target clinic department.');
            return;
        }
        const docVal = document.getElementById('doctor_id').value;
        const docId = docVal ? parseInt(docVal) : null;

        const payload = {
            patient_id: patId,
            staff_id: docId,
            department_id: deptId,
            appointment_date: document.getElementById('appointment_date').value,
            appointment_time: document.getElementById('appointment_time').value,
            notes: document.getElementById('notes').value || null
        };

        try {
            await api.post('/appointments', payload);
            alert('Consultation appointment booked successfully!');
            closeBookModal();
            loadAppointments();
        } catch (err) {
            alert(err.message || 'Failed to book appointment.');
        }
    }

    async function handleConfirmRequestSubmit(e) {
        e.preventDefault();
        const requestId = document.getElementById('confirm-request-id').value;
        const deptId = parseInt(document.getElementById('confirm_department_id').value);
        const submitBtn = e.target.querySelector('button[type="submit"]');

        if (!deptId) {
            alert('Please select a target clinic department.');
            return;
        }

        const payload = {
            staff_id: null,
            department_id: deptId
        };

        try {
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Confirming...';
            const res = await api.post(`/appointments/requests/${requestId}/confirm`, payload);
            alert(res.message || 'Appointment request confirmed successfully! Confirmation notice dispatched to patient\'s email.');
            closeConfirmRequestModal();
            loadPublicRequests();
            loadAppointments();
        } catch (err) {
            alert(err.message || 'Failed to confirm request.');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Confirm & Dispatch Email';
        }
    }

    async function rejectRequest(id) {
        if (!confirm('Reject this appointment request? A rejection notification email will be dispatched.')) return;
        try {
            await api.post(`/appointments/requests/${id}/reject`);
            alert('Appointment request rejected.');
            loadPublicRequests();
        } catch (err) {
            alert(err.message || 'Failed to reject request.');
        }
    }

    async function checkInAppointment(id) {
        if (!confirm('Capture triage check-in? Patient will be queued in the vitals entry desk.')) return;
        try {
            await api.post(`/appointments/${id}/check-in`);
            alert('Patient checked in! Pending visit created for triage.');
            loadAppointments();
        } catch (err) {
            alert(err.message || 'Check-in failed.');
        }
    }

    async function cancelAppointment(id) {
        if (!confirm('Are you sure you want to cancel this appointment?')) return;
        try {
            await api.post(`/appointments/${id}/cancel`);
            alert('Appointment cancelled.');
            loadAppointments();
        } catch (err) {
            alert(err.message || 'Cancellation failed.');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const role = user.roles && user.roles[0] ? user.roles[0].name : '';
        if (['super_admin', 'records_officer', 'receptionist', 'hospital_admin'].includes(role)) {
            document.getElementById('book-btn-container').classList.remove('hidden');
        }
        loadAppointments();
        loadPublicRequests();
        loadSetupData();
    });
</script>
@endsection
