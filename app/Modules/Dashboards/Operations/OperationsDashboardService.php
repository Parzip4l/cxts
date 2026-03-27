<?php

namespace App\Modules\Dashboards\Operations;

use App\Models\Inspection;
use App\Models\Ticket;
use App\Models\TicketWorklog;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class OperationsDashboardService
{

    public function overview(?User $actor, array $filters = []): array
    {
        [$from, $to] = $this->resolvePeriod($filters);

        return [
            'period' => $this->serializePeriod($from, $to),
            'summary' => $this->ticketSummary($actor, $from, $to, $filters),
            'sla' => $this->slaSummary($actor, $from, $to, $filters),
            'inspection_summary' => $this->inspectionSummary($actor, $from, $to),
            'daily_trend' => $this->ticketDailyTrend($actor, $from, $to, $filters),
            'top_engineers' => $this->engineerStats($actor, $from, $to, $filters, 5)->values()->all(),
            'report_structure' => $this->reportStructure($actor, $from, $to, $filters, 10),
        ];
    }

    public function slaPerformance(?User $actor, array $filters = []): array
    {
        [$from, $to] = $this->resolvePeriod($filters);

        return [
            'period' => $this->serializePeriod($from, $to),
            'summary' => $this->slaSummary($actor, $from, $to, $filters),
            'breach_tickets' => $this->breachTickets($actor, $from, $to, $filters),
            'daily_breach_trend' => $this->slaBreachTrend($actor, $from, $to, $filters),
        ];
    }

    public function engineerEffectiveness(?User $actor, array $filters = []): array
    {
        [$from, $to] = $this->resolvePeriod($filters);
        $engineers = $this->engineerStats($actor, $from, $to, $filters)->values();

        $totalAssigned = (int) $engineers->sum('assigned_tickets');
        $totalCompleted = (int) $engineers->sum('completed_tickets');

        return [
            'period' => $this->serializePeriod($from, $to),
            'summary' => [
                'engineer_count' => $engineers->count(),
                'total_assigned_tickets' => $totalAssigned,
                'total_completed_tickets' => $totalCompleted,
                'overall_completion_rate' => $this->percentage($totalCompleted, $totalAssigned),
                'avg_effectiveness_score' => round((float) $engineers->avg('effectiveness_score'), 2),
                'total_worklog_minutes' => (int) $engineers->sum('total_worklog_minutes'),
            ],
            'engineers' => $engineers->all(),
        ];
    }

    public function myEngineerPerformance(User $engineer, array $filters = []): array
    {
        [$from, $to] = $this->resolvePeriod($filters);
        $engineerStats = $this->engineerStats($engineer, $from, $to, $filters, null, $engineer->id)->first();

        return [
            'period' => $this->serializePeriod($from, $to),
            'engineer' => $engineerStats,
            'sla' => $this->slaSummary($engineer, $from, $to, $filters),
            'recent_tickets' => $this->recentTicketsForEngineer($engineer, $from, $to, $filters),
        ];
    }

    public function executiveReport(?User $actor, array $filters = []): array
    {
        [$from, $to] = $this->resolvePeriod($filters);
        $current = $this->reportSnapshot($actor, $from, $to, $filters);

        $comparisons = collect([
            $this->comparisonWindow('vs_7_days', 'Vs 7 Hari Sebelumnya', $current, $actor, $from->subDays(7), $to->subDays(7), $filters),
            $this->comparisonWindow('vs_1_month', 'Vs 1 Bulan Sebelumnya', $current, $actor, $from->subMonth(), $to->subMonth(), $filters),
            $this->comparisonWindow('vs_1_year', 'Vs 1 Tahun Sebelumnya', $current, $actor, $from->subYear(), $to->subYear(), $filters),
        ])->keyBy('key');

        $primaryComparison = $comparisons->get('vs_1_month') ?? $comparisons->first();

        return [
            'current' => $current,
            'comparisons' => $comparisons->values()->all(),
            'primary_comparison_key' => $primaryComparison['key'] ?? null,
            'executive_summary' => $this->executiveSummary($current, $primaryComparison),
            'action_plan' => $this->actionPlan($current, $primaryComparison),
            'top_risks' => $this->topRisks($current, $primaryComparison),
            'top_improvement_areas' => $this->topImprovementAreas($current, $primaryComparison),
        ];
    }

    private function ticketSummary(?User $actor, CarbonImmutable $from, CarbonImmutable $to, array $filters = []): array
    {
        $now = CarbonImmutable::now();
        $baseQuery = $this->ticketBaseQuery($actor, $from, $to, $filters);

        $totalTickets = (clone $baseQuery)->count();
        $completedTickets = (clone $baseQuery)->whereNotNull('tickets.completed_at')->count();
        $openTickets = (clone $baseQuery)->whereNull('tickets.completed_at')->count();

        $avgResponseMinutes = (clone $baseQuery)
            ->whereNotNull('tickets.responded_at')
            ->selectRaw('AVG(EXTRACT(EPOCH FROM (tickets.responded_at - tickets.created_at)) / 60) as avg_minutes')
            ->value('avg_minutes');

        $avgResolutionMinutes = (clone $baseQuery)
            ->whereNotNull('tickets.completed_at')
            ->selectRaw('AVG(EXTRACT(EPOCH FROM (tickets.completed_at - tickets.created_at)) / 60) as avg_minutes')
            ->value('avg_minutes');

        return [
            'total_tickets' => $totalTickets,
            'open_tickets' => $openTickets,
            'completed_tickets' => $completedTickets,
            'in_progress_tickets' => (clone $baseQuery)->whereNotNull('tickets.started_at')->whereNull('tickets.completed_at')->count(),
            'unassigned_tickets' => (clone $baseQuery)->whereNull('tickets.assigned_engineer_id')->count(),
            'overdue_resolution_tickets' => (clone $baseQuery)
                ->whereNull('tickets.completed_at')
                ->whereNull('tickets.paused_at')
                ->whereNotNull('tickets.resolution_due_at')
                ->where('tickets.resolution_due_at', '<', $now)
                ->count(),
            'avg_response_minutes' => $avgResponseMinutes !== null ? round((float) $avgResponseMinutes, 2) : null,
            'avg_resolution_minutes' => $avgResolutionMinutes !== null ? round((float) $avgResolutionMinutes, 2) : null,
        ];
    }

    private function reportSnapshot(?User $actor, CarbonImmutable $from, CarbonImmutable $to, array $filters = []): array
    {
        $summary = $this->ticketSummary($actor, $from, $to, $filters);
        $sla = $this->slaSummary($actor, $from, $to, $filters);
        $inspection = $this->inspectionSummary($actor, $from, $to);
        $engineers = $this->engineerStats($actor, $from, $to, $filters)->values();

        $totalAssigned = (int) $engineers->sum('assigned_tickets');
        $totalCompleted = (int) $engineers->sum('completed_tickets');
        $completionRate = $this->percentage($summary['completed_tickets'] ?? 0, $summary['total_tickets'] ?? 0);

        return [
            'period' => $this->serializePeriod($from, $to),
            'summary' => $summary,
            'sla' => $sla,
            'inspection' => $inspection,
            'engineer' => [
                'engineer_count' => $engineers->count(),
                'total_assigned_tickets' => $totalAssigned,
                'total_completed_tickets' => $totalCompleted,
                'overall_completion_rate' => $this->percentage($totalCompleted, $totalAssigned),
                'avg_effectiveness_score' => round((float) $engineers->avg('effectiveness_score'), 2),
                'total_worklog_minutes' => (int) $engineers->sum('total_worklog_minutes'),
            ],
            'derived' => [
                'completion_rate' => $completionRate,
                'abnormal_rate' => $inspection['submitted_inspections'] > 0
                    ? round(($inspection['abnormal_inspections'] / max(1, $inspection['submitted_inspections'])) * 100, 2)
                    : 0.0,
            ],
            'report_structure' => $this->reportStructure($actor, $from, $to, $filters, 5),
        ];
    }

    private function comparisonWindow(
        string $key,
        string $label,
        array $current,
        ?User $actor,
        CarbonImmutable $comparisonFrom,
        CarbonImmutable $comparisonTo,
        array $filters = []
    ): array {
        $comparison = $this->reportSnapshot($actor, $comparisonFrom, $comparisonTo, $filters);

        $delta = [
            'ticket_volume' => $this->metricDelta($current['summary']['total_tickets'], $comparison['summary']['total_tickets']),
            'completion_rate' => $this->metricDelta($current['derived']['completion_rate'], $comparison['derived']['completion_rate']),
            'response_compliance' => $this->metricDelta($current['sla']['response']['compliance_rate'], $comparison['sla']['response']['compliance_rate']),
            'resolution_compliance' => $this->metricDelta($current['sla']['resolution']['compliance_rate'], $comparison['sla']['resolution']['compliance_rate']),
            'avg_effectiveness_score' => $this->metricDelta($current['engineer']['avg_effectiveness_score'], $comparison['engineer']['avg_effectiveness_score']),
            'abnormal_inspections' => $this->metricDelta($current['inspection']['abnormal_inspections'], $comparison['inspection']['abnormal_inspections'], false),
            'overdue_resolution_tickets' => $this->metricDelta($current['summary']['overdue_resolution_tickets'], $comparison['summary']['overdue_resolution_tickets'], false),
            'unassigned_tickets' => $this->metricDelta($current['summary']['unassigned_tickets'], $comparison['summary']['unassigned_tickets'], false),
        ];

        return [
            'key' => $key,
            'label' => $label,
            'period' => $comparison['period'],
            'snapshot' => $comparison,
            'delta' => $delta,
            'status' => $this->comparisonStatus($delta),
            'drivers' => $this->comparisonDrivers($current, $comparison, $delta),
        ];
    }

    private function executiveSummary(array $current, ?array $comparison): array
    {
        if ($comparison === null) {
            return [
                'headline' => 'Belum ada baseline pembanding yang cukup untuk periode yang dipilih.',
                'tone' => 'stable',
                'highlights' => [],
                'note' => 'Ringkasan akan otomatis muncul ketika periode aktif punya pembanding historis yang relevan.',
            ];
        }

        $headline = match ($comparison['status']) {
            'improved' => "Secara umum, performa operasional pada periode ini membaik dibanding {$comparison['label']}.",
            'declined' => "Secara umum, performa operasional pada periode ini menurun dibanding {$comparison['label']}.",
            default => "Performa operasional pada periode ini cenderung campuran dibanding {$comparison['label']}.",
        };

        return [
            'headline' => $headline,
            'tone' => $comparison['status'],
            'highlights' => $comparison['drivers'],
            'note' => 'Ringkasan ini dibuat otomatis dari perubahan volume ticket, SLA, hasil inspection, distribusi taxonomy, dan efektivitas engineer pada data aktual.',
        ];
    }

    private function actionPlan(array $current, ?array $comparison): array
    {
        if ($comparison === null) {
            return [[
                'priority' => 'Monitoring',
                'title' => 'Lengkapi baseline pembanding',
                'owner' => 'Operations Lead',
                'timeframe' => 'Periode berikutnya',
                'message' => 'Perlu baseline historis yang memadai agar rekomendasi otomatis bisa lebih tajam dan tidak hanya bersifat observasional.',
            ]];
        }

        $actions = [];
        $delta = $comparison['delta'];

        if (
            $current['sla']['response']['compliance_rate'] < 90
            || $delta['response_compliance']['impact'] < 0
            || $delta['unassigned_tickets']['impact'] < 0
        ) {
            $actions[] = [
                'priority' => 'Immediate',
                'title' => 'Perkuat triage dan assignment awal',
                'owner' => 'Service Desk Supervisor',
                'timeframe' => '24-48 jam',
                'message' => 'Response SLA sedang tertekan atau ticket tanpa engineer bertambah. Fokuskan review pada aturan dispatch, kapasitas first response, dan backlog ticket yang belum punya owner.',
            ];
        }

        if (
            $current['sla']['resolution']['compliance_rate'] < 85
            || $delta['resolution_compliance']['impact'] < 0
            || $delta['overdue_resolution_tickets']['impact'] < 0
        ) {
            $actions[] = [
                'priority' => 'Immediate',
                'title' => 'Lakukan backlog recovery untuk ticket overdue',
                'owner' => 'Ops Admin / Supervisor',
                'timeframe' => 'Minggu ini',
                'message' => 'Ada indikasi penurunan ketepatan penyelesaian. Prioritaskan clearance pada ticket overdue, evaluasi bottleneck approval/assignment, dan siapkan jalur eskalasi untuk kasus yang paling lama terbuka.',
            ];
        }

        if ($delta['ticket_volume']['direction'] === 'up' && abs((float) ($delta['ticket_volume']['percentage_change'] ?? 0)) >= 10) {
            $actions[] = [
                'priority' => 'High',
                'title' => 'Sesuaikan kapasitas dengan kenaikan demand',
                'owner' => 'Operations Lead',
                'timeframe' => '1-2 minggu',
                'message' => 'Volume ticket naik signifikan terhadap baseline. Pertimbangkan penyesuaian shift, redistribusi engineer, atau fokus pada taxonomy dengan lonjakan tertinggi agar kualitas layanan tetap terjaga.',
            ];
        }

        if ($delta['abnormal_inspections']['direction'] === 'up') {
            $actions[] = [
                'priority' => 'High',
                'title' => 'Tindak lanjuti sumber abnormal inspection',
                'owner' => 'Inspection Coordinator',
                'timeframe' => 'Minggu ini',
                'message' => 'Temuan abnormal meningkat dan berpotensi menambah ticket lanjutan. Review aset/lokasi dengan temuan berulang lalu buat tindakan korektif sebelum berubah menjadi beban ticket operasional.',
            ];
        }

        if (
            $delta['avg_effectiveness_score']['impact'] < 0
            || $delta['completion_rate']['impact'] < 0
        ) {
            $actions[] = [
                'priority' => 'Medium',
                'title' => 'Review workload dan efektivitas engineer',
                'owner' => 'Engineering Supervisor',
                'timeframe' => '1 minggu',
                'message' => 'Efektivitas atau completion rate melemah dibanding baseline. Evaluasi workload terbuka per engineer, kecocokan skill, dan quality of handoff dari assignment ke eksekusi.',
            ];
        }

        if (empty($actions)) {
            $actions[] = [
                'priority' => 'Maintain',
                'title' => 'Pertahankan pola operasional yang sedang berjalan',
                'owner' => 'Operations Lead',
                'timeframe' => 'Berjalan',
                'message' => 'Indikator utama relatif stabil atau membaik. Fokus berikutnya adalah menjaga disiplin triage, SLA monitoring, dan inspeksi preventif agar kualitas tetap konsisten.',
            ];
        }

        return array_slice($actions, 0, 4);
    }

    private function topRisks(array $current, ?array $comparison): array
    {
        $risks = [];
        $delta = $comparison['delta'] ?? [];

        if (($current['sla']['response']['compliance_rate'] ?? 0) < 90) {
            $risks[] = [
                'severity' => 'Critical',
                'title' => 'Response SLA belum sehat',
                'message' => sprintf(
                    'Response SLA saat ini berada di %s%%. Kondisi ini meningkatkan risiko keterlambatan penanganan awal dan potensi eskalasi dari user.',
                    number_format($current['sla']['response']['compliance_rate'] ?? 0, 2)
                ),
            ];
        }

        if (($current['sla']['resolution']['compliance_rate'] ?? 0) < 85) {
            $risks[] = [
                'severity' => 'Critical',
                'title' => 'Resolution SLA berada di bawah target',
                'message' => sprintf(
                    'Resolution SLA saat ini %s%% dengan %s ticket overdue. Risiko utamanya adalah backlog berkepanjangan dan penurunan persepsi kualitas layanan.',
                    number_format($current['sla']['resolution']['compliance_rate'] ?? 0, 2),
                    number_format($current['summary']['overdue_resolution_tickets'] ?? 0)
                ),
            ];
        }

        if (($current['summary']['unassigned_tickets'] ?? 0) > 0) {
            $risks[] = [
                'severity' => 'High',
                'title' => 'Masih ada ticket tanpa owner engineer',
                'message' => sprintf(
                    'Saat ini ada %s ticket yang belum ter-assign. Semakin lama dibiarkan, semakin besar risiko response delay dan bottleneck eksekusi.',
                    number_format($current['summary']['unassigned_tickets'] ?? 0)
                ),
            ];
        }

        if (($current['inspection']['abnormal_inspections'] ?? 0) > 0) {
            $risks[] = [
                'severity' => 'High',
                'title' => 'Temuan abnormal inspection menambah tekanan operasional',
                'message' => sprintf(
                    'Ada %s temuan abnormal pada periode aktif. Ini berpotensi menjadi sumber ticket lanjutan dan menambah beban corrective work.',
                    number_format($current['inspection']['abnormal_inspections'] ?? 0)
                ),
            ];
        }

        if (($delta['ticket_volume']['direction'] ?? 'flat') === 'up' && abs((float) ($delta['ticket_volume']['percentage_change'] ?? 0)) >= 10) {
            $risks[] = [
                'severity' => 'Medium',
                'title' => 'Demand naik lebih cepat daripada ritme eksekusi',
                'message' => sprintf(
                    'Volume ticket naik %s%% dibanding benchmark utama. Tanpa penyesuaian kapasitas, risiko SLA breach dan queue congestion akan meningkat.',
                    number_format(abs($delta['ticket_volume']['percentage_change'] ?? 0), 1)
                ),
            ];
        }

        if (($delta['avg_effectiveness_score']['impact'] ?? 0) < 0) {
            $risks[] = [
                'severity' => 'Medium',
                'title' => 'Efektivitas engineer melemah dibanding baseline',
                'message' => sprintf(
                    'Average engineer effectiveness turun menjadi %s. Ini menandakan ada tekanan kapasitas, mismatch skill, atau throughput penyelesaian yang melambat.',
                    number_format($current['engineer']['avg_effectiveness_score'] ?? 0, 2)
                ),
            ];
        }

        if (empty($risks)) {
            $risks[] = [
                'severity' => 'Low',
                'title' => 'Tidak ada risiko dominan yang menonjol',
                'message' => 'Indikator utama relatif terkendali pada periode ini. Risiko operasional masih perlu dipantau, tetapi belum ada sinyal merah yang dominan dari data.',
            ];
        }

        return array_slice($risks, 0, 5);
    }

    private function topImprovementAreas(array $current, ?array $comparison): array
    {
        $areas = [];
        $delta = $comparison['delta'] ?? [];

        if (($delta['response_compliance']['impact'] ?? 0) < 0 || ($current['summary']['unassigned_tickets'] ?? 0) > 0) {
            $areas[] = [
                'priority' => 'Immediate',
                'title' => 'Percepat triage dan first assignment',
                'message' => 'Ruang perbaikan terbesar saat ini ada pada kecepatan respons awal. Penguatan triage, auto-routing, atau dispatch discipline akan memberi dampak cepat ke SLA response.',
            ];
        }

        if (($delta['resolution_compliance']['impact'] ?? 0) < 0 || ($current['summary']['overdue_resolution_tickets'] ?? 0) > 0) {
            $areas[] = [
                'priority' => 'High',
                'title' => 'Kurangi backlog dan ticket overdue',
                'message' => 'Backlog recovery dan aging control akan langsung memperbaiki resolution SLA, menurunkan queue stress, dan meningkatkan completion rate.',
            ];
        }

        if (($delta['abnormal_inspections']['direction'] ?? 'flat') === 'up') {
            $areas[] = [
                'priority' => 'High',
                'title' => 'Perkuat tindakan preventif dari hasil inspection',
                'message' => 'Ada peluang besar menekan ticket lanjutan dengan mempercepat koreksi pada aset/lokasi yang berulang kali menghasilkan temuan abnormal.',
            ];
        }

        if (($delta['avg_effectiveness_score']['impact'] ?? 0) < 0) {
            $areas[] = [
                'priority' => 'Medium',
                'title' => 'Optimalkan distribusi workload engineer',
                'message' => 'Penataan workload, pencocokan skill, dan quality handoff assignment berpotensi meningkatkan efektivitas engineer tanpa harus langsung menambah headcount.',
            ];
        }

        if (($delta['ticket_volume']['direction'] ?? 'flat') === 'up') {
            $areas[] = [
                'priority' => 'Medium',
                'title' => 'Sesuaikan kapasitas dengan lonjakan demand',
                'message' => 'Kenaikan volume membuka area perbaikan pada shift planning, kapasitas tim, dan fokus taxonomy yang paling banyak menyumbang pertumbuhan ticket.',
            ];
        }

        if (($delta['response_compliance']['impact'] ?? 0) > 0 && ($delta['resolution_compliance']['impact'] ?? 0) > 0) {
            $areas[] = [
                'priority' => 'Maintain',
                'title' => 'Scale praktik yang sudah terbukti efektif',
                'message' => 'SLA menunjukkan arah yang lebih baik. Area improvement berikutnya adalah menstandarkan praktik yang berhasil agar performa baik ini konsisten di periode berikutnya.',
            ];
        }

        if (empty($areas)) {
            $areas[] = [
                'priority' => 'Maintain',
                'title' => 'Lanjutkan disiplin operasional yang berjalan',
                'message' => 'Belum ada area perbaikan dominan yang menonjol. Fokus terbaik adalah menjaga konsistensi SLA monitoring, inspection follow-up, dan assignment discipline.',
            ];
        }

        return array_slice($areas, 0, 5);
    }

    private function comparisonDrivers(array $current, array $comparisonSnapshot, array $delta): array
    {
        $drivers = [];

        $ticketVolumeDelta = $delta['ticket_volume'];
        if ($ticketVolumeDelta['direction'] !== 'flat') {
            $topVolumeDriver = $this->topTaxonomyDriver(
                $current['report_structure']['taxonomy_breakdown'],
                $comparisonSnapshot['report_structure']['taxonomy_breakdown'],
                $ticketVolumeDelta['direction'] === 'up'
            );

            $message = sprintf(
                'Ticket volume %s by %s (%s%%).',
                $ticketVolumeDelta['direction'] === 'up' ? 'increased' : 'decreased',
                abs($ticketVolumeDelta['change']),
                number_format(abs($ticketVolumeDelta['percentage_change'] ?? 0), 1)
            );

            if ($topVolumeDriver !== null) {
                $message .= sprintf(
                    ' Pendorong paling kuat datang dari %s > %s > %s (%+d ticket).',
                    $topVolumeDriver['ticket_type_name'],
                    $topVolumeDriver['ticket_category_name'],
                    $topVolumeDriver['ticket_sub_category_name'],
                    $topVolumeDriver['delta']
                );
            }

            $drivers[] = [
                'title' => 'Perubahan Demand',
                'tone' => $ticketVolumeDelta['direction'] === 'up' ? 'warning' : 'success',
                'message' => str($message)
                    ->replace('Ticket volume increased by', 'Volume ticket meningkat sebanyak')
                    ->replace('Ticket volume decreased by', 'Volume ticket menurun sebanyak')
                    ->replace('tickets).', ' ticket).')
                    ->toString(),
            ];
        }

        $responseDelta = $delta['response_compliance'];
        $resolutionDelta = $delta['resolution_compliance'];
        if ($responseDelta['direction'] !== 'flat' || $resolutionDelta['direction'] !== 'flat') {
            $message = sprintf(
                'Response SLA moved from %s%% to %s%% and resolution SLA moved from %s%% to %s%%.',
                number_format($comparisonSnapshot['sla']['response']['compliance_rate'], 2),
                number_format($current['sla']['response']['compliance_rate'], 2),
                number_format($comparisonSnapshot['sla']['resolution']['compliance_rate'], 2),
                number_format($current['sla']['resolution']['compliance_rate'], 2)
            );

            if ($delta['overdue_resolution_tickets']['direction'] !== 'flat') {
                $message .= sprintf(
                    ' Overdue resolution tickets %s to %s.',
                    $delta['overdue_resolution_tickets']['direction'] === 'up' ? 'rose' : 'fell',
                    number_format($current['summary']['overdue_resolution_tickets'])
                );
            }

            if ($delta['unassigned_tickets']['direction'] !== 'flat') {
                $message .= sprintf(
                    ' Unassigned tickets %s to %s.',
                    $delta['unassigned_tickets']['direction'] === 'up' ? 'rose' : 'fell',
                    number_format($current['summary']['unassigned_tickets'])
                );
            }

            $drivers[] = [
                'title' => 'Pergerakan SLA',
                'tone' => ($responseDelta['impact'] + $resolutionDelta['impact']) >= 0 ? 'success' : 'danger',
                'message' => str($message)
                    ->replace('Response SLA moved from', 'Response SLA bergerak dari')
                    ->replace('and resolution SLA moved from', 'dan Resolution SLA bergerak dari')
                    ->replace('Overdue resolution tickets rose to', 'Ticket overdue resolution naik menjadi')
                    ->replace('Overdue resolution tickets fell to', 'Ticket overdue resolution turun menjadi')
                    ->replace('Unassigned tickets rose to', 'Ticket tanpa engineer naik menjadi')
                    ->replace('Unassigned tickets fell to', 'Ticket tanpa engineer turun menjadi')
                    ->toString(),
            ];
        }

        $abnormalDelta = $delta['abnormal_inspections'];
        if ($abnormalDelta['direction'] !== 'flat') {
            $drivers[] = [
                'title' => 'Kualitas Inspection',
                'tone' => $abnormalDelta['impact'] >= 0 ? 'success' : 'warning',
                'message' => sprintf(
                    'Temuan inspection abnormal berubah dari %s menjadi %s, yang %s tekanan ticket lanjutan.',
                    number_format($comparisonSnapshot['inspection']['abnormal_inspections']),
                    number_format($current['inspection']['abnormal_inspections']),
                    $abnormalDelta['direction'] === 'up' ? 'meningkatkan' : 'menurunkan'
                ),
            ];
        }

        $effectivenessDelta = $delta['avg_effectiveness_score'];
        if ($effectivenessDelta['direction'] !== 'flat') {
            $drivers[] = [
                'title' => 'Kapasitas Eksekusi',
                'tone' => $effectivenessDelta['impact'] >= 0 ? 'success' : 'warning',
                'message' => sprintf(
                    'Rata-rata efektivitas engineer bergerak dari %s menjadi %s, sementara completion rate bergeser dari %s%% menjadi %s%%.',
                    number_format($comparisonSnapshot['engineer']['avg_effectiveness_score'], 2),
                    number_format($current['engineer']['avg_effectiveness_score'], 2),
                    number_format($comparisonSnapshot['derived']['completion_rate'], 2),
                    number_format($current['derived']['completion_rate'], 2)
                ),
            ];
        }

        if (empty($drivers)) {
            $drivers[] = [
                'title' => 'Performa Stabil',
                'tone' => 'secondary',
                'message' => 'Periode yang dipilih relatif stabil terhadap baseline pembanding, tanpa pergeseran material pada demand ticket, SLA, hasil inspection, maupun eksekusi engineer.',
            ];
        }

        return $drivers;
    }

    private function topTaxonomyDriver(array $currentRows, array $comparisonRows, bool $positive = true): ?array
    {
        $currentMap = collect($currentRows)->keyBy(fn (array $row) => implode('|', [
            $row['ticket_type_name'],
            $row['ticket_category_name'],
            $row['ticket_sub_category_name'],
        ]));

        $comparisonMap = collect($comparisonRows)->keyBy(fn (array $row) => implode('|', [
            $row['ticket_type_name'],
            $row['ticket_category_name'],
            $row['ticket_sub_category_name'],
        ]));

        $keys = $currentMap->keys()->merge($comparisonMap->keys())->unique();

        $driver = $keys->map(function (string $key) use ($currentMap, $comparisonMap) {
            $currentRow = $currentMap->get($key);
            $comparisonRow = $comparisonMap->get($key);

            return [
                'ticket_type_name' => $currentRow['ticket_type_name'] ?? $comparisonRow['ticket_type_name'] ?? 'Unclassified Type',
                'ticket_category_name' => $currentRow['ticket_category_name'] ?? $comparisonRow['ticket_category_name'] ?? 'Unclassified Category',
                'ticket_sub_category_name' => $currentRow['ticket_sub_category_name'] ?? $comparisonRow['ticket_sub_category_name'] ?? 'Unclassified Sub Category',
                'delta' => (int) (($currentRow['total_tickets'] ?? 0) - ($comparisonRow['total_tickets'] ?? 0)),
            ];
        })->sortBy($positive ? fn (array $row) => -$row['delta'] : fn (array $row) => $row['delta'])->first();

        if ($driver === null || ($positive && $driver['delta'] <= 0) || (! $positive && $driver['delta'] >= 0)) {
            return null;
        }

        return $driver;
    }

    private function comparisonStatus(array $delta): string
    {
        $score = collect($delta)->sum('impact');

        if ($score >= 2) {
            return 'improved';
        }

        if ($score <= -2) {
            return 'declined';
        }

        return 'mixed';
    }

    private function metricDelta(int|float|null $current, int|float|null $previous, bool $higherIsBetter = true): array
    {
        $currentValue = (float) ($current ?? 0);
        $previousValue = (float) ($previous ?? 0);
        $change = round($currentValue - $previousValue, 2);
        $percentageChange = $previousValue != 0.0 ? round(($change / $previousValue) * 100, 2) : null;
        $direction = $change > 0 ? 'up' : ($change < 0 ? 'down' : 'flat');

        $impact = match ($direction) {
            'up' => $higherIsBetter ? 1 : -1,
            'down' => $higherIsBetter ? -1 : 1,
            default => 0,
        };

        return [
            'current' => $currentValue,
            'previous' => $previousValue,
            'change' => $change,
            'percentage_change' => $percentageChange,
            'direction' => $direction,
            'impact' => $impact,
        ];
    }

    private function slaSummary(?User $actor, CarbonImmutable $from, CarbonImmutable $to, array $filters = []): array
    {
        $now = CarbonImmutable::now();
        $baseQuery = $this->ticketBaseQuery($actor, $from, $to, $filters);

        $responseOnTime = (clone $baseQuery)
            ->whereNotNull('tickets.response_due_at')
            ->whereNotNull('tickets.responded_at')
            ->whereColumn('tickets.responded_at', '<=', 'tickets.response_due_at')
            ->count();

        $responseBreached = (clone $baseQuery)
            ->whereNotNull('tickets.response_due_at')
            ->where(function (Builder $query) use ($now): void {
                $query->where(function (Builder $started): void {
                    $started->whereNotNull('tickets.responded_at')
                        ->whereColumn('tickets.responded_at', '>', 'tickets.response_due_at');
                })->orWhere(function (Builder $pending) use ($now): void {
                    $pending->whereNull('tickets.responded_at')
                        ->whereNull('tickets.paused_at')
                        ->where('tickets.response_due_at', '<', $now);
                });
            })
            ->count();

        $responsePending = (clone $baseQuery)
            ->whereNotNull('tickets.response_due_at')
            ->whereNull('tickets.responded_at')
            ->whereNull('tickets.paused_at')
            ->where('tickets.response_due_at', '>=', $now)
            ->count();

        $resolutionOnTime = (clone $baseQuery)
            ->whereNotNull('tickets.resolution_due_at')
            ->whereNotNull('tickets.completed_at')
            ->whereColumn('tickets.completed_at', '<=', 'tickets.resolution_due_at')
            ->count();

        $resolutionBreached = (clone $baseQuery)
            ->whereNotNull('tickets.resolution_due_at')
            ->where(function (Builder $query) use ($now): void {
                $query->where(function (Builder $completed): void {
                    $completed->whereNotNull('tickets.completed_at')
                        ->whereColumn('tickets.completed_at', '>', 'tickets.resolution_due_at');
                })->orWhere(function (Builder $pending) use ($now): void {
                    $pending->whereNull('tickets.completed_at')
                        ->whereNull('tickets.paused_at')
                        ->where('tickets.resolution_due_at', '<', $now);
                });
            })
            ->count();

        $resolutionPending = (clone $baseQuery)
            ->whereNotNull('tickets.resolution_due_at')
            ->whereNull('tickets.completed_at')
            ->whereNull('tickets.paused_at')
            ->where('tickets.resolution_due_at', '>=', $now)
            ->count();

        $responseMeasured = $responseOnTime + $responseBreached;
        $resolutionMeasured = $resolutionOnTime + $resolutionBreached;

        return [
            'response' => [
                'on_time' => $responseOnTime,
                'breached' => $responseBreached,
                'pending' => $responsePending,
                'compliance_rate' => $this->percentage($responseOnTime, $responseMeasured),
            ],
            'resolution' => [
                'on_time' => $resolutionOnTime,
                'breached' => $resolutionBreached,
                'pending' => $resolutionPending,
                'compliance_rate' => $this->percentage($resolutionOnTime, $resolutionMeasured),
            ],
        ];
    }

    private function inspectionSummary(?User $actor, CarbonImmutable $from, CarbonImmutable $to): array
    {
        $baseQuery = Inspection::query()
            ->whereBetween('inspection_date', [$from->toDateString(), $to->toDateString()]);

        $this->applyInspectionAccessScope($baseQuery, $actor);

        $totalInspections = (clone $baseQuery)->count();
        $submittedInspections = (clone $baseQuery)->where('status', Inspection::STATUS_SUBMITTED)->count();
        $normalInspections = (clone $baseQuery)->where('final_result', Inspection::FINAL_RESULT_NORMAL)->count();
        $abnormalInspections = (clone $baseQuery)->where('final_result', Inspection::FINAL_RESULT_ABNORMAL)->count();

        return [
            'total_inspections' => $totalInspections,
            'submitted_inspections' => $submittedInspections,
            'draft_inspections' => (clone $baseQuery)->where('status', Inspection::STATUS_DRAFT)->count(),
            'in_progress_inspections' => (clone $baseQuery)->where('status', Inspection::STATUS_IN_PROGRESS)->count(),
            'normal_inspections' => $normalInspections,
            'abnormal_inspections' => $abnormalInspections,
            'submission_rate' => $this->percentage($submittedInspections, $totalInspections),
            'normal_rate' => $this->percentage($normalInspections, $submittedInspections),
        ];
    }

    private function ticketDailyTrend(?User $actor, CarbonImmutable $from, CarbonImmutable $to, array $filters = []): array
    {
        $baseQuery = $this->ticketBaseQuery($actor, $from, $to, $filters);

        $createdByDay = (clone $baseQuery)
            ->selectRaw('DATE(tickets.created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $completedByDay = (clone $baseQuery)
            ->whereNotNull('tickets.completed_at')
            ->whereBetween('tickets.completed_at', [$from, $to])
            ->selectRaw('DATE(tickets.completed_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $days = [];
        for ($date = $from->startOfDay(); $date->lte($to->startOfDay()); $date = $date->addDay()) {
            $dayKey = $date->toDateString();
            $days[] = [
                'date' => $dayKey,
                'created' => (int) ($createdByDay[$dayKey] ?? 0),
                'completed' => (int) ($completedByDay[$dayKey] ?? 0),
            ];
        }

        return $days;
    }

    private function slaBreachTrend(?User $actor, CarbonImmutable $from, CarbonImmutable $to, array $filters = []): array
    {
        $now = CarbonImmutable::now();
        $baseQuery = $this->ticketBaseQuery($actor, $from, $to, $filters);

        $breachByDay = (clone $baseQuery)
            ->where(function (Builder $query) use ($now): void {
                $query->where(function (Builder $response) use ($now): void {
                    $response->whereNotNull('tickets.response_due_at')
                        ->where(function (Builder $responseCheck) use ($now): void {
                            $responseCheck->where(function (Builder $started): void {
                                $started->whereNotNull('tickets.responded_at')
                                    ->whereColumn('tickets.responded_at', '>', 'tickets.response_due_at');
                            })->orWhere(function (Builder $pending) use ($now): void {
                                $pending->whereNull('tickets.responded_at')
                                    ->whereNull('tickets.paused_at')
                                    ->where('tickets.response_due_at', '<', $now);
                            });
                        });
                })->orWhere(function (Builder $resolution) use ($now): void {
                    $resolution->whereNotNull('tickets.resolution_due_at')
                        ->where(function (Builder $resolutionCheck) use ($now): void {
                            $resolutionCheck->where(function (Builder $completed): void {
                                $completed->whereNotNull('tickets.completed_at')
                                    ->whereColumn('tickets.completed_at', '>', 'tickets.resolution_due_at');
                            })->orWhere(function (Builder $pending) use ($now): void {
                                $pending->whereNull('tickets.completed_at')
                                    ->whereNull('tickets.paused_at')
                                    ->where('tickets.resolution_due_at', '<', $now);
                            });
                        });
                });
            })
            ->selectRaw('DATE(COALESCE(tickets.resolution_due_at, tickets.response_due_at)) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $days = [];
        for ($date = $from->startOfDay(); $date->lte($to->startOfDay()); $date = $date->addDay()) {
            $dayKey = $date->toDateString();
            $days[] = [
                'date' => $dayKey,
                'breached' => (int) ($breachByDay[$dayKey] ?? 0),
            ];
        }

        return $days;
    }

    private function breachTickets(?User $actor, CarbonImmutable $from, CarbonImmutable $to, array $filters = [], int $limit = 10): array
    {
        $now = CarbonImmutable::now();
        $tickets = $this->ticketBaseQuery($actor, $from, $to, $filters)
            ->with([
                'status:id,name,code',
                'priority:id,name,level',
                'assignedEngineer:id,name',
            ])
            ->where(function (Builder $query) use ($now): void {
                $query->where(function (Builder $response) use ($now): void {
                    $response->whereNotNull('tickets.response_due_at')
                        ->where(function (Builder $responseCheck) use ($now): void {
                            $responseCheck->where(function (Builder $started): void {
                                $started->whereNotNull('tickets.responded_at')
                                    ->whereColumn('tickets.responded_at', '>', 'tickets.response_due_at');
                            })->orWhere(function (Builder $pending) use ($now): void {
                                $pending->whereNull('tickets.responded_at')
                                    ->whereNull('tickets.paused_at')
                                    ->where('tickets.response_due_at', '<', $now);
                            });
                        });
                })->orWhere(function (Builder $resolution) use ($now): void {
                    $resolution->whereNotNull('tickets.resolution_due_at')
                        ->where(function (Builder $resolutionCheck) use ($now): void {
                            $resolutionCheck->where(function (Builder $completed): void {
                                $completed->whereNotNull('tickets.completed_at')
                                    ->whereColumn('tickets.completed_at', '>', 'tickets.resolution_due_at');
                            })->orWhere(function (Builder $pending) use ($now): void {
                                $pending->whereNull('tickets.completed_at')
                                    ->whereNull('tickets.paused_at')
                                    ->where('tickets.resolution_due_at', '<', $now);
                            });
                        });
                });
            })
            ->orderByRaw('COALESCE(tickets.resolution_due_at, tickets.response_due_at) ASC')
            ->limit($limit)
            ->get();

        return $tickets->map(function (Ticket $ticket) use ($now): array {
            $responseBreached = $ticket->response_due_at !== null
                && (($ticket->responded_at !== null && $ticket->responded_at->gt($ticket->response_due_at))
                    || ($ticket->responded_at === null && $ticket->paused_at === null && $ticket->response_due_at->lt($now)));

            $resolutionBreached = $ticket->resolution_due_at !== null
                && (($ticket->completed_at !== null && $ticket->completed_at->gt($ticket->resolution_due_at))
                    || ($ticket->completed_at === null && $ticket->paused_at === null && $ticket->resolution_due_at->lt($now)));

            $responseLateMinutes = null;
            if ($responseBreached && $ticket->response_due_at !== null) {
                $actualResponseAt = $ticket->responded_at ?? $now;
                $responseLateMinutes = max(0, $ticket->response_due_at->diffInMinutes($actualResponseAt));
            }

            $resolutionLateMinutes = null;
            if ($resolutionBreached && $ticket->resolution_due_at !== null) {
                $actualResolutionAt = $ticket->completed_at ?? $now;
                $resolutionLateMinutes = max(0, $ticket->resolution_due_at->diffInMinutes($actualResolutionAt));
            }

            return [
                'id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'title' => $ticket->title,
                'status_name' => $ticket->status?->name,
                'priority_name' => $ticket->priority?->name,
                'assigned_engineer_name' => $ticket->assignedEngineer?->name,
                'response_due_at' => $ticket->response_due_at,
                'resolution_due_at' => $ticket->resolution_due_at,
                'response_breached' => $responseBreached,
                'resolution_breached' => $resolutionBreached,
                'response_late_minutes' => $responseLateMinutes,
                'resolution_late_minutes' => $resolutionLateMinutes,
            ];
        })->all();
    }

    private function engineerStats(
        ?User $actor,
        CarbonImmutable $from,
        CarbonImmutable $to,
        array $filters = [],
        ?int $limit = null,
        ?int $specificEngineerId = null,
    ): Collection {
        $now = CarbonImmutable::now();

        $query = Ticket::query()
            ->from('tickets')
            ->join('users as engineers', 'tickets.assigned_engineer_id', '=', 'engineers.id')
            ->leftJoin('departments as engineer_departments', 'engineers.department_id', '=', 'engineer_departments.id')
            ->whereNotNull('tickets.assigned_engineer_id')
            ->whereBetween('tickets.created_at', [$from, $to]);

        $this->applyTicketAccessScope($query, $actor);
        $this->applyTicketFilters($query, $filters);

        if ($specificEngineerId !== null) {
            $query->where('tickets.assigned_engineer_id', $specificEngineerId);
        }

        $stats = $query
            ->selectRaw(
                'tickets.assigned_engineer_id as engineer_id,
                engineers.name as engineer_name,
                engineer_departments.name as department_name,
                COUNT(*) as assigned_tickets,
                SUM(CASE WHEN tickets.completed_at IS NOT NULL THEN 1 ELSE 0 END) as completed_tickets,
                SUM(CASE WHEN tickets.completed_at IS NULL THEN 1 ELSE 0 END) as open_tickets,
                SUM(CASE WHEN tickets.response_due_at IS NOT NULL AND tickets.responded_at IS NOT NULL AND tickets.responded_at <= tickets.response_due_at THEN 1 ELSE 0 END) as response_on_time_count,
                SUM(CASE WHEN tickets.response_due_at IS NOT NULL AND ((tickets.responded_at IS NOT NULL AND tickets.responded_at > tickets.response_due_at) OR (tickets.responded_at IS NULL AND tickets.paused_at IS NULL AND tickets.response_due_at < ?)) THEN 1 ELSE 0 END) as response_breached_count,
                SUM(CASE WHEN tickets.resolution_due_at IS NOT NULL AND tickets.completed_at IS NOT NULL AND tickets.completed_at <= tickets.resolution_due_at THEN 1 ELSE 0 END) as resolution_on_time_count,
                SUM(CASE WHEN tickets.resolution_due_at IS NOT NULL AND ((tickets.completed_at IS NOT NULL AND tickets.completed_at > tickets.resolution_due_at) OR (tickets.completed_at IS NULL AND tickets.paused_at IS NULL AND tickets.resolution_due_at < ?)) THEN 1 ELSE 0 END) as resolution_breached_count,
                AVG(CASE WHEN tickets.responded_at IS NOT NULL THEN EXTRACT(EPOCH FROM (tickets.responded_at - tickets.created_at)) / 60 END) as avg_response_minutes,
                AVG(CASE WHEN tickets.completed_at IS NOT NULL THEN EXTRACT(EPOCH FROM (tickets.completed_at - tickets.created_at)) / 60 END) as avg_resolution_minutes',
                [$now, $now]
            )
            ->groupBy('tickets.assigned_engineer_id', 'engineers.name', 'engineer_departments.name')
            ->orderByDesc('completed_tickets')
            ->get();

        $worklogQuery = TicketWorklog::query()
            ->join('tickets', 'ticket_worklogs.ticket_id', '=', 'tickets.id')
            ->selectRaw('ticket_worklogs.user_id, COALESCE(SUM(ticket_worklogs.duration_minutes), 0) as total_worklog_minutes')
            ->whereBetween('ticket_worklogs.created_at', [$from, $to]);

        $this->applyTicketFilters($worklogQuery, $filters);

        if ($specificEngineerId !== null) {
            $worklogQuery->where('ticket_worklogs.user_id', $specificEngineerId);
        }

        $worklogByEngineer = $worklogQuery
            ->groupBy('ticket_worklogs.user_id')
            ->pluck('total_worklog_minutes', 'ticket_worklogs.user_id');

        $mapped = $stats->map(function (object $row) use ($worklogByEngineer): array {
            $assignedTickets = (int) $row->assigned_tickets;
            $completedTickets = (int) $row->completed_tickets;
            $responseOnTimeCount = (int) $row->response_on_time_count;
            $responseBreachedCount = (int) $row->response_breached_count;
            $resolutionOnTimeCount = (int) $row->resolution_on_time_count;
            $resolutionBreachedCount = (int) $row->resolution_breached_count;

            $completionRate = $this->percentage($completedTickets, $assignedTickets);
            $responseCompliance = $this->percentage($responseOnTimeCount, $responseOnTimeCount + $responseBreachedCount);
            $resolutionCompliance = $this->percentage($resolutionOnTimeCount, $resolutionOnTimeCount + $resolutionBreachedCount);
            $effectivenessScore = round(($completionRate * 0.5) + ($resolutionCompliance * 0.35) + ($responseCompliance * 0.15), 2);

            return [
                'engineer_id' => (int) $row->engineer_id,
                'engineer_name' => $row->engineer_name,
                'department_name' => $row->department_name,
                'assigned_tickets' => $assignedTickets,
                'completed_tickets' => $completedTickets,
                'open_tickets' => (int) $row->open_tickets,
                'completion_rate' => $completionRate,
                'response_on_time_count' => $responseOnTimeCount,
                'response_breached_count' => $responseBreachedCount,
                'response_compliance_rate' => $responseCompliance,
                'resolution_on_time_count' => $resolutionOnTimeCount,
                'resolution_breached_count' => $resolutionBreachedCount,
                'resolution_compliance_rate' => $resolutionCompliance,
                'avg_response_minutes' => $row->avg_response_minutes !== null ? round((float) $row->avg_response_minutes, 2) : null,
                'avg_resolution_minutes' => $row->avg_resolution_minutes !== null ? round((float) $row->avg_resolution_minutes, 2) : null,
                'total_worklog_minutes' => (int) ($worklogByEngineer[(int) $row->engineer_id] ?? 0),
                'effectiveness_score' => $effectivenessScore,
            ];
        })->sortByDesc('effectiveness_score')->values();

        if ($limit !== null) {
            return $mapped->take($limit)->values();
        }

        return $mapped;
    }

    private function recentTicketsForEngineer(User $engineer, CarbonImmutable $from, CarbonImmutable $to, array $filters = [], int $limit = 8): array
    {
        $query = Ticket::query()
            ->with(['status:id,name,code', 'priority:id,name'])
            ->where('assigned_engineer_id', $engineer->id)
            ->whereBetween('created_at', [$from, $to]);

        $this->applyTicketFilters($query, $filters);

        return $query->orderByDesc('updated_at')
            ->limit($limit)
            ->get()
            ->map(fn (Ticket $ticket): array => [
                'id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'title' => $ticket->title,
                'status_name' => $ticket->status?->name,
                'priority_name' => $ticket->priority?->name,
                'created_at' => $ticket->created_at,
                'started_at' => $ticket->started_at,
                'responded_at' => $ticket->responded_at,
                'completed_at' => $ticket->completed_at,
            ])
            ->all();
    }

    private function ticketBaseQuery(?User $actor, CarbonImmutable $from, CarbonImmutable $to, array $filters = []): Builder
    {
        $query = Ticket::query()->whereBetween('tickets.created_at', [$from, $to]);

        $this->applyTicketAccessScope($query, $actor);
        $this->applyTicketFilters($query, $filters);

        return $query;
    }

    private function reportStructure(?User $actor, CarbonImmutable $from, CarbonImmutable $to, array $filters = [], int $limit = 10): array
    {
        $baseQuery = $this->ticketBaseQuery($actor, $from, $to, $filters);

        $taxonomyBreakdown = (clone $baseQuery)
            ->leftJoin('ticket_categories', 'tickets.ticket_category_id', '=', 'ticket_categories.id')
            ->leftJoin('ticket_subcategories', 'tickets.ticket_subcategory_id', '=', 'ticket_subcategories.id')
            ->leftJoin('ticket_detail_subcategories', 'tickets.ticket_detail_subcategory_id', '=', 'ticket_detail_subcategories.id')
            ->selectRaw(
                'tickets.ticket_category_id,
                ticket_categories.name as ticket_type_name,
                tickets.ticket_subcategory_id,
                ticket_subcategories.name as ticket_category_name,
                tickets.ticket_detail_subcategory_id,
                ticket_detail_subcategories.name as ticket_sub_category_name,
                COUNT(*) as total_tickets,
                SUM(CASE WHEN tickets.completed_at IS NULL THEN 1 ELSE 0 END) as open_tickets,
                SUM(CASE WHEN tickets.completed_at IS NOT NULL THEN 1 ELSE 0 END) as completed_tickets'
            )
            ->groupBy(
                'tickets.ticket_category_id',
                'ticket_categories.name',
                'tickets.ticket_subcategory_id',
                'ticket_subcategories.name',
                'tickets.ticket_detail_subcategory_id',
                'ticket_detail_subcategories.name'
            )
            ->orderByDesc('total_tickets')
            ->limit($limit)
            ->get()
            ->map(fn (object $row): array => [
                'ticket_type_id' => $row->ticket_category_id ? (int) $row->ticket_category_id : null,
                'ticket_type_name' => $row->ticket_type_name ?? 'Unclassified Type',
                'ticket_category_id' => $row->ticket_subcategory_id ? (int) $row->ticket_subcategory_id : null,
                'ticket_category_name' => $row->ticket_category_name ?? 'Unclassified Category',
                'ticket_sub_category_id' => $row->ticket_detail_subcategory_id ? (int) $row->ticket_detail_subcategory_id : null,
                'ticket_sub_category_name' => $row->ticket_sub_category_name ?? 'Unclassified Sub Category',
                'total_tickets' => (int) $row->total_tickets,
                'open_tickets' => (int) $row->open_tickets,
                'completed_tickets' => (int) $row->completed_tickets,
            ])
            ->all();

        $statusDistribution = (clone $baseQuery)
            ->leftJoin('ticket_statuses', 'tickets.ticket_status_id', '=', 'ticket_statuses.id')
            ->selectRaw('ticket_statuses.name as status_name, COUNT(*) as total_tickets')
            ->groupBy('ticket_statuses.name')
            ->orderByDesc('total_tickets')
            ->get()
            ->map(fn (object $row): array => [
                'status_name' => $row->status_name ?? 'Unassigned Status',
                'total_tickets' => (int) $row->total_tickets,
            ])
            ->all();

        $priorityDistribution = (clone $baseQuery)
            ->leftJoin('ticket_priorities', 'tickets.ticket_priority_id', '=', 'ticket_priorities.id')
            ->selectRaw('ticket_priorities.name as priority_name, COUNT(*) as total_tickets')
            ->groupBy('ticket_priorities.name', 'ticket_priorities.level')
            ->orderBy('ticket_priorities.level')
            ->orderByDesc('total_tickets')
            ->get()
            ->map(fn (object $row): array => [
                'priority_name' => $row->priority_name ?? 'No Priority',
                'total_tickets' => (int) $row->total_tickets,
            ])
            ->all();

        return [
            'taxonomy_breakdown' => $taxonomyBreakdown,
            'status_distribution' => $statusDistribution,
            'priority_distribution' => $priorityDistribution,
            'query_dimensions' => [
                'date_from',
                'date_to',
                'ticket_type',
                'ticket_category',
                'ticket_sub_category',
                'approval_status',
                'expected_approver',
                'expected_approver_role',
                'status',
                'priority',
                'assigned_engineer',
                'department',
                'service',
                'asset',
            ],
            'recommended_grouping' => [
                'ticket_type_name',
                'ticket_category_name',
                'ticket_sub_category_name',
            ],
        ];
    }

    private function applyTicketAccessScope(Builder $query, ?User $actor): void
    {
        if ($actor === null) {
            $query->whereRaw('1 = 0');
            return;
        }

        if ($actor->hasPermission('ticket.view_all')) {
            return;
        }

        $query->where(function (Builder $scopedQuery) use ($actor): void {
            $hasScope = false;

            if ($actor->hasPermission('ticket.view_department') && $actor->department_id !== null) {
                $scopedQuery->orWhere('tickets.requester_department_id', $actor->department_id);
                $hasScope = true;
            }

            if ($actor->hasPermission('ticket.view_assigned')) {
                $scopedQuery->orWhere('tickets.assigned_engineer_id', $actor->id);
                $hasScope = true;
            }

            if ($actor->hasPermission('ticket.view_own')) {
                $scopedQuery->orWhere('tickets.requester_id', $actor->id);
                $hasScope = true;
            }

            if (! $hasScope) {
                $scopedQuery->whereRaw('1 = 0');
            }
        });
    }

    private function applyTicketFilters(Builder $query, array $filters): void
    {
        if (($filters['ticket_category_id'] ?? null) !== null && $filters['ticket_category_id'] !== '') {
            $query->where('tickets.ticket_category_id', $filters['ticket_category_id']);
        }

        if (($filters['ticket_subcategory_id'] ?? null) !== null && $filters['ticket_subcategory_id'] !== '') {
            $query->where('tickets.ticket_subcategory_id', $filters['ticket_subcategory_id']);
        }

        if (($filters['ticket_detail_subcategory_id'] ?? null) !== null && $filters['ticket_detail_subcategory_id'] !== '') {
            $query->where('tickets.ticket_detail_subcategory_id', $filters['ticket_detail_subcategory_id']);
        }

        if (($filters['expected_approver_id'] ?? null) !== null && $filters['expected_approver_id'] !== '') {
            $query->where('tickets.expected_approver_id', $filters['expected_approver_id']);
        }

        if (($filters['expected_approver_role_code'] ?? null) !== null && $filters['expected_approver_role_code'] !== '') {
            $query->where('tickets.expected_approver_role_code', $filters['expected_approver_role_code']);
        }

        if (($filters['approval_status'] ?? null) !== null && $filters['approval_status'] !== '') {
            $query->where('tickets.approval_status', $filters['approval_status']);
        }
    }

    private function applyInspectionAccessScope(Builder $query, ?User $actor): void
    {
        if ($actor === null) {
            return;
        }

        if ($actor->hasPermission('dashboard.view_ops')) {
            return;
        }

        if ($actor->hasAnyPermission(['inspection_task.view_assigned', 'inspection_result.view_assigned'])) {
            $query->where('inspection_officer_id', $actor->id);

            return;
        }

        $query->whereRaw('1 = 0');
    }

    private function resolvePeriod(array $filters): array
    {
        $now = CarbonImmutable::now();

        $from = isset($filters['date_from']) && $filters['date_from'] !== ''
            ? CarbonImmutable::parse($filters['date_from'])->startOfDay()
            : $now->subDays(29)->startOfDay();

        $to = isset($filters['date_to']) && $filters['date_to'] !== ''
            ? CarbonImmutable::parse($filters['date_to'])->endOfDay()
            : $now->endOfDay();

        if ($from->gt($to)) {
            [$from, $to] = [$to->startOfDay(), $from->endOfDay()];
        }

        return [$from, $to];
    }

    private function serializePeriod(CarbonImmutable $from, CarbonImmutable $to): array
    {
        return [
            'date_from' => $from->toDateString(),
            'date_to' => $to->toDateString(),
        ];
    }

    private function percentage(int|float $numerator, int|float $denominator): float
    {
        if ($denominator <= 0) {
            return 0.0;
        }

        return round(($numerator / $denominator) * 100, 2);
    }
}
