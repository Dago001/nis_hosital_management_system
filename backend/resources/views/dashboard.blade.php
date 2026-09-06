@extends('layouts.app')

@section('title', 'Dashboard - NIS Medical Services Portal')

@section('content')
<div class="space-y-6">

    <!-- Welcome Banner -->
    <div class="bg-gradient-to-r from-[#006633] via-emerald-800 to-[#004422] p-6 rounded-2xl text-white shadow-xl relative overflow-hidden border border-emerald-700/30">
        <div class="relative z-10 flex items-center justify-between flex-wrap gap-4">
            <div class="space-y-1.5">
                <h1 class="text-xl font-black tracking-tight flex items-center gap-2">
                    
                    Nigeria Immigration Service Hospital
                </h1>
                <p class="text-emerald-100 text-xs">
                    Welcome back, <span class="font-bold text-white" id="welcome-name">User</span> 
                </p>
                <div class="inline-flex items-center gap-1.5 mt-1 px-3 py-1 bg-white/15 rounded-full text-[10px] font-bold uppercase tracking-wider border border-white/20" id="welcome-role">
                    Staff Portal
                </div>
            </div>
            <div class="hidden md:flex items-center gap-3 text-[10px] font-bold uppercase tracking-wider text-emerald-200">
                <span class="flex items-center gap-1"><i data-lucide="clock" class="w-3.5 h-3.5"></i> <span id="live-clock"></span></span>
                <span class="w-px h-4 bg-white/20"></span>
                <span class="flex items-center gap-1"><i data-lucide="calendar" class="w-3.5 h-3.5"></i> <span id="live-date"></span></span>
            </div>
        </div>
        <div class="absolute right-0 bottom-0 opacity-5 translate-y-1/4 translate-x-1/4 pointer-events-none">
            <i data-lucide="activity" class="w-52 h-52"></i>
        </div>
    </div>

    <!-- Spinner Loading -->
    <div id="dashboard-loading" class="flex items-center justify-center min-h-[350px]">
        <div class="text-center space-y-3">
            <div class="w-10 h-10 border-4 border-emerald-600 border-t-transparent rounded-full animate-spin mx-auto"></div>
            <p class="text-xs text-slate-500 font-semibold">Loading analytics dashboard...</p>
        </div>
    </div>

    <!-- Dashboard Content Container (injected dynamically) -->
    <div id="dashboard-view" class="hidden space-y-6"></div>
</div>
@endsection

