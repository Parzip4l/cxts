<?php

namespace App\Modules\Assistant;

use Carbon\CarbonImmutable;
use App\Models\Asset;
use App\Models\AssetLocation;
use App\Models\AssetStatus;
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

        if ($this->isEngineerAnalyticsIntent($normalized)) {
            return $this->engineerAnalyticsResponse($user, $normalized);
        }

        if ($this->isTicketMetricIntent($normalized)) {
            return $this->ticketMetricResponse($user, $normalized);
        }

        if ($this->isAssetInsightIntent($normalized)) {
            return $this->assetInsightResponse($user, $normalized);
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
            'message' => "Saya hanya menjawab hal yang terkait CXTS, seperti status ticket, ringkasan ticket/task/inspection, insight engineer, SLA, asset, dan panduan modul sesuai role Anda.\n\nCoba pertanyaan seperti: " . implode(' | ', $this->suggestionsForRole($user)),
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
                'message' => 'Belum ada cukup data final cycle pada periode aktif untuk menghitung MTTR final cycle.',
                'suggestions' => $this->suggestionsForRole($user),
            ];
        }

        $reopenRate = (float) ($overview['summary']['reopen_rate'] ?? 0);
        $reopenedTicketCount = (int) ($overview['summary']['reopened_ticket_count'] ?? 0);

        return [
            'message' => sprintf(
                "MTTR final cycle pada periode aktif saat ini adalah %s menit, dihitung dari %s ticket yang memiliki siklus kerja final lengkap.\n- Reopen rate companion: %s%% (%s ticket pernah dibuka kembali)\n\nDefinisi yang dipakai: rata-rata durasi dari `work_started` terakhir ke `work_completed` terakhir pada siklus final ticket. Ticket yang masih terbuka setelah reopen tidak ikut masuk sample final cycle.",
                number_format((float) $mttrMinutes, 2),
                number_format($ticketCount),
                number_format($reopenRate, 2),
                number_format($reopenedTicketCount)
            ),
            'suggestions' => ['Berapa ticket overdue saat ini?', 'Berapa ticket unassigned saat ini?', 'Modul apa yang bisa saya akses?'],
        ];
    }

    private function ticketMetricResponse(User $user, string $normalized): array
    {
        [$from, $to, $label] = $this->resolveAssistantPeriod($normalized);
        $query = $this->ticketScopedQuery($user);

        if ($this->containsAny($normalized, ['rata rata', 'rata-rata', 'average', 'avg'])) {
            $createdByDay = (clone $query)
                ->whereBetween('tickets.created_at', [$from, $to])
                ->selectRaw('DATE(tickets.created_at) as day, COUNT(*) as total')
                ->groupBy('day')
                ->pluck('total', 'day');

            $daysInRange = max(1, $from->diffInDays($to) + 1);
            $averageCreated = round(array_sum($createdByDay->all()) / $daysInRange, 2);

            return [
                'message' => sprintf(
                    "Rata-rata ticket masuk %s adalah %s ticket per hari.\n- Total ticket created: %s\n- Jumlah hari pada periode: %s",
                    $label,
                    number_format($averageCreated, 2),
                    number_format((int) array_sum($createdByDay->all())),
                    number_format($daysInRange)
                ),
                'suggestions' => ['Berapa ticket closed hari ini?', 'Berapa MTTR saat ini?', 'Modul apa yang bisa saya akses?'],
            ];
        }

        if ($this->containsAny($normalized, ['closed', 'ditutup'])) {
            $closedCount = (clone $query)
                ->whereNotNull('tickets.closed_at')
                ->whereBetween('tickets.closed_at', [$from, $to])
                ->count();

            return [
                'message' => sprintf('Jumlah ticket closed %s adalah %s ticket.', $label, number_format($closedCount)),
                'suggestions' => ['Berapa ticket completed hari ini?', 'Berapa ticket overdue saat ini?', 'Modul apa yang bisa saya akses?'],
            ];
        }

        if ($this->containsAny($normalized, ['completed', 'selesai', 'resolved'])) {
            $completedCount = (clone $query)
                ->whereNotNull('tickets.completed_at')
                ->whereBetween('tickets.completed_at', [$from, $to])
                ->count();

            return [
                'message' => sprintf('Jumlah ticket completed %s adalah %s ticket.', $label, number_format($completedCount)),
                'suggestions' => ['Berapa ticket closed hari ini?', 'Berapa MTTR saat ini?', 'Modul apa yang bisa saya akses?'],
            ];
        }

        if ($this->containsAny($normalized, ['open', 'terbuka', 'ongoing', 'aktif'])) {
            $openCount = (clone $query)
                ->whereNull('tickets.completed_at')
                ->whereNull('tickets.closed_at')
                ->whereBetween('tickets.created_at', [$from, $to])
                ->count();

            return [
                'message' => sprintf('Jumlah ticket open %s adalah %s ticket.', $label, number_format($openCount)),
                'suggestions' => ['Berapa ticket closed hari ini?', 'Berapa ticket overdue saat ini?', 'Modul apa yang bisa saya akses?'],
            ];
        }

        $createdCount = (clone $query)
            ->whereBetween('tickets.created_at', [$from, $to])
            ->count();

        return [
            'message' => sprintf('Jumlah ticket yang dibuat %s adalah %s ticket.', $label, number_format($createdCount)),
            'suggestions' => ['Berapa ticket closed hari ini?', 'Berapa rata-rata tiket bulan ini?', 'Modul apa yang bisa saya akses?'],
        ];
    }

    private function engineerAnalyticsResponse(User $user, string $normalized): array
    {
        if (! $user->hasPermission('dashboard.view_ops')) {
            return $this->forbiddenMetricResponse($user, 'Insight engineer hanya tersedia untuk role operasional yang punya akses dashboard ops.');
        }

        [$from, $to, $label] = $this->resolveAssistantPeriod($normalized, 'month');
        $effectiveness = $this->operationsDashboardService->engineerEffectiveness($user, [
            'date_from' => $from->toDateString(),
            'date_to' => $to->toDateString(),
        ]);

        $engineers = collect($effectiveness['engineers'] ?? []);

        if ($engineers->isEmpty()) {
            return [
                'message' => sprintf('Belum ada data engineer assignment %s untuk menghitung insight engineer.', $label),
                'suggestions' => $this->suggestionsForRole($user),
            ];
        }

        if ($this->containsAny($normalized, ['paling banyak close', 'close ticket terbanyak', 'closed ticket terbanyak', 'paling banyak selesai', 'paling banyak completed'])) {
            $topEngineer = $engineers
                ->sort(fn (array $left, array $right) => [
                    (float) ($right['completed_tickets'] ?? 0),
                    (float) ($right['completion_rate'] ?? 0),
                ] <=> [
                    (float) ($left['completed_tickets'] ?? 0),
                    (float) ($left['completion_rate'] ?? 0),
                ])
                ->first();

            return [
                'message' => sprintf(
                    "Engineer dengan ticket closed/completed terbanyak %s adalah %s.\n- Completed tickets: %s\n- Assigned tickets: %s\n- Completion rate: %s%%\n- Open tickets: %s",
                    $label,
                    $topEngineer['engineer_name'] ?? 'Unknown',
                    number_format((float) ($topEngineer['completed_tickets'] ?? 0), 2),
                    number_format((float) ($topEngineer['assigned_tickets'] ?? 0), 2),
                    number_format((float) ($topEngineer['completion_rate'] ?? 0), 2),
                    number_format((float) ($topEngineer['open_tickets'] ?? 0), 2)
                ),
                'suggestions' => ['Siapa engineer workload tertinggi bulan ini?', 'Siapa engineer dengan SLA terbaik bulan ini?', 'Berapa MTTR saat ini?'],
            ];
        }

        if ($this->containsAny($normalized, ['workload tertinggi', 'beban kerja tertinggi', 'paling sibuk', 'workload paling tinggi'])) {
            $topEngineer = $engineers
                ->sort(fn (array $left, array $right) => [
                    (float) ($right['open_tickets'] ?? 0),
                    (float) ($right['assigned_tickets'] ?? 0),
                    (int) ($right['total_worklog_minutes'] ?? 0),
                ] <=> [
                    (float) ($left['open_tickets'] ?? 0),
                    (float) ($left['assigned_tickets'] ?? 0),
                    (int) ($left['total_worklog_minutes'] ?? 0),
                ])
                ->first();

            return [
                'message' => sprintf(
                    "Engineer dengan workload tertinggi %s adalah %s.\n- Open tickets: %s\n- Assigned tickets: %s\n- Completed tickets: %s\n- Total worklog: %s menit",
                    $label,
                    $topEngineer['engineer_name'] ?? 'Unknown',
                    number_format((float) ($topEngineer['open_tickets'] ?? 0), 2),
                    number_format((float) ($topEngineer['assigned_tickets'] ?? 0), 2),
                    number_format((float) ($topEngineer['completed_tickets'] ?? 0), 2),
                    number_format((int) ($topEngineer['total_worklog_minutes'] ?? 0))
                ),
                'suggestions' => ['Siapa engineer paling efektif bulan ini?', 'Siapa engineer paling banyak close ticket bulan ini?', 'Berapa ticket overdue saat ini?'],
            ];
        }

        if ($this->containsAny($normalized, ['sla terbaik', 'sla paling baik', 'compliance terbaik', 'sla paling bagus'])) {
            $topEngineer = $engineers
                ->sort(fn (array $left, array $right) => [
                    (float) ($right['resolution_compliance_rate'] ?? 0),
                    (float) ($right['response_compliance_rate'] ?? 0),
                    (float) ($right['effectiveness_score'] ?? 0),
                ] <=> [
                    (float) ($left['resolution_compliance_rate'] ?? 0),
                    (float) ($left['response_compliance_rate'] ?? 0),
                    (float) ($left['effectiveness_score'] ?? 0),
                ])
                ->first();

            return [
                'message' => sprintf(
                    "Engineer dengan SLA terbaik %s adalah %s.\n- Resolution SLA compliance: %s%%\n- Response SLA compliance: %s%%\n- Effectiveness score: %s\n- Assigned tickets: %s",
                    $label,
                    $topEngineer['engineer_name'] ?? 'Unknown',
                    number_format((float) ($topEngineer['resolution_compliance_rate'] ?? 0), 2),
                    number_format((float) ($topEngineer['response_compliance_rate'] ?? 0), 2),
                    number_format((float) ($topEngineer['effectiveness_score'] ?? 0), 2),
                    number_format((float) ($topEngineer['assigned_tickets'] ?? 0), 2)
                ),
                'suggestions' => ['Siapa engineer paling efektif bulan ini?', 'Siapa engineer workload tertinggi bulan ini?', 'Berapa MTTR saat ini?'],
            ];
        }

        $topEngineer = $engineers->first();

        return [
            'message' => sprintf(
                "Engineer paling efektif %s adalah %s.\n- Effectiveness score: %s\n- Assigned tickets: %s\n- Completed tickets: %s\n- Completion rate: %s%%\n- Response SLA compliance: %s%%\n- Resolution SLA compliance: %s%%\n- Total worklog: %s menit",
                $label,
                $topEngineer['engineer_name'] ?? 'Unknown',
                number_format((float) ($topEngineer['effectiveness_score'] ?? 0), 2),
                number_format((float) ($topEngineer['assigned_tickets'] ?? 0), 2),
                number_format((float) ($topEngineer['completed_tickets'] ?? 0), 2),
                number_format((float) ($topEngineer['completion_rate'] ?? 0), 2),
                number_format((float) ($topEngineer['response_compliance_rate'] ?? 0), 2),
                number_format((float) ($topEngineer['resolution_compliance_rate'] ?? 0), 2),
                number_format((int) ($topEngineer['total_worklog_minutes'] ?? 0))
            ),
            'suggestions' => ['Siapa engineer paling banyak close ticket bulan ini?', 'Siapa engineer workload tertinggi bulan ini?', 'Siapa engineer dengan SLA terbaik bulan ini?'],
        ];
    }

    private function assetInsightResponse(User $user, string $normalized): array
    {
        if (! $user->hasPermission('asset.manage')) {
            return $this->forbiddenMetricResponse($user, 'Insight asset hanya tersedia untuk role yang punya akses data asset.');
        }

        if ($this->containsAny($normalized, ['asset status', 'status asset']) && $this->containsAny($normalized, ['berapa', 'jumlah', 'total'])) {
            return [
                'message' => sprintf('Jumlah master asset status aktif saat ini adalah %s status.', number_format(AssetStatus::query()->where('is_active', true)->count())),
                'suggestions' => ['Status asset apa yang paling banyak dipakai?', 'Berapa total asset location?', 'Berapa total asset aktif?'],
            ];
        }

        if ($this->containsAny($normalized, ['asset location', 'lokasi asset']) && $this->containsAny($normalized, ['berapa', 'jumlah', 'total'])) {
            return [
                'message' => sprintf('Jumlah master asset location aktif saat ini adalah %s lokasi.', number_format(AssetLocation::query()->where('is_active', true)->count())),
                'suggestions' => ['Lokasi asset mana yang paling banyak?', 'Berapa total asset aktif?', 'Status asset apa yang paling banyak dipakai?'],
            ];
        }

        if ($this->containsAny($normalized, ['status asset', 'asset status', 'status paling banyak', 'status terbanyak'])) {
            $topStatus = AssetStatus::query()
                ->withCount('assets')
                ->orderByDesc('assets_count')
                ->orderBy('name')
                ->first();

            if ($topStatus === null) {
                return [
                    'message' => 'Belum ada data asset status untuk diringkas.',
                    'suggestions' => $this->suggestionsForRole($user),
                ];
            }

            return [
                'message' => sprintf(
                    "Status asset yang paling banyak dipakai saat ini adalah %s.\n- Total asset pada status ini: %s\n- Status operasional: %s",
                    $topStatus->name,
                    number_format((int) $topStatus->assets_count),
                    $topStatus->is_operational ? 'Ya' : 'Tidak'
                ),
                'suggestions' => ['Berapa total asset aktif?', 'Lokasi asset mana yang paling banyak?', 'Berapa total asset status?'],
            ];
        }

        if ($this->containsAny($normalized, ['asset location', 'lokasi asset', 'lokasi terbanyak', 'location terbanyak', 'location paling banyak'])) {
            $topLocation = AssetLocation::query()
                ->withCount('assets')
                ->orderByDesc('assets_count')
                ->orderBy('name')
                ->first();

            if ($topLocation === null) {
                return [
                    'message' => 'Belum ada data asset location untuk diringkas.',
                    'suggestions' => $this->suggestionsForRole($user),
                ];
            }

            return [
                'message' => sprintf(
                    "Lokasi asset dengan jumlah asset terbanyak saat ini adalah %s.\n- Total asset: %s",
                    $topLocation->name,
                    number_format((int) $topLocation->assets_count)
                ),
                'suggestions' => ['Berapa total asset aktif?', 'Status asset apa yang paling banyak dipakai?', 'Berapa total asset location?'],
            ];
        }

        $totalAssets = Asset::query()->count();
        $activeAssets = Asset::query()->where('is_active', true)->count();
        $operationalAssets = Asset::query()
            ->whereHas('status', fn (Builder $statusQuery) => $statusQuery->where('is_operational', true))
            ->count();

        return [
            'message' => sprintf(
                "Ringkasan asset saat ini:\n- Total asset: %s\n- Asset aktif: %s\n- Asset dengan status operasional: %s",
                number_format($totalAssets),
                number_format($activeAssets),
                number_format($operationalAssets)
            ),
            'suggestions' => ['Status asset apa yang paling banyak dipakai?', 'Lokasi asset mana yang paling banyak?', 'Berapa total asset status?'],
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
                "Ringkasan operasional saat ini:\n- Ticket overdue resolution: %s\n- Ticket unassigned: %s\n- Response SLA compliance: %s%%\n- Resolution SLA compliance: %s%%\n- MTTR final cycle: %s menit\n- Reopen rate: %s%%",
                number_format((int) ($summary['overdue_resolution_tickets'] ?? 0)),
                number_format((int) ($summary['unassigned_tickets'] ?? 0)),
                number_format((float) ($sla['response']['compliance_rate'] ?? 0), 2),
                number_format((float) ($sla['resolution']['compliance_rate'] ?? 0), 2),
                number_format((float) ($summary['mttr_minutes'] ?? 0), 2),
                number_format((float) ($summary['reopen_rate'] ?? 0), 2)
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
            'super_admin', 'operational_admin' => [
                'Berapa MTTR saat ini?',
                'Berapa ticket closed hari ini?',
                'Siapa engineer paling efektif bulan ini?',
                'Siapa engineer workload tertinggi bulan ini?',
                'Siapa engineer dengan SLA terbaik bulan ini?',
                'Berapa total asset aktif?',
                'Status asset apa yang paling banyak dipakai?',
            ],
            'supervisor' => [
                'Berapa MTTR saat ini?',
                'Berapa ticket closed hari ini?',
                'Siapa engineer paling efektif bulan ini?',
                'Siapa engineer workload tertinggi bulan ini?',
                'Siapa engineer dengan SLA terbaik bulan ini?',
                'Berapa ticket overdue saat ini?',
                'Berapa rata-rata tiket bulan ini?',
            ],
            'engineer' => [
                'Task saya',
                'Berapa ticket open hari ini?',
                'Berapa ticket completed hari ini?',
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

    private function isTicketMetricIntent(string $normalized): bool
    {
        if (! $this->containsAny($normalized, ['ticket', 'tiket'])) {
            return false;
        }

        return $this->containsAny($normalized, [
            'closed',
            'ditutup',
            'completed',
            'selesai',
            'resolved',
            'rata rata',
            'rata-rata',
            'average',
            'avg',
            'hari ini',
            'minggu ini',
            'bulan ini',
        ]);
    }

    private function isEngineerAnalyticsIntent(string $normalized): bool
    {
        if (! $this->containsAny($normalized, ['engineer', 'teknisi'])) {
            return false;
        }

        return $this->containsAny($normalized, [
            'paling efektif',
            'terbaik',
            'top engineer',
            'most effective',
            'paling bagus',
            'paling produktif',
            'paling banyak close',
            'close ticket terbanyak',
            'closed ticket terbanyak',
            'paling banyak selesai',
            'workload tertinggi',
            'beban kerja tertinggi',
            'paling sibuk',
            'sla terbaik',
            'compliance terbaik',
        ]);
    }

    private function isAssetInsightIntent(string $normalized): bool
    {
        return $this->containsAny($normalized, [
            'asset',
            'aset',
            'asset status',
            'status asset',
            'asset location',
            'lokasi asset',
        ]);
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

    private function resolveAssistantPeriod(string $normalized, string $default = 'today'): array
    {
        $now = CarbonImmutable::now();

        if ($this->containsAny($normalized, ['hari ini', 'today'])) {
            return [$now->startOfDay(), $now->endOfDay(), 'hari ini'];
        }

        if ($this->containsAny($normalized, ['minggu ini', 'this week'])) {
            return [$now->startOfWeek(), $now->endOfWeek(), 'minggu ini'];
        }

        if ($this->containsAny($normalized, ['bulan ini', 'this month'])) {
            return [$now->startOfMonth(), $now->endOfMonth(), 'bulan ini'];
        }

        if ($default === 'month') {
            return [$now->startOfMonth(), $now->endOfMonth(), 'bulan ini'];
        }

        if ($default === 'week') {
            return [$now->startOfWeek(), $now->endOfWeek(), 'minggu ini'];
        }

        return [$now->startOfDay(), $now->endOfDay(), 'hari ini'];
    }
}
