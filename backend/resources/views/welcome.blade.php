<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NIS Medical Services Portal - Nigeria Immigration Service</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon" type="image/jpeg" href="/images/nis_logo.jpg">
    <link rel="apple-touch-icon" href="/images/nis_logo.jpg">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Locally bundled Tailwind CSS + Lucide icons -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#f4f7f5] text-slate-800 min-h-screen flex flex-col justify-between selection:bg-nigGreen-600 selection:text-white font-sans">
@if(\App\Models\Setting::getVal('maintenance_mode', '0') === '1')
    <!-- Maintenance Mode Page -->
    <div class="min-h-screen flex flex-col justify-between bg-cover bg-center relative w-full" style="background-image: url('/images/nis_building.jpg');">
        <!-- Dark Blur Overlay -->
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm pointer-events-none"></div>

        <!-- Top Official Banner Bar -->
        <div class="h-1.5 w-full bg-gradient-to-r from-emerald-600 via-white to-emerald-600 relative z-10"></div>

        <!-- Main Body -->
        <div class="flex-grow flex items-center justify-center py-16 px-4 relative z-10">
            <div class="max-w-xl w-full text-center space-y-8 bg-white border border-slate-200/80 p-8 rounded-3xl shadow-2xl">
                <!-- Logo & Icon Header -->
                <div class="flex flex-col items-center justify-center gap-3">
                    <img src="/images/nis_logo.jpg" alt="NIS Logo" onerror="this.src='/favicon.svg'" class="h-16 w-16 object-contain bg-white rounded-2xl p-1 border border-slate-200/80 shadow-md">
                   
                </div>

                <div class="space-y-2">
                    <span class="text-[9px] font-black text-emerald-600 tracking-widest uppercase">Nigeria Immigration Service</span>
                    <h1 class="text-xl font-extrabold text-slate-900 tracking-tight font-sans">System Under Maintenance</h1>
                    <p class="text-xs text-slate-650 font-sans leading-relaxed">
                        The NIS Medical Services Portal (NIS-MSP) is currently undergoing scheduled system optimization, database migrations, and security updates. 
                    </p>
                </div>

                <!-- Info Card -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-left text-xs text-slate-700 space-y-3 font-sans">
                    <div class="flex gap-2.5 items-start">
                        <i data-lucide="clock" class="w-4 h-4 text-emerald-650 shrink-0 mt-0.5 animate-spin [animation-duration:10s]"></i>
                        <div>
                            <b class="text-slate-900 block mb-0.5">Estimated Downtime:</b>
                            <span class="text-slate-600">Usually resolved in less than 2 hours. We appreciate your patience.</span>
                        </div>
                    </div>
                    <div class="flex gap-2.5 items-start border-t border-slate-200/60 pt-2.5">
                        <i data-lucide="activity" class="w-4 h-4 text-amber-600 shrink-0 mt-0.5"></i>
                        <div>
                            <b class="text-slate-900 block mb-0.5">Emergency Operations:</b>
                            <span class="text-slate-600">Hospital walk-in emergency units and triage teams are fully active. Please consult the physical facility reception desk directly.</span>
                        </div>
                    </div>
                </div>

                <!-- CTA Staff portal access -->
                <div class="pt-2">
                    <a href="/login" class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-xl text-xs font-bold transition-all shadow-lg shadow-emerald-600/10 cursor-pointer">
                        <i data-lucide="lock" class="w-4 h-4"></i> Access Staff Portal
                    </a>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="py-6 border-t border-white/10 text-center text-[10px] text-slate-300 font-sans relative z-10">
            &copy; 2026 All Right Reserved | Nigeria Immigration Service
        </footer>
    </div>
