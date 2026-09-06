<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - NIS Medical Services</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon" type="image/jpeg" href="/images/nis_logo.jpg">
    <link rel="apple-touch-icon" href="/images/nis_logo.jpg">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Locally bundled Tailwind CSS + Lucide icons -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Smooth fade up entry */
        @keyframes floatIn {
            0% {
                transform: translateY(15px);
                opacity: 0;
            }
            100% {
                transform: translateY(0);
                opacity: 1;
            }
        }
        .animate-float-in {
            animation: floatIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
    </style>
</head>
<body class="h-full font-sans flex items-center justify-center p-4 sm:p-6 relative overflow-hidden bg-slate-900">
    
    <!-- Background Image with Soft Shadow Mask -->
    <div class="absolute inset-0 z-0 bg-cover bg-center bg-no-repeat transform scale-102" 
         style="background-image: url('/images/nis_building.jpg'); filter: brightness(0.45) contrast(1.05);">
    </div>
    
    <!-- Linear Gradient Mask to overlay contrast -->
    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-slate-950/40 to-slate-950/60 z-0"></div>

    <!-- Floating Login Card Wrapper -->
    <div class="w-full max-w-md z-10 animate-float-in">
        <!-- Floating Login Card in Premium White -->
        <div class="bg-white border border-slate-200/80 shadow-2xl rounded-3xl p-8 relative overflow-hidden transition-all duration-300 hover:shadow-emerald-600/5">
            
            <div class="text-center mb-6 pt-2">
                <div class="flex justify-center mb-4">
                    <img src="/images/nis_logo.jpg" alt="NIS Logo" onerror="this.src='/favicon.svg'" class="h-16 w-16 object-contain rounded-2xl bg-white border border-slate-100 p-0.5 shadow-sm">
                </div>
                <h2 class="text-lg font-black text-slate-900 tracking-tight uppercase leading-none">
                    Nigeria Immigration Service
                </h2>
                <p class="mt-1 text-[10px] text-nigGreen-600 font-bold tracking-widest uppercase">
                    Medical Services Portal
                </p>
            </div>

            <!-- Error Banner -->
            <div id="error-banner" class="hidden mb-5 bg-red-550/10 border border-red-200 rounded-xl p-3 flex items-start gap-2.5">
                <i data-lucide="alert-circle" class="text-red-655 shrink-0 mt-0.5 w-4 h-4"></i>
                <span id="error-message" class="text-xs text-red-600 font-medium">Login failed. Please check credentials.</span>
            </div>

            <form id="login-form" onsubmit="handleLoginSubmit(event)" class="space-y-4">
                <!-- Username/Password Inputs Container -->
                <div id="credentials-container" class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">
                            Staff Email Address
                        </label>
                        <div class="relative">
                            <input type="email" id="email" required placeholder="admin@immigration.gov.ng"
                                   class="w-full bg-slate-50/65 border border-slate-200 rounded-xl py-3 pl-10 pr-4 text-slate-800 text-xs placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-nigGreen-600 focus:bg-white transition-all">
                            <i data-lucide="mail" class="absolute left-3.5 top-1/2 transform -translate-y-1/2 text-slate-450 w-4 h-4"></i>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">
                            Password
                        </label>
                        <div class="relative">
                            <input type="password" id="password" required placeholder="••••••••••••"
                                   class="w-full bg-slate-50/65 border border-slate-200 rounded-xl py-3 pl-10 pr-4 text-slate-800 text-xs placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-nigGreen-600 focus:bg-white transition-all">
                            <i data-lucide="lock" class="absolute left-3.5 top-1/2 transform -translate-y-1/2 text-slate-450 w-4 h-4"></i>
                        </div>
                    </div>
                </div>

                <!-- MFA Input Container (hidden by default) -->
                <div id="mfa-container" class="hidden">
                    <div class="text-center mb-5">
                        <div class="inline-flex p-3 rounded-full bg-nigGreen-50 border border-nigGreen-200 text-nigGreen-650 mb-3 animate-pulse">
                            <i data-lucide="key-round" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-sm font-bold text-slate-900">Enter MFA Passcode</h3>
                        <p class="text-[10px] text-slate-500 mt-1">
                            A verification code has been sent to your registered coordinates.
                        </p>
                    </div>

                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider text-center mb-1.5">
                        Verification OTP Code
                    </label>
                    <div class="relative">
                        <input type="text" id="mfa-code" maxLength="6" placeholder="123456"
                               class="w-full text-center bg-slate-50/65 border border-slate-200 rounded-xl py-3 text-slate-800 text-base tracking-widest font-bold placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-nigGreen-650 focus:bg-white transition-all">
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" id="submit-btn" class="w-full bg-nigGreen-600 hover:bg-nigGreen-700 text-white rounded-xl py-3.5 text-xs font-extrabold shadow-lg shadow-nigGreen-600/10 transition-all flex justify-center items-center gap-2 cursor-pointer disabled:opacity-50">
                        <span id="btn-text">Sign in</span>
                    </button>
                </div>
            </form>

            <div id="secured-banner" class="mt-6 text-center border-t border-slate-100 pt-4">
                <span class="text-[9px] text-slate-400 font-bold tracking-wider flex items-center justify-center gap-1.5 uppercase">
                    <i data-lucide="shield-check" class="text-nigGreen-600 w-3.5 h-3.5"></i> Authorized Staff Only
                </span>
            </div>
        </div>
    </div>

    <!-- JS API Call Handler -->
    <script>
        const apiBaseUrl = (() => {
            if (window.location.port === '5173' || window.location.port === '5174') {
                return 'http://localhost:8000/api';
            }
            const publicIdx = window.location.pathname.indexOf('/public');
            if (publicIdx !== -1) {
                return window.location.pathname.substring(0, publicIdx + 7) + '/api';
            }
            return '/api';
        })();

        let mfaRequired = false;

        async function handleLoginSubmit(e) {
            e.preventDefault();
            
            const errorBanner = document.getElementById('error-banner');
            const errorMessage = document.getElementById('error-message');
            const submitBtn = document.getElementById('submit-btn');
            const btnText = document.getElementById('btn-text');

            errorBanner.classList.add('hidden');
            submitBtn.disabled = true;
            btnText.innerText = 'Authenticating...';

            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const mfaCode = document.getElementById('mfa-code').value;

            try {
                let response;
                if (mfaRequired) {
                    response = await fetch(`${apiBaseUrl}/verify-mfa`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ email, code: mfaCode })
                    });
                } else {
                    response = await fetch(`${apiBaseUrl}/login`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ email, password })
                    });
                }

                const data = await response.json();
                
                if (!response.ok) {
                    throw new Error(data.message || 'Login failed. Please check your credentials.');
                }

                if (data.mfa_required) {
                    mfaRequired = true;
                    document.getElementById('credentials-container').classList.add('hidden');
                    document.getElementById('mfa-container').classList.remove('hidden');
                    document.getElementById('secured-banner').classList.add('hidden');
                    btnText.innerText = 'Verify Passcode';
                } else {
                    localStorage.setItem('nis_hms_token', data.access_token);
                    localStorage.setItem('nis_hms_user', JSON.stringify(data.user));
                    window.location.href = '/dashboard';
                }
            } catch (err) {
                errorBanner.classList.remove('hidden');
                errorMessage.innerText = err.message;
                btnText.innerText = mfaRequired ? 'Verify Passcode' : 'Access System Portal';
            } finally {
                submitBtn.disabled = false;
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const token = localStorage.getItem('nis_hms_token');
            if (token) {
                window.location.href = '/dashboard';
            }
            lucide.createIcons();
        });
    </script>
</body>
</html>
