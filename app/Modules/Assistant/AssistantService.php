<?php

namespace App\Modules\Assistant;

use App\Models\Inspection;
use App\Models\Ticket;
use App\Models\User;
use App\Modules\Dashboards\Operations\OperationsDashboardService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class AssistantService
{
    public function __construct(
        private readonly OperationsDashboardService $operationsDashboardService,
    ) {
    }

    public function respond(User $user, string $message): array
    {
        $cleanMessage = trim(preg_replace('/\s+/', ' ', $message) ?? '');
        $normalized = Str::lower($cleanMessage);

        if ($cleanMessage === '') {
            return $this->helpResponse($user, 'Saya siap bantu membaca data CXTS sesuai role Anda.');
        }

        if ($this->isHelpIntent($normalized)) {
            return $this->helpResponse($user);
        }

        $ticketNumber = $this->extractTicketNumber($cleanMessage);
        if ($ticketNumber !== null) {
            return $this->ticketLookupResponse($user, $ticketNumber);
        }

        if ($this->containsAny($normalized, ['mttr', 'mean time to repair'])) {
            return $this->mttrResponse($user);
        }

        if ($this->containsAny($normalized, ['overdue', 'unassigned', 'sla', 'backlog'])) {
            return $this->operationalSummaryResponse($user);
        }

        if ($this->containsAny($normalized, ['ticket saya', 'tiket saya', 'my ticket', 'ticket milik saya'])) {
            return $this->myTicketSummaryResponse($user);
        }

        if ($this->containsAny($normalized, ['task saya', 'tugas saya', 'my task', 'pekerjaan saya', 'workload saya'])) {
            return $this->myTaskSummaryResponse($user);
        }

        if ($this->containsAny($normalized, ['inspeksi saya', 'inspection saya', 'my inspection', 'hasil inspeksi'])) {
            return $this->myInspectionSummaryResponse($user);
        }

        if ($this->containsAny($normalized, ['modul', 'menu', 'fitur', 'bisa bantu apa', 'apa yang bisa kamu bantu'])) {
            return $this->helpResponse($user);
        }

        return [
            'message' => "Saya hanya menjawab hal yang terkait CXTS, seperti status ticket, ringkasan ticket/task/inspection, MTTR, SLA, dan panduan modul sesuai role Anda.\n\nCoba pertanyaan seperti: " . implode(' | ', $this->suggestionsForRole($user)),
            'suggestions' => $this->suggestionsForRole($user),
        ];
    }

    private function helpResponse(User $user, ?string $intro = null): array
    {
        $roleLabel = str($user->role)->replace('_', ' ')->title()->toString();
        $lines = array_filter([
            $intro,
            "Role aktif Anda: {$roleLabel}.",
            'Saya hanya menjawab dalam lingkup aplikasi CXTS dan data yang memang boleh Anda akses.',
            'Contoh pertanyaan:',
            ...array_map(fn (string $suggestion) => '- ' . $suggestion, $this->suggestionsForRole($user)),
        ]);

        return [
            'message' => implode("\n", $lines),
            'suggestions' => $this->suggestionsForRole($user),
        ];
    }

    private function mttrResponse(User $user): array
    {
        if (! $user->hasPermission('dashboard.view_ops')) {
            return $this->forbiddenMetricResponse($user, 'MTTR hanya tersedia untuk role operasional yang punya akses dashboard ops.');
        }

        $overview = $this->operationsDashboardService->overview($user, []);
        $mttrMinutes = $overview['summary']['mttr_minutes'] ?? null;
        $ticketCount = (int) ($overview['summary']['mttr_ticket_count'] ?? 0);

        if ($mttrMinutes === null || $ticketCount === 0) {
            return [
                'message' => 'Belum ada cukup data ticket started/completed pada periode aktif untuk menghitung MTTR.',
                'suggestions' => $this->suggestionsForRole($user),
            ];
        }

        return [
            'message' => sprintf(
                "MTTR pada periode aktif saat ini adalah %s menit, dihitung dari %s ticket yang punya waktu mulai kerja dan selesai.\n\nDefinisi yang dipakai: rata-rata durasi dari `started_at` ke `resolved_at` atau `completed_at`.",
                number_format((float) $mttrMinutes, 2),
                number_format($ticketCount)
            ),
            'suggestions' => ['Berapa ticket overdue saat ini?', 'Berapa ticket unassigned saat ini?', 'Modul apa yang bisa saya akses?'],
        ];
    }

    private function operationalSummaryResponse(User $user): array
    {
        if (! $user->hasPermission('dashboard.view_ops')) {
            return $this->forbiddenMetricResponse($user, 'Ringkasan SLA, overdue, dan unassigned queue hanya tersedia untuk role operasional.');
        }

        $overview = $this->operationsDashboardService->overview($user, []);
        $summary = $overview['summary'] ?? [];
        $sla = $overview['sla'] ?? [];

        return [
            'message' => sprintf(
                "Ringkasan operasional saat ini:\n- Ticket overdue resolution: %s\n- Ticket unassigned: %s\n- Response SLA compliance: %s%%\n- Resolution SLA compliance: %s%%\n- MTTR: %s menit",
                number_format((int) ($summary['overdue_resolution_tickets'] ?? 0)),
                number_format((int) ($summary['unassigned_tickets'] ?? 0)),
                number_format((float) ($sla['response']['compliance_rate'] ?? 0), 2),
                number_format((float) ($sla['resolution']['compliance_rate'] ?? 0), 2),
                number_format((float) ($summary['mttr_minutes'] ?? 0), 2)
            ),
            'suggestions' => ['Berapa MTTR saat ini?', 'Status ticket TCK-DASH-0001', 'Modul apa yang bisa saya akses?'],
        ];
    }

    private function myTicketSummaryResponse(User $user): array
    {
        $query = $this->ticketScopedQuery($user);
        $total = (clone $query)->count();
        $open = (clone $query)->whereNull('tickets.completed_at')->whereNull('tickets.closed_at')->count();
        $completed = (clone $query)->whereNotNull('tickets.completed_at')->count();
        $latest = (clone $query)->latest('tickets.created_at')->first(['tickets.ticket_number', 'tickets.title']);

        return [
            'message' => sprintf(
                "Ringkasan ticket yang bisa Anda akses:\n- Total ticket: %s\n- Masih terbuka: %s\n- Sudah completed: %s%s",
                number_format($total),
                number_format($open),
                number_format($completed),
                $latest !== null ? "\n- Ticket terbaru: {$latest->ticket_number} - {$latest->title}" : ''
            ),
            'suggestions' => ['Status ticket saya terbaru', 'Modul apa yang bisa saya akses?', 'Status ticket TCK-...'],
        ];
    }

    private function myTaskSummaryResponse(User $user): array
    {
        if (! $user->hasPermission('engineer_task.view_assigned')) {
            return $this->forbiddenMetricResponse($user, 'Ringkasan task hanya tersedia untuk engineer yang punya task assignment.');
        }

        $query = $this->ticketScopedQuery($user);
        $assigned = (clone $query)->count();
        $inProgress = (clone $query)->whereNotNull('tickets.started_at')->whereNull('tickets.completed_at')->count();
        $completed = (clone $query)->whereNotNull('tickets.completed_at')->count();
        $latest = (clone $query)->latest('tickets.updated_at')->first(['tickets.ticket_number', 'tickets.title']);

        return [
            'message' => sprintf(
                "Ringkasan task Anda:\n- Total assigned task: %s\n- Sedang dikerjakan: %s\n- Sudah completed: %s%s",
                number_format($assigned),
                number_format($inProgress),
                number_format($completed),
                $latest !== null ? "\n- Task terbaru: {$latest->ticket_number} - {$latest->title}" : ''
            ),
            'suggestions' => ['Task saya', 'Status ticket TCK-...', 'Modul apa yang bisa saya akses?'],
        ];
    }

    private function myInspectionSummaryResponse(User $user): array
    {
        if (! $user->hasAnyPermission(['inspection_task.view_assigned', 'inspection_result.view_assigned']) && $user->role !== 'engineer') {
            return $this->forbiddenMetricResponse($user, 'Ringkasan inspection hanya tersedia untuk role yang menangani inspection.');
        }

        $query = $this->inspectionScopedQuery($user);
        $total = (clone $query)->count();
        $draft = (clone $query)->where('status', Inspection::STATUS_DRAFT)->count();
        $inProgress = (clone $query)->where('status', Inspection::STATUS_IN_PROGRESS)->count();
        $submitted = (clone $query)->where('status', Inspection::STATUS_SUBMITTED)->count();
        $abnormal = (clone $query)->where('final_result', Inspection::FINAL_RESULT_ABNORMAL)->count();

        return [
            'message' => sprintf(
                "Ringkasan inspection yang bisa Anda akses:\n- Total inspection: %s\n- Draft: %s\n- In progress: %s\n- Submitted: %s\n- Abnormal result: %s",
                number_format($total),
                number_format($draft),
                number_format($inProgress),
                number_format($submitted),
                number_format($abnormal)
            ),
            'suggestions' => ['Inspeksi saya', 'Modul apa yang bisa saya akses?', 'Status ticket TCK-...'],
        ];
    }

    private function ticketLookupResponse(User $user, string $ticketNumber): array
    {
        $ticket = $this->ticketScopedQuery($user)
            ->with(['status:id,name,code', 'priority:id,name', 'assignedEngineer:id,name'])
            ->where('tickets.ticket_number', $ticketNumber)
            ->first();

        if ($ticket === null) {
            return [
                'message' => "Saya tidak menemukan ticket `{$ticketNumber}` dalam scope akses Anda.",
                'suggestions' => $this->suggestionsForRole($user),
            ];
        }

        $statusName = $ticket->status?->name ?? 'Unknown';
        $priorityName = $ticket->priority?->name ?? 'No Priority';
        $assignedEngineerName = $ticket->assignedEngineer?->name ?? 'Belum di-assign';
        $detailPath = $this->ticketDetailPathForUser($user, $ticket);

        return [
            'message' => sprintf(
                "Status ticket %s:\n- Judul: %s\n- Status: %s\n- Prioritas: %s\n- Assigned engineer: %s%s",
                $ticket->ticket_number,
                $ticket->title,
                $statusName,
                $priorityName,
                $assignedEngineerName,
                $detailPath !== null ? "\n- Detail: {$detailPath}" : ''
            ),
            'suggestions' => ['Ticket saya', 'Task saya', 'Modul apa yang bisa saya akses?'],
        ];
    }

    private function forbiddenMetricResponse(User $user, string $message): array
    {
        return [
            'message' => $message,
            'suggestions' => $this->suggestionsForRole($user),
        ];
    }

    private function suggestionsForRole(User $user): array
    {
        return match ($user->role) {
            'super_admin', 'operational_admin', 'supervisor' => [
                'Berapa MTTR saat ini?',
                'Berapa ticket overdue saat ini?',
                'Status ticket TCK-...',
            ],
            'engineer' => [
                'Task saya',
                'Status ticket TCK-...',
                'Modul apa yang bisa saya akses?',
            ],
            'inspection_officer' => [
                'Inspeksi saya',
                'Status ticket TCK-...',
                'Modul apa yang bisa saya akses?',
            ],
            default => [
                'Tiket saya',
                'Status ticket TCK-...',
                'Modul apa yang bisa saya akses?',
            ],
        };
    }

    private function isHelpIntent(string $normalized): bool
    {
        return $this->containsAny($normalized, ['help', 'bantuan', 'halo', 'hai', 'menu', 'fitur', 'apa yang bisa kamu bantu']);
    }

    private function containsAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (str_contains($haystack, Str::lower((string) $needle))) {
                return true;
            }
        }

        return false;
    }

    private function extractTicketNumber(string $message): ?string
    {
        preg_match('/\bTCK-[A-Z0-9-]+\b/i', $message, $matches);

        if (! isset($matches[0])) {
            return null;
        }

        return strtoupper($matches[0]);
    }

    private function ticketScopedQuery(User $user): Builder
    {
        $query = Ticket::query();

        if ($user->hasPermission('ticket.view_all')) {
            return $query;
        }

        $query->where(function (Builder $scopedQuery) use ($user): void {
            $hasScope = false;

            if ($user->hasAnyPermission(['ticket.approve_all', 'ticket.approve_department'])) {
                if ($user->id !== null) {
                    $scopedQuery->orWhere('tickets.expected_approver_id', $user->id);
                    $hasScope = true;
                }

                if (filled($user->role)) {
                    $scopedQuery->orWhere('tickets.expected_approver_role_code', $user->role);
                    $hasScope = true;
                }
            }

            if ($user->hasPermission('ticket.view_department') && $user->department_id !== null) {
                $scopedQuery->orWhere('tickets.requester_department_id', $user->department_id);
                $hasScope = true;
            }

            if ($user->hasPermission('ticket.view_assigned')) {
                $scopedQuery->orWhere('tickets.assigned_engineer_id', $user->id)
                    ->orWhereHas('assignedEngineers', fn (Builder $engineerQuery) => $engineerQuery->whereKey($user->id));
                $hasScope = true;
            }

            if ($user->hasPermission('ticket.view_own')) {
                $scopedQuery->orWhere('tickets.requester_id', $user->id);
                $hasScope = true;
            }

            if (! $hasScope) {
                $scopedQuery->whereRaw('1 = 0');
            }
        });

        return $query;
    }

    private function inspectionScopedQuery(User $user): Builder
    {
        $query = Inspection::query();

        if ($user->hasPermission('dashboard.view_ops')) {
            return $query;
        }

        if (in_array($user->role, ['inspection_officer', 'engineer'], true)) {
            $query->where('inspection_officer_id', $user->id);
            return $query;
        }

        $query->whereRaw('1 = 0');

        return $query;
    }

    private function ticketDetailPathForUser(User $user, Ticket $ticket): ?string
    {
        if ($user->hasPermission('engineer_task.view_assigned')) {
            return route('engineer-tasks.show', $ticket, false);
        }

        if ($user->hasAnyPermission(['ticket.view_all', 'ticket.view_department', 'ticket.view_own'])) {
            return route('tickets.show', $ticket, false);
        }

        return null;
    }
}
