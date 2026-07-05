<?php

namespace App\Repositories\Eloquent;

use App\Models\Patient;
use App\Repositories\Contracts\PatientRepositoryInterface;

class PatientRepository extends BaseRepository implements PatientRepositoryInterface
{
    public function __construct(Patient $model)
    {
        parent::__construct($model);
    }

    public function findByServiceNumber(string $serviceNumber): ?Patient
    {
        return $this->model->where('immigration_service_number', $serviceNumber)->first();
    }

    public function findByNin(string $nin): ?Patient
    {
        return $this->model->where('nin', $nin)->first();
    }
}
