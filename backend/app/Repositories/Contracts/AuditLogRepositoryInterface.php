<?php

namespace App\Repositories\Contracts;

interface AuditLogRepositoryInterface extends BaseRepositoryInterface
{
    public function log(int|string|null $userId, string $action, ?string $auditableType, ?int $auditableId, ?array $payload, ?string $ipAddress, ?string $userAgent): void;
}
