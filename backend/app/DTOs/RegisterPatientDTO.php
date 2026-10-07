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
        public ?string $middleName = null,
        public ?string $maritalStatus = null,
        public ?string $occupation = null,
        public ?string $religion = null,
        public ?string $placeOfBirth = null,
        public ?string $tribe = null,
        public ?string $state = null,
        public ?string $lga = null,
        public ?string $city = null,
        public ?string $nextOfKinName = null,
        public ?string $nextOfKinRelationship = null,
        public ?string $nextOfKinAddress = null,
        public ?string $email = null,
        public ?string $immigrationServiceNumber = null,
        public ?string $sponsorServiceNumber = null,
        public ?string $relationshipToSponsor = null,
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
            middleName: $data['middle_name'] ?? null,
            maritalStatus: $data['marital_status'] ?? null,
            occupation: $data['occupation'] ?? null,
            religion: $data['religion'] ?? null,
            placeOfBirth: $data['place_of_birth'] ?? null,
            tribe: $data['tribe'] ?? null,
            state: $data['state'] ?? null,
            lga: $data['lga'] ?? null,
            city: $data['city'] ?? null,
            nextOfKinName: $data['next_of_kin_name'] ?? null,
            nextOfKinRelationship: $data['next_of_kin_relationship'] ?? null,
            nextOfKinAddress: $data['next_of_kin_address'] ?? null,
            email: $data['email'] ?? null,
            immigrationServiceNumber: $data['immigration_service_number'] ?? null,
            sponsorServiceNumber: $data['sponsor_service_number'] ?? null,
            relationshipToSponsor: $data['relationship_to_sponsor'] ?? null,
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
            'middle_name' => $this->middleName,
            'last_name' => $this->lastName,
            'gender' => $this->gender,
            'marital_status' => $this->maritalStatus,
            'occupation' => $this->occupation,
            'religion' => $this->religion,
            'place_of_birth' => $this->placeOfBirth,
            'tribe' => $this->tribe,
            'date_of_birth' => $this->dateOfBirth,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'state' => $this->state,
            'lga' => $this->lga,
            'city' => $this->city,
            'next_of_kin_name' => $this->nextOfKinName,
            'next_of_kin_relationship' => $this->nextOfKinRelationship,
            'next_of_kin_address' => $this->nextOfKinAddress,
            'immigration_service_number' => $this->immigrationServiceNumber,
            'sponsor_service_number' => $this->sponsorServiceNumber,
            'relationship_to_sponsor' => $this->relationshipToSponsor,
            'nin' => $this->nin,
            'allergies' => $this->allergies,
            'blood_group' => $this->bloodGroup,
            'genotype' => $this->genotype,
            'disability' => $this->disability
        ];
    }
}
