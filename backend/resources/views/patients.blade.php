@extends('layouts.app')

@section('title', 'Patient Registry - NIS Medical Services Portal')

@section('content')
<div class="space-y-6">
    <!-- Title & Actions -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-white">Patient Records Directory</h1>
            <p class="text-xs text-slate-800 dark:text-slate-200">Manage patient demographics, registrations, biometric markers, and historical timelines</p>
        </div>
        <div id="register-btn-container" class="hidden">
            <button onclick="openRegisterModal()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-xl text-xs font-semibold flex items-center gap-2 shadow-lg shadow-emerald-600/10 transition cursor-pointer">
                <i data-lucide="plus" class="w-4 h-4"></i> Register Patient
            </button>
        </div>
    </div>

    <!-- Hospital Code Search -->
    <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200 dark:border-slate-800/80 shadow-sm">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
            <div class="flex items-center gap-2.5 shrink-0">
                <div class="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-slate-800 dark:text-white">Hospital Code Lookup</h3>
                    <p class="text-[10px] text-slate-500 dark:text-slate-400">Search patients by their unique Hospital Code only</p>
                </div>
            </div>
            <div class="relative flex-grow max-w-md">
                <input type="text" id="search-input" oninput="handleSearch(this.value)" 
                       placeholder="Enter Hospital Code e.g. NIS/PAT/000001" 
                       class="w-full pl-10 pr-4 py-3 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 rounded-xl text-xs focus:outline-none focus:ring-1 focus:ring-emerald-500 text-slate-800 dark:text-slate-100 placeholder-slate-400 transition-all font-mono tracking-wide">
                <i data-lucide="badge-check" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-emerald-500 w-4 h-4"></i>
            </div>
            <div class="text-[10px] text-amber-600 dark:text-amber-400 flex items-center gap-1.5 font-bold shrink-0 bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 px-3 py-2 rounded-xl">
                <i data-lucide="shield-alert" class="w-3.5 h-3.5"></i> Hospital Code Required
            </div>
        </div>
    </div>

    <!-- Patients Table Card -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-200 border-b border-slate-100 dark:border-slate-800">
                    <tr>
                        <th class="py-3.5 px-6 font-bold">Patient Name</th>
                        <th class="py-3.5 px-6 font-bold">Gender & DOB</th>
                        <th class="py-3.5 px-6 font-bold">Hospital / Service Code</th>
                        <th class="py-3.5 px-6 font-bold">NIN</th>
                        <th class="py-3.5 px-6 font-bold">Phone</th>
                        <th class="py-3.5 px-6 font-bold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="patients-table-body" class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    <tr><td colspan="6" class="py-12 text-center">
                        <div class="flex flex-col items-center gap-2 text-slate-500">
                            <i data-lucide="search" class="w-8 h-8 text-slate-300"></i>
                            <p class="text-sm font-bold text-slate-700 dark:text-slate-300">Enter a Hospital Code to search</p>
                            <p class="text-xs text-slate-400">Patient records are protected and require a valid Hospital Code to access.</p>
                        </div>
                    </td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Register Patient Modal -->
