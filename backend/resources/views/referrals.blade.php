@extends('layouts.app')

@section('title', 'Referral Management – NIS Medical Services Portal')

@section('content')
<div class="space-y-6">

    <!-- Page Header -->
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-white">Referral Management</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Inter-facility transfers — outgoing & incoming referrals</p>
        </div>
        <div class="flex items-center gap-2">
            <select id="ref-type-filter" onchange="loadReferrals()" class="text-xs px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                <option value="all">All Types</option>
                <option value="outgoing">Outgoing</option>
                <option value="incoming">Incoming</option>
            </select>
            <select id="ref-status-filter" onchange="loadReferrals()" class="text-xs px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                <option value="all">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="accepted">Accepted</option>
                <option value="in_transit">In Transit</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
            </select>
            <select id="ref-priority-filter" onchange="loadReferrals()" class="text-xs px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                <option value="all">All Priorities</option>
                <option value="routine">Routine</option>
                <option value="urgent">Urgent</option>
                <option value="emergency">Emergency</option>
            </select>
            <button onclick="openNewReferralModal()" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-4 py-2 rounded-xl flex items-center gap-1.5 transition">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i> New Referral
            </button>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 text-center">
            <p class="text-2xl font-black text-slate-800 dark:text-white" id="ref-stat-total">–</p>
            <p class="text-[10px] text-slate-500 uppercase tracking-wider mt-1">Total</p>
        </div>
        <div class="bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 rounded-2xl p-4 text-center">
            <p class="text-2xl font-black text-amber-700 dark:text-amber-400" id="ref-stat-pending">–</p>
            <p class="text-[10px] text-amber-600 uppercase tracking-wider mt-1">Pending</p>
        </div>
        <div class="bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/20 rounded-2xl p-4 text-center">
            <p class="text-2xl font-black text-blue-700 dark:text-blue-400" id="ref-stat-transit">–</p>
            <p class="text-[10px] text-blue-600 uppercase tracking-wider mt-1">In Transit</p>
        </div>
        <div class="bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 rounded-2xl p-4 text-center">
            <p class="text-2xl font-black text-red-700 dark:text-red-400" id="ref-stat-emergency">–</p>
            <p class="text-[10px] text-red-600 uppercase tracking-wider mt-1">Emergency Priority</p>
        </div>
    </div>

    <!-- Referrals Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-700">
                        <th class="text-left px-4 py-3 font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wide">Patient</th>
                        <th class="text-left px-4 py-3 font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wide">Type</th>
                        <th class="text-left px-4 py-3 font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wide">Priority</th>
                        <th class="text-left px-4 py-3 font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wide">Receiving Facility</th>
                        <th class="text-left px-4 py-3 font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wide">Doctor</th>
                        <th class="text-left px-4 py-3 font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wide">Reason</th>
                        <th class="text-left px-4 py-3 font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wide">Status</th>
                        <th class="text-left px-4 py-3 font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wide">Referred</th>
                        <th class="text-left px-4 py-3 font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wide">Actions</th>
                    </tr>
                </thead>
                <tbody id="referrals-tbody">
                    <tr><td colspan="9" class="py-10 text-center text-slate-400 text-xs"><i data-lucide="loader" class="w-5 h-5 mx-auto animate-spin mb-2"></i>Loading...</td></tr>
                </tbody>
            </table>
        </div>
        <div class="flex items-center justify-between px-5 py-3 border-t border-slate-100 dark:border-slate-800" id="ref-pagination"></div>
    </div>
</div>

