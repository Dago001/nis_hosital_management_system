<?php

namespace App\Services;

use App\DTOs\RegisterPatientDTO;
use App\Models\Patient;
use App\Repositories\Contracts\PatientRepositoryInterface;
use App\Repositories\Contracts\AuditLogRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PatientService
{
    protected PatientRepositoryInterface $patientRepo;
    protected AuditLogRepositoryInterface $auditLogRepo;

    public function __construct(
        PatientRepositoryInterface $patientRepo,
        AuditLogRepositoryInterface $auditLogRepo
    ) {
        $this->patientRepo = $patientRepo;
        $this->auditLogRepo = $auditLogRepo;
    }

    public function all(): Collection
    {
        return $this->patientRepo->all();
    }

    public function find(int $id): ?Patient
    {
        return $this->patientRepo->find($id);
    }

    public function register(RegisterPatientDTO $dto): Patient
    {
        return DB::transaction(function () use ($dto) {
            $data = $dto->toArray();
            
            // Mocking barcode and QR code data for NIS HMS Patient Cards
            $data['qr_code_data'] = 'NISHMS-PAT-' . time() . '-' . rand(1000, 9999);
            $data['barcode_data'] = 'NIS' . rand(100000, 999999);

            $patient = $this->patientRepo->create($data);

            // Log this action to the Audit Trail
            $this->auditLogRepo->log(
                userId: Auth::id(),
                action: 'create_patient',
                auditableType: Patient::class,
                auditableId: $patient->id,
                payload: $dto->toArray(),
                ipAddress: request()->ip(),
                userAgent: request()->userAgent()
            );

            return $patient;
        });
    }
}
