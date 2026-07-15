<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Visit;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\LabRequest;
use App\Models\RadiologyRequest;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ClinicalController extends Controller
{
    public function recordVitals(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'department_id' => 'required|exists:departments,id',
            'vitals_blood_pressure' => 'required|string',
            'vitals_temperature' => 'required|numeric',
            'vitals_pulse_rate' => 'required|integer',
            'vitals_respiratory_rate' => 'nullable|integer',
            'vitals_weight' => 'nullable|numeric',
            'vitals_height' => 'nullable|numeric',
            'doctor_id' => 'required|exists:staff,id', // Assigned doctor
        ]);

        // Save the assigned doctor as the staff_id for the visit
        $validated['staff_id'] = $validated['doctor_id'];
        unset($validated['doctor_id']);

        // Find existing pending visit with null vitals for this patient
        $visit = Visit::where('patient_id', $validated['patient_id'])
            ->whereNull('vitals_blood_pressure')
            ->first();

        if ($visit) {
            $visit->update($validated);
        } else {
            $visit = Visit::create($validated);
        }

        return response()->json([
            'message' => 'Vitals recorded successfully and patient assigned to doctor.',
            'visit' => $visit
        ], 210);
    }

    /**
     * Complete consultation notes, diagnoses, and orders (usually done by Doctors)
     */
    public function consult(Request $request, int $visitId)
    {
        $visit = Visit::find($visitId);

        if (!$visit) {
            return response()->json(['message' => 'Active visit not found.'], 404);
        }

        $validated = $request->validate([
            'chief_complaint' => 'required|string',
            'history' => 'nullable|string',
            'soap_notes_subjective' => 'required|string',
            'soap_notes_objective' => 'required|string',
            'soap_notes_assessment' => 'required|string',
            'soap_notes_plan' => 'required|string',
            'diagnosis_icd10' => 'required|string',
            'diagnosis_description' => 'required|string',
            'treatment_plan' => 'nullable|string',
            'follow_up_date' => 'nullable|date',
            
            // Orders
            'prescriptions' => 'nullable|array',
            'prescriptions.*.drug_name' => 'required|string',
            'prescriptions.*.dosage' => 'required|string',
            'prescriptions.*.frequency' => 'required|string',
            'prescriptions.*.duration_days' => 'required|integer',
            'prescriptions.*.quantity_prescribed' => 'required|integer',
            'prescriptions.*.instructions' => 'nullable|string',
            
            'lab_tests' => 'nullable|array',
            'lab_tests.*.test_name' => 'required|string',
            'lab_tests.*.clinical_indication' => 'nullable|string',
            
            'radiology_tests' => 'nullable|array',
            'radiology_tests.*.scan_type' => 'required|string',
            'radiology_tests.*.body_part' => 'required|string',
            'radiology_tests.*.clinical_indication' => 'nullable|string',
        ]);

        $doctor = Auth::user()->staff;
        $doctorId = $doctor ? $doctor->id : $visit->staff_id;

        DB::transaction(function () use ($visit, $validated, $doctorId) {
            // 1. Update the consultation details on the visit
            $visit->update([
                'staff_id' => $doctorId, // Ensure the actual doctor is logged
                'chief_complaint' => $validated['chief_complaint'],
                'history' => $validated['history'] ?? null,
                'soap_notes_subjective' => $validated['soap_notes_subjective'],
                'soap_notes_objective' => $validated['soap_notes_objective'],
                'soap_notes_assessment' => $validated['soap_notes_assessment'],
                'soap_notes_plan' => $validated['soap_notes_plan'],
                'diagnosis_icd10' => $validated['diagnosis_icd10'],
                'diagnosis_description' => $validated['diagnosis_description'],
                'treatment_plan' => $validated['treatment_plan'] ?? null,
                'follow_up_date' => $validated['follow_up_date'] ?? null,
            ]);

            // 2. Process Prescriptions if ordered
            if (!empty($validated['prescriptions'])) {
                $prescription = Prescription::create([
                    'visit_id' => $visit->id,
                    'patient_id' => $visit->patient_id,
                    'staff_id' => $doctorId,
                    'status' => 'pending'
                ]);

                foreach ($validated['prescriptions'] as $item) {
                    PrescriptionItem::create([
                        'prescription_id' => $prescription->id,
                        'drug_name' => $item['drug_name'],
                        'dosage' => $item['dosage'],
                        'frequency' => $item['frequency'],
                        'duration_days' => $item['duration_days'],
                        'quantity_prescribed' => $item['quantity_prescribed'],
                        'instructions' => $item['instructions'] ?? null,
                        'status' => 'pending'
                    ]);
                }
            }

            // 3. Process Lab requests if ordered
            if (!empty($validated['lab_tests'])) {
                foreach ($validated['lab_tests'] as $lab) {
                    LabRequest::create([
                        'visit_id' => $visit->id,
                        'patient_id' => $visit->patient_id,
                        'staff_id' => $doctorId,
                        'test_name' => $lab['test_name'],
                        'clinical_indication' => $lab['clinical_indication'] ?? null,
                        'status' => 'requested'
                    ]);
                }
            }

            // 4. Process Radiology requests if ordered
            if (!empty($validated['radiology_tests'])) {
                foreach ($validated['radiology_tests'] as $rad) {
                    RadiologyRequest::create([
                        'visit_id' => $visit->id,
                        'patient_id' => $visit->patient_id,
                        'staff_id' => $doctorId,
                        'scan_type' => $rad['scan_type'],
                        'body_part' => $rad['body_part'],
                        'clinical_indication' => $rad['clinical_indication'] ?? null,
                        'status' => 'requested'
                    ]);
                }
            }

            // 5. Generate Billing Invoice automatically for GOPD Consultation Fee
            $patient = $visit->patient;
            $discount = 0.00;
            if ($patient && $patient->isNhis()) {
                $discount = 2000.00 * 0.15; // 15% discount
            }

            $invoice = Invoice::create([
                'patient_id' => $visit->patient_id,
                'visit_id' => $visit->id,
                'total_amount' => 2000.00, // Fixed Consultation Fee in NGN
                'discount_amount' => $discount,
                'paid_amount' => 0.00,
                'status' => 'unpaid'
            ]);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'item_name' => 'General Medical Consultation',
                'quantity' => 1,
                'unit_price' => 2000.00,
                'total_price' => 2000.00
            ]);
        });

        return response()->json([
            'message' => 'Consultation completed and billing generated.',
            'visit' => $visit->load(['prescriptions', 'labRequests', 'radiologyRequests'])
        ]);
    }

    /**
     * Get active consult/triage visit for patient lookup
     */
    public function getActiveVisit(Request $request, $patientId)
    {
        $visit = Visit::with(['patient', 'doctor', 'department'])
            ->where('patient_id', $patientId)
            ->whereNotNull('vitals_blood_pressure')
            ->whereNull('chief_complaint')
            ->latest('created_at')
            ->first();

        if (!$visit) {
            return response()->json([
                'message' => 'No active waiting consult file found for this patient. Ensure triage vitals are recorded first.'
            ], 404);
        }

        return response()->json([
            'visit' => $visit
        ]);
    }
}