<!-- New Referral Modal -->
<div id="new-referral-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 w-full max-w-lg shadow-2xl overflow-y-auto max-h-[90vh]">
        <h3 class="text-base font-bold text-slate-800 dark:text-white mb-5">Create New Referral</h3>
        <form id="referral-form" onsubmit="submitReferral(event)" class="space-y-4">
            <!-- Patient Search & Verification -->
            <div class="bg-slate-50 dark:bg-slate-950/40 p-4 border border-slate-200 dark:border-slate-800 rounded-2xl space-y-3">
                <label class="block text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Search Patient by Hospital Code</label>
                <div class="flex gap-2">
                    <input type="text" id="ref-patient-search-code" placeholder="Enter Hospital Code e.g. NIS/PAT/000001" 
                           class="flex-grow bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-850 dark:text-slate-205 focus:outline-none focus:ring-1 focus:ring-emerald-500 font-mono tracking-wide">
                    <button type="button" onclick="lookupReferralPatient()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1 shrink-0 cursor-pointer">
                        Lookup
                    </button>
                </div>
                <!-- Patient Card Preview -->
                <div id="ref-patient-preview" class="hidden p-3 rounded-xl border border-emerald-500/20 bg-emerald-500/5 flex items-center justify-between">
                    <div>
                        <h4 class="text-xs font-black text-slate-800 dark:text-white" id="ref-preview-name">Name</h4>
                        <p class="text-[10px] text-slate-500 dark:text-slate-400">Gender: <span id="ref-preview-gender"></span> | DOB: <span id="ref-preview-dob"></span></p>
                    </div>
                    <span class="text-[9px] bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 font-mono font-bold px-2 py-0.5 rounded" id="ref-preview-code">Code</span>
                </div>
                <!-- Hidden Patient ID -->
                <input type="hidden" id="ref-patient-id" required>
            </div>

            <div class="grid grid-cols-1 gap-3">
                <div>
                    <label class="ref-label">Referring Doctor <span class="text-red-500">*</span></label>
                    <select id="ref-doctor-id" required class="ref-input w-full">
                        <option value="">– Select Doctor –</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="ref-label">Type <span class="text-red-500">*</span></label>
                    <select id="ref-type" required class="ref-input w-full">
                        <option value="outgoing">Outgoing (Transfer Out)</option>
                        <option value="incoming">Incoming (Transfer In)</option>
                    </select>
                </div>
                <div>
                    <label class="ref-label">Priority <span class="text-red-500">*</span></label>
                    <select id="ref-priority" required class="ref-input w-full">
                        <option value="routine">Routine</option>
                        <option value="urgent">Urgent</option>
                        <option value="emergency">Emergency</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="ref-label">Receiving Facility <span class="text-red-500">*</span></label>
                <select id="ref-facility" required class="ref-input w-full">
                    <option value="">Select Receiving Facility</option>
                    <option value="Ahmadu Bello University Teaching Hospital (ABUTH), Zaria">Ahmadu Bello University Teaching Hospital (ABUTH), Zaria</option>
                    <option value="Aminu Kano Teaching Hospital (AKTH), Kano">Aminu Kano Teaching Hospital (AKTH), Kano</option>
                    <option value="Federal Medical Centre, Abeokuta">Federal Medical Centre, Abeokuta</option>
                    <option value="Federal Medical Centre, Abuja">Federal Medical Centre, Abuja</option>
                    <option value="Federal Medical Centre, Asaba">Federal Medical Centre, Asaba</option>
                    <option value="Federal Medical Centre, Azare">Federal Medical Centre, Azare</option>
                    <option value="Federal Medical Centre, Bida">Federal Medical Centre, Bida</option>
                    <option value="Federal Medical Centre, Birnin Kebbi">Federal Medical Centre, Birnin Kebbi</option>
                    <option value="Federal Medical Centre, Ebute Metta, Lagos">Federal Medical Centre, Ebute Metta, Lagos</option>
                    <option value="Federal Medical Centre, Gombe">Federal Medical Centre, Gombe</option>
                    <option value="Federal Medical Centre, Gusau">Federal Medical Centre, Gusau</option>
                    <option value="Federal Medical Centre, Jalingo">Federal Medical Centre, Jalingo</option>
                    <option value="Federal Medical Centre, Katsina">Federal Medical Centre, Katsina</option>
                    <option value="Federal Medical Centre, Keffi">Federal Medical Centre, Keffi</option>
                    <option value="Federal Medical Centre, Lokoja">Federal Medical Centre, Lokoja</option>
                    <option value="Federal Medical Centre, Makurdi">Federal Medical Centre, Makurdi</option>
                    <option value="Federal Medical Centre, Nguru">Federal Medical Centre, Nguru</option>
                    <option value="Federal Medical Centre, Owerri">Federal Medical Centre, Owerri</option>
                    <option value="Federal Medical Centre, Owo">Federal Medical Centre, Owo</option>
                    <option value="Federal Medical Centre, Umuahia">Federal Medical Centre, Umuahia</option>
                    <option value="Federal Medical Centre, Yenagoa">Federal Medical Centre, Yenagoa</option>
                    <option value="Federal Medical Centre, Yola">Federal Medical Centre, Yola</option>
                    <option value="Federal Neuro-Psychiatric Hospital, Aro, Abeokuta">Federal Neuro-Psychiatric Hospital, Aro, Abeokuta</option>
                    <option value="Federal Neuro-Psychiatric Hospital, Kaduna">Federal Neuro-Psychiatric Hospital, Kaduna</option>
                    <option value="Federal Neuro-Psychiatric Hospital, Yaba, Lagos">Federal Neuro-Psychiatric Hospital, Yaba, Lagos</option>
                    <option value="Jos University Teaching Hospital (JUTH), Jos">Jos University Teaching Hospital (JUTH), Jos</option>
                    <option value="Lagos University Teaching Hospital (LUTH), Lagos">Lagos University Teaching Hospital (LUTH), Lagos</option>
                    <option value="National Ear Care Centre, Kaduna">National Ear Care Centre, Kaduna</option>
                    <option value="National Eye Centre, Kaduna">National Eye Centre, Kaduna</option>
                    <option value="National Hospital, Abuja">National Hospital, Abuja</option>
                    <option value="National Orthopaedic Hospital, Enugu">National Orthopaedic Hospital, Enugu</option>
                    <option value="National Orthopaedic Hospital, Igbobi, Lagos">National Orthopaedic Hospital, Igbobi, Lagos</option>
                    <option value="Nnamdi Azikiwe University Teaching Hospital (NAUTH), Nnewi">Nnamdi Azikiwe University Teaching Hospital (NAUTH), Nnewi</option>
                    <option value="Obafemi Awolowo University Teaching Hospitals Complex (OAUTHC), Ile-Ife">Obafemi Awolowo University Teaching Hospitals Complex (OAUTHC), Ile-Ife</option>
                    <option value="Orthopaedic Hospital, Dala, Kano">Orthopaedic Hospital, Dala, Kano</option>
                    <option value="University College Hospital (UCH), Ibadan">University College Hospital (UCH), Ibadan</option>
                    <option value="University of Benin Teaching Hospital (UBTH), Benin City">University of Benin Teaching Hospital (UBTH), Benin City</option>
                    <option value="University of Calabar Teaching Hospital (UCTH), Calabar">University of Calabar Teaching Hospital (UCTH), Calabar</option>
                    <option value="University of Ilorin Teaching Hospital (UITH), Ilorin">University of Ilorin Teaching Hospital (UITH), Ilorin</option>
                    <option value="University of Maiduguri Teaching Hospital (UMTH), Maiduguri">University of Maiduguri Teaching Hospital (UMTH), Maiduguri</option>
                    <option value="University of Nigeria Teaching Hospital (UNTH), Enugu">University of Nigeria Teaching Hospital (UNTH), Enugu</option>
                    <option value="University of Port Harcourt Teaching Hospital (UPTH), Port Harcourt">University of Port Harcourt Teaching Hospital (UPTH), Port Harcourt</option>
                    <option value="University of Uyo Teaching Hospital (UUTH), Uyo">University of Uyo Teaching Hospital (UUTH), Uyo</option>
                    <option value="Usman Danfodiyo University Teaching Hospital (UDUTH), Sokoto">Usman Danfodiyo University Teaching Hospital (UDUTH), Sokoto</option>
                </select>
            </div>
            <div>
                <label class="ref-label">Receiving Doctor (optional)</label>
                <input type="text" id="ref-receiving-doctor" placeholder="Name of receiving doctor" class="ref-input w-full">
            </div>
            <div>
                <label class="ref-label">Reason for Referral <span class="text-red-500">*</span></label>
                <input type="text" id="ref-reason" required placeholder="e.g. Specialist management required" class="ref-input w-full">
            </div>
            <div>
                <label class="ref-label">Clinical Summary <span class="text-red-500">*</span></label>
                <textarea id="ref-summary" required rows="3" placeholder="Relevant history, findings, medications..." class="ref-input w-full resize-none"></textarea>
            </div>
            <div>
                <label class="ref-label">Special Instructions</label>
                <textarea id="ref-instructions" rows="2" placeholder="Transport requirements, urgency notes..." class="ref-input w-full resize-none"></textarea>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeNewReferralModal()" class="flex-1 px-4 py-2 text-xs font-semibold border border-slate-200 dark:border-slate-700 rounded-xl text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition">Cancel</button>
                <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-4 py-2 rounded-xl transition">Create Referral</button>
            </div>
        </form>
    </div>
