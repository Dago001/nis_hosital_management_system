import React, { useEffect, useState } from 'react';
import api from '../services/api';
import { useAuth } from '../contexts/AuthContext';
import { 
  Pill, 
  Search, 
  Plus, 
  AlertTriangle, 
  CheckCircle,
  CheckSquare,
  ShieldAlert,
  Activity
} from 'lucide-react';

export const Pharmacy: React.FC = () => {
  const { user } = useAuth();
  const [activeTab, setActiveTab] = useState<'dispensary' | 'inventory'>('dispensary');
  
  // Inventory State
  const [inventory, setInventory] = useState<any[]>([]);
  const [invSearch, setInvSearch] = useState('');
  const [invLoading, setInvLoading] = useState(false);
  const [invModalOpen, setInvModalOpen] = useState(false);
  
  // Dispense State
  const [prescriptions, setPrescriptions] = useState<any[]>([]);
  const [prescLoading, setPrescLoading] = useState(false);
  const [dispenseModalOpen, setDispenseModalOpen] = useState(false);
  const [selectedPrescription, setSelectedPrescription] = useState<any>(null);

  // Form states
  const [formSuccess, setFormSuccess] = useState<string | null>(null);
  const [formError, setFormError] = useState<string | null>(null);

  // New Drug Form
  const [dName, setDName] = useState('');
  const [dGeneric, setDGeneric] = useState('');
  const [dCode, setDCode] = useState('');
  const [dCategory, setDCategory] = useState('Tablet');
  const [dBatch, setDBatch] = useState('');
  const [dExpiry, setDExpiry] = useState('');
  const [dStock, setDStock] = useState('0');
  const [dReorder, setDReorder] = useState('50');
  const [dPrice, setDPrice] = useState('0');

  // Dispensary Logic
  const [dispenseQuantities, setDispenseQuantities] = useState<Record<number, number>>({});
  const [dispenseItemsIds, setDispenseItemsIds] = useState<Record<number, number>>({});

  useEffect(() => {
    if (activeTab === 'inventory') {
      fetchInventory();
    } else {
      fetchPrescriptions();
    }
  }, [activeTab]);

  useEffect(() => {
    if (activeTab === 'inventory') {
      const delay = setTimeout(() => fetchInventory(), 500);
      return () => clearTimeout(delay);
    }
  }, [invSearch]);

  const fetchInventory = async () => {
    setInvLoading(true);
    try {
      const res = await api.get(`/pharmacy/inventory?search=${invSearch}`);
      setInventory(res.data.inventory);
    } catch (err) {
      console.error(err);
    } finally {
      setInvLoading(false);
    }
  };

  const fetchPrescriptions = async () => {
    setPrescLoading(true);
    try {
      const res = await api.get('/pharmacy/prescriptions');
      setPrescriptions(res.data.prescriptions);
    } catch (err) {
      console.error(err);
    } finally {
      setPrescLoading(false);
    }
  };

  const handleAddDrug = async (e: React.FormEvent) => {
    e.preventDefault();
    setFormSuccess(null);
    setFormError(null);

    const payload = {
      name: dName,
      generic_name: dGeneric,
      code: dCode,
      category: dCategory,
      batch_number: dBatch,
      expiry_date: dExpiry || null,
      quantity_in_stock: parseInt(dStock),
      reorder_level: parseInt(dReorder),
      price_per_unit: parseFloat(dPrice)
    };

    try {
      await api.post('/pharmacy/inventory', payload);
      setFormSuccess('Drug added to inventory successfully.');
      
      // Clear form
      setDName(''); setDGeneric(''); setDCode(''); setDBatch('');
      setDExpiry(''); setDStock('0'); setDReorder('50'); setDPrice('0');
      
      fetchInventory();
      setTimeout(() => {
        setInvModalOpen(false);
        setFormSuccess(null);
      }, 1500);
    } catch (err: any) {
      setFormError(err.response?.data?.message || 'Failed to add drug. Check fields.');
    }
  };

  const openDispenseModal = async (p: any) => {
    setSelectedPrescription(p);
    setDispenseModalOpen(true);
    
    // Auto-map if inventory matches by name approximately, else user must select
    // Fetch inventory for dropdowns
    if (inventory.length === 0) {
      await fetchInventory();
    }
    
    // reset quantities
    const qs: Record<number, number> = {};
    const ids: Record<number, number> = {};
    p.items.forEach((i: any) => {
      qs[i.id] = i.quantity_prescribed;
    });
    setDispenseQuantities(qs);
    setDispenseItemsIds(ids);
  };

  const handleDispense = async (e: React.FormEvent) => {
    e.preventDefault();
    setFormSuccess(null);
    setFormError(null);

    const dItems = selectedPrescription.items.map((item: any) => {
      if (!dispenseItemsIds[item.id]) {
        throw new Error(`Please select an inventory item for ${item.drug_name}`);
      }
      return {
        prescription_item_id: item.id,
        pharmacy_item_id: dispenseItemsIds[item.id],
        quantity: dispenseQuantities[item.id]
      };
    });

    try {
      await api.post(`/pharmacy/prescriptions/${selectedPrescription.id}/dispense`, {
        dispensed_items: dItems
      });
      setFormSuccess('Prescription dispensed successfully!');
      fetchPrescriptions();
      setTimeout(() => {
        setDispenseModalOpen(false);
        setFormSuccess(null);
      }, 1500);
    } catch (err: any) {
      setFormError(err.response?.data?.message || err.message || 'Failed to dispense.');
    }
  };


  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <div>
          <h1 className="text-2xl font-bold text-slate-800 tracking-tight">Pharmacy & Dispensary</h1>
          <p className="text-xs text-slate-500 mt-1">Manage prescriptions and drug inventory.</p>
        </div>
        <div className="flex bg-slate-100 p-1 rounded-xl">
          <button
            onClick={() => setActiveTab('dispensary')}
            className={`px-4 py-2 rounded-lg text-sm font-bold transition-all ${activeTab === 'dispensary' ? 'bg-white text-primary shadow-sm' : 'text-slate-600 hover:text-slate-800'}`}
          >
            Dispensary
          </button>
          <button
            onClick={() => setActiveTab('inventory')}
            className={`px-4 py-2 rounded-lg text-sm font-bold transition-all ${activeTab === 'inventory' ? 'bg-white text-primary shadow-sm' : 'text-slate-600 hover:text-slate-800'}`}
          >
            Inventory
          </button>
        </div>
      </div>

      {activeTab === 'dispensary' && (
        <div className="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
          <div className="p-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
            <h3 className="font-bold text-slate-800">Prescription Queue</h3>
            <button onClick={fetchPrescriptions} className="text-xs text-primary font-bold hover:underline">
              Refresh Queue
            </button>
          </div>
          <div className="p-0">
            {prescLoading ? (
              <div className="p-8 text-center text-slate-500 text-sm">Loading queue...</div>
            ) : prescriptions.length === 0 ? (
              <div className="p-8 text-center flex flex-col items-center justify-center">
                <CheckSquare size={40} className="text-emerald-500 mb-3 opacity-50" />
                <p className="text-sm font-semibold text-slate-700">No prescriptions in queue.</p>
              </div>
            ) : (
              <div className="overflow-x-auto">
                <table className="w-full text-left text-xs">
                  <thead className="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100 uppercase tracking-wider text-[10px]">
                    <tr>
                      <th className="px-4 py-3">Date</th>
                      <th className="px-4 py-3">Patient</th>
                      <th className="px-4 py-3">Doctor</th>
                      <th className="px-4 py-3">Items</th>
                      <th className="px-4 py-3">Status</th>
                      <th className="px-4 py-3 text-right">Action</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-slate-100">
                    {prescriptions.map(p => (
                      <tr key={p.id} className="hover:bg-slate-50/50 transition-colors">
                        <td className="px-4 py-3 text-slate-600">{new Date(p.created_at).toLocaleDateString()}</td>
                        <td className="px-4 py-3 font-semibold text-slate-800">{p.patient?.first_name} {p.patient?.last_name}</td>
                        <td className="px-4 py-3 text-slate-600">Dr. {p.doctor?.last_name}</td>
                        <td className="px-4 py-3 font-bold text-primary">{p.items?.length || 0} drugs</td>
                        <td className="px-4 py-3">
                          <span className={`px-2 py-1 rounded-full text-[9px] font-bold uppercase tracking-wider ${
                            p.status === 'dispensed' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'
                          }`}>
                            {p.status}
                          </span>
                        </td>
                        <td className="px-4 py-3 text-right">
                          <button
                            disabled={p.status === 'dispensed'}
                            onClick={() => openDispenseModal(p)}
                            className="bg-primary hover:bg-primary-dark text-white px-3 py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-wider transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                          >
                            {p.status === 'dispensed' ? 'Dispensed' : 'Dispense'}
                          </button>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            )}
          </div>
        </div>
      )}

      {activeTab === 'inventory' && (
        <div className="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
          <div className="p-4 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div className="relative w-full sm:w-64">
              <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400" size={16} />
              <input
                type="text"
                placeholder="Search inventory..."
                value={invSearch}
                onChange={(e) => setInvSearch(e.target.value)}
                className="w-full pl-9 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all"
              />
            </div>
            {['super_admin', 'pharmacist', 'inventory_officer', 'store_officer', 'procurement_officer'].includes(user?.roles?.[0]?.name || '') && (
              <button 
                onClick={() => setInvModalOpen(true)}
                className="flex items-center gap-2 bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-xl text-xs font-bold transition-all shadow-md shadow-primary/20"
              >
                <Plus size={16} />
                Add Drug
              </button>
            )}
          </div>
          
          {/* Low Stock Alert Banner */}
          {(() => {
            const lowStockItems = inventory.filter(item => item.quantity_in_stock <= item.reorder_level);
            if (lowStockItems.length === 0) return null;
            return (
              <div className="mx-4 mt-4 bg-red-50/80 border border-red-200/60 p-4 rounded-2xl flex items-start gap-3 text-red-800">
                <AlertTriangle className="text-red-600 flex-shrink-0 mt-0.5" size={18} />
                <div className="flex-1 min-w-0">
                  <h4 className="text-xs font-bold uppercase tracking-wider text-red-900">Critical Stock Alerts</h4>
                  <p className="text-[11px] text-red-700 mt-0.5">The following items are running out of stock and require replenishment:</p>
                  <div className="flex flex-wrap gap-2 mt-2">
                    {lowStockItems.map(item => (
                      <span key={item.id} className="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-bold bg-white border border-red-200 text-red-600 shadow-sm">
                        {item.name} ({item.quantity_in_stock} remaining / Reorder level: {item.reorder_level})
                      </span>
                    ))}
                  </div>
                </div>
              </div>
            );
          })()}

          <div className="p-0">
            {invLoading ? (
              <div className="p-8 text-center text-slate-500 text-sm">Loading inventory...</div>
            ) : inventory.length === 0 ? (
              <div className="p-8 text-center text-slate-500 text-sm">No drugs found in inventory.</div>
            ) : (
              <div className="overflow-x-auto">
                <table className="w-full text-left text-xs">
                  <thead className="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100 uppercase tracking-wider text-[10px]">
                    <tr>
                      <th className="px-4 py-3">Drug Name</th>
                      <th className="px-4 py-3">Code / Batch</th>
                      <th className="px-4 py-3">Category</th>
                      <th className="px-4 py-3 text-right">Stock</th>
                      <th className="px-4 py-3 text-right">Unit Price (₦)</th>
                      <th className="px-4 py-3">Status</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-slate-100">
                    {inventory.map((item) => (
                      <tr key={item.id} className="hover:bg-slate-50/50 transition-colors">
                        <td className="px-4 py-3">
                          <p className="font-bold text-slate-800">{item.name}</p>
                          <p className="text-[10px] text-slate-500">{item.generic_name}</p>
                        </td>
                        <td className="px-4 py-3">
                          <p className="font-semibold text-slate-700">{item.code}</p>
                          <p className="text-[10px] text-slate-500">{item.batch_number}</p>
                        </td>
                        <td className="px-4 py-3 text-slate-600">{item.category}</td>
                        <td className="px-4 py-3 text-right font-bold text-slate-800">{item.quantity_in_stock}</td>
                        <td className="px-4 py-3 text-right font-bold text-slate-800">₦{Number(item.price_per_unit).toLocaleString()}</td>
                        <td className="px-4 py-3">
                          {item.quantity_in_stock <= item.reorder_level ? (
                            <span className="flex items-center gap-1 text-red-600 font-bold text-[10px]">
                              <AlertTriangle size={12} /> Low Stock
                            </span>
                          ) : (
                            <span className="flex items-center gap-1 text-emerald-600 font-bold text-[10px]">
                              <CheckCircle size={12} /> In Stock
                            </span>
                          )}
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            )}
          </div>
        </div>
      )}

      {/* DISPENSE MODAL */}
      {dispenseModalOpen && selectedPrescription && (
        <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
          <div className="bg-white border border-slate-200 rounded-2xl w-full max-w-3xl overflow-hidden shadow-2xl">
            <div className="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
              <div className="flex items-center gap-3">
                <div className="p-2 bg-primary/10 text-primary rounded-lg">
                  <Activity size={20} />
                </div>
                <div>
                  <h3 className="font-bold text-slate-800">Dispense Prescription</h3>
                  <p className="text-[10px] text-slate-500">{selectedPrescription.patient?.first_name} {selectedPrescription.patient?.last_name}</p>
                </div>
              </div>
              <button onClick={() => setDispenseModalOpen(false)} className="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
            </div>
            
            <form onSubmit={handleDispense} className="p-6 max-h-[70vh] overflow-y-auto space-y-6">
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

              <div className="space-y-4">
                {selectedPrescription.items.map((item: any) => (
                  <div key={item.id} className="p-4 bg-slate-50 rounded-xl border border-slate-200">
                    <div className="flex justify-between items-start mb-3">
                      <div>
                        <h4 className="font-bold text-slate-800 text-sm">{item.drug_name}</h4>
                        <p className="text-[10px] text-slate-500 mt-1">{item.dosage} | {item.frequency} | {item.duration_days} days</p>
                      </div>
                      <div className="text-right">
                        <span className="text-[10px] font-bold text-slate-500 uppercase">Prescribed Qty</span>
                        <p className="font-extrabold text-primary text-sm">{item.quantity_prescribed}</p>
                      </div>
                    </div>
                    
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-3 pt-3 border-t border-slate-200">
                      <div>
                        <label className="text-[10px] font-bold text-slate-800">Select Inventory Match *</label>
                        <select
                          required
                          value={dispenseItemsIds[item.id] || ''}
                          onChange={(e) => setDispenseItemsIds({...dispenseItemsIds, [item.id]: parseInt(e.target.value)})}
                          className="w-full mt-1 px-3 py-2 border border-slate-200 rounded-lg text-xs"
                        >
                          <option value="">-- Select from Inventory --</option>
                          {inventory.map(inv => (
                            <option key={inv.id} value={inv.id}>
                              {inv.name} ({inv.quantity_in_stock} in stock)
                            </option>
                          ))}
                        </select>
                      </div>
                      <div>
                        <label className="text-[10px] font-bold text-slate-800">Dispense Quantity *</label>
                        <input
                          type="number"
                          required
                          min="1"
                          value={dispenseQuantities[item.id] || ''}
                          onChange={(e) => setDispenseQuantities({...dispenseQuantities, [item.id]: parseInt(e.target.value)})}
                          className="w-full mt-1 px-3 py-2 border border-slate-200 rounded-lg text-xs"
                        />
                      </div>
                    </div>
                  </div>
                ))}
              </div>

              <div className="pt-4 border-t border-slate-100 flex justify-end gap-3">
                <button
                  type="button"
                  onClick={() => setDispenseModalOpen(false)}
                  className="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-6 py-2 bg-primary hover:bg-primary-dark text-white text-xs font-bold rounded-xl transition-all shadow-md shadow-primary/20"
                >
                  Confirm Dispense
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* ADD DRUG MODAL */}
      {invModalOpen && (
        <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
          <div className="bg-white border border-slate-200 rounded-2xl w-full max-w-2xl overflow-hidden shadow-2xl">
            <div className="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
              <h3 className="font-bold text-slate-800 flex items-center gap-2"><Pill size={18} className="text-primary"/> Add to Inventory</h3>
              <button onClick={() => setInvModalOpen(false)} className="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
            </div>
            
            <form onSubmit={handleAddDrug} className="p-6 max-h-[70vh] overflow-y-auto space-y-6">
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

              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label className="text-[10px] font-bold text-slate-800">Drug Name *</label>
                  <input type="text" required value={dName} onChange={e=>setDName(e.target.value)} className="w-full mt-1 px-3 py-2 border border-slate-200 rounded-lg text-xs" />
                </div>
                <div>
                  <label className="text-[10px] font-bold text-slate-800">Generic Name</label>
                  <input type="text" value={dGeneric} onChange={e=>setDGeneric(e.target.value)} className="w-full mt-1 px-3 py-2 border border-slate-200 rounded-lg text-xs" />
                </div>
                <div>
                  <label className="text-[10px] font-bold text-slate-800">Item Code *</label>
                  <input type="text" required value={dCode} onChange={e=>setDCode(e.target.value)} placeholder="e.g. PCM-500" className="w-full mt-1 px-3 py-2 border border-slate-200 rounded-lg text-xs" />
                </div>
                <div>
                  <label className="text-[10px] font-bold text-slate-800">Category *</label>
                  <select required value={dCategory} onChange={e=>setDCategory(e.target.value)} className="w-full mt-1 px-3 py-2 border border-slate-200 rounded-lg text-xs">
                    <option>Tablet</option>
                    <option>Capsule</option>
                    <option>Syrup</option>
                    <option>Injection</option>
                    <option>Consumable</option>
                  </select>
                </div>
                <div>
                  <label className="text-[10px] font-bold text-slate-800">Batch Number</label>
                  <input type="text" value={dBatch} onChange={e=>setDBatch(e.target.value)} className="w-full mt-1 px-3 py-2 border border-slate-200 rounded-lg text-xs" />
                </div>
                <div>
                  <label className="text-[10px] font-bold text-slate-800">Expiry Date</label>
                  <input type="date" value={dExpiry} onChange={e=>setDExpiry(e.target.value)} className="w-full mt-1 px-3 py-2 border border-slate-200 rounded-lg text-xs" />
                </div>
                <div>
                  <label className="text-[10px] font-bold text-slate-800">Initial Stock *</label>
                  <input type="number" required min="0" value={dStock} onChange={e=>setDStock(e.target.value)} className="w-full mt-1 px-3 py-2 border border-slate-200 rounded-lg text-xs" />
                </div>
                <div>
                  <label className="text-[10px] font-bold text-slate-800">Reorder Level *</label>
                  <input type="number" required min="0" value={dReorder} onChange={e=>setDReorder(e.target.value)} className="w-full mt-1 px-3 py-2 border border-slate-200 rounded-lg text-xs" />
                </div>
                <div>
                  <label className="text-[10px] font-bold text-slate-800">Unit Price (₦) *</label>
                  <input type="number" required min="0" step="0.01" value={dPrice} onChange={e=>setDPrice(e.target.value)} className="w-full mt-1 px-3 py-2 border border-slate-200 rounded-lg text-xs" />
                </div>
              </div>

              <div className="pt-4 border-t border-slate-100 flex justify-end gap-3">
                <button type="button" onClick={() => setInvModalOpen(false)} className="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">Cancel</button>
                <button type="submit" className="px-6 py-2 bg-primary hover:bg-primary-dark text-white text-xs font-bold rounded-xl transition-all shadow-md shadow-primary/20">Add Drug</button>
              </div>
            </form>
          </div>
        </div>
      )}

    </div>
  );
};
