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

    <!-- My Assigned Patients (doctors only) -->
    <div id="assigned-panel" class="hidden bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800/80 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex items-center gap-2">
            <div class="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600"><i data-lucide="stethoscope" class="w-4 h-4"></i></div>
            <div>
                <h3 class="text-xs font-bold text-slate-800 dark:text-white">My Assigned Patients</h3>
                <p class="text-[10px] text-slate-500 dark:text-slate-400">Patients you are the consulting clinician for. You can also call up any patient below.</p>
            </div>
            <span id="assigned-count" class="ml-auto text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 px-2 py-1 rounded-full"></span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-200 border-b border-slate-100 dark:border-slate-800">
                    <tr>
                        <th class="py-2.5 px-4 font-bold">Patient</th>
                        <th class="py-2.5 px-4 font-bold">Hospital Code</th>
                        <th class="py-2.5 px-4 font-bold">Encounters</th>
                        <th class="py-2.5 px-4 font-bold">Last Seen</th>
                        <th class="py-2.5 px-4 font-bold text-right">Action</th>
                    </tr>
                </thead>
                <tbody id="assigned-body" class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300"></tbody>
            </table>
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
                    <h3 class="text-xs font-bold text-slate-800 dark:text-white">Patient Lookup</h3>
                    <p class="text-[10px] text-slate-500 dark:text-slate-400">Search by Hospital Code, name, NIN or phone number</p>
                </div>
            </div>
            <div class="relative flex-grow max-w-md">
                <input type="text" id="search-input" oninput="handleSearch(this.value)"
                       placeholder="Search by code, name, NIN or phone…"
                       class="w-full pl-10 pr-4 py-3 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 rounded-xl text-xs focus:outline-none focus:ring-1 focus:ring-emerald-500 text-slate-800 dark:text-slate-100 placeholder-slate-400 transition-all tracking-wide">
                <i data-lucide="badge-check" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-emerald-500 w-4 h-4"></i>
            </div>
            <div class="text-[10px] text-amber-600 dark:text-amber-400 flex items-center gap-1.5 font-bold shrink-0 bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 px-3 py-2 rounded-xl">
                <i data-lucide="shield-alert" class="w-3.5 h-3.5"></i> Min. 3 characters
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
                            <p class="text-sm font-bold text-slate-700 dark:text-slate-300">Search for a patient to begin</p>
                            <p class="text-xs text-slate-400">Patient records are protected and require a valid Hospital Code to access.</p>
                        </div>
                    </td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Register Patient Modal -->
