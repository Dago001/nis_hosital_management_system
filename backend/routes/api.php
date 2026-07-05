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

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify-mfa', [AuthController::class, 'verifyMfa']);

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

    // Patient Management
    Route::get('/patients', [PatientController::class, 'index']);
    Route::post('/patients', [PatientController::class, 'store'])->middleware('role_or_permission:register_patients');
    Route::get('/patients/{id}', [PatientController::class, 'show']);

    // Appointment Management
    Route::get('/appointments', [AppointmentController::class, 'index']);
    Route::post('/appointments', [AppointmentController::class, 'store']);
    Route::get('/appointments/doctors', [AppointmentController::class, 'getDoctors']);
    Route::post('/appointments/{id}/check-in', [AppointmentController::class, 'checkIn']);
    Route::post('/appointments/{id}/cancel', [AppointmentController::class, 'cancel']);

    // Clinical Workflows (Vitals & SOAP Consultations)
    Route::post('/clinical/vitals', [ClinicalController::class, 'recordVitals'])->middleware('role_or_permission:nursing_vitals');
    Route::post('/clinical/consult/{visitId}', [ClinicalController::class, 'consult'])->middleware('role_or_permission:consult_patients');

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
    Route::post('/pharmacy/inventory', [PharmacyController::class, 'addInventory']);
    Route::put('/pharmacy/inventory/{id}', [PharmacyController::class, 'updateInventory']);
    Route::get('/pharmacy/prescriptions', [PharmacyController::class, 'getPrescriptions']);
    Route::post('/pharmacy/prescriptions/{id}/dispense', [PharmacyController::class, 'dispensePrescription']);

    // Administrative / Audit Logs & Users
    Route::get('/admin/audit-logs', [AuditLogController::class, 'index'])->middleware('role_or_permission:view_audit_logs');
    
    Route::get('/admin/users', [UserController::class, 'index'])->middleware('role_or_permission:manage_users');
    Route::post('/admin/users', [UserController::class, 'store'])->middleware('role_or_permission:manage_users');
    Route::put('/admin/users/{id}', [UserController::class, 'update'])->middleware('role_or_permission:manage_users');
    Route::post('/admin/users/{id}/toggle', [UserController::class, 'toggleStatus'])->middleware('role_or_permission:manage_users');
    Route::post('/admin/users/{id}/reset-password', [UserController::class, 'resetPassword'])->middleware('role_or_permission:manage_users');
    Route::get('/admin/users/setup', [UserController::class, 'getSetupData'])->middleware('role_or_permission:manage_users');
});

