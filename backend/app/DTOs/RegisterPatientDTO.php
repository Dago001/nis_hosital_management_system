<?php

namespace App\DTOs;

readonly class RegisterPatientDTO
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $gender,
        public string $dateOfBirth,
        public string $phone,
        public string $address,
        public ?string $email = null,
        public ?string $immigrationServiceNumber = null,
        public ?string $nin = null,
        public ?string $allergies = null,
        public ?string $bloodGroup = null,
        public ?string $genotype = null,
        public ?string $disability = null
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            firstName: $data['first_name'],
            lastName: $data['last_name'],
            gender: $data['gender'],
            dateOfBirth: $data['date_of_birth'],
            phone: $data['phone'],
            address: $data['address'],
            email: $data['email'] ?? null,
            immigrationServiceNumber: $data['immigration_service_number'] ?? null,
            nin: $data['nin'] ?? null,
            allergies: $data['allergies'] ?? null,
            bloodGroup: $data['blood_group'] ?? null,
            genotype: $data['genotype'] ?? null,
            disability: $data['disability'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'gender' => $this->gender,
            'date_of_birth' => $this->dateOfBirth,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'immigration_service_number' => $this->immigrationServiceNumber,
            'nin' => $this->nin,
            'allergies' => $this->allergies,
            'blood_group' => $this->bloodGroup,
            'genotype' => $this->genotype,
            'disability' => $this->disability
        ];
    }
}
