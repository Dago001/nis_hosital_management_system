@extends('layouts.app')

@section('title', 'User Accounts - NIS Medical Services Portal')

@section('content')
<div class="space-y-6">
    <!-- Title & Action -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-white flex items-center gap-2">
                <i data-lucide="users" class="text-emerald-600"></i> Staff User Accounts Management
            </h1>
            <p class="text-xs text-slate-800 dark:text-slate-200">System Administrator portal to onboard medical staff, assign RBAC permissions, suspend accounts, and reset passcodes</p>
        </div>
        <button onclick="handleOpenCreateModal()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-xl text-xs font-semibold flex items-center gap-2 shadow-lg shadow-emerald-650/10 transition cursor-pointer">
            <i data-lucide="user-plus" class="w-4 h-4"></i> Create User Account
        </button>
    </div>

    <!-- Search filter -->
    <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800/80 flex items-center justify-between shadow-sm">
        <div class="relative w-80">
            <input type="text" id="user-search" oninput="handleUserSearch(this.value)" placeholder="Search staff name or email..." 
                   class="w-full pl-9 pr-4 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 rounded-xl text-xs focus:ring-1 focus:ring-emerald-500 focus:outline-none text-slate-800 dark:text-slate-100 placeholder-slate-400 transition-all">
            <i data-lucide="search" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400 w-4 h-4"></i>
        </div>
    </div>

    <!-- Users Table Card -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-200 border-b border-slate-100 dark:border-slate-800">
                    <tr>
                        <th class="py-3.5 px-6 font-bold">User / Staff Name</th>
                        <th class="py-3.5 px-6 font-bold">Email Login</th>
                        <th class="py-3.5 px-6 font-bold">Role Assignment</th>
                        <th class="py-3.5 px-6 font-bold">Status</th>
                        <th class="py-3.5 px-6 font-bold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="users-table-body" class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-350">
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

<!-- Create / Edit User Modal -->
<div id="user-modal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 w-full max-w-2xl shadow-2xl relative my-8">
        <h3 class="text-base font-bold text-slate-800 dark:text-white mb-4" id="modal-title">Create New Staff User</h3>
        
        <form id="user-form" onsubmit="handleUserSubmit(event)" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1">User Account Name *</label>
                    <input type="text" id="u_name" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1">Email Login *</label>
                    <input type="email" id="u_email" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>
                <div id="password-container">
                    <label class="block text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1">Password *</label>
                    <input type="password" id="u_password" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>
            </div>

            <!-- Role Checklist -->
            <div class="border-t border-slate-100 dark:border-slate-800 pt-4">
                <label class="block text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-2">Assign Role Permissions *</label>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 p-3 bg-slate-50 dark:bg-slate-950 rounded-xl border border-slate-200 dark:border-slate-800 max-h-40 overflow-y-auto" id="roles-checklist">
                    <!-- Injected -->
                </div>
            </div>

            <!-- Staff Profile Fields -->
            <div class="border-t border-slate-100 dark:border-slate-800 pt-4 space-y-4">
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="u_is_staff" onchange="toggleStaffFields(this.checked)" class="w-4 h-4 text-emerald-600 border-slate-300 rounded focus:ring-emerald-500">
                    <label for="u_is_staff" class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Designate as Medical/Administrative Staff Profile</label>
                </div>

                <div id="staff-fields" class="hidden grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1">First Name *</label>
                        <input type="text" id="u_first_name" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1">Last Name *</label>
                        <input type="text" id="u_last_name" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1">Department Unit *</label>
                        <select id="u_department_id" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200">
                            <!-- Injected -->
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1">Service/Staff Code</label>
                        <input type="text" id="u_service_number" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1">Staff Rank</label>
                        <input type="text" id="u_rank" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1">Phone Number</label>
                        <input type="text" id="u_phone" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200">
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800/80">
                <button type="button" onclick="closeUserModal()" class="px-4 py-2 text-xs font-semibold text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition">Cancel</button>
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 text-xs font-bold rounded-xl transition shadow-md">Save Account</button>
            </div>
        </form>
    </div>
</div>

