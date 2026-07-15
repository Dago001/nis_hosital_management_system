@extends('layouts.app')

@section('title', 'Pharmacy & Inventory - NIS Medical Services Portal')

@section('content')
<div class="space-y-6">
    <!-- Title & Tab Selection -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-white flex items-center gap-2">
                <i data-lucide="pill" class="text-emerald-600"></i> Pharmacy & Inventory Desk
            </h1>
            <p class="text-xs text-slate-800 dark:text-slate-200 font-sans">Dispense patient prescriptions, perform medication costings, and monitor critical drug inventory levels</p>
        </div>
        <div class="flex items-center gap-1 bg-slate-250 dark:bg-slate-800 rounded-xl p-1 shadow-inner">
            <button onclick="switchTab('dispensary')" id="tab-btn-dispensary" class="px-4 py-2 text-xs font-bold rounded-lg transition-colors cursor-pointer bg-white dark:bg-slate-900 text-emerald-600 shadow-sm">
                Dispensary Queue
            </button>
            <button onclick="switchTab('inventory')" id="tab-btn-inventory" class="px-4 py-2 text-xs font-bold rounded-lg transition-colors cursor-pointer text-slate-500 hover:text-slate-700 dark:text-slate-300 dark:hover:text-white">
                Drug Inventory
            </button>
        </div>
    </div>

    <!-- ================= TAB: DISPENSARY ================= -->
    <div id="tab-dispensary" class="space-y-6">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-200 border-b border-slate-100 dark:border-slate-800">
                        <tr>
                            <th class="py-3.5 px-6 font-bold">Patient Details</th>
                            <th class="py-3.5 px-6 font-bold">Prescribing Doctor</th>
                            <th class="py-3.5 px-6 font-bold">Request Date</th>
                            <th class="py-3.5 px-6 font-bold">Status</th>
                            <th class="py-3.5 px-6 font-bold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="prescriptions-table-body" class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-350">
                        <tr>
                            <td colSpan="5" class="py-8 text-center text-slate-800 dark:text-slate-200 text-sm">
                                <div class="w-6 h-6 border-2 border-emerald-600 border-t-transparent rounded-full animate-spin mx-auto"></div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ================= TAB: INVENTORY ================= -->
    <div id="tab-inventory" class="hidden space-y-6">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
            <div class="p-4 border-b border-slate-100 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-900 flex justify-between items-center gap-4">
                <div class="relative w-64">
                    <input type="text" id="inv-search" oninput="handleInvSearch(this.value)" placeholder="Search drugs..." 
                           class="w-full pl-9 pr-4 py-2 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs focus:ring-1 focus:ring-emerald-500 focus:outline-none text-slate-800 dark:text-slate-100">
                    <i data-lucide="search" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400 w-4 h-4"></i>
                </div>
                <div id="add-drug-container" class="hidden">
                    <button onclick="openAddDrugModal()" class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-xl text-xs font-bold transition shadow-md shadow-emerald-650/10">
                        <i data-lucide="plus" class="w-4 h-4"></i> Add Drug
                    </button>
                </div>
            </div>

            <!-- Critical Low Stock Alerts Banner -->
            <div id="inventory-low-stock-banner" class="hidden mx-4 mt-4 bg-red-500/5 dark:bg-red-500/10 border border-red-500/20 p-4 rounded-2xl flex items-start gap-3 text-red-700">
                <i data-lucide="alert-triangle" class="text-red-500 shrink-0 mt-0.5 w-4.5 h-4.5"></i>
                <div class="flex-1">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-red-900 dark:text-red-400">Critical Stock Warning</h4>
                    <p class="text-[11px] text-red-800 dark:text-red-300 mt-0.5">The following items are running below their reorder threshold:</p>
                    <div id="low-stock-alerts-list" class="flex flex-wrap gap-2 mt-2"></div>
                </div>
            </div>

            <div class="overflow-x-auto mt-4">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-950 text-slate-850 dark:text-slate-200 border-b border-slate-100 dark:border-slate-800">
                        <tr>
                            <th class="py-3.5 px-6 font-bold">Drug Details</th>
                            <th class="py-3.5 px-6 font-bold">Code & Batch</th>
                            <th class="py-3.5 px-6 font-bold">Category</th>
                            <th class="py-3.5 px-6 font-bold text-right">In Stock</th>
                            <th class="py-3.5 px-6 font-bold text-right">Unit Price</th>
                            <th class="py-3.5 px-6 font-bold">Status</th>
                        </tr>
                    </thead>
                    <tbody id="inventory-table-body" class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-350">
                        <!-- Loaded by JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Cost & Dispense Prescription Modal -->
