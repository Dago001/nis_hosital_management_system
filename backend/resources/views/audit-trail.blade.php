@extends('layouts.app')

@section('title', 'Audit Trail - NIS Medical Services Portal')

@section('content')
<div class="space-y-6">
    <!-- Title -->
    <div>
        <h1 class="text-xl font-bold text-slate-800 dark:text-white flex items-center gap-2">
            <i data-lucide="clipboard-list" class="text-emerald-600"></i> System Audit Trail & Logs
        </h1>
        <p class="text-xs text-slate-800 dark:text-slate-200">ICT administrator portal to inspect system activities, logins, database mutations, and transactions integrity</p>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800/80 flex items-center justify-between shadow-sm">
        <div class="relative w-80">
            <input type="text" id="action-filter" oninput="handleActionFilter(this.value)" placeholder="Filter by action name..." 
                   class="w-full pl-9 pr-4 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 rounded-xl text-xs focus:ring-1 focus:ring-emerald-500 focus:outline-none text-slate-800 dark:text-slate-100 placeholder-slate-400 transition-all">
            <i data-lucide="search" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400 w-4 h-4"></i>
        </div>
        <div class="flex items-center gap-1.5 text-[9px] bg-emerald-500/10 text-emerald-600 font-bold border border-emerald-500/20 px-3 py-1.5 rounded-full uppercase tracking-wider">
            <i data-lucide="shield-check" class="w-3.5 h-3.5"></i> Compliance Integrity Secure
        </div>
    </div>

    <!-- Logs Table Card -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-200 border-b border-slate-100 dark:border-slate-800">
                    <tr>
                        <th class="py-3.5 px-6 font-bold">Timestamp</th>
                        <th class="py-3.5 px-6 font-bold">Trigger User</th>
                        <th class="py-3.5 px-6 font-bold">Action Name</th>
                        <th class="py-3.5 px-6 font-bold">IP Address</th>
                        <th class="py-3.5 px-6 font-bold">User Agent</th>
                    </tr>
                </thead>
                <tbody id="logs-table-body" class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-350">
                    <tr>
                        <td colSpan="5" class="py-8 text-center text-slate-850 dark:text-slate-200 text-sm">
                            <div class="w-6 h-6 border-2 border-emerald-600 border-t-transparent rounded-full animate-spin mx-auto"></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let filterTimeout = null;

    async function loadLogs(action = '') {
        const tbody = document.getElementById('logs-table-body');
        try {
            const res = await api.get(`/admin/audit-logs?action=${encodeURIComponent(action)}`);
            const list = res.logs.data;

            if (list.length > 0) {
                tbody.innerHTML = list.map(log => `
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/20 transition">
                        <td class="py-3.5 px-6 font-mono text-[10px] text-slate-800 dark:text-slate-200">
                            ${new Date(log.created_at).toLocaleString()}
                        </td>
                        <td class="py-3.5 px-6 font-bold text-slate-805 dark:text-white">
                            <span class="flex items-center gap-1.5">
                                <i data-lucide="user" class="w-3.5 h-3.5 text-slate-500"></i>
                                ${log.user ? log.user.name : 'Guest Session'}
                            </span>
                        </td>
                        <td class="py-3.5 px-6">
                            <span class="px-2 py-0.5 text-[9px] font-mono font-bold bg-emerald-500/10 text-emerald-600 border border-emerald-500/20 rounded uppercase">
                                ${log.action}
                            </span>
                        </td>
                        <td class="py-3.5 px-6 font-mono text-slate-800 dark:text-slate-200">${log.ip_address}</td>
                        <td class="py-3.5 px-6 text-slate-800 dark:text-slate-200 truncate max-w-xs" title="${log.user_agent}">
                            ${log.user_agent}
                        </td>
                    </tr>
                `).join('');
                lucide.createIcons();
            } else {
                tbody.innerHTML = `<tr><td colspan="5" class="py-8 text-center text-slate-500 text-xs">No activity log entries found.</td></tr>`;
            }
        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="5" class="py-8 text-center text-red-500 text-xs">Failed to load activity logs.</td></tr>`;
        }
    }

    function handleActionFilter(val) {
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(() => {
            loadLogs(val);
        }, 300);
    }

    document.addEventListener('DOMContentLoaded', () => {
        loadLogs();
    });
</script>
@endsection
