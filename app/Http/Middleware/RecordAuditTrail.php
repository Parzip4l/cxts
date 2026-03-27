<?php

namespace App\Http\Middleware;

use App\Services\Audit\AuditTrailService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RecordAuditTrail
{
    public function __construct(private readonly AuditTrailService $auditTrailService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $this->auditTrailService->recordRequest($request, $response);

        return $response;
    }
}