<div id="register-modal" class="hidden fixed inset-0 z-50 overflow-y-auto p-4 bg-slate-950/60 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 w-full max-w-2xl shadow-2xl relative my-8 mx-auto overflow-hidden">
        <!-- Branded building-image banner header -->
        <div class="-mx-6 -mt-6 mb-5 relative h-24 bg-cover bg-center" style="background-image:url('/images/nis_building_day.jpg');">
            <div class="absolute inset-0 bg-gradient-to-r from-emerald-900/90 to-emerald-800/70"></div>
            <div class="relative h-full flex items-center gap-3 px-6">
                <div class="w-11 h-11 rounded-xl bg-white flex items-center justify-center p-1 shadow-md shrink-0">
                    <img src="/images/nis_logo.jpg" alt="NIS" class="w-9 h-9 object-contain">
                </div>
                <div class="text-white">
                    <h3 class="text-base font-black uppercase tracking-wider leading-tight">Register Patient File</h3>
                    <p class="text-[10px] text-emerald-100 font-semibold">Nigeria Immigration Service Hospital</p>
                </div>
                <button onclick="closeRegisterModal()" class="ml-auto text-white/80 hover:text-white transition focus:outline-none">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
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
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label id="mode-card-officer" class="reg-mode-card flex items-start gap-3 cursor-pointer rounded-xl border-2 border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 p-3 transition hover:border-emerald-400">
                            <input type="radio" name="registration_mode" id="mode-officer" value="officer" onchange="handleModeChange(this.value)" class="mt-0.5 text-emerald-600 focus:ring-emerald-500">
                            <span>
                                <span class="flex items-center gap-1.5 text-xs font-black text-slate-800 dark:text-slate-100 uppercase tracking-wide">
                                    <i data-lucide="shield-check" class="w-4 h-4 text-emerald-600"></i> NIS Officer
                                </span>
                                <span class="block text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">Verify by Service Number against the ID Card Portal, then auto-fill the officer's details.</span>
                            </span>
                        </label>
                        <label id="mode-card-civilian" class="reg-mode-card flex items-start gap-3 cursor-pointer rounded-xl border-2 border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 p-3 transition hover:border-emerald-400">
                            <input type="radio" name="registration_mode" id="mode-civilian" value="civilian" checked onchange="handleModeChange(this.value)" class="mt-0.5 text-emerald-600 focus:ring-emerald-500">
                            <span>
                                <span class="flex items-center gap-1.5 text-xs font-black text-slate-800 dark:text-slate-100 uppercase tracking-wide">
                                    <i data-lucide="user" class="w-4 h-4 text-emerald-600"></i> Civilian
                                </span>
                                <span class="block text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">Enter the patient's details manually. Dependants can be added.</span>
                            </span>
                        </label>
                    </div>
                </div>

                <!-- Officer verification (NIS Officer mode only): Service Number first -->
                <div id="officer-lookup-block" class="hidden mb-4">
                    <div class="bg-emerald-50/60 dark:bg-emerald-500/5 p-4 rounded-2xl border border-emerald-200 dark:border-emerald-500/20">
                        <label class="block text-[10px] font-bold text-slate-855 dark:text-slate-200 uppercase tracking-wider mb-2">Officer Service Number <span class="text-red-500">*</span></label>
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-end gap-3">
                            <div class="flex-grow">
                                <input type="text" id="officer_service_number" data-filter="digits" inputmode="numeric" maxlength="5" placeholder="e.g. 48213" onkeydown="if(event.key==='Enter'){event.preventDefault();handleVerifyOfficer();}" class="w-full bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-855 dark:text-slate-250 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                            </div>
                            <button type="button" id="verify-officer-btn" onclick="handleVerifyOfficer()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl text-xs font-bold transition shadow-sm h-9 shrink-0 flex items-center justify-center gap-1">
                                <i data-lucide="search" class="w-4 h-4"></i> Verify &amp; Fetch
                            </button>
                        </div>
                        <p class="text-[9px] text-slate-500 dark:text-slate-400 mt-2">This confirms the person is a serving officer of the Nigeria Immigration Service. Fields unlock once the officer is verified.</p>

                        <!-- Verification result card -->
                        <div id="officer-verify-status" class="hidden mt-3 p-3 rounded-xl border border-emerald-200 dark:border-emerald-500/20 bg-white dark:bg-slate-950 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2.5">
                                <div class="p-2 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600">
                                    <i data-lucide="user-check" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-slate-800 dark:text-white" id="officer-fullname-label">Officer verified</h4>
                                    <p class="text-[9px] text-slate-500 dark:text-slate-400" id="officer-meta-label"></p>
                                </div>
                            </div>
                            <span class="text-[8px] bg-emerald-100 text-emerald-800 font-bold px-2 py-0.5 rounded uppercase tracking-wider whitespace-nowrap">Verified</span>
                        </div>
                        <!-- Already-registered notice -->
                        <div id="officer-already-registered" class="hidden mt-3 p-3 rounded-xl border border-amber-200 dark:border-amber-500/20 bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 text-[10px] font-semibold">
                            <div class="flex items-start gap-2">
                                <i data-lucide="info" class="w-4 h-4 shrink-0 mt-0.5"></i>
                                <span id="officer-already-registered-text">This officer already has a patient file. You can add dependants to their existing file below.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Standalone Demographic Fields (Hidden in Dependant Mode) -->
                <div id="standalone-demographics-container" class="space-y-4">
                    <!-- Passport photograph (used on the Patient ID Card) -->
                    <div class="flex items-center gap-4 border-b border-slate-100 dark:border-slate-800 pb-4 mb-4">
                        <div class="shrink-0">
                            <img id="photo-preview" alt="" class="w-20 h-24 object-cover rounded-lg border border-slate-300 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 hidden">
                            <div id="photo-placeholder" class="w-20 h-24 rounded-lg border border-dashed border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/40 flex items-center justify-center text-slate-400">
                                <i data-lucide="user" class="w-8 h-8"></i>
                            </div>
                        </div>
                        <div class="flex-1">
                            <label class="block text-[10px] font-bold text-slate-850 dark:text-slate-200 uppercase tracking-wider mb-1">Passport Photograph</label>
                            <input type="file" id="passport_photo" accept="image/png,image/jpeg,image/webp" onchange="previewPhoto(this)" class="text-xs file:mr-2 file:rounded-lg file:border-0 file:bg-emerald-600 file:text-white file:px-3 file:py-1.5 file:text-xs file:font-bold file:cursor-pointer">
                            <p class="text-[9px] text-slate-400 mt-1">JPG / PNG / WEBP, up to 4&nbsp;MB. Appears on the patient's ID card.</p>
                        </div>
                    </div>

                    <!-- Standalone Name Inputs -->
                    <div id="standalone-names-group" class="grid grid-cols-1 sm:grid-cols-3 gap-4 border-b border-slate-100 dark:border-slate-800 pb-4 mb-4">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-850 dark:text-slate-200 uppercase tracking-wider mb-1">First Name</label>
                            <input type="text" id="first_name" data-filter="letters" maxLength="60" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-250 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-855 dark:text-slate-200 uppercase tracking-wider mb-1">Middle Name (Optional)</label>
                            <input type="text" id="middle_name" data-filter="letters" maxLength="60" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-250 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-855 dark:text-slate-200 uppercase tracking-wider mb-1">Last Name</label>
                            <input type="text" id="last_name" data-filter="letters" maxLength="60" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-250 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-855 dark:text-slate-200 uppercase tracking-wider mb-1">Gender</label>
                            <select id="gender" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <!-- Standalone DOB -->
                        <div id="standalone-dob-group">
                            <label class="block text-[10px] font-bold text-slate-855 dark:text-slate-200 uppercase tracking-wider mb-1">Date of Birth <span id="age-badge" class="hidden ml-1 normal-case text-emerald-600 dark:text-emerald-400 font-bold"></span></label>
                            <input type="date" id="date_of_birth" oninput="updateAgeDisplay()" onchange="updateAgeDisplay()" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-850 dark:text-slate-250 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-855 dark:text-slate-200 uppercase tracking-wider mb-1">Phone Number</label>
                            <input type="text" id="phone" required inputmode="numeric" data-filter="phone" maxLength="15" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-250 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-855 dark:text-slate-200 uppercase tracking-wider mb-1">Email Address</label>
                            <input type="email" id="email" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-250 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        </div>
                        <div id="standalone-service-group" class="hidden">
                            <label class="block text-[10px] font-bold text-slate-855 dark:text-slate-200 uppercase tracking-wider mb-1">Immigration Service Number</label>
                            <input type="text" id="immigration_service_number" data-filter="digits" inputmode="numeric" maxlength="5" placeholder="Auto-filled from the ID Card Portal" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-250 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-855 dark:text-slate-200 uppercase tracking-wider mb-1">National Identification Number (NIN)</label>
                            <input type="text" id="nin" maxLength="11" inputmode="numeric" data-filter="digits" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-250 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        </div>
                    </div>

                    <!-- NHIS coverage -->
                    <div class="bg-slate-50 dark:bg-slate-950/40 p-4 rounded-2xl border border-slate-200 dark:border-slate-800/80 mt-4">
                        <label class="block text-[10px] font-bold text-slate-855 dark:text-slate-200 uppercase tracking-wider mb-2">Is this patient covered under NHIS?</label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-800 dark:text-slate-200">
                                <input type="radio" name="nhis_status" value="yes" onchange="handleNhisChange(this.value)" class="text-emerald-600 focus:ring-emerald-500"> Yes (NHIS covered)
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-800 dark:text-slate-200">
                                <input type="radio" name="nhis_status" value="no" checked onchange="handleNhisChange(this.value)" class="text-emerald-600 focus:ring-emerald-500"> No (pays cash)
                            </label>
                        </div>
                        <div id="nhis-number-group" class="hidden mt-3">
                            <label class="block text-[10px] font-bold text-slate-855 dark:text-slate-200 uppercase tracking-wider mb-1">NHIS Valid Number <span class="text-red-500">*</span></label>
                            <input type="text" id="nhis_number" data-filter="code" maxLength="60" placeholder="e.g. NHIS-1234567" class="w-full bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-250 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        </div>
                        <p id="nhis-cost-note" class="text-[10px] text-amber-600 dark:text-amber-400 font-semibold mt-2">Non-NHIS: a registration fee and full service charges apply.</p>
                    </div>
                </div>
                <!-- Additional bio-data -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-855 dark:text-slate-200 uppercase tracking-wider mb-1">Marital Status <span class="text-red-500">*</span></label>
                        <select id="marital_status" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                            <option value="">Select…</option>
                            <option value="Single">Single</option>
                            <option value="Married">Married</option>
                            <option value="Divorced">Divorced</option>
                            <option value="Widowed">Widowed</option>
                            <option value="Separated">Separated</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-855 dark:text-slate-200 uppercase tracking-wider mb-1">Religion</label>
                        <select id="religion" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                            <option value="">Select…</option>
                            <option value="Christianity">Christianity</option>
                            <option value="Islam">Islam</option>
                            <option value="Traditional">Traditional</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-855 dark:text-slate-200 uppercase tracking-wider mb-1">Occupation <span class="normal-case text-slate-400">(for non-officers)</span></label>
                        <input type="text" id="occupation" maxLength="100" placeholder="e.g. Teacher, Trader" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-250 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-855 dark:text-slate-200 uppercase tracking-wider mb-1">Place of Birth</label>
                        <input type="text" id="place_of_birth" maxLength="100" placeholder="e.g. Kaduna" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-250 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-855 dark:text-slate-200 uppercase tracking-wider mb-1">Tribe / Ethnicity</label>
                        <input type="text" id="tribe" data-filter="letters" maxLength="60" placeholder="e.g. Hausa, Igbo, Yoruba" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-250 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    </div>
                </div>
            </div>

            <!-- STEP 2: Dependant Details -->
            <div id="step-section-2" class="hidden space-y-4">
                <!-- Mode-adaptive intro -->
                <div id="dependant-intro" class="p-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-700 dark:text-emerald-400 rounded-2xl text-xs">
                    <div class="flex items-start gap-2">
                        <i data-lucide="users" class="w-4 h-4 shrink-0 mt-0.5"></i>
                        <span id="dependant-intro-text">Optionally add dependants (spouse/children) for this officer/civilian. They will be tied to this file automatically. You can also skip and click Next.</span>
                    </div>
                </div>

                <!-- Dependant inputs group -->
                <div id="dependant-active-inputs" class="space-y-4">
                    <div class="p-3 bg-amber-500/10 border border-amber-500/20 text-amber-600 rounded-xl text-[10px] font-black flex items-center gap-2">
                        <i data-lucide="shield-alert" class="w-4 h-4 shrink-0"></i>
                        <span>NOTE: Dependants are limited to 1 Wife and 3 Children. No age limit applies.</span>
                    </div>

                    <!-- Dependants are tied to the officer/civilian being registered
                         on this form; the officer is verified in Step 1. -->
                    <input type="hidden" id="sponsor_service_number" value="">

                    <!-- Dependant Name & Details -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-850 dark:text-slate-200 uppercase tracking-wider mb-1">First Name</label>
                            <input type="text" id="dep_first_name" data-filter="letters" maxLength="60" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-250 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-855 dark:text-slate-200 uppercase tracking-wider mb-1">Middle Name (Optional)</label>
                            <input type="text" id="dep_middle_name" data-filter="letters" maxLength="60" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-250 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-855 dark:text-slate-200 uppercase tracking-wider mb-1">Gender</label>
                            <select id="dep_gender" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-855 dark:text-slate-200 uppercase tracking-wider mb-1">Date of Birth</label>
                            <input type="date" id="dep_date_of_birth" onchange="validateDependantAge()" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-850 dark:text-slate-250 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-855 dark:text-slate-200 uppercase tracking-wider mb-1">Relationship to Sponsor</label>
                            <select id="relationship_to_sponsor" onchange="validateDependantAge()" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                <option value="Son">Son</option>
                                <option value="Daughter">Daughter</option>
                                <option value="Ward">Ward</option>
                                <option value="Wife">Wife</option>
                            </select>
                        </div>
                        <!-- Per-dependant medical markers (each dependant is independent) -->
                        <div>
                            <label class="block text-[10px] font-bold text-slate-855 dark:text-slate-200 uppercase tracking-wider mb-1">Blood Group</label>
                            <select id="dep_blood_group" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                <option value="A+">A+</option><option value="A-">A-</option>
                                <option value="B+">B+</option><option value="B-">B-</option>
                                <option value="AB+">AB+</option><option value="AB-">AB-</option>
                                <option value="O+">O+</option><option value="O-">O-</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-855 dark:text-slate-200 uppercase tracking-wider mb-1">Genotype</label>
                            <select id="dep_genotype" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                <option value="AA">AA</option><option value="AS">AS</option>
                                <option value="SS">SS</option><option value="AC">AC</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-855 dark:text-slate-200 uppercase tracking-wider mb-1">National Identification Number (NIN) <span class="normal-case text-slate-400">(optional)</span></label>
                            <input type="text" id="dep_nin" maxLength="11" inputmode="numeric" data-filter="digits" placeholder="11 digits (optional)" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-250 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-855 dark:text-slate-200 uppercase tracking-wider mb-1">Allergies (optional)</label>
                            <input type="text" id="dep_allergies" maxLength="255" placeholder="e.g. Penicillin" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-250 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-855 dark:text-slate-200 uppercase tracking-wider mb-1">Disabilities (optional)</label>
                            <input type="text" id="dep_disability" maxLength="255" placeholder="e.g. None" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-250 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-[10px] font-bold text-slate-855 dark:text-slate-200 uppercase tracking-wider mb-1">Passport Photograph (optional)</label>
                            <input type="file" id="dep_passport_photo" accept="image/png,image/jpeg,image/webp" class="text-xs file:mr-2 file:rounded-lg file:border-0 file:bg-emerald-600 file:text-white file:px-3 file:py-1.5 file:text-xs file:font-bold file:cursor-pointer">
                            <p class="text-[9px] text-slate-400 mt-1">Appears on this dependant's ID card. Set before clicking "Add Dependant".</p>
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

                    <!-- Add Dependant Action Button -->
                    <div class="flex justify-end pt-2 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" onclick="handleAddDependantClick()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl text-xs font-bold transition shadow-md flex items-center gap-1.5 cursor-pointer">
                            <i data-lucide="plus-circle" class="w-4 h-4"></i> Add Dependant
                        </button>
                    </div>

                    <!-- List of Pending Dependants -->
                    <div id="pending-deps-container" class="hidden mt-4 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 bg-slate-50/50 dark:bg-slate-950/20">
                        <h4 class="text-xs font-bold text-slate-800 dark:text-white uppercase tracking-wider mb-2">Dependants to be Registered</h4>
                        <div id="pending-deps-list" class="space-y-2">
                            <!-- Pending dependants markup injected here -->
                        </div>
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
                    <div>
                        <label class="block text-[10px] font-bold text-slate-855 dark:text-slate-200 uppercase tracking-wider mb-1">City / Town of Residence</label>
                        <input type="text" id="city" maxLength="100" placeholder="e.g. Abuja" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-250 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-[10px] font-bold text-slate-855 dark:text-slate-200 uppercase tracking-wider mb-1">Home Address (House No. / Street)</label>
                        <textarea id="address" required placeholder="House number, street name, block, etc." class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-250 focus:outline-none focus:ring-1 focus:ring-emerald-500" rows="2"></textarea>
                    </div>
                </div>

                <!-- Next of Kin -->
                <div class="mt-2 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <h4 class="text-[11px] font-black text-slate-800 dark:text-white uppercase tracking-wider mb-3 flex items-center gap-1.5">
                        <i data-lucide="users" class="w-3.5 h-3.5 text-emerald-600"></i> Next of Kin
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-855 dark:text-slate-200 uppercase tracking-wider mb-1">Name of Next of Kin</label>
                            <input type="text" id="next_of_kin_name" data-filter="letters" maxLength="120" placeholder="Full name" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-250 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-855 dark:text-slate-200 uppercase tracking-wider mb-1">Relationship with Next of Kin</label>
                            <input type="text" id="next_of_kin_relationship" data-filter="letters" maxLength="60" placeholder="e.g. Spouse, Parent, Sibling" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-250 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-[10px] font-bold text-slate-855 dark:text-slate-200 uppercase tracking-wider mb-1">Address of Next of Kin</label>
                            <textarea id="next_of_kin_address" placeholder="Next of kin residential address" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-250 focus:outline-none focus:ring-1 focus:ring-emerald-500" rows="2"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 4: Allergies & Medical markers (main officer/civilian) -->
            <div id="step-section-4" class="hidden space-y-4">
                <!-- Shown in dependant-of-officer mode where there is no main file -->
                <div id="step4-dep-note" class="hidden p-4 bg-slate-50 dark:bg-slate-950/40 border border-dashed border-slate-200 dark:border-slate-800 rounded-2xl text-center text-xs text-slate-500 dark:text-slate-400">
                    <i data-lucide="info" class="w-5 h-5 mx-auto mb-1 text-emerald-500"></i>
                    Medical markers (blood group, genotype, allergies) are captured <b>per dependant</b> in Step 2. Click Next to review.
                </div>
                <div id="step4-main-markers" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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
            <div id="step-section-5" class="hidden space-y-4 max-h-[62vh] sm:max-h-[420px] overflow-y-auto pr-1">
                <div class="p-3 bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 rounded-xl text-[10px] font-bold flex items-center gap-1.5">
                    <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                    <span>Please review all patient file information before final registry creation.</span>
                </div>

                <!-- Professional patient-file summary (built dynamically) -->
                <div id="preview-content"></div>
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
<div id="details-modal" class="hidden fixed inset-0 z-50 overflow-y-auto p-4 bg-slate-950/60 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 w-full max-w-3xl shadow-2xl relative my-8 mx-auto">
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
                    <button type="button" onclick="switchDetailsTab('documents')" id="tab-btn-documents" class="border-b-2 border-transparent px-4 py-2 text-xs font-bold text-slate-500 hover:text-slate-700 dark:hover:text-slate-350 focus:outline-none transition-all">
                        Documents
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

                <!-- Tab 4: Documents -->
                <div class="hidden" id="det-documents">
                    <form id="doc-upload-form" onsubmit="uploadDocument(event)" class="grid grid-cols-1 sm:grid-cols-2 gap-2 p-3 mb-3 bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 rounded-xl">
                        <input id="doc-title" required placeholder="Document title" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg px-3 py-2 text-xs">
                        <select id="doc-category" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg px-3 py-2 text-xs">
                            <option value="referral">Referral letter</option>
                            <option value="consent">Consent form</option>
                            <option value="id_copy">ID copy</option>
                            <option value="lab_report">External lab/report</option>
                            <option value="insurance">Insurance / NHIS card</option>
                            <option value="other" selected>Other</option>
                        </select>
                        <input id="doc-file" type="file" required accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx" class="text-xs file:mr-2 file:rounded-lg file:border-0 file:bg-emerald-600 file:text-white file:px-3 file:py-1.5 file:text-xs sm:col-span-1">
                        <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-4 py-2 rounded-lg">Upload</button>
                        <p class="text-[9px] text-slate-400 sm:col-span-2">PDF / image / Word, up to 10&nbsp;MB. Files are stored privately and access is audit-logged.</p>
                    </form>
                    <div class="max-h-56 overflow-y-auto space-y-2 pr-1" id="det-documents-list">
                        <p class="text-xs text-slate-400">Loading…</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-6 border-t border-slate-150 dark:border-slate-800/80 mt-6">
            <button onclick="openIdCard()" class="px-5 py-2.5 text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl transition flex items-center gap-2">
                <i data-lucide="id-card" class="w-4 h-4"></i> ID Card
            </button>
            <button onclick="closeDetailsModal()" class="px-5 py-2.5 text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 hover:bg-slate-200 rounded-xl transition">Close File</button>
        </div>
    </div>
