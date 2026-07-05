import React, { useEffect, useState } from 'react';
import api from '../services/api';
import { Activity, ShieldAlert, CheckCircle, Heart, Thermometer } from 'lucide-react';

export const Vitals: React.FC = () => {
  const [patients, setPatients] = useState<any[]>([]);
  const [patientId, setPatientId] = useState('');
  
  // Vitals State
  const [bp, setBp] = useState('');
  const [temp, setTemp] = useState('');
  const [pulse, setPulse] = useState('');
  const [resp, setResp] = useState('');
  const [weight, setWeight] = useState('');
  const [height, setHeight] = useState('');

  const [departments, setDepartments] = useState<any[]>([]);
  const [departmentId, setDepartmentId] = useState('');
  
  const [success, setSuccess] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    const fetchSetup = async () => {
      try {
        const patRes = await api.get('/patients');
        setPatients(patRes.data.patients);
        
        // Setup simple outpatient department mock
        setDepartments([
          { id: 1, name: 'General Outpatient (GOPD)' },
          { id: 2, name: 'Accident & Emergency (ER)' }
        ]);
        setDepartmentId('1');
      } catch (err) {
        console.error(err);
      }
    };
    fetchSetup();
  }, []);

  const handleSaveVitals = async (e: React.FormEvent) => {
    e.preventDefault();
    setSuccess(null);
    setError(null);
    setLoading(true);

    const payload = {
      patient_id: patientId,
      department_id: departmentId,
      vitals_blood_pressure: bp,
      vitals_temperature: parseFloat(temp),
      vitals_pulse_rate: parseInt(pulse),
      vitals_respiratory_rate: resp ? parseInt(resp) : null,
      vitals_weight: weight ? parseFloat(weight) : null,
      vitals_height: height ? parseFloat(height) : null,
    };

    try {
      await api.post('/clinical/vitals', payload);
      setSuccess('Patient vital signs saved and queued for consultation!');
      
      // Reset form
      setPatientId('');
      setBp('');
      setTemp('');
      setPulse('');
      setResp('');
      setWeight('');
      setHeight('');
    } catch (err: any) {
      setError(err.response?.data?.message || 'Failed to save vital signs. Check input variables.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="space-y-6 max-w-2xl mx-auto">
      <div>
        <h1 className="text-xl font-bold text-slate-800 dark:text-white flex items-center gap-2">
          <Activity className="text-primary" /> Vital Signs Monitoring Log
        </h1>
        <p className="text-xs text-slate-800 dark:text-slate-200">Nurse portal to capture primary triage variables and forward files to doctor queues</p>
      </div>

      <div className="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
        {success && (
          <div className="mb-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-500 p-3 rounded-xl text-xs flex items-center gap-2">
            <CheckCircle size={16} /> {success}
          </div>
        )}
        {error && (
          <div className="mb-4 bg-red-500/10 border border-red-500/20 text-red-500 p-3 rounded-xl text-xs flex items-center gap-2">
            <ShieldAlert size={16} /> {error}
          </div>
        )}

        <form onSubmit={handleSaveVitals} className="space-y-6">
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label className="text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Patient *</label>
              <select
                required
                value={patientId}
                onChange={(e) => setPatientId(e.target.value)}
                className="w-full mt-1.5 px-3 py-2.5 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 rounded-xl text-xs text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-primary focus:outline-none"
              >
                <option value="">Select Patient...</option>
                {patients.map(p => (
                  <option key={p.id} value={p.id}>{p.first_name} {p.last_name} ({p.immigration_service_number || 'Civilian'})</option>
                ))}
              </select>
            </div>

            <div>
              <label className="text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Encounter Clinic Unit *</label>
              <select
                required
                value={departmentId}
                onChange={(e) => setDepartmentId(e.target.value)}
                className="w-full mt-1.5 px-3 py-2.5 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 rounded-xl text-xs text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-primary focus:outline-none"
              >
                {departments.map(d => (
                  <option key={d.id} value={d.id}>{d.name}</option>
                ))}
              </select>
            </div>
          </div>

          <div className="border-t border-slate-100 dark:border-slate-800 pt-4 space-y-4">
            <h3 className="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Triage Measurements</h3>
            
            <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <div>
                <label className="text-[10px] font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1">
                  <Heart size={12} className="text-red-500" /> Blood Pressure (mmHg) *
                </label>
                <input
                  type="text"
                  required
                  placeholder="e.g. 120/80"
                  value={bp}
                  onChange={(e) => setBp(e.target.value)}
                  className="w-full mt-1 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 rounded-xl text-xs text-slate-800 dark:text-slate-100"
                />
              </div>

              <div>
                <label className="text-[10px] font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1">
                  <Thermometer size={12} className="text-blue-500" /> Temperature (°C) *
                </label>
                <input
                  type="number"
                  step="0.1"
                  required
                  placeholder="e.g. 36.8"
                  value={temp}
                  onChange={(e) => setTemp(e.target.value)}
                  className="w-full mt-1 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 rounded-xl text-xs text-slate-800 dark:text-slate-100"
                />
              </div>

              <div>
                <label className="text-[10px] font-bold text-slate-800 dark:text-slate-200">Pulse Rate (bpm) *</label>
                <input
                  type="number"
                  required
                  placeholder="e.g. 72"
                  value={pulse}
                  onChange={(e) => setPulse(e.target.value)}
                  className="w-full mt-1 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 rounded-xl text-xs text-slate-800 dark:text-slate-100"
                />
              </div>

              <div>
                <label className="text-[10px] font-bold text-slate-800 dark:text-slate-200">Respiratory Rate (cpm)</label>
                <input
                  type="number"
                  placeholder="e.g. 18"
                  value={resp}
                  onChange={(e) => setResp(e.target.value)}
                  className="w-full mt-1 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 rounded-xl text-xs text-slate-800 dark:text-slate-100"
                />
              </div>

              <div>
                <label className="text-[10px] font-bold text-slate-800 dark:text-slate-200">Weight (kg)</label>
                <input
                  type="number"
                  step="0.1"
                  placeholder="e.g. 75.4"
                  value={weight}
                  onChange={(e) => setWeight(e.target.value)}
                  className="w-full mt-1 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 rounded-xl text-xs text-slate-800 dark:text-slate-100"
                />
              </div>

              <div>
                <label className="text-[10px] font-bold text-slate-800 dark:text-slate-200">Height (cm)</label>
                <input
                  type="number"
                  placeholder="e.g. 175"
                  value={height}
                  onChange={(e) => setHeight(e.target.value)}
                  className="w-full mt-1 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 rounded-xl text-xs text-slate-800 dark:text-slate-100"
                />
              </div>
            </div>
          </div>

          <div className="flex justify-end pt-4 border-t border-slate-100 dark:border-slate-800">
            <button
              type="submit"
              disabled={loading}
              className="bg-primary hover:bg-primary-dark text-white px-5 py-2.5 rounded-xl text-xs font-semibold shadow-lg shadow-primary/10 transition-all cursor-pointer disabled:opacity-50"
            >
              {loading ? 'Saving...' : 'Record Vital Signs'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};



