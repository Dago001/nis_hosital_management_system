<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'facility_id' => $this->facility_id,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'gender' => $this->gender,
            'marital_status' => $this->marital_status,
            'occupation' => $this->occupation,
            'religion' => $this->religion,
            'place_of_birth' => $this->place_of_birth,
            'tribe' => $this->tribe,
            'date_of_birth' => $this->date_of_birth,
            'age' => $this->age,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'state' => $this->state,
            'lga' => $this->lga,
            'city' => $this->city,
            'next_of_kin_name' => $this->next_of_kin_name,
            'next_of_kin_relationship' => $this->next_of_kin_relationship,
            'next_of_kin_address' => $this->next_of_kin_address,
            'immigration_service_number' => $this->immigration_service_number,
            'sponsor_service_number' => $this->sponsor_service_number,
            'relationship_to_sponsor' => $this->relationship_to_sponsor,
            'sponsor' => $this->sponsor ? [
                'full_name' => $this->sponsor->full_name,
                'immigration_service_number' => $this->sponsor->immigration_service_number,
            ] : null,
            'dependants' => $this->dependants ? $this->dependants->map(fn($dep) => [
                'id' => $dep->id,
                'full_name' => $dep->full_name,
                'immigration_service_number' => $dep->immigration_service_number,
                'relationship_to_sponsor' => $dep->relationship_to_sponsor,
                'date_of_birth' => $dep->date_of_birth,
            ]) : [],
            'medications' => $this->prescriptions ? $this->prescriptions->map(fn($pres) => [
                'id' => $pres->id,
                'date' => $pres->created_at?->toIso8601String(),
                'doctor_name' => $pres->doctor ? $pres->doctor->full_name : 'System/External',
                'status' => $pres->status,
                'items' => $pres->items->map(fn($item) => [
                    'drug_name' => $item->drug_name,
                    'dosage' => $item->dosage,
                    'frequency' => $item->frequency,
                    'duration_days' => $item->duration_days,
                    'quantity_prescribed' => $item->quantity_prescribed,
                    'quantity_dispensed' => $item->quantity_dispensed,
                    'instructions' => $item->instructions,
                    'status' => $item->status,
                ]),
            ]) : [],
            'nin' => $this->nin,
            'passport_photograph_url' => $this->passport_photograph_path ? asset('storage/' . $this->passport_photograph_path) : null,
            'qr_code_data' => $this->qr_code_data,
            'barcode_data' => $this->barcode_data,
            'allergies' => $this->allergies,
            'blood_group' => $this->blood_group,
            'genotype' => $this->genotype,
            'disability' => $this->disability,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
