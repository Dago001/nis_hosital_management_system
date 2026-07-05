import React, { useEffect, useState } from 'react';
import api from '../services/api';
import { useAuth } from '../contexts/AuthContext';
import { 
  Plus, 
  Search, 
  UserPlus, 
  Fingerprint, 
  QrCode, 
  ShieldAlert, 
  CheckCircle,
  Clock
} from 'lucide-react';

export const Patients: React.FC = () => {
  const { user } = useAuth();
  const [patients, setPatients] = useState<any[]>([]);
  const [search, setSearch] = useState('');
  const [loading, setLoading] = useState(true);
  const [modalOpen, setModalOpen] = useState(false);
  const [detailPatient, setDetailPatient] = useState<any>(null);
  const [timeline, setTimeline] = useState<any[]>([]);
  const [detailModalOpen, setDetailModalOpen] = useState(false);

  // Form Fields
  const [firstName, setFirstName] = useState('');
  const [lastName, setLastName] = useState('');
  const [gender, setGender] = useState('Male');
  const [dob, setDob] = useState('');
  const [phone, setPhone] = useState('');
  const [address, setAddress] = useState('');
  const [email, setEmail] = useState('');
  const [serviceNumber, setServiceNumber] = useState('');
  const [nin, setNin] = useState('');
  const [allergies, setAllergies] = useState('');
  const [bloodGroup, setBloodGroup] = useState('O+');
  const [genotype, setGenotype] = useState('AA');
  const [disability, setDisability] = useState('None');
  
  const [formSuccess, setFormSuccess] = useState<string | null>(null);
  const [formError, setFormError] = useState<string | null>(null);

  const fetchPatients = async () => {
    setLoading(true);
    try {
      const res = await api.get(`/patients?search=${search}`);
      setPatients(res.data.patients);
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchPatients();
  }, [search]);

  const handleRegister = async (e: React.FormEvent) => {
    e.preventDefault();
    setFormSuccess(null);
    setFormError(null);

    const payload = {
      first_name: firstName,
      last_name: lastName,
      gender,
      date_of_birth: dob,
      phone,
      address,
      email: email || null,
      immigration_service_number: serviceNumber || null,
      nin: nin || null,
      allergies: allergies || null,
      blood_group: bloodGroup,
      genotype,
      disability
    };

    try {
      await api.post('/patients', payload);
      setFormSuccess('Patient registered successfully in database!');
      
      // Clear form
      setFirstName('');
      setLastName('');
      setPhone('');
      setAddress('');
      setEmail('');
      setServiceNumber('');
      setNin('');
      setAllergies('');
      
      fetchPatients();
      setTimeout(() => {
        setModalOpen(false);
        setFormSuccess(null);
      }, 1500);
    } catch (err: any) {
      setFormError(err.response?.data?.message || 'Failed to register patient. Please check required fields.');
    }
  };

  const handleShowDetails = async (id: number) => {
    try {
      const res = await api.get(`/patients/${id}`);
      setDetailPatient(res.data.patient);
      setTimeline(res.data.timeline);
      setDetailModalOpen(true);
    } catch (err) {
      alert('Error fetching patient records.');
    }
  };

  return (
    <div className="space-y-6">
      {/* Title & Actions */}
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-xl font-bold text-slate-800 dark:text-white">Patient Records Directory</h1>
          <p className="text-xs text-slate-800 dark:text-slate-200">Manage patient demographics, registrations, biometric markers, and historical timelines</p>
        </div>
        {user?.roles?.[0]?.name && ['super_admin', 'records_officer'].includes(user.roles?.[0]?.name) && (
          <button 
            onClick={() => setModalOpen(true)}
            className="bg-primary hover:bg-primary-dark text-white px-4 py-2.5 rounded-xl text-xs font-semibold flex items-center gap-2 shadow-lg shadow-primary/10 transition-all cursor-pointer"
          >
            <Plus size={16} /> Register Patient
          </button>
        )}
      </div>

      {/* Filters & Search */}
      <div className="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800/80 flex items-center justify-between">
        <div className="relative w-80">
          <input 
            type="text" 
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            placeholder="Search name, NIN, service number..." 
            className="w-full pl-9 pr-4 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent text-slate-800 dark:text-slate-100 placeholder-slate-400 transition-all"
          />
          <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-800 dark:text-slate-200" size={14} />
        </div>
      </div>

      {/* Patients Table */}
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
                  <th className="py-3.5 px-6 font-bold">Patient Name</th>
                  <th className="py-3.5 px-6 font-bold">Gender & DOB</th>
                  <th className="py-3.5 px-6 font-bold">Service Number</th>
                  <th className="py-3.5 px-6 font-bold">NIN</th>
                  <th className="py-3.5 px-6 font-bold">Phone</th>
                  <th className="py-3.5 px-6 font-bold text-right">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                {patients.length > 0 ? (
                  patients.map((pat) => (
                    <tr key={pat.id} className="hover:bg-slate-50 dark:hover:bg-slate-900/20 transition-all">
                      <td className="py-3.5 px-6 font-bold text-slate-900 dark:text-white">
                        {pat.first_name} {pat.last_name}
                      </td>
                      <td className="py-3.5 px-6">
                        <span className="font-medium">{pat.gender}</span>
                        <div className="text-[10px] text-slate-800 dark:text-slate-200">{pat.date_of_birth}</div>
                      </td>
                      <td className="py-3.5 px-6">
                        {pat.immigration_service_number ? (
                          <span className="px-2 py-0.5 font-bold bg-primary/10 text-primary border border-primary/20 rounded">
                            {pat.immigration_service_number}
                          </span>
                        ) : (
                          <span className="text-slate-800 dark:text-slate-200">Civilian</span>
                        )}
                      </td>
                      <td className="py-3.5 px-6 font-mono text-slate-800 dark:text-slate-200">{pat.nin || '—'}</td>
                      <td className="py-3.5 px-6">{pat.phone}</td>
                      <td className="py-3.5 px-6 text-right">
                        <button 
                          onClick={() => handleShowDetails(pat.id)}
                          className="text-primary hover:text-emerald-600 font-bold hover:underline cursor-pointer"
                        >
                          View File
                        </button>
                      </td>
                    </tr>
                  ))
                ) : (
                  <tr>
                    <td colSpan={6} className="py-8 text-center text-slate-800 dark:text-slate-200 text-sm">No patient records found matching query.</td>
                  </tr>
                )}
              </tbody>
            </table>
          </div>
        )}
      </div>

      {/* ==================================================== */}
      {/* 1. REGISTRATION MODAL FORM */}
      {/* ==================================================== */}
      {modalOpen && (
        <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
          <div className="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-3xl overflow-hidden shadow-2xl animate-in fade-in-50 zoom-in-95">
            <div className="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50">
              <h3 className="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
                <UserPlus size={18} className="text-primary" /> Register Patient File
              </h3>
              <button 
                onClick={() => setModalOpen(false)}
                className="text-slate-800 dark:text-slate-200 hover:text-slate-600 text-lg cursor-pointer"
              >
                &times;
              </button>
            </div>
            
            <form onSubmit={handleRegister} className="p-6 space-y-6 max-h-[70vh] overflow-y-auto">
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

              {/* Demographics */}
              <div className="space-y-3">
                <h4 className="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Demographic Details</h4>
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label className="text-[10px] font-bold text-slate-800 dark:text-slate-200">First Name *</label>
                    <input 
                      type="text" 
                      required
                      value={firstName}
                      onChange={(e) => setFirstName(e.target.value)}
                      placeholder="e.g. Babajide"
                      className="w-full mt-1 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 rounded-xl text-xs text-slate-800 dark:text-slate-100"
                    />
                  </div>
                  <div>
                    <label className="text-[10px] font-bold text-slate-800 dark:text-slate-200">Last Name *</label>
                    <input 
                      type="text" 
                      required
                      value={lastName}
                      onChange={(e) => setLastName(e.target.value)}
                      placeholder="e.g. Sanwo"
                      className="w-full mt-1 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 rounded-xl text-xs text-slate-800 dark:text-slate-100"
                    />
                  </div>
                  <div>
                    <label className="text-[10px] font-bold text-slate-800 dark:text-slate-200">Gender *</label>
                    <select 
                      value={gender}
                      onChange={(e) => setGender(e.target.value)}
                      className="w-full mt-1 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 rounded-xl text-xs text-slate-800 dark:text-slate-100"
                    >
                      <option>Male</option>
                      <option>Female</option>
                      <option>Other</option>
                    </select>
                  </div>
                  <div>
                    <label className="text-[10px] font-bold text-slate-800 dark:text-slate-200">Date of Birth *</label>
                    <input 
                      type="date" 
                      required
                      value={dob}
                      onChange={(e) => setDob(e.target.value)}
                      className="w-full mt-1 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 rounded-xl text-xs text-slate-800 dark:text-slate-100"
                    />
                  </div>
                </div>
              </div>

              {/* Identifications */}
              <div className="space-y-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                <h4 className="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">NIS & Government Identifications</h4>
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label className="text-[10px] font-bold text-slate-800 dark:text-slate-200">Immigration Service Number (NIS Officer/Dependant)</label>
                    <input 
                      type="text" 
                      value={serviceNumber}
                      onChange={(e) => setServiceNumber(e.target.value)}
                      placeholder="e.g. NIS/OFF/10293"
                      className="w-full mt-1 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 rounded-xl text-xs text-slate-800 dark:text-slate-100"
                    />
                  </div>
                  <div>
                    <label className="text-[10px] font-bold text-slate-800 dark:text-slate-200">National ID (11-digit NIN)</label>
                    <input 
                      type="text" 
                      maxLength={11}
                      value={nin}
                      onChange={(e) => setNin(e.target.value)}
                      placeholder="11 digits"
                      className="w-full mt-1 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 rounded-xl text-xs text-slate-800 dark:text-slate-100"
                    />
                  </div>
                </div>
              </div>

              {/* Contacts */}
              <div className="space-y-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                <h4 className="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Contact Details</h4>
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label className="text-[10px] font-bold text-slate-800 dark:text-slate-200">Phone Number *</label>
                    <input 
                      type="text" 
                      required
                      value={phone}
                      onChange={(e) => setPhone(e.target.value)}
                      placeholder="e.g. 08055554433"
                      className="w-full mt-1 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 rounded-xl text-xs text-slate-800 dark:text-slate-100"
                    />
                  </div>
                  <div>
                    <label className="text-[10px] font-bold text-slate-800 dark:text-slate-200">Email Address</label>
                    <input 
                      type="email" 
                      value={email}
                      onChange={(e) => setEmail(e.target.value)}
                      placeholder="patient@mail.com"
                      className="w-full mt-1 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 rounded-xl text-xs text-slate-800 dark:text-slate-100"
                    />
                  </div>
                  <div className="sm:col-span-2">
                    <label className="text-[10px] font-bold text-slate-800 dark:text-slate-200">Residential Address *</label>
                    <textarea 
                      required
                      rows={2}
                      value={address}
                      onChange={(e) => setAddress(e.target.value)}
                      placeholder="Residential address"
                      className="w-full mt-1 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 rounded-xl text-xs text-slate-800 dark:text-slate-100"
                    />
                  </div>
                </div>
              </div>

              {/* Medical History */}
              <div className="space-y-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                <h4 className="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Clinical Profiling</h4>
                <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                  <div>
                    <label className="text-[10px] font-bold text-slate-800 dark:text-slate-200">Blood Group</label>
                    <select 
                      value={bloodGroup}
                      onChange={(e) => setBloodGroup(e.target.value)}
                      className="w-full mt-1 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 rounded-xl text-xs text-slate-800 dark:text-slate-100"
                    >
                      <option>A+</option>
                      <option>A-</option>
                      <option>B+</option>
                      <option>B-</option>
                      <option>AB+</option>
                      <option>AB-</option>
                      <option>O+</option>
                      <option>O-</option>
                    </select>
                  </div>
                  <div>
                    <label className="text-[10px] font-bold text-slate-800 dark:text-slate-200">Genotype</label>
                    <select 
                      value={genotype}
                      onChange={(e) => setGenotype(e.target.value)}
                      className="w-full mt-1 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 rounded-xl text-xs text-slate-800 dark:text-slate-100"
                    >
                      <option>AA</option>
                      <option>AS</option>
                      <option>SS</option>
                      <option>AC</option>
                      <option>SC</option>
                    </select>
                  </div>
                  <div>
                    <label className="text-[10px] font-bold text-slate-800 dark:text-slate-200">Disability / Special Needs</label>
                    <input 
                      type="text" 
                      value={disability}
                      onChange={(e) => setDisability(e.target.value)}
                      className="w-full mt-1 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 rounded-xl text-xs text-slate-800 dark:text-slate-100"
                    />
                  </div>
                  <div className="sm:col-span-3">
                    <label className="text-[10px] font-bold text-slate-800 dark:text-slate-200">Allergies (Separated by comma)</label>
                    <input 
                      type="text" 
                      value={allergies}
                      onChange={(e) => setAllergies(e.target.value)}
                      placeholder="e.g. Penicillin, Sulfur, Dust"
                      className="w-full mt-1 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 rounded-xl text-xs text-slate-800 dark:text-slate-100"
                    />
                  </div>
                </div>
              </div>

              {/* MOCK BIOMETRICS IN CLINICAL FILE */}
              <div className="bg-primary/5 p-4 rounded-xl border border-primary/10 flex items-center justify-between">
                <div className="flex items-center gap-3">
                  <div className="p-2 rounded-full bg-primary/10 text-primary">
                    <Fingerprint size={24} />
                  </div>
                  <div>
                    <span className="text-xs font-bold text-slate-800 dark:text-white">Integrated Biometrics Mock Capture</span>
                    <p className="text-[10px] text-slate-800 dark:text-slate-200">Capture fingerprint and passport photo for governmental health insurance</p>
                  </div>
                </div>
                <button type="button" className="px-3 py-1.5 bg-primary/20 text-primary hover:bg-primary/30 rounded-lg text-[10px] font-bold border border-primary/20 cursor-pointer">
                  Initialize Scanner
                </button>
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
                  Create Patient File
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* ==================================================== */}
      {/* 2. PATIENT CARD / TIMELINE VIEW MODAL */}
      {/* ==================================================== */}
      {detailModalOpen && detailPatient && (
        <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
          <div className="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-4xl overflow-hidden shadow-2xl animate-in fade-in-50 zoom-in-95">
            <div className="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50">
              <h3 className="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
                Patient Health record Card
              </h3>
              <button 
                onClick={() => setDetailModalOpen(false)}
                className="text-slate-800 dark:text-slate-200 hover:text-slate-600 text-lg cursor-pointer"
              >
                &times;
              </button>
            </div>

            <div className="p-6 space-y-6 max-h-[75vh] overflow-y-auto">
              {/* Header profile details block */}
              <div className="p-6 bg-gradient-to-r from-[#008000] via-[#006B3F] to-[#004D2D] text-white rounded-2xl border border-green-900/20 flex flex-col sm:flex-row justify-between gap-6">
                <div className="flex gap-4">
                  <div className="w-16 h-16 rounded-xl bg-white/15 flex items-center justify-center font-bold text-2xl text-white border border-white/20">
                    {detailPatient.first_name.charAt(0)}{detailPatient.last_name.charAt(0)}
                  </div>
                  <div className="space-y-1">
                    <h2 className="text-lg font-bold text-white leading-tight">
                      {detailPatient.first_name} {detailPatient.last_name}
                    </h2>
                    <div className="flex gap-2">
                      <span className="text-[10px] px-2 py-0.5 bg-white/15 rounded-full">{detailPatient.gender}</span>
                      <span className="text-[10px] px-2 py-0.5 bg-white/15 rounded-full">{detailPatient.date_of_birth}</span>
                    </div>
                    {detailPatient.immigration_service_number && (
                      <div className="text-[10px] text-white/80 font-bold flex items-center gap-1">
                        Service Number: {detailPatient.immigration_service_number}
                      </div>
                    )}
                  </div>
                </div>

                {/* Patient card Barcode and QR */}
                <div className="bg-white p-2.5 rounded-xl border border-white/20 max-w-[150px] self-start sm:self-center flex flex-col items-center justify-center">
                  <QrCode size={48} className="text-slate-900" />
                  <span className="text-[8px] font-mono font-bold text-slate-900 tracking-wider mt-1">{detailPatient.barcode_data}</span>
                </div>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                {/* Demographic specs */}
                <div className="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-4">
                  <h4 className="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Demographic Metrics</h4>
                  <div className="space-y-2 text-xs">
                    <div className="flex justify-between border-b border-slate-100 pb-1.5">
                      <span className="text-slate-800 dark:text-slate-200">Blood Group:</span>
                      <span className="font-bold text-slate-800">{detailPatient.blood_group || '—'}</span>
                    </div>
                    <div className="flex justify-between border-b border-slate-100 pb-1.5">
                      <span className="text-slate-800 dark:text-slate-200">Genotype:</span>
                      <span className="font-bold text-slate-800">{detailPatient.genotype || '—'}</span>
                    </div>
                    <div className="flex justify-between border-b border-slate-100 pb-1.5">
                      <span className="text-slate-800 dark:text-slate-200">NIN:</span>
                      <span className="font-mono text-slate-800">{detailPatient.nin || '—'}</span>
                    </div>
                    <div className="flex justify-between border-b border-slate-100 pb-1.5">
                      <span className="text-slate-800 dark:text-slate-200">Phone:</span>
                      <span className="font-bold text-slate-800">{detailPatient.phone}</span>
                    </div>
                    <div className="flex justify-between pb-1.5">
                      <span className="text-slate-800 dark:text-slate-200">Disability:</span>
                      <span className="font-bold text-slate-800">{detailPatient.disability}</span>
                    </div>
                  </div>
                  
                  <div className="p-3 bg-red-500/10 border border-red-500/20 rounded-xl mt-4">
                    <span className="text-[10px] font-bold text-red-500 uppercase tracking-wider block mb-1">Clinical Allergies</span>
                    <p className="text-xs text-red-500 font-semibold">{detailPatient.allergies || 'No allergies recorded.'}</p>
                  </div>
                </div>

                {/* Patient timeline list */}
                <div className="md:col-span-2 space-y-4">
                  <h4 className="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider flex items-center gap-2">
                    <Clock size={16} /> Patient Clinical Timeline
                  </h4>
                  <div className="relative border-l border-slate-200 dark:border-slate-800 pl-4 ml-2 space-y-6">
                    {timeline && timeline.length > 0 ? (
                      timeline.map((item: any, idx: number) => (
                        <div key={idx} className="relative">
                          <div className="absolute -left-[21px] top-1 w-2.5 h-2.5 rounded-full bg-primary border-2 border-white dark:border-slate-900"></div>
                          <div className="space-y-1">
                            <span className="text-[10px] text-slate-800 dark:text-slate-200 font-medium">{item.date}</span>
                            <h5 className="text-xs font-bold text-slate-800 dark:text-white">{item.title}</h5>
                            <p className="text-xs text-slate-800 dark:text-slate-200">{item.description}</p>
                            {item.details && (
                              <div className="mt-2 bg-slate-50 dark:bg-slate-900/60 p-3 rounded-lg border border-slate-200 dark:border-slate-800 text-[11px] text-slate-600  space-y-1">
                                {item.details.bp && <div>Vitals BP: <span className="font-semibold text-slate-800 dark:text-slate-200">{item.details.bp}</span></div>}
                                {item.details.temp && <div>Vitals Temp: <span className="font-semibold text-slate-800 dark:text-slate-200">{item.details.temp}°C</span></div>}
                                {item.details.complaints && <div className="mt-1">Chief Complaint: <span className="italic text-slate-700 dark:text-slate-300">"{item.details.complaints}"</span></div>}
                              </div>
                            )}
                          </div>
                        </div>
                      ))
                    ) : (
                      <div className="text-slate-800 dark:text-slate-200 text-xs">No clinical entries found in timeline history.</div>
                    )}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};



