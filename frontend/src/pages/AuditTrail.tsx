import React, { useEffect, useState } from 'react';
import api from '../services/api';
import { ClipboardList, Search, ShieldCheck, User } from 'lucide-react';

export const AuditTrail: React.FC = () => {
  const [logs, setLogs] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [actionFilter, setActionFilter] = useState('');

  const fetchLogs = async () => {
    setLoading(true);
    try {
      const res = await api.get(`/admin/audit-logs?action=${actionFilter}`);
      setLogs(res.data.logs.data);
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchLogs();
  }, [actionFilter]);

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-xl font-bold text-slate-800 dark:text-white flex items-center gap-2">
          <ClipboardList className="text-primary" /> System Audit Trail & Logs
        </h1>
        <p className="text-xs text-slate-800 dark:text-slate-200">ICT administrator portal to inspect system activities, logins, database mutations, and transactions integrity</p>
      </div>

      {/* Filters */}
      <div className="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800/80 flex items-center justify-between">
        <div className="relative w-80">
          <input 
            type="text" 
            value={actionFilter}
            onChange={(e) => setActionFilter(e.target.value)}
            placeholder="Filter by action name..." 
            className="w-full pl-9 pr-4 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent text-slate-800 dark:text-slate-100 placeholder-slate-400 transition-all"
          />
          <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-800 dark:text-slate-200" size={14} />
        </div>
        <div className="flex items-center gap-1.5 text-[10px] bg-emerald-500/10 text-emerald-500 font-bold border border-emerald-500/20 px-3 py-1 rounded-full uppercase tracking-wider">
          <ShieldCheck size={12} /> Compliance Integrity Secure
        </div>
      </div>

      {/* Logs Table */}
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
                  <th className="py-3.5 px-6 font-bold">Timestamp</th>
                  <th className="py-3.5 px-6 font-bold">Trigger User</th>
                  <th className="py-3.5 px-6 font-bold">Action Name</th>
                  <th className="py-3.5 px-6 font-bold">IP Address</th>
                  <th className="py-3.5 px-6 font-bold">User Agent</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                {logs.length > 0 ? (
                  logs.map((log) => (
                    <tr key={log.id} className="hover:bg-slate-50 dark:hover:bg-slate-900/20 transition-all">
                      <td className="py-3.5 px-6 font-mono text-[11px] text-slate-800 dark:text-slate-200">
                        {new Date(log.created_at).toLocaleString()}
                      </td>
                      <td className="py-3.5 px-6 font-bold text-slate-800 dark:text-slate-200">
                        <span className="flex items-center gap-1.5">
                          <User size={14} className="text-slate-800 dark:text-slate-200" />
                          {log.user ? log.user.name : 'Guest Session'}
                        </span>
                      </td>
                      <td className="py-3.5 px-6">
                        <span className="px-2 py-0.5 text-[10px] font-mono font-bold bg-primary/10 text-primary border border-primary/20 rounded uppercase">
                          {log.action}
                        </span>
                      </td>
                      <td className="py-3.5 px-6 font-mono text-slate-800 dark:text-slate-200">{log.ip_address}</td>
                      <td className="py-3.5 px-6 text-slate-800 dark:text-slate-200 truncate max-w-xs" title={log.user_agent}>
                        {log.user_agent}
                      </td>
                    </tr>
                  ))
                ) : (
                  <tr>
                    <td colSpan={5} className="py-8 text-center text-slate-800 dark:text-slate-200 text-sm">No activity log entries found.</td>
                  </tr>
                )}
              </tbody>
            </table>
          </div>
        )}
      </div>
    </div>
  );
};



