import React, { useEffect, useState } from 'react';
import api from '../services/api';
import { 
  UserPlus, Edit, 
  Search, 
  User, 
  Key, 
  UserCheck, 
  UserX, 
  CheckCircle, 
  ShieldAlert 
} from 'lucide-react';

export const Users: React.FC = () => {
  const [users, setUsers] = useState<any[]>([]);
  const [roles, setRoles] = useState<any[]>([]);
  const [departments, setDepartments] = useState<any[]>([]);
  const [search, setSearch] = useState('');
  const [loading, setLoading] = useState(true);
  
  // Create/Edit User Modal state
  const [createModalOpen, setCreateModalOpen] = useState(false);
  const [editMode, setEditMode] = useState(false);
  const [editingUserId, setEditingUserId] = useState<number | null>(null);
  
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [selectedRoles, setSelectedRoles] = useState<string[]>([]);
  
  // Staff fields
  const [isStaff, setIsStaff] = useState(false);
  const [firstName, setFirstName] = useState('');
  const [lastName, setLastName] = useState('');
  const [departmentId, setDepartmentId] = useState('');
  const [serviceNumber, setServiceNumber] = useState('');
  const [rank, setRank] = useState('');
  const [phone, setPhone] = useState('');
  const [specialization, setSpecialization] = useState('');

  // Password reset Modal state
  const [resetModalOpen, setResetModalOpen] = useState(false);
  const [activeUser, setActiveUser] = useState<any>(null);
  const [newPassword, setNewPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');

  const [success, setSuccess] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);

  const fetchUsers = async () => {
    setLoading(true);
    try {
      const res = await api.get(`/admin/users?search=${search}`);
      setUsers(res.data.users);
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const fetchSetup = async () => {
    try {
      const res = await api.get('/admin/users/setup');
      setRoles(res.data.roles);
      setDepartments(res.data.departments);
      if (res.data.departments.length > 0) {
        setDepartmentId(res.data.departments[0].id.toString());
      }
    } catch (err) {
      console.error(err);
    }
  };

  useEffect(() => {
    fetchUsers();
    fetchSetup();
  }, [search]);

  const handleOpenCreateModal = () => {
    setEditMode(false);
    setEditingUserId(null);
    setName('');
    setEmail('');
    setPassword('');
    setSelectedRoles([]);
    setIsStaff(false);
    setFirstName('');
    setLastName('');
    setServiceNumber('');
    setRank('');
    setPhone('');
    setSpecialization('');
    setSuccess(null);
    setError(null);
    setCreateModalOpen(true);
  };

  const handleOpenEditModal = (u: any) => {
    setEditMode(true);
    setEditingUserId(u.id);
    setName(u.name);
    setEmail(u.email);
    setPassword(''); // Leave blank unless changing
    setSelectedRoles(u.roles.map((r: any) => r.name));
    
    if (u.staff) {
      setIsStaff(true);
      setFirstName(u.staff.first_name || '');
      setLastName(u.staff.last_name || '');
      setDepartmentId(u.staff.department_id ? u.staff.department_id.toString() : (departments[0]?.id.toString() || ''));
      setServiceNumber(u.staff.service_number || '');
      setRank(u.staff.rank || '');
      setPhone(u.staff.phone || '');
      setSpecialization(u.staff.specialization || '');
    } else {
      setIsStaff(false);
      setFirstName('');
      setLastName('');
      setServiceNumber('');
      setRank('');
      setPhone('');
      setSpecialization('');
    }
    
    setSuccess(null);
    setError(null);
    setCreateModalOpen(true);
  };

  const handleUserSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setSuccess(null);
    setError(null);

    const payload: any = {
      name,
      email,
      roles: selectedRoles,
      is_staff: isStaff,
      first_name: firstName,
      last_name: lastName,
      department_id: departmentId ? parseInt(departmentId) : null,
      service_number: serviceNumber || null,
      rank: rank || null,
      phone,
      specialization: specialization || null
    };

    if (!editMode || password) {
      payload.password = password;
    }

    try {
      if (editMode) {
        await api.put(`/admin/users/${editingUserId}`, payload);
        setSuccess('User profile updated successfully!');
      } else {
        await api.post('/admin/users', payload);
        setSuccess('User account and profile created successfully!');
      }
      
      // Clear fields
      setName('');
      setEmail('');
      setPassword('');
      setSelectedRoles([]);
      setIsStaff(false);
      setFirstName('');
      setLastName('');
      setServiceNumber('');
      setRank('');
      setPhone('');
      setSpecialization('');
      
      fetchUsers();
      setTimeout(() => {
        setCreateModalOpen(false);
        setSuccess(null);
      }, 1500);
    } catch (err: any) {
      setError(err.response?.data?.message || 'Failed to create user account.');
    }
  };

  const handleToggleStatus = async (id: number) => {
    try {
      const res = await api.post(`/admin/users/${id}/toggle`);
      setUsers(users.map(u => u.id === id ? { ...u, status: res.data.user.status } : u));
    } catch (err) {
      alert('Error updating user account status.');
    }
  };

  const handleOpenResetModal = (u: any) => {
    setActiveUser(u);
    setNewPassword('');
    setConfirmPassword('');
    setSuccess(null);
    setError(null);
    setResetModalOpen(true);
  };

  const handleResetPasswordSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setSuccess(null);
    setError(null);

    if (newPassword !== confirmPassword) {
      setError('Passwords do not match.');
      return;
    }

    try {
      await api.post(`/admin/users/${activeUser.id}/reset-password`, {
        password: newPassword,
        password_confirmation: confirmPassword
      });
      setSuccess('User password reset successfully!');
      setTimeout(() => {
        setResetModalOpen(false);
        setSuccess(null);
      }, 1500);
    } catch (err: any) {
      setError(err.response?.data?.message || 'Password reset failed.');
    }
  };

  const handleRoleCheckbox = (roleName: string) => {
    if (selectedRoles.includes(roleName)) {
      setSelectedRoles(selectedRoles.filter(r => r !== roleName));
    } else {
      setSelectedRoles([...selectedRoles, roleName]);
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-xl font-bold text-slate-800 dark:text-white flex items-center gap-2">
            <User className="text-primary" /> User Account Management
          </h1>
          <p className="text-xs text-slate-800 dark:text-slate-200">ICT administrator dashboard to add users, assign roles, reset credentials, and suspend active staff accounts</p>
        </div>
        <button 
          onClick={handleOpenCreateModal}
          className="bg-primary hover:bg-primary-dark text-white px-4 py-2.5 rounded-xl text-xs font-semibold flex items-center gap-2 shadow-lg shadow-primary/10 transition-all cursor-pointer"
        >
          <UserPlus size={16} /> Create User Account
        </button>
      </div>

      {/* Search Filter */}
      <div className="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800/80">
        <div className="relative w-80">
          <input 
            type="text" 
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            placeholder="Search by name or email address..." 
            className="w-full pl-9 pr-4 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent text-slate-800 dark:text-slate-100 placeholder-slate-400 transition-all"
          />
          <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-800 dark:text-slate-200" size={14} />
        </div>
      </div>

      {/* Users table */}
      <div className="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
        {loading ? (
          <div className="p-12 flex justify-center">
            <div className="w-6 h-6 border-2 border-primary border-t-transparent rounded-full animate-spin"></div>
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-left text-xs">
              <thead className="bg-slate-50 dark:bg-slate-900/50 text-slate-800 dark:text-slate-200  border-b border-slate-100 dark:border-slate-800">
                <tr>
                  <th className="py-3.5 px-6 font-bold">User</th>
                  <th className="py-3.5 px-6 font-bold">Department</th>
                  <th className="py-3.5 px-6 font-bold">Role Matrix</th>
                  <th className="py-3.5 px-6 font-bold">Status</th>
                  <th className="py-3.5 px-6 font-bold text-right">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                {users.map((u) => (
                  <tr key={u.id} className="hover:bg-slate-50 dark:hover:bg-slate-900/20 transition-all">
                    <td className="py-3.5 px-6">
                      <span className="font-bold text-slate-900 dark:text-white">{u.name}</span>
                      <div className="text-[10px] text-slate-800 dark:text-slate-200">{u.email}</div>
                    </td>
                    <td className="py-3.5 px-6">
                      {u.staff ? (
                        <>
                          <span className="font-semibold text-slate-800 dark:text-slate-200">{u.staff.department?.name}</span>
                          <div className="text-[10px] text-slate-800 dark:text-slate-200">{u.staff.rank} ({u.staff.service_number})</div>
                        </>
                      ) : (
                        <span className="text-slate-800 dark:text-slate-200">Non-staff Admin</span>
                      )}
                    </td>
                    <td className="py-3.5 px-6">
                      <div className="flex flex-wrap gap-1">
                        {u.roles.map((r: any, idx: number) => (
                          <span key={idx} className="px-2 py-0.5 text-[9px] font-bold bg-primary/10 text-primary border border-primary/20 rounded">
                            {r.display_name}
                          </span>
                        ))}
                      </div>
                    </td>
                    <td className="py-3.5 px-6">
                      <span className={`px-2.5 py-1 text-[9px] font-bold rounded-full border ${
                        u.status === 'active' 
                          ? 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20' 
                          : 'bg-red-500/10 text-red-500 border-red-500/20'
                      }`}>
                        {u.status}
                      </span>
                    </td>
                    <td className="py-3.5 px-6 text-right space-x-3">
                      <button 
                        onClick={() => handleToggleStatus(u.id)}
                        className={`font-bold hover:underline inline-flex items-center gap-1 cursor-pointer ${
                          u.status === 'active' ? 'text-red-500 hover:text-red-600' : 'text-emerald-500 hover:text-emerald-600'
                        }`}
                      >
                        {u.status === 'active' ? <UserX size={14} /> : <UserCheck size={14} />}
                        {u.status === 'active' ? 'Suspend' : 'Activate'}
                      </button>
                      <button 
                        onClick={() => handleOpenEditModal(u)}
                        className="text-blue-500 hover:text-blue-600 font-bold hover:underline inline-flex items-center gap-1 cursor-pointer"
                      >
                        <Edit size={14} /> Edit
                      </button>
                      <button 
                        onClick={() => handleOpenResetModal(u)}
                        className="text-amber-500 hover:text-amber-600 font-bold hover:underline inline-flex items-center gap-1 cursor-pointer"
                      >
                        <Key size={14} /> Reset Pass
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>

      {/* 1. CREATE USER ACCOUNT MODAL */}
      {createModalOpen && (
        <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
          <div className="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-2xl overflow-hidden shadow-2xl animate-in fade-in-50 zoom-in-95">
            <div className="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50">
              <h3 className="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
                {editMode ? 'Edit User Profile' : 'Create User & Profile'}
              </h3>
              <button 
                onClick={() => setCreateModalOpen(false)}
                className="text-slate-800 dark:text-slate-200 hover:text-slate-600 text-lg cursor-pointer"
              >
                &times;
              </button>
            </div>
            
            <form onSubmit={handleUserSubmit} className="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
              {error && (
                <div className="bg-red-500/10 border border-red-500/20 text-red-500 p-3 rounded-xl text-xs flex items-center gap-2">
                  <ShieldAlert size={16} /> {error}
                </div>
              )}
              {success && (
                <div className="bg-emerald-500/10 border border-emerald-500/20 text-emerald-500 p-3 rounded-xl text-xs flex items-center gap-2">
                  <CheckCircle size={16} /> {success}
                </div>
              )}

              {/* Core Credentials */}
              <div className="space-y-3">
                <h4 className="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Account Credentials</h4>
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label className="text-[10px] font-bold text-slate-800 dark:text-slate-200">Full Name *</label>
                    <input 
                      type="text" 
                      required
                      value={name}
                      onChange={(e) => setName(e.target.value)}
                      placeholder="e.g. Dr. Jane Doe"
                      className="w-full mt-1.5 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 rounded-xl text-xs"
                    />
                  </div>
                  <div>
                    <label className="text-[10px] font-bold text-slate-800 dark:text-slate-200">Email Address *</label>
                    <input 
                      type="email" 
                      required
                      value={email}
                      onChange={(e) => setEmail(e.target.value)}
                      placeholder="jane.doe@mail.com"
                      className="w-full mt-1.5 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 rounded-xl text-xs"
                    />
                  </div>
                  <div className="sm:col-span-2">
                    <label className="text-[10px] font-bold text-slate-800 dark:text-slate-200">Account Password {editMode ? '(Leave blank to keep)' : '*'}</label>
                    <input 
                      type="password" 
                      required={!editMode}
                      value={password}
                      onChange={(e) => setPassword(e.target.value)}
                      placeholder="Min 8 characters"
                      className="w-full mt-1.5 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 rounded-xl text-xs"
                    />
                  </div>
                </div>
              </div>

              {/* Roles Checklist */}
              <div className="space-y-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                <h4 className="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Assign Role Matrix</h4>
                <div className="grid grid-cols-2 sm:grid-cols-3 gap-3">
                  {roles.map(role => (
                    <label key={role.id} className="flex items-center gap-2 p-2.5 hover:bg-slate-50 dark:hover:bg-slate-900/40 border border-slate-200 dark:border-slate-800 rounded-xl cursor-pointer transition-colors">
                      <input 
                        type="checkbox" 
                        checked={selectedRoles.includes(role.name)}
                        onChange={() => handleRoleCheckbox(role.name)}
                        className="rounded border-slate-300 text-primary focus:ring-primary w-4 h-4"
                      />
                      <span className="text-[10px] font-semibold text-slate-700 dark:text-slate-300">{role.display_name}</span>
                    </label>
                  ))}
                </div>
              </div>

              {/* Staff Details toggle */}
              <div className="space-y-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                <div className="flex items-center justify-between">
                  <h4 className="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Hospital Staff Profile</h4>
                  <label className="flex items-center gap-2 cursor-pointer">
                    <input 
                      type="checkbox"
                      checked={isStaff}
                      onChange={() => setIsStaff(!isStaff)}
                      className="rounded border-slate-300 text-primary focus:ring-primary w-4 h-4"
                    />
                    <span className="text-[10px] font-bold text-primary uppercase">Link Staff details</span>
                  </label>
                </div>

                {isStaff && (
                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 bg-slate-50 dark:bg-slate-900/40 border border-slate-100 dark:border-slate-800 rounded-2xl animate-in slide-in-from-top-3">
                    <div>
                      <label className="text-[10px] font-bold text-slate-800 dark:text-slate-200">First Name *</label>
                      <input 
                        type="text" 
                        required={isStaff}
                        value={firstName}
                        onChange={(e) => setFirstName(e.target.value)}
                        className="w-full mt-1.5 px-3 py-2 border border-slate-200 bg-white dark:bg-slate-900 rounded-xl text-xs"
                      />
                    </div>
                    <div>
                      <label className="text-[10px] font-bold text-slate-800 dark:text-slate-200">Last Name *</label>
                      <input 
                        type="text" 
                        required={isStaff}
                        value={lastName}
                        onChange={(e) => setLastName(e.target.value)}
                        className="w-full mt-1.5 px-3 py-2 border border-slate-200 bg-white dark:bg-slate-900 rounded-xl text-xs"
                      />
                    </div>
                    <div>
                      <label className="text-[10px] font-bold text-slate-800 dark:text-slate-200">Department Unit *</label>
                      <select 
                        required={isStaff}
                        value={departmentId}
                        onChange={(e) => setDepartmentId(e.target.value)}
                        className="w-full mt-1.5 px-3 py-2 border border-slate-200 bg-white dark:bg-slate-900 rounded-xl text-xs"
                      >
                        {departments.map(d => (
                          <option key={d.id} value={d.id}>{d.name}</option>
                        ))}
                      </select>
                    </div>
                    <div>
                      <label className="text-[10px] font-bold text-slate-800 dark:text-slate-200">NIS Service Number</label>
                      <input 
                        type="text" 
                        value={serviceNumber}
                        onChange={(e) => setServiceNumber(e.target.value)}
                        placeholder="e.g. NIS/2026/839"
                        className="w-full mt-1.5 px-3 py-2 border border-slate-200 bg-white dark:bg-slate-900 rounded-xl text-xs"
                      />
                    </div>
                    <div>
                      <label className="text-[10px] font-bold text-slate-800 dark:text-slate-200">Rank</label>
                      <input 
                        type="text" 
                        value={rank}
                        onChange={(e) => setRank(e.target.value)}
                        className="w-full mt-1.5 px-3 py-2 border border-slate-200 bg-white dark:bg-slate-900 rounded-xl text-xs"
                      />
                    </div>
                    <div>
                      <label className="text-[10px] font-bold text-slate-800 dark:text-slate-200">Phone Number *</label>
                      <input 
                        type="text" 
                        required={isStaff}
                        value={phone}
                        onChange={(e) => setPhone(e.target.value)}
                        className="w-full mt-1.5 px-3 py-2 border border-slate-200 bg-white dark:bg-slate-900 rounded-xl text-xs"
                      />
                    </div>
                  </div>
                )}
              </div>

              <div className="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                <button 
                  type="button" 
                  onClick={() => setCreateModalOpen(false)}
                  className="px-4 py-2 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 dark:text-slate-200 hover:bg-slate-50 cursor-pointer"
                >
                  Cancel
                </button>
                <button 
                  type="submit" 
                  className="bg-primary hover:bg-primary-dark text-white px-6 py-2 rounded-xl text-xs font-bold transition-colors cursor-pointer"
                >
                  {editMode ? 'Save Changes' : 'Create User Account'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* 2. RESET PASSWORD MODAL */}
      {resetModalOpen && activeUser && (
        <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
          <div className="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-sm overflow-hidden shadow-2xl">
            <div className="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50">
              <h3 className="text-sm font-bold text-slate-800 dark:text-white">
                Reset Password: {activeUser.name}
              </h3>
              <button 
                onClick={() => setResetModalOpen(false)}
                className="text-slate-800 dark:text-slate-200 hover:text-slate-600 text-lg cursor-pointer"
              >
                &times;
              </button>
            </div>
            
            <form onSubmit={handleResetPasswordSubmit} className="p-6 space-y-4">
              {error && (
                <div className="bg-red-500/10 border border-red-500/20 text-red-500 p-3 rounded-xl text-xs flex items-center gap-2">
                  <ShieldAlert size={16} /> {error}
                </div>
              )}
              {success && (
                <div className="bg-emerald-500/10 border border-emerald-500/20 text-emerald-500 p-3 rounded-xl text-xs flex items-center gap-2">
                  <CheckCircle size={16} /> {success}
                </div>
              )}

              <div>
                <label className="text-[10px] font-bold text-slate-800 dark:text-slate-200">New Password *</label>
                <input
                  type="password"
                  required
                  placeholder="Min 8 characters"
                  value={newPassword}
                  onChange={(e) => setNewPassword(e.target.value)}
                  className="w-full mt-1.5 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 rounded-xl text-xs text-slate-800 dark:text-slate-100"
                />
              </div>

              <div>
                <label className="text-[10px] font-bold text-slate-800 dark:text-slate-200">Confirm Password *</label>
                <input
                  type="password"
                  required
                  placeholder="Confirm password"
                  value={confirmPassword}
                  onChange={(e) => setConfirmPassword(e.target.value)}
                  className="w-full mt-1.5 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 rounded-xl text-xs text-slate-800 dark:text-slate-100"
                />
              </div>

              <div className="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                <button 
                  type="button" 
                  onClick={() => setResetModalOpen(false)}
                  className="px-4 py-2 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 dark:text-slate-200 hover:bg-slate-50 cursor-pointer"
                >
                  Cancel
                </button>
                <button 
                  type="submit" 
                  className="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-xl text-xs font-semibold shadow-lg shadow-primary/10 cursor-pointer"
                >
                  Save New Password
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};