<div id="register-modal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 w-full max-w-2xl shadow-2xl relative my-8">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-black text-slate-805 dark:text-white uppercase tracking-wider">Register Patient File</h3>
            <button onclick="closeRegisterModal()" class="text-slate-400 hover:text-slate-655 dark:hover:text-slate-250 transition focus:outline-none">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Step Progress Indicator (Form Breaker) -->
        <div class="flex items-center justify-between mb-6 bg-slate-50 dark:bg-slate-950/40 p-4 rounded-2xl border border-slate-200/60 dark:border-slate-800/80">
            <div class="flex flex-col items-center gap-1 flex-1 relative step-indicator" data-step="1">
                <div class="w-7 h-7 rounded-full bg-emerald-600 text-white font-bold flex items-center justify-center text-xs shadow-md transition-all duration-300" id="step-badge-1">1</div>
                <span class="text-[9px] font-black text-slate-800 dark:text-slate-200 uppercase tracking-wider mt-1">Personnel</span>
            </div>
            <div class="h-0.5 bg-slate-200 dark:bg-slate-800 flex-1 -mt-4 transition-all" id="step-line-1"></div>
            
            <div class="flex flex-col items-center gap-1 flex-1 relative step-indicator" data-step="2">
                <div class="w-7 h-7 rounded-full bg-slate-200 dark:bg-slate-800 text-slate-550 dark:text-slate-400 font-bold flex items-center justify-center text-xs transition-all duration-300" id="step-badge-2">2</div>
                <span class="text-[9px] font-black text-slate-450 dark:text-slate-500 uppercase tracking-wider mt-1">Dependant</span>
            </div>
            <div class="h-0.5 bg-slate-200 dark:bg-slate-800 flex-1 -mt-4 transition-all" id="step-line-2"></div>
            
            <div class="flex flex-col items-center gap-1 flex-1 relative step-indicator" data-step="3">
                <div class="w-7 h-7 rounded-full bg-slate-200 dark:bg-slate-800 text-slate-550 dark:text-slate-400 font-bold flex items-center justify-center text-xs transition-all duration-300" id="step-badge-3">3</div>
                <span class="text-[9px] font-black text-slate-450 dark:text-slate-500 uppercase tracking-wider mt-1">Address</span>
            </div>
            <div class="h-0.5 bg-slate-200 dark:bg-slate-800 flex-1 -mt-4 transition-all" id="step-line-3"></div>

            <div class="flex flex-col items-center gap-1 flex-1 relative step-indicator" data-step="4">
                <div class="w-7 h-7 rounded-full bg-slate-200 dark:bg-slate-800 text-slate-550 dark:text-slate-400 font-bold flex items-center justify-center text-xs transition-all duration-300" id="step-badge-4">4</div>
                <span class="text-[9px] font-black text-slate-450 dark:text-slate-500 uppercase tracking-wider mt-1">Allergies</span>
            </div>
            <div class="h-0.5 bg-slate-200 dark:bg-slate-800 flex-1 -mt-4 transition-all" id="step-line-4"></div>

            <div class="flex flex-col items-center gap-1 flex-1 relative step-indicator" data-step="5">
                <div class="w-7 h-7 rounded-full bg-slate-200 dark:bg-slate-800 text-slate-550 dark:text-slate-400 font-bold flex items-center justify-center text-xs transition-all duration-300" id="step-badge-5">5</div>
                <span class="text-[9px] font-black text-slate-450 dark:text-slate-500 uppercase tracking-wider mt-1">Preview</span>
            </div>
        </div>
        
        <form id="register-form" onsubmit="handleRegisterSubmit(event)" class="space-y-4">
            
            <!-- STEP 1: Personnel Information -->
            <div id="step-section-1" class="space-y-4">
                <div class="bg-slate-50 dark:bg-slate-950/40 p-4 rounded-2xl border border-slate-200 dark:border-slate-800/80 mb-4">
                    <label class="block text-[10px] font-bold text-slate-855 dark:text-slate-200 uppercase tracking-wider mb-2">Registration Mode</label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-800 dark:text-slate-200">
                            <input type="radio" name="registration_mode" id="mode-standalone" value="standalone" checked onchange="handleModeChange(this.value)" class="text-emerald-600 focus:ring-emerald-500">
                            Standalone (Civilian/Officer)
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-800 dark:text-slate-200">
                            <input type="radio" name="registration_mode" id="mode-dependant" value="dependant" onchange="handleModeChange(this.value)" class="text-emerald-600 focus:ring-emerald-500">
                            Dependant of NIS Officer
                        </label>
                    </div>
                </div>

                <!-- Standalone Name Inputs (Hidden in Dependant Mode) -->
                <div id="standalone-names-group" class="grid grid-cols-1 sm:grid-cols-3 gap-4 border-b border-slate-100 dark:border-slate-800 pb-4 mb-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-850 dark:text-slate-200 uppercase tracking-wider mb-1">First Name</label>
                        <input type="text" id="first_name" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-250 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-855 dark:text-slate-200 uppercase tracking-wider mb-1">Middle Name (Optional)</label>
                        <input type="text" id="middle_name" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-250 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-855 dark:text-slate-200 uppercase tracking-wider mb-1">Last Name</label>
                        <input type="text" id="last_name" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-250 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-855 dark:text-slate-200 uppercase tracking-wider mb-1">Gender</label>
                        <select id="gender" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <!-- Standalone DOB (Hidden in Dependant Mode) -->
                    <div id="standalone-dob-group">
                        <label class="block text-[10px] font-bold text-slate-855 dark:text-slate-200 uppercase tracking-wider mb-1">Date of Birth</label>
                        <input type="date" id="date_of_birth" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-850 dark:text-slate-250 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-855 dark:text-slate-200 uppercase tracking-wider mb-1">Phone Number</label>
                        <input type="text" id="phone" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-250 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-855 dark:text-slate-200 uppercase tracking-wider mb-1">Email Address</label>
                        <input type="email" id="email" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-250 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    </div>
                    <div id="standalone-service-group">
                        <label class="block text-[10px] font-bold text-slate-855 dark:text-slate-200 uppercase tracking-wider mb-1">Immigration Service Number (blank for Civilian)</label>
                        <input type="text" id="immigration_service_number" placeholder="e.g. NIS-123456" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-250 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-855 dark:text-slate-200 uppercase tracking-wider mb-1">National Identification Number (NIN)</label>
                        <input type="text" id="nin" maxLength="11" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-250 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    </div>
                </div>
                <!-- Dependant Mode notice in Step 1 -->
                <div id="dependant-mode-notice" class="hidden p-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 rounded-xl text-xs font-semibold">
                    <div class="flex items-center gap-2">
                        <i data-lucide="info" class="w-4 h-4"></i>
                        <span>Dependant mode active: names, DOB and sponsor details will be configured in Step 2.</span>
                    </div>
                </div>
            </div>

            <!-- STEP 2: Dependant Details -->
            <div id="step-section-2" class="hidden space-y-4">
                <!-- If standalone mode, show skipped msg -->
                <div id="dependant-skipped-msg" class="p-6 text-center text-slate-500 dark:text-slate-450 border border-dashed border-slate-200 dark:border-slate-800 rounded-2xl">
                    <i data-lucide="arrow-right-left" class="w-8 h-8 mx-auto mb-2 opacity-50 text-emerald-500"></i>
                    <p class="text-xs font-bold text-slate-700 dark:text-slate-300">Standalone Mode Active</p>
                    <p class="text-[10px] text-slate-400 mt-1">Sponsor/dependant linking is not required. Click Next to proceed.</p>
                </div>

                <!-- Dependant inputs group -->
                <div id="dependant-active-inputs" class="hidden space-y-4">
                    <div class="p-3 bg-amber-500/10 border border-amber-500/20 text-amber-600 rounded-xl text-[10px] font-black flex items-center gap-2">
                        <i data-lucide="shield-alert" class="w-4 h-4 shrink-0"></i>
                        <span>NOTE: Dependant status is strictly restricted to children below 18 years of age.</span>
                    </div>

                    <!-- Sponsor search input -->
                    <div class="bg-slate-50 dark:bg-slate-950/40 p-4 border border-slate-200 dark:border-slate-800/80 rounded-2xl flex flex-col sm:flex-row items-end gap-3">
                        <div class="flex-grow">
                            <label class="block text-[10px] font-bold text-slate-855 dark:text-slate-200 uppercase tracking-wider mb-1">Sponsor / Officer Service Number</label>
                            <input type="text" id="sponsor_service_number" placeholder="e.g. NIS-123456" class="w-full bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-855 dark:text-slate-250 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        </div>
                        <button type="button" onclick="handleVerifySponsor()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl text-xs font-bold transition shadow-sm h-9 shrink-0 flex items-center gap-1">
                            <i data-lucide="search" class="w-4 h-4"></i> Verify Sponsor
                        </button>
                    </div>

                    <!-- Sponsor verification card -->
                    <div id="sponsor-verify-status" class="hidden p-4 rounded-xl border border-slate-200 dark:border-slate-850 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="p-2 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600" id="sponsor-icon-box">
                                <i data-lucide="user-check" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-slate-800 dark:text-white" id="sponsor-fullname-label">Sponsor Found</h4>
                                <p class="text-[9px] text-slate-500 dark:text-slate-400">Surname auto-populated: <b class="text-slate-700 dark:text-slate-200 uppercase" id="sponsor-surname-badge"></b></p>
                            </div>
                        </div>
                        <span class="text-[8px] bg-emerald-100 text-emerald-800 font-bold px-2 py-0.5 rounded uppercase tracking-wider">Verified</span>
                    </div>

                    <!-- Dependant Name Details -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-850 dark:text-slate-200 uppercase tracking-wider mb-1">First Name</label>
                            <input type="text" id="dep_first_name" oninput="syncDepName()" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-250 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-855 dark:text-slate-200 uppercase tracking-wider mb-1">Middle Name (Optional)</label>
                            <input type="text" id="dep_middle_name" oninput="syncDepName()" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-250 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-855 dark:text-slate-200 uppercase tracking-wider mb-1">Date of Birth</label>
                            <input type="date" id="dep_date_of_birth" onchange="syncDepDob()" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-850 dark:text-slate-250 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-855 dark:text-slate-200 uppercase tracking-wider mb-1">Relationship to Sponsor</label>
                            <select id="relationship_to_sponsor" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                <option value="Son">Son</option>
                                <option value="Daughter">Daughter</option>
                                <option value="Ward">Ward</option>
                            </select>
                        </div>
                    </div>

                    <!-- Age warning status alert -->
                    <div id="dependant-age-warning" class="hidden p-4 rounded-xl border border-red-200 dark:border-red-500/20 bg-red-50 dark:bg-red-500/10 text-red-650">
                        <div class="flex items-center gap-2 text-xs font-bold">
                            <i data-lucide="alert-octagon" class="w-4 h-4"></i>
                            <span>Age Policy Check Failed</span>
                        </div>
                        <p class="text-[10px] mt-1 text-slate-600 dark:text-slate-400 font-semibold" id="dependant-age-msg"></p>
                    </div>
                </div>
            </div>

            <!-- STEP 3: Address Details -->
            <div id="step-section-3" class="hidden space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-855 dark:text-slate-200 uppercase tracking-wider mb-1">State of Origin</label>
                        <select id="state" required onchange="handleStateChange(this.value)" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                            <option value="">Select State</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-855 dark:text-slate-200 uppercase tracking-wider mb-1">Local Government Area (LGA) of Origin</label>
                        <select id="lga" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                            <option value="">Select LGA</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-[10px] font-bold text-slate-855 dark:text-slate-200 uppercase tracking-wider mb-1">Street Address</label>
                        <textarea id="address" required placeholder="House number, street name, block, etc." class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-250 focus:outline-none focus:ring-1 focus:ring-emerald-500" rows="3"></textarea>
                    </div>
                </div>
            </div>

            <!-- STEP 4: Allergies & Medical markers -->
            <div id="step-section-4" class="hidden space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-855 dark:text-slate-200 uppercase tracking-wider mb-1">Blood Group</label>
                        <select id="blood_group" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                            <option value="A+">A+</option>
                            <option value="A-">A-</option>
                            <option value="B+">B+</option>
                            <option value="B-">B-</option>
                            <option value="AB+">AB+</option>
                            <option value="AB-">AB-</option>
                            <option value="O+">O+</option>
                            <option value="O-">O-</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-855 dark:text-slate-200 uppercase tracking-wider mb-1">Genotype</label>
                        <select id="genotype" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                            <option value="AA">AA</option>
                            <option value="AS">AS</option>
                            <option value="SS">SS</option>
                            <option value="AC">AC</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-855 dark:text-slate-200 uppercase tracking-wider mb-1">Allergies</label>
                        <input type="text" id="allergies" placeholder="e.g. Penicillin, Dust" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-250 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-855 dark:text-slate-200 uppercase tracking-wider mb-1">Disabilities</label>
                        <input type="text" id="disability" placeholder="e.g. None" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-250 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    </div>
                </div>
            </div>

            <!-- STEP 5: Preview details -->
            <div id="step-section-5" class="hidden space-y-4 max-h-[330px] overflow-y-auto pr-1">
                <div class="p-3 bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 rounded-xl text-[10px] font-bold flex items-center gap-1.5">
                    <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                    <span>Please review all patient file information details before final registry creation.</span>
                </div>
                
                <div class="border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden text-xs">
                    <table class="w-full text-left divide-y divide-slate-100 dark:divide-slate-800">
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-950/40 text-slate-700 dark:text-slate-350">
                            <tr>
                                <td class="py-2.5 px-4 font-bold bg-slate-50 dark:bg-slate-900 w-1/3">Full Name</td>
                                <td class="py-2.5 px-4 font-semibold text-slate-900 dark:text-white" id="prev-name"></td>
                            </tr>
                            <tr>
                                <td class="py-2.5 px-4 font-bold bg-slate-50 dark:bg-slate-900">Gender & DOB</td>
                                <td class="py-2.5 px-4" id="prev-gender-dob"></td>
                            </tr>
                            <tr>
                                <td class="py-2.5 px-4 font-bold bg-slate-50 dark:bg-slate-900">Contact Details</td>
                                <td class="py-2.5 px-4" id="prev-contact"></td>
                            </tr>
                            <tr>
                                <td class="py-2.5 px-4 font-bold bg-slate-50 dark:bg-slate-900">NIN & Service Code</td>
                                <td class="py-2.5 px-4 font-mono" id="prev-identifiers"></td>
                            </tr>
                            <tr id="prev-row-dependant" class="hidden">
                                <td class="py-2.5 px-4 font-bold bg-slate-50 dark:bg-slate-900">Dependant Status</td>
                                <td class="py-2.5 px-4 text-emerald-600 dark:text-emerald-450 font-bold" id="prev-dependant"></td>
                            </tr>
                            <tr>
                                <td class="py-2.5 px-4 font-bold bg-slate-50 dark:bg-slate-900">Address Details</td>
                                <td class="py-2.5 px-4" id="prev-address"></td>
                            </tr>
                            <tr>
                                <td class="py-2.5 px-4 font-bold bg-slate-50 dark:bg-slate-900">Medical Markers</td>
                                <td class="py-2.5 px-4" id="prev-medical"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modal Navigation controls -->
            <div class="flex justify-between items-center pt-4 border-t border-slate-100 dark:border-slate-800 mt-2">
                <button type="button" id="prev-step-btn" onclick="handlePrevStep()" class="invisible px-4 py-2 text-xs font-bold text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition flex items-center gap-1">
                    <i data-lucide="chevron-left" class="w-4 h-4"></i> Back
                </button>
                <div class="flex items-center gap-3">
                    <button type="button" onclick="closeRegisterModal()" class="px-4 py-2 text-xs font-semibold text-slate-550 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition">Cancel</button>
                    <button type="button" id="next-step-btn" onclick="handleNextStep()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 text-xs font-bold rounded-xl transition shadow-md flex items-center gap-1">
                        Next <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </button>
                    <button type="submit" id="submit-step-btn" class="hidden bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 text-xs font-bold rounded-xl transition shadow-md flex items-center gap-1">
                        Register File <i data-lucide="check-circle" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Success Notification Modal -->
