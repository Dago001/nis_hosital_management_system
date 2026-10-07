<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nigeria Immigration Service Hospital - Center of Excellence in Medical Care</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                        heading: ['Montserrat', 'sans-serif'],
                    },
                    colors: {
                        nigGreen: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            300: '#86efac',
                            450: '#008751',
                            600: '#006633',
                            700: '#0a5c36',
                            800: '#064426',
                            900: '#03331b',
                        },
                        hospitalBlue: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            600: '#0284c7',
                            700: '#0369a1',
                            900: '#0c4a6e',
                        }
                    }
                }
            }
        }
    </script>
    <!-- Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-[#f8fafc] text-slate-800 min-h-screen flex flex-col justify-between selection:bg-nigGreen-600 selection:text-white font-sans antialiased">

@if(\App\Models\Setting::getVal('maintenance_mode', '0') === '1')
    <!-- Maintenance Mode Page -->
    <div class="min-h-screen flex flex-col justify-between bg-cover bg-center relative w-full" style="background-image: url('/images/nis_building.jpg');">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm pointer-events-none"></div>
        <div class="h-1.5 w-full bg-gradient-to-r from-emerald-600 via-white to-emerald-600 relative z-10"></div>
        <div class="flex-grow flex items-center justify-center py-16 px-4 relative z-10">
            <div class="max-w-xl w-full text-center space-y-8 bg-white border border-slate-200/80 p-8 rounded-3xl shadow-2xl">
                <div class="flex flex-col items-center justify-center gap-3">
                    <img src="/assets/nis_logo-R4erN-9J.jpg" alt="NIS Logo" onerror="this.src='/favicon.svg'" class="h-16 w-16 object-contain bg-white rounded-2xl p-1 border border-slate-200/80 shadow-md">
                </div>
                <div class="space-y-2">
                    <span class="text-[9px] font-black text-emerald-600 tracking-widest uppercase">Nigeria Immigration Service</span>
                    <h1 class="text-xl font-extrabold text-slate-900 tracking-tight font-heading">System Under Maintenance</h1>
                    <p class="text-xs text-slate-650 leading-relaxed">
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
        <footer class="py-6 border-t border-white/10 text-center text-[10px] text-slate-300 relative z-10">
            &copy; 2026 Nigeria Immigration Service Medical Unit. All rights reserved.
        </footer>
    </div>
@else

    <!-- Top Utility Bar (Kelina-Inspired Top Header) -->
    <div class="w-full bg-nigGreen-900 text-white text-[11px] py-2 border-b border-nigGreen-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-2">
            <div class="flex items-center gap-6">
                <span class="flex items-center gap-1.5 text-emerald-300">
                    <i data-lucide="map-pin" class="w-3.5 h-3.5"></i> Sauka, Airport Road, Abuja &bull; Formations Nationwide
                </span>
                <span class="hidden md:flex items-center gap-1.5 text-slate-300">
                    <i data-lucide="clock" class="w-3.5 h-3.5 text-emerald-400"></i> Emergency & Triage: 24/7 Hours
                </span>
            </div>
            <div class="flex items-center gap-4 text-emerald-200 font-medium">
                <a href="tel:+2348031234567" class="hover:text-white flex items-center gap-1">
                    <i data-lucide="phone-call" class="w-3 h-3 text-emerald-400"></i> +234 (0) 9-234-5678
                </a>
                <span class="text-emerald-700">|</span>
                <a href="/login" class="hover:text-white flex items-center gap-1 font-bold">
                    <i data-lucide="lock" class="w-3 h-3 text-emerald-400"></i> Staff Portal Login
                </a>
            </div>
        </div>
    </div>

    <!-- Official Header & Navigation (Kelina-Inspired Clean Header) -->
    <header class="w-full bg-white border-b border-slate-200/90 shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <div class="flex items-center gap-3.5">
                <a href="/" class="flex items-center gap-3">
                    <img src="/assets/nis_logo-R4erN-9J.jpg" alt="NIS Logo" onerror="this.src='/favicon.svg'" class="h-14 w-14 object-contain rounded-xl p-0.5 border border-slate-100 shadow-sm">
                    <div>
                        <span class="text-base font-extrabold text-nigGreen-800 tracking-tight font-heading block leading-tight">
                            NIS HOSPITAL
                        </span>
                        <span class="text-[9px] text-slate-500 font-bold uppercase tracking-widest leading-none">
                            Nigeria Immigration Service Medical Directorate
                        </span>
                    </div>
                </a>
            </div>

            <!-- Navbar Links -->
            <nav class="hidden lg:flex items-center gap-7 text-xs font-bold text-slate-700 font-heading">
                <a href="/" class="text-nigGreen-700 hover:text-nigGreen-900 border-b-2 border-nigGreen-600 pb-1">Home</a>
                <a href="/about" class="hover:text-nigGreen-700 transition">About Hospital</a>
                <a href="/services" class="hover:text-nigGreen-700 transition">Clinical Specialties</a>
                <a href="#locations-section" class="hover:text-nigGreen-700 transition">Facilities</a>
                <a href="/privacy-policy" class="hover:text-nigGreen-700 transition">Privacy</a>
                <a href="/cookie-policy" class="hover:text-nigGreen-700 transition">Cookies</a>
            </nav>
            
            <div class="flex items-center gap-3">
                <a href="#appointment-section" class="bg-nigGreen-700 hover:bg-nigGreen-800 text-white px-5 py-2.5 rounded-full text-xs font-extrabold font-heading transition-all shadow-md flex items-center gap-1.5">
                    <i data-lucide="calendar" class="w-4 h-4"></i> Request Appointment
                </a>
                <a href="/login" id="portal-btn" class="border border-slate-200 hover:border-nigGreen-600 text-slate-700 hover:text-nigGreen-700 px-4 py-2.5 rounded-full text-xs font-bold transition-all flex items-center gap-1.5">
                    <i data-lucide="user" class="w-3.5 h-3.5"></i> Portal
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow space-y-16 pb-16">

        <!-- 1. HERO SECTION (Kelina-style high-impact hero banner with background overlay & quick badges) -->
        <section class="relative bg-nigGreen-950 text-white overflow-hidden py-16 lg:py-24">
            <div class="absolute inset-0 z-0">
                <img src="/images/nis_building.jpg" alt="NIS Medical Facility" class="w-full h-full object-cover opacity-25 scale-105 transition duration-1000">
                <div class="absolute inset-0 bg-gradient-to-r from-nigGreen-950 via-nigGreen-900/90 to-nigGreen-950/70"></div>
            </div>

            <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl space-y-6">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-[10px] font-extrabold uppercase tracking-widest bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                        <i data-lucide="award" class="w-3.5 h-3.5"></i> Federal Center of Medical Excellence
                    </div>

                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black font-heading tracking-tight leading-tight">
                        Advancing Compassionate Healthcare & Modern Medical Innovation
                    </h1>

                    <p class="text-sm sm:text-base text-slate-200 leading-relaxed font-sans max-w-2xl">
                        The Nigeria Immigration Service Hospital provides comprehensive inpatient, outpatient, and surgical care for officers, their families, and the general public. Built in the tradition of the nation's premier tertiary healthcare facilities.
                    </p>

                    <div class="flex flex-wrap items-center gap-4 pt-3">
                        <a href="#appointment-section" class="bg-emerald-500 hover:bg-emerald-600 text-slate-950 px-8 py-3.5 rounded-full text-xs font-black font-heading transition-all shadow-xl flex items-center gap-2">
                            <span>Book Consultation</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                        <a href="/services" class="bg-white/10 hover:bg-white/20 text-white border border-white/20 px-8 py-3.5 rounded-full text-xs font-bold font-heading transition-all backdrop-blur-sm">
                            Explore Specialties
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- 2. QUICK REVEAL ACTION CARDS (Kelina-style 3 highlight boxes: Emergency, Specialties, Patient Portal) -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-10 relative z-20">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Card 1: 24/7 Emergency Care -->
                <div class="bg-white rounded-3xl p-7 shadow-xl border border-slate-100 flex flex-col justify-between hover:shadow-2xl hover:-translate-y-1 transition duration-300">
                    <div class="space-y-3">
                        <div class="w-12 h-12 rounded-2xl bg-red-50 text-red-600 flex items-center justify-center font-bold">
                            <i data-lucide="ambulance" class="w-6 h-6"></i>
                        </div>
                        <h3 class="text-base font-bold font-heading text-slate-900">24/7 Emergency & Trauma</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Immediate triage response, dedicated resuscitation bays, and round-the-clock emergency surgical readiness.
                        </p>
                    </div>
                    <div class="pt-5 border-t border-slate-100 mt-4 flex items-center justify-between">
                        <span class="text-[11px] font-bold text-red-600 font-mono">Emergency Line: +234 (0) 9-234-5678</span>
                        <i data-lucide="phone-call" class="w-4 h-4 text-red-500"></i>
                    </div>
                </div>

                <!-- Card 2: Specialized Centers -->
                <div class="bg-white rounded-3xl p-7 shadow-xl border border-slate-100 flex flex-col justify-between hover:shadow-2xl hover:-translate-y-1 transition duration-300">
                    <div class="space-y-3">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-nigGreen-700 flex items-center justify-center font-bold">
                            <i data-lucide="stethoscope" class="w-6 h-6"></i>
                        </div>
                        <h3 class="text-base font-bold font-heading text-slate-900">Specialist Clinical Centers</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Endoscopy, General & Minimal Access Surgery, Urology, Cardiology, Pediatrics, Obstetrics, and Advanced Pathology Diagnostics.
                        </p>
                    </div>
                    <div class="pt-5 border-t border-slate-100 mt-4">
                        <a href="/services" class="text-xs font-bold text-nigGreen-700 hover:text-nigGreen-900 flex items-center gap-1">
                            <span>View All Departments</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </a>
                    </div>
                </div>

                <!-- Card 3: Online Portal Access -->
                <div class="bg-nigGreen-900 text-white rounded-3xl p-7 shadow-xl border border-nigGreen-800 flex flex-col justify-between hover:shadow-2xl hover:-translate-y-1 transition duration-300">
                    <div class="space-y-3">
                        <div class="w-12 h-12 rounded-2xl bg-white/10 text-emerald-300 flex items-center justify-center font-bold">
                            <i data-lucide="shield-check" class="w-6 h-6"></i>
                        </div>
                        <h3 class="text-base font-bold font-heading text-white">Digital Medical Records</h3>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            End-to-end encrypted electronic medical records (EMR), automated prescriptions, verified lab downloads, and patient scheduling.
                        </p>
                    </div>
                    <div class="pt-5 border-t border-white/10 mt-4">
                        <a href="/login" class="text-xs font-bold text-emerald-300 hover:text-emerald-200 flex items-center gap-1">
                            <span>Sign In to System Portal</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- 3. ABOUT HOSPITAL & CORE PHILOSOPHY (Kelina-style Welcome, Mission, Objective section) -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white border border-slate-200/80 rounded-3xl p-8 lg:p-14 shadow-sm grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
                <div class="lg:col-span-7 space-y-6">
                    <div class="space-y-2">
                        <span class="text-[10px] font-extrabold text-nigGreen-600 uppercase tracking-widest font-heading">
                            Welcome to the Hospital
                        </span>
                        <h2 class="text-2xl sm:text-3xl font-black font-heading text-slate-900 leading-tight">
                            Providing Modern Clinical Care in a Dignified & State-of-the-Art Environment
                        </h2>
                    </div>

                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        The Nigeria Immigration Service Hospital was established to champion high-standard medical interventions across Nigeria. Equipped with cutting-edge surgical operating theatres, fully automated clinical pathology labs, and high-definition digital radiology scanners, the Hospital serves as a beacon of clinical excellence.
                    </p>

                    <!-- Mission, Objective, Values Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-1.5">
                            <i data-lucide="heart-pulse" class="w-5 h-5 text-nigGreen-600"></i>
                            <h4 class="font-bold font-heading text-slate-900 text-xs">Our Mission</h4>
                            <p class="text-[11px] text-slate-500 leading-relaxed">To promote, preserve, and restore health through expert clinical practices and compassionate patient engagement.</p>
                        </div>
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-1.5">
                            <i data-lucide="target" class="w-5 h-5 text-nigGreen-600"></i>
                            <h4 class="font-bold font-heading text-slate-900 text-xs">Our Objective</h4>
                            <p class="text-[11px] text-slate-500 leading-relaxed">To serve as a premier national center of excellence with zero compromise on diagnostic accuracy and surgical precision.</p>
                        </div>
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-1.5">
                            <i data-lucide="users" class="w-5 h-5 text-nigGreen-600"></i>
                            <h4 class="font-bold font-heading text-slate-900 text-xs">Our Ethics</h4>
                            <p class="text-[11px] text-slate-500 leading-relaxed">Confidentiality, patient dignity, compliance with national data privacy, and round-the-clock service readiness.</p>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-5 relative">
                    <div class="rounded-3xl overflow-hidden shadow-2xl border-4 border-white">
                        <img src="/images/nis_building.jpg" alt="Hospital Facility" class="w-full h-80 lg:h-96 object-cover">
                    </div>
                    <div class="absolute -bottom-5 -left-5 bg-nigGreen-700 text-white p-5 rounded-2xl shadow-xl max-w-xs hidden sm:block">
                        <p class="text-2xl font-black font-heading">24/7</p>
                        <p class="text-[10px] text-emerald-200">Continuous emergency services, inpatient monitoring, and pharmacy operations.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- 4. CLINICAL SPECIALTIES SHOWCASE (Kelina-style Department Cards) -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="text-center max-w-2xl mx-auto space-y-2">
                <span class="text-[10px] font-extrabold text-nigGreen-600 uppercase tracking-widest font-heading">
                    Comprehensive Care
                </span>
                <h2 class="text-2xl sm:text-3xl font-black font-heading text-slate-900">
                    Clinical Departments & Specialties
                </h2>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Our team of experienced consultants, medical officers, and allied health professionals deliver evidence-based care across key medical specialties.
                </p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <div class="bg-white p-5 rounded-2xl border border-slate-200 text-center space-y-2.5 hover:shadow-lg hover:border-nigGreen-600 transition group">
                    <div class="w-12 h-12 mx-auto rounded-xl bg-emerald-50 text-nigGreen-600 flex items-center justify-center group-hover:bg-nigGreen-600 group-hover:text-white transition">
                        <i data-lucide="activity" class="w-6 h-6"></i>
                    </div>
                    <h4 class="font-bold font-heading text-xs text-slate-800">General Surgery</h4>
                    <p class="text-[10px] text-slate-500">Minimal access, laparoscopic, and elective surgical procedures.</p>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200 text-center space-y-2.5 hover:shadow-lg hover:border-nigGreen-600 transition group">
                    <div class="w-12 h-12 mx-auto rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition">
                        <i data-lucide="baby" class="w-6 h-6"></i>
                    </div>
                    <h4 class="font-bold font-heading text-xs text-slate-800">Pediatrics & Neonatal</h4>
                    <p class="text-[10px] text-slate-500">Child healthcare, immunizations, and specialist pediatric clinics.</p>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200 text-center space-y-2.5 hover:shadow-lg hover:border-nigGreen-600 transition group">
                    <div class="w-12 h-12 mx-auto rounded-xl bg-pink-50 text-pink-600 flex items-center justify-center group-hover:bg-pink-600 group-hover:text-white transition">
                        <i data-lucide="heart" class="w-6 h-6"></i>
                    </div>
                    <h4 class="font-bold font-heading text-xs text-slate-800">Obstetrics & Gynae</h4>
                    <p class="text-[10px] text-slate-500">Antenatal delivery, maternity wards, and reproductive wellness.</p>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200 text-center space-y-2.5 hover:shadow-lg hover:border-nigGreen-600 transition group">
                    <div class="w-12 h-12 mx-auto rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center group-hover:bg-purple-600 group-hover:text-white transition">
                        <i data-lucide="flask-conical" class="w-6 h-6"></i>
                    </div>
                    <h4 class="font-bold font-heading text-xs text-slate-800">Pathology & Lab</h4>
                    <p class="text-[10px] text-slate-500">Automated hematology, microbiology, and chemical pathology.</p>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200 text-center space-y-2.5 hover:shadow-lg hover:border-nigGreen-600 transition group">
                    <div class="w-12 h-12 mx-auto rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center group-hover:bg-amber-600 group-hover:text-white transition">
                        <i data-lucide="scan" class="w-6 h-6"></i>
                    </div>
                    <h4 class="font-bold font-heading text-xs text-slate-800">Digital Radiology</h4>
                    <p class="text-[10px] text-slate-500">Ultrasound scanning, X-ray, and computed imaging diagnostics.</p>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200 text-center space-y-2.5 hover:shadow-lg hover:border-nigGreen-600 transition group">
                    <div class="w-12 h-12 mx-auto rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center group-hover:bg-teal-600 group-hover:text-white transition">
                        <i data-lucide="pill" class="w-6 h-6"></i>
                    </div>
                    <h4 class="font-bold font-heading text-xs text-slate-800">Pharmacy & Dispensing</h4>
                    <p class="text-[10px] text-slate-500">NAFDAC-verified pharmaceuticals with batch-tracked distribution.</p>
                </div>
            </div>
        </section>

        <!-- 5. APPOINTMENT BOOKING SECTION (Clean Form with Policy Check) -->
        <section id="appointment-section" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white border border-slate-200 shadow-xl rounded-3xl p-8 lg:p-12 space-y-6">
                <div class="space-y-2">
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-nigGreen-700">
                        <i data-lucide="calendar-check" class="w-3.5 h-3.5"></i> Outpatient Booking Desk
                    </div>
                    <h2 class="text-xl sm:text-2xl font-bold font-heading text-slate-900">
                        Request a Medical Consultation Appointment
                    </h2>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Submit your appointment request online. Our Front Desk team will review your preferred date, assign an available consultant, and send confirmation details via email.
                    </p>
                </div>
                
                <form id="appointment-booking-form" class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-650 uppercase tracking-wider mb-2">First Name <span class="text-red-500">*</span></label>
                        <input type="text" id="apt-first-name" required placeholder="e.g. Ibrahim" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-nigGreen-600 outline-none bg-slate-50/50">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-650 uppercase tracking-wider mb-2">Last Name <span class="text-red-500">*</span></label>
                        <input type="text" id="apt-last-name" required placeholder="e.g. Musa" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-nigGreen-600 outline-none bg-slate-50/50">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-650 uppercase tracking-wider mb-2">Email Address <span class="text-red-500">*</span></label>
                        <input type="email" id="apt-email" required placeholder="patient@example.com" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-nigGreen-600 outline-none bg-slate-50/50">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-655 uppercase tracking-wider mb-2">Phone Number <span class="text-red-500">*</span></label>
                        <input type="tel" id="apt-phone" required placeholder="080XXXXXXXX" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-nigGreen-600 outline-none bg-slate-50/50">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-655 uppercase tracking-wider mb-2">Preferred Date <span class="text-red-500">*</span></label>
                        <input type="date" id="apt-date" required class="w-full px-4 py-3 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-nigGreen-600 outline-none bg-slate-50/50">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-655 uppercase tracking-wider mb-2">Preferred Time <span class="text-red-500">*</span></label>
                        <input type="time" id="apt-time" required class="w-full px-4 py-3 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-nigGreen-600 outline-none bg-slate-50/50">
                    </div>
                    <div class="md:col-span-1">
                        <label class="block text-[10px] font-bold text-slate-655 uppercase tracking-wider mb-2">Hospital Code (If registered)</label>
                        <input type="text" id="apt-service-number" placeholder="NIS/PAT/XXXXXX" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-nigGreen-600 outline-none bg-slate-50/50">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-[10px] font-bold text-slate-655 uppercase tracking-wider mb-2">Chief Complaint / Clinical Notes</label>
                        <textarea id="apt-notes" rows="1" placeholder="Briefly describe your symptoms or reason for visit..." class="w-full px-4 py-3 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-nigGreen-600 outline-none bg-slate-50/50"></textarea>
                    </div>

                    <!-- Regulatory Check -->
                    <div class="md:col-span-3 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pt-2 border-t border-slate-100">
                        <p class="text-[11px] text-slate-500">
                            By submitting, you agree to our 
                            <a href="/privacy-policy" target="_blank" class="text-nigGreen-700 underline font-bold">Privacy Policy</a> & 
                            <a href="/cookie-policy" target="_blank" class="text-nigGreen-700 underline font-bold">Cookie Policy</a>.
                        </p>
                        <button type="submit" class="bg-nigGreen-700 hover:bg-nigGreen-800 text-white px-8 py-3.5 rounded-full text-xs font-black font-heading transition shadow-lg shadow-nigGreen-700/20">
                            Submit Appointment Request
                        </button>
                    </div>
                </form>

                <!-- Success/Error Notification -->
                <div id="booking-alert" class="hidden p-4 rounded-2xl text-xs font-bold font-sans"></div>
            </div>
        </section>

        <!-- 6. LOCATIONS & CONTACT (Kelina-style Locations & Interactive Map) -->
        <section id="locations-section" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="bg-white border border-slate-200 shadow-lg rounded-3xl p-8 space-y-6 flex flex-col justify-between">
                <div class="space-y-3">
                    <span class="text-[10px] font-bold text-nigGreen-600 uppercase tracking-widest font-heading">Our Facility Locations</span>
                    <h3 class="text-xl font-bold font-heading text-slate-900">Hospital Headquarters & Contact Info</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        The primary NIS Medical Complex is situated in Sauka, Abuja along the Nnamdi Azikiwe International Airport Expressway, with satellite medical posts across regional commands.
                    </p>
                </div>

                <div class="space-y-4">
                    <div class="flex items-start gap-3 p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
                        <div class="p-2 bg-emerald-100 text-nigGreen-700 rounded-xl shrink-0 mt-0.5">
                            <i data-lucide="map-pin" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-900">NIS Headquarters Medical Complex</p>
                            <p class="text-[11px] text-slate-500">Sauka, Airport Road, P.M.B. 19, Garki, Abuja, FCT, Nigeria.</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
                        <div class="p-2 bg-emerald-100 text-nigGreen-700 rounded-xl shrink-0">
                            <i data-lucide="phone" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-900">Helpdesk & Emergency Lines</p>
                            <p class="text-[11px] text-slate-500">+234 (0) 9-234-5678, +234 (0) 803-123-4567</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
                        <div class="p-2 bg-emerald-100 text-nigGreen-700 rounded-xl shrink-0">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-900">Electronic Mail</p>
                            <p class="text-[11px] text-slate-500">medical@nishms.gov.ng &bull; support@immigration.gov.ng</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Map Iframe Embed -->
            <div class="bg-white border border-slate-200 shadow-lg rounded-3xl p-3 overflow-hidden h-[380px]">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3941.0562629165913!2d7.420803514785465!3d9.01284569353086!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x104e76a666e858db%3A0xe54d241ebad5ba7f!2sNigeria%20Immigration%20Service%2520Headquarters!5e0!3m2!1sen!2sng!4v1657492934241!5m2!1sen!2sng" 
                        width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" class="rounded-2xl"></iframe>
            </div>
        </section>

    </main>

    <!-- 7. OFFICIAL FOOTER (Kelina-Inspired Clean Multi-Column Footer with Cookie & Privacy Policy) -->
    <footer class="w-full bg-slate-900 text-slate-300 pt-14 pb-8 text-xs border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-10 pb-10 border-b border-slate-800">
                <!-- Column 1: Hospital Profile -->
                <div class="space-y-3.5">
                    <div class="flex items-center gap-2.5">
                        <img src="/assets/nis_logo-R4erN-9J.jpg" alt="NIS Logo" onerror="this.src='/favicon.svg'" class="h-9 w-9 object-contain rounded-lg bg-white p-0.5">
                        <span class="font-black text-white font-heading text-xs tracking-wider">NIS HOSPITAL</span>
                    </div>
                    <p class="text-[11px] text-slate-400 leading-relaxed font-sans">
                        Managing federal healthcare parameters, roster queues, diagnostic approvals, and inventory costing audits for the Nigeria Immigration Service officers and civil service.
                    </p>
                    <div class="text-[10px] text-emerald-400 font-bold uppercase tracking-wider">
                        Center of Excellence &bull; Abuja, Nigeria
                    </div>
                </div>

                <!-- Column 2: Quick Links -->
                <div class="space-y-3">
                    <h4 class="font-bold font-heading text-white text-xs uppercase tracking-wider">Quick Directory</h4>
                    <ul class="space-y-2 text-[11px] text-slate-400 font-sans">
                        <li><a href="/" class="hover:text-emerald-400 transition">Hospital Home</a></li>
                        <li><a href="/about" class="hover:text-emerald-400 transition">About the Facility</a></li>
                        <li><a href="/services" class="hover:text-emerald-400 transition">Clinical Specialties</a></li>
                        <li><a href="#appointment-section" class="hover:text-emerald-400 transition">Book Appointment</a></li>
                        <li><a href="/login" class="hover:text-emerald-400 transition">Staff Medical Portal</a></li>
                    </ul>
                </div>

                <!-- Column 3: Legal & Regulatory Policies -->
                <div class="space-y-3">
                    <h4 class="font-bold font-heading text-white text-xs uppercase tracking-wider">Policies & Regulations</h4>
                    <ul class="space-y-2 text-[11px] text-slate-400 font-sans">
                        <li>
                            <a href="/privacy-policy" class="hover:text-emerald-400 transition flex items-center gap-1.5 font-semibold text-slate-200">
                                <i data-lucide="shield" class="w-3.5 h-3.5 text-emerald-400"></i> Privacy Policy
                            </a>
                        </li>
                        <li>
                            <a href="/cookie-policy" class="hover:text-emerald-400 transition flex items-center gap-1.5 font-semibold text-slate-200">
                                <i data-lucide="cookie" class="w-3.5 h-3.5 text-amber-400"></i> Cookie Policy
                            </a>
                        </li>
                        <li>
                            <button onclick="openConsentModal()" class="hover:text-emerald-400 transition flex items-center gap-1.5 text-left text-slate-300">
                                <i data-lucide="file-check" class="w-3.5 h-3.5 text-blue-400"></i> Patient Data Consent Policy
                            </button>
                        </li>
                        <li><span class="text-slate-500">Nigeria Data Protection Act (NDPA) Compliant</span></li>
                    </ul>
                </div>

                <!-- Column 4: Contact & Hours -->
                <div class="space-y-3">
                    <h4 class="font-bold font-heading text-white text-xs uppercase tracking-wider">Contact Headquarters</h4>
                    <ul class="space-y-2 text-[11px] text-slate-400 font-sans">
                        <li>NIS Headquarters, Sauka, Airport Road, Abuja.</li>
                        <li>Emergency Phone: +234 (0) 9-234-5678</li>
                        <li>Support Email: medical@nishms.gov.ng</li>
                        <li class="text-emerald-400 font-bold">Inpatient & Emergency: Open 24/7</li>
                    </ul>
                </div>
            </div>

            <!-- Footer Bottom -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 text-[11px] text-slate-500 font-sans">
                <span>&copy; 2026 Nigeria Immigration Service Hospital &bull; Medical Directorate. All rights reserved.</span>
                <div class="flex items-center gap-4">
                    <a href="/privacy-policy" class="hover:text-slate-300">Privacy Policy</a>
                    <span>&bull;</span>
                    <a href="/cookie-policy" class="hover:text-slate-300">Cookie Policy</a>
                    <span>&bull;</span>
                    <a href="/login" class="hover:text-slate-300">Access Portal</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Patient Data Consent Modal -->
    <div id="consent-modal" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm">
        <div class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-8 w-full max-w-lg shadow-2xl relative my-8 flex flex-col max-h-[85vh]">
            <div class="flex items-center gap-2 mb-4 text-nigGreen-700">
                <i data-lucide="shield-check" class="w-5 h-5"></i>
                <h3 class="text-base font-bold font-heading text-slate-900">Data Consent & Processing Policy</h3>
            </div>
            
            <div class="overflow-y-auto pr-2 text-xs text-slate-650 space-y-4 leading-relaxed font-sans flex-grow">
                <p><strong>1. Introduction</strong><br>
                Welcome to the Nigeria Immigration Service (NIS) Hospital Medical Portal. We are committed to safeguarding your personal and health data in compliance with the Nigeria Data Protection Act (NDPA) and other applicable federal health guidelines.</p>
                
                <p><strong>2. Information Collection & Use</strong><br>
                By using this portal, registering for care, or requesting an outpatient appointment, you authorize the NIS Hospital Medical Center to collect, store, and process your personal details (Name, Service Number, Phone, Email, Address, and clinical encounter notes) for diagnostic, scheduling, and billing coordination.</p>
                
                <p><strong>3. Data Sharing & Security</strong><br>
                Your clinical data is strictly confidential. It is only accessible to authorized medical personnel (Front Desk records staff, Nursing staff, Assigned Physicians, Pathology Lab scientists, Pharmacists, and Cashiers) involved directly in your clinical care path.</p>
                
                <p><strong>4. User Acceptance</strong><br>
                By clicking "Agree & Proceed", you confirm you have read, understood, and voluntarily agree to the collection and processing of your personal health metrics.</p>
            </div>

            <div class="flex justify-between items-center gap-3 pt-4 mt-4 border-t border-slate-100">
                <a href="/privacy-policy" target="_blank" class="text-xs font-bold text-nigGreen-700 underline">Read Full Privacy Policy</a>
                <button onclick="acceptConsentPolicy()" class="bg-nigGreen-700 hover:bg-nigGreen-800 text-white px-6 py-2.5 rounded-full text-xs font-bold font-heading transition shadow-md">
                    Agree & Proceed
                </button>
            </div>
        </div>
    </div>

    <!-- 8. COOKIE CONSENT BANNER (Modern floating banner for first-time visitors) -->
    <div id="cookie-banner" class="hidden fixed bottom-6 left-6 right-6 sm:left-auto sm:right-6 sm:max-w-md bg-slate-900 text-white p-5 rounded-3xl shadow-2xl border border-slate-800 z-[90] space-y-3">
        <div class="flex items-start gap-3">
            <div class="p-2 rounded-xl bg-amber-500/20 text-amber-400 shrink-0">
                <i data-lucide="cookie" class="w-5 h-5"></i>
            </div>
            <div class="space-y-1">
                <p class="text-xs font-bold font-heading text-white">Cookie & Privacy Notice</p>
                <p class="text-[11px] text-slate-300 leading-relaxed font-sans">
                    We use strictly necessary and functional cookies to ensure portal security and preserve user preferences in compliance with NDPA guidelines.
                </p>
            </div>
        </div>
        <div class="flex items-center justify-end gap-2 pt-1">
            <a href="/cookie-policy" class="text-[11px] text-slate-400 hover:text-white underline font-semibold px-2">Learn More</a>
            <button onclick="acceptCookies()" class="bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-heading font-extrabold text-[11px] px-5 py-2 rounded-full transition">
                Accept Cookies
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
                btn.innerHTML = `<i data-lucide="layout-dashboard" class="w-3.5 h-3.5"></i> Dashboard`;
                btn.href = '/dashboard';
            }
        }

        document.getElementById('appointment-booking-form')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            if (localStorage.getItem('nis_data_consent_agreed') !== 'true') {
                pendingSubmit = true;
                openConsentModal();
                const alert = document.getElementById('booking-alert');
                alert.className = 'p-4 rounded-2xl text-xs font-bold font-sans bg-amber-50 text-amber-700';
                alert.innerText = 'Please read and agree to our Data Consent Policy to complete your appointment request.';
                alert.classList.remove('hidden');
                return;
            }

            submitAppointmentRequest();
        });

        async function submitAppointmentRequest() {
            const alert = document.getElementById('booking-alert');
            alert.className = 'p-4 rounded-2xl text-xs font-bold font-sans bg-emerald-50 text-nigGreen-700';
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
                    alert.className = 'p-4 rounded-2xl text-xs font-bold font-sans bg-emerald-100 text-emerald-800';
                    alert.innerText = data.message;
                    document.getElementById('appointment-booking-form').reset();
                } else {
                    alert.className = 'p-4 rounded-2xl text-xs font-bold font-sans bg-red-100 text-red-800';
                    alert.innerText = data.message || 'Verification failed. Please check inputs.';
                }
            } catch (error) {
                alert.className = 'p-4 rounded-2xl text-xs font-bold font-sans bg-red-100 text-red-800';
                alert.innerText = 'Error connecting to servers.';
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            checkAuthSession();
            checkCookieBanner();
            lucide.createIcons();
        });
    </script>
    <script src="/assets/support-chat.js"></script>
@endif
</body>
</html>
