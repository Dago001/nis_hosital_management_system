import React from 'react';
import { Link, useLocation } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';
import { 
  LayoutDashboard, 
  Users, 
  Calendar, 
  Activity, 
  Stethoscope, 
  TestTube, 
  CreditCard, 
  ClipboardList, 
  Settings, 
  LogOut,
  ChevronLeft,
  ChevronRight,
  Pill
} from 'lucide-react';
import logo from '../assets/nis_logo.jpg';

interface SidebarProps {
  collapsed: boolean;
  setCollapsed: (val: boolean) => void;
}

export const Sidebar: React.FC<SidebarProps> = ({ collapsed, setCollapsed }) => {
  const { user, logout } = useAuth();
  const location = useLocation();

  const getMenuItems = () => {
    const role = user?.roles?.[0]?.name;
    const items = [
      {
        path: '/',
        label: 'Dashboard',
        icon: <LayoutDashboard size={20} />,
        roles: [
          'super_admin', 'hospital_admin', 'medical_director', 'chief_medical_officer',
          'doctor', 'consultant', 'nurse', 'pharmacist', 'lab_scientist', 'radiographer',
          'records_officer', 'cashier', 'account_officer', 'receptionist', 'health_info_officer',
          'store_officer', 'inventory_officer', 'procurement_officer', 'hr_officer', 'ict_admin',
          'ambulance_officer', 'ward_manager', 'theatre_manager', 'dental_officer',
          'eye_clinic_officer', 'physiotherapist', 'staff', 'patient'
        ]
      },
      {
        path: '/patients',
        label: 'Patients',
        icon: <Users size={20} />,
        roles: [
          'super_admin', 'hospital_admin', 'medical_director', 'chief_medical_officer',
          'doctor', 'consultant', 'nurse', 'records_officer', 'cashier', 'receptionist',
          'health_info_officer', 'ambulance_officer', 'ward_manager', 'theatre_manager',
          'dental_officer', 'eye_clinic_officer', 'physiotherapist', 'staff', 'patient'
        ]
      },
      {
        path: '/appointments',
        label: 'Appointments',
        icon: <Calendar size={20} />,
        roles: [
          'super_admin', 'hospital_admin', 'chief_medical_officer', 'doctor', 'consultant',
          'nurse', 'records_officer', 'receptionist', 'dental_officer', 'eye_clinic_officer'
        ]
      },
      {
        path: '/vitals',
        label: 'Vitals Entry',
        icon: <Activity size={20} />,
        roles: ['super_admin', 'nurse', 'ward_manager', 'ambulance_officer']
      },
      {
        path: '/consultations',
        label: 'Consultation',
        icon: <Stethoscope size={20} />,
        roles: [
          'super_admin', 'doctor', 'consultant', 'dental_officer', 'eye_clinic_officer',
          'physiotherapist', 'theatre_manager'
        ]
      },
      {
        path: '/laboratory',
        label: 'Laboratory',
        icon: <TestTube size={20} />,
        roles: ['super_admin', 'medical_director', 'lab_scientist', 'radiographer', 'doctor']
      },
      {
        path: '/pharmacy',
        label: 'Pharmacy',
        icon: <Pill size={20} />,
        roles: [
          'super_admin', 'medical_director', 'pharmacist', 'store_officer', 'inventory_officer',
          'procurement_officer'
        ]
      },
      {
        path: '/billing',
        label: 'Billing & Cashier',
        icon: <CreditCard size={20} />,
        roles: ['super_admin', 'hospital_admin', 'medical_director', 'cashier', 'account_officer']
      },
      {
        path: '/audit-trail',
        label: 'Audit Trail',
        icon: <ClipboardList size={20} />,
        roles: ['super_admin', 'ict_admin', 'health_info_officer']
      },
      {
        path: '/admin/users',
        label: 'User Accounts',
        icon: <Users size={20} />,
        roles: ['super_admin', 'ict_admin', 'hr_officer', 'hospital_admin']
      },
      {
        path: '/settings',
        label: 'System Settings',
        icon: <Settings size={20} />,
        roles: ['super_admin', 'ict_admin', 'hospital_admin']
      }
    ];

    // Filter items based on user's role
    return items.filter(item => {
      if (!item.roles) return true; // Items without roles (e.g. Dashboard) are visible to everyone
      if (!role) return false;      // If user has no role, hide restricted items
      return item.roles.includes(role);
    });
  };

  const menuItems = getMenuItems();

  return (
    <aside 
      className={`fixed top-0 left-0 z-20 h-screen text-white flex flex-col justify-between border-r border-white/10 transition-all duration-300 ${
        collapsed ? 'w-0 -translate-x-full md:w-20 md:translate-x-0' : 'w-64 translate-x-0'
      }`}
      style={{ backgroundColor: '#008000' }}
    >
      <div>
        {/* Logo and Branding header */}
        <div className="p-4 flex items-center gap-3 border-b border-white/10 relative">
          <img 
            src={logo} 
            alt="NIS Crest" 
            className="w-10 h-10 object-contain rounded-md bg-white border border-white/20 p-0.5" 
          />
          {!collapsed && (
            <div className="flex flex-col">
              <span className="font-bold text-sm leading-tight text-white tracking-wide">NIS HOSPITALS</span>
              <span className="text-xs text-white/80 font-semibold">HMS ENTERPRISE</span>
            </div>
          )}
          <button 
            onClick={() => setCollapsed(!collapsed)}
            className="absolute top-1/2 -right-3 transform -translate-y-1/2 bg-white/20 hover:bg-white/30 text-white rounded-full p-1 border border-white/10 cursor-pointer shadow-md transition-colors"
          >
            {collapsed ? <ChevronRight size={12} /> : <ChevronLeft size={12} />}
          </button>
        </div>

        {/* User Card info inside sidebar */}
        {!collapsed && user && (
          <div className="p-4 bg-white/10 border-b border-white/15 m-3 rounded-lg flex items-center gap-3">
            <div className="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center text-white font-bold border border-white/30">
              {user.name.charAt(0)}
            </div>
            <div className="flex flex-col min-w-0">
              <p className="font-bold text-sm truncate">{user?.name}</p>
              <span className="text-xs text-white/70 truncate">{user?.roles?.[0]?.display_name || 'Staff'}</span>
            </div>
          </div>
        )}

        {/* Navigation links */}
        <nav className="mt-4 px-2 space-y-1">
          {menuItems.map((item) => {
            const isActive = location.pathname === item.path;
            return (
              <Link
                key={item.path}
                to={item.path}
                className={`flex items-center gap-3 px-3 py-3 rounded-lg text-sm font-medium transition-all duration-200 ${
                  isActive 
                    ? 'bg-white/20 text-white shadow-md' 
                    : 'text-white/80 hover:bg-white/10 hover:text-white'
                }`}
              >
                <div className="text-white">
                  {item.icon}
                </div>
                {!collapsed && <span className="truncate">{item.label}</span>}
              </Link>
            );
          })}
        </nav>
      </div>

      {/* Logout button */}
      <div className="p-2 border-t border-white/10">
        <button
          onClick={logout}
          className="w-full flex items-center gap-3 px-3 py-3 text-white/80 hover:bg-white/10 hover:text-white rounded-lg text-sm font-medium transition-all cursor-pointer"
        >
          <LogOut size={20} />
          {!collapsed && <span>Logout</span>}
        </button>
      </div>
    </aside>
  );
};
