import React, { useState } from 'react';
import { BrowserRouter, Routes, Route, Navigate, useLocation } from 'react-router-dom';
import { AuthProvider, useAuth } from './contexts/AuthContext';
import { ThemeProvider } from './contexts/ThemeContext';
import { ErrorBoundary } from './components/ErrorBoundary';
import { Sidebar } from './components/Sidebar';
import { Navbar } from './components/Navbar';
import { Login } from './pages/Login';
import { Dashboard } from './pages/Dashboard';
import { Patients } from './pages/Patients';
import { Appointments } from './pages/Appointments';
import { Vitals } from './pages/Vitals';
import { Consultations } from './pages/Consultations';
import { Laboratory } from './pages/Laboratory';
import { Billing } from './pages/Billing';
import { AuditTrail } from './pages/AuditTrail';
import { Settings } from './pages/Settings';
import { Users } from './pages/Users';
import { Pharmacy } from './pages/Pharmacy';


// Route Guard to verify token authentication status
const AuthGuard: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const { token, loading } = useAuth();

  if (loading) {
    return (
      <div className="flex items-center justify-center min-h-screen bg-white">
        <div className="w-8 h-8 border-4 border-primary border-t-transparent rounded-full animate-spin"></div>
      </div>
    );
  }

  return token ? <>{children}</> : <Navigate to="/login" replace />;
};

// Main Admin Layout
const AppLayout: React.FC = () => {
  const [collapsed, setCollapsed] = useState(window.innerWidth < 768);
  const location = useLocation();

  React.useEffect(() => {
    const handleResize = () => {
      if (window.innerWidth < 768) {
        setCollapsed(true);
      } else {
        setCollapsed(false);
      }
    };
    window.addEventListener('resize', handleResize);
    return () => window.removeEventListener('resize', handleResize);
  }, []);

  const isDashboard = location.pathname === '/';

  return (
    <div className="min-h-screen bg-white text-slate-800 dark:text-slate-200 flex">
      {/* Mobile Sidebar Overlay Backdrop */}
      {!collapsed && (
        <div 
          className="fixed inset-0 z-10 bg-black/50 backdrop-blur-sm md:hidden transition-opacity duration-300" 
          onClick={() => setCollapsed(true)}
        ></div>
      )}

      <Sidebar collapsed={collapsed} setCollapsed={setCollapsed} />
      
      <div className={`flex-1 flex flex-col min-h-screen transition-all duration-300 ${collapsed ? 'pl-0 md:pl-20' : 'pl-0 md:pl-64'}`}>
        <Navbar toggleSidebar={() => setCollapsed(!collapsed)} />
        <main className={`flex-1 p-4 md:p-8 max-w-7xl w-full mx-auto space-y-6 ${!isDashboard ? 'theme-white-only' : ''}`}>
          <Routes>
            <Route path="/" element={<Dashboard />} />
            <Route path="/patients" element={<Patients />} />
            <Route path="/appointments" element={<Appointments />} />
            <Route path="/vitals" element={<Vitals />} />
            <Route path="/consultations" element={<Consultations />} />
            <Route path="/laboratory" element={<Laboratory />} />
            <Route path="/pharmacy" element={<Pharmacy />} />
            <Route path="/billing" element={<Billing />} />
            <Route path="/audit-trail" element={<AuditTrail />} />
            <Route path="/admin/users" element={<Users />} />
            <Route path="/settings" element={<Settings />} />
            <Route path="*" element={<Navigate to="/" replace />} />
          </Routes>
        </main>
        <footer className="bg-white border-t border-slate-200 py-6 text-center text-xs text-slate-800 dark:text-slate-200 font-medium">
          <p>© {new Date().getFullYear()} Nigeria Immigration Service (NIS) Medical Services. All Rights Reserved. Enterprise Portal.</p>
        </footer>
      </div>
    </div>
  );
};

export const App: React.FC = () => {
  return (
    <ThemeProvider>
      <AuthProvider>
        <BrowserRouter>
          <Routes>
            <Route path="/login" element={<Login />} />
            <Route 
              path="/*" 
              element={
                <AuthGuard>
                  <ErrorBoundary>
                    <AppLayout />
                  </ErrorBoundary>
                </AuthGuard>
              } 
            />
          </Routes>
        </BrowserRouter>
      </AuthProvider>
    </ThemeProvider>
  );
};

export default App;