<!-- Password Reset Modal -->
<div id="reset-modal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 w-full max-w-md shadow-2xl relative my-8">
        <h3 class="text-base font-bold text-slate-800 dark:text-white mb-2 flex items-center gap-2">
            <i data-lucide="key" class="text-emerald-500"></i> Reset Staff Password
        </h3>
        <p class="text-[10px] text-slate-500 mb-4" id="reset-user-name"></p>
        
        <form id="reset-form" onsubmit="handleResetSubmit(event)" class="space-y-4">
            <div>
                <label class="block text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1">New Password *</label>
                <input type="password" id="reset_password" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1">Confirm New Password *</label>
                <input type="password" id="reset_confirm_password" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500">
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800/80">
                <button type="button" onclick="closeResetModal()" class="px-4 py-2 text-xs font-semibold text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition">Cancel</button>
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 text-xs font-bold rounded-xl transition shadow-md">Update Password</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let usersList = [];
    let rolesList = [];
    let departmentsList = [];
    let searchTimeout = null;
    let editMode = false;
    let editingUserId = null;
    let activeUser = null;

    async function loadUsers(query = '') {
        const tbody = document.getElementById('users-table-body');
        try {
            const res = await api.get(`/admin/users?search=${encodeURIComponent(query)}`);
            usersList = res.users;

            if (usersList.length > 0) {
                tbody.innerHTML = usersList.map(u => {
                    const rolesText = u.roles.map(r => r.display_name).join(', ') || 'No Assigned Roles';
                    
                    let statusClass = 'bg-slate-100 text-slate-700';
                    if (u.status === 'active') statusClass = 'bg-emerald-500/10 text-emerald-600 border border-emerald-500/20';
                    else if (u.status === 'suspended') statusClass = 'bg-red-500/10 text-red-500 border border-red-500/20';

                    const isSuspended = u.status === 'suspended';
                    const toggleText = isSuspended ? 'Activate' : 'Suspend';
                    const toggleIcon = isSuspended ? 'user-check' : 'user-x';
                    const toggleColor = isSuspended ? 'text-emerald-600' : 'text-red-500';

                    return `
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/20 transition">
                            <td class="py-3.5 px-6 font-bold text-slate-900 dark:text-white">
                                ${u.name}
                                <div class="text-[9px] text-slate-500 font-semibold">${u.staff ? `${u.staff.rank} | ${u.staff.department?.name || 'Medical Services'}` : 'Guest User'}</div>
                            </td>
                            <td class="py-3.5 px-6 text-slate-800 dark:text-slate-200">${u.email}</td>
                            <td class="py-3.5 px-6">
                                <span class="px-2 py-0.5 text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-650 dark:text-slate-300 rounded">
                                    ${rolesText}
                                </span>
                            </td>
                            <td class="py-3.5 px-6">
                                <span class="px-2.5 py-0.5 text-[9px] font-bold rounded-full uppercase ${statusClass}">
                                    ${u.status}
                                </span>
                            </td>
                            <td class="py-3.5 px-6 text-right flex items-center justify-end gap-3">
                                <button onclick="openEditModal(${u.id})" class="text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-white" title="Edit User">
                                    <i data-lucide="edit" class="w-4 h-4"></i>
                                </button>
                                <button onclick="openResetModal(${u.id})" class="text-blue-500 hover:text-blue-700" title="Reset Password">
                                    <i data-lucide="key" class="w-4 h-4"></i>
                                </button>
                                <button onclick="toggleUserStatus(${u.id}, '${u.status}')" class="${toggleColor} hover:opacity-80" title="${toggleText} User">
                                    <i data-lucide="${toggleIcon}" class="w-4 h-4"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                }).join('');
                lucide.createIcons();
            } else {
                tbody.innerHTML = `<tr><td colspan="5" class="py-8 text-center text-slate-500 text-xs">No user accounts found.</td></tr>`;
            }
        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="5" class="py-8 text-center text-red-500 text-xs">Failed to load user accounts records.</td></tr>`;
        }
    }

    function handleUserSearch(val) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            loadUsers(val);
        }, 300);
    }

    async function loadSetupData() {
        try {
            const res = await api.get('/admin/users/setup');
            rolesList = res.roles;
            departmentsList = res.departments;

            // Render roles checklist
            const checklist = document.getElementById('roles-checklist');
            checklist.innerHTML = rolesList.map(role => `
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="roles" value="${role.name}" id="role-${role.id}" class="w-3.5 h-3.5 text-emerald-600 border-slate-300 rounded focus:ring-emerald-500">
                    <label for="role-${role.id}" class="text-[10px] text-slate-800 dark:text-slate-200 font-semibold">${role.display_name}</label>
                </div>
            `).join('');

            // Render department dropdown options
            const deptSelect = document.getElementById('u_department_id');
            deptSelect.innerHTML = departmentsList.map(d => `<option value="${d.id}">${d.name}</option>`).join('');

        } catch (err) {
            console.error('Failed to load setup database roles/depts:', err);
        }
    }

    function toggleStaffFields(checked) {
        const fields = document.getElementById('staff-fields');
        if (checked) {
            fields.classList.remove('hidden');
            document.getElementById('u_first_name').required = true;
            document.getElementById('u_last_name').required = true;
        } else {
            fields.classList.add('hidden');
            document.getElementById('u_first_name').required = false;
            document.getElementById('u_last_name').required = false;
        }
    }

    function handleOpenCreateModal() {
        editMode = false;
        editingUserId = null;
        document.getElementById('user-form').reset();
        document.getElementById('modal-title').innerText = "Create New Staff User";
        document.getElementById('password-container').classList.remove('hidden');
        document.getElementById('u_password').required = true;
        toggleStaffFields(false);
        document.getElementById('user-modal').classList.remove('hidden');
    }

    function openEditModal(id) {
        editMode = true;
        editingUserId = id;
        const u = usersList.find(userObj => userObj.id === id);
        if (!u) return;

        document.getElementById('user-form').reset();
        document.getElementById('modal-title').innerText = `Edit User: ${u.name}`;
        
        // Hide password field during editing (done via reset passcode action)
        document.getElementById('password-container').classList.add('hidden');
        document.getElementById('u_password').required = false;

        document.getElementById('u_name').value = u.name;
        document.getElementById('u_email').value = u.email;

        // Set roles checklist checkboxes
        const assignedRoleNames = u.roles.map(r => r.name);
        document.querySelectorAll('input[name="roles"]').forEach(chk => {
            chk.checked = assignedRoleNames.includes(chk.value);
        });

        // Set staff profile
        if (u.staff) {
            document.getElementById('u_is_staff').checked = true;
            toggleStaffFields(true);
            document.getElementById('u_first_name').value = u.staff.first_name || '';
            document.getElementById('u_last_name').value = u.staff.last_name || '';
            document.getElementById('u_department_id').value = u.staff.department_id || '';
            document.getElementById('u_service_number').value = u.staff.immigration_service_number || '';
            document.getElementById('u_rank').value = u.staff.rank || '';
            document.getElementById('u_phone').value = u.staff.phone || '';
        } else {
            document.getElementById('u_is_staff').checked = false;
            toggleStaffFields(false);
        }

        document.getElementById('user-modal').classList.remove('hidden');
    }

    function closeUserModal() {
        document.getElementById('user-modal').classList.add('hidden');
    }

    async function handleUserSubmit(e) {
        e.preventDefault();
        
        const selectedRoleInputs = Array.from(document.querySelectorAll('input[name="roles"]:checked'));
        const roles = selectedRoleInputs.map(input => input.value);

        if (roles.length === 0) {
            alert('Please assign at least one permission role.');
            return;
        }

        const payload = {
            name: document.getElementById('u_name').value,
            email: document.getElementById('u_email').value,
            roles: roles,
            is_staff: document.getElementById('u_is_staff').checked
        };

        if (!editMode) {
            payload.password = document.getElementById('u_password').value;
        }

        if (payload.is_staff) {
            payload.first_name = document.getElementById('u_first_name').value;
            payload.last_name = document.getElementById('u_last_name').value;
            payload.department_id = parseInt(document.getElementById('u_department_id').value);
            payload.immigration_service_number = document.getElementById('u_service_number').value || null;
            payload.rank = document.getElementById('u_rank').value || null;
            payload.phone = document.getElementById('u_phone').value || null;
        }

        try {
            if (editMode) {
                await api.put(`/admin/users/${editingUserId}`, payload);
                alert('User account settings saved!');
            } else {
                await api.post('/admin/users', payload);
                alert('New user credentials onboarded successfully!');
            }
            closeUserModal();
            loadUsers();
        } catch (err) {
            alert(err.message || 'Failed to save user details.');
        }
    }

    function openResetModal(id) {
        activeUser = usersList.find(u => u.id === id);
        if (!activeUser) return;
        document.getElementById('reset-form').reset();
        document.getElementById('reset-user-name').innerText = `Resetting password for trigger user: ${activeUser.name} (${activeUser.email})`;
        document.getElementById('reset-modal').classList.remove('hidden');
    }

    function closeResetModal() {
        document.getElementById('reset-modal').classList.add('hidden');
    }

    async function handleResetSubmit(e) {
        e.preventDefault();
        const p = document.getElementById('reset_password').value;
        const confirmP = document.getElementById('reset_confirm_password').value;

        if (p !== confirmP) {
            alert('Passwords do not match.');
            return;
        }

        try {
            // Re-use edit endpoint passing password only
            await api.put(`/admin/users/${activeUser.id}`, {
                name: activeUser.name,
                email: activeUser.email,
                password: p,
                roles: activeUser.roles.map(r => r.name)
            });
            alert('Password reset completed successfully!');
            closeResetModal();
        } catch (err) {
            alert(err.message || 'Reset failed.');
        }
    }

    async function toggleUserStatus(id, currentStatus) {
        const u = usersList.find(userObj => userObj.id === id);
        if (!u) return;

        const nextStatus = currentStatus === 'active' ? 'suspended' : 'active';
        if (!confirm(`Are you sure you want to change status to ${nextStatus} for ${u.name}?`)) return;

        try {
            await api.put(`/admin/users/${id}`, {
                name: u.name,
                email: u.email,
                status: nextStatus,
                roles: u.roles.map(r => r.name)
            });
            alert(`Account status updated to ${nextStatus}!`);
            loadUsers();
        } catch (err) {
            alert(err.message || 'Failed to update user account status.');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        loadUsers();
        loadSetupData();
    });
</script>
@endsection
