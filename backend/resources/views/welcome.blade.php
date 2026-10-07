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
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm pointer-events-none"></div>
        <div class="h-1.5 w-full bg-gradient-to-r from-emerald-600 via-white to-emerald-600 relative z-10"></div>
        <div class="flex-grow flex items-center justify-center py-16 px-4 relative z-10">
            <div class="max-w-xl w-full text-center space-y-8 bg-white border border-slate-200/80 p-8 rounded-3xl shadow-2xl">
                <div class="flex flex-col items-center justify-center gap-3">
                    <img src="/images/nis_logo.jpg" alt="NIS Logo" onerror="this.src='/favicon.svg'" class="h-16 w-16 object-contain bg-white rounded-2xl p-1 border border-slate-200/80 shadow-md">
                </div>
                <div class="space-y-2">
                    <span class="text-[9px] font-black text-emerald-600 tracking-widest uppercase">Nigeria Immigration Service</span>
                    <h1 class="text-xl font-extrabold text-slate-900 tracking-tight font-sans">System Under Maintenance</h1>
                    <p class="text-xs text-slate-650 font-sans leading-relaxed">
                        The NIS Medical Services Portal (NIS-MSP) is currently undergoing scheduled system optimization.
                    </p>
                </div>
                <div class="pt-2">
                    <a href="/login" class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-xl text-xs font-bold transition-all shadow-lg cursor-pointer">
                        <i data-lucide="lock" class="w-4 h-4"></i> Access Staff Portal
                    </a>
                </div>
            </div>
        </div>
        <footer class="py-6 border-t border-white/10 text-center text-[10px] text-slate-300 font-sans relative z-10">
            &copy; 2026 Nigeria Immigration Service Medical Unit. All rights reserved.
        </footer>
    </div>
