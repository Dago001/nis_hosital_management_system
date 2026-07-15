@extends('layouts.app')

@section('title', 'Laboratory - NIS Medical Services Portal')

@section('content')
<div class="space-y-6">
    <!-- Title & Controls -->
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-white flex items-center gap-2">
                <i data-lucide="test-tube" class="text-emerald-600"></i> Pathological Laboratory Queue
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Laboratory scientist portal to coordinate sample collections, record diagnostics values, and authorize reports</p>
        </div>
        <button onclick="loadLabQueue()" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold px-4 py-2 rounded-xl flex items-center gap-1.5 transition cursor-pointer">
            <i data-lucide="refresh-cw" class="w-3.5 h-3.5 animate-spin-slow"></i> Refresh Queue
        </button>
    </div>

    <!-- Diagnostic Lab Metrics Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4">
            <div class="flex items-start justify-between mb-2">
                <div class="p-2 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                    <i data-lucide="microscope" class="w-4 h-4"></i>
                </div>
            </div>
            <p class="text-xl font-black text-slate-800 dark:text-white" id="lab-stat-total">0</p>
            <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1 uppercase tracking-wider font-bold">Total Worklist</p>
        </div>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4">
            <div class="flex items-start justify-between mb-2">
                <div class="p-2 rounded-xl bg-amber-50 dark:bg-amber-500/10 text-amber-600">
                    <i data-lucide="git-pull-request" class="w-4 h-4"></i>
                </div>
            </div>
            <p class="text-xl font-black text-amber-600 dark:text-amber-400" id="lab-stat-pending">0</p>
            <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1 uppercase tracking-wider font-bold">Pending Collections</p>
        </div>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4">
            <div class="flex items-start justify-between mb-2">
                <div class="p-2 rounded-xl bg-blue-50 dark:bg-blue-500/10 text-blue-600">
                    <i data-lucide="activity" class="w-4 h-4"></i>
                </div>
            </div>
            <p class="text-xl font-black text-blue-600 dark:text-blue-400" id="lab-stat-processing">0</p>
            <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1 uppercase tracking-wider font-bold">Samples Processing</p>
        </div>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4">
            <div class="flex items-start justify-between mb-2">
                <div class="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                </div>
            </div>
            <p class="text-xl font-black text-emerald-600 dark:text-emerald-400" id="lab-stat-approved">0</p>
            <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1 uppercase tracking-wider font-bold">Authorized Reports</p>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white dark:bg-slate-900 p-4 border border-slate-200 dark:border-slate-800 rounded-2xl flex flex-col sm:flex-row gap-4 items-center justify-between shadow-sm">
        <div class="relative w-full sm:max-w-xs">
            <input type="text" id="lab-search" oninput="filterWorklist()" placeholder="Search patient by name or code..." class="w-full pl-9 pr-4 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 rounded-xl text-xs focus:outline-none focus:ring-1 focus:ring-emerald-500 text-slate-800 dark:text-slate-100 placeholder-slate-450 transition-all">
            <i data-lucide="search" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400 w-4 h-4"></i>
        </div>
        <div class="flex gap-2 w-full sm:w-auto shrink-0">
            <select id="lab-status-filter" onchange="filterWorklist()" class="w-full sm:w-44 text-xs px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                <option value="all">All Test Requests</option>
                <option value="requested">Pending Samples Collection</option>
                <option value="sample_collected">In Processing</option>
                <option value="result_submitted">Result Pending Authorization</option>
                <option value="approved">Authorized & Released</option>
            </select>
        </div>
    </div>

    <!-- Lab Table Card -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-200 border-b border-slate-100 dark:border-slate-800">
                    <tr>
                        <th class="py-3.5 px-6 font-bold">Patient Details</th>
                        <th class="py-3.5 px-6 font-bold">Requested Test</th>
                        <th class="py-3.5 px-6 font-bold">Request Date</th>
                        <th class="py-3.5 px-6 font-bold">Status</th>
                        <th class="py-3.5 px-6 font-bold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="lab-table-body" class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-350">
                    <tr>
                        <td colSpan="5" class="py-8 text-center text-slate-800 dark:text-slate-200 text-sm">
                            <div class="w-6 h-6 border-2 border-emerald-600 border-t-transparent rounded-full animate-spin mx-auto"></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Submit Result Modal -->
