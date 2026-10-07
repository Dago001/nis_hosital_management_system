@extends('layouts.app')

@section('title', 'Cookie Policy - Nigeria Immigration Service Hospital')

@section('content')
<div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 space-y-8">
    <!-- Header -->
    <div class="bg-gradient-to-r from-slate-800 to-nigGreen-900 text-white rounded-3xl p-8 sm:p-10 shadow-xl relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 opacity-10">
            <i data-lucide="cookie" class="w-64 h-64"></i>
        </div>
        <div class="relative z-10 space-y-3">
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-white/10 backdrop-blur-sm border border-white/20">
                <i data-lucide="cookie" class="w-3.5 h-3.5 text-amber-300"></i> Technical Compliance
            </span>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight">Cookie Policy</h1>
            <p class="text-xs sm:text-sm text-slate-200 max-w-2xl leading-relaxed">
                Explanation of how the Nigeria Immigration Service Hospital Management System (NIS-HMS) uses cookies, session tokens, and local cache to safeguard portal security and enhance user experience.
            </p>
            <p class="text-[10px] text-slate-300 font-mono">Last Updated: October 2026 | Effective Date: Continuous</p>
        </div>
    </div>

    <!-- Content Sections -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-10 shadow-sm space-y-8 text-xs text-slate-700 dark:text-slate-300 leading-relaxed">
        <!-- 1. What are cookies -->
        <section class="space-y-3">
            <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-nigGreen-50 dark:bg-nigGreen-900/40 text-nigGreen-600 flex items-center justify-center font-bold text-xs">1</span>
                What Are Cookies & Web Storage?
            </h2>
            <p>
                Cookies and browser local storage items are small, encrypted text strings placed on your device when you interact with the NIS Hospital Management System. They allow the application to identify authenticated medical personnel, verify session integrity, prevent Cross-Site Request Forgery (CSRF), and maintain clinical UI state across departments.
            </p>
        </section>

        <!-- 2. Types of Cookies -->
        <section class="space-y-4 border-t border-slate-100 dark:border-slate-800 pt-6">
            <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-nigGreen-50 dark:bg-nigGreen-900/40 text-nigGreen-600 flex items-center justify-center font-bold text-xs">2</span>
                Categories of Cookies Used in NIS-HMS
            </h2>

            <div class="space-y-3">
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800 space-y-1.5">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-slate-900 dark:text-white text-xs">Strictly Essential Security Cookies</span>
                        <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">Always Active</span>
                    </div>
                    <p class="text-[11px] text-slate-500 leading-relaxed">
                        Required for logging in, managing multi-tier RBAC sessions, handling CSRF token tokens (<code class="bg-slate-200 dark:bg-slate-700 px-1 rounded font-mono">XSRF-TOKEN</code>), and preventing unauthorized session tampering.
                    </p>
                </div>

                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800 space-y-1.5">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-slate-900 dark:text-white text-xs">Functional & Preference Storage</span>
                        <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300">Functional</span>
                    </div>
                    <p class="text-[11px] text-slate-500 leading-relaxed">
                        Stores UI preferences such as Dark Mode / Light Mode theme selections, notification badges, active patient lookup filters, and data consent acknowledgments.
                    </p>
                </div>

                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800 space-y-1.5">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-slate-900 dark:text-white text-xs">Clinical Telemetry & Performance Monitoring</span>
                        <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300">Performance</span>
                    </div>
                    <p class="text-[11px] text-slate-500 leading-relaxed">
                        Measures round-trip latency to the hospital server, helping IT administrators detect slow database response times or intermittent network connectivity across hospital branches.
                    </p>
                </div>
            </div>
        </section>

        <!-- 3. Managing Cookies -->
        <section class="space-y-3 border-t border-slate-100 dark:border-slate-800 pt-6">
            <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-nigGreen-50 dark:bg-nigGreen-900/40 text-nigGreen-600 flex items-center justify-center font-bold text-xs">3</span>
                Managing Your Preferences
            </h2>
            <p>
                You can manage or clear stored cookies through your browser settings at any time. However, please note that disabling strictly necessary cookies will prevent you from signing in to the hospital workstation or accessing clinical dashboards.
            </p>
            <p class="text-slate-500 text-[11px]">
                To reset portal consent preferences immediately, click the button below:
            </p>
            <div>
                <button type="button" onclick="localStorage.removeItem('nis_cookie_consent_choice'); alert('Your cookie preferences have been reset. Reloading page...'); location.reload();" class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 transition">
                    Reset Cookie Choices
                </button>
            </div>
        </section>

        <!-- 4. Contact -->
        <section class="space-y-3 border-t border-slate-100 dark:border-slate-800 pt-6">
            <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-nigGreen-50 dark:bg-nigGreen-900/40 text-nigGreen-600 flex items-center justify-center font-bold text-xs">4</span>
                Questions & Assistance
            </h2>
            <p>
                For technical questions regarding web security headers or cookie practices, contact the <strong>NIS Medical Directorate ICT & Systems Administration Unit</strong> at <span class="font-mono text-emerald-600">ict@nishms.gov.ng</span>.
            </p>
        </section>
    </div>

    <!-- Back to home link -->
    <div class="text-center pt-2">
        <a href="/" class="inline-flex items-center gap-2 text-xs font-bold text-nigGreen-600 hover:text-nigGreen-700 underline">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Return to Medical Portal Home
        </a>
    </div>
</div>
@endsection
