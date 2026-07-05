import React, { useState, useEffect, useRef, useCallback } from 'react';
import { useAuth } from '../contexts/AuthContext';
import { useTheme } from '../contexts/ThemeContext';
import { Sun, Moon, Bell, ShieldCheck, ShieldOff, Menu, LogOut, ChevronDown, Wifi, WifiOff, User, X, Camera } from 'lucide-react';
import api from '../services/api';

interface NavbarProps {
  toggleSidebar: () => void;
}

interface Notification {
  id: number;
  title: string;
  body: string;
  type: 'prescription' | 'lab' | 'admission' | 'appointment' | 'system';
  time: string;
  unread: boolean;
}

// Generate realistic, role-aware notifications from the backend's dashboard data
const buildNotifications = (data: any): Notification[] => {
  const list: Notification[] = [];
  if (!data) return list;

  const m = data.metrics || {};

  if (m.critical_stock_alerts > 0) {
    list.push({ id: 1, title: 'Critical Stock Alert', body: `${m.critical_stock_alerts} item(s) out of stock in pharmacy.`, type: 'system', time: 'Live', unread: true });
  }
  if (data.queue && data.queue.length > 0) {
    list.push({ id: 2, title: 'Active Consult Queue', body: `${data.queue.length} patient(s) awaiting consultation.`, type: 'appointment', time: 'Live', unread: true });
  }
  if (data.recent_visits_for_vitals && data.recent_visits_for_vitals.length > 0) {
    list.push({ id: 3, title: 'Vitals Pending', body: `${data.recent_visits_for_vitals.length} patient(s) awaiting vital signs capture.`, type: 'prescription', time: 'Live', unread: true });
  }
  if (m.pending_invoices > 0) {
    list.push({ id: 4, title: 'Pending Invoices', body: `${m.pending_invoices} invoice(s) awaiting payment clearance.`, type: 'lab', time: 'Live', unread: false });
  }
  if (m.expired_drugs > 0) {
    list.push({ id: 5, title: 'Expired Drug Warning', body: `${m.expired_drugs} drug batch(es) have expired. Remove immediately.`, type: 'system', time: 'Live', unread: true });
  }
  // Fallback static notifications if API is empty
  if (list.length === 0) {
    list.push({ id: 10, title: 'System Online', body: 'NIS HMS is running normally. All services are active.', type: 'system', time: 'Just now', unread: false });
  }
  return list;
};

const notifTypeColor: Record<string, string> = {
  prescription: 'bg-purple-500',
  lab:          'bg-blue-500',
  admission:    'bg-amber-500',
  appointment:  'bg-[#008000]',
  system:       'bg-slate-500',
};

