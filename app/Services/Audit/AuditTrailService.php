<?php

namespace App\Services\Audit;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditTrailService
{
    public function recordRequest(Request $request, Response $response): void
    {
        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return;
        }

        if ($response->getStatusCode() >= 400) {
            return;
        }

        $route = $request->route();
        $routeName = $route?->getName();

        if ($routeName !== null && (
            str_starts_with($routeName, 'audit-trail.')
            || str_starts_with($routeName, 'notifications.')
            || in_array($routeName, ['login', 'logout'], true)
        )) {
            return;
        }

        [$subjectType, $subjectId] = $this->resolveSubject($request);

        AuditLog::query()->create([
            'actor_user_id' => $request->user()?->id,
            'module' => $this->resolveModule($routeName, $request->path()),
            'action' => $this->resolveAction($routeName, $request),
            'route_name' => $routeName,
            'method' => $request->method(),
            'path' => '/' . ltrim($request->path(), '/'),
            'status_code' => $response->getStatusCode(),
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => $this->sanitizeMetadata($request),
        ]);
    }

    public function recordManual(
        ?User $actor,
        string $module,
        string $action,
        string $path,
        ?Request $request = null,
        ?array $metadata = null,
    ): void {
        AuditLog::query()->create([
            'actor_user_id' => $actor?->id,
            'module' => $module,
            'action' => $action,
            'route_name' => $request?->route()?->getName(),
            'method' => $request?->method() ?? 'SYSTEM',
            'path' => $path,
            'status_code' => 200,
            'subject_type' => $actor ? User::class : null,
            'subject_id' => $actor ? (string) $actor->getKey() : null,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'metadata' => $metadata,
        ]);
    }

    private function resolveModule(?string $routeName, string $path): string
    {
        return match (true) {
            $routeName !== null && str_starts_with($routeName, 'tickets.') => 'Ticket Operations',
            $routeName !== null && str_starts_with($routeName, 'engineer-tasks.') => 'Engineering Tasks',
            $routeName !== null && str_starts_with($routeName, 'inspections.') => 'Inspection Operations',
            $routeName !== null && str_starts_with($routeName, 'inspection-results.') => 'Inspection Results',
            $routeName !== null && str_starts_with($routeName, 'master-data.users.') => 'Master Data - Users',
            $routeName !== null && str_starts_with($routeName, 'master-data.departments.') => 'Master Data - Departments',
            $routeName !== null && str_starts_with($routeName, 'master-data.vendors.') => 'Master Data - Vendors',
            $routeName !== null && str_starts_with($routeName, 'master-data.services.') => 'Master Data - Services',
            $routeName !== null && str_starts_with($routeName, 'master-data.engineer-skills.') => 'Master Data - Engineer Skills',
            $routeName !== null && str_starts_with($routeName, 'master-data.shifts.') => 'Master Data - Shifts',
            $routeName !== null && str_starts_with($routeName, 'master-data.engineer-schedules.') => 'Master Data - Engineer Schedules',
            $routeName !== null && str_starts_with($routeName, 'master-data.asset-') => 'Master Data - Assets',
            $routeName !== null && str_starts_with($routeName, 'master-data.ticket-') => 'Ticket Setup',
            $routeName !== null && str_starts_with($routeName, 'master-data.sla-') => 'SLA Setup',
            $routeName !== null && str_starts_with($routeName, 'master-data.inspection-templates.') => 'Inspection Setup',
            $routeName !== null && str_starts_with($routeName, 'master-data.permissions.') => 'Access Control',
            $routeName !== null && str_starts_with($routeName, 'master-data.role-permissions.') => 'Access Control',
            $routeName !== null && str_starts_with($routeName, 'profile.') => 'Profile',
            str_starts_with($path, 'public/tickets') => 'Public Ticket Intake',
            str_starts_with($path, 'public/inspections') => 'Public Inspection Intake',
            str_starts_with($path, 'login') || str_starts_with($path, 'logout') => 'Authentication',
            default => 'General',
        };
    }

    private function resolveAction(?string $routeName, Request $request): string
    {
        if ($routeName !== null) {
            return str_replace('.', ' / ', $routeName);
        }

        return $request->method() . ' ' . $request->path();
    }

    private function resolveSubject(Request $request): array
    {
        foreach ($request->route()?->parameters() ?? [] as $parameter) {
            if ($parameter instanceof Model) {
                return [$parameter::class, (string) $parameter->getKey()];
            }
        }

        return [null, null];
    }

    private function sanitizeMetadata(Request $request): array
    {
        $sensitive = [
            'password',
            'password_confirmation',
            'current_password',
            'token',
            'remember_token',
        ];

        $input = collect($request->except($sensitive))
            ->map(function ($value, $key) use ($request) {
                if ($request->hasFile($key)) {
                    $files = $request->file($key);
                    $files = is_array($files) ? $files : [$files];

                    return collect($files)
                        ->filter()
                        ->map(fn ($file) => [
                            'name' => $file->getClientOriginalName(),
                            'size' => $file->getSize(),
                        ])
                        ->values()
                        ->all();
                }

                if (is_string($value) && mb_strlen($value) > 300) {
                    return mb_substr($value, 0, 300) . '...';
                }

                return $value;
            })
            ->all();

        return [
            'input' => $input,
        ];
    }
}
