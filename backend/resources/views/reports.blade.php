@extends('layouts.app')

@section('title', 'Analytics & Reports – NIS Medical Services Portal')

@section('content')
<div class="space-y-6">

    <!-- Print-only branded header -->
    <div class="print-only text-center mb-4 pb-3 border-b border-slate-300">
        <img src="/images/nis_logo.jpg" alt="NIS" style="height:64px;width:64px;object-fit:contain;margin:0 auto 6px;">
        <h2 style="font-weight:800;text-transform:uppercase;letter-spacing:.05em;">Nigeria Immigration Service</h2>
        <p style="font-size:12px;color:#475569;">Hospital Medical Services Portal, Abuja | Analytics Report</p>
    </div>

    <!-- Page Header -->
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-white">Analytics & Reports</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Executive intelligence dashboard · KPIs, trends & clinical metrics</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <select id="period-filter" onchange="loadAll()" class="text-xs px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                <option value="today">Today</option>
                <option value="week">This Week</option>
                <option value="month" selected>This Month</option>
                <option value="quarter">This Quarter</option>
                <option value="year">This Year</option>
            </select>
            <button onclick="exportCSV()" class="bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-850 text-slate-700 dark:text-slate-200 text-xs font-semibold px-3 py-2 rounded-xl flex items-center gap-1.5 transition cursor-pointer">
                <i data-lucide="download" class="w-3.5 h-3.5"></i> Export CSV
            </button>
            <button onclick="window.print()" class="bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-850 text-slate-700 dark:text-slate-200 text-xs font-semibold px-3 py-2 rounded-xl flex items-center gap-1.5 transition cursor-pointer">
                <i data-lucide="printer" class="w-3.5 h-3.5"></i> Print
            </button>
            <button onclick="loadAll()" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold px-4 py-2 rounded-xl flex items-center gap-1.5 transition cursor-pointer">
                <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Date Range Banner -->
    <div class="bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 rounded-xl px-4 py-2.5 flex items-center gap-2 text-xs text-emerald-800 dark:text-emerald-300 font-semibold" id="date-banner">
        <i data-lucide="calendar-range" class="w-3.5 h-3.5"></i>
        <span id="date-range-text">Loading date range...</span>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4" id="kpi-grid">
        @foreach([
            ['id'=>'kpi-patients','label'=>'Total Patients','icon'=>'users','color'=>'emerald'],
            ['id'=>'kpi-visits','label'=>'Outpatient Visits','icon'=>'stethoscope','color'=>'blue'],
            ['id'=>'kpi-revenue','label'=>'Revenue (₦)','icon'=>'trending-up','color'=>'violet'],
            ['id'=>'kpi-admissions','label'=>'Admissions','icon'=>'hospital','color'=>'amber'],
            ['id'=>'kpi-emergencies','label'=>'Emergencies','icon'=>'alert-circle','color'=>'red'],
            ['id'=>'kpi-referrals','label'=>'Referrals','icon'=>'share-2','color'=>'indigo'],
            ['id'=>'kpi-lab','label'=>'Lab Tests','icon'=>'test-tube','color'=>'teal'],
            ['id'=>'kpi-appt-rate','label'=>'Appt. Completion %','icon'=>'bar-chart','color'=>'orange'],
            ['id'=>'kpi-best-doctor','label'=>'Best Performing Doctor','icon'=>'award','color'=>'rose'],
        ] as $k)
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4">
            <div class="flex items-start justify-between mb-3">
                <div class="p-2 rounded-xl bg-{{ $k['color'] }}-50 dark:bg-{{ $k['color'] }}-500/10 text-{{ $k['color'] }}-600 dark:text-{{ $k['color'] }}-400">
                    <i data-lucide="{{ $k['icon'] }}" class="w-4 h-4"></i>
                </div>
            </div>
            <p class="text-xl font-black text-slate-800 dark:text-white" id="{{ $k['id'] }}">–</p>
            <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1">{{ $k['label'] }}</p>
        </div>
        @endforeach
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Patient Flow Trend -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5">
            <h3 class="text-sm font-bold text-slate-800 dark:text-white mb-4">Patient Flow Trend</h3>
            <canvas id="flow-chart" height="200"></canvas>
        </div>

        <!-- Emergency Triage Breakdown -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5">
            <h3 class="text-sm font-bold text-slate-800 dark:text-white mb-4">Emergency Triage Distribution</h3>
            <div class="flex items-center justify-center h-48">
                <canvas id="triage-chart"></canvas>
            </div>
        </div>
    </div>

    <!-- Admissions by Ward -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5">
        <h3 class="text-sm font-bold text-slate-800 dark:text-white mb-4">Admissions by Ward</h3>
        <canvas id="ward-chart" height="100"></canvas>
    </div>

    <!-- Revenue & Pending -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 rounded-2xl p-5">
            <div class="flex items-center gap-3 mb-2">
                <i data-lucide="trending-up" class="w-5 h-5 text-emerald-600"></i>
                <span class="text-sm font-bold text-emerald-800 dark:text-emerald-300">Revenue Collected</span>
            </div>
            <p class="text-3xl font-black text-emerald-700 dark:text-emerald-400" id="revenue-collected">–</p>
        </div>
        <div class="bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 rounded-2xl p-5">
            <div class="flex items-center gap-3 mb-2">
                <i data-lucide="clock" class="w-5 h-5 text-amber-600"></i>
                <span class="text-sm font-bold text-amber-800 dark:text-amber-300">Pending Revenue</span>
            </div>
            <p class="text-3xl font-black text-amber-700 dark:text-amber-400" id="revenue-pending">–</p>
        </div>
    </div>

    <!-- ── Finance depth ─────────────────────────────────────────── -->
    <div class="pt-2">
        <h2 class="text-lg font-black text-slate-800 dark:text-white flex items-center gap-2"><i data-lucide="wallet" class="text-emerald-600 w-5 h-5"></i> Finance Reports</h2>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <!-- Daily cash reconciliation -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-bold text-slate-800 dark:text-white">Daily Cash Reconciliation</h3>
                <input type="date" id="cash-date" onchange="loadCashRecon()" class="bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg px-2 py-1.5 text-xs">
            </div>
            <p class="text-2xl font-black text-emerald-600" id="cash-total">₦0</p>
            <p class="text-[10px] text-slate-500 mb-3"><span id="cash-txns">0</span> transactions</p>
            <div class="text-xs">
                <div class="font-bold text-slate-500 uppercase text-[9px] tracking-wide mb-1">By Method</div>
                <div id="cash-methods" class="space-y-1"></div>
                <div class="font-bold text-slate-500 uppercase text-[9px] tracking-wide mt-3 mb-1">By Cashier</div>
                <div id="cash-cashiers" class="space-y-1"></div>
            </div>
        </div>

        <!-- Revenue by department -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5">
            <h3 class="text-sm font-bold text-slate-800 dark:text-white mb-3">Revenue by Department (this month)</h3>
            <div id="rev-dept" class="space-y-2 text-xs"></div>
        </div>
    </div>

    <!-- Debtor aging -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5">
        <h3 class="text-sm font-bold text-slate-800 dark:text-white mb-3">Debtor Aging</h3>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4" id="aging-buckets"></div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-950 text-slate-700 dark:text-slate-300"><tr>
                    <th class="py-2 px-3 font-bold">Patient</th><th class="py-2 px-3 font-bold">Code</th>
                    <th class="py-2 px-3 font-bold">Invoices</th><th class="py-2 px-3 font-bold">Oldest (days)</th>
                    <th class="py-2 px-3 font-bold text-right">Outstanding</th>
                </tr></thead>
                <tbody id="debtors-body" class="divide-y divide-slate-100 dark:divide-slate-800"></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
    let flowChart, triageChart, wardChart;

    function fmt(n) {
        if (n === undefined || n === null) return '–';
        if (n >= 1000000) return '₦' + (n/1000000).toFixed(1) + 'M';
        if (n >= 1000)    return '₦' + (n/1000).toFixed(1) + 'K';
        return n.toString();
    }

    function isDark() { return document.documentElement.classList.contains('dark'); }
    function chartColor() { return isDark() ? '#94a3b8' : '#64748b'; }
    function gridColor()  { return isDark() ? '#1e293b' : '#f1f5f9'; }

    async function loadExecutive() {
        const period = document.getElementById('period-filter').value;
        try {
            const data = await window.api.get(`/reports?period=${period}`);
            const k = data.kpis || {};

            // Date range
            if (data.date_range) {
                document.getElementById('date-range-text').textContent = `${data.date_range.start} → ${data.date_range.end}`;
            }

            // KPIs
            document.getElementById('kpi-patients').textContent   = k.total_patients      ?? 0;
            document.getElementById('kpi-visits').textContent     = k.total_visits         ?? 0;
            document.getElementById('kpi-revenue').textContent    = fmt(k.total_revenue    ?? 0);
            document.getElementById('kpi-admissions').textContent = k.total_admissions     ?? 0;
            document.getElementById('kpi-emergencies').textContent= k.total_emergencies    ?? 0;
            document.getElementById('kpi-referrals').textContent  = k.total_referrals      ?? 0;
            document.getElementById('kpi-lab').textContent        = k.lab_tests            ?? 0;
            document.getElementById('kpi-appt-rate').textContent  = (k.appointment_completion_rate ?? 0) + '%';
            document.getElementById('kpi-best-doctor').textContent = k.best_doctor          ?? 'N/A';
            document.getElementById('revenue-collected').textContent = fmt(k.total_revenue   ?? 0);
            document.getElementById('revenue-pending').textContent   = fmt(k.pending_revenue ?? 0);

            // Triage Chart
            const triage = data.emergency_by_triage || {};
            renderTriageChart(triage);

            // Ward Chart
            const wards = data.admissions_by_ward || {};
            renderWardChart(wards);

        } catch(e) { console.error('Executive report error:', e); }
    }

    function exportCSV() {
        const period = document.getElementById('period-filter').value;
        const kpis = [
            ['Metric', 'Value'],
            ['Total Patients', document.getElementById('kpi-patients').innerText],
            ['Outpatient Visits', document.getElementById('kpi-visits').innerText],
            ['Revenue Collected (₦)', document.getElementById('kpi-revenue').innerText],
            ['Admissions', document.getElementById('kpi-admissions').innerText],
            ['Emergencies', document.getElementById('kpi-emergencies').innerText],
            ['Referrals', document.getElementById('kpi-referrals').innerText],
            ['Lab Tests', document.getElementById('kpi-lab').innerText],
            ['Appt. Completion %', document.getElementById('kpi-appt-rate').innerText],
            ['Best Performing Doctor', document.getElementById('kpi-best-doctor').innerText],
            ['Revenue Pending (₦)', document.getElementById('revenue-pending').innerText]
        ];

        let csvContent = "data:text/csv;charset=utf-8,";
        kpis.forEach(row => {
            csvContent += row.map(v => `"${v.replace(/"/g, '""')}"`).join(",") + "\r\n";
        });

        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", `NIS_Executive_Report_${period}_${new Date().toISOString().split('T')[0]}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    async function loadPatientFlow() {
        const period = document.getElementById('period-filter').value;
        try {
            const data = await window.api.get(`/reports/patient-flow?period=${period}`);
            renderFlowChart(data);
        } catch(e) {}
    }

    function renderFlowChart(data) {
        const labels = Object.keys(data.visits || {});
        const visits = Object.values(data.visits || {});
        const appts  = labels.map(l => (data.appointments || {})[l] ?? 0);
        const emgs   = labels.map(l => (data.emergencies  || {})[l] ?? 0);

        if (flowChart) flowChart.destroy();
        flowChart = new Chart(document.getElementById('flow-chart'), {
            type: 'line',
            data: {
                labels,
                datasets: [
                    { label: 'Visits',       data: visits, borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.1)', fill: true, tension: 0.4, pointRadius: 3 },
                    { label: 'Appointments', data: appts,  borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.05)', fill: false, tension: 0.4, pointRadius: 3 },
                    { label: 'Emergencies',  data: emgs,   borderColor: '#ef4444', backgroundColor: 'rgba(239,68,68,0.05)',  fill: false, tension: 0.4, pointRadius: 3 },
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: true,
                plugins: { legend: { labels: { color: chartColor(), font: { size: 10 } } } },
                scales: {
                    x: { ticks: { color: chartColor(), font: { size: 9 } }, grid: { color: gridColor() } },
                    y: { ticks: { color: chartColor(), font: { size: 9 } }, grid: { color: gridColor() }, beginAtZero: true }
                }
            }
        });
    }

    function renderTriageChart(triage) {
        const labels = ['Red (Critical)', 'Yellow (Urgent)', 'Green (Minor)', 'Black (Expectant)'];
        const values = [triage.red ?? 0, triage.yellow ?? 0, triage.green ?? 0, triage.black ?? 0];
        if (triageChart) triageChart.destroy();
        triageChart = new Chart(document.getElementById('triage-chart'), {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{ data: values, backgroundColor: ['#ef4444','#f59e0b','#10b981','#1e293b'], borderWidth: 0 }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { color: chartColor(), font: { size: 10 }, padding: 10 } } }
            }
        });
    }

    function renderWardChart(wards) {
        const labels = Object.keys(wards);
        const values = Object.values(wards);
        if (wardChart) wardChart.destroy();
        wardChart = new Chart(document.getElementById('ward-chart'), {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Admissions',
                    data: values,
                    backgroundColor: 'rgba(16,185,129,0.7)',
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: true,
                plugins: { legend: { display: false } },
                scales: {
                    x: { ticks: { color: chartColor(), font: { size: 9 } }, grid: { display: false } },
                    y: { ticks: { color: chartColor(), font: { size: 9 } }, grid: { color: gridColor() }, beginAtZero: true }
                }
            }
        });
    }

    const naira = (v) => '₦' + Number(v || 0).toLocaleString();

    async function loadCashRecon() {
        const date = document.getElementById('cash-date').value;
        try {
            const res = await window.api.get('/reports/cash-reconciliation' + (date ? `?date=${date}` : ''));
            document.getElementById('cash-total').innerText = naira(res.total_collected);
            document.getElementById('cash-txns').innerText = res.transactions;
            const row = (label, r) => `<div class="flex justify-between"><span class="text-slate-600 dark:text-slate-300">${label} <span class="text-slate-400">(${r.count})</span></span><b class="text-slate-800 dark:text-white">${naira(r.total)}</b></div>`;
            document.getElementById('cash-methods').innerHTML = (res.by_method||[]).map(m => row(m.payment_method, m)).join('') || '<span class="text-slate-400">No payments.</span>';
            document.getElementById('cash-cashiers').innerHTML = (res.by_cashier||[]).map(c => row(c.cashier, c)).join('') || '<span class="text-slate-400">—</span>';
        } catch (e) {}
    }

    async function loadRevenueByDept() {
        try {
            const res = await window.api.get('/reports/revenue-by-department?period=month');
            const max = Math.max(1, ...(res.departments||[]).map(d => Number(d.total)));
            document.getElementById('rev-dept').innerHTML = (res.departments||[]).map(d => `
                <div>
                    <div class="flex justify-between mb-0.5"><span class="text-slate-700 dark:text-slate-300">${d.department}</span><b class="text-slate-800 dark:text-white">${naira(d.total)}</b></div>
                    <div class="h-1.5 rounded-full bg-slate-100 dark:bg-slate-800"><div class="h-1.5 rounded-full bg-emerald-500" style="width:${(Number(d.total)/max*100).toFixed(0)}%"></div></div>
                </div>`).join('') || '<span class="text-slate-400">No revenue this month.</span>';
        } catch (e) {}
    }

    async function loadDebtorAging() {
        try {
            const res = await window.api.get('/reports/debtor-aging');
            const b = res.buckets || {};
            const colors = {'0-30':'emerald','31-60':'blue','61-90':'amber','90+':'red'};
            document.getElementById('aging-buckets').innerHTML = Object.keys(b).map(k => `
                <div class="border border-slate-200 dark:border-slate-800 rounded-xl p-3">
                    <div class="text-base font-black text-${colors[k]}-600">${naira(b[k])}</div>
                    <div class="text-[9px] text-slate-500 uppercase font-semibold">${k} days</div>
                </div>`).join('');
            document.getElementById('debtors-body').innerHTML = (res.debtors||[]).length ? res.debtors.map(d => `
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30">
                    <td class="py-2 px-3 font-semibold text-slate-800 dark:text-white">${d.patient}</td>
                    <td class="py-2 px-3 font-mono">${d.hospital_code||'—'}</td>
                    <td class="py-2 px-3">${d.invoices}</td>
                    <td class="py-2 px-3">${d.oldest_days}</td>
                    <td class="py-2 px-3 text-right font-bold text-red-600">${naira(d.outstanding)}</td>
                </tr>`).join('') : '<tr><td colspan="5" class="py-4 text-center text-slate-400">No outstanding debtors.</td></tr>';
        } catch (e) {}
    }

    function loadAll() {
        loadExecutive();
        loadPatientFlow();
        loadCashRecon();
        loadRevenueByDept();
        loadDebtorAging();
    }

    document.addEventListener('DOMContentLoaded', loadAll);
</script>
@endsection