<div id="success-modal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 w-full max-w-md shadow-2xl relative text-center">
        <div class="mx-auto w-16 h-16 bg-emerald-100 dark:bg-emerald-500/10 text-emerald-600 rounded-full flex items-center justify-center mb-4">
            <i data-lucide="shield-check" class="w-8 h-8"></i>
        </div>
        
        <h3 class="text-lg font-black text-slate-900 dark:text-white uppercase tracking-wide">Registration Successful!</h3>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 mb-6">The patient profile has been created.</p>
        
        <div class="bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-850 p-5 rounded-2xl mb-6 space-y-3">
            <div>
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block">Patient Full Name</span>
                <span class="text-sm font-black text-slate-800 dark:text-white" id="success-patient-name">John Obi</span>
            </div>
            <div class="border-t border-slate-200 dark:border-slate-800/80 pt-3">
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block">Patient Unique Hospital Code</span>
                <span class="text-base font-mono font-black text-emerald-600 tracking-wider block bg-emerald-500/5 py-1.5 rounded-lg border border-emerald-500/10 mt-1" id="success-patient-code">NIS/PAT/000123</span>
            </div>
        </div>

        <button type="button" onclick="closeSuccessModal()" class="w-full bg-slate-900 hover:bg-slate-850 dark:bg-emerald-600 dark:hover:bg-emerald-700 text-white font-bold text-xs py-3 rounded-xl transition shadow-md">
            Done & Close
        </button>
    </div>
</div>