@else
    <!-- Top Official Banner Bar (Green-White-Green Accent) -->
    <div class="h-2 w-full bg-gradient-to-r from-nigGreen-600 via-white to-nigGreen-600"></div>

    <!-- Official Header -->
    <header class="w-full bg-white border-b border-slate-200/80 shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <img src="/images/nis_logo.jpg" alt="NIS Logo" onerror="this.src='/favicon.svg'" class="h-14 w-14 object-contain bg-white rounded-xl p-0.5 border border-slate-100 shadow-sm">
                <div>
                    <span class="text-sm font-extrabold text-nigGreen-600 tracking-tight uppercase block leading-tight">Nigeria Immigration Service</span>
                    <span class="text-[9px] text-slate-500 font-bold uppercase tracking-widest leading-none">Medical Services Portal</span>
                </div>
            </div>

            <!-- Navbar Links -->
            <nav class="hidden md:flex items-center gap-8 text-xs font-bold text-slate-600">
                <a href="/" class="text-nigGreen-600 hover:text-nigGreen-700 transition">Home</a>
                <a href="/about" class="hover:text-nigGreen-600 transition">About Us</a>
                <a href="/services" class="hover:text-nigGreen-600 transition">Clinical Services</a>
            </nav>
            
            <div class="flex items-center gap-3">
                <!-- Portal Dashboard/Login Button -->
                <a href="/login" id="portal-btn" class="bg-nigGreen-600 hover:bg-nigGreen-700 text-white px-5 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 shadow-md shadow-nigGreen-600/10">
                    <i data-lucide="lock" class="w-4 h-4"></i> Access Portal
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow py-10 space-y-12">
        <!-- Hero section card -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white border border-slate-200 shadow-xl rounded-3xl overflow-hidden flex flex-col lg:flex-row">
                <!-- Left building image -->
                <div class="w-full lg:w-5/12 bg-slate-900 relative min-h-[300px] lg:min-h-full">
                    <img src="/images/nis_building.jpg" alt="Nigeria Immigration Service Facility" class="absolute inset-0 w-full h-full object-cover opacity-90">
                    <div class="absolute inset-0 bg-gradient-to-t from-nigGreen-900/90 via-transparent to-black/30"></div>
                    <div class="absolute bottom-6 left-6 right-6 text-white text-left">
                        <span class="text-[9px] font-bold text-emerald-450 uppercase tracking-widest block mb-0.5">Abuja HQ</span>
                        <h3 class="text-base font-extrabold">NIS Medical Services Headquarters</h3>
                        <p class="text-[10px] text-slate-300 mt-1 font-sans">Federal Secretariat Complex, Abuja, FCT.</p>
                    </div>
                </div>

                <!-- Right Welcome text -->
                <div class="w-full lg:w-7/12 p-8 lg:p-12 space-y-6 flex flex-col justify-center bg-white">
                    <div class="space-y-4">
                        <div class="flex items-center gap-2">
                            <span class="h-1.5 w-8 rounded bg-nigGreen-600"></span>
                            <span class="text-[9px] font-bold text-nigGreen-600 uppercase tracking-widest">FEDERAL REPUBLIC OF NIGERIA</span>
                        </div>
                        
                        <h1 class="text-2xl lg:text-3xl font-black text-slate-900 tracking-tight leading-tight font-sans">
                            NIS Medical Services Portal (NIS-MSP)
                        </h1>
                        
                        <p class="text-xs text-slate-600 leading-relaxed font-sans">
                            This secure portal coordinates all clinical operations for Nigeria Immigration Service medical facilities. Authorized personnel can access triage logs, physician SOAP consultations, pathology worklists, pharmacy costing files, and cashier checkout invoicing.
                        </p>
                    </div>

                    <!-- CTA -->
                    <div class="flex">
                        <a href="/login" class="bg-nigGreen-600 hover:bg-nigGreen-700 text-white px-8 py-3.5 rounded-xl text-xs font-extrabold transition-all text-center flex items-center justify-center gap-1.5 shadow-lg shadow-nigGreen-600/10">
                            Launch Systems Portal <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Trust / Statistics Band -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach([
                    ['icon'=>'users','value'=>'50,000+','label'=>'Patients Served'],
                    ['icon'=>'stethoscope','value'=>'120+','label'=>'Medical Officers'],
                    ['icon'=>'building-2','value'=>'12','label'=>'Clinical Departments'],
                    ['icon'=>'clock','value'=>'24/7','label'=>'Emergency Response'],
                ] as $stat)
                    <div class="bg-white border border-slate-200 rounded-2xl p-5 flex items-center gap-4 shadow-sm">
                        <div class="p-3 rounded-xl bg-nigGreen-50 text-nigGreen-600 shrink-0">
                            <i data-lucide="{{ $stat['icon'] }}" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <div class="text-lg sm:text-xl font-black text-slate-900 leading-none">{{ $stat['value'] }}</div>
                            <div class="text-[10px] text-slate-500 font-semibold uppercase tracking-wide mt-1">{{ $stat['label'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Clinical Services Highlights -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-8">
                <div class="flex items-center justify-center gap-2 mb-2">
                    <span class="h-1.5 w-8 rounded bg-nigGreen-600"></span>
                    <span class="text-[9px] font-bold text-nigGreen-600 uppercase tracking-widest">Our Capabilities</span>
                    <span class="h-1.5 w-8 rounded bg-nigGreen-600"></span>
                </div>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">Comprehensive Clinical Services</h2>
                <p class="text-xs text-slate-600 mt-2 leading-relaxed">A fully integrated digital healthcare platform coordinating every stage of the patient journey · from registration and triage to diagnostics, pharmacy and billing.</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach([
                    ['icon'=>'user-plus','title'=>'Patient Registration','desc'=>'Fast, secure enrolment of officers, dependants and civilians with unique hospital codes and biometric markers.'],
                    ['icon'=>'activity','title'=>'Nursing Triage & Vitals','desc'=>'Structured vital-sign capture and priority triage routing directly into the physician consultation queue.'],
                    ['icon'=>'stethoscope','title'=>'Physician Consultation','desc'=>'SOAP-based clinical notes, ICD-10 diagnosis coding and AI-assisted clinical decision support.'],
                    ['icon'=>'test-tube','title'=>'Laboratory & Radiology','desc'=>'End-to-end diagnostic worklists with sample tracking, result entry and consultant approval.'],
                    ['icon'=>'pill','title'=>'Pharmacy & Dispensary','desc'=>'Prescription costing, dispensing and real-time drug inventory with reorder-level alerts.'],
                    ['icon'=>'credit-card','title'=>'Billing & Cashiering','desc'=>'Automated invoicing, NHIS discounting and multi-channel payment collection with receipts.'],
                ] as $svc)
                    <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm hover:shadow-lg hover:border-nigGreen-200 transition-all group">
                        <div class="p-3 rounded-2xl bg-nigGreen-50 text-nigGreen-600 w-fit mb-4 group-hover:bg-nigGreen-600 group-hover:text-white transition-colors">
                            <i data-lucide="{{ $svc['icon'] }}" class="w-6 h-6"></i>
                        </div>
                        <h3 class="text-sm font-bold text-slate-900 mb-1.5">{{ $svc['title'] }}</h3>
                        <p class="text-xs text-slate-600 leading-relaxed">{{ $svc['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Appointment Booking Form Section -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white border border-slate-200 shadow-xl rounded-3xl p-6 sm:p-8 lg:p-12 space-y-6">
                <div class="space-y-2">
                    <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                        <i data-lucide="calendar" class="text-nigGreen-600"></i> Book an Outpatient Appointment
                    </h2>
                    <p class="text-xs text-slate-500 leading-relaxed font-sans">
                        Submit a consultation appointment request. Your booking will be reviewed and confirmed by the Front Desk, upon which you will receive a confirmation notice email.
                    </p>
                </div>
                
                <form id="appointment-booking-form" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-650 uppercase tracking-wider mb-2">First Name</label>
                        <input type="text" id="apt-first-name" required class="w-full px-4 py-3 rounded-xl border border-slate-200 text-xs font-medium focus:ring-1 focus:ring-nigGreen-650 outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-650 uppercase tracking-wider mb-2">Last Name</label>
                        <input type="text" id="apt-last-name" required class="w-full px-4 py-3 rounded-xl border border-slate-200 text-xs font-medium focus:ring-1 focus:ring-nigGreen-650 outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-650 uppercase tracking-wider mb-2">Email Address</label>
                        <input type="email" id="apt-email" required class="w-full px-4 py-3 rounded-xl border border-slate-200 text-xs font-medium focus:ring-1 focus:ring-nigGreen-650 outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-655 uppercase tracking-wider mb-2">Phone Number</label>
                        <input type="tel" id="apt-phone" required class="w-full px-4 py-3 rounded-xl border border-slate-200 text-xs font-medium focus:ring-1 focus:ring-nigGreen-655 outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-655 uppercase tracking-wider mb-2">Preferred Date</label>
                        <input type="date" id="apt-date" required class="w-full px-4 py-3 rounded-xl border border-slate-200 text-xs font-medium focus:ring-1 focus:ring-nigGreen-655 outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-655 uppercase tracking-wider mb-2">Preferred Time</label>
                        <input type="time" id="apt-time" required class="w-full px-4 py-3 rounded-xl border border-slate-200 text-xs font-medium focus:ring-1 focus:ring-nigGreen-655 outline-none">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-[10px] font-bold text-slate-655 uppercase tracking-wider mb-2">Hospital Code (If registered)</label>
                        <input type="text" id="apt-service-number" placeholder="NIS/PAT/XXXXXX" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-xs font-medium focus:ring-1 focus:ring-nigGreen-655 outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-655 uppercase tracking-wider mb-2">Chief Complaint / Notes</label>
                        <textarea id="apt-notes" rows="3" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-xs font-medium focus:ring-1 focus:ring-nigGreen-655 outline-none resize-y"></textarea>
                    </div>
                    <div class="md:col-span-3 flex justify-end">
                        <button type="submit" class="bg-nigGreen-600 hover:bg-nigGreen-700 text-white px-8 py-3 rounded-xl text-xs font-bold transition shadow-md shadow-nigGreen-600/10">
                            Submit Appointment Request
                        </button>
                    </div>
                </form>

                <!-- Success/Error Notification -->
                <div id="booking-alert" class="hidden p-4 rounded-xl text-xs font-bold font-sans"></div>
            </div>
        </div>

        <!-- Location Real Time Map & Contact Info Section -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Info Panel -->
            <div class="bg-white border border-slate-200 shadow-lg rounded-3xl p-8 space-y-6 flex flex-col justify-between">
                <div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2 flex items-center gap-2">
                        <i data-lucide="map-pin" class="text-nigGreen-600"></i> Hospital Physical Location
                    </h3>
                    <p class="text-xs text-slate-600 leading-relaxed font-sans">
                        The Nigeria Immigration Service (NIS) Headquarters is situated along the Airport Road in Sauka, Abuja. The medical center provides primary healthcare and clinical command roster checks for officers and nearby civil communities.
                    </p>
                </div>

                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-nigGreen-50 text-nigGreen-600 rounded-lg">
                            <i data-lucide="map-pin" class="w-4 h-4"></i>
                        </div>
                        <span class="text-xs font-semibold text-slate-700">NIS Headquarters, Sauka, Airport Road, Abuja, Nigeria.</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-nigGreen-50 text-nigGreen-600 rounded-lg">
                            <i data-lucide="phone" class="w-4 h-4"></i>
                        </div>
                        <span class="text-xs font-semibold text-slate-700">+234 (0) 9-234-5678, +234 (0) 803-123-4567</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-nigGreen-50 text-nigGreen-600 rounded-lg">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                        </div>
                        <span class="text-xs font-semibold text-slate-700">support@immigration.gov.ng, medical@immigration.gov.ng</span>
                    </div>
                </div>
            </div>

            <!-- Map Iframe Embed -->
            <div class="bg-white border border-slate-200 shadow-lg rounded-3xl p-3 overflow-hidden h-[320px]">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3941.0562629165913!2d7.420803514785465!3d9.01284569353086!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x104e76a666e858db%3A0xe54d241ebad5ba7f!2sNigeria%20Immigration%20Service%2520Headquarters!5e0!3m2!1sen!2sng!4v1657492934241!5m2!1sen!2sng" 
                        width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" class="rounded-2xl"></iframe>
            </div>
        </div>
    </main>

    <!-- Official Government Footer -->
    <footer class="w-full bg-[#e8ebe9] border-t border-slate-200/80 pt-10 pb-8 text-xs text-slate-650">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8 pb-8 border-b border-slate-200">
                <!-- Column 1: Command Title -->
                <div class="space-y-3">
                    <div class="flex items-center gap-2">
                        <img src="/images/nis_logo.jpg" alt="NIS Logo" onerror="this.src='/favicon.svg'" class="h-8 w-8 object-contain rounded bg-white">
                        <span class="font-extrabold text-nigGreen-600 uppercase text-[10px] tracking-wider">THE NIS HOSPITAL</span>
                    </div>
                    <p class="text-[10px] text-slate-500 leading-relaxed font-sans">
                        Managing federal healthcare parameters, roster queues, diagnostic approvals, and inventory costing audits for the Nigeria Immigration Service officers and civil service.
                    </p>
                </div>

                <!-- Column 2: Quick Links -->
                <div class="space-y-3">
                    <h4 class="font-bold text-slate-900 text-[10px] uppercase tracking-wider">Quick Directory</h4>
                    <ul class="space-y-2 text-[10px] text-slate-500 font-semibold font-sans">
                        <li><a href="/" class="hover:text-nigGreen-600">Home Directory</a></li>
                        <li><a href="/about" class="hover:text-nigGreen-600">Command Profile</a></li>
                        <li><a href="/services" class="hover:text-nigGreen-600">Healthcare Services</a></li>
                        <li><a href="/login" class="hover:text-nigGreen-600">Staff Portal Entry</a></li>
                    </ul>
                </div>

                <!-- Column 3: Contact Info -->
                <div class="space-y-3">
                    <h4 class="font-bold text-slate-900 text-[10px] uppercase tracking-wider">Contact Desk</h4>
                    <ul class="space-y-2 text-[10px] text-slate-500 font-sans">
                        <li>NIS HQ, Sauka, Airport Road, Abuja.</li>
                        <li>Phone: +234 (0) 9-234-5678</li>
                        <li>Email: medical@immigration.gov.ng</li>
                    </ul>
                </div>

                <!-- Column 4: Regulatory Warning -->
                <div class="space-y-3">
                    <h4 class="font-bold text-slate-900 text-[10px] uppercase tracking-wider">Legal Framework</h4>
                    <p class="text-[10px] text-slate-500 leading-relaxed font-sans">
                        Please review and agree to our processing policies to schedule outpatient consultations.
                    </p>
                    <button onclick="openConsentModal()" class="text-nigGreen-600 hover:text-nigGreen-700 text-[10px] font-bold underline flex items-center gap-1">
                        <i data-lucide="file-text" class="w-3.5 h-3.5"></i> Read & Agree to Policy
                    </button>
                </div>
            </div>

            <!-- Footer Bottom -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 text-[10px] text-slate-500 font-sans">
                <span>&copy; 2026 All Right Reserved | Nigeria Immigration Service</span>
            </div>
        </div>
    </footer>

    <!-- Patient Data Consent Modal -->
    <div id="consent-modal" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm">
        <div class="bg-white border border-slate-200 rounded-3xl p-6 w-full max-w-lg shadow-2xl relative my-8 flex flex-col max-h-[85vh]">
            <div class="flex items-center gap-2 mb-4 text-nigGreen-600">
                <i data-lucide="shield-check" class="w-5 h-5"></i>
                <h3 class="text-base font-bold text-slate-900">Data Consent & Processing Policy</h3>
            </div>
            
            <div class="overflow-y-auto pr-2 text-xs text-slate-650 space-y-4 leading-relaxed font-sans flex-grow">
                <p><strong>1. Introduction</strong><br>
                Welcome to the Nigeria Immigration Service (NIS) Hospital Medical Portal. We are committed to safeguarding your personal and health data in compliance with the Nigeria Data Protection Regulation (NDPR) and other applicable federal health guidelines.</p>
                
                <p><strong>2. Information Collection & Use</strong><br>
                By using this portal, registering for care, or requesting an outpatient appointment, you authorize the NIS Hospital Medical Center to collect, store, and process your personal details (Name, Service Number, Phone, Email, Address, and clinical encounter notes) for diagnostic, scheduling, and billing coordination.</p>
                
                <p><strong>3. Data Sharing & Security</strong><br>
                Your clinical data is strictly confidential. It is only accessible to authorized medical personnel (Front Desk records staff, Nursing staff, Assigned Physicians, Pathology Lab scientists, Pharmacists, and Cashiers) involved directly in your clinical care path. No data is shared with external third parties without your explicit consent, except as required by federal law.</p>
                
                <p><strong>4. User Acceptance</strong><br>
                By clicking "Agree & Proceed", you confirm you have read, understood, and voluntarily agree to the collection and processing of your personal health metrics by the NIS Hospital Medical Center.</p>
            </div>

            <div class="flex justify-end gap-3 pt-4 mt-4 border-t border-slate-100">
                <button onclick="acceptConsentPolicy()" class="bg-nigGreen-600 hover:bg-nigGreen-700 text-white px-6 py-2.5 rounded-xl text-xs font-bold transition shadow-md shadow-nigGreen-600/10">
                    Agree & Proceed
                </button>
            </div>
        </div>
    </div>

    <!-- Dynamic Session check script -->
    <script>
        let pendingSubmit = false;

        function openConsentModal() {
            document.getElementById('consent-modal').classList.remove('hidden');
        }

        function closeConsentModal() {
            document.getElementById('consent-modal').classList.add('hidden');
        }

        function acceptConsentPolicy() {
            localStorage.setItem('nis_data_consent_agreed', 'true');
            closeConsentModal();
            if (pendingSubmit) {
                pendingSubmit = false;
                submitAppointmentRequest();
            }
        }

        function checkAuthSession() {
            const token = localStorage.getItem('nis_hms_token');
            const btn = document.getElementById('portal-btn');
            if (token && btn) {
                btn.innerHTML = `<i data-lucide="layout-dashboard" class="w-4 h-4"></i> Go to Dashboard`;
                btn.href = '/dashboard';
            }
        }

        document.getElementById('appointment-booking-form')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            // Check if patient agreed to the consent policy
            if (localStorage.getItem('nis_data_consent_agreed') !== 'true') {
                pendingSubmit = true;
                openConsentModal();
                const alert = document.getElementById('booking-alert');
                alert.className = 'p-4 rounded-xl text-xs font-bold font-sans bg-amber-50 text-amber-700';
                alert.innerText = 'Please read and agree to our Data Consent Policy to complete your appointment request.';
                alert.classList.remove('hidden');
                return;
            }

            submitAppointmentRequest();
        });

        async function submitAppointmentRequest() {
            const alert = document.getElementById('booking-alert');
            alert.className = 'p-4 rounded-xl text-xs font-bold font-sans bg-nigGreen-50 text-nigGreen-600';
            alert.innerText = 'Submitting appointment request...';
            alert.classList.remove('hidden');

            const payload = {
                first_name: document.getElementById('apt-first-name').value,
                last_name: document.getElementById('apt-last-name').value,
                email: document.getElementById('apt-email').value,
                phone: document.getElementById('apt-phone').value,
                appointment_date: document.getElementById('apt-date').value,
                appointment_time: document.getElementById('apt-time').value,
                immigration_service_number: document.getElementById('apt-service-number').value || null,
                notes: document.getElementById('apt-notes').value || null
            };

            try {
                const res = await fetch('/api/appointments/request', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (res.ok) {
                    alert.className = 'p-4 rounded-xl text-xs font-bold font-sans bg-emerald-100 text-emerald-800';
                    alert.innerText = data.message;
                    document.getElementById('appointment-booking-form').reset();
                } else {
                    alert.className = 'p-4 rounded-xl text-xs font-bold font-sans bg-red-100 text-red-800';
                    alert.innerText = data.message || 'Verification failed. Please check inputs.';
                }
            } catch (error) {
                alert.className = 'p-4 rounded-xl text-xs font-bold font-sans bg-red-100 text-red-800';
                alert.innerText = 'Error connecting to servers.';
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            checkAuthSession();
            lucide.createIcons();
        });
    </script>
    <script src="/assets/support-chat.js"></script>

    <!-- ═══════════ MediBot: AI Assistant (bottom-left) ═══════════ -->
    <div id="medibot" class="fixed bottom-6 left-6 z-[9998] font-sans">
        <!-- Launcher button -->
        <button id="medibot-toggle" onclick="mediBotToggle()"
                class="group flex items-center gap-2 bg-nigGreen-600 hover:bg-nigGreen-700 text-white pl-3 pr-4 py-3 rounded-full shadow-xl shadow-nigGreen-900/20 transition-all">
            <span class="relative flex h-6 w-6 items-center justify-center">
                <i data-lucide="bot" class="w-5 h-5"></i>
            </span>
            <span class="text-xs font-bold">Ask MediBot</span>
        </button>

        <!-- Chat panel -->
        <div id="medibot-panel" class="hidden absolute bottom-16 left-0 w-[calc(100vw-3rem)] max-w-sm bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col" style="height:min(70vh,560px);">
            <!-- Header -->
            <div class="bg-nigGreen-600 text-white px-4 py-3 flex items-center gap-3 shrink-0">
                <div class="w-9 h-9 rounded-full bg-white/15 flex items-center justify-center">
                    <i data-lucide="bot" class="w-5 h-5"></i>
                </div>
                <div class="flex-grow">
                    <p class="text-sm font-bold leading-tight">MediBot Assistant</p>
                    <p class="text-[10px] text-emerald-100 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-300"></span> Online · answers &amp; bookings
                    </p>
                </div>
                <button onclick="mediBotToggle()" class="text-white/80 hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <!-- Messages -->
            <div id="medibot-messages" class="flex-grow overflow-y-auto p-4 space-y-3 bg-slate-50 text-xs"></div>
            <!-- Quick chips -->
            <div id="medibot-chips" class="px-3 pt-2 flex flex-wrap gap-1.5 shrink-0 bg-white border-t border-slate-100">
                <button onclick="mediBotSend('Book an appointment')" class="text-[10px] font-semibold bg-nigGreen-50 text-nigGreen-700 border border-nigGreen-200 px-2.5 py-1 rounded-full hover:bg-nigGreen-100">Book an appointment</button>
                <button onclick="mediBotSend('What services do you offer?')" class="text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200 px-2.5 py-1 rounded-full hover:bg-slate-200">Our services</button>
                <button onclick="mediBotSend('What are your opening hours?')" class="text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200 px-2.5 py-1 rounded-full hover:bg-slate-200">Opening hours</button>
            </div>
            <!-- Input -->
            <form onsubmit="mediBotSubmit(event)" class="p-3 flex items-center gap-2 shrink-0 bg-white border-t border-slate-100">
                <input id="medibot-input" type="text" autocomplete="off" placeholder="Type your message…"
                       class="flex-grow bg-slate-100 rounded-full px-4 py-2.5 text-xs focus:outline-none focus:ring-1 focus:ring-nigGreen-500">
                <button type="submit" class="bg-nigGreen-600 hover:bg-nigGreen-700 text-white w-10 h-10 rounded-full flex items-center justify-center shrink-0">
                    <i data-lucide="send" class="w-4 h-4"></i>
                </button>
            </form>
        </div>
    </div>

    <script>
        let mediBotState = {};
        let mediBotOpened = false;

        function mediBotToggle() {
            const panel = document.getElementById('medibot-panel');
            const open = panel.classList.toggle('hidden');
            if (!open && !mediBotOpened) {
                mediBotOpened = true;
                mediBotAppend('bot', "Hello! 👋 I'm **MediBot**. I can answer questions about our services and **book an appointment** for you. How can I help?");
            }
            if (!open) setTimeout(() => document.getElementById('medibot-input')?.focus(), 100);
            lucide.createIcons();
        }

        function mediBotFormat(text) {
            const esc = (text || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
            return esc.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>').replace(/\n/g, '<br>');
        }

        function mediBotAppend(who, text) {
            const box = document.getElementById('medibot-messages');
            const wrap = document.createElement('div');
            wrap.className = who === 'user' ? 'flex justify-end' : 'flex justify-start';
            wrap.innerHTML = who === 'user'
                ? `<div class="bg-nigGreen-600 text-white rounded-2xl rounded-br-sm px-3 py-2 max-w-[85%] leading-relaxed">${mediBotFormat(text)}</div>`
                : `<div class="bg-white border border-slate-200 text-slate-700 rounded-2xl rounded-bl-sm px-3 py-2 max-w-[90%] leading-relaxed">${mediBotFormat(text)}</div>`;
            box.appendChild(wrap);
            box.scrollTop = box.scrollHeight;
        }

        function mediBotTyping(on) {
            const box = document.getElementById('medibot-messages');
            let t = document.getElementById('medibot-typing');
            if (on && !t) {
                t = document.createElement('div');
                t.id = 'medibot-typing';
                t.className = 'flex justify-start';
                t.innerHTML = `<div class="bg-white border border-slate-200 text-slate-400 rounded-2xl px-3 py-2">…</div>`;
                box.appendChild(t); box.scrollTop = box.scrollHeight;
            } else if (!on && t) { t.remove(); }
        }

        async function mediBotSend(message) {
            if (!message || !message.trim()) return;
            mediBotAppend('user', message);
            mediBotTyping(true);
            try {
                const res = await fetch('/api/chatbot', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ message, state: mediBotState })
                });
                const data = await res.json();
                mediBotTyping(false);
                if (res.ok) {
                    mediBotState = data.state || {};
                    mediBotAppend('bot', data.reply);
                } else {
                    mediBotAppend('bot', data.message || "Sorry, I couldn't process that. Please try again.");
                }
            } catch (e) {
                mediBotTyping(false);
                mediBotAppend('bot', "I'm having trouble connecting right now. Please try again shortly.");
            }
        }

        function mediBotSubmit(e) {
            e.preventDefault();
            const input = document.getElementById('medibot-input');
            const msg = input.value;
            input.value = '';
            mediBotSend(msg);
        }
    </script>
@endif
</body>
</html>
