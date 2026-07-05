import React, { useEffect, useState } from 'react';
import { useAuth } from '../contexts/AuthContext';
import api from '../services/api';
import { 
  Users, 
  Calendar, 
  TrendingUp, 
  Bed, 
  AlertTriangle, 
  Activity, 
  FileText, 
  CheckCircle,
  Package,
  TrendingDown,
  DollarSign
} from 'lucide-react';
import { 
  AreaChart, 
  Area, 
  XAxis, 
  YAxis, 
  CartesianGrid, 
  Tooltip, 
  ResponsiveContainer,
  BarChart,
  Bar,
  Cell,
  PieChart,
  Pie
} from 'recharts';

export const Dashboard: React.FC = () => {
  const { user } = useAuth();
  const [data, setData] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [revenuePeriod, setRevenuePeriod] = useState<'3M' | '6M' | '12M' | 'All'>('6M');

  useEffect(() => {
    const fetchDashboardData = async () => {
      try {
        const res = await api.get('/dashboard');
        setData(res.data);
      } catch (err: any) {
        setError('Failed to load dashboard metrics.');
      } finally {
        setLoading(false);
      }
    };
    fetchDashboardData();
  }, []);

  if (loading) {
    return (
      <div className="flex items-center justify-center min-h-[500px]">
        <div className="w-8 h-8 border-4 border-primary border-t-transparent rounded-full animate-spin"></div>
      </div>
    );
  }

  if (error || !data) {
    return (
      <div className="p-6 bg-red-500/10 border border-red-500/20 text-red-500 rounded-2xl">
        {error || 'An error occurred.'}
      </div>
    );
  }

  const role = data.role;
  const metrics = data.metrics;

  // ── Build real revenue trend from backend data ─────────────────────────
  const buildRevenueTrend = () => {
    const raw: Array<{ amount: string; month: string }> = data?.revenue_trend || [];
    const MONTH_LABELS: Record<string, string> = {
      '01': 'Jan', '02': 'Feb', '03': 'Mar', '04': 'Apr',
      '05': 'May', '06': 'Jun', '07': 'Jul', '08': 'Aug',
      '09': 'Sep', '10': 'Oct', '11': 'Nov', '12': 'Dec',
    };
    const mapped = raw.map(r => {
      const [year, mon] = r.month.split('-');
      return {
        name: `${MONTH_LABELS[mon] ?? mon} '${year.slice(2)}`,
        amount: parseFloat(r.amount) || 0,
        rawMonth: r.month,
      };
    });

    // Apply period filter
    if (revenuePeriod === 'All') return mapped;
    const cutoff = revenuePeriod === '3M' ? 3 : revenuePeriod === '6M' ? 6 : 12;
    return mapped.slice(-cutoff);
  };

  const revenueTrendData = buildRevenueTrend();
  const revenueTotalShown = revenueTrendData.reduce((s, d) => s + d.amount, 0);
  const revenueGrowth = revenueTrendData.length >= 2
    ? (((revenueTrendData[revenueTrendData.length - 1].amount - revenueTrendData[0].amount)
        / (revenueTrendData[0].amount || 1)) * 100).toFixed(1)
    : null;
  const revenueHighest = revenueTrendData.reduce(
    (best, d) => d.amount > best.amount ? d : best,
    { name: '—', amount: 0 }
  );

  // Custom tooltip renderer for ₦ currency
  const RevenueTooltip = ({ active, payload, label }: any) => {
    if (active && payload && payload.length) {
      return (
        <div className="bg-white border border-slate-200 rounded-xl shadow-lg px-4 py-3">
          <p className="text-xs font-bold text-slate-800 mb-1">{label}</p>
          <p className="text-sm font-extrabold text-[#008000]">
            ₦{payload[0].value.toLocaleString()}
          </p>
        </div>
      );
    }
    return null;
  };

  // Y-axis formatter — abbreviate large numbers
  const formatYAxis = (v: number) => {
    if (v >= 1_000_000) return `₦${(v / 1_000_000).toFixed(1)}M`;
    if (v >= 1_000) return `₦${(v / 1_000).toFixed(0)}K`;
    return `₦${v}`;
  };

  return (
    <div className="space-y-6">
      {/* Welcome Banner */}
      <div className="bg-gradient-to-r from-[#008000] via-[#006B3F] to-[#004D2D] p-6 rounded-2xl text-white shadow-xl relative overflow-hidden border border-emerald-600/30">
        <div className="relative z-10 space-y-1">
          <h1 className="text-2xl font-bold tracking-tight">Nigeria Immigration Service Medical Services</h1>
          <p className="text-slate-300 text-sm">
            Welcome back, <span className="font-semibold text-white">{user?.name}</span>. You are logged into the NIS Hospital Management System.
          </p>
          <div className="inline-block mt-3 px-3 py-1 bg-white/10 rounded-full text-xs font-semibold uppercase tracking-wide border border-white/20">
            {user?.roles?.[0]?.display_name || 'Staff'} Portal
          </div>
        </div>
        <div className="absolute right-0 bottom-0 opacity-10 translate-y-1/4 translate-x-1/4">
          <Users size={200} />
        </div>
      </div>

      {/* ==================================================== */}
      {/* 1. EXECUTIVE / ADMIN PORTAL VIEW */}
      {/* ==================================================== */}
      {['super_admin', 'medical_director', 'hospital_admin', 'chief_medical_officer'].includes(role) && (
        <>
          {/* Executive Metrics Cards */}
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div className="bg-white p-6 rounded-2xl border border-slate-200 flex items-center justify-between shadow-sm">
              <div className="space-y-1">
                <span className="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Total Patients</span>
                <p className="text-2xl font-extrabold text-slate-900">{metrics.total_patients}</p>
                <div className="text-[10px] text-slate-800 dark:text-slate-200 font-medium">
                  {metrics.total_male_patients} Male / {metrics.total_female_patients} Female
                </div>
              </div>
              <div className="p-3 rounded-full bg-emerald-500/10 text-emerald-600 border border-emerald-500/20">
                <Users size={24} />
              </div>
            </div>

            <div className="bg-white p-6 rounded-2xl border border-slate-200 flex items-center justify-between shadow-sm">
              <div className="space-y-1">
                <span className="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Staff Onboarded</span>
                <p className="text-2xl font-extrabold text-slate-900">{metrics.total_staff_onboarded}</p>
                <div className="text-[10px] text-emerald-600 font-medium">Active roster schedules</div>
              </div>
              <div className="p-3 rounded-full bg-blue-500/10 text-blue-600 border border-blue-500/20">
                <Activity size={24} />
              </div>
            </div>

            <div className="bg-white p-6 rounded-2xl border border-slate-200 flex items-center justify-between shadow-sm">
              <div className="space-y-1">
                <span className="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Today's Revenue</span>
                <p className="text-2xl font-extrabold text-slate-900">₦{metrics.today_revenue.toLocaleString()}</p>
                <div className="text-[10px] text-slate-800 dark:text-slate-200 font-medium">Total: ₦{metrics.total_revenue.toLocaleString()}</div>
              </div>
              <div className="p-3 rounded-full bg-amber-500/10 text-amber-600 border border-amber-500/20">
                <DollarSign size={24} />
              </div>
            </div>

            <div className="bg-white p-6 rounded-2xl border border-slate-200 flex items-center justify-between shadow-sm">
              <div className="space-y-1">
                <span className="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Bed Occupancy Rate</span>
                <p className="text-2xl font-extrabold text-slate-900">{metrics.bed_occupancy_rate}%</p>
                <div className="text-[10px] text-slate-800 dark:text-slate-200 font-medium">{metrics.active_admissions} active admissions</div>
              </div>
              <div className="p-3 rounded-full bg-indigo-500/10 text-indigo-600 border border-indigo-500/20">
                <Bed size={24} />
              </div>
            </div>
          </div>

          {/* Executive Analytics Charts */}
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {/* 1. Monthly Financial Revenue Chart */}
            <div className="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-200">
              {/* Header row */}
              <div className="flex items-center justify-between mb-4">
                <div>
                  <h3 className="text-sm font-bold text-slate-800">Monthly Financial Revenue</h3>
                  <p className="text-[10px] text-slate-500 mt-0.5">Live from billing payments database</p>
                </div>
                {/* Period filter */}
                <div className="flex items-center gap-1 bg-slate-100 rounded-lg p-0.5">
                  {(['3M', '6M', '12M', 'All'] as const).map(p => (
                    <button
                      key={p}
                      onClick={() => setRevenuePeriod(p)}
                      className={`px-2.5 py-1 text-[10px] font-bold rounded-md transition-colors cursor-pointer ${
                        revenuePeriod === p
                          ? 'bg-white text-[#008000] shadow-sm'
                          : 'text-slate-500 hover:text-slate-700'
                      }`}
                    >
                      {p}
                    </button>
                  ))}
                </div>
              </div>

              {/* Chart */}
              {revenueTrendData.length === 0 ? (
                <div className="h-64 flex flex-col items-center justify-center text-slate-400 text-sm gap-2">
                  <DollarSign size={32} className="opacity-30" />
                  <p>No payment records found for this period.</p>
                </div>
              ) : (
                <div className="h-64">
                  <ResponsiveContainer width="100%" height="100%">
                    <AreaChart data={revenueTrendData} margin={{ top: 4, right: 4, left: 8, bottom: 0 }}>
                      <defs>
                        <linearGradient id="colorRevenue" x1="0" y1="0" x2="0" y2="1">
                          <stop offset="5%" stopColor="#008000" stopOpacity={0.25}/>
                          <stop offset="95%" stopColor="#008000" stopOpacity={0}/>
                        </linearGradient>
                      </defs>
                      <CartesianGrid strokeDasharray="3 3" stroke="#f1f5f9" />
                      <XAxis dataKey="name" stroke="#94a3b8" fontSize={10} tickLine={false} axisLine={false} />
                      <YAxis stroke="#94a3b8" fontSize={10} tickLine={false} axisLine={false} tickFormatter={formatYAxis} width={72} />
                      <Tooltip content={<RevenueTooltip />} />
                      <Area
                        type="monotone"
                        dataKey="amount"
                        stroke="#008000"
                        strokeWidth={2.5}
                        fillOpacity={1}
                        fill="url(#colorRevenue)"
                        dot={{ fill: '#008000', r: 3, strokeWidth: 0 }}
                        activeDot={{ r: 5, fill: '#008000' }}
                      />
                    </AreaChart>
                  </ResponsiveContainer>
                </div>
              )}

              {/* Summary footer */}
              <div className="mt-4 pt-4 border-t border-slate-100 grid grid-cols-3 gap-3">
                <div>
                  <p className="text-[9px] font-bold text-slate-400 uppercase tracking-wide">Period Total</p>
                  <p className="text-sm font-extrabold text-slate-900">₦{revenueTotalShown.toLocaleString()}</p>
                </div>
                <div>
                  <p className="text-[9px] font-bold text-slate-400 uppercase tracking-wide">Peak Month</p>
                  <p className="text-sm font-extrabold text-slate-900">{revenueHighest.name}</p>
                  <p className="text-[9px] text-slate-500">₦{revenueHighest.amount.toLocaleString()}</p>
                </div>
                <div>
                  <p className="text-[9px] font-bold text-slate-400 uppercase tracking-wide">Period Growth</p>
                  {revenueGrowth !== null ? (
                    <p className={`text-sm font-extrabold ${
                      parseFloat(revenueGrowth) >= 0 ? 'text-[#008000]' : 'text-red-500'
                    }`}>
                      {parseFloat(revenueGrowth) >= 0 ? '+' : ''}{revenueGrowth}%
                    </p>
                  ) : (
                    <p className="text-sm font-extrabold text-slate-400">—</p>
                  )}
                </div>
              </div>
            </div>

            {/* 2. Medicine alerts */}
            <div className="bg-white p-6 rounded-2xl border border-slate-200 flex flex-col justify-between">
              <div>
                <h3 className="text-sm font-bold text-slate-800 mb-4">Medicine Stock Alerts</h3>
                <div className="space-y-4">
                  <div className="flex items-center justify-between p-3 bg-red-500/10 border border-red-500/20 rounded-xl">
                    <div className="flex items-center gap-2">
                      <AlertTriangle className="text-red-600" size={18} />
                      <span className="text-xs text-red-600 font-bold">Critical Out of Stock</span>
                    </div>
                    <span className="text-xs font-bold text-slate-800">{metrics.critical_stock_alerts} items</span>
                  </div>

                  <div className="flex items-center justify-between p-3 bg-amber-500/10 border border-amber-500/20 rounded-xl">
                    <div className="flex items-center gap-2">
                      <Package className="text-amber-600" size={18} />
                      <span className="text-xs text-amber-500 font-bold">Total Stock Quantity</span>
                    </div>
                    <span className="text-xs font-bold text-slate-800">{metrics.medicine_stock} Units</span>
                  </div>
                </div>
              </div>

              <div className="border-t border-slate-100 pt-4 mt-4 flex items-center justify-between text-xs text-slate-800 dark:text-slate-200">
                <span>Database health:</span>
                <span className="font-bold text-emerald-600">Perfect 3NF Status</span>
              </div>
            </div>
          </div>

          {/* 3. Superadmin Visualizations Grid (Pie & Bar charts) */}
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {/* Pie Chart: Patient Gender Breakdown */}
            <div className="bg-white p-6 rounded-2xl border border-slate-200">
              <h3 className="text-sm font-bold text-slate-800 mb-4 font-sans">Patient Gender Demographics</h3>
              <div className="h-64 flex flex-col items-center justify-center">
                <ResponsiveContainer width="100%" height={200}>
                  <PieChart>
                    <Pie
                      data={[
                        { name: 'Male Patients', value: metrics.total_male_patients || 1 },
                        { name: 'Female Patients', value: metrics.total_female_patients || 1 }
                      ]}
                      cx="50%"
                      cy="50%"
                      innerRadius={60}
                      outerRadius={80}
                      paddingAngle={5}
                      dataKey="value"
                    >
                      <Cell fill="#008000" />
                      <Cell fill="#10B981" />
                    </Pie>
                    <Tooltip />
                  </PieChart>
                </ResponsiveContainer>
                <div className="flex justify-center gap-6 mt-2 text-xs">
                  <div className="flex items-center gap-1.5">
                    <span className="w-3 h-3 rounded-full bg-[#008000]"></span>
                    <span className="text-slate-600 font-medium">Male ({metrics.total_male_patients})</span>
                  </div>
                  <div className="flex items-center gap-1.5">
                    <span className="w-3 h-3 rounded-full bg-[#10B981]"></span>
                    <span className="text-slate-600 font-medium">Female ({metrics.total_female_patients})</span>
                  </div>
                </div>
              </div>
            </div>

            {/* Bar Chart: Comparisons counts */}
            <div className="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-200">
              <h3 className="text-sm font-bold text-slate-800 mb-4">Operations Metrics Comparison</h3>
              <div className="h-64">
                <ResponsiveContainer width="100%" height="100%">
                  <BarChart data={[
                    { name: 'Patients', count: metrics.total_patients || 0, fill: '#008000' },
                    { name: 'Staff Onboard', count: metrics.total_staff_onboarded || 0, fill: '#10B981' },
                    { name: 'Admissions', count: metrics.active_admissions || 0, fill: '#3B82F6' },
                    { name: 'Appointments', count: metrics.today_appointments || 0, fill: '#F59E0B' }
                  ]}>
                    <CartesianGrid strokeDasharray="3 3" stroke="#e2e8f0" />
                    <XAxis dataKey="name" stroke="#000000" fontSize={11} />
                    <YAxis stroke="#000000" fontSize={11} />
                    <Tooltip />
                    <Bar dataKey="count" radius={[6, 6, 0, 0]}>
                      <Cell fill="#008000" />
                      <Cell fill="#10B981" />
                      <Cell fill="#3B82F6" />
                      <Cell fill="#F59E0B" />
                    </Bar>
                  </BarChart>
                </ResponsiveContainer>
              </div>
            </div>
          </div>
        </>
      )}

      {/* ==================================================== */}
      {/* 2. DOCTOR / CLINICAL PORTAL VIEW */}
      {/* ==================================================== */}
      {['doctor', 'consultant'].includes(role) && (
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          {/* Doctor Stats & Consult queue */}
          <div className="lg:col-span-2 space-y-6">
            {/* Quick Stats */}
            <div className="grid grid-cols-1 sm:grid-cols-3 gap-6">
              <div className="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div className="space-y-1">
                  <span className="text-xs font-bold text-slate-800 dark:text-slate-200">Consulted Today</span>
                  <p className="text-2xl font-extrabold text-slate-900">{metrics.consulted_today}</p>
                </div>
                <div className="p-3 rounded-full bg-[#008000]/10 text-[#008000]">
                  <CheckCircle size={20} />
                </div>
              </div>

              <div className="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div className="space-y-1">
                  <span className="text-xs font-bold text-slate-800 dark:text-slate-200">Total Queue List</span>
                  <p className="text-2xl font-extrabold text-slate-900">{metrics.my_appointments_today}</p>
                </div>
                <div className="p-3 rounded-full bg-blue-500/10 text-blue-600">
                  <Activity size={20} />
                </div>
              </div>

              <div className="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div className="space-y-1">
                  <span className="text-xs font-bold text-slate-800 dark:text-slate-200">Active Admissions</span>
                  <p className="text-2xl font-extrabold text-slate-900">{metrics.active_admissions}</p>
                </div>
                <div className="p-3 rounded-full bg-indigo-500/10 text-indigo-600">
                  <Bed size={20} />
                </div>
              </div>
            </div>

            {/* Waiting Queue List */}
            <div className="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
              <div className="px-6 py-4 border-b border-slate-100 bg-slate-50">
                <h3 className="text-sm font-bold text-slate-800">Active Patient Consult Queue</h3>
              </div>
              <div className="divide-y divide-slate-100">
                {data.queue && data.queue.length > 0 ? (
                  data.queue.map((apt: any) => (
                    <div key={apt.id} className="p-6 flex items-center justify-between hover:bg-slate-50">
                      <div className="flex items-center gap-3">
                        <div className="w-10 h-10 rounded-full bg-primary/10 text-primary font-bold flex items-center justify-center">
                          {apt.queue_number}
                        </div>
                        <div>
                          <h4 className="text-sm font-bold text-slate-800">
                            {apt.patient.first_name} {apt.patient.last_name}
                          </h4>
                          <span className="text-[10px] text-slate-800 dark:text-slate-200">{apt.patient.immigration_service_number || 'Civilian'}</span>
                        </div>
                      </div>
                      <div className="flex items-center gap-2">
                        <span className={`px-2.5 py-1 text-[10px] font-bold rounded-full border ${
                          apt.status === 'checked_in' 
                            ? 'bg-[#008000]/10 text-[#008000] border-[#008000]/20' 
                            : 'bg-amber-500/10 text-amber-600 border-amber-500/20'
                        }`}>
                          {apt.status === 'checked_in' ? 'Vitals Captured' : 'Waiting Vitals'}
                        </span>
                      </div>
                    </div>
                  ))
                ) : (
                  <div className="p-8 text-center text-slate-800 dark:text-slate-200 text-sm">
                    No active patients in the queue for today.
                  </div>
                )}
              </div>
            </div>
          </div>

          {/* Quick Actions Panel */}
          <div className="bg-white p-6 rounded-2xl border border-slate-200 flex flex-col justify-between shadow-sm">
            <div>
              <h3 className="text-sm font-bold text-slate-800 mb-4">Quick Doctor Actions</h3>
              <div className="space-y-3">
                <button className="w-full text-left p-4 bg-primary/10 border border-primary/20 text-primary hover:bg-primary/20 rounded-xl transition-all font-semibold text-xs flex items-center gap-3 cursor-pointer">
                  <FileText size={16} /> Write Consultation SOAP Notes
                </button>
                <button className="w-full text-left p-4 bg-slate-50 border border-slate-200 text-slate-700 hover:bg-slate-100 rounded-xl transition-all font-semibold text-xs flex items-center gap-3 cursor-pointer">
                  <Calendar size={16} /> View Roster & Schedules
                </button>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* ==================================================== */}
      {/* 3. NURSE PORTAL VIEW */}
      {/* ==================================================== */}
      {role === 'nurse' && (
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div className="lg:col-span-2 space-y-6">
            <div className="grid grid-cols-1 sm:grid-cols-3 gap-6">
              <div className="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div className="space-y-1">
                  <span className="text-xs font-bold text-slate-800 dark:text-slate-200">Active Admissions</span>
                  <p className="text-2xl font-extrabold text-slate-900">{metrics.active_admissions}</p>
                </div>
                <div className="p-3 rounded-full bg-indigo-500/10 text-indigo-600">
                  <Bed size={20} />
                </div>
              </div>

              <div className="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div className="space-y-1">
                  <span className="text-xs font-bold text-slate-800 dark:text-slate-200">Occupied Beds</span>
                  <p className="text-2xl font-extrabold text-slate-900">{metrics.occupied_beds}</p>
                </div>
                <div className="p-3 rounded-full bg-red-500/10 text-red-600">
                  <TrendingUp size={20} />
                </div>
              </div>

              <div className="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div className="space-y-1">
                  <span className="text-xs font-bold text-slate-800 dark:text-slate-200">Available Beds</span>
                  <p className="text-2xl font-extrabold text-slate-900">{metrics.available_beds}</p>
                </div>
                <div className="p-3 rounded-full bg-[#008000]/10 text-[#008000]">
                  <CheckCircle size={20} />
                </div>
              </div>
            </div>

            {/* Waiting for Vitals checklist */}
            <div className="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
              <div className="px-6 py-4 border-b border-slate-100 bg-slate-50">
                <h3 className="text-sm font-bold text-slate-800">Patients Awaiting Vital Signs Capture</h3>
              </div>
              <div className="divide-y divide-slate-100">
                {data.recent_visits_for_vitals && data.recent_visits_for_vitals.length > 0 ? (
                  data.recent_visits_for_vitals.map((v: any) => (
                    <div key={v.id} className="p-6 flex items-center justify-between hover:bg-slate-50">
                      <div>
                        <h4 className="text-sm font-bold text-slate-800">
                          {v.patient.first_name} {v.patient.last_name}
                        </h4>
                        <span className="text-[10px] text-slate-800 dark:text-slate-200">{v.patient.immigration_service_number || 'Civilian'}</span>
                      </div>
                      <button className="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-xl text-xs font-semibold transition-all shadow-md shadow-primary/10 cursor-pointer">
                        Record Vitals
                      </button>
                    </div>
                  ))
                ) : (
                  <div className="p-8 text-center text-slate-800 dark:text-slate-200 text-sm">
                    No pending patients requiring vitals entry.
                  </div>
                )}
              </div>
            </div>
          </div>

          <div className="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
            <h3 className="text-sm font-bold text-slate-800 mb-4">Shift Quick Tasks</h3>
            <div className="space-y-3">
              <div className="p-4 bg-slate-50 border border-slate-200 rounded-xl flex items-center gap-3">
                <span className="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                <span className="text-xs text-slate-700 font-semibold">Perform Ward-C Roster clean check</span>
              </div>
              <div className="p-4 bg-slate-50 border border-slate-200 rounded-xl flex items-center gap-3">
                <span className="w-2.5 h-2.5 rounded-full bg-[#008000]"></span>
                <span className="text-xs text-slate-700 font-semibold">Medication round for Ward-A Completed</span>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* ==================================================== */}
      {/* 4. PHARMACIST & INVENTORY PORTAL VIEW */}
      {/* ==================================================== */}
      {['pharmacist', 'inventory_officer', 'store_officer', 'procurement_officer'].includes(role) && (
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div className="lg:col-span-2 space-y-6">
            <div className="grid grid-cols-1 sm:grid-cols-3 gap-6">
              <div className="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div className="space-y-1">
                  <span className="text-xs font-bold text-slate-800 dark:text-slate-200">Total Medicine Items</span>
                  <p className="text-2xl font-extrabold text-slate-900">{metrics.total_medicine_items}</p>
                </div>
                <div className="p-3 rounded-full bg-[#008000]/10 text-[#008000]">
                  <Package size={20} />
                </div>
              </div>

              <div className="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div className="space-y-1">
                  <span className="text-xs font-bold text-slate-800 dark:text-slate-200">Expired Drugs</span>
                  <p className="text-2xl font-extrabold text-slate-900">{metrics.expired_drugs}</p>
                </div>
                <div className="p-3 rounded-full bg-red-500/10 text-red-600">
                  <AlertTriangle size={20} />
                </div>
              </div>

              <div className="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div className="space-y-1">
                  <span className="text-xs font-bold text-slate-800 dark:text-slate-200">Low Stock Warning</span>
                  <p className="text-2xl font-extrabold text-slate-900">{metrics.low_stock_count}</p>
                </div>
                <div className="p-3 rounded-full bg-amber-500/10 text-amber-600">
                  <TrendingDown size={20} />
                </div>
              </div>
            </div>

            {/* Low stock checklist table */}
            <div className="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
              <div className="px-6 py-4 border-b border-slate-100 bg-slate-50">
                <h3 className="text-sm font-bold text-slate-800">Critical Low Stock Medication Items</h3>
              </div>
              <div className="p-4">
                <div className="overflow-x-auto">
                  <table className="w-full text-left text-xs">
                    <thead>
                      <tr className="border-b border-slate-100 text-slate-800 dark:text-slate-200">
                        <th className="py-3 px-4 font-bold">Item Name</th>
                        <th className="py-3 px-4 font-bold">Code</th>
                        <th className="py-3 px-4 font-bold">Qty Remaining</th>
                        <th className="py-3 px-4 font-bold">Reorder Level</th>
                      </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100 text-slate-700">
                      {data.low_stock_list && data.low_stock_list.length > 0 ? (
                        data.low_stock_list.map((item: any) => (
                          <tr key={item.id} className="hover:bg-slate-50">
                            <td className="py-3 px-4 font-semibold">{item.name}</td>
                            <td className="py-3 px-4">{item.code}</td>
                            <td className="py-3 px-4 text-red-500 font-bold">{item.quantity_in_stock}</td>
                            <td className="py-3 px-4 font-bold">{item.reorder_level}</td>
                          </tr>
                        ))
                      ) : (
                        <tr>
                          <td colSpan={4} className="py-4 text-center text-slate-800 dark:text-slate-200">No items are currently running low.</td>
                        </tr>
                      )}
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

          <div className="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
            <h3 className="text-sm font-bold text-slate-800 mb-4">Dispenser Quick Tools</h3>
            <div className="space-y-3">
              <button className="w-full text-left p-4 bg-primary/10 border border-primary/20 text-primary hover:bg-primary/20 rounded-xl transition-all font-semibold text-xs flex items-center gap-3 cursor-pointer">
                <FileText size={16} /> Validate Electronic Prescriptions
              </button>
            </div>
          </div>
        </div>
      )}

      {/* ==================================================== */}
      {/* 5. CASHIER / BILLING PORTAL VIEW */}
      {/* ==================================================== */}
      {role === 'cashier' && (
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div className="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div className="space-y-1">
              <span className="text-xs font-bold text-slate-800 dark:text-slate-200">Revenue Gathered Today</span>
              <p className="text-2xl font-extrabold text-slate-900">₦{metrics.today_revenue.toLocaleString()}</p>
            </div>
            <div className="p-3 rounded-full bg-[#008000]/10 text-[#008000]">
              <DollarSign size={20} />
            </div>
          </div>

          <div className="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div className="space-y-1">
              <span className="text-xs font-bold text-slate-800 dark:text-slate-200">Pending Invoices</span>
              <p className="text-2xl font-extrabold text-slate-900">{metrics.pending_invoices}</p>
            </div>
            <div className="p-3 rounded-full bg-amber-500/10 text-amber-600">
              <FileText size={20} />
            </div>
          </div>

          <div className="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div className="space-y-1">
              <span className="text-xs font-bold text-slate-800 dark:text-slate-200">Total Registered Patients</span>
              <p className="text-2xl font-extrabold text-slate-900">{metrics.total_patients}</p>
            </div>
            <div className="p-3 rounded-full bg-blue-500/10 text-blue-600">
              <Users size={20} />
            </div>
          </div>
        </div>
      )}
      </div>
  );
};




