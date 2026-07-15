@extends('layouts.app')

@section('title', 'Queue Management – NIS Medical Services Portal')

@section('content')
<div class="space-y-6">

    <!-- Page Header -->
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-white">Queue Management</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Real-time outpatient waiting list & patient flow</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <input type="date" id="queue-date" class="text-xs px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500">
            <select id="dept-filter" class="text-xs px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                <option value="">All Departments</option>
            </select>
            <select id="status-filter" class="text-xs px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                <option value="all">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="checked_in">Checked In</option>
                <option value="in_consultation">In Consultation</option>
                <option value="completed">Completed</option>
            </select>
            <button id="refresh-btn" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold px-4 py-2 rounded-xl flex items-center gap-1.5 transition">
                <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 text-center">
            <p class="text-2xl font-black text-slate-800 dark:text-white" id="stat-total">–</p>
            <p class="text-[10px] text-slate-500 uppercase tracking-wider mt-1">Total</p>
        </div>
        <div class="bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 rounded-2xl p-4 text-center">
            <p class="text-2xl font-black text-amber-700 dark:text-amber-400" id="stat-waiting">–</p>
            <p class="text-[10px] text-amber-600 uppercase tracking-wider mt-1">Waiting</p>
        </div>
        <div class="bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/20 rounded-2xl p-4 text-center">
            <p class="text-2xl font-black text-blue-700 dark:text-blue-400" id="stat-checkedin">–</p>
            <p class="text-[10px] text-blue-600 uppercase tracking-wider mt-1">Checked In</p>
        </div>
        <div class="bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 rounded-2xl p-4 text-center">
            <p class="text-2xl font-black text-emerald-700 dark:text-emerald-400" id="stat-completed">–</p>
            <p class="text-[10px] text-emerald-600 uppercase tracking-wider mt-1">Completed</p>
        </div>
    </div>

    <!-- Live indicator -->
    <div class="flex items-center gap-2 text-[10px] text-slate-400">
        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse inline-block"></span>
        <span>Live – auto-refreshes every 60 seconds</span>
        <span class="text-slate-300 dark:text-slate-600">|</span>
        <span>Last updated: <span id="last-updated">–</span></span>
    </div>

    <!-- API error notice -->
    <div id="api-error-notice" class="hidden bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 rounded-xl px-4 py-3 text-xs text-red-700 dark:text-red-400 flex items-start gap-2">
        <i data-lucide="alert-triangle" class="w-4 h-4 shrink-0 mt-0.5"></i>
        <span id="api-error-text"></span>
    </div>

    <!-- Queue Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-700">
                        <th class="text-left px-4 py-3 font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wide"># Queue</th>
                        <th class="text-left px-4 py-3 font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wide">Patient</th>
                        <th class="text-left px-4 py-3 font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wide">Hospital No.</th>
                        <th class="text-left px-4 py-3 font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wide">Department</th>
                        <th class="text-left px-4 py-3 font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wide">Doctor</th>
                        <th class="text-left px-4 py-3 font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wide">Appt. Date / Time</th>
                        <th class="text-left px-4 py-3 font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wide">Status</th>
                        <th class="text-left px-4 py-3 font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wide">Action</th>
                    </tr>
                </thead>
                <tbody id="queue-tbody">
                    <tr>
                        <td colspan="8" class="py-12 text-center text-slate-400">
                            <div style="display:flex;flex-direction:column;align-items:center;gap:8px;">
                                <i data-lucide="loader" class="w-6 h-6 animate-spin"></i>
                                <p class="text-xs mt-1">Loading queue...</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div id="status-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 w-full max-w-sm shadow-2xl">
        <h3 class="text-base font-bold text-slate-800 dark:text-white mb-4">Update Queue Status</h3>
        <input type="hidden" id="modal-appt-id">
        <div class="space-y-3">
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">New Status</label>
                <select id="modal-status" style="width:100%;font-size:.75rem;padding:.625rem .75rem;border-radius:.75rem;border:1px solid #e2e8f0;background:#f8fafc;color:#1e293b;outline:none;">
                    <option value="pending">Pending</option>
                    <option value="checked_in">Checked In</option>
                    <option value="in_consultation">In Consultation</option>
                    <option value="completed">Completed</option>
                    <option value="no_show">No Show</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Notes (optional)</label>
                <textarea id="modal-notes" rows="2" style="width:100%;font-size:.75rem;padding:.625rem .75rem;border-radius:.75rem;border:1px solid #e2e8f0;background:#f8fafc;color:#1e293b;outline:none;resize:none;"></textarea>
            </div>
        </div>
        <div class="flex gap-3 mt-5">
            <button id="cancel-status-btn" class="flex-1 px-4 py-2 text-xs font-semibold border border-slate-200 dark:border-slate-700 rounded-xl text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition">Cancel</button>
            <button id="submit-status-btn" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-4 py-2 rounded-xl transition">Update Status</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function () {
    'use strict';

    var autoRefreshTimer = null;

    var statusColors = {
        pending:         'background:#fef3c7;color:#92400e;',
        checked_in:      'background:#dbeafe;color:#1e40af;',
        in_consultation: 'background:#ede9fe;color:#5b21b6;',
        completed:       'background:#d1fae5;color:#065f46;',
        no_show:         'background:#fee2e2;color:#991b1b;',
        cancelled:       'background:#f1f5f9;color:#64748b;',
    };

    function showError(msg) {
        var el = document.getElementById('api-error-notice');
        var txt = document.getElementById('api-error-text');
        if (el && txt) {
            txt.textContent = msg;
            el.classList.remove('hidden');
        }
    }
    function hideError() {
        var el = document.getElementById('api-error-notice');
        if (el) el.classList.add('hidden');
    }

    /* ── Load queue ─────────────────────────────── */
    function loadQueue() {
        var dateEl   = document.getElementById('queue-date');
        var deptEl   = document.getElementById('dept-filter');
        var statusEl = document.getElementById('status-filter');
        var tbody    = document.getElementById('queue-tbody');

        var date   = dateEl   ? dateEl.value   : '';
        var dept   = deptEl   ? deptEl.value   : '';
        var status = statusEl ? statusEl.value : 'all';

        var qs = '?status=' + encodeURIComponent(status);
        if (date)   qs += '&date='          + encodeURIComponent(date);
        if (dept)   qs += '&department_id=' + encodeURIComponent(dept);

        tbody.innerHTML = '<tr><td colspan="8" style="padding:40px;text-align:center;color:#94a3b8;font-size:.75rem;">Loading appointments...</td></tr>';
        hideError();

        window.api.get('/queue' + qs).then(function(data) {
            renderQueue(data.queue  || []);
            renderStats(data.stats  || {});
            var lu = document.getElementById('last-updated');
            if (lu) lu.textContent = new Date().toLocaleTimeString();
        }).catch(function(e) {
            showError('Failed to load queue: ' + (e.message || 'Unknown error'));
            tbody.innerHTML = '<tr><td colspan="8" style="padding:40px;text-align:center;color:#ef4444;font-size:.75rem;">Could not load queue data. Check console for details.</td></tr>';
            console.error('Queue API error:', e);
        });
    }

    function renderStats(s) {
        var ids = { 'stat-total': s.total, 'stat-waiting': s.waiting, 'stat-checkedin': s.checked_in, 'stat-completed': s.completed };
        Object.keys(ids).forEach(function(id) {
            var el = document.getElementById(id);
            if (el) el.textContent = (ids[id] !== undefined && ids[id] !== null) ? ids[id] : 0;
        });
    }

    function renderQueue(list) {
        var tbody = document.getElementById('queue-tbody');
        if (!list.length) {
            var dateEl = document.getElementById('queue-date');
            var dateStr = dateEl && dateEl.value ? ' on ' + dateEl.value : '';
            tbody.innerHTML =
                '<tr><td colspan="8" style="padding:48px 16px;text-align:center;">' +
                '<div style="display:flex;flex-direction:column;align-items:center;gap:8px;color:#94a3b8;">' +
                '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1" ry="1"/><line x1="9" y1="12" x2="15" y2="12"/><line x1="9" y1="16" x2="12" y2="16"/></svg>' +
                '<p style="font-weight:700;color:#475569;font-size:.875rem;">No appointments found' + dateStr + '</p>' +
                '<p style="font-size:.75rem;">Try selecting a different date or removing filters.</p>' +
                '</div></td></tr>';
            return;
        }

        var rows = list.map(function(p) {
            var sc     = statusColors[p.status] || statusColors.pending;
            var label  = (p.status || '').replace(/_/g, ' ').replace(/\b\w/g, function(c) { return c.toUpperCase(); });
            var wait   = p.wait_time_minutes > 0
                ? '<span style="color:#d97706;font-size:10px;margin-left:4px;">(' + p.wait_time_minutes + 'm wait)</span>' : '';
            return '<tr style="border-bottom:1px solid #f1f5f9;">' +
                '<td style="padding:12px 16px;font-weight:700;color:#334155;">' + (p.queue_number || '–') + '</td>' +
                '<td style="padding:12px 16px;">' +
                    '<p style="font-weight:600;color:#0f172a;">' + (p.patient_name || 'Unknown') + '</p>' +
                    '<p style="color:#94a3b8;font-size:10px;">' + (p.patient_phone || '') + '</p>' +
                '</td>' +
                '<td style="padding:12px 16px;font-family:monospace;font-size:10px;color:#475569;">' + (p.hospital_number || '–') + '</td>' +
                '<td style="padding:12px 16px;color:#475569;">' + (p.department || '–') + '</td>' +
                '<td style="padding:12px 16px;color:#475569;">' + (p.doctor || '–') + '</td>' +
                '<td style="padding:12px 16px;color:#475569;">' + (p.appointment_date ? p.appointment_date + ' ' : '') + (p.appointment_time || '–') + wait + '</td>' +
                '<td style="padding:12px 16px;"><span style="padding:3px 8px;border-radius:999px;font-size:10px;font-weight:700;' + sc + '">' + label + '</span></td>' +
                '<td style="padding:12px 16px;">' +
                    '<button class="queue-update-btn" data-id="' + p.id + '" data-status="' + p.status + '" ' +
                        'style="color:#059669;font-size:10px;font-weight:700;text-decoration:underline;background:none;border:none;cursor:pointer;">Update</button>' +
                '</td>' +
            '</tr>';
        }).join('');

        tbody.innerHTML = rows;

        tbody.querySelectorAll('.queue-update-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                openStatusModal(btn.dataset.id, btn.dataset.status);
            });
        });
    }

    /* ── Departments ─────────────────────────────── */
    function loadDepartments() {
        window.api.get('/queue/departments').then(function(data) {
            var sel = document.getElementById('dept-filter');
            if (!sel) return;
            (data.departments || []).forEach(function(d) {
                var opt = document.createElement('option');
                opt.value = d.id; opt.textContent = d.name;
                sel.appendChild(opt);
            });
        }).catch(function() {});
    }

    /* ── Status Modal ─────────────────────────────── */
    function openStatusModal(id, currentStatus) {
        document.getElementById('modal-appt-id').value = id;
        document.getElementById('modal-status').value  = currentStatus || 'pending';
        document.getElementById('modal-notes').value   = '';
        document.getElementById('status-modal').classList.remove('hidden');
    }
    function closeStatusModal() {
        document.getElementById('status-modal').classList.add('hidden');
    }
    function submitStatusUpdate() {
        var id     = document.getElementById('modal-appt-id').value;
        var status = document.getElementById('modal-status').value;
        var notes  = document.getElementById('modal-notes').value;
        var btn    = document.getElementById('submit-status-btn');
        btn.disabled = true; btn.textContent = 'Saving…';
        window.api.post('/queue/' + id + '/status', { status: status, notes: notes }).then(function() {
            closeStatusModal();
            loadQueue();
        }).catch(function(e) {
            alert('Failed to update: ' + (e.message || 'Error'));
        }).finally(function() {
            btn.disabled = false; btn.textContent = 'Update Status';
        });
    }

    /* ── Bootstrap ───────────────────────────────── */
    document.addEventListener('DOMContentLoaded', function() {
        /* Default date = today */
        var dateEl = document.getElementById('queue-date');
        if (dateEl) {
            dateEl.value = new Date().toISOString().split('T')[0];
            dateEl.addEventListener('change', loadQueue);
        }

        /* Filter listeners */
        ['status-filter', 'dept-filter'].forEach(function(id) {
            var el = document.getElementById(id);
            if (el) el.addEventListener('change', loadQueue);
        });

        /* Buttons */
        var refreshBtn = document.getElementById('refresh-btn');
        if (refreshBtn) refreshBtn.addEventListener('click', loadQueue);

        var cancelBtn = document.getElementById('cancel-status-btn');
        if (cancelBtn) cancelBtn.addEventListener('click', closeStatusModal);

        var submitBtn = document.getElementById('submit-status-btn');
        if (submitBtn) submitBtn.addEventListener('click', submitStatusUpdate);

        /* Load data */
        loadDepartments();
        loadQueue();

        /* Auto-refresh every 60s */
        autoRefreshTimer = setInterval(loadQueue, 60000);
    });
})();
</script>
@endsection