@else

    <!-- Top Utility Bar (Kelina-Inspired Clean Banner) -->
    <div class="w-full bg-slate-900 text-slate-300 text-[11px] py-2 border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-2">
            <div class="flex items-center gap-6">
                <span class="flex items-center gap-1.5 text-emerald-400 font-medium">
                    <i data-lucide="map-pin" class="w-3.5 h-3.5"></i> Headquarters Medical Center, Sauka, Airport Road, Abuja
                </span>
                <span class="hidden md:flex items-center gap-1.5 text-slate-400">
                    <i data-lucide="clock" class="w-3.5 h-3.5 text-emerald-400"></i> Emergency & Triage: 24/7 Hours
                </span>
            </div>
            <div class="flex items-center gap-4 text-slate-300 font-medium">
                <a href="tel:+2348031234567" class="hover:text-white flex items-center gap-1 transition">
                    <i data-lucide="phone" class="w-3 h-3 text-emerald-400"></i> +234 (0) 9-234-5678
                </a>
                <span class="text-slate-700">|</span>
                <a href="/login" class="hover:text-emerald-400 flex items-center gap-1 font-bold text-white transition">
                    <i data-lucide="lock" class="w-3 h-3 text-emerald-400"></i> Staff Portal
                </a>
            </div>
        </div>
    </div>

    <!-- Top Official Green Accent -->
    <div class="h-1.5 w-full bg-gradient-to-r from-nigGreen-600 via-white to-nigGreen-600"></div>

    <!-- Official Header & Navigation (Consistent with About & Services Pages) -->
    <header class="w-full bg-white border-b border-slate-200/80 shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <a href="/" class="flex items-center gap-3">
                <img src="/images/nis_logo.jpg" alt="NIS Logo" onerror="this.src='/favicon.svg'" class="h-14 w-14 object-contain bg-white rounded-xl p-0.5 border border-slate-100 shadow-sm">
                <div>
                    <span class="text-sm font-extrabold text-nigGreen-600 tracking-tight uppercase block leading-tight">Nigeria Immigration Service</span>
                    <span class="text-[9px] text-slate-500 font-bold uppercase tracking-widest leading-none">Medical Services Portal</span>
                </div>
            </a>

            <!-- Navbar Links -->
            <nav class="hidden md:flex items-center gap-8 text-xs font-bold text-slate-650">
                <a href="/" class="text-nigGreen-600 hover:text-nigGreen-700 transition">Home</a>
                <a href="/about" class="hover:text-nigGreen-600 transition">About Us</a>
                <a href="/services" class="hover:text-nigGreen-600 transition">Clinical Services</a>
                <a href="#appointment-section" class="hover:text-nigGreen-600 transition">Appointments</a>
                <a href="/privacy-policy" class="hover:text-nigGreen-600 transition">Privacy</a>
                <a href="/cookie-policy" class="hover:text-nigGreen-600 transition">Cookies</a>
            </nav>
            
            <div class="flex items-center gap-3">
                <a href="#appointment-section" class="hidden sm:flex bg-emerald-50 text-nigGreen-700 hover:bg-emerald-100 px-4 py-2.5 rounded-xl text-xs font-bold transition items-center gap-1.5">
                    <i data-lucide="calendar" class="w-4 h-4"></i> Book Visit
                </a>
                <a href="/login" id="portal-btn" class="bg-nigGreen-600 hover:bg-nigGreen-700 text-white px-5 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 shadow-md shadow-nigGreen-600/10">
                    <i data-lucide="lock" class="w-4 h-4"></i> Access Portal
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow py-8 space-y-12">

        <!-- 1. HERO SECTION (Kelina-style high-impact hero banner with background overlay & quick badges) -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white border border-slate-200 shadow-xl rounded-3xl overflow-hidden flex flex-col lg:flex-row">
                <!-- Left building image with dark gradient overlay -->
                <div class="w-full lg:w-5/12 bg-slate-900 relative min-h-[340px] lg:min-h-full">
                    <img src="/images/nis_building.jpg" alt="Nigeria Immigration Service Facility" class="absolute inset-0 w-full h-full object-cover opacity-90">
                    <div class="absolute inset-0 bg-gradient-to-t from-nigGreen-900/90 via-slate-900/40 to-black/20"></div>
                    <div class="absolute bottom-6 left-6 right-6 text-white text-left space-y-1">
                        <span class="text-[9px] font-bold text-emerald-450 uppercase tracking-widest block">Abuja HQ Medical Center</span>
                        <h3 class="text-base font-extrabold">NIS Medical Services Headquarters</h3>
                        <p class="text-[10px] text-slate-300 font-sans">Federal Secretariat Complex & Sauka Medical Formations, Abuja, FCT.</p>
                    </div>
                </div>

                <!-- Right Welcome text -->
                <div class="w-full lg:w-7/12 p-8 lg:p-12 space-y-6 flex flex-col justify-center bg-white">
                    <div class="space-y-4">
                        <div class="flex items-center gap-2">
                            <span class="h-1.5 w-8 rounded bg-nigGreen-600"></span>
                            <span class="text-[9px] font-bold text-nigGreen-600 uppercase tracking-widest">FEDERAL REPUBLIC OF NIGERIA</span>
                        </div>
                        
                        <h1 class="text-2xl lg:text-3xl font-black text-slate-900 tracking-tight leading-tight">
                            Center of Medical Excellence & Healthcare Coordination
                        </h1>
                        
                        <p class="text-xs text-slate-600 leading-relaxed font-sans">
                            Providing comprehensive primary, surgical, inpatient, and emergency clinical services to Nigeria Immigration Service officers, their dependents, and civilian patients nationwide.
                        </p>
                    </div>

                    <!-- CTA Actions -->
                    <div class="flex flex-wrap items-center gap-3">
                        <a href="/login" class="bg-nigGreen-600 hover:bg-nigGreen-700 text-white px-7 py-3 rounded-xl text-xs font-bold transition-all text-center flex items-center justify-center gap-1.5 shadow-lg shadow-nigGreen-600/10">
                            Launch Systems Portal <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                        <a href="#appointment-section" class="bg-slate-100 hover:bg-slate-200 text-slate-800 px-6 py-3 rounded-xl text-xs font-bold transition text-center flex items-center justify-center gap-1.5">
                            <i data-lucide="calendar" class="w-4 h-4 text-emerald-600"></i> Book Appointment
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. QUICK REVEAL ACTION CARDS (Kelina Hospital Inspired Highlight Pillars) -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Card 1: 24/7 Emergency Care -->
                <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm hover:shadow-md transition space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-red-50 text-red-600 flex items-center justify-center font-bold">
                        <i data-lucide="ambulance" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900">24/7 Emergency & Triage</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Round-the-clock emergency medical teams, acute triage response, and resuscitation bays for critical emergencies.
                    </p>
                    <div class="pt-2 text-[11px] font-bold text-red-600">
                        Hotline: +234 (0) 9-234-5678
                    </div>
                </div>

                <!-- Card 2: Clinical Specialties -->
                <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm hover:shadow-md transition space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-nigGreen-600 flex items-center justify-center font-bold">
                        <i data-lucide="stethoscope" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900">Clinical Specialties</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Specialist clinics across General Surgery, Pediatrics, Obstetrics, Pathology, and Digital Radiology diagnostics.
                    </p>
                    <div class="pt-2">
                        <a href="/services" class="text-[11px] font-bold text-nigGreen-600 hover:underline flex items-center gap-1">
                            Explore All Services <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </a>
                    </div>
                </div>

                <!-- Card 3: Digital EMR Portal -->
                <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm hover:shadow-md transition space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                        <i data-lucide="shield-check" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900">Protected Health Information</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Secure digital electronic records, automated prescriptions, and verified pathology reports under strict NDPA compliance.
                    </p>
                    <div class="pt-2">
                        <a href="/login" class="text-[11px] font-bold text-blue-600 hover:underline flex items-center gap-1">
                            Staff Access Login <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. HOSPITAL MISSION & OBJECTIVES (Kelina-style Philosophy section) -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white border border-slate-200 shadow-sm rounded-3xl p-8 lg:p-10 space-y-6">
                <div class="max-w-2xl space-y-2">
                    <span class="text-[9px] font-bold text-nigGreen-600 uppercase tracking-widest">About Our Healthcare Mandate</span>
                    <h2 class="text-xl font-bold text-slate-900">Promoting Health & Clinical Innovation</h2>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Established in the tradition of the nation's premier medical facilities, NIS Hospital combines clinical expertise, compassionate care, and state-of-the-art medical technology.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5 pt-2">
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-1.5">
                        <div class="flex items-center gap-2 text-nigGreen-600 font-bold text-xs">
                            <i data-lucide="heart" class="w-4 h-4"></i>
                            <span>Our Mission</span>
                        </div>
                        <p class="text-[11px] text-slate-600 leading-relaxed">
                            To promote, preserve, and restore patient health by providing expert medical and surgical care within a dignified environment.
                        </p>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-1.5">
                        <div class="flex items-center gap-2 text-nigGreen-600 font-bold text-xs">
                            <i data-lucide="target" class="w-4 h-4"></i>
                            <span>Our Objective</span>
                        </div>
                        <p class="text-[11px] text-slate-600 leading-relaxed">
                            An emerging center of medical excellence in surgical and outpatient clinical delivery with strict diagnostic precision.
                        </p>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-1.5">
                        <div class="flex items-center gap-2 text-nigGreen-600 font-bold text-xs">
                            <i data-lucide="shield" class="w-4 h-4"></i>
                            <span>Our Ethics</span>
                        </div>
                        <p class="text-[11px] text-slate-600 leading-relaxed">
                            Absolute confidentiality, respect for patient rights, and rigorous compliance with federal data protection protocols.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. APPOINTMENT BOOKING FORM SECTION -->
        <div id="appointment-section" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white border border-slate-200 shadow-xl rounded-3xl p-8 lg:p-12 space-y-6">
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
                        <label class="block text-[10px] font-bold text-slate-650 uppercase tracking-wider mb-2">First Name <span class="text-red-500">*</span></label>
                        <input type="text" id="apt-first-name" required placeholder="e.g. Ibrahim" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-xs font-medium focus:ring-1 focus:ring-nigGreen-650 outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-650 uppercase tracking-wider mb-2">Last Name <span class="text-red-500">*</span></label>
                        <input type="text" id="apt-last-name" required placeholder="e.g. Musa" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-xs font-medium focus:ring-1 focus:ring-nigGreen-650 outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-650 uppercase tracking-wider mb-2">Email Address <span class="text-red-500">*</span></label>
                        <input type="email" id="apt-email" required placeholder="patient@example.com" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-xs font-medium focus:ring-1 focus:ring-nigGreen-650 outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-655 uppercase tracking-wider mb-2">Phone Number <span class="text-red-500">*</span></label>
                        <input type="tel" id="apt-phone" required placeholder="080XXXXXXXX" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-xs font-medium focus:ring-1 focus:ring-nigGreen-655 outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-655 uppercase tracking-wider mb-2">Preferred Date <span class="text-red-500">*</span></label>
                        <input type="date" id="apt-date" required class="w-full px-4 py-3 rounded-xl border border-slate-200 text-xs font-medium focus:ring-1 focus:ring-nigGreen-655 outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-655 uppercase tracking-wider mb-2">Preferred Time <span class="text-red-500">*</span></label>
                        <input type="time" id="apt-time" required class="w-full px-4 py-3 rounded-xl border border-slate-200 text-xs font-medium focus:ring-1 focus:ring-nigGreen-655 outline-none">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-[10px] font-bold text-slate-655 uppercase tracking-wider mb-2">Hospital Code (If registered)</label>
                        <input type="text" id="apt-service-number" placeholder="NIS/PAT/XXXXXX" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-xs font-medium focus:ring-1 focus:ring-nigGreen-655 outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-655 uppercase tracking-wider mb-2">Chief Complaint / Notes</label>
                        <textarea id="apt-notes" rows="1" placeholder="Brief reason for visit..." class="w-full px-4 py-3 rounded-xl border border-slate-200 text-xs font-medium focus:ring-1 focus:ring-nigGreen-655 outline-none"></textarea>
                    </div>
                    <div class="md:col-span-3 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pt-2">
                        <p class="text-[11px] text-slate-500">
                            By booking, you agree to our 
                            <a href="/privacy-policy" target="_blank" class="text-nigGreen-600 underline font-bold">Privacy Policy</a> & 
                            <a href="/cookie-policy" target="_blank" class="text-nigGreen-600 underline font-bold">Cookie Policy</a>.
                        </p>
                        <button type="submit" class="bg-nigGreen-600 hover:bg-nigGreen-700 text-white px-8 py-3 rounded-xl text-xs font-bold transition shadow-md shadow-nigGreen-600/10">
                            Submit Appointment Request
                        </button>
                    </div>
                </form>

                <!-- Success/Error Notification -->
                <div id="booking-alert" class="hidden p-4 rounded-xl text-xs font-bold font-sans"></div>
            </div>
        </div>

        <!-- 5. LOCATION REAL TIME MAP & CONTACT INFO -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-2 gap-8">
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
                        <span class="text-xs font-semibold text-slate-700">support@immigration.gov.ng, medical@nishms.gov.ng</span>
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

    <!-- 6. OFFICIAL FOOTER (Consistent, Clean, with Privacy & Cookie Policies) -->
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
                        <li>Email: medical@nishms.gov.ng</li>
                    </ul>
                </div>

                <!-- Column 4: Legal & Policies -->
                <div class="space-y-3">
                    <h4 class="font-bold text-slate-900 text-[10px] uppercase tracking-wider">Legal Framework</h4>
                    <ul class="space-y-2 text-[10px] text-slate-500 font-semibold font-sans">
                        <li><a href="/privacy-policy" class="hover:text-nigGreen-600 flex items-center gap-1"><i data-lucide="shield" class="w-3.5 h-3.5 text-emerald-600"></i> Privacy Policy</a></li>
                        <li><a href="/cookie-policy" class="hover:text-nigGreen-600 flex items-center gap-1"><i data-lucide="cookie" class="w-3.5 h-3.5 text-amber-600"></i> Cookie Policy</a></li>
                        <li>
                            <button onclick="openConsentModal()" class="text-nigGreen-600 hover:text-nigGreen-700 text-[10px] font-bold underline flex items-center gap-1">
                                <i data-lucide="file-text" class="w-3.5 h-3.5"></i> Read & Agree to Policy
                            </button>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Footer Bottom -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 text-[10px] text-slate-500 font-sans">
                <span>&copy; 2026 Nigeria Immigration Service - All rights reserved.</span>
                <div class="flex items-center gap-4">
                    <a href="/privacy-policy" class="hover:text-nigGreen-600">Privacy Policy</a>
                    <span>&bull;</span>
                    <a href="/cookie-policy" class="hover:text-nigGreen-600">Cookie Policy</a>
                    <span>&bull;</span>
                    <a href="/login" class="hover:text-nigGreen-600">Staff Portal</a>
                </div>
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
                Welcome to the Nigeria Immigration Service (NIS) Hospital Medical Portal. We are committed to safeguarding your personal and health data in compliance with the Nigeria Data Protection Act (NDPA) and other applicable federal health guidelines.</p>
                
                <p><strong>2. Information Collection & Use</strong><br>
                By using this portal, registering for care, or requesting an outpatient appointment, you authorize the NIS Hospital Medical Center to collect, store, and process your personal details (Name, Service Number, Phone, Email, Address, and clinical encounter notes) for diagnostic, scheduling, and billing coordination.</p>
                
                <p><strong>3. Data Sharing & Security</strong><br>
                Your clinical data is strictly confidential. It is only accessible to authorized medical personnel (Front Desk records staff, Nursing staff, Assigned Physicians, Pathology Lab scientists, Pharmacists, and Cashiers) involved directly in your clinical care path. No data is shared with external third parties without your explicit consent, except as required by federal law.</p>
                
                <p><strong>4. User Acceptance</strong><br>
                By clicking "Agree & Proceed", you confirm you have read, understood, and voluntarily agree to the collection and processing of your personal health metrics by the NIS Hospital Medical Center.</p>
            </div>

            <div class="flex justify-between items-center gap-3 pt-4 mt-4 border-t border-slate-100">
                <a href="/privacy-policy" target="_blank" class="text-xs font-bold text-nigGreen-600 underline">Read Full Privacy Policy</a>
                <button onclick="acceptConsentPolicy()" class="bg-nigGreen-600 hover:bg-nigGreen-700 text-white px-6 py-2.5 rounded-xl text-xs font-bold transition shadow-md shadow-nigGreen-600/10">
                    Agree & Proceed
                </button>
            </div>
        </div>
    </div>

    <!-- Floating Cookie Notice Banner -->
    <div id="cookie-banner" class="hidden fixed bottom-6 left-6 right-6 sm:left-auto sm:right-6 sm:max-w-sm bg-white dark:bg-slate-900 text-slate-800 dark:text-white p-5 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 z-[90] space-y-3">
        <div class="flex items-start gap-3">
            <div class="p-2 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 shrink-0">
                <i data-lucide="cookie" class="w-5 h-5"></i>
            </div>
            <div class="space-y-1">
                <p class="text-xs font-bold text-slate-900 dark:text-white">Cookie & Privacy Notice</p>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed font-sans">
                    We use cookies and encrypted tokens to ensure secure authentication and preserve your preferences under NDPA rules.
                </p>
            </div>
        </div>
        <div class="flex items-center justify-end gap-2 pt-1 border-t border-slate-100 dark:border-slate-800">
            <a href="/cookie-policy" class="text-[11px] text-slate-500 hover:text-slate-800 dark:hover:text-white underline font-semibold px-2">Learn More</a>
            <button onclick="acceptCookies()" class="bg-nigGreen-600 hover:bg-nigGreen-700 text-white font-bold text-[11px] px-4 py-1.5 rounded-xl transition">
                Accept
            </button>
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

        function acceptCookies() {
            localStorage.setItem('nis_cookie_consent_choice', 'accepted');
            const banner = document.getElementById('cookie-banner');
            if (banner) banner.classList.add('hidden');
        }

        function checkCookieBanner() {
            if (!localStorage.getItem('nis_cookie_consent_choice')) {
                const banner = document.getElementById('cookie-banner');
                if (banner) banner.classList.remove('hidden');
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
            checkCookieBanner();
            if (window.lucide) {
                lucide.createIcons();
            }
        });
    </script>
    <script src="/assets/support-chat.js"></script>
@endif
</body>
</html>