</div>

<!-- Status Update Modal -->
<div id="ref-status-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 w-full max-w-sm shadow-2xl">
        <h3 class="text-base font-bold text-slate-800 dark:text-white mb-4">Update Referral Status</h3>
        <input type="hidden" id="ref-update-id">
        <div>
            <label class="ref-label">New Status</label>
            <select id="ref-new-status" class="ref-input w-full">
                <option value="pending">Pending</option>
                <option value="accepted">Accepted</option>
                <option value="in_transit">In Transit</option>
                <option value="arrived">Arrived</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>
        <div class="flex gap-3 mt-5">
            <button onclick="closeRefStatusModal()" class="flex-1 px-4 py-2 text-xs font-semibold border border-slate-200 dark:border-slate-700 rounded-xl text-slate-700 dark:text-slate-300 hover:bg-slate-50 transition">Cancel</button>
            <button onclick="submitRefStatus()" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-4 py-2 rounded-xl transition">Update</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<style>
    .ref-input {
        font-size: 0.75rem;
        padding: 0.5rem 0.75rem;
        border-radius: 0.75rem;
        border: 1px solid #e2e8f0;
        background-color: #f8fafc;
        color: #1e293b;
        outline: none;
        transition: all 0.2s;
    }
    .dark .ref-input {
        border-color: #1e293b;
        background-color: #020617;
        color: #e2e8f0;
    }
    .ref-input:focus {
        border-color: #10b981;
        box-shadow: 0 0 0 1px #10b981;
    }
    .ref-label {
        display: block;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #1e293b;
        margin-bottom: 0.25rem;
    }
    .dark .ref-label {
        color: #e2e8f0;
    }
