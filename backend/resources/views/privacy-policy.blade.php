@extends('layouts.app')

@section('title', 'Privacy Policy - Nigeria Immigration Service Hospital')

@section('content')
<div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 space-y-8">
    <!-- Header -->
    <div class="bg-gradient-to-r from-nigGreen-700 to-nigGreen-900 text-white rounded-3xl p-8 sm:p-10 shadow-xl relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 opacity-10">
            <i data-lucide="shield-check" class="w-64 h-64"></i>
        </div>
        <div class="relative z-10 space-y-3">
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-white/10 backdrop-blur-sm border border-white/20">
                <i data-lucide="lock" class="w-3.5 h-3.5 text-emerald-300"></i> Legal & Data Protection
            </span>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight">Privacy Policy</h1>
            <p class="text-xs sm:text-sm text-slate-200 max-w-2xl leading-relaxed">
                Nigeria Immigration Service Hospital Medical Services Directorate — Protecting your clinical, personal, and administrative data under the Nigeria Data Protection Act (NDPA) and NDPR regulations.
            </p>
            <p class="text-[10px] text-slate-300 font-mono">Last Updated: October 2026 | Effective Date: Continuous</p>
        </div>
    </div>

    <!-- Policy Sections -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-10 shadow-sm space-y-8 text-xs text-slate-700 dark:text-slate-300 leading-relaxed">
        <!-- 1. Introduction -->
        <section class="space-y-3">
            <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-nigGreen-50 dark:bg-nigGreen-900/40 text-nigGreen-600 flex items-center justify-center font-bold text-xs">1</span>
                Introduction & Statutory Authority
            </h2>
            <p>
                The <strong>Nigeria Immigration Service Hospital Medical Services Directorate</strong> ("NIS Hospital", "we", "our", or "the Hospital") is committed to protecting the privacy, dignity, and personal health data of NIS personnel, their accredited dependents, and civilian patients receiving care at our medical formations across Nigeria.
            </p>
            <p>
                This policy governs the collection, storage, transfer, and processing of all Personally Identifiable Information (PII) and Protected Health Information (PHI) within the NIS Hospital Management System (NIS-HMS).
            </p>
        </section>

        <!-- 2. Information Collected -->
        <section class="space-y-3 border-t border-slate-100 dark:border-slate-800 pt-6">
            <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-nigGreen-50 dark:bg-nigGreen-900/40 text-nigGreen-600 flex items-center justify-center font-bold text-xs">2</span>
                Information We Collect
            </h2>
            <ul class="list-disc pl-5 space-y-2">
                <li><strong>Biographical Information:</strong> Full name, date of birth, gender, marital status, nationality, and National Identity Number (NIN).</li>
                <li><strong>Service & Identification Credentials:</strong> NIS Service Number, command/formation posting, rank, sponsor details (for dependents), and hospital file numbers.</li>
                <li><strong>Contact Information:</strong> Residential address, phone numbers, email addresses, and emergency next-of-kin contacts.</li>
                <li><strong>Clinical Records (PHI):</strong> Consultation encounter notes, diagnosis codes, vital signs, surgical summaries, laboratory pathology results, radiological imaging reports, and prescribed pharmacotherapy.</li>
                <li><strong>Financial & Invoicing Data:</strong> Medical insurance details, billing history, payment receipt numbers, and sponsor co-payment status.</li>
            </ul>
        </section>

        <!-- 3. Legal Basis & Use -->
        <section class="space-y-3 border-t border-slate-100 dark:border-slate-800 pt-6">
            <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-nigGreen-50 dark:bg-nigGreen-900/40 text-nigGreen-600 flex items-center justify-center font-bold text-xs">3</span>
                How We Use Your Clinical & Personal Data
            </h2>
            <p>We process your data strictly for legitimate medical, administrative, and statutory purposes:</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                    <p class="font-bold text-slate-900 dark:text-white mb-1">Direct Clinical Delivery</p>
                    <p class="text-[11px] text-slate-500">Diagnosis, triage, specialist consultation, surgical planning, and pharmacy dispensing.</p>
                </div>
                <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                    <p class="font-bold text-slate-900 dark:text-white mb-1">Inter-Facility Referrals</p>
                    <p class="text-[11px] text-slate-500">Secure electronic patient transfers to national tertiary medical centers and teaching hospitals.</p>
                </div>
                <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                    <p class="font-bold text-slate-900 dark:text-white mb-1">Audit & Quality Assurance</p>
                    <p class="text-[11px] text-slate-500">Clinical auditing, pharmacy batch verification, and hospital service optimization.</p>
                </div>
                <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                    <p class="font-bold text-slate-900 dark:text-white mb-1">Appointment & Notification Dispatch</p>
                    <p class="text-[11px] text-slate-500">Sending appointment confirmations, clinic readiness alerts, and critical lab notifications.</p>
                </div>
            </div>
        </section>

        <!-- 4. Security & Retention -->
        <section class="space-y-3 border-t border-slate-100 dark:border-slate-800 pt-6">
            <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-nigGreen-50 dark:bg-nigGreen-900/40 text-nigGreen-600 flex items-center justify-center font-bold text-xs">4</span>
                Data Security & Access Controls
            </h2>
            <p>
                Access to medical files is guarded by strict Role-Based Access Control (RBAC). Only licensed physicians, certified nursing teams, registered pharmacists, laboratory scientists, and authorized medical records personnel can view relevant portions of patient files. All file interactions are permanently recorded in the immutable system Audit Trail.
            </p>
        </section>

        <!-- 5. Your Rights -->
        <section class="space-y-3 border-t border-slate-100 dark:border-slate-800 pt-6">
            <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-nigGreen-50 dark:bg-nigGreen-900/40 text-nigGreen-600 flex items-center justify-center font-bold text-xs">5</span>
                Your Patient Rights
            </h2>
            <p>Under Nigerian health privacy laws and NDPR provisions, you are entitled to:</p>
            <ul class="list-disc pl-5 space-y-1 text-slate-600 dark:text-slate-400">
                <li>Request access to your medical history and clinical discharge summaries.</li>
                <li>Request updates to biographical information or contact numbers.</li>
                <li>Confidentiality of all HIV, reproductive, psychological, and critical diagnostic reports.</li>
                <li>Lodge an inquiry with the NIS Hospital Data Protection Officer.</li>
            </ul>
        </section>

        <!-- 6. Contact Desk -->
        <section class="space-y-3 border-t border-slate-100 dark:border-slate-800 pt-6">
            <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-nigGreen-50 dark:bg-nigGreen-900/40 text-nigGreen-600 flex items-center justify-center font-bold text-xs">6</span>
                Contact the Data Protection Office
            </h2>
            <p>For inquiries, records verification, or privacy concerns, contact:</p>
            <div class="p-4 rounded-2xl bg-emerald-50/60 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900/40 text-slate-800 dark:text-slate-200">
                <p class="font-bold text-emerald-800 dark:text-emerald-400">Medical Directorate Data Compliance Unit</p>
                <p>Nigeria Immigration Service Headquarters, Sauka, Airport Road, Abuja, FCT.</p>
                <p class="mt-1">Email: <span class="font-mono text-emerald-700 dark:text-emerald-300">privacy@nishms.gov.ng</span> | Phone: +234 (0) 9-234-5678</p>
            </div>
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
