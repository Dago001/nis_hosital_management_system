<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Repositories\Contracts\AuditLogRepositoryInterface;
use Symfony\Component\HttpFoundation\Response;

class LogAuditAction
{
    protected AuditLogRepositoryInterface $auditLogRepo;

    public function __construct(AuditLogRepositoryInterface $auditLogRepo)
    {
        $this->auditLogRepo = $auditLogRepo;
    }

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only log mutating requests (POST, PUT, PATCH, DELETE) and successful responses
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE']) && $response->getStatusCode() < 400) {
            $action = strtolower($request->method()) . '_' . str_replace('/', '_', trim($request->path(), '/'));
            
            // Mask password payloads
            $payload = $request->except(['password', 'password_confirmation', 'mfa_secret']);

            $this->auditLogRepo->log(
                userId: Auth::id(),
                action: $action,
                auditableType: null,
                auditableId: null,
                payload: $payload,
                ipAddress: $request->ip(),
                userAgent: $request->userAgent()
            );
        }

        return $response;
    }
}