<div id="dispense-modal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 w-full max-w-2xl shadow-2xl relative my-8">
        <h3 class="text-base font-bold text-slate-800 dark:text-white mb-2 flex items-center gap-2">
            <i data-lucide="activity" class="text-emerald-500"></i> Process Patient Prescription File
        </h3>
        <p class="text-[10px] text-slate-500 mb-4" id="disp-patient-name"></p>

        <form id="dispense-form" onsubmit="handleDispenseSubmit(event)" class="space-y-4">
            <div class="space-y-3 max-h-72 overflow-y-auto pr-2" id="disp-items-container">
                <!-- Injected dynamically -->
            </div>

            <!-- Costing Warning / Invoice Paid Check Info -->
            <div id="cost-payment-status-info" class="p-3.5 rounded-xl text-xs flex items-center gap-2">
                <!-- Injected dynamically -->
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800/80">
                <button type="button" onclick="closeDispenseModal()" class="px-4 py-2 text-xs font-semibold text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition">Cancel</button>
                <button type="submit" id="disp-confirm-btn" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 text-xs font-bold rounded-xl transition shadow-md"></button>
            </div>
        </form>
    </div>
</div>

<!-- Add Drug to Inventory Modal -->
<div id="add-drug-modal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 w-full max-w-xl shadow-2xl relative my-8">
        <h3 class="text-base font-bold text-slate-800 dark:text-white mb-4 flex items-center gap-2">
            <i data-lucide="pill" class="text-emerald-500"></i> Restock / Add Drug to Inventory
        </h3>
        
        <form id="add-drug-form" onsubmit="handleAddDrugSubmit(event)" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1">Select Drug / Injection *</label>
                    <select id="d_name_select" onchange="handleDrugSelectChange(this.value)" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        <option value="">-- Choose drug/injection preset --</option>
                        <optgroup label="Tablets & Capsules">
                            <option value="Paracetamol 500mg">Paracetamol 500mg</option>
                            <option value="Ibuprofen 400mg">Ibuprofen 400mg</option>
                            <option value="Amoxicillin 500mg">Amoxicillin 500mg</option>
                            <option value="Ciprofloxacin 500mg">Ciprofloxacin 500mg</option>
                            <option value="Metronidazole 400mg">Metronidazole 400mg</option>
                            <option value="Artemether-Lumefantrine 80/480mg">Artemether-Lumefantrine 80/480mg</option>
                            <option value="Omeprazole 20mg">Omeprazole 20mg</option>
                            <option value="Amlodipine 5mg">Amlodipine 5mg</option>
                            <option value="Metformin 500mg">Metformin 500mg</option>
                        </optgroup>
                        <optgroup label="Injections">
                            <option value="Injection Artesunate 60mg">Injection Artesunate 60mg</option>
                            <option value="Injection Diclofenac 75mg/3ml">Injection Diclofenac 75mg/3ml</option>
                            <option value="Injection Ceftriaxone 1g">Injection Ceftriaxone 1g</option>
                            <option value="Injection Gentamicin 80mg">Injection Gentamicin 80mg</option>
                            <option value="Injection Tramadol 50mg">Injection Tramadol 50mg</option>
                            <option value="Injection Hydrocortisone 100mg">Injection Hydrocortisone 100mg</option>
                            <option value="Injection Oxytocin 10 IU">Injection Oxytocin 10 IU</option>
                        </optgroup>
                        <optgroup label="Syrups & Suspensions">
                            <option value="Paracetamol Syrup 125mg/5ml">Paracetamol Syrup 125mg/5ml</option>
                            <option value="Amoxicillin Suspension 125mg/5ml">Amoxicillin Suspension 125mg/5ml</option>
                            <option value="Metronidazole Suspension 200mg/5ml">Metronidazole Suspension 200mg/5ml</option>
                        </optgroup>
                        <optgroup label="Infusions & Fluids">
                            <option value="IV Normal Saline 500ml">IV Normal Saline 500ml</option>
                            <option value="IV Dextrose 5% 500ml">IV Dextrose 5% 500ml</option>
                            <option value="IV Ringers Lactate 500ml">IV Ringers Lactate 500ml</option>
                        </optgroup>
                        <option value="custom">-- Other / Custom Drug --</option>
                    </select>
                </div>
                <div id="custom-drug-name-container" class="hidden">
                    <label class="block text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1">Custom Drug Name *</label>
                    <input type="text" id="d_name_custom" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-205 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1">Generic Name</label>
                    <input type="text" id="d_generic" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1">Item Code *</label>
                    <input type="text" id="d_code" placeholder="PCM-500" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1">Category *</label>
                    <select id="d_category" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        <option>Tablet</option>
                        <option>Capsule</option>
                        <option>Syrup</option>
                        <option>Injection</option>
                        <option>Consumable</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1">Batch Number *</label>
                    <input type="text" id="d_batch" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1">Expiry Date *</label>
                    <input type="date" id="d_expiry" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1">Initial Stock Quantity *</label>
                    <input type="number" id="d_stock" required min="0" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1">Reorder Level *</label>
                    <input type="number" id="d_reorder" required min="0" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1">Unit Price (₦) *</label>
                    <input type="number" step="0.01" id="d_price" required min="0" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800/80">
                <button type="button" onclick="closeAddDrugModal()" class="px-4 py-2 text-xs font-semibold text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition">Cancel</button>
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 text-xs font-bold rounded-xl transition shadow-md">Add Item</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let activeTab = 'dispensary';
    let inventoryList = [];
    let prescriptionsList = [];
    let selectedPrescription = null;
    let invSearchTimeout = null;

    function switchTab(tab) {
        activeTab = tab;
        
        const btnDisp = document.getElementById('tab-btn-dispensary');
        const btnInv = document.getElementById('tab-btn-inventory');
        const viewDisp = document.getElementById('tab-dispensary');
        const viewInv = document.getElementById('tab-inventory');

        if (tab === 'dispensary') {
            btnDisp.className = "px-4 py-2 text-xs font-bold rounded-lg transition-colors cursor-pointer bg-white dark:bg-slate-900 text-emerald-600 shadow-sm";
            btnInv.className = "px-4 py-2 text-xs font-bold rounded-lg transition-colors cursor-pointer text-slate-500 hover:text-slate-700 dark:text-slate-300 dark:hover:text-white";
            viewDisp.classList.remove('hidden');
            viewInv.classList.add('hidden');
            loadPrescriptions();
        } else {
            btnInv.className = "px-4 py-2 text-xs font-bold rounded-lg transition-colors cursor-pointer bg-white dark:bg-slate-900 text-emerald-600 shadow-sm";
            btnDisp.className = "px-4 py-2 text-xs font-bold rounded-lg transition-colors cursor-pointer text-slate-500 hover:text-slate-700 dark:text-slate-300 dark:hover:text-white";
            viewInv.classList.remove('hidden');
            viewDisp.classList.add('hidden');
            loadInventory();
        }
    }

    async function loadInventory(query = '') {
        const tbody = document.getElementById('inventory-table-body');
        try {
            const res = await api.get(`/pharmacy/inventory?search=${encodeURIComponent(query)}`);
            inventoryList = res.inventory;

            // Render warning banner if low stock items exist
            const lowStock = inventoryList.filter(i => i.quantity_in_stock <= i.reorder_level);
            const banner = document.getElementById('inventory-low-stock-banner');
            const listEl = document.getElementById('low-stock-alerts-list');

            if (lowStock.length > 0) {
                banner.classList.remove('hidden');
                listEl.innerHTML = lowStock.map(item => `
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[9px] font-bold bg-white dark:bg-slate-900 border border-red-250 text-red-500 shadow-sm">
                        ${item.name} (${item.quantity_in_stock} remaining / Reorder level: ${item.reorder_level})
                    </span>
                `).join('');
            } else {
                banner.classList.add('hidden');
            }

            if (inventoryList.length > 0) {
                tbody.innerHTML = inventoryList.map(item => `
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/20 transition">
                        <td class="py-3.5 px-6">
                            <p class="font-bold text-slate-800 dark:text-white">${item.name}</p>
                            <p class="text-[9px] text-slate-500">${item.generic_name || 'Generic'}</p>
                        </td>
                        <td class="py-3.5 px-6">
                            <p class="font-semibold text-slate-700 dark:text-slate-350">${item.code}</p>
                            <p class="text-[9px] text-slate-500">${item.batch_number}</p>
                        </td>
                        <td class="py-3.5 px-6 text-slate-800 dark:text-slate-200">${item.category}</td>
                        <td class="py-3.5 px-6 text-right font-bold text-slate-800 dark:text-slate-250">${item.quantity_in_stock}</td>
                        <td class="py-3.5 px-6 text-right font-bold text-slate-800 dark:text-slate-250">₦${Number(item.price_per_unit).toLocaleString()}</td>
                        <td class="py-3.5 px-6">
                            ${item.quantity_in_stock <= item.reorder_level 
                                ? `<span class="flex items-center gap-1 text-red-500 font-bold text-[9px]"><i data-lucide="alert-triangle" class="w-3 h-3"></i> Low Stock</span>`
                                : `<span class="flex items-center gap-1 text-emerald-650 font-bold text-[9px]"><i data-lucide="check-circle" class="w-3 h-3"></i> In Stock</span>`
                            }
                        </td>
                    </tr>
                `).join('');
                lucide.createIcons();
            } else {
                tbody.innerHTML = `<tr><td colSpan="6" class="py-8 text-center text-slate-500 text-xs">No drugs registered in inventory.</td></tr>`;
            }
        } catch (err) {
            tbody.innerHTML = `<tr><td colSpan="6" class="py-8 text-center text-red-500 text-xs">Failed to load drug inventory.</td></tr>`;
        }
    }

    function handleInvSearch(val) {
        clearTimeout(invSearchTimeout);
        invSearchTimeout = setTimeout(() => {
            loadInventory(val);
        }, 300);
    }

    async function loadPrescriptions() {
        const tbody = document.getElementById('prescriptions-table-body');
        try {
            const res = await api.get('/pharmacy/prescriptions');
            prescriptionsList = res.prescriptions;

            if (prescriptionsList.length > 0) {
                tbody.innerHTML = prescriptionsList.map(pres => {
                    let statusClass = 'bg-slate-100 text-slate-700';
                    let actionText = 'Dispense';
                    let actionClass = 'bg-emerald-650 hover:bg-emerald-700 text-white';

                    if (pres.status === 'pending') {
                        statusClass = 'bg-amber-500/10 text-amber-600 border border-amber-500/20';
                        actionText = 'Cost Medication';
                        actionClass = 'bg-amber-600 hover:bg-amber-700 text-white';
                    } else if (pres.status === 'costed') {
                        statusClass = 'bg-indigo-500/10 text-indigo-605 border border-indigo-500/20';
                        actionText = 'Awaiting Payment';
                        actionClass = 'bg-slate-100 text-slate-500 border border-slate-200 cursor-not-allowed';
                    } else if (pres.status === 'paid') {
                        statusClass = 'bg-blue-500/10 text-blue-600 border border-blue-500/20';
                        actionText = 'Dispense Drugs';
                        actionClass = 'bg-emerald-600 hover:bg-emerald-700 text-white';
                    } else if (pres.status === 'dispensed') {
                        statusClass = 'bg-emerald-500/10 text-emerald-600 border border-emerald-500/20';
                        actionText = 'View Details';
                        actionClass = 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-200 hover:bg-slate-200';
                    }

                    return `
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/20 transition">
                            <td class="py-3.5 px-6">
                                <span class="font-bold text-slate-850 dark:text-white">${pres.patient.first_name} ${pres.patient.last_name}</span>
                                <div class="text-[9px] text-slate-500">Hospital Code: ${pres.patient.immigration_service_number}</div>
                            </td>
                            <td class="py-3.5 px-6">
                                <span class="font-semibold text-slate-800 dark:text-slate-200">Dr. ${pres.doctor?.full_name || 'Staff'}</span>
                                <div class="text-[9px] text-slate-500">Clinical Unit: ${pres.visit?.department?.name || 'GOPD'}</div>
                            </td>
                            <td class="py-3.5 px-6 text-slate-800 dark:text-slate-200">${new Date(pres.created_at).toLocaleDateString()}</td>
                            <td class="py-3.5 px-6">
                                <span class="px-2.5 py-0.5 text-[9px] font-bold rounded-full uppercase ${statusClass}">
                                    ${pres.status}
                                </span>
                            </td>
                            <td class="py-3.5 px-6 text-right">
                                <button onclick="openDispenseModal(${pres.id})" class="text-[10px] font-bold px-3 py-1.5 rounded-xl transition ${actionClass}">
                                    ${actionText}
                                </button>
                            </td>
                        </tr>
                    `;
                }).join('');
            } else {
                tbody.innerHTML = `<tr><td colspan="5" class="py-8 text-center text-slate-500 text-xs">No patient prescription records found.</td></tr>`;
            }
        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="5" class="py-8 text-center text-red-500 text-xs">Failed to load prescriptions.</td></tr>`;
        }
    }

    async function openDispenseModal(id) {
        selectedPrescription = prescriptionsList.find(p => p.id === id);
        if (!selectedPrescription) return;

        // Ensure we have loaded full inventory details for drop-downs
        if (inventoryList.length === 0) {
            const res = await api.get('/pharmacy/inventory');
            inventoryList = res.inventory;
        }

        document.getElementById('disp-patient-name').innerText = `Patient: ${selectedPrescription.patient.first_name} ${selectedPrescription.patient.last_name} | Code: ${selectedPrescription.patient.immigration_service_number}`;

        const container = document.getElementById('disp-items-container');
        const statusPanel = document.getElementById('cost-payment-status-info');
        const confirmBtn = document.getElementById('disp-confirm-btn');

        // Render Prescription Items
        if (selectedPrescription.status === 'pending') {
            // COSTING SCREEN
            statusPanel.className = "p-3.5 rounded-xl text-xs flex items-center gap-2 bg-amber-500/10 border border-amber-500/25 text-amber-600";
            statusPanel.innerHTML = `<i data-lucide="info" class="w-4 h-4"></i> <span>Prescription must be costed and forwarded to cashier. Cashier payment validates dispense release.</span>`;
            confirmBtn.innerText = "Forward to Cashier";
            confirmBtn.disabled = false;
            confirmBtn.className = "bg-amber-600 hover:bg-amber-700 text-white px-5 py-2 text-xs font-bold rounded-xl transition shadow-md";

            container.innerHTML = selectedPrescription.items.map(item => {
                // Find matching drug in inventory by name or prefix matching
                const matches = inventoryList.filter(inv => inv.name.toLowerCase().includes(item.drug_name.toLowerCase()) || item.drug_name.toLowerCase().includes(inv.name.toLowerCase()));
                const options = matches.map(inv => `<option value="${inv.id}" data-price="${inv.price_per_unit}">${inv.name} (₦${Number(inv.price_per_unit).toLocaleString()} - ${inv.quantity_in_stock} stock)</option>`).join('');

                return `
                    <div class="p-4 bg-slate-50 dark:bg-slate-950 rounded-xl border border-slate-200 dark:border-slate-800">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <h4 class="font-bold text-slate-800 dark:text-white text-xs">${item.drug_name}</h4>
                                <p class="text-[9px] text-slate-500">${item.dosage} | ${item.frequency} | ${item.duration_days} days</p>
                            </div>
                            <div class="text-right">
                                <span class="text-[9px] font-bold text-slate-500 block uppercase">Prescribed Qty</span>
                                <p class="font-extrabold text-emerald-600 text-xs">${item.quantity_prescribed}</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-3 pt-3 border-t border-slate-150 dark:border-slate-850">
                            <div>
                                <label class="text-[9px] font-bold text-slate-800 dark:text-slate-200 uppercase">Inventory Match *</label>
                                <select required name="match-${item.id}" onchange="updateCostingPrice(${item.id}, this)" class="w-full mt-1 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 rounded-lg text-xs text-slate-800 dark:text-slate-100">
                                    <option value="">-- Choose inventory match --</option>
                                    ${options}
                                    ${inventoryList.map(inv => `<option value="${inv.id}" data-price="${inv.price_per_unit}">${inv.name} (₦${Number(inv.price_per_unit).toLocaleString()} - ${inv.quantity_in_stock} stock)</option>`).join('')}
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="text-[9px] font-bold text-slate-800 dark:text-slate-200 uppercase">Unit Cost (₦) *</label>
                                    <input type="number" step="0.01" required name="price-${item.id}" id="price-${item.id}" class="w-full mt-1 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 rounded-lg text-xs text-slate-800 dark:text-slate-100">
                                </div>
                                <div>
                                    <label class="text-[9px] font-bold text-slate-800 dark:text-slate-200 uppercase">Quantity *</label>
                                    <input type="number" required min="1" name="qty-${item.id}" value="${item.quantity_prescribed}" class="w-full mt-1 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 rounded-lg text-xs text-slate-800 dark:text-slate-100">
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');

        } else if (selectedPrescription.status === 'costed') {
            // COSTED, AWAITING PAYMENT
            statusPanel.className = "p-3.5 rounded-xl text-xs flex items-center gap-2 bg-indigo-500/10 border border-indigo-500/25 text-indigo-650";
            statusPanel.innerHTML = `<i data-lucide="info" class="w-4 h-4"></i> <span>Medication costed! Invoice code created. Awaiting payment collection from cashier.</span>`;
            confirmBtn.innerText = "Awaiting Cashier Payment";
            confirmBtn.disabled = true;
            confirmBtn.className = "bg-slate-100 text-slate-400 border border-slate-200 px-5 py-2 text-xs font-bold rounded-xl cursor-not-allowed";

            container.innerHTML = selectedPrescription.items.map(item => `
                <div class="p-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl flex justify-between items-center text-xs">
                    <div>
                        <h4 class="font-bold text-slate-800 dark:text-white">${item.drug_name}</h4>
                        <p class="text-[9px] text-slate-500">${item.dosage} | ${item.frequency}</p>
                    </div>
                    <div class="text-right">
                        <span class="text-[9px] font-bold text-slate-500 block">Costed Qty</span>
                        <p class="font-bold text-slate-850 dark:text-white">${item.quantity_prescribed} units</p>
                    </div>
                </div>
            `).join('');

        } else if (selectedPrescription.status === 'paid' || selectedPrescription.status === 'dispensed') {
            // PAID AND READY TO DISPENSE
            statusPanel.className = "p-3.5 rounded-xl text-xs flex items-center gap-2 bg-emerald-500/10 border border-emerald-500/25 text-emerald-650";
            statusPanel.innerHTML = `<i data-lucide="check-circle" class="w-4 h-4"></i> <span>Payment verified by cashier! Ready to release drugs.</span>`;
            
            if (selectedPrescription.status === 'paid') {
                confirmBtn.innerText = "Confirm Drug Dispense";
                confirmBtn.disabled = false;
                confirmBtn.className = "bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 text-xs font-bold rounded-xl transition shadow-md";
            } else {
                confirmBtn.innerText = "Already Dispensed";
                confirmBtn.disabled = true;
                confirmBtn.className = "bg-slate-150 text-slate-500 px-5 py-2 text-xs font-bold rounded-xl cursor-not-allowed";
            }

            container.innerHTML = selectedPrescription.items.map(item => {
                const matches = inventoryList.filter(inv => inv.name.toLowerCase().includes(item.drug_name.toLowerCase()) || item.drug_name.toLowerCase().includes(inv.name.toLowerCase()));
                const options = matches.map(inv => `<option value="${inv.id}">${inv.name} (${inv.quantity_in_stock} stock)</option>`).join('');

                return `
                    <div class="p-4 bg-slate-50 dark:bg-slate-950 rounded-xl border border-slate-200 dark:border-slate-800 text-xs">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <h4 class="font-bold text-slate-800 dark:text-white">${item.drug_name}</h4>
                                <p class="text-[9px] text-slate-500">${item.dosage} | ${item.frequency}</p>
                            </div>
                            <div class="text-right">
                                <span class="text-[9px] font-bold text-slate-550 block">Quantity</span>
                                <p class="font-bold text-slate-850 dark:text-white">${item.quantity_prescribed} units</p>
                            </div>
                        </div>
                        ${selectedPrescription.status === 'paid' ? `
                            <div class="mt-2 pt-2 border-t border-slate-150 dark:border-slate-850 grid grid-cols-2 gap-4">
                                <div>
                                    <label class="text-[9px] font-bold text-slate-800 dark:text-slate-200 uppercase">Confirm Inventory Item</label>
                                    <select required name="disp-match-${item.id}" class="w-full mt-1 px-3 py-2 border border-slate-200 dark:border-slate-850 bg-white dark:bg-slate-900 rounded-lg text-xs">
                                        ${options}
                                        ${inventoryList.map(inv => `<option value="${inv.id}">${inv.name} (${inv.quantity_in_stock} stock)</option>`).join('')}
                                    </select>
                                </div>
                                <div>
                                    <label class="text-[9px] font-bold text-slate-800 dark:text-slate-200 uppercase">Confirm Qty</label>
                                    <input type="number" required min="1" value="${item.quantity_prescribed}" name="disp-qty-${item.id}" class="w-full mt-1 px-3 py-2 border border-slate-200 dark:border-slate-850 bg-white dark:bg-slate-900 rounded-lg text-xs">
                                </div>
                            </div>
                        ` : ''}
                    </div>
                `;
            }).join('');
        }

        document.getElementById('dispense-modal').classList.remove('hidden');
        lucide.createIcons();
    }

    function updateCostingPrice(itemId, select) {
        const option = select.options[select.selectedIndex];
        const price = option.getAttribute('data-price');
        const priceInput = document.getElementById(`price-${itemId}`);
        if (priceInput && price) {
            priceInput.value = price;
        }
    }

    function closeDispenseModal() {
        document.getElementById('dispense-modal').classList.add('hidden');
    }

    async function handleDispenseSubmit(e) {
        e.preventDefault();
        if (!selectedPrescription) return;

        const confirmBtn = document.getElementById('disp-confirm-btn');
        confirmBtn.disabled = true;

        if (selectedPrescription.status === 'pending') {
            // SEND COSTING POST
            const items = selectedPrescription.items.map(item => {
                const pharmacyItemId = document.querySelector(`[name="match-${item.id}"]`).value;
                const price = document.getElementById(`price-${item.id}`).value;
                const qty = document.querySelector(`[name="qty-${item.id}"]`).value;

                return {
                    prescription_item_id: item.id,
                    pharmacy_item_id: parseInt(pharmacyItemId),
                    price_per_unit: parseFloat(price),
                    quantity: parseInt(qty)
                };
            });

            try {
                await api.post(`/pharmacy/prescriptions/${selectedPrescription.id}/cost`, { items });
                alert('Prescription costed successfully and invoice forwarded to cashier!');
                closeDispenseModal();
                loadPrescriptions();
            } catch (err) {
                alert(err.message || 'Failed to cost prescription.');
                confirmBtn.disabled = false;
            }

        } else if (selectedPrescription.status === 'paid') {
            // SEND DISPENSE POST
            const dispensedItems = selectedPrescription.items.map(item => {
                const pharmacyItemId = document.querySelector(`[name="disp-match-${item.id}"]`).value;
                const qty = document.querySelector(`[name="disp-qty-${item.id}"]`).value;

                return {
                    prescription_item_id: item.id,
                    pharmacy_item_id: parseInt(pharmacyItemId),
                    quantity: parseInt(qty)
                };
            });

            try {
                await api.post(`/pharmacy/prescriptions/${selectedPrescription.id}/dispense`, { dispensed_items: dispensedItems });
                alert('Medication fully dispensed and inventory stock updated!');
                closeDispenseModal();
                loadPrescriptions();
            } catch (err) {
                alert(err.message || 'Dispensing failed. Check inventory stock levels.');
                confirmBtn.disabled = false;
            }
        }
    }

    // Standard drug & injection presets database mapping
    const STANDARD_DRUGS_PRESETS = {
        "Paracetamol 500mg": { category: "Tablet", code: "PCM-500", generic: "Paracetamol" },
        "Ibuprofen 400mg": { category: "Tablet", code: "IBU-400", generic: "Ibuprofen" },
        "Amoxicillin 500mg": { category: "Capsule", code: "AMX-500", generic: "Amoxicillin" },
        "Ciprofloxacin 500mg": { category: "Tablet", code: "CIP-500", generic: "Ciprofloxacin" },
        "Metronidazole 400mg": { category: "Tablet", code: "MET-400", generic: "Metronidazole" },
        "Artemether-Lumefantrine 80/480mg": { category: "Tablet", code: "ACT-80", generic: "Artemether + Lumefantrine" },
        "Omeprazole 20mg": { category: "Capsule", code: "OMP-20", generic: "Omeprazole" },
        "Amlodipine 5mg": { category: "Tablet", code: "AML-5", generic: "Amlodipine" },
        "Metformin 500mg": { category: "Tablet", code: "MTF-500", generic: "Metformin" },
        
        "Injection Artesunate 60mg": { category: "Injection", code: "INJ-ART", generic: "Artesunate" },
        "Injection Diclofenac 75mg/3ml": { category: "Injection", code: "INJ-DIC", generic: "Diclofenac Sodium" },
        "Injection Ceftriaxone 1g": { category: "Injection", code: "INJ-CEF", generic: "Ceftriaxone" },
        "Injection Gentamicin 80mg": { category: "Injection", code: "INJ-GEN", generic: "Gentamicin" },
        "Injection Tramadol 50mg": { category: "Injection", code: "INJ-TRA", generic: "Tramadol" },
        "Injection Hydrocortisone 100mg": { category: "Injection", code: "INJ-HYD", generic: "Hydrocortisone" },
        "Injection Oxytocin 10 IU": { category: "Injection", code: "INJ-OXY", generic: "Oxytocin" },
        
        "Paracetamol Syrup 125mg/5ml": { category: "Syrup", code: "PCM-SYR", generic: "Paracetamol" },
        "Amoxicillin Suspension 125mg/5ml": { category: "Syrup", code: "AMX-SUSP", generic: "Amoxicillin" },
        "Metronidazole Suspension 200mg/5ml": { category: "Syrup", code: "MET-SUSP", generic: "Metronidazole" },
        
        "IV Normal Saline 500ml": { category: "Consumable", code: "IV-NS", generic: "0.9% Sodium Chloride" },
        "IV Dextrose 5% 500ml": { category: "Consumable", code: "IV-D5", generic: "5% Dextrose" },
        "IV Ringers Lactate 500ml": { category: "Consumable", code: "IV-RL", generic: "Ringers Lactate" }
    };

    function handleDrugSelectChange(val) {
        const customContainer = document.getElementById('custom-drug-name-container');
        const customInput = document.getElementById('d_name_custom');
        const genericInput = document.getElementById('d_generic');
        const codeInput = document.getElementById('d_code');
        const catSelect = document.getElementById('d_category');

        if (!customContainer || !customInput) return;

        if (val === 'custom') {
            customContainer.classList.remove('hidden');
            customInput.required = true;
            customInput.value = '';
            
            // Clear presets
            genericInput.value = '';
            codeInput.value = '';
            catSelect.value = 'Tablet';
        } else if (val && STANDARD_DRUGS_PRESETS[val]) {
            customContainer.classList.add('hidden');
            customInput.required = false;
            customInput.value = '';
            
            // Auto fill fields
            const preset = STANDARD_DRUGS_PRESETS[val];
            genericInput.value = preset.generic;
            codeInput.value = preset.code;
            catSelect.value = preset.category;
        } else {
            customContainer.classList.add('hidden');
            customInput.required = false;
            customInput.value = '';
            
            genericInput.value = '';
            codeInput.value = '';
            catSelect.value = 'Tablet';
        }
    }

    // Add Drug Inventory controls
    function openAddDrugModal() {
        document.getElementById('add-drug-form').reset();
        handleDrugSelectChange('');
        document.getElementById('add-drug-modal').classList.remove('hidden');
    }

    function closeAddDrugModal() {
        document.getElementById('add-drug-modal').classList.add('hidden');
    }

    async function handleAddDrugSubmit(e) {
        e.preventDefault();
        
        const selectVal = document.getElementById('d_name_select').value;
        let drugName = '';
        if (selectVal === 'custom') {
            drugName = document.getElementById('d_name_custom').value.trim();
        } else {
            drugName = selectVal;
        }

        if (!drugName) {
            alert('Please select a drug preset or specify a custom drug name.');
            return;
        }

        const payload = {
            name: drugName,
            generic_name: document.getElementById('d_generic').value || null,
            code: document.getElementById('d_code').value,
            category: document.getElementById('d_category').value,
            batch_number: document.getElementById('d_batch').value,
            expiry_date: document.getElementById('d_expiry').value,
            quantity_in_stock: parseInt(document.getElementById('d_stock').value),
            reorder_level: parseInt(document.getElementById('d_reorder').value),
            price_per_unit: parseFloat(document.getElementById('d_price').value)
        };

        try {
            await api.post('/pharmacy/inventory', payload);
            alert('New drug item registered in inventory successfully!');
            closeAddDrugModal();
            loadInventory();
        } catch (err) {
            alert(err.message || 'Failed to add item to inventory.');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const role = user.roles && user.roles[0] ? user.roles[0].name : '';
        if (['super_admin', 'pharmacist', 'store_officer', 'inventory_officer', 'procurement_officer'].includes(role)) {
            document.getElementById('add-drug-container').classList.remove('hidden');
        }
        loadPrescriptions();
    });
</script>
@endsection
