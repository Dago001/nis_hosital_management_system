<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\ClinicalController;
use App\Http\Controllers\Api\DiagnosticsController;
use App\Http\Controllers\Api\BillingController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\PharmacyController;
use App\Http\Controllers\Api\SupportChatController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ClinicalAiController;
use App\Http\Controllers\Api\SettingController;

// Public routes
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
Route::post('/verify-mfa', [AuthController::class, 'verifyMfa'])->middleware('throttle:10,1');
Route::post('/appointments/request', [AppointmentController::class, 'requestAppointment'])->middleware('throttle:30,1');
Route::post('/chat/session/init', [SupportChatController::class, 'initSession'])->middleware('throttle:30,1');
Route::get('/chat/messages', [SupportChatController::class, 'getVisitorMessages']);
Route::post('/chat/send', [SupportChatController::class, 'sendVisitorMessage'])->middleware('throttle:60,1');
Route::get('/external/sync-patients', [SettingController::class, 'syncPatients']);
Route::post('/chatbot', [App\Http\Controllers\Api\ChatbotController::class, 'chat'])->middleware('throttle:60,1');

// Protected routes
Route::middleware(['auth:sanctum', 'audit'])->group(function () {
    // Auth & Profile
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/profile/avatar', [AuthController::class, 'uploadAvatar']);
    Route::post('/toggle-mfa', [AuthController::class, 'toggleMfa']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // General Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Patient Management — reading PHI requires a clinical/administrative need-to-know
    Route::get('/patients', [PatientController::class, 'index'])->middleware('role_or_permission:view_patients');
    // Patients assigned to the authenticated doctor (must precede the {id} route)
    Route::get('/patients/assigned', [PatientController::class, 'assignedToMe'])->middleware('role_or_permission:consult_patients');
    Route::post('/patients', [PatientController::class, 'store'])->middleware('role_or_permission:register_patients');
    Route::get('/patients/{id}', [PatientController::class, 'show'])->middleware('role_or_permission:view_patients');
    // Printable patient ID card (QR)
    Route::get('/patients/{id}/id-card', [PatientController::class, 'idCard'])->middleware('role_or_permission:view_patients');
    // Patient document attachments (PHI — private storage, streamed downloads)
    Route::get('/patients/{id}/documents', [App\Http\Controllers\Api\PatientDocumentController::class, 'index'])->middleware('role_or_permission:view_patients');
    Route::post('/patients/{id}/documents', [App\Http\Controllers\Api\PatientDocumentController::class, 'store'])->middleware('role_or_permission:register_patients,view_patients');
    Route::get('/patient-documents/{documentId}/download', [App\Http\Controllers\Api\PatientDocumentController::class, 'download'])->middleware('role_or_permission:view_patients');
    Route::delete('/patient-documents/{documentId}', [App\Http\Controllers\Api\PatientDocumentController::class, 'destroy'])->middleware('role_or_permission:register_patients');

    // Central Store / Inventory issuance (Inventory Officer -> Pharmacy)
    Route::get('/inventory/items', [App\Http\Controllers\Api\InventoryController::class, 'items'])->middleware('role_or_permission:view_inventory');
    Route::get('/inventory/pharmacists', [App\Http\Controllers\Api\InventoryController::class, 'pharmacists'])->middleware('role_or_permission:view_inventory');
    Route::get('/inventory/issuances', [App\Http\Controllers\Api\InventoryController::class, 'issuances'])->middleware('role_or_permission:view_inventory');
    Route::post('/inventory/issue', [App\Http\Controllers\Api\InventoryController::class, 'issue'])->middleware('role_or_permission:issue_stock');

    // Appointment Management
    Route::get('/appointments', [AppointmentController::class, 'index']);
    Route::post('/appointments', [AppointmentController::class, 'store'])->middleware('role_or_permission:doctor,consultant,nurse,records_officer,receptionist,hospital_admin,dental_officer,eye_clinic_officer');
    Route::get('/appointments/doctors', [AppointmentController::class, 'getDoctors']);
    Route::post('/appointments/{id}/check-in', [AppointmentController::class, 'checkIn'])->middleware('role_or_permission:doctor,consultant,nurse,records_officer,receptionist,hospital_admin');
    Route::post('/appointments/{id}/cancel', [AppointmentController::class, 'cancel'])->middleware('role_or_permission:doctor,consultant,nurse,records_officer,receptionist,hospital_admin');
    Route::get('/appointments/requests', [AppointmentController::class, 'listRequests']);
    Route::post('/appointments/requests/{id}/confirm', [AppointmentController::class, 'confirmRequest'])->middleware('role_or_permission:doctor,consultant,nurse,records_officer,receptionist,hospital_admin');
    Route::post('/appointments/requests/{id}/reject', [AppointmentController::class, 'rejectRequest'])->middleware('role_or_permission:doctor,consultant,nurse,records_officer,receptionist,hospital_admin');

    // Clinical Workflows (Vitals & SOAP Consultations)
    Route::post('/clinical/vitals', [ClinicalController::class, 'recordVitals'])->middleware('role_or_permission:nursing_vitals');
    Route::get('/clinical/active-visit/{patientId}', [ClinicalController::class, 'getActiveVisit'])->middleware('role_or_permission:consult_patients');
    Route::post('/clinical/consult/{visitId}', [ClinicalController::class, 'consult'])->middleware('role_or_permission:consult_patients');
    Route::post('/clinical/drug-safety-check', [ClinicalController::class, 'drugSafetyCheck'])->middleware('role_or_permission:consult_patients');
    Route::post('/clinical/ai-chat', [ClinicalAiController::class, 'consult']);

    // Diagnostics - Laboratory
    Route::get('/diagnostics/lab/queue', [DiagnosticsController::class, 'getLabQueue']);
    Route::post('/diagnostics/lab/collect-sample/{requestId}', [DiagnosticsController::class, 'collectSample'])->middleware('role_or_permission:fill_lab_results');
    Route::post('/diagnostics/lab/submit-result/{requestId}', [DiagnosticsController::class, 'submitLabResult'])->middleware('role_or_permission:fill_lab_results');
    Route::post('/diagnostics/lab/approve-result/{requestId}', [DiagnosticsController::class, 'approveLabResult'])->middleware('role_or_permission:approve_diagnostics');

    // Diagnostics - Radiology
    Route::get('/diagnostics/radiology/queue', [DiagnosticsController::class, 'getRadiologyQueue']);
    Route::post('/diagnostics/radiology/submit-result/{requestId}', [DiagnosticsController::class, 'submitRadiologyResult'])->middleware('role_or_permission:fill_radiology_results');
    Route::post('/diagnostics/radiology/approve-result/{requestId}', [DiagnosticsController::class, 'approveRadiologyResult'])->middleware('role_or_permission:approve_diagnostics');

    // Billing & Finance
    Route::get('/billing/invoices/pending', [BillingController::class, 'getPendingInvoices'])->middleware('role_or_permission:collect_payments');
    Route::get('/billing/invoices/{id}', [BillingController::class, 'showInvoice']);
    Route::post('/billing/invoices/{invoiceId}/pay', [BillingController::class, 'collectPayment'])->middleware('role_or_permission:collect_payments');

    // Pharmacy & Dispensary
    Route::get('/pharmacy/inventory', [PharmacyController::class, 'getInventory']);
    Route::post('/pharmacy/inventory', [PharmacyController::class, 'addInventory'])->middleware('role_or_permission:manage_inventory');
    Route::put('/pharmacy/inventory/{id}', [PharmacyController::class, 'updateInventory'])->middleware('role_or_permission:manage_inventory');
    Route::get('/pharmacy/prescriptions', [PharmacyController::class, 'getPrescriptions']);
    Route::post('/pharmacy/prescriptions/{id}/cost', [PharmacyController::class, 'costPrescription'])->middleware('role_or_permission:dispense_drugs');
    Route::post('/pharmacy/prescriptions/{id}/dispense', [PharmacyController::class, 'dispensePrescription'])->middleware('role_or_permission:dispense_drugs');

    // Administrative / Audit Logs & Users
    Route::get('/admin/audit-logs', [AuditLogController::class, 'index'])->middleware('role_or_permission:view_audit_logs');
    
    Route::get('/admin/users', [UserController::class, 'index'])->middleware('role_or_permission:manage_users');
    Route::post('/admin/users', [UserController::class, 'store'])->middleware('role_or_permission:manage_users');
    Route::put('/admin/users/{id}', [UserController::class, 'update'])->middleware('role_or_permission:manage_users');
    Route::post('/admin/users/{id}/toggle', [UserController::class, 'toggleStatus'])->middleware('role_or_permission:manage_users');
    Route::post('/admin/users/{id}/reset-password', [UserController::class, 'resetPassword'])->middleware('role_or_permission:manage_users');
    Route::get('/admin/users/setup', [UserController::class, 'getSetupData'])->middleware('role_or_permission:manage_users');

    // Realtime Customer Support Center
    Route::get('/admin/chats', [SupportChatController::class, 'listSessions']);
    Route::get('/admin/chats/{id}', [SupportChatController::class, 'getSessionMessages']);
    Route::post('/admin/chats/{id}/reply', [SupportChatController::class, 'sendStaffReply']);
    Route::post('/admin/chats/{id}/close', [SupportChatController::class, 'closeSession']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);

    // Sponsor Lookup
    Route::get('/sponsor/lookup', [PatientController::class, 'lookupSponsor']);


    // === Queue Management ===
    Route::get('/queue', [App\Http\Controllers\Api\QueueController::class, 'index']);
    Route::post('/queue/{id}/status', [App\Http\Controllers\Api\QueueController::class, 'updateStatus'])->middleware('role_or_permission:doctor,consultant,nurse,receptionist,ward_manager,records_officer');
    Route::get('/queue/departments', [App\Http\Controllers\Api\QueueController::class, 'getDepartments']);
    Route::get('/queue/stats', [App\Http\Controllers\Api\QueueController::class, 'stats']);

    // === IPD Management ===
    Route::get('/ipd/wards', [App\Http\Controllers\Api\IpdController::class, 'getWards']);
    Route::get('/ipd/admissions', [App\Http\Controllers\Api\IpdController::class, 'getAdmissions']);
    Route::post('/ipd/admit', [App\Http\Controllers\Api\IpdController::class, 'admit'])->middleware('role_or_permission:doctor,consultant,nurse,ward_manager,medical_director,hospital_admin');
    Route::put('/ipd/admissions/{id}', [App\Http\Controllers\Api\IpdController::class, 'update'])->middleware('role_or_permission:doctor,consultant,nurse,ward_manager,medical_director,hospital_admin');
    Route::post('/ipd/admissions/{id}/discharge', [App\Http\Controllers\Api\IpdController::class, 'discharge'])->middleware('role_or_permission:doctor,consultant,nurse,ward_manager,medical_director,hospital_admin');
    Route::get('/ipd/beds/available', [App\Http\Controllers\Api\IpdController::class, 'getAvailableBeds']);
    Route::post('/ipd/wards', [App\Http\Controllers\Api\IpdController::class, 'createWard'])->middleware('role_or_permission:ward_manager,medical_director,hospital_admin');
    Route::post('/ipd/beds', [App\Http\Controllers\Api\IpdController::class, 'createBed'])->middleware('role_or_permission:ward_manager,medical_director,hospital_admin');
    Route::put('/ipd/beds/{id}/status', [App\Http\Controllers\Api\IpdController::class, 'updateBedStatus'])->middleware('role_or_permission:doctor,consultant,nurse,ward_manager,medical_director,hospital_admin');

    // Inpatient bedside care: observations + Medication Administration Record
    Route::get('/ipd/admissions/{id}/care', [App\Http\Controllers\Api\AdmissionCareController::class, 'show'])->middleware('role_or_permission:doctor,consultant,nurse,ward_manager,medical_director,hospital_admin');
    Route::post('/ipd/admissions/{id}/observations', [App\Http\Controllers\Api\AdmissionCareController::class, 'storeObservation'])->middleware('role_or_permission:doctor,consultant,nurse,ward_manager');
    Route::post('/ipd/admissions/{id}/medications', [App\Http\Controllers\Api\AdmissionCareController::class, 'storeMedication'])->middleware('role_or_permission:doctor,consultant,nurse,ward_manager');


    // === Reports & Analytics ===
    Route::get('/reports', [App\Http\Controllers\Api\ReportController::class, 'executive']);
    Route::get('/reports/patient-flow', [App\Http\Controllers\Api\ReportController::class, 'patientFlow']);
    Route::get('/reports/revenue', [App\Http\Controllers\Api\ReportController::class, 'revenue']);
    Route::get('/reports/clinical', [App\Http\Controllers\Api\ReportController::class, 'clinical']);
    Route::get('/reports/diagnoses', [App\Http\Controllers\Api\ReportController::class, 'diagnoses']);
    Route::get('/reports/bed-occupancy', [App\Http\Controllers\Api\ReportController::class, 'bedOccupancy']);
    Route::get('/reports/staff-performance', [App\Http\Controllers\Api\ReportController::class, 'staffPerformance']);



    // === Referral Management ===
    Route::get('/referrals', [App\Http\Controllers\Api\ReferralController::class, 'index']);
    Route::post('/referrals', [App\Http\Controllers\Api\ReferralController::class, 'store'])->middleware('role_or_permission:doctor,consultant,nurse,medical_director,chief_medical_officer,hospital_admin');
    Route::get('/referrals/doctors', [App\Http\Controllers\Api\ReferralController::class, 'getDoctors']);
    Route::get('/referrals/{id}', [App\Http\Controllers\Api\ReferralController::class, 'show']);
    Route::post('/referrals/{id}/status', [App\Http\Controllers\Api\ReferralController::class, 'updateStatus'])->middleware('role_or_permission:doctor,consultant,nurse,medical_director,chief_medical_officer,hospital_admin');



    // === Emergency Management ===
    Route::get('/emergencies', [App\Http\Controllers\Api\EmergencyController::class, 'index']);
    Route::post('/emergencies', [App\Http\Controllers\Api\EmergencyController::class, 'store'])->middleware('role_or_permission:doctor,consultant,nurse,ambulance_officer,ward_manager,hospital_admin');
    Route::put('/emergencies/{id}', [App\Http\Controllers\Api\EmergencyController::class, 'update'])->middleware('role_or_permission:doctor,consultant,nurse,ambulance_officer,ward_manager,hospital_admin');
    Route::post('/emergencies/{id}/admit-to-ward', [App\Http\Controllers\Api\EmergencyController::class, 'admitToWard'])->middleware('role_or_permission:doctor,consultant,nurse,ward_manager,hospital_admin');
    Route::get('/emergencies/staff', [App\Http\Controllers\Api\EmergencyController::class, 'getStaff']);
    Route::get('/emergencies/beds', [App\Http\Controllers\Api\EmergencyController::class, 'getAvailableBeds']);



    // NHIS / HMO Claims
    Route::get('/claims', [App\Http\Controllers\Api\ClaimController::class, 'index'])->middleware('role_or_permission:collect_payments,view_revenue_reports');
    Route::get('/claims/eligible', [App\Http\Controllers\Api\ClaimController::class, 'eligible'])->middleware('role_or_permission:collect_payments,view_revenue_reports');
    Route::get('/claims/{id}', [App\Http\Controllers\Api\ClaimController::class, 'show'])->middleware('role_or_permission:collect_payments,view_revenue_reports');
    Route::post('/claims', [App\Http\Controllers\Api\ClaimController::class, 'store'])->middleware('role_or_permission:collect_payments,create_invoices');
    Route::post('/claims/{id}/status', [App\Http\Controllers\Api\ClaimController::class, 'updateStatus'])->middleware('role_or_permission:collect_payments,create_invoices');

    // Service Tariffs (price catalogue)
    Route::get('/tariffs', [App\Http\Controllers\Api\TariffController::class, 'index']);
    Route::post('/tariffs', [App\Http\Controllers\Api\TariffController::class, 'store'])->middleware('role_or_permission:manage_settings');
    Route::put('/tariffs/{id}', [App\Http\Controllers\Api\TariffController::class, 'update'])->middleware('role_or_permission:manage_settings');

    // System Settings
    Route::get('/settings', [SettingController::class, 'index'])->middleware('role_or_permission:manage_settings');
    Route::post('/settings', [SettingController::class, 'update'])->middleware('role_or_permission:manage_settings');
    Route::post('/settings/test-fetch', [SettingController::class, 'fetchExternalData'])->middleware('role_or_permission:manage_settings');
    Route::post('/settings/test-webhook', [SettingController::class, 'triggerWebhook'])->middleware('role_or_permission:manage_settings');
});

