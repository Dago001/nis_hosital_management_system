import React, { useState } from 'react';
import { Settings as SettingsIcon, CheckCircle } from 'lucide-react';

export const Settings: React.FC = () => {
  const [hospitalName, setHospitalName] = useState('NIS Medical Clinic HQ');
  const [branch, setBranch] = useState('Central command, Abuja');
  const [success, setSuccess] = useState<string | null>(null);

  const handleSave = (e: React.FormEvent) => {
    e.preventDefault();
    setSuccess('Settings updated successfully in cached config!');
    setTimeout(() => setSuccess(null), 1500);
  };

  return (
    <div className="space-y-6 max-w-2xl mx-auto">
      <div>
        <h1 className="text-xl font-bold text-slate-800 dark:text-white flex items-center gap-2">
          <SettingsIcon className="text-primary" /> Hospital Administration & Settings
        </h1>
        <p className="text-xs text-slate-800 dark:text-slate-200">Configure clinic metadata variables, branches, parameters, and branding assets</p>
      </div>

      <div className="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
        {success && (
          <div className="mb-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-500 p-3 rounded-xl text-xs flex items-center gap-2">
            <CheckCircle size={16} /> {success}
          </div>
        )}

        <form onSubmit={handleSave} className="space-y-6">
          <div className="space-y-4">
            <h3 className="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Demographic branding</h3>
            <div className="grid grid-cols-1 gap-4">
              <div>
                <label className="text-[10px] font-bold text-slate-800 dark:text-slate-200">Hospital Facility Name</label>
                <input
                  type="text"
                  value={hospitalName}
                  onChange={(e) => setHospitalName(e.target.value)}
                  className="w-full mt-1.5 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 rounded-xl text-xs text-slate-800 dark:text-slate-100"
                />
              </div>

              <div>
                <label className="text-[10px] font-bold text-slate-800 dark:text-slate-200">Active Branch Division</label>
                <input
                  type="text"
                  value={branch}
                  onChange={(e) => setBranch(e.target.value)}
                  className="w-full mt-1.5 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 rounded-xl text-xs text-slate-800 dark:text-slate-100"
                />
              </div>
            </div>
          </div>

          <div className="flex justify-end pt-4 border-t border-slate-100 dark:border-slate-800">
            <button
              type="submit"
              className="bg-primary hover:bg-primary-dark text-white px-5 py-2.5 rounded-xl text-xs font-semibold shadow-lg shadow-primary/10 transition-all cursor-pointer"
            >
              Save Configuration
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};



