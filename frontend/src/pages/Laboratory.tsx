import React, { useEffect, useState } from 'react';
import api from '../services/api';
import { useAuth } from '../contexts/AuthContext';
import { TestTube, CheckCircle, ShieldAlert } from 'lucide-react';

export const Laboratory: React.FC = () => {
  const { user } = useAuth();
  const [labRequests, setLabRequests] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [activeRequest, setActiveRequest] = useState<any>(null);
  const [modalOpen, setModalOpen] = useState(false);

  // Result Form fields
  const [val, setVal] = useState('');
  const [minVal, setMinVal] = useState('');
  const [maxVal, setMaxVal] = useState('');
  const [unit, setUnit] = useState('');
  const [remarks, setRemarks] = useState('');
  
  const [success, setSuccess] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);

  const fetchLabQueue = async () => {
    setLoading(true);
    try {
      const res = await api.get('/diagnostics/lab/queue');
      setLabRequests(res.data.lab_requests);
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchLabQueue();
  }, []);

  const handleCollectSample = async (id: number) => {
    try {
      await api.post(`/diagnostics/lab/collect-sample/${id}`);
      fetchLabQueue();
    } catch (err) {
      alert('Error updating status.');
    }
  };

  const handleOpenResultModal = (req: any) => {
    setActiveRequest(req);
    setVal('');
    setMinVal('');
    setMaxVal('');
    setUnit('');
    setRemarks('');
    setModalOpen(true);
  };

  const handleSubmitResult = async (e: React.FormEvent) => {
    e.preventDefault();
    setSuccess(null);
    setError(null);

    const payload = {
      result_value: val,
      normal_range_min: minVal || null,
      normal_range_max: maxVal || null,
      unit: unit || null,
      remarks: remarks || null
    };

    try {
      await api.post(`/diagnostics/lab/submit-result/${activeRequest.id}`, payload);
      setSuccess('Laboratory result submitted successfully as draft!');
      fetchLabQueue();
      
      setTimeout(() => {
        setModalOpen(false);
        setSuccess(null);
      }, 1500);
    } catch (err: any) {
      setError(err.response?.data?.message || 'Failed to submit results.');
    }
  };

  const handleApprove = async (id: number) => {
    try {
      await api.post(`/diagnostics/lab/approve-result/${id}`);
      fetchLabQueue();
    } catch (err) {
      alert('Error approving result.');
    }
  };

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-xl font-bold text-slate-800 dark:text-white flex items-center gap-2">
          <TestTube className="text-primary" /> Pathological Laboratory Queue
        </h1>
        <p className="text-xs text-slate-800 dark:text-slate-200">Laboratory scientist portal to coordinate sample collections, record diagnostics values, and authorize reports</p>
      </div>

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
                  <th className="py-3.5 px-6 font-bold">Request Date</th>
                  <th className="py-3.5 px-6 font-bold">Patient Name</th>
                  <th className="py-3.5 px-6 font-bold">Test Name</th>
                  <th className="py-3.5 px-6 font-bold">Requesting Doctor</th>
                  <th className="py-3.5 px-6 font-bold">Status</th>
                  <th className="py-3.5 px-6 font-bold text-right">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                {labRequests.length > 0 ? (
                  labRequests.map((req) => (
                    <tr key={req.id} className="hover:bg-slate-50 dark:hover:bg-slate-900/20 transition-all">
                      <td className="py-3.5 px-6">{req.requested_at}</td>
                      <td className="py-3.5 px-6 font-bold text-slate-900 dark:text-white">
                        {req.patient.first_name} {req.patient.last_name}
                      </td>
                      <td className="py-3.5 px-6 font-semibold">{req.test_name}</td>
                      <td className="py-3.5 px-6">Dr. {req.doctor?.first_name} {req.doctor?.last_name}</td>
                      <td className="py-3.5 px-6">
                        <span className={`px-2 py-0.5 text-[9px] font-bold rounded-full border ${
                          req.status === 'sample_collected'
                            ? 'bg-blue-500/10 text-blue-500 border-blue-500/20'
                            : req.status === 'completed'
                            ? 'bg-amber-500/10 text-amber-500 border-amber-500/20'
                            : req.status === 'approved'
                            ? 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20'
                            : 'bg-slate-500/10 text-slate-800 dark:text-slate-200 border-slate-500/20'
                        }`}>
                          {req.status}
                        </span>
                      </td>
                      <td className="py-3.5 px-6 text-right space-x-2">
                        {req.status === 'requested' && (
                          <button 
                            onClick={() => handleCollectSample(req.id)}
                            className="text-primary hover:text-emerald-600 font-bold hover:underline cursor-pointer"
                          >
                            Collect Sample
                          </button>
                        )}
                        {req.status === 'sample_collected' && (
                          <button 
                            onClick={() => handleOpenResultModal(req)}
                            className="text-amber-500 hover:text-amber-600 font-bold hover:underline cursor-pointer"
                          >
                            Enter Results
                          </button>
                        )}
                        {req.status === 'completed' && user?.roles?.[0]?.name && ['super_admin', 'lab_scientist', 'medical_director'].includes(user.roles?.[0]?.name) && (
                          <button 
                            onClick={() => handleApprove(req.id)}
                            className="text-emerald-500 hover:text-emerald-600 font-bold hover:underline cursor-pointer"
                          >
                            Approve
                          </button>
                        )}
                      </td>
                    </tr>
                  ))
                ) : (
                  <tr>
                    <td colSpan={6} className="py-8 text-center text-slate-800 dark:text-slate-200 text-sm">No laboratory requests in the active queue.</td>
                  </tr>
                )}
              </tbody>
            </table>
          </div>
        )}
      </div>

      {/* Enter Result Modal */}
      {modalOpen && activeRequest && (
        <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
          <div className="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-md overflow-hidden shadow-2xl animate-in fade-in-50 zoom-in-95">
            <div className="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50">
              <h3 className="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
                Enter Result: {activeRequest.test_name}
              </h3>
              <button 
                onClick={() => setModalOpen(false)}
                className="text-slate-800 dark:text-slate-200 hover:text-slate-600 text-lg cursor-pointer"
              >
                &times;
              </button>
            </div>
            
            <form onSubmit={handleSubmitResult} className="p-6 space-y-4">
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
                <label className="text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Result Value *</label>
                <input
                  type="text"
                  required
                  placeholder="e.g. 13.5"
                  value={val}
                  onChange={(e) => setVal(e.target.value)}
                  className="w-full mt-1.5 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 rounded-xl text-xs text-slate-800 dark:text-slate-100"
                />
              </div>

              <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                  <label className="text-[10px] font-bold text-slate-800 dark:text-slate-200">Min Normal</label>
                  <input
                    type="text"
                    placeholder="e.g. 11.5"
                    value={minVal}
                    onChange={(e) => setMinVal(e.target.value)}
                    className="w-full mt-1 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 rounded-xl text-xs"
                  />
                </div>
                <div>
                  <label className="text-[10px] font-bold text-slate-800 dark:text-slate-200">Max Normal</label>
                  <input
                    type="text"
                    placeholder="e.g. 16.5"
                    value={maxVal}
                    onChange={(e) => setMaxVal(e.target.value)}
                    className="w-full mt-1 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 rounded-xl text-xs"
                  />
                </div>
                <div>
                  <label className="text-[10px] font-bold text-slate-800 dark:text-slate-200">Unit</label>
                  <input
                    type="text"
                    placeholder="e.g. g/dL"
                    value={unit}
                    onChange={(e) => setUnit(e.target.value)}
                    className="w-full mt-1 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 rounded-xl text-xs"
                  />
                </div>
              </div>

              <div>
                <label className="text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Remarks / Comments</label>
                <textarea
                  rows={2}
                  value={remarks}
                  onChange={(e) => setRemarks(e.target.value)}
                  placeholder="Scientist diagnostics observation..."
                  className="w-full mt-1.5 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 rounded-xl text-xs text-slate-800 dark:text-slate-100"
                />
              </div>

              <div className="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                <button 
                  type="button" 
                  onClick={() => setModalOpen(false)}
                  className="px-4 py-2 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-semibold text-slate-800 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-900/40 cursor-pointer"
                >
                  Cancel
                </button>
                <button 
                  type="submit" 
                  className="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-xl text-xs font-semibold shadow-lg shadow-primary/10 cursor-pointer"
                >
                  Save Result Draft
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};