</style>
<script>
    const priorityBadge = {
        routine:   'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300',
        urgent:    'bg-amber-100 text-amber-800 dark:bg-amber-500/20 dark:text-amber-400',
        emergency: 'bg-red-100 text-red-800 dark:bg-red-500/20 dark:text-red-400',
    };
    const statusBadge = {
        pending:   'bg-amber-100 text-amber-800 dark:bg-amber-500/20 dark:text-amber-400',
        accepted:  'bg-blue-100 text-blue-800 dark:bg-blue-500/20 dark:text-blue-400',
        in_transit:'bg-violet-100 text-violet-800 dark:bg-violet-500/20 dark:text-violet-400',
        arrived:   'bg-teal-100 text-teal-800 dark:bg-teal-500/20 dark:text-teal-400',
        completed: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-400',
        cancelled: 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400',
    };

    let currentPage = 1;

    async function loadReferrals(page = 1) {
        currentPage = page;
        const type     = document.getElementById('ref-type-filter').value;
        const status   = document.getElementById('ref-status-filter').value;
        const priority = document.getElementById('ref-priority-filter').value;
        const tbody    = document.getElementById('referrals-tbody');
        tbody.innerHTML = `<tr><td colspan="9" class="py-10 text-center text-slate-400 text-xs"><i data-lucide="loader" class="w-5 h-5 mx-auto animate-spin mb-2"></i>Loading...</td></tr>`;
        lucide.createIcons();

        try {
            const data = await window.api.get(`/referrals?type=${type}&status=${status}&priority=${priority}&page=${page}`);
            const list = data.referrals || [];
            const stats = data.stats || {};

            document.getElementById('ref-stat-total').textContent     = stats.total             ?? 0;
            document.getElementById('ref-stat-pending').textContent   = stats.pending           ?? 0;
            document.getElementById('ref-stat-transit').textContent   = stats.in_transit        ?? 0;
            document.getElementById('ref-stat-emergency').textContent = stats.emergency_priority ?? 0;

            if (!list.length) {
                tbody.innerHTML = `<tr><td colspan="9" class="py-12 text-center text-slate-400 text-xs"><i data-lucide="share-2" class="w-8 h-8 mx-auto mb-2 opacity-40"></i><p>No referrals found.</p></td></tr>`;
                lucide.createIcons(); return;
            }

            tbody.innerHTML = list.map(r => `
                <tr class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition">
                    <td class="px-4 py-3"><p class="font-semibold text-slate-800 dark:text-white">${r.patient_name}</p><p class="text-slate-400 text-[10px] font-mono">${r.hospital_number}</p></td>
                    <td class="px-4 py-3"><span class="px-2 py-1 rounded-full text-[10px] font-bold ${r.referral_type === 'outgoing' ? 'bg-blue-100 text-blue-700' : 'bg-violet-100 text-violet-700'}">${r.referral_type}</span></td>
                    <td class="px-4 py-3"><span class="px-2 py-1 rounded-full text-[10px] font-bold ${priorityBadge[r.priority] || ''}">${r.priority}</span></td>
                    <td class="px-4 py-3 text-slate-600 dark:text-slate-300">${r.receiving_facility}</td>
                    <td class="px-4 py-3 text-slate-600 dark:text-slate-300">${r.referring_doctor}</td>
                    <td class="px-4 py-3 text-slate-500 max-w-[150px] truncate">${r.reason}</td>
                    <td class="px-4 py-3"><span class="px-2 py-1 rounded-full text-[10px] font-bold ${statusBadge[r.status] || ''}">${r.status.replace(/_/g,' ')}</span></td>
                    <td class="px-4 py-3 text-slate-400 text-[10px]">${r.referred_at}</td>
                    <td class="px-4 py-3"><button onclick="openRefStatusModal(${r.id}, '${r.status}')" class="text-emerald-600 hover:text-emerald-800 text-[10px] font-bold underline underline-offset-2">Update</button></td>
                </tr>`).join('');

            renderPagination(data.pagination || {});
            lucide.createIcons();
        } catch(e) {
            tbody.innerHTML = `<tr><td colspan="9" class="py-10 text-center text-red-400 text-xs">${e.message}</td></tr>`;
        }
    }

    function renderPagination(p) {
        const el = document.getElementById('ref-pagination');
        if (!el || !p.last_page) { el.innerHTML = ''; return; }
        el.innerHTML = `<span class="text-xs text-slate-500">Page ${p.current_page} of ${p.last_page} · ${p.total} records</span>
            <div class="flex gap-1">
                ${p.current_page > 1 ? `<button onclick="loadReferrals(${p.current_page - 1})" class="text-xs px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800">Prev</button>` : ''}
                ${p.current_page < p.last_page ? `<button onclick="loadReferrals(${p.current_page + 1})" class="text-xs px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800">Next</button>` : ''}
            </div>`;
    }

    async function loadDoctors() {
        try {
            const data = await window.api.get('/referrals/doctors');
            const sel  = document.getElementById('ref-doctor-id');
            (data.doctors || []).forEach(d => {
                const opt = document.createElement('option');
                opt.value = d.id; opt.textContent = `${d.name} (${d.department})`;
                sel.appendChild(opt);
            });
        } catch(e) {}
    }

    async function lookupReferralPatient() {
        const code = document.getElementById('ref-patient-search-code').value.trim();
        const preview = document.getElementById('ref-patient-preview');
        const hiddenId = document.getElementById('ref-patient-id');
        
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
                document.getElementById('ref-preview-name').innerText = pat.full_name;
                document.getElementById('ref-preview-gender').innerText = pat.gender;
                document.getElementById('ref-preview-dob').innerText = pat.date_of_birth;
                document.getElementById('ref-preview-code').innerText = pat.immigration_service_number;
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

    function openNewReferralModal()   { 
        loadDoctors(); 
        document.getElementById('referral-form').reset();
        document.getElementById('ref-patient-preview').classList.add('hidden');
        document.getElementById('ref-patient-id').value = '';
        document.getElementById('new-referral-modal').classList.remove('hidden'); 
    }
    function closeNewReferralModal()  { document.getElementById('new-referral-modal').classList.add('hidden'); }
    function openRefStatusModal(id, status) {
        document.getElementById('ref-update-id').value  = id;
        document.getElementById('ref-new-status').value = status;
        document.getElementById('ref-status-modal').classList.remove('hidden');
    }
    function closeRefStatusModal() { document.getElementById('ref-status-modal').classList.add('hidden'); }

    async function submitReferral(e) {
        e.preventDefault();
        const patientId = document.getElementById('ref-patient-id').value;
        if (!patientId) {
            alert('Please lookup and verify a patient by Hospital Code first.');
            return;
        }

        const payload = {
            patient_id:            patientId,
            referring_doctor_id:   document.getElementById('ref-doctor-id').value,
            referral_type:         document.getElementById('ref-type').value,
            priority:              document.getElementById('ref-priority').value,
            receiving_facility:    document.getElementById('ref-facility').value,
            receiving_doctor:      document.getElementById('ref-receiving-doctor').value,
            reason:                document.getElementById('ref-reason').value,
            clinical_summary:      document.getElementById('ref-summary').value,
            special_instructions:  document.getElementById('ref-instructions').value,
        };
        try {
            await window.api.post('/referrals', payload);
            closeNewReferralModal();
            loadReferrals();
            alert('Referral created successfully!');
        } catch(e) { alert('Error: ' + e.message); }
    }

    async function submitRefStatus() {
        const id     = document.getElementById('ref-update-id').value;
        const status = document.getElementById('ref-new-status').value;
        try {
            await window.api.post(`/referrals/${id}/status`, { status });
            closeRefStatusModal();
            loadReferrals();
        } catch(e) { alert('Error: ' + e.message); }
    }

    document.addEventListener('DOMContentLoaded', () => loadReferrals());
</script>
@endsection
