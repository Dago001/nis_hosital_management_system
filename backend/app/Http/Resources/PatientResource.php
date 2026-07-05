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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'gender' => $this->gender,
            'date_of_birth' => $this->date_of_birth,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'immigration_service_number' => $this->immigration_service_number,
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
