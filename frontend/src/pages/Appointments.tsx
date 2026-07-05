import React, { useEffect, useState } from 'react';
import api from '../services/api';
import { useAuth } from '../contexts/AuthContext';
import { CheckCircle, ShieldAlert, Plus } from 'lucide-react';

export const Appointments: React.FC = () => {
  const { } = useAuth();
  const [appointments, setAppointments] = useState<any[]>([]);
  const [doctors, setDoctors] = useState<any[]>([]);
  const [patients, setPatients] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [modalOpen, setModalOpen] = useState(false);

  // Form Fields
  const [patientId, setPatientId] = useState('');
  const [doctorId, setDoctorId] = useState('');
  const [date, setDate] = useState('');
  const [time, setTime] = useState('');
  const [notes, setNotes] = useState('');
  const [formSuccess, setFormSuccess] = useState<string | null>(null);
  const [formError, setFormError] = useState<string | null>(null);

  const fetchAppointments = async () => {
    setLoading(true);
    try {
      const res = await api.get('/appointments');
      setAppointments(res.data.appointments);
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const fetchSetupData = async () => {
    try {
      const docRes = await api.get('/appointments/doctors');
      setDoctors(docRes.data.doctors);

      const patRes = await api.get('/patients');
      setPatients(patRes.data.patients);
    } catch (err) {
      console.error(err);
    }
  };

  useEffect(() => {
    fetchAppointments();
    fetchSetupData();
  }, []);

  const handleBook = async (e: React.FormEvent) => {
    e.preventDefault();
    setFormSuccess(null);
    setFormError(null);

    const doctor = doctors.find(d => d.id === parseInt(doctorId));
    const departmentId = doctor ? doctor.department_id : null;

    if (!departmentId) {
      setFormError('Could not resolve doctor department.');
      return;
    }

    const payload = {
      patient_id: patientId,
      staff_id: doctorId,
      department_id: departmentId,
      appointment_date: date,
      appointment_time: time,
      notes
    };

    try {
      await api.post('/appointments', payload);
      setFormSuccess('Appointment booked successfully!');
      
      // Clear fields
      setPatientId('');
      setDoctorId('');
      setDate('');
      setTime('');
      setNotes('');
      
      fetchAppointments();
      setTimeout(() => {
        setModalOpen(false);
        setFormSuccess(null);
      }, 1500);
    } catch (err: any) {
      setFormError(err.response?.data?.message || 'Failed to book appointment.');
    }
  };

  const handleCheckIn = async (id: number) => {
    try {
      await api.post(`/appointments/${id}/check-in`);
      fetchAppointments();
    } catch (err) {
      alert('Error updating appointment.');
    }
  };

  const handleCancel = async (id: number) => {
    try {
      await api.post(`/appointments/${id}/cancel`);
      fetchAppointments();
    } catch (err) {
      alert('Error updating appointment.');
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-xl font-bold text-slate-800 dark:text-white">Active Consult Appointments</h1>
          <p className="text-xs text-slate-800 dark:text-slate-200">Queue list and scheduling dashboard for patients and consultation queues</p>
        </div>
        <button 
          onClick={() => setModalOpen(true)}
          className="bg-primary hover:bg-primary-dark text-white px-4 py-2.5 rounded-xl text-xs font-semibold flex items-center gap-2 shadow-lg shadow-primary/10 transition-all cursor-pointer"
        >
          <Plus size={16} /> Book Appointment
        </button>
      </div>

      {/* Appointment Table */}
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
                  <th className="py-3.5 px-6 font-bold">Queue No.</th>
                  <th className="py-3.5 px-6 font-bold">Patient Name</th>
                  <th className="py-3.5 px-6 font-bold">Assigned Doctor</th>
                  <th className="py-3.5 px-6 font-bold">Date & Time</th>
                  <th className="py-3.5 px-6 font-bold">Status</th>
                  <th className="py-3.5 px-6 font-bold text-right">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                {appointments.length > 0 ? (
                  appointments.map((apt) => (
                    <tr key={apt.id} className="hover:bg-slate-50 dark:hover:bg-slate-900/20 transition-all">
                      <td className="py-3.5 px-6 font-bold text-primary">{apt.queue_number}</td>
                      <td className="py-3.5 px-6 font-bold text-slate-900 dark:text-white">
                        {apt.patient.first_name} {apt.patient.last_name}
                      </td>
                      <td className="py-3.5 px-6">
                        <span className="font-semibold">Dr. {apt.doctor?.first_name} {apt.doctor?.last_name}</span>
                        <div className="text-[10px] text-slate-800 dark:text-slate-200">{apt.department?.name}</div>
                      </td>
                      <td className="py-3.5 px-6">
                        <span className="font-semibold">{apt.appointment_date}</span>
                        <div className="text-[10px] text-slate-800 dark:text-slate-200">{apt.appointment_time}</div>
                      </td>
                      <td className="py-3.5 px-6">
                        <span className={`px-2 py-0.5 text-[9px] font-bold rounded-full border ${
                          apt.status === 'checked_in' 
                            ? 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20' 
                            : apt.status === 'completed'
                            ? 'bg-blue-500/10 text-blue-500 border-blue-500/20'
                            : apt.status === 'cancelled'
                            ? 'bg-red-500/10 text-red-500 border-red-500/20'
                            : 'bg-amber-500/10 text-amber-500 border-amber-500/20'
                        }`}>
                          {apt.status}
                        </span>
                      </td>
                      <td className="py-3.5 px-6 text-right space-x-2">
                        {apt.status === 'pending' && (
                          <>
                            <button 
                              onClick={() => handleCheckIn(apt.id)}
                              className="text-emerald-500 hover:text-emerald-600 font-bold hover:underline cursor-pointer"
                            >
                              Check In
                            </button>
                            <button 
                              onClick={() => handleCancel(apt.id)}
                              className="text-red-500 hover:text-red-600 font-bold hover:underline cursor-pointer"
                            >
                              Cancel
                            </button>
                          </>
                        )}
                      </td>
                    </tr>
                  ))
                ) : (
                  <tr>
                    <td colSpan={6} className="py-8 text-center text-slate-800 dark:text-slate-200 text-sm">No appointments scheduled for today.</td>
                  </tr>
                )}
              </tbody>
            </table>
          </div>
        )}
      </div>

      {/* Book Appointment Modal */}
      {modalOpen && (
        <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
          <div className="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-md overflow-hidden shadow-2xl animate-in fade-in-50 zoom-in-95">
            <div className="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50">
              <h3 className="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
                Book Patient Appointment
              </h3>
              <button 
                onClick={() => setModalOpen(false)}
                className="text-slate-800 dark:text-slate-200 hover:text-slate-600 text-lg cursor-pointer"
              >
                &times;
              </button>
            </div>
            
            <form onSubmit={handleBook} className="p-6 space-y-4">
              {formError && (
                <div className="bg-red-500/10 border border-red-500/20 text-red-500 p-3 rounded-xl text-xs flex items-center gap-2">
                  <ShieldAlert size={16} /> {formError}
                </div>
              )}
              {formSuccess && (
                <div className="bg-emerald-500/10 border border-emerald-500/20 text-emerald-500 p-3 rounded-xl text-xs flex items-center gap-2">
                  <CheckCircle size={16} /> {formSuccess}
                </div>
              )}

              <div>
                <label className="text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Patient *</label>
                <select 
                  required
                  value={patientId}
                  onChange={(e) => setPatientId(e.target.value)}
                  className="w-full mt-1.5 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 rounded-xl text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary"
                >
                  <option value="">Select Patient...</option>
                  {patients.map(p => (
                    <option key={p.id} value={p.id}>{p.first_name} {p.last_name} ({p.immigration_service_number || 'Civilian'})</option>
                  ))}
                </select>
              </div>

              <div>
                <label className="text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Consulting Doctor *</label>
                <select 
                  required
                  value={doctorId}
                  onChange={(e) => setDoctorId(e.target.value)}
                  className="w-full mt-1.5 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 rounded-xl text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary"
                >
                  <option value="">Select Doctor...</option>
                  {doctors.map(d => (
                    <option key={d.id} value={d.id}>Dr. {d.first_name} {d.last_name} ({d.specialization || 'Clinical Generalist'})</option>
                  ))}
                </select>
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Date *</label>
                  <input 
                    type="date" 
                    required
                    value={date}
                    onChange={(e) => setDate(e.target.value)}
                    className="w-full mt-1.5 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 rounded-xl text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary"
                  />
                </div>
                <div>
                  <label className="text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Time *</label>
                  <input 
                    type="time" 
                    required
                    value={time}
                    onChange={(e) => setTime(e.target.value)}
                    className="w-full mt-1.5 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 rounded-xl text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary"
                  />
                </div>
              </div>

              <div>
                <label className="text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Clinical Notes</label>
                <textarea 
                  rows={3}
                  value={notes}
                  onChange={(e) => setNotes(e.target.value)}
                  placeholder="Reason for consultation visit..."
                  className="w-full mt-1.5 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 rounded-xl text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary"
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
                  Book Appointment
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};



