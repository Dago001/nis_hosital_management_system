@extends('layouts.app')

@section('title', 'Roles & Access | NIS Medical Services Portal')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-xl font-bold text-slate-800 dark:text-white flex items-center gap-2">
            <i data-lucide="shield-check" class="text-emerald-600 shrink-0"></i> Roles &amp; Access Control
        </h1>
        <p class="text-xs text-slate-500 dark:text-slate-400">Review each role and configure the permissions it grants across the system.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Roles list -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
            <div class="p-4 border-b border-slate-100 dark:border-slate-800"><h3 class="text-sm font-bold text-slate-800 dark:text-white">Roles</h3></div>
            <div class="max-h-[32rem] overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800" id="roles-list">
                <p class="p-4 text-xs text-slate-400">Loading…</p>
            </div>
        </div>

        <!-- Permission matrix -->
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
            <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between gap-3">
                <div>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-white" id="perm-title">Select a role</h3>
                    <p class="text-[10px] text-slate-500" id="perm-sub">Choose a role on the left to view its permissions.</p>
                </div>
                <button id="perm-save" onclick="savePermissions()" class="hidden bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-4 py-2 rounded-xl">Save</button>
            </div>
            <div class="p-4 max-h-[30rem] overflow-y-auto" id="perm-body">
                <p class="text-xs text-slate-400">No role selected.</p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let modules = {}, selectedRole = null;

    async function loadModules() {
        try { const res = await api.get('/roles/permissions'); modules = res.modules || {}; } catch (e) { modules = {}; }
    }

    async function loadRoles() {
        const box = document.getElementById('roles-list');
        try {
            const res = await api.get('/roles');
            box.innerHTML = (res.roles||[]).map(r => `
                <button onclick="selectRole(${r.id}, this)" data-role="${r.id}" class="w-full text-left p-3 hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                    <div class="flex items-center justify-between">
                        <b class="text-xs text-slate-800 dark:text-white">${r.display_name}</b>
                        <span class="text-[9px] text-slate-400 font-mono">${r.name}</span>
                    </div>
                    <div class="text-[10px] text-slate-500 mt-0.5">${r.permissions_count} permissions · ${r.users_count} users</div>
                </button>`).join('') || '<p class="p-4 text-xs text-slate-400">No roles.</p>';
        } catch (e) { box.innerHTML = '<p class="p-4 text-xs text-red-500">Failed to load roles.</p>'; }
    }

    async function selectRole(id, btn) {
        document.querySelectorAll('#roles-list button').forEach(b => b.classList.remove('bg-emerald-50','dark:bg-emerald-500/10'));
        if (btn) btn.classList.add('bg-emerald-50','dark:bg-emerald-500/10');
        try {
            const res = await api.get(`/roles/${id}`);
            selectedRole = res.role;
            const owned = new Set(res.permission_ids || []);
            document.getElementById('perm-title').innerText = res.role.display_name;
            const isSuper = res.role.name === 'super_admin';
            document.getElementById('perm-sub').innerText = isSuper ? 'super_admin has unrestricted access (not editable).' : 'Tick the permissions this role should have, then Save.';
            const saveBtn = document.getElementById('perm-save');
            saveBtn.classList.toggle('hidden', isSuper);

            const body = document.getElementById('perm-body');
            body.innerHTML = Object.keys(modules).map(mod => `
                <div class="mb-4">
                    <div class="text-[10px] font-black uppercase tracking-wider text-slate-500 mb-2 border-b border-slate-100 dark:border-slate-800 pb-1">${mod}</div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                        ${modules[mod].map(p => `
                            <label class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300 ${isSuper ? 'opacity-60' : ''}">
                                <input type="checkbox" class="perm-chk" value="${p.id}" ${owned.has(p.id) ? 'checked' : ''} ${isSuper ? 'disabled' : ''}>
                                <span>${p.display_name}</span>
                            </label>`).join('')}
                    </div>
                </div>`).join('') || '<p class="text-xs text-slate-400">No permissions defined.</p>';
        } catch (e) { alert(e.message || 'Failed to load role.'); }
    }

    async function savePermissions() {
        if (!selectedRole) return;
        const ids = [...document.querySelectorAll('.perm-chk:checked')].map(c => parseInt(c.value));
        try {
            const res = await api.put(`/roles/${selectedRole.id}/permissions`, { permission_ids: ids });
            alert(res.message || 'Saved.');
            loadRoles();
        } catch (e) { alert(e.message || 'Failed to save.'); }
    }

    document.addEventListener('DOMContentLoaded', async () => {
        await loadModules();
        loadRoles();
    });
</script>
@endsection
