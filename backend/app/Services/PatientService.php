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

    public function generateHospitalCode(): string
    {
        return 'NIS/PAT/' . rand(100000, 999999);
    }

    public function register(RegisterPatientDTO $dto): Patient
    {
        return DB::transaction(function () use ($dto) {
            $data = $dto->toArray();
            
            if (empty($data['immigration_service_number'])) {
                $data['immigration_service_number'] = $this->generateHospitalCode();
            }

            // Mocking barcode and QR code data for NIS HMS Patient Cards
            $data['qr_code_data'] = 'NISHMS-PAT-' . time() . '-' . rand(1000, 9999);
            $data['barcode_data'] = 'NIS' . rand(100000, 999999);

            $patient = $this->patientRepo->create($data);

            // Generate registration fee invoice for new Cash/Civilian (non-NHIS) patient
            $isNhis = !empty($patient->sponsor_service_number) || (!empty($dto->immigration_service_number) && !str_contains($dto->immigration_service_number, '/PAT/'));
            if (!$isNhis) {
                $regFee = \App\Models\ServiceTariff::priceFor('REG_NEW', 5000.00);
                $invoice = \App\Models\Invoice::create([
                    'patient_id' => $patient->id,
                    'visit_id' => null,
                    'total_amount' => $regFee,
                    'discount_amount' => 0.00,
                    'paid_amount' => 0.00,
                    'status' => 'unpaid'
                ]);

                \App\Models\InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'item_name' => 'New Patient Registration Fee (Civilian/Cash)',
                    'quantity' => 1,
                    'unit_price' => $regFee,
                    'total_price' => $regFee
                ]);
            }

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