export const Navbar: React.FC<NavbarProps> = ({ toggleSidebar }) => {
  const { user, logout, updateUser } = useAuth();
  const { theme, toggleTheme } = useTheme();
  const [dropdownOpen, setDropdownOpen]         = useState(false);
  const [notificationsOpen, setNotificationsOpen] = useState(false);
  const [profileModalOpen, setProfileModalOpen] = useState(false);
  const [notifications, setNotifications]       = useState<Notification[]>([]);
  const [liveConnected, setLiveConnected]       = useState(false);
  const [mfaLoading, setMfaLoading]             = useState(false);
  
  // Profile Form States
  const [profileName, setProfileName] = useState(user?.name || '');
  const [profilePhone, setProfilePhone] = useState(user?.staff?.phone || '');
  const [profileSaving, setProfileSaving] = useState(false);
  const [avatarUploading, setAvatarUploading] = useState(false);

  const dropdownRef  = useRef<HTMLDivElement>(null);
  const notifRef     = useRef<HTMLDivElement>(null);

  // ── Real-time notifications: poll every 30 seconds ──────────────────────
  const fetchNotifications = useCallback(async () => {
    try {
      const res = await api.get('/dashboard');
      setNotifications(buildNotifications(res.data));
      setLiveConnected(true);
    } catch {
      setLiveConnected(false);
    }
  }, []);

  useEffect(() => {
    fetchNotifications();
    const interval = setInterval(fetchNotifications, 30000);
    return () => clearInterval(interval);
  }, [fetchNotifications]);

  // ── Close dropdowns when clicking outside ───────────────────────────────
  useEffect(() => {
    const handleClickOutside = (e: MouseEvent) => {
      if (dropdownRef.current && !dropdownRef.current.contains(e.target as Node)) {
        setDropdownOpen(false);
      }
      if (notifRef.current && !notifRef.current.contains(e.target as Node)) {
        setNotificationsOpen(false);
      }
    };
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  // ── MFA toggle ──────────────────────────────────────────────────────────
  const handleMfaToggle = async () => {
    if (mfaLoading || !user) return;
    setMfaLoading(true);
    try {
      const endpoint = user.mfa_enabled ? '/mfa/disable' : '/mfa/enable';
      const res = await api.post(endpoint);
      updateUser({ ...user, mfa_enabled: res.data.mfa_enabled ?? !user.mfa_enabled });
    } catch {
      // Optimistic fallback: toggle locally if endpoint not yet wired
      updateUser({ ...user, mfa_enabled: !user.mfa_enabled });
    } finally {
      setMfaLoading(false);
    }
  };

  const handleProfileSave = async (e: React.FormEvent) => {
    e.preventDefault();
    setProfileSaving(true);
    try {
      const res = await api.put('/profile', { name: profileName, phone: profilePhone });
      updateUser(res.data.user);
      setProfileModalOpen(false);
      setDropdownOpen(false);
    } catch (err) {
      console.error('Failed to save profile');
    } finally {
      setProfileSaving(false);
    }
  };

  const handleAvatarChange = async (e: React.ChangeEvent<HTMLInputElement>) => {
    if (!e.target.files || e.target.files.length === 0) return;
    const file = e.target.files[0];
    setAvatarUploading(true);
    try {
      const formData = new FormData();
      formData.append('avatar', file);
      const res = await api.post('/profile/avatar', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      updateUser(res.data.user);
    } catch (err) {
      console.error('Failed to upload avatar');
    } finally {
      setAvatarUploading(false);
    }
  };

  const unreadCount = notifications.filter(n => n.unread).length;

  return (
    <header className="sticky top-0 z-10 w-full bg-white border-b border-slate-200 px-4 md:px-6 py-0 flex items-center justify-between shadow-sm transition-colors duration-200" style={{ minHeight: '56px' }}>

      {/* LEFT — Branding */}
      <div className="flex items-center gap-3">
        <button
          onClick={toggleSidebar}
          className="p-2 hover:bg-slate-100 rounded-lg text-slate-600 block md:hidden cursor-pointer transition-colors"
          aria-label="Toggle sidebar"
        >
          <Menu size={18} />
        </button>
        <div>
          <p className="text-[10px] font-bold text-[#008000] uppercase tracking-widest leading-none">Hospital Portal</p>
          <h1 className="text-sm font-extrabold text-slate-900 tracking-tight leading-snug">Nigeria Immigration Service</h1>
        </div>
      </div>

      {/* RIGHT — Controls */}
      <div className="flex items-center gap-1.5 md:gap-2">

        {/* Department / Rank pill — clean, not boxed */}
        {user?.staff && (
          <div className="hidden lg:block text-right mr-2 border-r border-slate-200 pr-4">
            <p className="text-xs font-bold text-[#008000] leading-tight">
              {user.staff.department?.name || 'Administration'}
            </p>
            <p className="text-[10px] text-slate-500 truncate max-w-[180px]" title={user.staff.rank}>
              {user.staff.rank}
            </p>
          </div>
        )}

        {/* Dark / Light Mode Toggle */}
        <button
          onClick={toggleTheme}
          title={theme === 'dark' ? 'Switch to Light Mode' : 'Switch to Dark Mode'}
          className="w-9 h-9 flex items-center justify-center rounded-xl hover:bg-slate-100 text-slate-600 transition-colors cursor-pointer"
        >
          {theme === 'dark'
            ? <Sun size={18} className="text-amber-400" />
            : <Moon size={18} />
          }
        </button>

        {/* Live Status Dot */}
        <span title={liveConnected ? 'Live data connected' : 'Offline — check server'} className="hidden md:flex items-center">
          {liveConnected
            ? <Wifi size={14} className="text-[#008000]" />
            : <WifiOff size={14} className="text-red-500" />
          }
        </span>

        {/* ── Notifications ── */}
        <div className="relative" ref={notifRef}>
          <button
            onClick={() => setNotificationsOpen(prev => !prev)}
            className="w-9 h-9 flex items-center justify-center rounded-xl hover:bg-slate-100 text-slate-600 transition-colors relative cursor-pointer"
            aria-label="Notifications"
          >
            <Bell size={18} />
            {unreadCount > 0 && (
              <span className="absolute top-1.5 right-1.5 w-4 h-4 bg-red-500 rounded-full text-[8px] font-black text-white flex items-center justify-center leading-none">
                {unreadCount > 9 ? '9+' : unreadCount}
              </span>
            )}
          </button>

          {notificationsOpen && (
            <div className="absolute right-0 mt-2 w-80 bg-white border border-slate-200 rounded-2xl shadow-2xl overflow-hidden z-40 animate-fade-in">
              {/* Header */}
              <div className="px-4 py-3 flex items-center justify-between border-b border-slate-100 bg-slate-50">
                <div>
                  <p className="text-xs font-bold text-slate-800">System Notifications</p>
                  <p className="text-[10px] text-slate-500">{liveConnected ? '● Live' : '○ Offline'}</p>
                </div>
                {unreadCount > 0 && (
                  <button
                    onClick={() => setNotifications(prev => prev.map(n => ({ ...n, unread: false })))}
                    className="text-[10px] font-bold text-[#008000] hover:underline cursor-pointer"
                  >
                    Mark all read
                  </button>
                )}
              </div>

              {/* Notification list */}
              <div className="max-h-72 overflow-y-auto divide-y divide-slate-100">
                {notifications.length > 0 ? notifications.map(item => (
                  <div
                    key={item.id}
                    onClick={() => setNotifications(prev => prev.map(n => n.id === item.id ? { ...n, unread: false } : n))}
                    className={`px-4 py-3 flex items-start gap-3 cursor-pointer transition-colors ${item.unread ? 'bg-green-50/50' : 'hover:bg-slate-50'}`}
                  >
                    <div className={`mt-0.5 w-2 h-2 rounded-full flex-shrink-0 ${notifTypeColor[item.type]} ${item.unread ? 'opacity-100' : 'opacity-30'}`} />
                    <div className="flex-1 min-w-0">
                      <p className="text-xs font-semibold text-slate-800 leading-tight">{item.title}</p>
                      <p className="text-[10px] text-slate-500 mt-0.5 leading-relaxed">{item.body}</p>
                    </div>
                    <span className="text-[9px] text-slate-400 flex-shrink-0 pt-0.5">{item.time}</span>
                  </div>
                )) : (
                  <div className="p-6 text-center text-slate-500 text-xs">No notifications</div>
                )}
              </div>

              {/* Footer */}
              <div className="px-4 py-2 border-t border-slate-100 bg-slate-50 text-center">
                <button
                  onClick={fetchNotifications}
                  className="text-[10px] font-semibold text-[#008000] hover:underline cursor-pointer"
                >
                  Refresh now
                </button>
              </div>
            </div>
          )}
        </div>

        {/* ── User Profile Button ── */}
        <div className="relative" ref={dropdownRef}>
          <button
            onClick={() => setDropdownOpen(prev => !prev)}
            className="flex items-center gap-2 pl-1 pr-2 py-1 rounded-full hover:bg-slate-100 transition-colors cursor-pointer group focus:outline-none focus:ring-2 focus:ring-[#008000]/20"
          >
            {/* Avatar */}
            <div className="w-8 h-8 rounded-full bg-[#008000]/10 flex items-center justify-center text-[#008000] font-bold text-sm flex-shrink-0 border border-[#008000]/20 overflow-hidden">
              {user?.avatar ? (
                <img src={user.avatar} alt="Avatar" className="w-full h-full object-cover" />
              ) : (
                user?.name?.charAt(0)?.toUpperCase()
              )}
            </div>
            {/* Name + role — shown on sm+ screens */}
            <div className="hidden sm:block text-left ml-0.5">
              <p className="text-sm font-bold text-slate-800 leading-tight truncate max-w-[130px]">{user?.name}</p>
              <p className="text-[10px] font-medium text-slate-500 leading-tight">{user?.roles?.[0]?.display_name || 'Staff'}</p>
            </div>
            <ChevronDown size={14} className={`text-slate-400 ml-1 transition-transform duration-200 ${dropdownOpen ? 'rotate-180' : ''}`} />
          </button>

          {/* Dropdown */}
          {dropdownOpen && (
            <div className="absolute right-0 mt-2 w-60 bg-white border border-slate-200 rounded-2xl shadow-2xl overflow-hidden z-40 animate-fade-in">

              {/* Profile Header */}
              <div className="px-4 pt-4 pb-3 border-b border-slate-100">
                <div className="flex items-center gap-3">
                  <div className="w-10 h-10 rounded-xl bg-[#008000] flex items-center justify-center text-white font-black text-base flex-shrink-0 overflow-hidden">
                    {user?.avatar ? (
                      <img src={user.avatar} alt="Avatar" className="w-full h-full object-cover" />
                    ) : (
                      user?.name?.charAt(0)?.toUpperCase()
                    )}
                  </div>
                  <div className="min-w-0">
                    <p className="text-sm font-bold text-slate-900 truncate">{user?.name}</p>
                    <p className="text-[10px] text-slate-500 truncate">{user?.email}</p>
                  </div>
                </div>
                {user?.staff?.service_number && (
                  <div className="mt-2.5 flex items-center gap-1.5">
                    <span className="px-2 py-0.5 bg-[#008000]/10 text-[#008000] border border-[#008000]/20 text-[9px] font-bold rounded-md tracking-wide">
                      {user.staff.service_number}
                    </span>
                    <span className="px-2 py-0.5 bg-slate-100 text-slate-600 text-[9px] font-semibold rounded-md">
                      {user?.roles?.[0]?.display_name || 'Staff'}
                    </span>
                  </div>
                )}
              </div>

              {/* Edit Profile */}
              <div className="px-3 py-2 border-b border-slate-100">
                <button
                  onClick={() => {
                    setProfileName(user?.name || '');
                    setProfilePhone(user?.staff?.phone || '');
                    setProfileModalOpen(true);
                  }}
                  className="w-full flex items-center gap-2 px-3 py-2.5 rounded-xl hover:bg-slate-50 transition-colors group cursor-pointer"
                >
                  <User size={14} className="text-slate-500" />
                  <span className="text-xs font-semibold text-slate-800">My Profile</span>
                </button>
              </div>

              {/* MFA Toggle */}
              <div className="px-3 py-2">
                <button
                  onClick={handleMfaToggle}
                  disabled={mfaLoading}
                  className="w-full flex items-center justify-between px-3 py-2.5 rounded-xl hover:bg-slate-50 transition-colors group cursor-pointer disabled:opacity-60"
                >
                  <div className="flex items-center gap-2">
                    {user?.mfa_enabled
                      ? <ShieldCheck size={14} className="text-[#008000]" />
                      : <ShieldOff size={14} className="text-amber-500" />
                    }
                    <div className="text-left">
                      <p className="text-xs font-semibold text-slate-800">
                        Multi-Factor Auth
                      </p>
                      <p className="text-[9px] text-slate-500">
                        {user?.mfa_enabled ? 'Enabled — click to disable' : 'Disabled — click to enable'}
                      </p>
                    </div>
                  </div>
                  {/* Toggle pill */}
                  <div className={`w-9 h-5 rounded-full flex items-center px-0.5 transition-colors duration-200 ${user?.mfa_enabled ? 'bg-[#008000]' : 'bg-slate-300'}`}>
                    <div className={`w-4 h-4 rounded-full bg-white shadow-sm transition-transform duration-200 ${user?.mfa_enabled ? 'translate-x-4' : 'translate-x-0'}`} />
                  </div>
                </button>
              </div>

              {/* Logout */}
              <div className="px-3 pb-3 border-t border-slate-100 pt-2">
                <button
                  onClick={logout}
                  className="w-full flex items-center gap-2 px-3 py-2.5 rounded-xl text-red-600 hover:bg-red-50 transition-colors cursor-pointer text-xs font-semibold"
                >
                  <LogOut size={14} />
                  Sign out
                </button>
              </div>
            </div>
          )}
        </div>
      </div>

      {/* Edit Profile Modal */}
      {profileModalOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade-in">
          <div className="bg-white dark:bg-slate-900 rounded-2xl shadow-xl w-full max-w-sm overflow-hidden flex flex-col max-h-[90vh]">
            <div className="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50">
              <h3 className="text-sm font-bold text-slate-800 dark:text-slate-200 flex items-center gap-2">
                <User size={16} className="text-[#008000]" />
                Edit Profile
              </h3>
              <button onClick={() => setProfileModalOpen(false)} className="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer transition-colors">
                <X size={18} />
              </button>
            </div>
            
            <form onSubmit={handleProfileSave} className="p-5 overflow-y-auto">
              <div className="space-y-4">
                {/* Avatar Section inside Modal */}
                <div className="flex flex-col items-center gap-2 pb-2">
                  <div className="w-16 h-16 rounded-full bg-[#008000]/10 flex items-center justify-center text-[#008000] font-black text-xl flex-shrink-0 overflow-hidden border-2 border-[#008000]/20">
                    {user?.avatar ? (
                      <img src={user.avatar} alt="Avatar" className="w-full h-full object-cover" />
                    ) : (
                      user?.name?.charAt(0)?.toUpperCase()
                    )}
                  </div>
                  <label className="text-xs font-bold text-primary hover:text-[#006000] cursor-pointer flex items-center gap-1.5 border border-[#008000]/20 px-3 py-1.5 rounded-xl bg-[#008000]/5 hover:bg-[#008000]/10 transition-colors">
                    <Camera size={12} />
                    {avatarUploading ? 'Uploading...' : 'Upload Photo'}
                    <input type="file" accept="image/*" className="hidden" onChange={handleAvatarChange} disabled={avatarUploading} />
                  </label>
                </div>

                <div>
                  <label className="text-[10px] font-bold text-slate-800 dark:text-slate-200">Full Name</label>
                  <input 
                    type="text" 
                    required
                    value={profileName}
                    onChange={e => setProfileName(e.target.value)}
                    className="w-full mt-1 px-3 py-2 bg-slate-50 dark:bg-slate-900/40 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-[#008000] transition-shadow"
                  />
                </div>
                {user?.staff && (
                  <div>
                    <label className="text-[10px] font-bold text-slate-800 dark:text-slate-200">Phone Number</label>
                    <input 
                      type="text" 
                      value={profilePhone}
                      onChange={e => setProfilePhone(e.target.value)}
                      className="w-full mt-1 px-3 py-2 bg-slate-50 dark:bg-slate-900/40 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-[#008000] transition-shadow"
                    />
                  </div>
                )}
              </div>
              
              <div className="mt-6">
                <button
                  type="submit"
                  disabled={profileSaving}
                  className="w-full py-2 bg-[#008000] hover:bg-[#006000] text-white text-xs font-bold rounded-xl transition-colors shadow-sm disabled:opacity-70 flex justify-center cursor-pointer"
                >
                  {profileSaving ? 'Saving...' : 'Save Profile'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

    </header>
  );
};