<div id="result-modal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 w-full max-w-lg shadow-2xl relative my-8">
        <h3 class="text-base font-bold text-slate-800 dark:text-white mb-4">Input Laboratory Diagnostics Values</h3>
        
        <form id="result-form" onsubmit="handleResultSubmit(event)" class="space-y-4">
            <div>
                <label class="block text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1">Result Value *</label>
                <input type="text" id="result_value" required placeholder="e.g. 5.4 or Negative" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500">
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1">Min Normal</label>
                    <input type="text" id="normal_range_min" placeholder="e.g. 4.0" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1">Max Normal</label>
                    <input type="text" id="normal_range_max" placeholder="e.g. 6.0" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1">Unit</label>
                    <input type="text" id="unit" placeholder="e.g. mmol/L" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1">Scientist Remarks</label>
                <textarea id="remarks" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500" rows="3" placeholder="Clinical comments..."></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800/80">
                <button type="button" onclick="closeResultModal()" class="px-4 py-2 text-xs font-semibold text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition">Cancel</button>
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 text-xs font-semibold rounded-xl transition shadow-md">Submit Draft</button>
            </div>
        </form>
    </div>
</div>

<!-- Diagnostic Report Printing Modal -->
<div id="print-report-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 w-full max-w-lg shadow-2xl relative">
        <div id="printable-report-area" class="bg-white text-slate-900 p-6 border border-slate-100 rounded-2xl space-y-4 font-sans">
            <!-- Header -->
            <div class="text-center space-y-1 pb-3 border-b border-slate-200">
                <h2 class="text-base font-black uppercase tracking-wider text-slate-800">Nigeria Immigration Service</h2>
                <h3 class="text-xs font-bold text-slate-600">Pathological & Diagnostic Laboratories, Abuja</h3>
                <p class="text-[9px] text-slate-400">Official Clinical Laboratory Report</p>
            </div>
            <!-- Metadata -->
            <div class="grid grid-cols-2 gap-2 text-[10px] text-slate-600">
                <div>
                    <span class="font-bold block text-slate-450 uppercase text-[8px]">Patient Name:</span>
                    <span id="rep-patient-name" class="text-slate-800 font-semibold"></span>
                </div>
                <div>
                    <span class="font-bold block text-slate-450 uppercase text-[8px]">Hospital Code:</span>
                    <span id="rep-patient-code" class="text-slate-850 font-semibold font-mono"></span>
                </div>
                <div>
                    <span class="font-bold block text-slate-450 uppercase text-[8px]">Ordering Doctor:</span>
                    <span id="rep-doctor-name" class="text-slate-800 font-semibold"></span>
                </div>
                <div>
                    <span class="font-bold block text-slate-450 uppercase text-[8px]">Date Authorized:</span>
                    <span id="rep-date" class="text-slate-800 font-semibold"></span>
                </div>
            </div>
            <!-- Test & Results Table -->
            <div class="border border-slate-200 rounded-xl overflow-hidden mt-3">
                <table class="w-full text-left text-[10px]">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-700">
                        <tr>
                            <th class="p-2 font-bold">Investigation Ordered</th>
                            <th class="p-2 font-bold text-center">Result Value</th>
                            <th class="p-2 font-bold text-center">Reference Range</th>
                            <th class="p-2 font-bold text-center">Unit</th>
                        </tr>
                    </thead>
                    <tbody class="text-slate-700 divide-y divide-slate-100">
                        <tr>
                            <td class="p-2 font-bold" id="rep-test-name"></td>
                            <td class="p-2 text-center font-black text-slate-900" id="rep-value"></td>
                            <td class="p-2 text-center" id="rep-range"></td>
                            <td class="p-2 text-center" id="rep-unit"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Scientist Remarks -->
            <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-[10px] text-slate-700">
                <span class="font-bold block mb-1">Pathologist / Scientist Remarks:</span>
                <span id="rep-remarks" class="italic"></span>
            </div>
            <!-- Signature stamp -->
            <div class="text-center text-[8px] text-slate-400 pt-2 border-t border-slate-100">
                <p>This report has been electronically verified and authorized for clinical release.</p>
                <p class="font-mono mt-1" id="rep-stamp">Verification Stamp: NIS-LAB-VERIFIED</p>
            </div>
        </div>
        
        <div class="flex gap-3 mt-5">
            <button onclick="closePrintReportModal()" class="flex-grow px-4 py-2 text-xs font-semibold border border-slate-200 dark:border-slate-700 rounded-xl text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition">Close</button>
            <button onclick="printReportVoucher()" class="flex-grow bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-4 py-2 rounded-xl transition flex items-center justify-center gap-1">
                Print Report
            </button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let activeRequest = null;
    let labWorklist = [];

    async function loadLabQueue() {
        try {
            const res = await api.get('/diagnostics/lab/queue');
            labWorklist = res.lab_requests || [];
            
            calculateLabStats(labWorklist);
            filterWorklist();
        } catch (err) {
            document.getElementById('lab-table-body').innerHTML = `<tr><td colspan="5" class="py-8 text-center text-red-500 text-xs">Failed to load pathological worklist.</td></tr>`;
        }
    }

    function calculateLabStats(list) {
        document.getElementById('lab-stat-total').innerText = list.length;
        document.getElementById('lab-stat-pending').innerText = list.filter(r => r.status === 'requested').length;
        document.getElementById('lab-stat-processing').innerText = list.filter(r => r.status === 'sample_collected').length;
        document.getElementById('lab-stat-approved').innerText = list.filter(r => r.status === 'approved').length;
    }

    function filterWorklist() {
        const query = document.getElementById('lab-search').value.toLowerCase().trim();
        const status = document.getElementById('lab-status-filter').value;
        const role = user.roles && user.roles[0] ? user.roles[0].name : '';
        const tbody = document.getElementById('lab-table-body');

        let filtered = labWorklist;

        if (status !== 'all') {
            filtered = filtered.filter(r => r.status === status);
        }

        if (query.length > 0) {
            filtered = filtered.filter(r => {
                const name = `${r.patient?.first_name} ${r.patient?.last_name}`.toLowerCase();
                const code = (r.patient?.immigration_service_number || '').toLowerCase();
                return name.includes(query) || code.includes(query);
            });
        }

        if (filtered.length > 0) {
            tbody.innerHTML = filtered.map(req => {
                let statusClass = 'bg-slate-100 text-slate-700';
                let actionHTML = '';

                if (req.status === 'requested') {
                    statusClass = 'bg-amber-500/10 text-amber-600 border border-amber-500/20';
                    if (['super_admin', 'lab_scientist', 'radiographer'].includes(role)) {
                        actionHTML = `<button onclick="collectSample(${req.id})" class="bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] font-bold px-3 py-1.5 rounded-xl transition cursor-pointer">Collect Sample</button>`;
                    }
                } else if (req.status === 'sample_collected') {
                    statusClass = 'bg-blue-500/10 text-blue-600 border border-blue-500/20';
                    if (['super_admin', 'lab_scientist', 'radiographer'].includes(role)) {
                        actionHTML = `<button onclick="openResultModal(${JSON.stringify(req).replace(/"/g, '&quot;')})" class="bg-blue-600 hover:bg-blue-700 text-white text-[10px] font-bold px-3 py-1.5 rounded-xl transition cursor-pointer">Input Values</button>`;
                    }
                } else if (req.status === 'result_submitted') {
                    statusClass = 'bg-indigo-500/10 text-indigo-600 border border-indigo-500/20';
                    if (['super_admin', 'medical_director', 'chief_medical_officer'].includes(role)) {
                        actionHTML = `<button onclick="approveResult(${req.id})" class="bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] font-bold px-3 py-1.5 rounded-xl transition cursor-pointer">Approve Report</button>`;
                    } else {
                        actionHTML = `<span class="text-slate-800 dark:text-slate-200 font-semibold text-[10px]">Awaiting Approval</span>`;
                    }
                } else if (req.status === 'approved') {
                    statusClass = 'bg-emerald-500/10 text-emerald-600 border border-emerald-500/20';
                    actionHTML = `
                        <button onclick="openPrintReportModal(${req.id})" class="bg-slate-800 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-705 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 text-[10px] font-bold px-2.5 py-1.5 rounded-xl transition cursor-pointer">
                            Print Report
                        </button>
                    `;
                }

                return `
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/20 transition">
                        <td class="py-3.5 px-6">
                            <span class="font-bold text-slate-850 dark:text-white">${req.patient.first_name} ${req.patient.last_name}</span>
                            <div class="text-[9px] text-slate-500">Code: ${req.patient.immigration_service_number}</div>
                        </td>
                        <td class="py-3.5 px-6">
                            <span class="font-bold text-slate-800 dark:text-slate-200">${req.test_name}</span>
                            <div class="text-[9px] text-slate-500">Ordered by: Dr. ${req.doctor?.full_name || 'Staff'}</div>
                        </td>
                        <td class="py-3.5 px-6 text-slate-800 dark:text-slate-200">${new Date(req.created_at).toLocaleDateString()}</td>
                        <td class="py-3.5 px-6">
                            <span class="px-2.5 py-0.5 text-[9px] font-bold rounded-full uppercase ${statusClass}">
                                ${req.status.replace('_', ' ')}
                            </span>
                        </td>
                        <td class="py-3.5 px-6 text-right">${actionHTML}</td>
                    </tr>
                `;
            }).join('');
        } else {
            tbody.innerHTML = `<tr><td colspan="5" class="py-8 text-center text-slate-500 text-xs font-semibold">No laboratory requests found matching active filters.</td></tr>`;
        }
    }

    async function collectSample(id) {
        if (!confirm('Log sample collection for this test request?')) return;
        try {
            await api.post(`/diagnostics/lab/collect-sample/${id}`);
            alert('Sample registered! Status set to Collected.');
            loadLabQueue();
        } catch (err) {
            alert(err.message || 'Sample collection failed.');
        }
    }

    function openResultModal(req) {
        activeRequest = req;
        document.getElementById('result-form').reset();
        document.getElementById('result-modal').classList.remove('hidden');
    }

    function closeResultModal() {
        document.getElementById('result-modal').classList.add('hidden');
    }

    async function handleResultSubmit(e) {
        e.preventDefault();
        const payload = {
            result_value: document.getElementById('result_value').value,
            normal_range_min: document.getElementById('normal_range_min').value || null,
            normal_range_max: document.getElementById('normal_range_max').value || null,
            unit: document.getElementById('unit').value || null,
            remarks: document.getElementById('remarks').value || null
        };

        try {
            await api.post(`/diagnostics/lab/submit-result/${activeRequest.id}`, payload);
            alert('Laboratory draft results submitted for medical review!');
            closeResultModal();
            loadLabQueue();
        } catch (err) {
            alert(err.message || 'Failed to submit diagnostics values.');
        }
    }

    async function approveResult(id) {
        if (!confirm('Authorize and release this diagnostic report?')) return;
        try {
            await api.post(`/diagnostics/lab/approve-result/${id}`);
            alert('Diagnostic report released successfully!');
            loadLabQueue();
        } catch (err) {
            alert(err.message || 'Approval failed.');
        }
    }

    function openPrintReportModal(id) {
        const req = labWorklist.find(r => r.id === id);
        if (!req) return;

        document.getElementById('rep-patient-name').innerText = `${req.patient.first_name} ${req.patient.last_name}`;
        document.getElementById('rep-patient-code').innerText = req.patient.immigration_service_number;
        document.getElementById('rep-doctor-name').innerText = `Dr. ${req.doctor?.full_name || 'Staff'}`;
        document.getElementById('rep-date').innerText = new Date(req.updated_at || req.created_at).toLocaleString();

        document.getElementById('rep-test-name').innerText = req.test_name;
        document.getElementById('rep-value').innerText = req.result_value || 'Pending';
        document.getElementById('rep-range').innerText = (req.normal_range_min || req.normal_range_max) 
            ? `${req.normal_range_min || '0'} - ${req.normal_range_max || '∞'}` 
            : 'N/A';
        document.getElementById('rep-unit').innerText = req.unit || 'N/A';
        document.getElementById('rep-remarks').innerText = req.remarks || 'No pathology comments provided.';
        
        document.getElementById('rep-stamp').innerText = `Verification ID: NIS-LAB-VERIFIED-${req.id}-${new Date(req.updated_at).getTime()}`;

        document.getElementById('print-report-modal').classList.remove('hidden');
    }

    function closePrintReportModal() {
        document.getElementById('print-report-modal').classList.add('hidden');
    }

    function printReportVoucher() {
        window.print();
    }

    document.addEventListener('DOMContentLoaded', () => {
        loadLabQueue();
    });
</script>

<style>
    @media print {
        header, footer, aside, nav, button, select, h1, p, input, .grid, .bg-white:not(#print-report-modal), #result-modal {
            display: none !important;
        }
        body {
            background: white !important;
            color: black !important;
        }
        #print-report-modal {
            position: absolute !important;
            left: 0 !important;
            top: 0 !important;
            width: 100% !important;
            height: auto !important;
            display: block !important;
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        #print-report-modal > div {
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            margin: 0 !important;
            max-width: 100% !important;
        }
        #printable-report-area {
            border: none !important;
            padding: 0 !important;
            width: 100% !important;
        }
        button {
            display: none !important;
        }
    }
</style>
@endsection