<!-- View Patient Details File Modal -->
<div id="details-modal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 w-full max-w-3xl shadow-2xl relative my-8">
        <h3 class="text-base font-bold text-slate-800 dark:text-white mb-4 flex items-center gap-2">
            <i data-lucide="fingerprint" class="text-emerald-500"></i> Comprehensive Clinical File History
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Left Info Panel -->
            <div class="space-y-4 border-r border-slate-100 dark:border-slate-800 pr-4">
                <div>
                    <h4 class="text-sm font-extrabold text-slate-900 dark:text-white" id="det-full-name">Name</h4>
                    <span class="text-[9px] font-bold px-2 py-0.5 bg-emerald-500/10 text-emerald-600 border border-emerald-500/20 rounded" id="det-service-code">Code</span>
                </div>
                <div class="overflow-x-auto mt-2">
                    <table class="w-full text-[11px] text-left border-collapse">
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="py-1.5 pr-3 font-bold text-slate-500 w-1/3">DOB</td>
                                <td class="py-1.5 text-slate-800 dark:text-slate-200" id="det-dob"></td>
                            </tr>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="py-1.5 pr-3 font-bold text-slate-500">Gender</td>
                                <td class="py-1.5 text-slate-800 dark:text-slate-200" id="det-gender"></td>
                            </tr>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="py-1.5 pr-3 font-bold text-slate-500">Phone</td>
                                <td class="py-1.5 text-slate-800 dark:text-slate-200" id="det-phone"></td>
                            </tr>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="py-1.5 pr-3 font-bold text-slate-500">NIN</td>
                                <td class="py-1.5 text-slate-800 dark:text-slate-200" id="det-nin"></td>
                            </tr>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="py-1.5 pr-3 font-bold text-slate-500">Blood Group</td>
                                <td class="py-1.5 text-slate-800 dark:text-slate-200" id="det-blood"></td>
                            </tr>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="py-1.5 pr-3 font-bold text-slate-500">Genotype</td>
                                <td class="py-1.5 text-slate-800 dark:text-slate-200" id="det-genotype"></td>
                            </tr>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="py-1.5 pr-3 font-bold text-slate-500">State of Origin</td>
                                <td class="py-1.5 text-slate-800 dark:text-slate-200" id="det-state"></td>
                            </tr>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="py-1.5 pr-3 font-bold text-slate-500">LGA of Origin</td>
                                <td class="py-1.5 text-slate-800 dark:text-slate-200" id="det-lga"></td>
                            </tr>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="py-1.5 pr-3 font-bold text-slate-500">Address</td>
                                <td class="py-1.5 text-slate-800 dark:text-slate-200" id="det-address"></td>
                            </tr>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="py-1.5 pr-3 font-bold text-slate-500">Allergies</td>
                                <td class="py-1.5 text-red-500 font-semibold" id="det-allergies"></td>
                            </tr>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="py-1.5 pr-3 font-bold text-slate-500">Disability</td>
                                <td class="py-1.5 text-amber-600 font-semibold" id="det-disability"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Dependant Info section -->
                <div id="det-dependant-section" class="border-t border-slate-100 dark:border-slate-800 pt-3 space-y-2">
                    <h5 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Family & Dependants</h5>
                    
                    <!-- If this patient is a dependant -->
                    <div id="det-sponsor-info" class="hidden p-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-[10px]">
                        <span class="text-slate-450 dark:text-slate-500 block mb-0.5">Sponsor Officer:</span>
                        <b class="text-slate-800 dark:text-white" id="det-sponsor-name">John Doe</b>
                        <span class="text-slate-500 font-mono block mt-0.5" id="det-sponsor-code">NIS/PAT/123456</span>
                    </div>

                    <!-- If this patient is a sponsor -->
                    <div id="det-dependants-list-group" class="hidden space-y-1.5">
                        <span class="text-slate-450 dark:text-slate-500 text-[9px] block">Registered Dependants:</span>
                        <div id="det-dependants-list" class="space-y-1 max-h-32 overflow-y-auto pr-1">
                            <!-- Dependant list items injected dynamically -->
                        </div>
                    </div>
                    
                    <p id="det-no-family" class="text-[10px] text-slate-400 italic">No linked dependants or sponsor.</p>
                </div>
            </div>

            <!-- Right History Panel (Tabs) -->
            <div class="md:col-span-2 space-y-4">
                <!-- Tab Headers -->
                <div class="flex border-b border-slate-150 dark:border-slate-800">
                    <button type="button" onclick="switchDetailsTab('timeline')" id="tab-btn-timeline" class="border-b-2 border-emerald-600 px-4 py-2 text-xs font-bold text-emerald-650 focus:outline-none transition-all">
                        Clinical Timeline
                    </button>
                    <button type="button" onclick="switchDetailsTab('medications')" id="tab-btn-medications" class="border-b-2 border-transparent px-4 py-2 text-xs font-bold text-slate-500 hover:text-slate-700 dark:hover:text-slate-350 focus:outline-none transition-all">
                        Medications & Prescriptions
                    </button>
                    <button type="button" onclick="switchDetailsTab('diagnostics')" id="tab-btn-diagnostics" class="border-b-2 border-transparent px-4 py-2 text-xs font-bold text-slate-500 hover:text-slate-700 dark:hover:text-slate-350 focus:outline-none transition-all">
                        Diagnostic Reports
                    </button>
                </div>

                <!-- Tab 1: Timeline -->
                <div class="max-h-72 overflow-y-auto space-y-4 pr-2" id="det-timeline">
                    <!-- Events Injected dynamically -->
                </div>

                <!-- Tab 2: Medications -->
                <div class="max-h-72 overflow-y-auto space-y-3 pr-2 hidden" id="det-medications">
                    <!-- Medications list Injected dynamically -->
                </div>

                <!-- Tab 3: Diagnostics -->
                <div class="max-h-72 overflow-y-auto space-y-3 pr-2 hidden" id="det-diagnostics">
                    <!-- Diagnostics list Injected dynamically -->
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-6 border-t border-slate-150 dark:border-slate-800/80 mt-6">
            <button onclick="closeDetailsModal()" class="px-5 py-2.5 text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 hover:bg-slate-200 rounded-xl transition">Close File</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let searchTimeout = null;

    async function loadPatients(query = '') {
        const tbody = document.getElementById('patients-table-body');

        // Guard: require at least 3 characters before hitting the API
        if (query.trim().length < 3) {
            tbody.innerHTML = `<tr><td colspan="6" class="py-12 text-center">
                <div class="flex flex-col items-center gap-2 text-slate-500">
                    <i data-lucide="search" class="w-8 h-8 text-slate-300"></i>
                    <p class="text-sm font-bold text-slate-700 dark:text-slate-300">Enter a Hospital Code to search</p>
                    <p class="text-xs text-slate-400">Patient records are protected and require a valid Hospital Code to access.</p>
                </div>
            </td></tr>`;
            lucide.createIcons();
            return;
        }

        try {
            const res = await api.get(`/patients?search=${encodeURIComponent(query)}`);
            const list = res.patients;

            if (list.length > 0) {
                tbody.innerHTML = list.map(pat => `
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/20 transition">
                        <td class="py-3.5 px-6 font-bold text-slate-900 dark:text-white">${pat.first_name} ${pat.middle_name ? pat.middle_name + ' ' : ''}${pat.last_name}</td>
                        <td class="py-3.5 px-6">
                            <span class="font-medium">${pat.gender}</span>
                            <div class="text-[9px] text-slate-500">${pat.date_of_birth}</div>
                        </td>
                        <td class="py-3.5 px-6">
                            <span class="px-2 py-0.5 font-bold ${pat.immigration_service_number.includes('NIS/PAT/') ? 'bg-slate-100 text-slate-650 dark:bg-slate-800 dark:text-slate-300' : 'bg-emerald-500/10 text-emerald-600 border border-emerald-500/20'} rounded">
                                ${pat.immigration_service_number}
                            </span>
                        </td>
                        <td class="py-3.5 px-6 font-mono text-slate-800 dark:text-slate-200">${pat.nin || '—'}</td>
                        <td class="py-3.5 px-6">${pat.phone}</td>
                        <td class="py-3.5 px-6 text-right">
                            <button onclick="handleShowDetails(${pat.id})" class="text-emerald-600 hover:text-emerald-700 font-bold hover:underline cursor-pointer">
                                View File
                            </button>
                        </td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = `<tr><td colspan="6" class="py-12 text-center">
                    <div class="flex flex-col items-center gap-2 text-slate-500">
                        <i data-lucide="search" class="w-8 h-8 text-slate-300"></i>
                        <p class="text-sm font-bold text-slate-700 dark:text-slate-300">No matching patient found for the provided Hospital Code.</p>
                        <p class="text-xs text-slate-400">Double-check the code and try again.</p>
                    </div>
                </td></tr>`;
            }
            lucide.createIcons();
        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="6" class="py-8 text-center text-red-500 text-xs">Failed to load patient records.</td></tr>`;
        }
    }

    function handleSearch(val) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            loadPatients(val);
        }, 300);
    }

    let currentRegisterStep = 1;
    let registrationMode = 'standalone';
    let sponsorVerified = false;
    let sponsorSurname = '';

    function handleModeChange(mode) {
        registrationMode = mode;
        const standaloneNames = document.getElementById('standalone-names-group');
        const standaloneDob = document.getElementById('standalone-dob-group');
        const dependantNotice = document.getElementById('dependant-mode-notice');
        
        const depSkipped = document.getElementById('dependant-skipped-msg');
        const depActive = document.getElementById('dependant-active-inputs');
        
        const firstName = document.getElementById('first_name');
        const lastName = document.getElementById('last_name');
        const dob = document.getElementById('date_of_birth');
        
        const depFirstName = document.getElementById('dep_first_name');
        const depDob = document.getElementById('dep_date_of_birth');
        
        if (mode === 'standalone') {
            if (standaloneNames) standaloneNames.classList.remove('hidden');
            if (standaloneDob) standaloneDob.classList.remove('hidden');
            if (dependantNotice) dependantNotice.classList.add('hidden');
            
            if (depSkipped) depSkipped.classList.remove('hidden');
            if (depActive) depActive.classList.add('hidden');
            
            if (firstName) firstName.setAttribute('required', 'required');
            if (lastName) lastName.setAttribute('required', 'required');
            if (dob) dob.setAttribute('required', 'required');
            
            if (depFirstName) depFirstName.removeAttribute('required');
            if (depDob) depDob.removeAttribute('required');
        } else {
            if (standaloneNames) standaloneNames.classList.add('hidden');
            if (standaloneDob) standaloneDob.classList.add('hidden');
            if (dependantNotice) dependantNotice.classList.remove('hidden');
            
            if (depSkipped) depSkipped.classList.add('hidden');
            if (depActive) depActive.classList.remove('hidden');
            
            if (firstName) firstName.removeAttribute('required');
            if (lastName) lastName.removeAttribute('required');
            if (dob) dob.removeAttribute('required');
            
            if (depFirstName) depFirstName.setAttribute('required', 'required');
            if (depDob) depDob.setAttribute('required', 'required');
        }
    }

    function syncDepName() {
        const depFirst = document.getElementById('dep_first_name');
        const depMiddle = document.getElementById('dep_middle_name');
        if (depFirst) document.getElementById('first_name').value = depFirst.value;
        if (depMiddle) document.getElementById('middle_name').value = depMiddle.value;
    }

    function syncDepDob() {
        const depDob = document.getElementById('dep_date_of_birth');
        if (depDob) {
            document.getElementById('date_of_birth').value = depDob.value;
        }
        validateDependantAge();
    }

    function calculateAge(dobString) {
        if (!dobString) return 0;
        const today = new Date();
        const birthDate = new Date(dobString);
        let age = today.getFullYear() - birthDate.getFullYear();
        const m = today.getMonth() - birthDate.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        return age;
    }

    function validateDependantAge() {
        const dob = document.getElementById('date_of_birth').value;
        const ageWarning = document.getElementById('dependant-age-warning');
        const ageMsg = document.getElementById('dependant-age-msg');
        const nextBtn = document.getElementById('next-step-btn');
        
        if (registrationMode !== 'dependant') {
            if (ageWarning) ageWarning.classList.add('hidden');
            if (nextBtn) {
                nextBtn.disabled = false;
                nextBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
            return true;
        }
        
        if (!dob) {
            if (ageWarning) ageWarning.classList.add('hidden');
            if (nextBtn) {
                nextBtn.disabled = false;
                nextBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
            return false;
        }
        
        const age = calculateAge(dob);
        if (age >= 18) {
            if (ageMsg) {
                ageMsg.innerHTML = `⚠️ Dependant is <b>${age} years old</b>. Dependant status is strictly restricted to children below 18 years of age. Please register this patient as a <b>standalone patient</b> (go back and select Standalone mode).`;
            }
            if (ageWarning) ageWarning.classList.remove('hidden');
            if (nextBtn) {
                nextBtn.disabled = true;
                nextBtn.classList.add('opacity-50', 'cursor-not-allowed');
            }
            return false;
        } else {
            if (ageWarning) ageWarning.classList.add('hidden');
            if (nextBtn) {
                nextBtn.disabled = false;
                nextBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
            return true;
        }
    }

    async function handleVerifySponsor() {
        const serviceNumInput = document.getElementById('sponsor_service_number');
        if (!serviceNumInput) return;
        const serviceNum = serviceNumInput.value.trim();
        const statusCard = document.getElementById('sponsor-verify-status');
        const fullnameLabel = document.getElementById('sponsor-fullname-label');
        const surnameBadge = document.getElementById('sponsor-surname-badge');
        
        if (serviceNum.length < 3) {
            alert('Please enter a valid Service Number (at least 3 characters).');
            return;
        }
        
        try {
            const res = await api.get(`/sponsor/lookup?service_number=${encodeURIComponent(serviceNum)}`);
            if (res.found) {
                sponsorVerified = true;
                sponsorSurname = res.surname;
                
                if (statusCard) statusCard.classList.remove('hidden');
                if (fullnameLabel) fullnameLabel.innerText = res.full_name;
                if (surnameBadge) surnameBadge.innerText = res.surname;
                
                // Automatically populate and sync last name of the patient record
                document.getElementById('last_name').value = res.surname;
                
                alert('Sponsor verified successfully! Surname has been fetched and mapped.');
            } else {
                sponsorVerified = false;
                sponsorSurname = '';
                if (statusCard) statusCard.classList.add('hidden');
                document.getElementById('last_name').value = '';
                alert(res.message || 'Sponsor not found. Please verify the Service Number.');
            }
        } catch (err) {
            sponsorVerified = false;
            sponsorSurname = '';
            if (statusCard) statusCard.classList.add('hidden');
            document.getElementById('last_name').value = '';
            alert('Sponsor verification failed: ' + (err.message || 'connection error'));
        }
    }

    // Modal display rules
    function openRegisterModal() {
        document.getElementById('register-form').reset();
        
        // Load states into dropdown
        loadStates();

        // Reset LGA dropdown
        const lgaSelect = document.getElementById('lga');
        if (lgaSelect) {
            lgaSelect.innerHTML = '<option value="">Select LGA</option>';
        }

        // Hide dependant inputs and verification status by default
        const verifyStatus = document.getElementById('sponsor-verify-status');
        if (verifyStatus) verifyStatus.classList.add('hidden');
        const ageWarning = document.getElementById('dependant-age-warning');
        if (ageWarning) ageWarning.classList.add('hidden');

        // Set standalone mode default
        const modeStandaloneRadio = document.getElementById('mode-standalone');
        if (modeStandaloneRadio) {
            modeStandaloneRadio.checked = true;
        }
        handleModeChange('standalone');

        // Reset wizard to Step 1
        currentRegisterStep = 1;
        showStep(1);

        document.getElementById('register-modal').classList.remove('hidden');
    }

    function closeRegisterModal() {
        document.getElementById('register-modal').classList.add('hidden');
    }

    function showStep(stepNum) {
        // Hide all step sections
        for (let i = 1; i <= 5; i++) {
            document.getElementById(`step-section-${i}`).classList.add('hidden');
        }
        // Show current step section
        document.getElementById(`step-section-${stepNum}`).classList.remove('hidden');

        // Toggle back button visibility
        const prevBtn = document.getElementById('prev-step-btn');
        if (stepNum > 1) {
            if (prevBtn) prevBtn.classList.remove('invisible');
        } else {
            if (prevBtn) prevBtn.classList.add('invisible');
        }

        // Toggle Next / Submit buttons
        const nextBtn = document.getElementById('next-step-btn');
        const submitBtn = document.getElementById('submit-step-btn');
        if (stepNum === 5) {
            if (nextBtn) nextBtn.classList.add('hidden');
            if (submitBtn) submitBtn.classList.remove('hidden');
        } else {
            if (nextBtn) nextBtn.classList.remove('hidden');
            if (submitBtn) submitBtn.classList.add('hidden');
        }

        // Update step badges
        updateStepIndicator(stepNum);
    }

    function updateStepIndicator(stepNum) {
        for (let i = 1; i <= 5; i++) {
            const badge = document.getElementById(`step-badge-${i}`);
            if (!badge) continue;
            
            // Update styles
            if (i < stepNum) {
                badge.className = 'w-7 h-7 rounded-full bg-emerald-100 text-emerald-600 font-bold flex items-center justify-center text-xs shadow-sm transition-all duration-300';
                badge.innerHTML = '<i data-lucide="check" class="w-4 h-4"></i>';
            } else if (i === stepNum) {
                badge.className = 'w-7 h-7 rounded-full bg-emerald-600 text-white font-bold flex items-center justify-center text-xs shadow-md transition-all duration-300';
                badge.innerHTML = i;
            } else {
                badge.className = 'w-7 h-7 rounded-full bg-slate-200 dark:bg-slate-800 text-slate-550 dark:text-slate-400 font-bold flex items-center justify-center text-xs transition-all duration-300';
                badge.innerHTML = i;
            }

            // Update connector line
            if (i < 5) {
                const line = document.getElementById(`step-line-${i}`);
                if (line) {
                    if (i < stepNum) {
                        line.className = 'h-0.5 bg-emerald-500 flex-1 -mt-4 transition-all duration-300';
                    } else {
                        line.className = 'h-0.5 bg-slate-200 dark:bg-slate-800 flex-1 -mt-4 transition-all duration-300';
                    }
                }
            }
        }
        lucide.createIcons();
    }

    function handleNextStep() {
        if (currentRegisterStep === 1) {
            // Validate Step 1 fields based on mode
            const gender = document.getElementById('gender').value;
            const phone = document.getElementById('phone').value.trim();

            if (registrationMode === 'standalone') {
                const first = document.getElementById('first_name').value.trim();
                const last = document.getElementById('last_name').value.trim();
                const dob = document.getElementById('date_of_birth').value;
                if (!first || !last || !gender || !dob || !phone) {
                    alert('Please fill out First Name, Last Name, Gender, DOB and Phone Number.');
                    return;
                }
            } else {
                if (!gender || !phone) {
                    alert('Please fill out Gender and Phone Number.');
                    return;
                }
            }
        }
        
        if (currentRegisterStep === 2) {
            // Validate Dependant details if in dependant mode
            if (registrationMode === 'dependant') {
                const sponsor = document.getElementById('sponsor_service_number').value.trim();
                const depFirst = document.getElementById('dep_first_name').value.trim();
                const depDob = document.getElementById('dep_date_of_birth').value;

                if (!sponsor) {
                    alert('Sponsor/Officer Service Number is required.');
                    return;
                }
                if (!sponsorVerified) {
                    alert('Please verify the Sponsor Service Number before proceeding.');
                    return;
                }
                if (!depFirst || !depDob) {
                    alert('Please fill out Dependant First Name and Date of Birth.');
                    return;
                }
                if (!validateDependantAge()) {
                    alert('Sponsor dependants must be strictly under 18 years of age.');
                    return;
                }
            }
        }

        if (currentRegisterStep === 3) {
            // Validate Address details
            const state = document.getElementById('state').value;
            const lga = document.getElementById('lga').value;
            const addr = document.getElementById('address').value.trim();

            if (!state || !lga || !addr) {
                alert('Please select State, LGA and enter the Street Address.');
                return;
            }
        }

        if (currentRegisterStep === 4) {
            // Populate Preview screen (Step 5)
            const first = document.getElementById('first_name').value.trim();
            const middle = document.getElementById('middle_name').value.trim();
            const last = document.getElementById('last_name').value.trim();
            const gender = document.getElementById('gender').value;
            const dob = document.getElementById('date_of_birth').value;
            const phone = document.getElementById('phone').value.trim();
            const email = document.getElementById('email').value.trim() || '—';
            const serviceNo = document.getElementById('immigration_service_number').value.trim() || '—';
            const nin = document.getElementById('nin').value.trim() || '—';
            const blood = document.getElementById('blood_group').value;
            const genotypeVal = document.getElementById('genotype').value;
            const state = document.getElementById('state').value;
            const lga = document.getElementById('lga').value;
            const address = document.getElementById('address').value.trim();
            const allergies = document.getElementById('allergies').value.trim() || 'None';
            const disability = document.getElementById('disability').value.trim() || 'None';

            const sponsor = document.getElementById('sponsor_service_number').value.trim();
            const rel = document.getElementById('relationship_to_sponsor').value;

            document.getElementById('prev-name').innerText = middle ? `${first} ${middle} ${last}` : `${first} ${last}`;
            document.getElementById('prev-gender-dob').innerText = `${gender} — DOB: ${dob} (Age: ${calculateAge(dob)} years)`;
            document.getElementById('prev-contact').innerText = `Phone: ${phone} | Email: ${email}`;
            document.getElementById('prev-identifiers').innerText = `NIN: ${nin} | Service No: ${serviceNo}`;
            
            const depRow = document.getElementById('prev-row-dependant');
            if (registrationMode === 'dependant') {
                depRow.classList.remove('hidden');
                document.getElementById('prev-dependant').innerText = `Yes (${rel} of Sponsor: ${sponsor})`;
            } else {
                depRow.classList.add('hidden');
            }

            document.getElementById('prev-address').innerText = `${address}, ${lga}, ${state} State`;
            document.getElementById('prev-medical').innerText = `Blood: ${blood} | Genotype: ${genotypeVal} | Allergies: ${allergies} | Disability: ${disability}`;
        }

        if (currentRegisterStep < 5) {
            currentRegisterStep++;
            showStep(currentRegisterStep);
        }
    }

    function handlePrevStep() {
        if (currentRegisterStep > 1) {
            currentRegisterStep--;
            showStep(currentRegisterStep);
        }
    }

    // Nigeria States & LGAs Data Mapping
    const NIGERIA_STATES_AND_LGAS = {
        "Abia": ["Aba North", "Aba South", "Arochukwu", "Bende", "Ikwuano", "Isiala Ngwa North", "Isiala Ngwa South", "Isuikwuato", "Obi Ngwa", "Ohafia", "Osisioma", "Ugwunagbo", "Ukwa East", "Ukwa West", "Umuahia North", "Umuahia South", "Umunneochi"],
        "Adamawa": ["Demsa", "Fufure", "Ganye", "Gayuk", "Gombi", "Grie", "Hong", "Jada", "Lamurde", "Madagali", "Maiha", "Mayo Belwa", "Michika", "Mubi North", "Mubi South", "Numan", "Shelleng", "Song", "Toungo", "Yola North", "Yola South"],
        "Akwa Ibom": ["Abak", "Eastern Obolo", "Eket", "Esit Eket", "Essien Udim", "Etim Ekpo", "Etinan", "Ibeno", "Ibesikpo Asutan", "Ibiono-Ibom", "Ika", "Ikono", "Ikot Abasi", "Ikot Ekpene", "Ini", "Itu", "Mbo", "Mkpat-Enin", "Nsit-Atai", "Nsit-Ibom", "Nsit-Ubium", "Obot Akara", "Okobo", "Onna", "Oron", "Oruk Anam", "Udung-Uko", "Ukanafun", "Uruan", "Urue-Offong/Oruko", "Uyo"],
        "Anambra": ["Aguata", "Anambra East", "Anambra West", "Anaocha", "Awka North", "Awka South", "Ayamelum", "Dunukofia", "Ekwusigo", "Idemili North", "Idemili South", "Ihiala", "Njikoka", "Nnewi North", "Nnewi South", "Ogbaru", "Onitsha North", "Onitsha South", "Orumba North", "Orumba South", "Oyi"],
        "Bauchi": ["Alkaleri", "Bauchi", "Bogoro", "Damban", "Darazo", "Dass", "Gamawa", "Ganjuwa", "Giade", "Itas/Gadau", "Jama'are", "Katagum", "Kirfi", "Misau", "Ningi", "Shira", "Tafawa Balewa", "Toro", "Warji", "Zaki"],
        "Bayelsa": ["Brass", "Ekeremor", "Kolokuma/Opokuma", "Nembe", "Ogbia", "Sagbama", "Southern Ijaw", "Yenagoa"],
        "Benue": ["Agatu", "Apa", "Ado", "Buruku", "Gboko", "Guma", "Gwer East", "Gwer West", "Katsina-Ala", "Konshisha", "Kwande", "Logo", "Makurdi", "Obi", "Ogbadibo", "Ohimini", "Oju", "Okpokwu", "Oturkpo", "Tarka", "Ukum", "Ushongo", "Vandeikya"],
        "Borno": ["Abadam", "Askira/Uba", "Bama", "Bayo", "Biu", "Chibok", "Damboa", "Dikwa", "Gubio", "Guzamala", "Gwoza", "Hawul", "Jere", "Kaga", "Kala/Balge", "Konduga", "Kukawa", "Kwaya Kusar", "Mafa", "Magumeri", "Maiduguri", "Marte", "Mobbar", "Monguno", "Ngala", "Nganzai", "Shani"],
        "Cross River": ["Abi", "Akamkpa", "Akpabuyo", "Bakassi", "Bekwarra", "Biase", "Boki", "Calabar Municipal", "Calabar South", "Etung", "Ikom", "Obanliku", "Obubra", "Obudu", "Odukpani", "Ogoja", "Yakuur", "Yala"],
        "Delta": ["Aniocha North", "Aniocha South", "Bomadi", "Burutu", "Ethiope East", "Ethiope West", "Ika North East", "Ika South", "Isoko North", "Isoko South", "Ndokwa East", "Ndokwa West", "Okpe", "Oshimili North", "Oshimili South", "Patani", "Sapele", "Udu", "Ughelli North", "Ughelli South", "Ukwuani", "Uvwie", "Warri North", "Warri South", "Warri South West"],
        "Ebonyi": ["Abakaliki", "Afikpo North", "Afikpo South", "Ebonyi", "Ezza North", "Ezza South", "Ikwo", "Ishielu", "Ivo", "Izzi", "Ohaozara", "Ohaukwu", "Onicha"],
        "Edo": ["Akoko-Edo", "Egor", "Esan Central", "Esan North-East", "Esan South-East", "Esan West", "Etsako Central", "Etsako East", "Etsako West", "Igueben", "Ikpoba Okha", "Orhionmwon", "Oredo", "Ovia North-East", "Ovia South-West", "Owan East", "Owan West", "Uhunmwonde"],
        "Ekiti": ["Ado Ekiti", "Efon", "Ekiti East", "Ekiti South-West", "Ekiti West", "Emure", "Gbonyin", "Ido Osi", "Ijero", "Ikere", "Ikole", "Ilejemeje", "Irepodun/Ifelodun", "Ise/Orun", "Moba", "Oye"],
        "Enugu": ["Aninri", "Awgu", "Enugu East", "Enugu North", "Enugu South", "Ezeagu", "Igbo Etiti", "Igbo Eze North", "Igbo Eze South", "Isi Uzo", "Nkanu East", "Nkanu West", "Nsukka", "Oji River", "Udenu", "Udi", "Uzo-Uwani"],
        "FCT (Abuja)": ["Abaji", "Bwari", "Gwagwalada", "Kuje", "Kwali", "Municipal Area Council"],
        "Gombe": ["Akko", "Balanga", "Billiri", "Dukku", "Funakaye", "Gombe", "Kaltungo", "Kwami", "Nafada", "Shongom", "Yamaltu/Deba"],
        "Imo": ["Aboh Mbaise", "Ahiazu Mbaise", "Ehime Mbano", "Ezinihitte", "Ideato North", "Ideato South", "Ihitte/Uboma", "Ikeduru", "Isiala Mbano", "Isu", "Mbaitoli", "Ngor Okpala", "Njaba", "Nkwerre", "Nwangele", "Obowo", "Oguta", "Ohaji/Egbema", "Okigwe", "Orlu", "Orsu", "Oru East", "Oru West", "Owerri Municipal", "Owerri North", "Owerri West", "Unuimo"],
        "Jigawa": ["Auyo", "Babura", "Biriniwa", "Birnin Kudu", "Buji", "Dutse", "Gagarawa", "Garki", "Gumel", "Guri", "Gwaram", "Gwiwa", "Hadejia", "Jahun", "Kafin Hausa", "Kazaure", "Kiri Kasama", "Kiyawa", "Maigatari", "Malam Madori", "Miga", "Ringim", "Roni", "Sule Tankarkar", "Taura", "Yankwashi"],
        "Kaduna": ["Birnin Gwari", "Chikun", "Giwa", "Kajuru", "Igabi", "Ikara", "Jaba", "Jema'a", "Kachia", "Kaduna North", "Kaduna South", "Kagarko", "Kaura", "Kauru", "Kubau", "Kudan", "Lere", "Makarfi", "Sabon Gari", "Sanga", "Soba", "Zangon Kataf", "Zaria"],
        "Kano": ["Ajingi", "Albasu", "Bagwai", "Bebeji", "Bichi", "Bunkure", "Dala", "Dambatta", "Dawakin Kudu", "Dawakin Tofa", "Doguwa", "Fagge", "Gabasawa", "Garko", "Garun Mallam", "Gaya", "Gezawa", "Gwale", "Gwarzo", "Kabo", "Kano Municipal", "Karaye", "Kibiya", "Kiru", "Kumbotso", "Kunchi", "Kura", "Madobi", "Makoda", "Minjibir", "Nasarawa", "Rano", "Rimin Gado", "Rogo", "Shanono", "Sumaila", "Takai", "Tarauni", "Tofa", "Tsanyawa", "Tudun Wada", "Ungogo", "Warawa", "Wudil"],
        "Katsina": ["Bakori", "Batagarawa", "Batsari", "Baure", "Bindawa", "Charanchi", "Dandume", "Danja", "Dan Musa", "Daura", "Dutsin Ma", "Faskari", "Funtua", "Ingawa", "Jibia", "Kafur", "Kaita", "Kankara", "Kankia", "Katsina", "Kurfi", "Kusada", "Mai'Adua", "Malumfashi", "Mani", "Mashi", "Musawa", "Rimi", "Sabuwa", "Safana", "Sandamu", "Zango"],
        "Kebbi": ["Aleiro", "Arewa Dandi", "Argungu", "Augie", "Bagudo", "Birnin Kebbi", "Bunza", "Dandi", "Fakai", "Gwandu", "Jega", "Kalgo", "Koko/Besse", "Maiyama", "Ngaski", "Sakaba", "Shanga", "Suru", "Wasagu/Danko", "Yauri", "Zuru"],
        "Kogi": ["Adavi", "Ajaokuta", "Ankpa", "Bassa", "Dekina", "Ibaji", "Idah", "Igalamela Odolu", "Ijumu", "Kabba/Bunu", "Kogi", "Lokoja", "Mopa Muro", "Ofu", "Ogori/Magongo", "Okehi", "Okene", "Olamaboro", "Omala", "Yagba East", "Yagba West"],
        "Kwara": ["Asa", "Baruten", "Edu", "Ekiti", "Ilorin East", "Ilorin South", "Ilorin West", "Irepodun", "Isin", "Kaiama", "Moro", "Offa", "Oke Ero", "Oyun", "Pategi"],
        "Lagos": ["Agege", "Ajeromi-Ifelodun", "Alimosho", "Amuwo-Odofin", "Apapa", "Badagry", "Epe", "Eti Osa", "Ibeju-Lekki", "Ifako-Ijaiye", "Ikeja", "Ikorodu", "Kosofe", "Lagos Island", "Lagos Mainland", "Mushin", "Ojo", "Oshodi-Isolo", "Shomolu", "Surulere"],
        "Nasarawa": ["Akwanga", "Awe", "Doma", "Karu", "Keana", "Keffi", "Kokona", "Lafia", "Nasarawa", "Nasarawa Egon", "Obi", "Toto", "Wamba"],
        "Niger": ["Agaie", "Agwara", "Bida", "Borgu", "Bosso", "Chanchaga", "Edati", "Gbako", "Gurara", "Katcha", "Kontagora", "Lapai", "Lavun", "Magama", "Mariga", "Mashegu", "Mokwa", "Moya", "Paikoro", "Rafi", "Rijau", "Shiroro", "Suleja", "Tafa", "Wushishi"],
        "Ogun": ["Abeokuta North", "Abeokuta South", "Ado-Odo/Ota", "Egbado North", "Egbado South", "Ewekoro", "Ifo", "Ijebu East", "Ijebu North", "Ijebu North East", "Ijebu Ode", "Ikenne", "Imeko Afon", "Ipokia", "Obafemi Owode", "Odeda", "Odogbolu", "Ogun Waterside", "Remo North", "Shagamu"],
        "Ondo": ["Akoko North-East", "Akoko North-West", "Akoko South-West", "Akoko South-East", "Akure North", "Akure South", "Ese Odo", "Idanre", "Ifedore", "Ilaje", "Ile Oluji/Okeigbo", "Irele", "Odigbo", "Okitipupa", "Ondo East", "Ondo West", "Ose", "Owo"],
        "Osun": ["Atakunmosa East", "Atakunmosa West", "Aiyedaade", "Aiyedire", "Boluwaduro", "Boripe", "Ede North", "Ede South", "Ife Central", "Ife East", "Ife North", "Ife South", "Egbedore", "Ila", "Ilesa East", "Ilesa West", "Irepodun", "Irewole", "Isokan", "Iwo", "Obokun", "Odo Otin", "Ola Oluwa", "Olorunda", "Oriade", "Orolu", "Osogbo"],
        "Oyo": ["Afijio", "Akinyele", "Atiba", "Atisbo", "Egbeda", "Ibadan North", "Ibadan North-East", "Ibadan North-West", "Ibadan South-East", "Ibadan South-West", "Ibarapa Central", "Ibarapa East", "Ibarapa North", "Ido", "Irepo", "Iseyin", "Itesiwaju", "Iwajowa", "Kajola", "Lagelu", "Ogbomosho North", "Ogbomosho South", "Ogo Oluwa", "Olorunsogo", "Oluyole", "Ona Ara", "Orelope", "Ori Ire", "Oyo East", "Oyo West", "Saki East", "Saki West", "Surulere"],
        "Plateau": ["Bokkos", "Barkin Ladi", "Bassa", "Jos East", "Jos North", "Jos South", "Kanam", "Kanke", "Langtang North", "Langtang South", "Mangu", "Mikang", "Pankshin", "Qua'an Pan", "Riyom", "Jos North", "Shendam", "Wase"],
        "Rivers": ["Abua/Odual", "Ahoada East", "Ahoada West", "Akuku Toru", "Andoni", "Asari-Toru", "Bonny", "Degema", "Eleme", "Emuoha", "Etche", "Gokana", "Ikwerre", "Khana", "Obio/Akpor", "Ogba/Egbema/Ndoni", "Ogu/Bolo", "Okrika", "Omuma", "Opobo/Nkoro", "Oyigbo", "Port Harcourt", "Tai"],
        "Sokoto": ["Binji", "Bodinga", "Dange Shuni", "Gada", "Goronyo", "Gudu", "Gwadabawa", "Illela", "Isa", "Kebbe", "Kware", "Rabah", "Sabon Birni", "Shagari", "Silame", "Sokoto North", "Sokoto South", "Tambuwal", "Tangaza", "Tureta", "Wamako", "Wurno", "Yabo"],
        "Taraba": ["Ardo Kola", "Bali", "Donga", "Gashaka", "Gassol", "Ibi", "Jalingo", "Karim Lamido", "Kumi", "Lau", "Sardauna", "Takum", "Ussa", "Wukari", "Yorro", "Zing"],
        "Yobe": ["Bade", "Bursari", "Damaturu", "Fika", "Fune", "Geidam", "Gujba", "Gulani", "Jakusko", "Karasuwa", "Machina", "Nangere", "Nguru", "Potiskum", "Tarmuwa", "Yunusari", "Yusufari"],
        "Zamfara": ["Anka", "Bakura", "Birnin Magaji/Kiyaw", "Bukkuyum", "Bungudu", "Gummi", "Gusau", "Kaura Namoda", "Maradun", "Maru", "Shinkafi", "Talata Mafara", "Chafe", "Zurmi"]
    };

    function loadStates() {
        const stateSelect = document.getElementById('state');
        if (!stateSelect) return;
        
        // Preserve default option
        stateSelect.innerHTML = '<option value="">Select State</option>';
        
        // Populate alphabetically sorted states
        Object.keys(NIGERIA_STATES_AND_LGAS).sort().forEach(stateName => {
            const opt = document.createElement('option');
            opt.value = stateName;
            opt.textContent = stateName;
            stateSelect.appendChild(opt);
        });
    }

    function handleStateChange(stateName) {
        const lgaSelect = document.getElementById('lga');
        if (!lgaSelect) return;

        // Reset LGA list
        lgaSelect.innerHTML = '<option value="">Select LGA</option>';

        if (stateName && NIGERIA_STATES_AND_LGAS[stateName]) {
            NIGERIA_STATES_AND_LGAS[stateName].forEach(lga => {
                const opt = document.createElement('option');
                opt.value = lga;
                opt.textContent = lga;
                lgaSelect.appendChild(opt);
            });
        }
    }

    async function handleRegisterSubmit(e) {
        e.preventDefault();

        const isDep = (registrationMode === 'dependant');
        let serviceNo = document.getElementById('immigration_service_number').value.trim();

        if (isDep && !serviceNo) {
            // Generate a unique dependant service code format
            serviceNo = 'NIS/DEP/' + Math.floor(10000 + Math.random() * 90000);
        }

        const payload = {
            first_name: document.getElementById('first_name').value,
            middle_name: document.getElementById('middle_name').value || null,
            last_name: document.getElementById('last_name').value,
            gender: document.getElementById('gender').value,
            date_of_birth: document.getElementById('date_of_birth').value,
            phone: document.getElementById('phone').value,
            email: document.getElementById('email').value || null,
            immigration_service_number: serviceNo || null,
            sponsor_service_number: isDep ? document.getElementById('sponsor_service_number').value.trim() : null,
            relationship_to_sponsor: isDep ? document.getElementById('relationship_to_sponsor').value : null,
            nin: document.getElementById('nin').value || null,
            blood_group: document.getElementById('blood_group').value,
            genotype: document.getElementById('genotype').value,
            state: document.getElementById('state').value || null,
            lga: document.getElementById('lga').value || null,
            address: document.getElementById('address').value,
            allergies: document.getElementById('allergies').value || null,
            disability: document.getElementById('disability').value || 'None'
        };

        try {
            const res = await api.post('/patients', payload);
            const pat = res.patient;
            
            // Populate Success Modal Fields
            document.getElementById('success-patient-name').innerText = pat.full_name;
            document.getElementById('success-patient-code').innerText = pat.immigration_service_number;
            
            // Close register modal and open success modal
            closeRegisterModal();
            document.getElementById('success-modal').classList.remove('hidden');
            
            // Reload patient list
            loadPatients();
        } catch (err) {
            alert(err.message || 'Failed to register patient file.');
        }
    }

    function closeSuccessModal() {
        document.getElementById('success-modal').classList.add('hidden');
    }

    function switchDetailsTab(tabName) {
        const tabBtnTimeline = document.getElementById('tab-btn-timeline');
        const tabBtnMedications = document.getElementById('tab-btn-medications');
        const tabBtnDiagnostics = document.getElementById('tab-btn-diagnostics');
        const detTimeline = document.getElementById('det-timeline');
        const detMedications = document.getElementById('det-medications');
        const detDiagnostics = document.getElementById('det-diagnostics');

        if (!tabBtnTimeline || !tabBtnMedications || !detTimeline || !detMedications) return;

        // Reset all buttons and panels
        const activeBtnClass = 'border-b-2 border-emerald-600 px-4 py-2 text-xs font-bold text-emerald-650 focus:outline-none transition-all';
        const inactiveBtnClass = 'border-b-2 border-transparent px-4 py-2 text-xs font-bold text-slate-500 hover:text-slate-700 dark:hover:text-slate-350 focus:outline-none transition-all';

        tabBtnTimeline.className = inactiveBtnClass;
        tabBtnMedications.className = inactiveBtnClass;
        if (tabBtnDiagnostics) tabBtnDiagnostics.className = inactiveBtnClass;

        detTimeline.classList.add('hidden');
        detMedications.classList.add('hidden');
        if (detDiagnostics) detDiagnostics.classList.add('hidden');

        // Activate selected
        if (tabName === 'timeline') {
            tabBtnTimeline.className = activeBtnClass;
            detTimeline.classList.remove('hidden');
        } else if (tabName === 'medications') {
            tabBtnMedications.className = activeBtnClass;
            detMedications.classList.remove('hidden');
        } else if (tabName === 'diagnostics') {
            if (tabBtnDiagnostics) tabBtnDiagnostics.className = activeBtnClass;
            if (detDiagnostics) detDiagnostics.classList.remove('hidden');
        }
    }

    async function handleShowDetails(id) {
        try {
            const res = await api.get(`/patients/${id}`);
            const pat = res.patient;
            const timeline = res.timeline;

            // Reset tabs to timeline default view
            switchDetailsTab('timeline');

            // Inject demographic details
            document.getElementById('det-full-name').innerText = pat.full_name;
            document.getElementById('det-service-code').innerText = pat.immigration_service_number;
            document.getElementById('det-dob').innerText = pat.date_of_birth;
            document.getElementById('det-gender').innerText = pat.gender;
            document.getElementById('det-phone').innerText = pat.phone;
            document.getElementById('det-nin').innerText = pat.nin || '—';
            document.getElementById('det-blood').innerText = pat.blood_group || '—';
            document.getElementById('det-genotype').innerText = pat.genotype || '—';
            document.getElementById('det-state').innerText = pat.state || '—';
            document.getElementById('det-lga').innerText = pat.lga || '—';
            document.getElementById('det-address').innerText = pat.address || '—';
            document.getElementById('det-allergies').innerText = pat.allergies || 'None';
            document.getElementById('det-disability').innerText = pat.disability || 'None';

            // Inject Dependant / Family information
            const sponsorCard = document.getElementById('det-sponsor-info');
            const sponsorName = document.getElementById('det-sponsor-name');
            const sponsorCode = document.getElementById('det-sponsor-code');
            
            const depsListGroup = document.getElementById('det-dependants-list-group');
            const depsList = document.getElementById('det-dependants-list');
            const noFamily = document.getElementById('det-no-family');

            // Default hidden
            if (sponsorCard) sponsorCard.classList.add('hidden');
            if (depsListGroup) depsListGroup.classList.add('hidden');
            if (noFamily) noFamily.classList.remove('hidden');

            let hasFamily = false;

            if (pat.sponsor) {
                hasFamily = true;
                if (noFamily) noFamily.classList.add('hidden');
                if (sponsorCard) {
                    sponsorCard.classList.remove('hidden');
                    if (sponsorName) sponsorName.innerText = pat.sponsor.full_name;
                    if (sponsorCode) sponsorCode.innerText = pat.sponsor.immigration_service_number;
                }
            }
            
            if (pat.dependants && pat.dependants.length > 0) {
                hasFamily = true;
                if (noFamily) noFamily.classList.add('hidden');
                if (depsListGroup) {
                    depsListGroup.classList.remove('hidden');
                    if (depsList) {
                        depsList.innerHTML = pat.dependants.map(dep => `
                            <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200/60 dark:border-slate-800 text-[10px] gap-2">
                                <div class="truncate">
                                    <b class="text-slate-800 dark:text-white block truncate">${dep.full_name}</b>
                                    <span class="text-slate-450 dark:text-slate-500 font-mono block mt-0.5 truncate">${dep.immigration_service_number} (${dep.relationship_to_sponsor})</span>
                                </div>
                                <button type="button" onclick="handleShowDetails(${dep.id})" class="text-emerald-600 hover:underline font-bold shrink-0">
                                    View File
                                </button>
                            </div>
                        `).join('');
                    }
                }
            }

            // Inject clinical timeline
            const timBody = document.getElementById('det-timeline');
            if (timeline.length > 0) {
                timBody.innerHTML = timeline.map(event => {
                    let icon = 'clock';
                    let color = 'text-slate-500 bg-slate-100';
                    if (event.type === 'consultation') {
                        icon = 'stethoscope';
                        color = 'text-emerald-650 bg-emerald-50/10 border border-emerald-500/20';
                    } else if (event.type === 'appointment') {
                        icon = 'calendar';
                        color = 'text-blue-600 bg-blue-500/10 border border-blue-500/20';
                    }

                    return `
                        <div class="flex gap-3 items-start text-xs p-3 hover:bg-slate-50 dark:hover:bg-slate-800/40 rounded-xl">
                            <div class="p-2 rounded-full ${color} shrink-0 mt-0.5">
                                <i data-lucide="${icon}" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <span class="text-[9px] text-slate-500 font-bold block mb-0.5">${event.date}</span>
                                <h5 class="font-bold text-slate-850 dark:text-white">${event.title}</h5>
                                <p class="text-slate-800 dark:text-slate-200 text-[11px] mt-0.5">${event.description}</p>
                            </div>
                        </div>
                    `;
                }).join('');
            } else {
                timBody.innerHTML = `<p class="text-xs text-slate-800 dark:text-slate-200">No clinical timeline events recorded.</p>`;
            }

            // Inject Medication / Prescriptions History
            const medBody = document.getElementById('det-medications');
            if (medBody) {
                if (pat.medications && pat.medications.length > 0) {
                    medBody.innerHTML = pat.medications.map(pres => {
                        const itemsMarkup = pres.items.map(item => `
                            <div class="flex justify-between items-center text-[10px] bg-white dark:bg-slate-900 border border-slate-200/50 dark:border-slate-800/60 p-2 rounded-lg gap-2">
                                <div>
                                    <b class="text-slate-805 dark:text-white">${item.drug_name}</b>
                                    <span class="text-slate-500 block mt-0.5">Dosage: ${item.dosage} | Freq: ${item.frequency} | Duration: ${item.duration_days} days</span>
                                    ${item.instructions ? `<span class="text-slate-450 dark:text-slate-500 italic mt-0.5 block">Note: "${item.instructions}"</span>` : ''}
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="px-1.5 py-0.5 rounded font-black text-[8px] uppercase tracking-wider ${
                                        item.status === 'dispensed' 
                                        ? 'bg-emerald-500/10 text-emerald-600 border border-emerald-500/20' 
                                        : 'bg-amber-500/10 text-amber-600 border border-amber-500/20'
                                    }">
                                        ${item.status}
                                    </span>
                                </div>
                            </div>
                        `).join('');

                        return `
                            <div class="p-3 bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-850 rounded-xl space-y-2">
                                <div class="flex justify-between items-center border-b border-slate-200/50 dark:border-slate-800/60 pb-1.5 text-[10px]">
                                    <div>
                                        <span class="text-slate-400">Date:</span>
                                        <b class="text-slate-800 dark:text-slate-200">${new Date(pres.date).toLocaleDateString()}</b>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-slate-400">Prescribed by Dr. </span>
                                        <b class="text-slate-800 dark:text-slate-200">${pres.doctor_name}</b>
                                    </div>
                                </div>
                                <div class="space-y-1.5">
                                    ${itemsMarkup}
                                </div>
                            </div>
                        `;
                    }).join('');
                } else {
                    medBody.innerHTML = `<p class="text-xs text-slate-800 dark:text-slate-200">No historical prescriptions found for this patient file.</p>`;
                }
            }

            // Inject Diagnostics History
            const diagBody = document.getElementById('det-diagnostics');
            if (diagBody) {
                const diagnosticsList = res.diagnostics || [];
                if (diagnosticsList.length > 0) {
                    diagBody.innerHTML = diagnosticsList.map(d => {
                        const isLab = d.type === 'lab';
                        const typeBadge = isLab 
                            ? 'bg-violet-100 text-violet-850 dark:bg-violet-500/20 dark:text-violet-400 font-bold border border-violet-500/10' 
                            : 'bg-blue-100 text-blue-850 dark:bg-blue-500/20 dark:text-blue-400 font-bold border border-blue-500/10';

                        return `
                            <div class="p-4 rounded-2xl border border-slate-150 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/20 space-y-2 text-xs">
                                <div class="flex justify-between items-start gap-4 border-b border-slate-200/60 dark:border-slate-800 pb-2">
                                    <div>
                                        <span class="px-2 py-0.5 rounded uppercase tracking-wider text-[8px] ${typeBadge}">
                                            ${isLab ? 'LAB REPORT' : 'RADIOLOGY SCAN'}
                                        </span>
                                        <h5 class="font-bold text-slate-900 dark:text-white text-sm mt-1 mb-0.5">${d.test_name}</h5>
                                        <p class="text-[9px] text-slate-500 font-semibold">Ordered by Dr. ${d.doctor_name}</p>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <span class="text-[9px] text-slate-400 block mb-0.5">Completed Date</span>
                                        <b class="text-[10px] text-slate-700 dark:text-slate-300">${new Date(d.completed_at).toLocaleString()}</b>
                                    </div>
                                </div>
                                <div class="pt-1">
                                    <div class="p-3 bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-xl">
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Result & Findings</span>
                                        <p class="text-slate-800 dark:text-slate-200 font-medium text-sm">${d.result_value}</p>
                                        
                                        ${isLab ? `
                                            <div class="mt-2 text-[10px] bg-slate-50 dark:bg-slate-950 p-2 rounded-lg border border-slate-100 dark:border-slate-850">
                                                <span class="text-slate-500">Normal Reference Range:</span> 
                                                <b class="text-slate-700 dark:text-slate-300">${d.normal_range}</b>
                                            </div>
                                        ` : ''}
                                    </div>
                                    <div class="flex items-center justify-between mt-3 text-[10px]">
                                        <div>
                                            <span class="text-slate-400">Scientist / Radiographer:</span>
                                            <b class="text-slate-700 dark:text-slate-300">${d.scientist_name}</b>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-slate-400">Remarks:</span>
                                            <i class="text-slate-600 dark:text-slate-400">${d.remarks || 'None'}</i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    }).join('');
                } else {
                    diagBody.innerHTML = `<p class="text-xs text-slate-800 dark:text-slate-200">No diagnostic reports found for this patient file.</p>`;
                }
            }

            document.getElementById('details-modal').classList.remove('hidden');
            lucide.createIcons();
        } catch (err) {
            alert('Failed to fetch patient file history.');
        }
    }

    function closeDetailsModal() {
        document.getElementById('details-modal').classList.add('hidden');
    }

    // Initialize Register Patient button permissions
    document.addEventListener('DOMContentLoaded', () => {
        const role = user.roles && user.roles[0] ? user.roles[0].name : '';
        if (['super_admin', 'records_officer', 'receptionist'].includes(role)) {
            document.getElementById('register-btn-container').classList.remove('hidden');
        }

        // Populate States list dynamically
        const stateSelect = document.getElementById('state');
        if (stateSelect) {
            Object.keys(NIGERIA_STATES_AND_LGAS).forEach(state => {
                const opt = document.createElement('option');
                opt.value = state;
                opt.textContent = state;
                stateSelect.appendChild(opt);
            });
        }

        // Real-time age validator trigger on DOB change
        const dobInput = document.getElementById('date_of_birth');
        if (dobInput) {
            dobInput.addEventListener('change', validateDependantAge);
        }

        lucide.createIcons();
    });
</script>
@endsection