</div>

<style>
    #idcard-print .idcard { width: 100%; }
    @media print {
        body * { visibility: hidden !important; }
        #idcard-print, #idcard-print * { visibility: visible !important; }
        #idcard-print { position: fixed; inset: 0; margin: 24px auto; width: 340px; }
        #idcard-print .idcard { box-shadow: none; border: 1px solid #94a3b8; }
        .no-print { display: none !important; }
    }
</style>

<!-- Patient ID Card Modal (printable) -->
<div id="idcard-modal" class="hidden fixed inset-0 z-[60] overflow-y-auto flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 w-full max-w-md shadow-2xl">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2"><i data-lucide="id-card" class="text-emerald-600 w-5 h-5"></i> Patient ID Card</h3>
            <button onclick="closeIdCard()" class="text-slate-400 hover:text-slate-700 dark:hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>

        <!-- The card itself (this is what prints) -->
        <div id="idcard-print">
            <div class="idcard border border-slate-300 rounded-2xl overflow-hidden bg-white text-slate-900">
                <div class="idcard-head flex items-center gap-2 px-4 py-2 bg-emerald-700 text-white">
                    <i data-lucide="shield-plus" class="w-5 h-5 shrink-0"></i>
                    <div class="leading-tight">
                        <div class="text-[11px] font-black uppercase tracking-wide">Nigeria Immigration Service</div>
                        <div class="text-[9px] opacity-90">Medical Services — Patient Identification Card</div>
                    </div>
                </div>
                <div class="flex gap-3 p-4">
                    <div class="shrink-0 text-center">
                        <img id="idc-photo" alt="" class="w-20 h-24 object-cover rounded-lg border border-slate-300 bg-slate-100 hidden">
                        <div id="idc-photo-ph" class="w-20 h-24 rounded-lg border border-slate-300 bg-slate-100 flex items-center justify-center text-slate-400"><i data-lucide="user" class="w-8 h-8"></i></div>
                    </div>
                    <div class="min-w-0 flex-1 text-[11px] leading-snug">
                        <div id="idc-name" class="text-sm font-black text-slate-900 truncate">—</div>
                        <div id="idc-code" class="font-mono text-emerald-700 font-bold text-[11px] mb-1">—</div>
                        <div class="grid grid-cols-2 gap-x-2 gap-y-0.5">
                            <div><span class="text-slate-500">DOB:</span> <b id="idc-dob">—</b></div>
                            <div><span class="text-slate-500">Sex:</span> <b id="idc-gender">—</b></div>
                            <div><span class="text-slate-500">Blood:</span> <b id="idc-blood">—</b></div>
                            <div><span class="text-slate-500">Geno:</span> <b id="idc-geno">—</b></div>
                        </div>
                        <div id="idc-nhis" class="mt-1 inline-block text-[8px] font-black uppercase px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 hidden">NHIS Covered</div>
                    </div>
                    <div class="shrink-0 text-center">
                        <img id="idc-qr" alt="QR" class="w-24 h-24">
                        <div id="idc-barcode" class="font-mono text-[8px] text-slate-500 mt-0.5">—</div>
                    </div>
                </div>
                <div class="px-4 pb-2 flex items-center justify-between text-[8px] text-slate-500 border-t border-slate-200 pt-1">
                    <span>Issued: <b id="idc-issued">—</b></span>
                    <span class="italic">Property of NIS Medical Services. If found, return to nearest facility.</span>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-2 mt-4 no-print">
            <button onclick="closeIdCard()" class="px-4 py-2 text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 rounded-xl">Close</button>
            <button onclick="printIdCard()" class="px-4 py-2 text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl flex items-center gap-2"><i data-lucide="printer" class="w-4 h-4"></i> Print</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // ── Live input filtering (numbers stay numbers, letters stay letters) ──
    // Delegated so it also covers dynamically shown dependant fields.
    document.addEventListener('input', (e) => {
        const el = e.target;
        const kind = el?.dataset?.filter;
        if (!kind) return;
        const before = el.value;
        let after = before;
        if (kind === 'letters') {
            // Letters, spaces, hyphen, apostrophe, period only.
            after = before.replace(/[^A-Za-z\s'.\-]/g, '');
        } else if (kind === 'digits') {
            after = before.replace(/\D/g, '');
        } else if (kind === 'phone') {
            // Digits with a single optional leading +.
            after = before.replace(/[^\d+]/g, '').replace(/(?!^)\+/g, '');
        } else if (kind === 'code') {
            // Service/hospital codes: letters, digits, slash, hyphen; upper-cased.
            after = before.replace(/[^A-Za-z0-9\/\-]/g, '').toUpperCase();
        }
        if (after !== before) {
            const pos = el.selectionStart - (before.length - after.length);
            el.value = after;
            try { el.setSelectionRange(pos, pos); } catch (_) {}
        }
    });

    let searchTimeout = null;

    async function loadPatients(query = '') {
        const tbody = document.getElementById('patients-table-body');

        // Guard: require at least 3 characters before hitting the API
        if (query.trim().length < 3) {
            tbody.innerHTML = `<tr><td colspan="6" class="py-12 text-center">
                <div class="flex flex-col items-center gap-2 text-slate-500">
                    <i data-lucide="search" class="w-8 h-8 text-slate-300"></i>
                    <p class="text-sm font-bold text-slate-700 dark:text-slate-300">Search for a patient to begin</p>
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
                        <td class="py-3.5 px-6 font-mono text-slate-800 dark:text-slate-200">${pat.nin || '·'}</td>
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
    let registrationMode = 'civilian';           // 'civilian' | 'officer'
    // Officer-mode verification state (ID Card Portal)
    let officerVerified = false;
    let officerData = null;                       // fetched officer bio-data
    let officerAlreadyPatientId = null;           // set when officer already has a file
    // Dependants tied to the person being registered
    let sponsorExistingDependants = [];           // dependants already on an existing file
    let sponsorPhone = '';
    let pendingDependants = [];

    function setModeCardHighlight(mode) {
        const cards = { officer: document.getElementById('mode-card-officer'), civilian: document.getElementById('mode-card-civilian') };
        Object.entries(cards).forEach(([key, el]) => {
            if (!el) return;
            if (key === mode) {
                el.classList.add('border-emerald-500', 'ring-1', 'ring-emerald-500', 'bg-emerald-50/40', 'dark:bg-emerald-500/5');
                el.classList.remove('border-slate-200', 'dark:border-slate-800');
            } else {
                el.classList.remove('border-emerald-500', 'ring-1', 'ring-emerald-500', 'bg-emerald-50/40', 'dark:bg-emerald-500/5');
                el.classList.add('border-slate-200', 'dark:border-slate-800');
            }
        });
    }

    function handleModeChange(mode) {
        registrationMode = mode;
        setModeCardHighlight(mode);

        const demographicsContainer = document.getElementById('standalone-demographics-container');
        const depActive = document.getElementById('dependant-active-inputs');
        const sponsorBlock = document.getElementById('sponsor-block');   // legacy Step-2 sponsor search: unused now
        const officerBlock = document.getElementById('officer-lookup-block');
        const introText = document.getElementById('dependant-intro-text');
        const step4Markers = document.getElementById('step4-main-markers');
        const step4Note = document.getElementById('step4-dep-note');

        const demoFields = ['first_name', 'last_name', 'date_of_birth', 'phone', 'gender', 'email', 'middle_name'];

        // Dependant capture and manual demographics are available in BOTH modes.
        if (depActive) depActive.classList.remove('hidden');
        if (demographicsContainer) demographicsContainer.classList.remove('hidden');
        if (sponsorBlock) sponsorBlock.classList.add('hidden');          // never used in the new flow
        if (introText) introText.textContent = 'Optionally add dependants (spouse/children) for this file. They are tied to it automatically. You can skip and click Next.';

        // Reset officer + dependant state whenever the mode changes.
        officerVerified = false;
        officerData = null;
        officerAlreadyPatientId = null;
        sponsorExistingDependants = [];
        pendingDependants = [];
        renderPendingDependantsList();
        const ovs = document.getElementById('officer-verify-status');
        if (ovs) ovs.classList.add('hidden');
        const oar = document.getElementById('officer-already-registered');
        if (oar) oar.classList.add('hidden');

        // Medical markers card is shown for a NEW main file (always in this flow).
        if (step4Markers) step4Markers.classList.remove('hidden');
        if (step4Note) step4Note.classList.add('hidden');

        const svcField = document.getElementById('immigration_service_number');
        const svcGroup = document.getElementById('standalone-service-group');
        if (mode === 'officer') {
            if (officerBlock) officerBlock.classList.remove('hidden');
            // Lock the demographic fields until the officer is verified.
            setDemographicsLocked(true);
            // The service number is authoritative (from the portal) — not typed here.
            if (svcGroup) svcGroup.classList.remove('hidden');
            if (svcField) { svcField.value = ''; svcField.disabled = true; svcField.classList.add('opacity-70'); }
            if (introText) introText.textContent = 'Add the verified officer’s dependants (spouse/children). They are tied to the officer automatically.';
        } else {
            if (officerBlock) officerBlock.classList.add('hidden');
            setDemographicsLocked(false);
            // Civilians have no NIS service number — hide the field entirely.
            if (svcGroup) svcGroup.classList.add('hidden');
            if (svcField) { svcField.value = ''; svcField.disabled = false; svcField.classList.remove('opacity-70'); }
        }

        // Required attributes for the manual demographics (both modes need them,
        // but in officer mode they are filled by the portal fetch).
        const req = ['first_name', 'last_name', 'date_of_birth', 'phone', 'marital_status'];
        req.forEach(id => { const el = document.getElementById(id); if (el) el.setAttribute('required', 'required'); });
        void demoFields;
    }

    // Enable/disable the officer demographic inputs until verification succeeds.
    function setDemographicsLocked(locked) {
        const ids = ['first_name', 'middle_name', 'last_name', 'gender', 'date_of_birth',
                     'phone', 'email', 'nin', 'marital_status', 'religion', 'occupation',
                     'place_of_birth', 'tribe', 'passport_photo'];
        ids.forEach(id => {
            const el = document.getElementById(id);
            if (!el) return;
            el.disabled = locked;
            el.classList.toggle('opacity-50', locked);
            el.classList.toggle('cursor-not-allowed', locked);
        });
    }

    // Verify an officer's Service Number against the ID Card Portal and
    // auto-populate the form with their bio-data.
    async function handleVerifyOfficer() {
        const input = document.getElementById('officer_service_number');
        if (!input) return;
        const serviceNum = input.value.trim();
        if (!/^\d{2,5}$/.test(serviceNum)) {
            alert('Please enter a valid NIS Service Number (2–5 digits).');
            return;
        }
        const btn = document.getElementById('verify-officer-btn');
        if (btn) { btn.disabled = true; btn.classList.add('opacity-60'); }
        try {
            const res = await api.get(`/officers/lookup?service_number=${encodeURIComponent(serviceNum)}`);
            if (!res.found) {
                officerVerified = false;
                officerData = null;
                officerAlreadyPatientId = null;
                document.getElementById('officer-verify-status').classList.add('hidden');
                document.getElementById('officer-already-registered').classList.add('hidden');
                setDemographicsLocked(true);
                alert(res.message || 'Officer not found in the ID Card Portal.');
                return;
            }

            officerVerified = true;
            officerData = res.officer || {};
            officerAlreadyPatientId = res.already_registered ? res.patient_id : null;
            sponsorExistingDependants = res.existing_dependants || [];
            sponsorPhone = officerData.phone || '';

            // Unlock and auto-populate the demographic fields.
            setDemographicsLocked(false);
            applyOfficerData(officerData, serviceNum);
            document.getElementById('immigration_service_number').disabled = true; // service number stays authoritative

            // Verification card
            const fullName = [officerData.first_name, officerData.middle_name, officerData.last_name].filter(Boolean).join(' ');
            document.getElementById('officer-fullname-label').innerText = fullName || 'Officer verified';
            const meta = [officerData.rank, officerData.command, 'Service No: ' + serviceNum].filter(Boolean).join(' · ');
            document.getElementById('officer-meta-label').innerText = meta;
            document.getElementById('officer-verify-status').classList.remove('hidden');

            // Already-registered handling: switch to dependants-only.
            const oar = document.getElementById('officer-already-registered');
            const step4Markers = document.getElementById('step4-main-markers');
            const step4Note = document.getElementById('step4-dep-note');
            if (officerAlreadyPatientId) {
                oar.classList.remove('hidden');
                document.getElementById('officer-already-registered-text').innerText =
                    `This officer already has a patient file (${res.hospital_number || 'existing file'}). Add dependants to the existing file below, then click through to save.`;
                if (step4Markers) step4Markers.classList.add('hidden');
                if (step4Note) step4Note.classList.remove('hidden');
            } else {
                oar.classList.add('hidden');
                if (step4Markers) step4Markers.classList.remove('hidden');
                if (step4Note) step4Note.classList.add('hidden');
            }

            const srcNote = res.source === 'portal' ? '' : ' (from local directory)';
            alert('Officer verified successfully' + srcNote + '. Details auto-filled — review and complete the form.');
            lucide.createIcons();
        } catch (err) {
            officerVerified = false;
            setDemographicsLocked(true);
            alert('Officer verification failed: ' + (err.message || 'connection error'));
        } finally {
            if (btn) { btn.disabled = false; btn.classList.remove('opacity-60'); }
        }
    }

    // Copy fetched officer bio-data into the registration form.
    function applyOfficerData(o, serviceNum) {
        const set = (id, v) => { const el = document.getElementById(id); if (el && v != null && v !== '') el.value = v; };
        set('first_name', o.first_name);
        set('middle_name', o.middle_name);
        set('last_name', o.last_name);
        set('date_of_birth', o.date_of_birth);
        set('phone', o.phone);
        set('email', o.email);
        set('nin', o.nin);
        set('immigration_service_number', serviceNum);
        if (o.gender) { const g = document.getElementById('gender'); if (g) g.value = o.gender; }
        if (o.marital_status) { const m = document.getElementById('marital_status'); if (m) m.value = o.marital_status; }
        updateAgeDisplay();
        // Address (Step 3) — auto-fill from the officer's file, still editable.
        const stateSel = document.getElementById('state');
        if (o.state && stateSel) { stateSel.value = o.state; handleStateChange(o.state); const l = document.getElementById('lga'); if (o.lga && l) l.value = o.lga; }
        if (o.city) set('city', o.city);
        if (o.address) set('address', o.address);
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

    // Live age readout next to the Date of Birth field.
    function updateAgeDisplay() {
        const dob = document.getElementById('date_of_birth')?.value;
        const badge = document.getElementById('age-badge');
        if (!badge) return;
        if (dob) {
            const age = calculateAge(dob);
            badge.textContent = `(Age: ${age} yr${age === 1 ? '' : 's'})`;
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    }

    function validateDependantAge() {
        // Dependants have no age limit — nothing to enforce.
        const ageWarning = document.getElementById('dependant-age-warning');
        if (ageWarning) ageWarning.classList.add('hidden');
        return true;
    }

    function updateRelationshipOptions() {
        const select = document.getElementById('relationship_to_sponsor');
        if (!select) return;

        const existingWifeCount = sponsorExistingDependants.filter(d => d.relationship_to_sponsor === 'Wife').length;
        const pendingWifeCount = pendingDependants.filter(d => d.relationship_to_sponsor === 'Wife').length;
        const totalWife = existingWifeCount + pendingWifeCount;

        const currentVal = select.value;
        select.innerHTML = '';

        const options = [
            { value: 'Son', text: 'Son' },
            { value: 'Daughter', text: 'Daughter' },
            { value: 'Ward', text: 'Ward' }
        ];

        // Only allow "Wife" option if none exists yet
        if (totalWife === 0) {
            options.push({ value: 'Wife', text: 'Wife' });
        }

        options.forEach(opt => {
            const o = document.createElement('option');
            o.value = opt.value;
            o.textContent = opt.text;
            select.appendChild(o);
        });

        if (options.some(opt => opt.value === currentVal)) {
            select.value = currentVal;
        } else {
            select.value = options[0].value;
        }
    }

    function handleAddDependantClick() {
        const depFirst = document.getElementById('dep_first_name').value.trim();
        const depMiddle = document.getElementById('dep_middle_name').value.trim();
        const depDob = document.getElementById('dep_date_of_birth').value;
        const depGender = document.getElementById('dep_gender').value;
        const rel = document.getElementById('relationship_to_sponsor').value;
        const depBlood = document.getElementById('dep_blood_group').value;
        const depGenotype = document.getElementById('dep_genotype').value;
        const depAllergies = document.getElementById('dep_allergies').value.trim();
        const depDisability = document.getElementById('dep_disability').value.trim();
        const depNin = document.getElementById('dep_nin').value.trim();
        const depPhotoInput = document.getElementById('dep_passport_photo');
        const depPhotoFile = (depPhotoInput && depPhotoInput.files && depPhotoInput.files[0]) ? depPhotoInput.files[0] : null;

        // Dependants are tied to the officer/civilian being registered on this
        // form. In officer mode the person is verified; in both modes the surname
        // comes from the main file's Last Name (auto-filled for officers).
        if (registrationMode === 'officer' && !officerVerified) {
            alert('Please verify the officer’s Service Number in Step 1 first.');
            return;
        }
        let depLastName = document.getElementById('last_name').value.trim();
        if (!depLastName) {
            alert('Please fill (or verify) the patient’s Last Name in Step 1 first.');
            return;
        }
        // Resolved to the file's service number at submit time.
        let sponsor = (registrationMode === 'officer' && officerData) ? (officerData.service_number || '') : '';

        if (!depFirst || !depDob) {
            alert('Please fill out First Name and Date of Birth.');
            return;
        }

        // NIN is optional, but must be exactly 11 digits when provided.
        if (depNin && !/^\d{11}$/.test(depNin)) {
            alert('Dependant NIN must be exactly 11 digits (or left blank).');
            return;
        }

        const existingCount = sponsorExistingDependants.length;
        const pendingCount = pendingDependants.length;
        if (existingCount + pendingCount >= 4) {
            alert('A maximum of 4 dependants is allowed per sponsor.');
            return;
        }

        const existingWifeCount = sponsorExistingDependants.filter(d => d.relationship_to_sponsor === 'Wife').length;
        const pendingWifeCount = pendingDependants.filter(d => d.relationship_to_sponsor === 'Wife').length;
        if (rel === 'Wife' && (existingWifeCount + pendingWifeCount >= 1)) {
            alert('Only one Wife is allowed as a dependant.');
            return;
        }

        const existingChildCount = sponsorExistingDependants.filter(d => d.relationship_to_sponsor !== 'Wife').length;
        const pendingChildCount = pendingDependants.filter(d => d.relationship_to_sponsor !== 'Wife').length;
        if (rel !== 'Wife' && (existingChildCount + pendingChildCount >= 3)) {
            alert('A maximum of 3 children (Son/Daughter/Ward) is allowed.');
            return;
        }

        pendingDependants.push({
            first_name: depFirst,
            middle_name: depMiddle || null,
            last_name: depLastName,
            gender: depGender,
            date_of_birth: depDob,
            relationship_to_sponsor: rel,
            sponsor_service_number: sponsor,
            blood_group: depBlood,
            genotype: depGenotype,
            allergies: depAllergies || null,
            disability: depDisability || 'None',
            nin: depNin || null,
            photoFile: depPhotoFile
        });

        renderPendingDependantsList();
        updateRelationshipOptions();

        // Clear input fields
        document.getElementById('dep_first_name').value = '';
        document.getElementById('dep_middle_name').value = '';
        document.getElementById('dep_date_of_birth').value = '';
        document.getElementById('dep_gender').value = 'Male';
        document.getElementById('dep_allergies').value = '';
        document.getElementById('dep_disability').value = '';
        document.getElementById('dep_nin').value = '';
        if (depPhotoInput) depPhotoInput.value = '';
    }

    function renderPendingDependantsList() {
        const container = document.getElementById('pending-deps-container');
        const listDiv = document.getElementById('pending-deps-list');
        if (!container || !listDiv) return;

        if (pendingDependants.length === 0) {
            container.classList.add('hidden');
            return;
        }

        container.classList.remove('hidden');
        listDiv.innerHTML = pendingDependants.map((dep, idx) => `
            <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs">
                <div>
                    <span class="px-2 py-0.5 rounded font-black text-[8px] bg-blue-100 text-blue-800 dark:bg-blue-500/10 dark:text-blue-400 uppercase mr-2">
                        ${dep.relationship_to_sponsor}
                    </span>
                    <b class="text-slate-805 dark:text-white">${dep.first_name} ${dep.middle_name ? dep.middle_name + ' ' : ''}${dep.last_name}</b>
                    <span class="text-slate-500 text-[10px] ml-2">(${dep.gender} · DOB: ${dep.date_of_birth})</span>
                    <span class="text-slate-400 text-[10px] block mt-0.5">Blood: ${dep.blood_group || '—'} · Genotype: ${dep.genotype || '—'}${dep.nin ? ' · NIN: ' + dep.nin : ''}${dep.allergies ? ' · Allergies: ' + dep.allergies : ''}${dep.disability && dep.disability !== 'None' ? ' · Disability: ' + dep.disability : ''}</span>
                </div>
                <button type="button" onclick="removePendingDependant(${idx})" class="text-red-500 hover:text-red-700 font-bold flex items-center gap-0.5 cursor-pointer">
                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Remove
                </button>
            </div>
        `).join('');
        lucide.createIcons();
    }

    function removePendingDependant(idx) {
        pendingDependants.splice(idx, 1);
        renderPendingDependantsList();
        updateRelationshipOptions();
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

        // Hide officer verification + age status by default
        const ovs = document.getElementById('officer-verify-status');
        if (ovs) ovs.classList.add('hidden');
        const oar = document.getElementById('officer-already-registered');
        if (oar) oar.classList.add('hidden');
        const officerNumInput = document.getElementById('officer_service_number');
        if (officerNumInput) officerNumInput.value = '';
        const ageWarning = document.getElementById('dependant-age-warning');
        if (ageWarning) ageWarning.classList.add('hidden');

        // Reset memory lists + officer state
        pendingDependants = [];
        sponsorExistingDependants = [];
        sponsorPhone = '';
        officerVerified = false;
        officerData = null;
        officerAlreadyPatientId = null;
        renderPendingDependantsList();

        // Reset passport photo preview
        const pv = document.getElementById('photo-preview');
        if (pv) { pv.classList.add('hidden'); pv.removeAttribute('src'); }
        const pph = document.getElementById('photo-placeholder');
        if (pph) pph.classList.remove('hidden');

        // Reset NHIS to "No"
        const nhisNo = document.querySelector('input[name="nhis_status"][value="no"]');
        if (nhisNo) nhisNo.checked = true;
        handleNhisChange('no');

        // Default to Civilian mode
        const modeCivilianRadio = document.getElementById('mode-civilian');
        if (modeCivilianRadio) modeCivilianRadio.checked = true;
        handleModeChange('civilian');

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
            // NIS Officer must be verified against the ID Card Portal first.
            if (registrationMode === 'officer' && !officerVerified) {
                alert('Please verify the officer’s Service Number before continuing.');
                return;
            }
            const gender = document.getElementById('gender').value;
            const phone = document.getElementById('phone').value.trim();
            const first = document.getElementById('first_name').value.trim();
            const last = document.getElementById('last_name').value.trim();
            const dob = document.getElementById('date_of_birth').value;
            const marital = document.getElementById('marital_status').value;
            if (!first || !last || !gender || !dob || !phone) {
                alert('Please fill out First Name, Last Name, Gender, DOB and Phone Number.');
                return;
            }
            if (!marital) {
                alert('Please select a Marital Status.');
                return;
            }
        }

        if (currentRegisterStep === 2) {
            const depFirst = document.getElementById('dep_first_name').value.trim();
            const depDob = document.getElementById('dep_date_of_birth').value;

            // Dependants are OPTIONAL. If details were typed but not added,
            // capture them so they aren't lost; otherwise just continue.
            if (pendingDependants.length === 0 && (depFirst || depDob)) {
                handleAddDependantClick();
                if (pendingDependants.length === 0) return; // add failed (age policy, limits)
            }

            // For an already-registered officer we are adding dependants only, so
            // at least one dependant is required.
            if (registrationMode === 'officer' && officerAlreadyPatientId && pendingDependants.length === 0) {
                alert('This officer already has a file. Add at least one dependant to continue.');
                return;
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
            renderPreview();
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

    // Build the detailed, professional patient-file summary shown on Step 5.
    function renderPreview() {
        const g = (id) => { const el = document.getElementById(id); return el ? String(el.value).trim() : ''; };
        const esc = (s) => String(s == null ? '' : s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
        const dash = '<span class="text-slate-400 dark:text-slate-600">—</span>';
        const show = (v) => (v && String(v).trim() !== '') ? esc(v) : dash;

        const first = g('first_name'), middle = g('middle_name'), last = g('last_name');
        const fullName = [first, middle, last].filter(Boolean).join(' ') || 'Unnamed patient';
        const gender = g('gender'), dob = g('date_of_birth');
        const ageStr = dob ? calculateAge(dob) + ' yrs' : '';
        const phone = g('phone'), email = g('email');
        const serviceNo = g('immigration_service_number'), nin = g('nin');
        const marital = g('marital_status'), religion = g('religion'), occupation = g('occupation');
        const pob = g('place_of_birth'), tribe = g('tribe');
        const state = g('state'), lga = g('lga'), city = g('city'), address = g('address');
        const nokName = g('next_of_kin_name'), nokRel = g('next_of_kin_relationship'), nokAddr = g('next_of_kin_address');
        const blood = g('blood_group'), genotype = g('genotype'), allergies = g('allergies'), disability = g('disability');

        const isOfficer = registrationMode === 'officer';
        const dependantsOnly = isOfficer && officerAlreadyPatientId;
        const nhisYes = document.querySelector('input[name="nhis_status"]:checked')?.value === 'yes';
        const nhisNum = g('nhis_number');

        const rankLine = isOfficer && officerData
            ? [officerData.rank, officerData.command].filter(Boolean).map(esc).join(' · ')
            : '';

        // photo thumbnail from the live preview (data URI), if one was chosen
        const pv = document.getElementById('photo-preview');
        const photoSrc = (pv && !pv.classList.contains('hidden') && pv.getAttribute('src')) ? pv.getAttribute('src') : '';
        const photoHtml = photoSrc
            ? `<img src="${photoSrc}" alt="" class="w-16 h-20 object-cover rounded-lg border border-slate-200 dark:border-slate-700">`
            : `<div class="w-16 h-20 rounded-lg border border-dashed border-slate-300 dark:border-slate-700 bg-white/60 dark:bg-slate-900 flex items-center justify-center text-slate-400"><i data-lucide="user" class="w-7 h-7"></i></div>`;

        const typeBadge = isOfficer
            ? `<span class="inline-flex items-center gap-1 text-[9px] font-black uppercase tracking-wider bg-emerald-600 text-white px-2 py-0.5 rounded-full"><i data-lucide="shield-check" class="w-3 h-3"></i> NIS Officer</span>`
            : `<span class="inline-flex items-center gap-1 text-[9px] font-black uppercase tracking-wider bg-slate-700 text-white px-2 py-0.5 rounded-full"><i data-lucide="user" class="w-3 h-3"></i> Civilian</span>`;
        const nhisBadge = nhisYes
            ? `<span class="text-[9px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400 px-2 py-0.5 rounded-full">NHIS covered</span>`
            : `<span class="text-[9px] font-black uppercase tracking-wider bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400 px-2 py-0.5 rounded-full">Self-pay (cash)</span>`;
        const existingBadge = dependantsOnly
            ? `<span class="text-[9px] font-black uppercase tracking-wider bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-400 px-2 py-0.5 rounded-full">Existing file</span>`
            : '';

        // a labelled field cell
        const field = (label, valueHtml, wide) => `
            <div class="${wide ? 'sm:col-span-2' : ''}">
                <div class="text-[8.5px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">${label}</div>
                <div class="text-[11px] font-semibold text-slate-800 dark:text-slate-100 mt-0.5 break-words">${valueHtml}</div>
            </div>`;
        // a section card
        const section = (icon, title, bodyHtml) => `
            <div class="border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden bg-white dark:bg-slate-950/40">
                <div class="flex items-center gap-1.5 px-4 py-2 bg-slate-50 dark:bg-slate-900 border-b border-slate-100 dark:border-slate-800">
                    <i data-lucide="${icon}" class="w-3.5 h-3.5 text-emerald-600"></i>
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-700 dark:text-slate-200">${title}</span>
                </div>
                <div class="p-4">${bodyHtml}</div>
            </div>`;
        const grid = (cells) => `<div class="grid grid-cols-2 gap-x-4 gap-y-3">${cells.join('')}</div>`;

        // --- Bio-data ---
        const bio = grid([
            field('Gender', show(gender)),
            field('Date of Birth', dob ? `${esc(dob)} <span class="text-slate-400">(${ageStr})</span>` : dash),
            field('Marital Status', show(marital)),
            field('Religion', show(religion)),
            field('Occupation', show(occupation)),
            field('Place of Birth', show(pob)),
            field('Tribe / Ethnicity', show(tribe), true),
        ]);

        // --- Contact ---
        const contact = grid([
            field('Phone Number', show(phone)),
            field('Email Address', show(email)),
        ]);

        // --- Identification & coverage ---
        const ident = grid([
            field('Registration Type', isOfficer ? 'NIS Officer' : 'Civilian'),
            field('Service Number', isOfficer ? `<span class="font-mono">${show(serviceNo)}</span>` : dash),
            field('NIN', `<span class="font-mono">${show(nin)}</span>`),
            field('NHIS Status', nhisYes ? `Covered · <span class="font-mono">${show(nhisNum)}</span>` : 'Not covered (cash)'),
        ]);

        // --- Origin & address ---
        const addr = grid([
            field('State of Origin', show(state)),
            field('LGA of Origin', show(lga)),
            field('City / Town', show(city)),
            field('Home Address', show(address), true),
        ]);

        // --- Next of kin ---
        const nok = grid([
            field('Name', show(nokName)),
            field('Relationship', show(nokRel)),
            field('Address', show(nokAddr), true),
        ]);

        // --- Medical markers ---
        const medical = dependantsOnly
            ? `<p class="text-[11px] text-slate-500 dark:text-slate-400">Existing officer file — medical markers are recorded per dependant below.</p>`
            : grid([
                field('Blood Group', show(blood)),
                field('Genotype', show(genotype)),
                field('Allergies', show(allergies)),
                field('Disability', show(disability)),
            ]);

        // --- Dependants ---
        let deps;
        if (pendingDependants.length === 0) {
            deps = `<p class="text-[11px] text-slate-500 dark:text-slate-400">No dependants added.</p>`;
        } else {
            const rows = pendingDependants.map(d => `
                <tr class="border-t border-slate-100 dark:border-slate-800">
                    <td class="py-2 px-3 font-semibold text-slate-800 dark:text-slate-100">${esc([d.first_name, d.middle_name, d.last_name].filter(Boolean).join(' '))}</td>
                    <td class="py-2 px-3"><span class="text-[9px] font-black uppercase bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-400 px-1.5 py-0.5 rounded">${esc(d.relationship_to_sponsor)}</span></td>
                    <td class="py-2 px-3">${esc(d.gender)}</td>
                    <td class="py-2 px-3 whitespace-nowrap">${esc(d.date_of_birth)} <span class="text-slate-400">(${calculateAge(d.date_of_birth)}y)</span></td>
                    <td class="py-2 px-3 font-mono">${esc(d.nin || '—')}</td>
                    <td class="py-2 px-3 font-mono">${esc(d.blood_group || '—')}/${esc(d.genotype || '—')}</td>
                    <td class="py-2 px-3">${esc(d.allergies || '—')}</td>
                    <td class="py-2 px-3">${esc(d.disability || 'None')}</td>
                </tr>`).join('');
            deps = `
                <div class="overflow-x-auto -m-4">
                    <table class="w-full text-[10.5px] text-left text-slate-700 dark:text-slate-300">
                        <thead class="bg-slate-50 dark:bg-slate-900 text-[8.5px] font-black uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="py-2 px-3">Name</th><th class="py-2 px-3">Relationship</th>
                                <th class="py-2 px-3">Gender</th><th class="py-2 px-3">DOB</th>
                                <th class="py-2 px-3">NIN</th>
                                <th class="py-2 px-3">Blood/Genotype</th><th class="py-2 px-3">Allergies</th>
                                <th class="py-2 px-3">Disability</th>
                            </tr>
                        </thead>
                        <tbody>${rows}</tbody>
                    </table>
                </div>`;
        }
        const depCount = pendingDependants.length;
        const depTitle = `Dependants${depCount ? ` (${depCount}${dependantsOnly ? ' to add' : ''})` : ''}`;

        const html = `
            <!-- Header -->
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-gradient-to-r from-emerald-50 to-white dark:from-emerald-500/5 dark:to-slate-950/40 p-4 flex items-center gap-4 mb-4">
                ${photoHtml}
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h3 class="text-base font-black text-slate-900 dark:text-white truncate">${esc(fullName)}</h3>
                        ${typeBadge}${existingBadge}
                    </div>
                    ${rankLine ? `<p class="text-[10px] text-slate-600 dark:text-slate-400 mt-0.5">${rankLine}</p>` : ''}
                    <div class="flex flex-wrap items-center gap-2 mt-1.5">
                        ${isOfficer ? `<span class="text-[10px] font-mono text-slate-600 dark:text-slate-300">Service&nbsp;No:&nbsp;${show(serviceNo)}</span>` : ''}
                        <span class="text-[10px] text-slate-500 dark:text-slate-400">${esc(gender || '')}${dob ? ' · ' + ageStr : ''}</span>
                        ${nhisBadge}
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                ${section('badge-check', 'Identification & Coverage', ident)}
                ${section('user', 'Bio-Data', bio)}
                ${section('phone', 'Contact', contact)}
                ${section('map-pin', 'Origin & Address', addr)}
                ${section('users', 'Next of Kin', nok)}
                ${section('activity', 'Medical Markers', medical)}
            </div>

            <div class="mt-3">
                ${section('users', depTitle, deps)}
            </div>`;

        const target = document.getElementById('preview-content');
        if (target) target.innerHTML = html;
        if (window.lucide) lucide.createIcons();
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

    // Live preview of the selected passport photo (FileReader -> data URI is the
    // most reliable across browsers).
    function previewPhoto(input) {
        const img = document.getElementById('photo-preview');
        const ph = document.getElementById('photo-placeholder');
        const file = input.files && input.files[0];
        if (!file) {
            img.classList.add('hidden');
            img.removeAttribute('src');
            if (ph) ph.classList.remove('hidden');
            return;
        }
        const reader = new FileReader();
        reader.onload = (e) => {
            img.src = e.target.result;
            img.classList.remove('hidden');
            if (ph) ph.classList.add('hidden');
        };
        reader.onerror = () => {
            img.classList.add('hidden');
            if (ph) ph.classList.remove('hidden');
            alert('Could not read that image. Please choose a valid JPG, PNG or WEBP file.');
        };
        reader.readAsDataURL(file);
    }

    // Toggle the NHIS number field + cost note based on the NHIS answer.
    function handleNhisChange(value) {
        const grp = document.getElementById('nhis-number-group');
        const numInput = document.getElementById('nhis_number');
        const note = document.getElementById('nhis-cost-note');
        if (value === 'yes') {
            if (grp) grp.classList.remove('hidden');
            if (numInput) numInput.setAttribute('required', 'required');
            if (note) { note.textContent = 'NHIS covered: registration fee waived and service charges discounted.'; note.className = 'text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold mt-2'; }
        } else {
            if (grp) grp.classList.add('hidden');
            if (numInput) { numInput.removeAttribute('required'); numInput.value = ''; }
            if (note) { note.textContent = 'Non-NHIS: a registration fee and full service charges apply.'; note.className = 'text-[10px] text-amber-600 dark:text-amber-400 font-semibold mt-2'; }
        }
    }

    // Upload a passport photo for a freshly created patient (best-effort).
    async function uploadPatientPhoto(patientId, file) {
        if (!file) return;
        const fd = new FormData();
        fd.append('photo', file);
        try { await api.post(`/patients/${patientId}/photo`, fd); }
        catch (e) { console.warn('Passport photo upload failed:', e); }
    }

    async function handleRegisterSubmit(e) {
        e.preventDefault();

        // Dependants-only path: an NIS officer who already has a patient file —
        // we just attach dependants to it, we do not recreate the officer.
        const isDependantsOnly = (registrationMode === 'officer' && officerAlreadyPatientId);
        const val = (id) => document.getElementById(id) ? document.getElementById(id).value : '';

        // Address + next-of-kin context shared by the main file and its dependants.
        const addressCtx = {
            state: val('state') || null,
            lga: val('lga') || null,
            city: val('city') || null,
            address: document.getElementById('address').value,
            next_of_kin_name: val('next_of_kin_name') || null,
            next_of_kin_relationship: val('next_of_kin_relationship') || null,
            next_of_kin_address: val('next_of_kin_address') || null,
        };

        // Build a dependant payload; each dependant carries its OWN medical markers
        // and inherits the sponsor's NHIS coverage.
        const depPayload = (dep, sponsorNumber, contactPhone, isNhisFlag) => ({
            ...addressCtx,
            first_name: dep.first_name,
            middle_name: dep.middle_name,
            last_name: dep.last_name,
            gender: dep.gender,
            marital_status: dep.relationship_to_sponsor === 'Wife' ? 'Married' : 'Single',
            date_of_birth: dep.date_of_birth,
            phone: (contactPhone && /^\+?\d{7,15}$/.test(contactPhone)) ? contactPhone : '00000000000',
            email: null,
            immigration_service_number: 'NIS/DEP/' + Math.floor(10000 + Math.random() * 90000),
            sponsor_service_number: sponsorNumber,
            relationship_to_sponsor: dep.relationship_to_sponsor,
            nin: dep.nin || null,
            is_nhis: !!isNhisFlag,
            blood_group: dep.blood_group,
            genotype: dep.genotype,
            allergies: dep.allergies || null,
            disability: dep.disability || 'None',
        });

        // NHIS answer for the main officer/civilian.
        const mainIsNhis = document.querySelector('input[name="nhis_status"]:checked')?.value === 'yes';
        const mainNhisNumber = mainIsNhis ? (document.getElementById('nhis_number').value.trim() || null) : null;

        try {
            if (!isDependantsOnly) {
                // 1. Register the main officer/civilian.
                const serviceNo = document.getElementById('immigration_service_number').value.trim();
                const mainPayload = {
                    ...addressCtx,
                    first_name: document.getElementById('first_name').value,
                    middle_name: document.getElementById('middle_name').value || null,
                    last_name: document.getElementById('last_name').value,
                    gender: document.getElementById('gender').value,
                    marital_status: val('marital_status') || null,
                    occupation: val('occupation') || null,
                    religion: val('religion') || null,
                    place_of_birth: val('place_of_birth') || null,
                    tribe: val('tribe') || null,
                    date_of_birth: document.getElementById('date_of_birth').value,
                    phone: document.getElementById('phone').value,
                    email: document.getElementById('email').value || null,
                    immigration_service_number: serviceNo || null,
                    nin: document.getElementById('nin').value || null,
                    is_nhis: mainIsNhis,
                    nhis_number: mainNhisNumber,
                    blood_group: document.getElementById('blood_group').value,
                    genotype: document.getElementById('genotype').value,
                    allergies: document.getElementById('allergies').value || null,
                    disability: document.getElementById('disability').value || 'None',
                };
                const res = await api.post('/patients', mainPayload);
                const main = res.patient;
                const created = [main];

                // Upload the main patient's passport photo (if any).
                await uploadPatientPhoto(main.id, document.getElementById('passport_photo').files[0]);

                // 2. Register dependants tied to the officer/civilian just created.
                //    Dependants inherit the main person's NHIS coverage.
                for (const dep of pendingDependants) {
                    const depRes = await api.post('/patients', depPayload(dep, main.immigration_service_number, main.phone, mainIsNhis));
                    await uploadPatientPhoto(depRes.patient.id, dep.photoFile);
                    created.push(depRes.patient);
                }

                document.getElementById('success-patient-name').innerText = created.map(p => p.full_name).join(', ');
                document.getElementById('success-patient-code').innerText = created.map(p => p.immigration_service_number).join(', ');
            } else {
                // Dependants for an EXISTING NIS officer (officer file already exists).
                if (pendingDependants.length === 0) {
                    const depFirst = document.getElementById('dep_first_name').value.trim();
                    const depDob = document.getElementById('dep_date_of_birth').value;
                    if (depFirst || depDob) { handleAddDependantClick(); if (pendingDependants.length === 0) return; }
                    else { alert('Please add at least one Dependant.'); return; }
                }
                const officerService = officerData ? officerData.service_number : '';
                const registeredPats = [];
                for (const dep of pendingDependants) {
                    // Dependants of an NIS officer are NHIS-covered.
                    const depRes = await api.post('/patients', depPayload(dep, dep.sponsor_service_number || officerService, sponsorPhone, true));
                    await uploadPatientPhoto(depRes.patient.id, dep.photoFile);
                    registeredPats.push(depRes.patient);
                }
                document.getElementById('success-patient-name').innerText = registeredPats.map(p => p.full_name).join(', ');
                document.getElementById('success-patient-code').innerText = registeredPats.map(p => p.immigration_service_number).join(', ');
            }

            closeRegisterModal();
            document.getElementById('success-modal').classList.remove('hidden');
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
        const tabBtnDocuments = document.getElementById('tab-btn-documents');
        const detTimeline = document.getElementById('det-timeline');
        const detMedications = document.getElementById('det-medications');
        const detDiagnostics = document.getElementById('det-diagnostics');
        const detDocuments = document.getElementById('det-documents');

        if (!tabBtnTimeline || !tabBtnMedications || !detTimeline || !detMedications) return;

        // Reset all buttons and panels
        const activeBtnClass = 'border-b-2 border-emerald-600 px-4 py-2 text-xs font-bold text-emerald-650 focus:outline-none transition-all';
        const inactiveBtnClass = 'border-b-2 border-transparent px-4 py-2 text-xs font-bold text-slate-500 hover:text-slate-700 dark:hover:text-slate-350 focus:outline-none transition-all';

        tabBtnTimeline.className = inactiveBtnClass;
        tabBtnMedications.className = inactiveBtnClass;
        if (tabBtnDiagnostics) tabBtnDiagnostics.className = inactiveBtnClass;
        if (tabBtnDocuments) tabBtnDocuments.className = inactiveBtnClass;

        detTimeline.classList.add('hidden');
        detMedications.classList.add('hidden');
        if (detDiagnostics) detDiagnostics.classList.add('hidden');
        if (detDocuments) detDocuments.classList.add('hidden');

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
        } else if (tabName === 'documents') {
            if (tabBtnDocuments) tabBtnDocuments.className = activeBtnClass;
            if (detDocuments) detDocuments.classList.remove('hidden');
            loadDocuments();
        }
    }

    async function handleShowDetails(id) {
        try {
            const res = await api.get(`/patients/${id}`);
            const pat = res.patient;
            const timeline = res.timeline;

            // Remember which file is open (used by ID card + documents).
            window.__currentPatient = pat;
            window.__currentPatientId = id;

            // Reset tabs to timeline default view
            switchDetailsTab('timeline');

            // Inject demographic details
            document.getElementById('det-full-name').innerText = pat.full_name;
            document.getElementById('det-service-code').innerText = pat.immigration_service_number;
            document.getElementById('det-dob').innerText = pat.date_of_birth;
            document.getElementById('det-gender').innerText = pat.gender;
            document.getElementById('det-phone').innerText = pat.phone;
            document.getElementById('det-nin').innerText = pat.nin || '·';
            document.getElementById('det-blood').innerText = pat.blood_group || '·';
            document.getElementById('det-genotype').innerText = pat.genotype || '·';
            document.getElementById('det-state').innerText = pat.state || '·';
            document.getElementById('det-lga').innerText = pat.lga || '·';
            document.getElementById('det-address').innerText = pat.address || '·';
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
                    } else if (event.type === 'payment') {
                        icon = 'banknote';
                        color = 'text-emerald-600 bg-emerald-500/10 border border-emerald-500/20';
                    } else if (event.type === 'billing_charge') {
                        icon = 'receipt';
                        color = 'text-amber-600 bg-amber-500/10 border border-amber-500/20';
                    } else if (event.type === 'lab_result') {
                        icon = 'flask-conical';
                        color = 'text-indigo-600 bg-indigo-500/10 border border-indigo-500/20';
                    } else if (event.type === 'radiology_result') {
                        icon = 'scan';
                        color = 'text-purple-600 bg-purple-500/10 border border-purple-500/20';
                    } else if (event.type === 'admission') {
                        icon = 'bed';
                        color = 'text-rose-600 bg-rose-500/10 border border-rose-500/20';
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

    // ─── Patient ID Card ───────────────────────────────────────────────
    async function openIdCard() {
        const id = window.__currentPatientId;
        if (!id) return;
        try {
            const res = await api.get(`/patients/${id}/id-card`);
            const c = res.card;
            document.getElementById('idc-name').innerText = c.full_name;
            document.getElementById('idc-code').innerText = c.hospital_code || '—';
            document.getElementById('idc-dob').innerText = c.date_of_birth || '—';
            document.getElementById('idc-gender').innerText = c.gender || '—';
            document.getElementById('idc-blood').innerText = c.blood_group || '—';
            document.getElementById('idc-geno').innerText = c.genotype || '—';
            document.getElementById('idc-issued').innerText = c.issued_on;
            document.getElementById('idc-barcode').innerText = c.barcode || '';
            document.getElementById('idc-qr').src = res.qr;

            const nhis = document.getElementById('idc-nhis');
            nhis.classList.toggle('hidden', !c.nhis);

            const photo = document.getElementById('idc-photo');
            const ph = document.getElementById('idc-photo-ph');
            if (c.photo_url) {
                photo.src = c.photo_url; photo.classList.remove('hidden'); ph.classList.add('hidden');
            } else {
                photo.classList.add('hidden'); ph.classList.remove('hidden');
            }

            document.getElementById('idcard-modal').classList.remove('hidden');
            lucide.createIcons();
        } catch (e) { alert(e.message || 'Failed to build ID card.'); }
    }
    function closeIdCard() { document.getElementById('idcard-modal').classList.add('hidden'); }
    function printIdCard() { window.print(); }

    // ─── Patient Documents ─────────────────────────────────────────────
    const docCategoryLabels = { referral:'Referral', consent:'Consent', id_copy:'ID copy', lab_report:'External report', insurance:'Insurance', other:'Other' };

    async function loadDocuments() {
        const id = window.__currentPatientId;
        const box = document.getElementById('det-documents-list');
        if (!id || !box) return;
        box.innerHTML = '<p class="text-xs text-slate-400">Loading…</p>';
        try {
            const res = await api.get(`/patients/${id}/documents`);
            const list = res.documents || [];
            box.innerHTML = list.length ? list.map(d => `
                <div class="flex items-center justify-between gap-2 p-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-[11px]">
                    <div class="min-w-0">
                        <b class="text-slate-800 dark:text-white block truncate">${d.title}</b>
                        <span class="text-slate-500 block truncate">${docCategoryLabels[d.category]||d.category} · ${d.original_name} · ${d.size_kb} KB</span>
                        <span class="text-slate-400 text-[9px] block">${d.uploaded_by ? 'by '+d.uploaded_by+' · ' : ''}${d.created_at}</span>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <button onclick="downloadDocument(${d.id},'${(d.original_name||'file').replace(/'/g,"\\'")}')" class="text-emerald-600 hover:underline font-bold">Download</button>
                        <button onclick="deleteDocument(${d.id})" class="text-red-500 hover:underline font-bold">Delete</button>
                    </div>
                </div>`).join('') : '<p class="text-xs text-slate-400">No documents attached to this file.</p>';
        } catch (e) { box.innerHTML = '<p class="text-xs text-red-500">Failed to load documents.</p>'; }
    }

    async function uploadDocument(ev) {
        ev.preventDefault();
        const id = window.__currentPatientId;
        const fileEl = document.getElementById('doc-file');
        if (!id || !fileEl.files.length) return;
        const fd = new FormData();
        fd.append('title', document.getElementById('doc-title').value);
        fd.append('category', document.getElementById('doc-category').value);
        fd.append('file', fileEl.files[0]);
        try {
            await api.post(`/patients/${id}/documents`, fd);
            document.getElementById('doc-upload-form').reset();
            loadDocuments();
        } catch (e) { alert(e.message || 'Upload failed.'); }
    }

    async function downloadDocument(docId, name) {
        try {
            const token = localStorage.getItem('nis_hms_token');
            const res = await fetch(`${api.baseUrl}/patient-documents/${docId}/download`, {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            if (!res.ok) throw new Error('Download failed');
            const blob = await res.blob();
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url; a.download = name || 'document';
            document.body.appendChild(a); a.click(); a.remove();
            URL.revokeObjectURL(url);
        } catch (e) { alert('Could not download the document.'); }
    }

    async function deleteDocument(docId) {
        if (!confirm('Delete this document permanently?')) return;
        try { await api.delete(`/patient-documents/${docId}`); loadDocuments(); }
        catch (e) { alert(e.message || 'Delete failed.'); }
    }

    // Initialize Register Patient button permissions
    async function loadAssignedPatients() {
        const panel = document.getElementById('assigned-panel');
        const bodyEl = document.getElementById('assigned-body');
        try {
            const res = await api.get('/patients/assigned');
            const list = res.patients || [];
            if (!list.length) return; // hide panel entirely if no assigned patients
            panel.classList.remove('hidden');
            document.getElementById('assigned-count').innerText = (res.pagination?.total ?? list.length) + ' patient(s)';
            bodyEl.innerHTML = list.map(p => `
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/20">
                    <td class="py-2.5 px-4 font-bold text-slate-900 dark:text-white">${p.full_name}
                        <span class="text-[10px] text-slate-400 font-normal">${p.gender}${p.age != null ? ', ' + p.age + 'y' : ''}</span></td>
                    <td class="py-2.5 px-4 font-mono">${p.immigration_service_number || '—'}</td>
                    <td class="py-2.5 px-4">${p.encounters_count ?? 0}</td>
                    <td class="py-2.5 px-4 text-slate-500">${p.last_seen_at || '—'}</td>
                    <td class="py-2.5 px-4 text-right">
                        <button onclick="handleShowDetails(${p.id})" class="text-emerald-600 hover:text-emerald-700 font-bold hover:underline cursor-pointer">Open File</button>
                    </td>
                </tr>`).join('');
            lucide.createIcons();
        } catch (e) { /* non-doctors get 403 — panel simply stays hidden */ }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const role = user.roles && user.roles[0] ? user.roles[0].name : '';
        if (['super_admin', 'records_officer', 'receptionist'].includes(role)) {
            document.getElementById('register-btn-container').classList.remove('hidden');
        }

        // Doctors/consultants see their assigned patients (and can still search any).
        if (['doctor', 'consultant', 'dental_officer', 'eye_clinic_officer', 'physiotherapist'].includes(role)) {
            loadAssignedPatients();
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
