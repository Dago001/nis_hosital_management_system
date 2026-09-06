<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\AdmissionObservation;
use App\Models\MedicationAdministration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Bedside inpatient care: ongoing ward observations (nursing vitals + fluid
 * balance + notes) and the Medication Administration Record (MAR).
 */
class AdmissionCareController extends Controller
{
    /**
     * Full care record for one admission: patient, observations, MAR, and the
     * prescribed drugs (from the linked visit) available for administration.
     */
    public function show($id)
    {
        $admission = Admission::with([
            'patient', 'bed.ward', 'doctor',
            'observations.recorder',
            'medicationAdministrations.administrator',
            'visit.prescriptions.items',
        ])->findOrFail($id);

        // Flatten prescribed drugs for the MAR "administer" picker.
        $prescribed = [];
        foreach ($admission->visit?->prescriptions ?? [] as $pres) {
            foreach ($pres->items as $item) {
                $prescribed[] = [
                    'prescription_item_id' => $item->id,
                    'drug_name' => $item->drug_name,
                    'dosage' => $item->dosage,
                    'frequency' => $item->frequency,
                ];
            }
        }

        return response()->json([
            'admission' => [
                'id' => $admission->id,
                'status' => $admission->status,
                'patient_name' => $admission->patient?->full_name,
                'hospital_code' => $admission->patient?->immigration_service_number,
                'allergies' => $admission->patient?->allergies,
                'ward' => $admission->bed?->ward?->name,
                'bed' => $admission->bed?->bed_number,
                'doctor' => $admission->doctor?->full_name,
                'diagnosis' => $admission->diagnosis_on_admission,
                'admitted_at' => $admission->admitted_at?->toDateTimeString(),
                'discharged_at' => $admission->discharged_at?->toDateTimeString(),
                'discharge_summary' => $admission->discharge_summary,
            ],
            'prescribed_drugs' => $prescribed,
            'observations' => $admission->observations->map(fn ($o) => [
                'id' => $o->id,
                'recorded_by' => $o->recorder?->full_name ?? 'Nurse',
                'blood_pressure' => $o->blood_pressure,
                'temperature' => $o->temperature,
                'pulse_rate' => $o->pulse_rate,
                'respiratory_rate' => $o->respiratory_rate,
                'spo2' => $o->spo2,
                'fluid_intake_ml' => $o->fluid_intake_ml,
                'fluid_output_ml' => $o->fluid_output_ml,
                'news_score' => $o->news_score,
                'notes' => $o->notes,
                'recorded_at' => $o->recorded_at?->toDateTimeString(),
            ]),
            'medications' => $admission->medicationAdministrations->map(fn ($m) => [
                'id' => $m->id,
                'drug_name' => $m->drug_name,
                'dose' => $m->dose,
                'route' => $m->route,
                'status' => $m->status,
                'notes' => $m->notes,
                'administered_by' => $m->administrator?->full_name ?? 'Nurse',
                'administered_at' => $m->administered_at?->toDateTimeString(),
            ]),
        ]);
    }

    public function storeObservation(Request $request, $id)
    {
        $admission = Admission::findOrFail($id);
        if ($admission->status !== 'active') {
            return response()->json(['message' => 'Cannot record observations on a discharged admission.'], 422);
        }

        $validated = $request->validate([
            'blood_pressure' => 'nullable|string|max:20',
            'temperature' => 'nullable|numeric|min:25|max:45',
            'pulse_rate' => 'nullable|integer|min:0|max:300',
            'respiratory_rate' => 'nullable|integer|min:0|max:100',
            'spo2' => 'nullable|integer|min:0|max:100',
            'fluid_intake_ml' => 'nullable|numeric|min:0',
            'fluid_output_ml' => 'nullable|numeric|min:0',
            'news_score' => 'nullable|integer|min:0|max:20',
            'notes' => 'nullable|string|max:2000',
        ]);

        $validated['admission_id'] = $admission->id;
        $validated['recorded_by'] = Auth::user()?->staff?->id;
        $validated['recorded_at'] = now();

        $obs = AdmissionObservation::create($validated);

        return response()->json(['message' => 'Observation recorded.', 'observation' => $obs], 201);
    }

    public function storeMedication(Request $request, $id)
    {
        $admission = Admission::findOrFail($id);
        if ($admission->status !== 'active') {
            return response()->json(['message' => 'Cannot administer medication on a discharged admission.'], 422);
        }

        $validated = $request->validate([
            'prescription_item_id' => 'nullable|exists:prescription_items,id',
            'drug_name' => 'required|string|max:255',
            'dose' => 'nullable|string|max:100',
            'route' => 'nullable|string|max:50',
            'status' => 'required|in:given,missed,held,refused',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validated['admission_id'] = $admission->id;
        $validated['administered_by'] = Auth::user()?->staff?->id;
        $validated['administered_at'] = now();

        $mar = MedicationAdministration::create($validated);

        return response()->json(['message' => 'Medication administration recorded.', 'medication' => $mar], 201);
    }
}
