import React, { useEffect, useState } from 'react';
import api from '../services/api';
import { CreditCard, CheckCircle, ShieldAlert } from 'lucide-react';

export const Billing: React.FC = () => {
  const [invoices, setInvoices] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [detailInvoice, setDetailInvoice] = useState<any>(null);
  const [modalOpen, setModalOpen] = useState(false);

  // Payment Form fields
  const [amount, setAmount] = useState('');
  const [method, setMethod] = useState('Cash');
  const [ref, setRef] = useState('');
  
  const [success, setSuccess] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);

  const fetchPendingInvoices = async () => {
    setLoading(true);
    try {
      const res = await api.get('/billing/invoices/pending');
      setInvoices(res.data.invoices);
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchPendingInvoices();
  }, []);

  const handleOpenPaymentModal = async (id: number) => {
    try {
      const res = await api.get(`/billing/invoices/${id}`);
      setDetailInvoice(res.data.invoice);
      setAmount((res.data.invoice.total_amount - res.data.invoice.discount_amount - res.data.invoice.paid_amount).toString());
      setMethod('Cash');
      setRef('');
      setSuccess(null);
      setError(null);
      setModalOpen(true);
    } catch (err) {
      alert('Error fetching invoice details.');
    }
  };

  const handleCheckout = async (e: React.FormEvent) => {
    e.preventDefault();
    setSuccess(null);
    setError(null);

    const payload = {
      amount: parseFloat(amount),
      payment_method: method,
      transaction_reference: ref || null
    };

    try {
      await api.post(`/billing/invoices/${detailInvoice.id}/pay`, payload);
      setSuccess('Payment recorded successfully! Receipt generated.');
      fetchPendingInvoices();
      
      setTimeout(() => {
        setModalOpen(false);
        setSuccess(null);
      }, 1500);
    } catch (err: any) {
      setError(err.response?.data?.message || 'Payment collection failed.');
    }
  };

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-xl font-bold text-slate-800 dark:text-white flex items-center gap-2">
          <CreditCard className="text-primary" /> Patient Invoices & Cashier Desk
        </h1>
        <p className="text-xs text-slate-800 dark:text-slate-200">Cashier portal to receive payments (Cash, POS, Transfer, Insurance) and issue governmental receipts</p>
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
                  <th className="py-3.5 px-6 font-bold">Invoice Date</th>
                  <th className="py-3.5 px-6 font-bold">Patient Name</th>
                  <th className="py-3.5 px-6 font-bold">Total Bill</th>
                  <th className="py-3.5 px-6 font-bold">Amount Paid</th>
                  <th className="py-3.5 px-6 font-bold">Status</th>
                  <th className="py-3.5 px-6 font-bold text-right">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                {invoices.length > 0 ? (
                  invoices.map((inv) => (
                    <tr key={inv.id} className="hover:bg-slate-50 dark:hover:bg-slate-900/20 transition-all">
                      <td className="py-3.5 px-6">{new Date(inv.created_at).toLocaleDateString()}</td>
                      <td className="py-3.5 px-6 font-bold text-slate-900 dark:text-white">
                        {inv.patient.first_name} {inv.patient.last_name}
                      </td>
                      <td className="py-3.5 px-6 font-bold">₦{parseFloat(inv.total_amount).toLocaleString()}</td>
                      <td className="py-3.5 px-6 text-slate-800 dark:text-slate-200">₦{parseFloat(inv.paid_amount).toLocaleString()}</td>
                      <td className="py-3.5 px-6">
                        <span className={`px-2 py-0.5 text-[9px] font-bold rounded-full border ${
                          inv.status === 'paid'
                            ? 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20'
                            : inv.status === 'partially_paid'
                            ? 'bg-blue-500/10 text-blue-500 border-blue-500/20'
                            : 'bg-red-500/10 text-red-500 border-red-500/20'
                        }`}>
                          {inv.status}
                        </span>
                      </td>
                      <td className="py-3.5 px-6 text-right">
                        <button 
                          onClick={() => handleOpenPaymentModal(inv.id)}
                          className="bg-primary hover:bg-primary-dark text-white px-3 py-1.5 rounded-lg font-bold transition-all shadow-sm shadow-primary/10 cursor-pointer"
                        >
                          Checkout
                        </button>
                      </td>
                    </tr>
                  ))
                ) : (
                  <tr>
                    <td colSpan={6} className="py-8 text-center text-slate-800 dark:text-slate-200 text-sm">No pending patient invoices found.</td>
                  </tr>
                )}
              </tbody>
            </table>
          </div>
        )}
      </div>

      {/* Checkout Invoice Modal */}
      {modalOpen && detailInvoice && (
        <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
          <div className="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-2xl overflow-hidden shadow-2xl animate-in fade-in-50 zoom-in-95">
            <div className="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50">
              <h3 className="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
                Checkout Invoice: #{detailInvoice.id}
              </h3>
              <button 
                onClick={() => setModalOpen(false)}
                className="text-slate-800 dark:text-slate-200 hover:text-slate-600 text-lg cursor-pointer"
              >
                &times;
              </button>
            </div>
            
            <div className="p-6 grid grid-cols-1 md:grid-cols-2 gap-6 max-h-[70vh] overflow-y-auto">
              {/* Invoice lines summary */}
              <div className="space-y-4">
                <h4 className="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Line Items</h4>
                <div className="bg-slate-50 dark:bg-slate-900/40 p-4 rounded-xl border border-slate-200 dark:border-slate-800/80 space-y-3 text-xs">
                  {detailInvoice.items.map((item: any) => (
                    <div key={item.id} className="flex justify-between border-b border-slate-100 dark:border-slate-800/80 pb-2">
                      <div>
                        <span className="font-semibold text-slate-800 dark:text-slate-200">{item.item_name}</span>
                        <div className="text-[10px] text-slate-800 dark:text-slate-200">Qty: {item.quantity} @ ₦{parseFloat(item.unit_price).toLocaleString()}</div>
                      </div>
                      <span className="font-bold text-slate-800 dark:text-slate-200">₦{parseFloat(item.total_price).toLocaleString()}</span>
                    </div>
                  ))}
                  <div className="flex justify-between font-extrabold text-sm pt-2 text-slate-900 dark:text-white border-t border-slate-200 dark:border-slate-800">
                    <span>Total Amount</span>
                    <span>₦{parseFloat(detailInvoice.total_amount).toLocaleString()}</span>
                  </div>
                </div>
              </div>

              {/* Checkout processing form */}
              <form onSubmit={handleCheckout} className="space-y-4">
                <h4 className="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Payment Collection</h4>

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
                  <label className="text-[10px] font-bold text-slate-800 dark:text-slate-200">Checkout Amount *</label>
                  <input
                    type="number"
                    required
                    value={amount}
                    onChange={(e) => setAmount(e.target.value)}
                    className="w-full mt-1.5 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 rounded-xl text-xs font-bold text-slate-800 dark:text-slate-100"
                  />
                </div>

                <div>
                  <label className="text-[10px] font-bold text-slate-800 dark:text-slate-200">Payment Method *</label>
                  <select
                    value={method}
                    onChange={(e) => setMethod(e.target.value)}
                    className="w-full mt-1.5 px-3 py-2.5 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 rounded-xl text-xs text-slate-800 dark:text-slate-100"
                  >
                    <option>Cash</option>
                    <option>POS</option>
                    <option>Bank Transfer</option>
                    <option>Insurance</option>
                  </select>
                </div>

                <div>
                  <label className="text-[10px] font-bold text-slate-800 dark:text-slate-200">Transaction Reference / Slip ID</label>
                  <input
                    type="text"
                    value={ref}
                    onChange={(e) => setRef(e.target.value)}
                    placeholder="e.g. TXN-29302-39"
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
                    Post Checkout
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};



