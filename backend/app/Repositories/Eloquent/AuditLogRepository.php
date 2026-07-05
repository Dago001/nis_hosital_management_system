<?php

namespace App\Repositories\Eloquent;

use App\Models\AuditLog;
use App\Repositories\Contracts\AuditLogRepositoryInterface;

class AuditLogRepository extends BaseRepository implements AuditLogRepositoryInterface
{
    public function __construct(AuditLog $model)
    {
        parent::__construct($model);
    }

    public function log(int|string|null $userId, string $action, ?string $auditableType, ?int $auditableId, ?array $payload, ?string $ipAddress, ?string $userAgent): void
    {
        $this->create([
            'user_id' => $userId,
            'action' => $action,
            'auditable_type' => $auditableType,
            'auditable_id' => $auditableId,
            'payload' => $payload,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent
        ]);
    }
}
