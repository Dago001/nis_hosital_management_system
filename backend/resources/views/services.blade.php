<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Services - NIS Medical Services</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                    colors: {
                        nigGreen: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            300: '#86efac',
                            450: '#008751',
                            600: '#006633',
                            700: '#14532d',
                            900: '#064e3b',
                        }
                    }
                }
            }
        }
    </script>
    <!-- Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-[#f4f7f5] text-slate-800 min-h-screen flex flex-col justify-between selection:bg-nigGreen-600 selection:text-white font-sans">

    <!-- Top Official Banner Bar (Green-White-Green Accent) -->
    <div class="h-2 w-full bg-gradient-to-r from-nigGreen-600 via-white to-nigGreen-600"></div>

    <!-- Official Header -->
    <header class="w-full bg-white border-b border-slate-200/80 shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <img src="/assets/nis_logo-R4erN-9J.jpg" alt="NIS Logo" onerror="this.src='/favicon.svg'" class="h-14 w-14 object-contain bg-white rounded-xl p-0.5 border border-slate-100 shadow-sm">
                <div>
                    <span class="text-sm font-extrabold text-nigGreen-600 tracking-tight uppercase block leading-tight">Nigeria Immigration Service</span>
                    <span class="text-[9px] text-slate-500 font-bold uppercase tracking-widest leading-none">Medical Services Portal</span>
                </div>
            </div>

            <!-- Navbar Links -->
            <nav class="hidden md:flex items-center gap-8 text-xs font-bold text-slate-650">
                <a href="/" class="hover:text-nigGreen-600 transition">Home</a>
                <a href="/about" class="hover:text-nigGreen-600 transition">About Us</a>
                <a href="/services" class="text-nigGreen-600 hover:text-nigGreen-700 transition">Clinical Services</a>
            </nav>
            
            <div class="flex items-center gap-3">
                <a href="/login" id="portal-btn" class="bg-nigGreen-600 hover:bg-nigGreen-700 text-white px-5 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 shadow-md shadow-nigGreen-600/10">
                    <i data-lucide="lock" class="w-4 h-4"></i> Access Portal
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow py-10 space-y-12">
        <div class="max-w-5xl mx-auto px-4 sm:px-6">
            <!-- Breadcrumbs -->
            <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-2 flex items-center gap-1">
                <a href="/" class="hover:text-nigGreen-600">Home</a>
                <span>/</span>
                <span class="text-slate-500">Clinical Services</span>
            </div>

            <!-- Services Heading -->
            <div class="bg-white border border-slate-200 shadow-xl rounded-3xl p-8 lg:p-12 space-y-8">
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <span class="h-1.5 w-8 rounded bg-nigGreen-600"></span>
                        <span class="text-[9px] font-bold text-nigGreen-600 uppercase tracking-widest">Healthcare capabilities</span>
                    </div>
                    <h1 class="text-2xl lg:text-3xl font-black text-slate-900 tracking-tight leading-tight">
                        NIS Healthcare & Medical Facilities
                    </h1>
                    <p class="text-xs text-slate-655 leading-relaxed font-sans">
                        The Medical Center provides full-service general medicine, diagnostics, and pharmaceutical support across NIS clinics in Nigeria.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-slate-100">
                    <!-- Service 1 -->
                    <div class="p-6 bg-slate-50 border border-slate-100 rounded-2xl space-y-3">
                        <div class="p-2.5 bg-nigGreen-100 text-nigGreen-600 rounded-xl w-max">
                            <i data-lucide="activity" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-sm font-bold text-slate-900">General Outpatient GOPD</h3>
                        <p class="text-xs text-slate-500 leading-relaxed font-sans">
                            Consultations, vital signs triage records, primary health check-ups, and routine clinic referrals for officers and civil patient services.
                        </p>
                    </div>

                    <!-- Service 2 -->
                    <div class="p-6 bg-slate-50 border border-slate-100 rounded-2xl space-y-3">
                        <div class="p-2.5 bg-nigGreen-100 text-nigGreen-600 rounded-xl w-max">
                            <i data-lucide="test-tube" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-sm font-bold text-slate-900">Pathological Laboratories</h3>
                        <p class="text-xs text-slate-500 leading-relaxed font-sans">
                            Fully equipped diagnostics department for running hematology, biochemistry, microbiology, and sample testing with director reviews.
                        </p>
                    </div>

                    <!-- Service 3 -->
                    <div class="p-6 bg-slate-50 border border-slate-100 rounded-2xl space-y-3">
                        <div class="p-2.5 bg-nigGreen-100 text-nigGreen-600 rounded-xl w-max">
                            <i data-lucide="pill" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-sm font-bold text-slate-900">Pharmaceutical Dispensary</h3>
                        <p class="text-xs text-slate-500 leading-relaxed font-sans">
                            Stock inventory audits, medicine cost pricing, and strict invoice verification to dispense drugs securely to verified receipt holders.
                        </p>
                    </div>

                    <!-- Service 4 -->
                    <div class="p-6 bg-slate-50 border border-slate-100 rounded-2xl space-y-3">
                        <div class="p-2.5 bg-nigGreen-100 text-nigGreen-600 rounded-xl w-max">
                            <i data-lucide="shield-alert" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-sm font-bold text-slate-900">Emergency & Ambulance Command</h3>
                        <p class="text-xs text-slate-500 leading-relaxed font-sans">
                            24/7 border patrol emergency responders, ambulance units management, and rapid evacuation clinic support channels.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Official Government Footer -->
    <footer class="w-full bg-[#e8ebe9] border-t border-slate-200/80 pt-10 pb-8 text-xs text-slate-655">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8 pb-8 border-b border-slate-200">
                <!-- Column 1: Command Title -->
                <div class="space-y-3">
                    <div class="flex items-center gap-2">
                        <img src="/assets/nis_logo-R4erN-9J.jpg" alt="NIS Logo" onerror="this.src='/favicon.svg'" class="h-8 w-8 object-contain rounded bg-white">
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
                <span>&copy; 2026 Nigeria Immigration Service. Integrated Clinic Registry Operations System.</span>
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
        function openConsentModal() {
            document.getElementById('consent-modal').classList.remove('hidden');
        }

        function closeConsentModal() {
            document.getElementById('consent-modal').classList.add('hidden');
        }

        function acceptConsentPolicy() {
            localStorage.setItem('nis_data_consent_agreed', 'true');
            closeConsentModal();
        }

        function checkAuthSession() {
            const token = localStorage.getItem('nis_hms_token');
            const btn = document.getElementById('portal-btn');
            if (token && btn) {
                btn.innerHTML = `<i data-lucide="layout-dashboard" class="w-4 h-4"></i> Go to Dashboard`;
                btn.href = '/dashboard';
            }
        }
        document.addEventListener('DOMContentLoaded', () => {
            checkAuthSession();
            lucide.createIcons();
        });
    </script>
    <script src="/assets/support-chat.js"></script>
</body>
</html>