@section('scripts')
<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Live Clock
    function updateClock() {
        const now = new Date();
        const timeEl = document.getElementById('live-clock');
        const dateEl = document.getElementById('live-date');
        if (timeEl) timeEl.textContent = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        if (dateEl) dateEl.textContent = now.toLocaleDateString([], { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' });
    }
    setInterval(updateClock, 1000);
    updateClock();

    const CHART_MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    // Helper: stat card HTML
    function statCard(label, value, sub, icon, colorClass, trend = null) {
        const trendHTML = trend !== null
            ? `<div class="text-[9px] flex items-center gap-1 mt-1 ${trend >= 0 ? 'text-emerald-600' : 'text-red-500'} font-bold">
                <i data-lucide="${trend >= 0 ? 'trending-up' : 'trending-down'}" class="w-3 h-3"></i>
                ${Math.abs(trend)}% from last month
              </div>`
            : `<div class="text-[9px] text-slate-500 dark:text-slate-400 mt-1 font-semibold">${sub}</div>`;
        return `
            <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition-shadow group">
                <div class="flex items-start justify-between">
                    <div class="space-y-1 flex-grow">
                        <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">${label}</span>
                        <p class="text-2xl font-black text-slate-900 dark:text-white">${value}</p>
                        ${trendHTML}
                    </div>
                    <div class="p-3 rounded-2xl ${colorClass} shrink-0 group-hover:scale-105 transition-transform">
                        <i data-lucide="${icon}" class="w-5 h-5"></i>
                    </div>
                </div>
            </div>`;
    }

    // Helper: section header
    function sectionHeader(title, subtitle) {
        return `<div class="flex items-center gap-2 mb-4">
            <div class="w-1 h-5 rounded-full bg-emerald-600"></div>
            <div>
                <h3 class="text-sm font-black text-slate-800 dark:text-white">${title}</h3>
                ${subtitle ? `<p class="text-[10px] text-slate-500 dark:text-slate-400">${subtitle}</p>` : ''}
            </div>
        </div>`;
    }

    async function loadDashboard() {
        const loading = document.getElementById('dashboard-loading');
        const view = document.getElementById('dashboard-view');

        try {
            document.getElementById('welcome-name').innerText = user.name || 'User';
            const roleLabel = user.roles && user.roles[0] ? user.roles[0].display_name || user.roles[0].name.replace('_', ' ') : 'Staff';
            document.getElementById('welcome-role').innerText = `${roleLabel} Portal`;

            const res = await api.get('/dashboard');
            loading.classList.add('hidden');
            view.classList.remove('hidden');

            renderDashboardByRole(res.role, res.metrics, res);
        } catch (err) {
            loading.innerHTML = `
                <div class="p-6 bg-red-500/10 border border-red-500/20 text-red-500 rounded-2xl text-xs font-semibold flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                    Failed to load dashboard metrics. Check backend connection.
                </div>`;
        }
    }

    function renderDashboardByRole(role, metrics, rawData) {
        const view = document.getElementById('dashboard-view');

        // ─── 1. Executive / Admin View ───────────────────────────────────────────
        if (['super_admin', 'medical_director', 'hospital_admin', 'chief_medical_officer'].includes(role)) {

            const admissions = rawData.recent_admissions || [];
            const admissionsHTML = admissions.length > 0
                ? admissions.map(a => `
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition text-xs">
                        <td class="py-3 px-4 font-semibold text-slate-800 dark:text-white">${a.patient?.first_name || '·'} ${a.patient?.last_name || ''}</td>
                        <td class="py-3 px-4 text-slate-500 dark:text-slate-400">${a.patient?.immigration_service_number || '·'}</td>
                        <td class="py-3 px-4 text-slate-500 dark:text-slate-400">${a.bed?.ward?.name || '·'}</td>
                        <td class="py-3 px-4"><span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">Active</span></td>
                    </tr>`).join('')
                : `<tr><td colspan="4" class="py-6 text-center text-xs text-slate-400">No recent admissions.</td></tr>`;

            view.innerHTML = `
                <!-- KPI Stats Row -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    ${statCard('Total Patients', (metrics.total_patients || 0).toLocaleString(), `${metrics.total_male_patients || 0} M / ${metrics.total_female_patients || 0} F`, 'users', 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600', null)}
                    ${statCard('Staff Roster', (metrics.total_staff_onboarded || 0).toLocaleString(), 'Active clinical staff', 'briefcase-medical', 'bg-blue-50 dark:bg-blue-500/10 text-blue-600', null)}
                    ${statCard("Today's Revenue", '₦' + (metrics.today_revenue || 0).toLocaleString(), 'Total: ₦' + (metrics.total_revenue || 0).toLocaleString(), 'banknote', 'bg-amber-50 dark:bg-amber-500/10 text-amber-600', null)}
                    ${statCard('Bed Occupancy', (metrics.bed_occupancy_rate || 0) + '%', (metrics.active_admissions || 0) + ' active admissions', 'bed', 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600', null)}
                </div>

                <!-- Charts + Quick Actions Row -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Revenue Line Chart -->
                    <div class="lg:col-span-2 bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
                        ${sectionHeader('Revenue Trend', 'Monthly payment collections from billing')}
                        <div class="h-56 relative">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>

                    <!-- Donut: Patient Gender Split -->
                    <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
                        ${sectionHeader('Patient Demographics', 'Gender distribution')}
                        <div class="h-44 relative flex items-center justify-center">
                            <canvas id="genderChart"></canvas>
                        </div>
                        <div class="flex items-center justify-center gap-6 mt-3 text-xs font-semibold">
                            <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span> Male (${metrics.total_male_patients || 0})</span>
                            <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-blue-400 inline-block"></span> Female (${metrics.total_female_patients || 0})</span>
                        </div>
                    </div>
                </div>

                <!-- Second row: admission table + quick actions -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Recent Admissions -->
                    <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900">
                            ${sectionHeader('Recent Admissions', 'Last 5 ward admissions')}
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead class="bg-slate-50 dark:bg-slate-950 text-[10px] text-slate-500 uppercase tracking-wider font-bold border-b border-slate-100 dark:border-slate-800">
                                    <tr>
                                        <th class="py-3 px-4">Patient</th>
                                        <th class="py-3 px-4">Hospital Code</th>
                                        <th class="py-3 px-4">Ward</th>
                                        <th class="py-3 px-4">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">${admissionsHTML}</tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Quick Admin Actions -->
                    <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
                        ${sectionHeader('Quick Actions', 'Common administrative tasks')}
                        <div class="space-y-3">
                            <a href="/admin/users" class="flex items-center gap-3 p-3.5 bg-emerald-50 dark:bg-emerald-500/10 hover:bg-emerald-100 border border-emerald-200 dark:border-emerald-500/20 text-emerald-700 dark:text-emerald-400 rounded-xl transition text-xs font-bold">
                                <i data-lucide="users" class="w-4 h-4 shrink-0"></i> Manage Staff Credentials
                            </a>
                            <a href="/audit-trail" class="flex items-center gap-3 p-3.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 rounded-xl transition text-xs font-bold">
                                <i data-lucide="clipboard-list" class="w-4 h-4 shrink-0"></i> Inspect Security Audit Logs
                            </a>
                            <a href="/patients" class="flex items-center gap-3 p-3.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 rounded-xl transition text-xs font-bold">
                                <i data-lucide="folder-open" class="w-4 h-4 shrink-0"></i> Browse Patient Registry
                            </a>
                            <a href="/billing" class="flex items-center gap-3 p-3.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 rounded-xl transition text-xs font-bold">
                                <i data-lucide="receipt" class="w-4 h-4 shrink-0"></i> View Billing & Collections
                            </a>
                        </div>
                    </div>
                </div>
            `;

            setTimeout(() => {
                // Revenue line chart
                const rCtx = document.getElementById('revenueChart');
                if (rCtx) {
                    const trend = rawData.revenue_trend || [];
                    const labels = trend.map(t => {
                        const p = t.month.split('-');
                        return `${CHART_MONTHS[parseInt(p[1]) - 1]} '${p[0].slice(2)}`;
                    });
                    const data = trend.map(t => parseFloat(t.amount) || 0);
                    new Chart(rCtx, {
                        type: 'line',
                        data: {
                            labels,
                            datasets: [{
                                label: 'Revenue (₦)',
                                data,
                                borderColor: '#006633',
                                backgroundColor: 'rgba(0,102,51,0.07)',
                                fill: true,
                                tension: 0.4,
                                borderWidth: 2.5,
                                pointBackgroundColor: '#006633',
                                pointRadius: 3
                            }]
                        },
                        options: {
                            responsive: true, maintainAspectRatio: false,
                            plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ' ₦' + ctx.raw.toLocaleString() } } },
                            scales: {
                                y: { grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { font: { size: 9 }, callback: v => '₦' + v.toLocaleString() } },
                                x: { grid: { display: false }, ticks: { font: { size: 9 } } }
                            }
                        }
                    });
                }
                // Gender donut
                const gCtx = document.getElementById('genderChart');
                if (gCtx) {
                    new Chart(gCtx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Male', 'Female'],
                            datasets: [{ data: [metrics.total_male_patients || 0, metrics.total_female_patients || 0], backgroundColor: ['#10b981', '#60a5fa'], borderWidth: 0 }]
                        },
                        options: {
                            responsive: true, maintainAspectRatio: false, cutout: '68%',
                            plugins: { legend: { display: false } }
                        }
                    });
                }
                lucide.createIcons();
            }, 100);
        }

        // ─── 2. Doctor / Consultant View ─────────────────────────────────────────
        else if (['doctor', 'consultant', 'dental_officer', 'eye_clinic_officer', 'physiotherapist', 'theatre_manager'].includes(role)) {
            const queueHTML = rawData.queue && rawData.queue.length > 0
                ? rawData.queue.map((visit, i) => `
                    <div class="p-4 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-800/30 transition rounded-xl">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-emerald-100 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 font-black flex items-center justify-center text-xs shrink-0">${i + 1}</div>
                            <div>
                                <h4 class="text-xs font-bold text-slate-900 dark:text-white">${visit.patient?.first_name || '·'} ${visit.patient?.last_name || ''}</h4>
                                <span class="text-[10px] text-slate-500 font-mono">${visit.patient?.immigration_service_number || '·'}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 text-[9px] font-bold rounded-full bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400 border border-amber-200 dark:border-amber-500/20">Awaiting</span>
                            <a href="/consultations" class="bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] font-bold px-3 py-1.5 rounded-xl transition">Start</a>
                        </div>
                    </div>`)
                    .join('')
                : `<div class="p-10 text-center text-slate-400 text-xs"><i data-lucide="check-circle" class="w-8 h-8 mx-auto mb-2 text-emerald-400"></i>No patients in your queue.</div>`;

            view.innerHTML = `
                <div class="grid grid-cols-3 gap-4">
                    ${statCard('Consulted Today', metrics.consulted_today || 0, 'Completed sessions', 'check-circle', 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600')}
                    ${statCard('Queue Size', metrics.my_appointments_today || 0, 'Patients assigned to you', 'clock', 'bg-amber-50 dark:bg-amber-500/10 text-amber-600')}
                    ${statCard('Active Admissions', metrics.active_admissions || 0, 'Hospital-wide', 'bed', 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600')}
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50">
                            ${sectionHeader('My Patient Queue', 'Patients assigned by nursing triage')}
                        </div>
                        <div class="divide-y divide-slate-100 dark:divide-slate-800 p-2">${queueHTML}</div>
                    </div>
                    <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
                        ${sectionHeader('Quick Actions', '')}
                        <div class="space-y-3">
                            <a href="/consultations" class="flex items-center gap-3 p-3.5 bg-emerald-50 dark:bg-emerald-500/10 hover:bg-emerald-100 border border-emerald-200 dark:border-emerald-500/20 text-emerald-700 dark:text-emerald-400 rounded-xl transition text-xs font-bold">
                                <i data-lucide="stethoscope" class="w-4 h-4 shrink-0"></i> Open Consultation SOAP
                            </a>
                            <a href="/patients" class="flex items-center gap-3 p-3.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 rounded-xl transition text-xs font-bold">
                                <i data-lucide="folder-open" class="w-4 h-4 shrink-0"></i> Browse Patient Files
                            </a>
                            <a href="/laboratory" class="flex items-center gap-3 p-3.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 rounded-xl transition text-xs font-bold">
                                <i data-lucide="test-tube" class="w-4 h-4 shrink-0"></i> View Lab Orders
                            </a>
                        </div>
                    </div>
                </div>`;
        }

        // ─── 3. Nurse View ────────────────────────────────────────────────────────
        else if (role === 'nurse' || role === 'ward_manager') {
            const vitalsQueue = rawData.recent_visits_for_vitals || [];
            const vitalsHTML = vitalsQueue.length > 0
                ? vitalsQueue.map((v, i) => `
                    <div class="p-4 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-800/30 transition rounded-xl">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-amber-100 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 font-black flex items-center justify-center text-xs shrink-0">${i + 1}</div>
                            <div>
                                <h4 class="text-xs font-bold text-slate-900 dark:text-white">${v.patient?.first_name || '·'} ${v.patient?.last_name || ''}</h4>
                                <span class="text-[10px] text-slate-500 font-mono">${v.patient?.immigration_service_number || '·'}</span>
                            </div>
                        </div>
                        <a href="/vitals" class="bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] font-bold px-3 py-1.5 rounded-xl transition">Record Vitals</a>
                    </div>`).join('')
                : `<div class="p-10 text-center text-slate-400 text-xs"><i data-lucide="check-circle" class="w-8 h-8 mx-auto mb-2 text-emerald-400"></i>No patients awaiting vitals.</div>`;

            const bedOccupied = rawData.occupied_beds || metrics.occupied_beds || 0;
            const bedAvailable = rawData.available_beds || metrics.available_beds || 0;
            const bedTotal = bedOccupied + bedAvailable;
            const bedPct = bedTotal > 0 ? Math.round((bedOccupied / bedTotal) * 100) : 0;

            view.innerHTML = `
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    ${statCard('Active Admissions', metrics.active_admissions || 0, 'Currently admitted', 'bed', 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600')}
                    ${statCard('Bed Occupancy', bedPct + '%', `${bedOccupied} occupied / ${bedAvailable} free`, 'bar-chart-2', 'bg-amber-50 dark:bg-amber-500/10 text-amber-600')}
                    ${statCard("Today's Appointments", metrics.today_appointments || 0, 'Scheduled visits today', 'calendar', 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600')}
                    ${statCard('Vitals Pending', vitalsQueue.length, 'Patients needing vitals', 'activity', 'bg-red-50 dark:bg-red-500/10 text-red-600')}
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50">
                            ${sectionHeader('Vitals Queue', 'Checked-in patients awaiting vitals capture')}
                        </div>
                        <div class="divide-y divide-slate-100 dark:divide-slate-800 p-2">${vitalsHTML}</div>
                    </div>
                    <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
                        ${sectionHeader('Ward Capacity', 'Live bed occupancy')}
                        <div class="space-y-4">
                            <div>
                                <div class="flex justify-between text-xs font-bold mb-1.5">
                                    <span class="text-slate-700 dark:text-slate-300">Occupancy Rate</span>
                                    <span class="text-emerald-600">${bedPct}%</span>
                                </div>
                                <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2.5 overflow-hidden">
                                    <div class="h-2.5 rounded-full ${bedPct > 80 ? 'bg-red-500' : bedPct > 60 ? 'bg-amber-500' : 'bg-emerald-500'} transition-all" style="width:${bedPct}%"></div>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3 text-xs font-semibold text-center">
                                <div class="p-3 bg-red-50 dark:bg-red-500/10 rounded-xl text-red-600 dark:text-red-400">
                                    <div class="text-lg font-black">${bedOccupied}</div>Occupied
                                </div>
                                <div class="p-3 bg-emerald-50 dark:bg-emerald-500/10 rounded-xl text-emerald-600 dark:text-emerald-400">
                                    <div class="text-lg font-black">${bedAvailable}</div>Available
                                </div>
                            </div>
                        </div>
                        <a href="/vitals" class="mt-4 flex items-center gap-2 p-3 bg-emerald-50 dark:bg-emerald-500/10 hover:bg-emerald-100 border border-emerald-200 dark:border-emerald-500/20 text-emerald-700 dark:text-emerald-400 rounded-xl transition text-xs font-bold">
                            <i data-lucide="activity" class="w-4 h-4 shrink-0"></i> Open Vitals Entry Board
                        </a>
                    </div>
                </div>`;
        }

        // ─── 4. Pharmacist / Inventory View ──────────────────────────────────────
        else if (['pharmacist', 'store_officer', 'inventory_officer', 'procurement_officer'].includes(role)) {
            const lowStock = rawData.low_stock_list || rawData.critical_low_stock || [];
            const alertsHTML = lowStock.length > 0
                ? lowStock.map(item => `
                    <div class="flex items-center justify-between text-xs p-3 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 rounded-xl hover:bg-red-100 transition">
                        <div class="flex items-center gap-2">
                            <i data-lucide="alert-triangle" class="w-3.5 h-3.5 text-red-500 shrink-0"></i>
                            <span class="font-bold text-slate-800 dark:text-white">${item.name}</span>
                        </div>
                        <span class="text-[10px] font-bold text-red-600 dark:text-red-400">${item.quantity_in_stock} left (min: ${item.reorder_level})</span>
                    </div>`).join('')
                : `<p class="text-xs text-emerald-600 font-semibold p-3 flex items-center gap-2"><i data-lucide="check-circle" class="w-4 h-4"></i>All stock levels are healthy.</p>`;

            view.innerHTML = `
                <div class="grid grid-cols-3 gap-4">
                    ${statCard('Total Drug Items', (metrics.total_medicine_items || metrics.total_inventory_items || 0).toLocaleString(), 'In pharmacy inventory', 'package', 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600')}
                    ${statCard('Low Stock Alerts', metrics.low_stock_count || 0, 'Below reorder level', 'alert-triangle', 'bg-amber-50 dark:bg-amber-500/10 text-amber-600')}
                    ${statCard('Expired Batches', metrics.expired_drugs_count || metrics.expired_drugs || 0, 'Requiring disposal', 'trash-2', 'bg-red-50 dark:bg-red-500/10 text-red-600')}
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
                        ${sectionHeader('Critical Reorder Alerts', 'Items below minimum stock threshold')}
                        <div class="space-y-2 max-h-72 overflow-y-auto pr-1">${alertsHTML}</div>
                    </div>
                    <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
                        ${sectionHeader('Quick Actions', '')}
                        <div class="space-y-3">
                            <a href="/pharmacy" class="flex items-center gap-3 p-3.5 bg-emerald-50 dark:bg-emerald-500/10 hover:bg-emerald-100 border border-emerald-200 dark:border-emerald-500/20 text-emerald-700 dark:text-emerald-400 rounded-xl transition text-xs font-bold">
                                <i data-lucide="pill" class="w-4 h-4 shrink-0"></i> Open Dispensary Panel
                            </a>
                            <a href="/pharmacy" class="flex items-center gap-3 p-3.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 rounded-xl transition text-xs font-bold">
                                <i data-lucide="package" class="w-4 h-4 shrink-0"></i> Manage Inventory Catalog
                            </a>
                        </div>
                    </div>
                </div>`;
        }

        // ─── 5. Cashier / Account View ────────────────────────────────────────────
        else if (['cashier', 'account_officer'].includes(role)) {
            view.innerHTML = `
                <div class="grid grid-cols-2 gap-4">
                    ${statCard("Today's Collections", '₦' + (metrics.today_revenue || 0).toLocaleString(), 'Cash & POS payments', 'banknote', 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600')}
                    ${statCard('Unpaid Invoices', metrics.outstanding_invoices_count || metrics.pending_invoices || 0, 'Awaiting settlement', 'file-warning', 'bg-red-50 dark:bg-red-500/10 text-red-600')}
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-center">
                        <div class="text-center space-y-2 text-slate-400">
                            <i data-lucide="credit-card" class="w-10 h-10 mx-auto text-emerald-400"></i>
                            <p class="text-sm font-bold text-slate-700 dark:text-slate-300">Billing Desk Ready</p>
                            <p class="text-xs">Open the Billing module to process invoices and collect payments.</p>
                            <a href="/billing" class="inline-flex items-center gap-2 mt-3 bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-xl text-xs font-bold transition shadow-sm">
                                <i data-lucide="receipt" class="w-4 h-4"></i> Open Cashier Billing Desk
                            </a>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
                        ${sectionHeader('Quick Actions', '')}
                        <div class="space-y-3">
                            <a href="/billing" class="flex items-center gap-3 p-3.5 bg-emerald-50 dark:bg-emerald-500/10 hover:bg-emerald-100 border border-emerald-200 dark:border-emerald-500/20 text-emerald-700 dark:text-emerald-400 rounded-xl transition text-xs font-bold">
                                <i data-lucide="credit-card" class="w-4 h-4 shrink-0"></i> Collect Payment
                            </a>
                            <a href="/patients" class="flex items-center gap-3 p-3.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 rounded-xl transition text-xs font-bold">
                                <i data-lucide="search" class="w-4 h-4 shrink-0"></i> Lookup Patient File
                            </a>
                        </div>
                    </div>
                </div>`;
        }

        // ─── 6. Receptionist View ─────────────────────────────────────────────────
        else if (role === 'receptionist' || role === 'records_officer') {
            view.innerHTML = `
                <div class="grid grid-cols-2 gap-4">
                    ${statCard("Today's Appointments", metrics.today_appointments || 0, 'Booked visits', 'calendar', 'bg-blue-50 dark:bg-blue-500/10 text-blue-600')}
                    ${statCard('Total Patients', (metrics.total_patients || 0).toLocaleString(), 'In registry', 'users', 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600')}
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-center min-h-[200px]">
                        <div class="text-center space-y-2">
                            <i data-lucide="clipboard-check" class="w-10 h-10 mx-auto text-blue-400"></i>
                            <p class="text-sm font-bold text-slate-700 dark:text-slate-300">Appointment Queue</p>
                            <a href="/appointments" class="inline-flex items-center gap-2 mt-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-xs font-bold transition shadow-sm">
                                <i data-lucide="calendar" class="w-4 h-4"></i> Open Appointments Panel
                            </a>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
                        ${sectionHeader('Quick Actions', '')}
                        <div class="space-y-3">
                            <a href="/appointments" class="flex items-center gap-3 p-3.5 bg-blue-50 dark:bg-blue-500/10 hover:bg-blue-100 border border-blue-200 dark:border-blue-500/20 text-blue-700 dark:text-blue-400 rounded-xl transition text-xs font-bold">
                                <i data-lucide="calendar-plus" class="w-4 h-4 shrink-0"></i> Book Appointment
                            </a>
                            <a href="/patients" class="flex items-center gap-3 p-3.5 bg-emerald-50 dark:bg-emerald-500/10 hover:bg-emerald-100 border border-emerald-200 dark:border-emerald-500/20 text-emerald-700 dark:text-emerald-400 rounded-xl transition text-xs font-bold">
                                <i data-lucide="user-plus" class="w-4 h-4 shrink-0"></i> Register New Patient
                            </a>
                            <a href="/support-chats" class="flex items-center gap-3 p-3.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 rounded-xl transition text-xs font-bold">
                                <i data-lucide="message-circle" class="w-4 h-4 shrink-0"></i> Support Chat Desk
                            </a>
                        </div>
                    </div>
                </div>`;
        }

        // ─── 7. Laboratory Scientist View ───────────────────────────────────────────
        else if (role === 'lab_scientist') {
            view.innerHTML = `
                <div class="grid grid-cols-2 gap-4">
                    ${statCard('Pending Lab Orders', metrics.pending_requests || 0, 'Awaiting processing', 'test-tubes', 'bg-amber-50 dark:bg-amber-500/10 text-amber-600')}
                    ${statCard('Completed Today', metrics.completed_today || 0, 'Test results submitted', 'check-circle', 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600')}
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-center min-h-[200px]">
                        <div class="text-center space-y-2">
                            <i data-lucide="microscope" class="w-10 h-10 mx-auto text-amber-500"></i>
                            <p class="text-sm font-bold text-slate-700 dark:text-slate-300">Laboratory Queue Active</p>
                            <a href="/laboratory" class="inline-flex items-center gap-2 mt-2 bg-amber-600 hover:bg-amber-700 text-white px-5 py-2.5 rounded-xl text-xs font-bold transition shadow-sm">
                                <i data-lucide="test-tube" class="w-4 h-4"></i> Open Lab Processing Desk
                            </a>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
                        ${sectionHeader('Quick Actions', '')}
                        <div class="space-y-3">
                            <a href="/laboratory" class="flex items-center gap-3 p-3.5 bg-amber-50 dark:bg-amber-500/10 hover:bg-amber-100 border border-amber-200 dark:border-amber-500/20 text-amber-700 dark:text-amber-400 rounded-xl transition text-xs font-bold">
                                <i data-lucide="flask-conical" class="w-4 h-4 shrink-0"></i> Process Samples
                            </a>
                            <a href="/patients" class="flex items-center gap-3 p-3.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 rounded-xl transition text-xs font-bold">
                                <i data-lucide="search" class="w-4 h-4 shrink-0"></i> Lookup Patient File
                            </a>
                        </div>
                    </div>
                </div>`;
        }

        // ─── 8. Radiographer View ─────────────────────────────────────────────────
        else if (role === 'radiographer') {
            view.innerHTML = `
                <div class="grid grid-cols-2 gap-4">
                    ${statCard('Pending Scans', metrics.pending_requests || 0, 'Awaiting processing', 'scan', 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600')}
                    ${statCard('Completed Today', metrics.completed_today || 0, 'Radiology results submitted', 'check-circle', 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600')}
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-center min-h-[200px]">
                        <div class="text-center space-y-2">
                            <i data-lucide="radio" class="w-10 h-10 mx-auto text-indigo-500"></i>
                            <p class="text-sm font-bold text-slate-700 dark:text-slate-300">Radiology Queue Active</p>
                            <a href="/laboratory" class="inline-flex items-center gap-2 mt-2 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl text-xs font-bold transition shadow-sm">
                                <i data-lucide="bone" class="w-4 h-4"></i> Open Radiology Desk
                            </a>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
                        ${sectionHeader('Quick Actions', '')}
                        <div class="space-y-3">
                            <a href="/laboratory" class="flex items-center gap-3 p-3.5 bg-indigo-50 dark:bg-indigo-500/10 hover:bg-indigo-100 border border-indigo-200 dark:border-indigo-500/20 text-indigo-700 dark:text-indigo-400 rounded-xl transition text-xs font-bold">
                                <i data-lucide="scan-line" class="w-4 h-4 shrink-0"></i> Perform Scans
                            </a>
                            <a href="/patients" class="flex items-center gap-3 p-3.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 rounded-xl transition text-xs font-bold">
                                <i data-lucide="search" class="w-4 h-4 shrink-0"></i> Lookup Patient File
                            </a>
                        </div>
                    </div>
                </div>`;
        }

        // ─── 9. HR Officer View ───────────────────────────────────────────────────
        else if (role === 'hr_officer') {
            view.innerHTML = `
                <div class="grid grid-cols-2 gap-4">
                    ${statCard('Total Staff', (metrics.total_staff || 0).toLocaleString(), 'Clinical & Non-clinical', 'users', 'bg-blue-50 dark:bg-blue-500/10 text-blue-600')}
                    ${statCard('System Users', (metrics.total_users || 0).toLocaleString(), 'Registered accounts', 'user-check', 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600')}
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-center min-h-[200px]">
                        <div class="text-center space-y-2">
                            <i data-lucide="briefcase" class="w-10 h-10 mx-auto text-blue-500"></i>
                            <p class="text-sm font-bold text-slate-700 dark:text-slate-300">Human Resources Module</p>
                            <a href="/admin/users" class="inline-flex items-center gap-2 mt-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-xs font-bold transition shadow-sm">
                                <i data-lucide="users" class="w-4 h-4"></i> Manage Staff Accounts
                            </a>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
                        ${sectionHeader('Quick Actions', '')}
                        <div class="space-y-3">
                            <a href="/admin/users" class="flex items-center gap-3 p-3.5 bg-blue-50 dark:bg-blue-500/10 hover:bg-blue-100 border border-blue-200 dark:border-blue-500/20 text-blue-700 dark:text-blue-400 rounded-xl transition text-xs font-bold">
                                <i data-lucide="user-plus" class="w-4 h-4 shrink-0"></i> Onboard New Staff
                            </a>
                        </div>
                    </div>
                </div>`;
        }

        // ─── 10. ICT Admin View ───────────────────────────────────────────────────
        else if (role === 'ict_admin') {
            view.innerHTML = `
                <div class="grid grid-cols-3 gap-4">
                    ${statCard('System Status', metrics.system_health || '100%', 'All services operational', 'server', 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600')}
                    ${statCard('Total Users', (metrics.total_users || 0).toLocaleString(), 'Registered accounts', 'users', 'bg-blue-50 dark:bg-blue-500/10 text-blue-600')}
                    ${statCard("Today's Audits", (metrics.audit_logs_today || 0).toLocaleString(), 'System events logged', 'shield-alert', 'bg-amber-50 dark:bg-amber-500/10 text-amber-600')}
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-center min-h-[200px]">
                        <div class="text-center space-y-2">
                            <i data-lucide="terminal" class="w-10 h-10 mx-auto text-emerald-500"></i>
                            <p class="text-sm font-bold text-slate-700 dark:text-slate-300">ICT Operations</p>
                            <a href="/audit-trail" class="inline-flex items-center gap-2 mt-2 bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-xl text-xs font-bold transition shadow-sm">
                                <i data-lucide="clipboard-list" class="w-4 h-4"></i> View Security Audit Logs
                            </a>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
                        ${sectionHeader('Quick Actions', '')}
                        <div class="space-y-3">
                            <a href="/admin/users" class="flex items-center gap-3 p-3.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 rounded-xl transition text-xs font-bold">
                                <i data-lucide="users" class="w-4 h-4 shrink-0"></i> Manage User Access
                            </a>
                            <a href="/settings" class="flex items-center gap-3 p-3.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 rounded-xl transition text-xs font-bold">
                                <i data-lucide="settings" class="w-4 h-4 shrink-0"></i> System Configuration
                            </a>
                            <a href="/support-chats" class="flex items-center gap-3 p-3.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 rounded-xl transition text-xs font-bold">
                                <i data-lucide="headset" class="w-4 h-4 shrink-0"></i> IT Support Chat
                            </a>
                        </div>
                    </div>
                </div>`;
        }

        // ─── 11. Ambulance Officer View ───────────────────────────────────────────
        else if (role === 'ambulance_officer') {
            view.innerHTML = `
                <div class="grid grid-cols-2 gap-4">
                    ${statCard('Active Dispatches', metrics.active_emergencies || 0, 'Currently active emergencies', 'siren', 'bg-red-50 dark:bg-red-500/10 text-red-600')}
                    ${statCard('Emergencies Today', metrics.emergencies_today || 0, 'Logged today', 'activity', 'bg-amber-50 dark:bg-amber-500/10 text-amber-600')}
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-center min-h-[200px]">
                        <div class="text-center space-y-2">
                            <i data-lucide="ambulance" class="w-10 h-10 mx-auto text-red-500"></i>
                            <p class="text-sm font-bold text-slate-700 dark:text-slate-300">Ambulance Dispatch Queue</p>
                            <a href="/emergencies" class="inline-flex items-center gap-2 mt-2 bg-red-600 hover:bg-red-700 text-white px-5 py-2.5 rounded-xl text-xs font-bold transition shadow-sm">
                                <i data-lucide="radio-tower" class="w-4 h-4"></i> View Emergency Board
                            </a>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
                        ${sectionHeader('Quick Actions', '')}
                        <div class="space-y-3">
                            <a href="/emergencies" class="flex items-center gap-3 p-3.5 bg-red-50 dark:bg-red-500/10 hover:bg-red-100 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 rounded-xl transition text-xs font-bold">
                                <i data-lucide="siren" class="w-4 h-4 shrink-0"></i> Log Emergency
                            </a>
                            <a href="/vitals" class="flex items-center gap-3 p-3.5 bg-emerald-50 dark:bg-emerald-500/10 hover:bg-emerald-100 border border-emerald-200 dark:border-emerald-500/20 text-emerald-700 dark:text-emerald-400 rounded-xl transition text-xs font-bold">
                                <i data-lucide="activity" class="w-4 h-4 shrink-0"></i> Record Triage Vitals
                            </a>
                        </div>
                    </div>
                </div>`;
        }

        // ─── 12. Health Information Officer (HIM/Records) View ──────────────────
        else if (role === 'health_info_officer' || role === 'records_officer') {
            view.innerHTML = `
                <div class="grid grid-cols-3 gap-4">
                    ${statCard('Total Patients', (metrics.total_patients || 0).toLocaleString(), 'Registered records', 'folder-open', 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600')}
                    ${statCard("Today's Visits", (metrics.visits_today || 0).toLocaleString(), 'Clinical encounters logged', 'calendar-check', 'bg-blue-50 dark:bg-blue-500/10 text-blue-600')}
                    ${statCard('Active Admissions', (metrics.active_admissions || 0).toLocaleString(), 'Inpatient files', 'bed', 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600')}
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-center min-h-[200px]">
                        <div class="text-center space-y-2">
                            <i data-lucide="file-stack" class="w-10 h-10 mx-auto text-emerald-500"></i>
                            <p class="text-sm font-bold text-slate-700 dark:text-slate-300">Health Information Management</p>
                            <a href="/patients" class="inline-flex items-center gap-2 mt-2 bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-xl text-xs font-bold transition shadow-sm">
                                <i data-lucide="users" class="w-4 h-4"></i> Open Patient Directory
                            </a>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
                        ${sectionHeader('Quick Actions', '')}
                        <div class="space-y-3">
                            <a href="/patients" class="flex items-center gap-3 p-3.5 bg-emerald-50 dark:bg-emerald-500/10 hover:bg-emerald-100 border border-emerald-200 dark:border-emerald-500/20 text-emerald-700 dark:text-emerald-400 rounded-xl transition text-xs font-bold">
                                <i data-lucide="user-plus" class="w-4 h-4 shrink-0"></i> Register New Patient
                            </a>
                            <a href="/appointments" class="flex items-center gap-3 p-3.5 bg-blue-50 dark:bg-blue-500/10 hover:bg-blue-100 border border-blue-200 dark:border-blue-500/20 text-blue-700 dark:text-blue-400 rounded-xl transition text-xs font-bold">
                                <i data-lucide="calendar" class="w-4 h-4 shrink-0"></i> Manage Appointments
                            </a>
                            <a href="/audit-trail" class="flex items-center gap-3 p-3.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 rounded-xl transition text-xs font-bold">
                                <i data-lucide="clipboard-list" class="w-4 h-4 shrink-0"></i> Review Audit Trail
                            </a>
                        </div>
                    </div>
                </div>`;
        }

        // ─── 13. Generic Staff Fallback ─────────────────────────────────────────────
        else {
            view.innerHTML = `
                <div class="grid grid-cols-3 gap-4">
                    ${statCard('Total Patients', (metrics.total_patients || 0).toLocaleString(), 'In registry', 'users', 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600')}
                    ${statCard("Today's Appointments", metrics.today_appointments || 0, 'Scheduled today', 'calendar', 'bg-blue-50 dark:bg-blue-500/10 text-blue-600')}
                    ${statCard('Active Admissions', metrics.active_admissions || 0, 'Currently admitted', 'bed', 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600')}
                </div>
                <div class="bg-white dark:bg-slate-900 p-8 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm text-center">
                    <i data-lucide="layout-dashboard" class="w-10 h-10 mx-auto text-slate-300 mb-3"></i>
                    <p class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Staff Dashboard Active</p>
                    <p class="text-xs text-slate-500">Use the sidebar navigation to access your clinical modules.</p>
                </div>`;
        }

        lucide.createIcons();
    }

    document.addEventListener('DOMContentLoaded', loadDashboard);
</script>
@endsection
