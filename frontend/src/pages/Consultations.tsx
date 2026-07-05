import React, { useEffect, useState } from 'react';
import api from '../services/api';
import { Stethoscope, ShieldAlert, CheckCircle, Trash } from 'lucide-react';

export const Consultations: React.FC = () => {
  const [activeVisits, setActiveVisits] = useState<any[]>([]);
  const [visitId, setVisitId] = useState('');
  
  // Clinical Notes & Diagnosis
  const [complaint, setComplaint] = useState('');
  const [history, setHistory] = useState('');
  const [subjective, setSubjective] = useState('');
  const [objective, setObjective] = useState('');
  const [assessment, setAssessment] = useState('');
  const [plan, setPlan] = useState('');
  const [icd10, setIcd10] = useState('');
  const [diagnosisDesc, setDiagnosisDesc] = useState('');
  const [treatmentPlan, setTreatmentPlan] = useState('');
  
  // Prescriptions
  const [prescriptions, setPrescriptions] = useState<any[]>([]);
  const [drugName, setDrugName] = useState('');
  const [dosage, setDosage] = useState('');
  const [frequency, setFrequency] = useState('');
  const [duration, setDuration] = useState('');
  const [qty, setQty] = useState('');
  const [instructions, setInstructions] = useState('');

  // Lab Tests
  const [labTests, setLabTests] = useState<any[]>([]);
  const [labTestName, setLabTestName] = useState('');

  const [success, setSuccess] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);

  const fetchActiveVisits = async () => {
    try {
      // Fetch list of check-ins that have vitals recorded but no consult note completed
      const res = await api.get('/appointments'); // Or active visits list
      
      // Let's create mock active visits from appointments
      const mockVisits = res.data.appointments
        .filter((apt: any) => apt.status === 'checked_in')
        .map((apt: any) => ({
          id: apt.id, // Using appointment ID as mock visit ID
          patient_name: `${apt.patient.first_name} ${apt.patient.last_name}`,
          gender: apt.patient.gender,
          date_of_birth: apt.patient.date_of_birth,
        }));
      setActiveVisits(mockVisits);
    } catch (err) {
      console.error(err);
    }
  };

  useEffect(() => {
    fetchActiveVisits();
  }, []);

  const addPrescriptionItem = () => {
    if (!drugName || !dosage || !frequency || !duration || !qty) return;
    
    setPrescriptions([...prescriptions, {
      drug_name: drugName,
      dosage,
      frequency,
      duration_days: parseInt(duration),
      quantity_prescribed: parseInt(qty),
      instructions
    }]);

    // Clear item inputs
    setDrugName('');
    setDosage('');
    setFrequency('');
    setDuration('');
    setQty('');
    setInstructions('');
  };

  const removePrescriptionItem = (index: number) => {
    setPrescriptions(prescriptions.filter((_, i) => i !== index));
  };

  const addLabTest = () => {
    if (!labTestName) return;
    setLabTests([...labTests, { test_name: labTestName }]);
    setLabTestName('');
  };

  const removeLabTest = (index: number) => {
    setLabTests(labTests.filter((_, i) => i !== index));
  };

  const handleConsultSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setSuccess(null);
    setError(null);
    setLoading(true);

    const payload = {
      chief_complaint: complaint,
      history,
      soap_notes_subjective: subjective,
      soap_notes_objective: objective,
      soap_notes_assessment: assessment,
      soap_notes_plan: plan,
      diagnosis_icd10: icd10,
      diagnosis_description: diagnosisDesc,
      treatment_plan: treatmentPlan,
      prescriptions,
      lab_tests: labTests
    };

    try {
      await api.post(`/clinical/consult/${visitId}`, payload);
      setSuccess('Consultation recorded successfully and billing invoice generated!');
      
      // Clear Form
      setVisitId('');
      setComplaint('');
      setHistory('');
      setSubjective('');
      setObjective('');
      setAssessment('');
      setPlan('');
      setIcd10('');
      setDiagnosisDesc('');
      setTreatmentPlan('');
      setPrescriptions([]);
      setLabTests([]);
      
      fetchActiveVisits();
    } catch (err: any) {
      setError(err.response?.data?.message || 'Failed to submit consultation details.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="space-y-6 max-w-4xl mx-auto">
      <div>
        <h1 className="text-xl font-bold text-slate-800 dark:text-white flex items-center gap-2">
          <Stethoscope className="text-primary" /> General Outpatient Medical Consultation
        </h1>
        <p className="text-xs text-slate-800 dark:text-slate-200">Doctor clinical portal to document SOAP notes, ICD-10 diagnoses, and electronically issue prescriptions</p>
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

        <form onSubmit={handleConsultSubmit} className="space-y-6">
          <div>
            <label className="text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Patient Active Check-in *</label>
            <select
              required
              value={visitId}
              onChange={(e) => setVisitId(e.target.value)}
              className="w-full mt-1.5 px-3 py-2.5 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 rounded-xl text-xs text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-primary focus:outline-none"
            >
              <option value="">Select waiting patient...</option>
              {activeVisits.map(v => (
                <option key={v.id} value={v.id}>{v.patient_name} ({v.gender}, DOB: {v.date_of_birth})</option>
              ))}
            </select>
          </div>

          {/* SOAP Notes */}
          <div className="border-t border-slate-100 dark:border-slate-800 pt-4 space-y-4">
            <h3 className="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Clinical SOAP Notes</h3>
            
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label className="text-[10px] font-bold text-slate-800 dark:text-slate-200">Chief Complaint *</label>
                <textarea
                  required
                  rows={2}
                  value={complaint}
                  onChange={(e) => setComplaint(e.target.value)}
                  placeholder="Reason for encounter..."
                  className="w-full mt-1 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 rounded-xl text-xs text-slate-800 dark:text-slate-100"
                />
              </div>

              <div>
                <label className="text-[10px] font-bold text-slate-800 dark:text-slate-200">History of Presenting Illness</label>
                <textarea
                  rows={2}
                  value={history}
                  onChange={(e) => setHistory(e.target.value)}
                  placeholder="Presenting illness history details..."
                  className="w-full mt-1 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 rounded-xl text-xs text-slate-800 dark:text-slate-100"
                />
              </div>

              <div>
                <label className="text-[10px] font-bold text-slate-800 dark:text-slate-200">Subjective Note (S) *</label>
                <textarea
                  required
                  rows={2}
                  value={subjective}
                  onChange={(e) => setSubjective(e.target.value)}
                  placeholder="Patient reports..."
                  className="w-full mt-1 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 rounded-xl text-xs text-slate-800 dark:text-slate-100"
                />
              </div>

              <div>
                <label className="text-[10px] font-bold text-slate-800 dark:text-slate-200">Objective Note (O) *</label>
                <textarea
                  required
                  rows={2}
                  value={objective}
                  onChange={(e) => setObjective(e.target.value)}
                  placeholder="Physical exam details..."
                  className="w-full mt-1 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 rounded-xl text-xs text-slate-800 dark:text-slate-100"
                />
              </div>

              <div>
                <label className="text-[10px] font-bold text-slate-800 dark:text-slate-200">Assessment Note (A) *</label>
                <textarea
                  required
                  rows={2}
                  value={assessment}
                  onChange={(e) => setAssessment(e.target.value)}
                  placeholder="Clinical assessment notes..."
                  className="w-full mt-1 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 rounded-xl text-xs text-slate-800 dark:text-slate-100"
                />
              </div>

              <div>
                <label className="text-[10px] font-bold text-slate-800 dark:text-slate-200">Plan Note (P) *</label>
                <textarea
                  required
                  rows={2}
                  value={plan}
                  onChange={(e) => setPlan(e.target.value)}
                  placeholder="Clinical plan details..."
                  className="w-full mt-1 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 rounded-xl text-xs text-slate-800 dark:text-slate-100"
                />
              </div>
            </div>
          </div>

          {/* Diagnosis */}
          <div className="border-t border-slate-100 dark:border-slate-800 pt-4 space-y-4">
            <h3 className="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Diagnosis Details</h3>
            
            <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <div>
                <label className="text-[10px] font-bold text-slate-800 dark:text-slate-200">ICD-10 Code *</label>
                <input
                  type="text"
                  required
                  placeholder="e.g. B50.9"
                  value={icd10}
                  onChange={(e) => setIcd10(e.target.value)}
                  className="w-full mt-1 px-3 py-2.5 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 rounded-xl text-xs text-slate-800 dark:text-slate-100"
                />
              </div>

              <div className="sm:col-span-2">
                <label className="text-[10px] font-bold text-slate-800 dark:text-slate-200">Diagnosis Description *</label>
                <input
                  type="text"
                  required
                  placeholder="e.g. Severe Plasmodium falciparum malaria"
                  value={diagnosisDesc}
                  onChange={(e) => setDiagnosisDesc(e.target.value)}
                  className="w-full mt-1 px-3 py-2.5 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 rounded-xl text-xs text-slate-800 dark:text-slate-100"
                />
              </div>
            </div>
          </div>

          {/* Lab Orders */}
          <div className="border-t border-slate-100 dark:border-slate-800 pt-4 space-y-4">
            <h3 className="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Laboratory Requests</h3>
            
            <div className="flex gap-4">
              <input
                type="text"
                placeholder="e.g. Full Blood Count"
                value={labTestName}
                onChange={(e) => setLabTestName(e.target.value)}
                className="flex-1 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 rounded-xl text-xs text-slate-800 dark:text-slate-100"
              />
              <button
                type="button"
                onClick={addLabTest}
                className="bg-primary/10 border border-primary/20 text-primary px-4 rounded-xl text-xs font-bold cursor-pointer"
              >
                Add Test
              </button>
            </div>

            {labTests.length > 0 && (
              <div className="flex flex-wrap gap-2 pt-2">
                {labTests.map((t, i) => (
                  <span key={i} className="px-3 py-1 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs font-semibold rounded-full flex items-center gap-2">
                    {t.test_name}
                    <button type="button" onClick={() => removeLabTest(i)} className="text-red-500 hover:text-red-600 font-bold">&times;</button>
                  </span>
                ))}
              </div>
            )}
          </div>

          {/* Electronic Prescription */}
          <div className="border-t border-slate-100 dark:border-slate-800 pt-4 space-y-4">
            <h3 className="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Electronic Prescription</h3>
            
            <div className="grid grid-cols-2 sm:grid-cols-5 gap-3 bg-slate-50 dark:bg-slate-900/40 p-4 rounded-xl border border-slate-100 dark:border-slate-800/80">
              <div className="col-span-2">
                <label className="text-[9px] font-bold text-slate-800 dark:text-slate-200">Drug Name</label>
                <input
                  type="text"
                  placeholder="Paracetamol 500mg"
                  value={drugName}
                  onChange={(e) => setDrugName(e.target.value)}
                  className="w-full mt-1 px-3.5 py-1.5 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 rounded-lg text-xs"
                />
              </div>
              <div>
                <label className="text-[9px] font-bold text-slate-800 dark:text-slate-200">Dosage</label>
                <input
                  type="text"
                  placeholder="2 tabs"
                  value={dosage}
                  onChange={(e) => setDosage(e.target.value)}
                  className="w-full mt-1 px-3.5 py-1.5 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 rounded-lg text-xs"
                />
              </div>
              <div>
                <label className="text-[9px] font-bold text-slate-800 dark:text-slate-200">Frequency</label>
                <input
                  type="text"
                  placeholder="TDS (3x daily)"
                  value={frequency}
                  onChange={(e) => setFrequency(e.target.value)}
                  className="w-full mt-1 px-3.5 py-1.5 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 rounded-lg text-xs"
                />
              </div>
              <div>
                <label className="text-[9px] font-bold text-slate-800 dark:text-slate-200">Duration (Days)</label>
                <input
                  type="number"
                  placeholder="5"
                  value={duration}
                  onChange={(e) => setDuration(e.target.value)}
                  className="w-full mt-1 px-3.5 py-1.5 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 rounded-lg text-xs"
                />
              </div>
              <div>
                <label className="text-[9px] font-bold text-slate-800 dark:text-slate-200">Qty Prescribed</label>
                <input
                  type="number"
                  placeholder="30"
                  value={qty}
                  onChange={(e) => setQty(e.target.value)}
                  className="w-full mt-1 px-3.5 py-1.5 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 rounded-lg text-xs"
                />
              </div>
              <div className="col-span-3">
                <label className="text-[9px] font-bold text-slate-800 dark:text-slate-200">Usage Instructions</label>
                <input
                  type="text"
                  placeholder="Take after meals"
                  value={instructions}
                  onChange={(e) => setInstructions(e.target.value)}
                  className="w-full mt-1 px-3.5 py-1.5 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 rounded-lg text-xs"
                />
              </div>
              <div className="col-span-2 flex items-end">
                <button
                  type="button"
                  onClick={addPrescriptionItem}
                  className="w-full py-1.5 bg-primary hover:bg-primary-dark text-white rounded-lg text-xs font-bold transition-colors cursor-pointer"
                >
                  Add Medicine
                </button>
              </div>
            </div>

            {prescriptions.length > 0 && (
              <div className="overflow-x-auto">
                <table className="w-full text-left text-[11px] border border-slate-100 dark:border-slate-800 rounded-xl">
                  <thead className="bg-slate-50 dark:bg-slate-900/50">
                    <tr className="text-slate-800 dark:text-slate-200">
                      <th className="py-2.5 px-4 font-bold">Drug Name</th>
                      <th className="py-2.5 px-4 font-bold">Dosage & Frequency</th>
                      <th className="py-2.5 px-4 font-bold">Days</th>
                      <th className="py-2.5 px-4 font-bold">Qty</th>
                      <th className="py-2.5 px-4 font-bold text-right">Action</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    {prescriptions.map((item, index) => (
                      <tr key={index}>
                        <td className="py-2.5 px-4 font-semibold">{item.drug_name}</td>
                        <td className="py-2.5 px-4">{item.dosage} / {item.frequency}</td>
                        <td className="py-2.5 px-4">{item.duration_days} days</td>
                        <td className="py-2.5 px-4">{item.quantity_prescribed}</td>
                        <td className="py-2.5 px-4 text-right">
                          <button
                            type="button"
                            onClick={() => removePrescriptionItem(index)}
                            className="text-red-500 hover:text-red-600 cursor-pointer"
                          >
                            <Trash size={14} />
                          </button>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            )}
          </div>

          <div className="flex justify-end pt-4 border-t border-slate-100 dark:border-slate-800">
            <button
              type="submit"
              disabled={loading}
              className="bg-primary hover:bg-primary-dark text-white px-5 py-2.5 rounded-xl text-xs font-semibold shadow-lg shadow-primary/10 transition-all cursor-pointer disabled:opacity-50"
            >
              {loading ? 'Submitting Consult...' : 'Submit Consultation & Orders'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};



