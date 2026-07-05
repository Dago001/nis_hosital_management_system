<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->avatar ? asset('storage/' . $this->avatar) : null,
            'mfa_enabled' => $this->mfa_enabled,
            'status' => $this->status,
            'roles' => $this->roles->map(fn($role) => [
                'name' => $role->name,
                'display_name' => $role->display_name
            ]),
            'staff' => $this->staff ? [
                'id' => $this->staff->id,
                'first_name' => $this->staff->first_name,
                'last_name' => $this->staff->last_name,
                'phone' => $this->staff->phone,
                'specialization' => $this->staff->specialization,
                'rank' => $this->staff->rank,
                'service_number' => $this->staff->service_number,
                'department_id' => $this->staff->department_id,
                'department' => $this->staff->department ? [
                    'id' => $this->staff->department->id,
                    'name' => $this->staff->department->name,
                    'code' => $this->staff->department->code,
                ] : null
            ] : null,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
