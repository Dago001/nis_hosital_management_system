<?php

namespace App\Repositories\Contracts;

use App\Models\Patient;

interface PatientRepositoryInterface extends BaseRepositoryInterface
{
    public function findByServiceNumber(string $serviceNumber): ?Patient;
    public function findByNin(string $nin): ?Patient;
}
