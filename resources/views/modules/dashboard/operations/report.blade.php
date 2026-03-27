@extends('layouts.vertical', ['subtitle' => 'Executive Report'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Operations', 'subtitle' => 'Executive Report'])

    @php
        $current = $data['current'];
        $comparisons = collect($data['comparisons']);
        $summary = $data['executive_summary'];
        $actionPlan = collect($data['action_plan'] ?? []);
        $topRisks = collect($data['top_risks'] ?? []);
        $topImprovementAreas = collect($data['top_improvement_areas'] ?? []);
        $primaryComparison = $comparisons->firstWhere('key', $data['primary_comparison_key']) ?? $comparisons->first();

        $toneBadgeClass = fn ($tone) => match ($tone) {
            'improved', 'success' => 'bg-success-subtle text-success',
            'declined', 'danger', 'immediate' => 'bg-danger-subtle text-danger',
            'mixed', 'warning', 'high' => 'bg-warning-subtle text-warning',
            'medium' => 'bg-info-subtle text-info',
            'maintain', 'stable' => 'bg-secondary-subtle text-secondary',
            'critical' => 'bg-danger-subtle text-danger',
            'low' => 'bg-secondary-subtle text-secondary',
            default => 'bg-secondary-subtle text-secondary',
        };

        $deltaClass = fn ($direction, $higherIsBetter = true) => match ($direction) {
            'up' => $higherIsBetter ? 'text-success' : 'text-danger',
            'down' => $higherIsBetter ? 'text-danger' : 'text-success',
            default => 'text-muted',
        };

        $deltaIcon = fn ($direction) => match ($direction) {
            'up' => 'solar:arrow-up-outline',
            'down' => 'solar:arrow-down-outline',
            default => 'solar:minus-outline',
        };

        $metrics = [
            [
                'label' => 'Total Tickets',
                'current' => $current['summary']['total_tickets'],
                'key' => 'ticket_volume',
                'format' => fn ($value) => number_format($value),
                'higher_is_better' => false,
            ],
            [
                'label' => 'Completion Rate',
                'current' => $current['derived']['completion_rate'],
                'key' => 'completion_rate',
                'format' => fn ($value) => number_format($value, 2) . '%',
                'higher_is_better' => true,
            ],
            [
                'label' => 'Response SLA',
                'current' => $current['sla']['response']['compliance_rate'],
                'key' => 'response_compliance',
                'format' => fn ($value) => number_format($value, 2) . '%',
                'higher_is_better' => true,
            ],
            [
                'label' => 'Resolution SLA',
                'current' => $current['sla']['resolution']['compliance_rate'],
                'key' => 'resolution_compliance',
                'format' => fn ($value) => number_format($value, 2) . '%',
                'higher_is_better' => true,
            ],
            [
                'label' => 'Engineer Effectiveness',
                'current' => $current['engineer']['avg_effectiveness_score'],
                'key' => 'avg_effectiveness_score',
                'format' => fn ($value) => number_format($value, 2),
                'higher_is_better' => true,
            ],
            [
                'label' => 'Abnormal Inspections',
                'current' => $current['inspection']['abnormal_inspections'],
                'key' => 'abnormal_inspections',
                'format' => fn ($value) => number_format($value),
                'higher_is_better' => false,
            ],
        ];
    @endphp

    <div class="report-page">
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 pt-4 pb-0">
            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                <div>
                    <h5 class="mb-1">Executive Report Filters</h5>
                </div>
                <span class="badge bg-primary-subtle text-primary">Board View</span>
            </div>
        </div>
        <div class="card-body pt-3">
            @include('modules.dashboard.operations.partials.filter', ['routeName' => 'dashboard.report'])
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4 report-hero-card">
        <div class="card-body p-4">
            <div class="row g-4 align-items-start">
                <div class="col-xl-7">
                    <div class="small text-uppercase fw-semibold text-muted mb-2">Executive Summary Otomatis</div>
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                        <h3 class="mb-0">{{ $summary['headline'] }}</h3>
                        <span class="badge {{ $toneBadgeClass($summary['tone']) }}">{{ str($summary['tone'])->title() }}</span>
                    </div>
                    <p class="text-muted mb-3">
                        Periode aktif: {{ \Carbon\Carbon::parse($current['period']['date_from'])->format('d M Y') }}
                        to {{ \Carbon\Carbon::parse($current['period']['date_to'])->format('d M Y') }}.
                        Benchmark utama:
                        {{ \Carbon\Carbon::parse($primaryComparison['period']['date_from'])->format('d M Y') }}
                        to {{ \Carbon\Carbon::parse($primaryComparison['period']['date_to'])->format('d M Y') }}.
                    </p>
                    <div class="row g-3">
                        @foreach ($summary['highlights'] as $highlight)
                            <div class="col-md-6">
                                <div class="rounded-3 border bg-white p-3 h-100 report-mini-card">
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                        <div class="fw-semibold">{{ $highlight['title'] }}</div>
                                        <span class="badge {{ $toneBadgeClass($highlight['tone']) }}">{{ str($highlight['tone'])->title() }}</span>
                                    </div>
                                    <div class="small text-muted">{{ $highlight['message'] }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="col-xl-5">
                    <div class="rounded-4 border bg-white p-4 h-100 report-mini-card">
                        <div class="small text-uppercase fw-semibold text-muted mb-3">Snapshot Kualitas Saat Ini</div>
                        <div class="d-flex flex-column gap-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Ticket Volume</span>
                                <span class="fw-semibold">{{ number_format($current['summary']['total_tickets']) }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Completion Rate</span>
                                <span class="fw-semibold">{{ number_format($current['derived']['completion_rate'], 2) }}%</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Response SLA</span>
                                <span class="fw-semibold">{{ number_format($current['sla']['response']['compliance_rate'], 2) }}%</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Resolution SLA</span>
                                <span class="fw-semibold">{{ number_format($current['sla']['resolution']['compliance_rate'], 2) }}%</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Engineer Effectiveness</span>
                                <span class="fw-semibold">{{ number_format($current['engineer']['avg_effectiveness_score'], 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Abnormal Inspections</span>
                                <span class="fw-semibold">{{ number_format($current['inspection']['abnormal_inspections']) }}</span>
                            </div>
                        </div>
                        <div class="small text-muted mt-3 mb-0">{{ $summary['note'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 pb-0">
            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                <div>
                    <h4 class="card-title mb-1">Recommended Action Plan</h4>
                    <small class="text-muted">Prioritas tindakan otomatis yang disusun dari perubahan SLA, demand, inspection, dan efektivitas engineer.</small>
                </div>
                <span class="badge bg-light text-dark border">{{ $actionPlan->count() }} aksi</span>
            </div>
        </div>
        <div class="card-body pt-3">
            <div class="row g-3">
                @foreach ($actionPlan as $action)
                    <div class="col-xl-6">
                        <div class="rounded-3 border bg-white p-3 h-100 report-mini-card">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <div class="fw-semibold">{{ $action['title'] }}</div>
                                <span class="badge {{ $toneBadgeClass(strtolower($action['priority'])) }}">{{ $action['priority'] }}</span>
                            </div>
                            <div class="small text-muted mb-3">{{ $action['message'] }}</div>
                            <div class="d-flex flex-wrap gap-2 small">
                                <span class="badge bg-light text-dark border">Owner: {{ $action['owner'] }}</span>
                                <span class="badge bg-light text-dark border">Target: {{ $action['timeframe'] }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm h-100 dashboard-section-card">
                <div class="card-header bg-transparent border-0 pb-0 dashboard-section-header">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div>
                            <h4 class="card-title mb-1">Top 5 Risks</h4>
                            <small class="text-muted">Area risiko paling penting yang perlu dimonitor berdasarkan kondisi periode aktif dan baseline utama.</small>
                        </div>
                        <span class="badge bg-light text-dark border">{{ $topRisks->count() }} risiko</span>
                    </div>
                </div>
                <div class="card-body pt-3">
                    <div class="d-flex flex-column gap-3">
                        @foreach ($topRisks as $risk)
                            <div class="rounded-3 border bg-white p-3 report-mini-card">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                    <div class="fw-semibold">{{ $risk['title'] }}</div>
                                    <span class="badge {{ $toneBadgeClass(strtolower($risk['severity'])) }}">{{ $risk['severity'] }}</span>
                                </div>
                                <div class="small text-muted">{{ $risk['message'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm h-100 dashboard-section-card">
                <div class="card-header bg-transparent border-0 pb-0 dashboard-section-header">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div>
                            <h4 class="card-title mb-1">Top 5 Improvement Areas</h4>
                            <small class="text-muted">Area dengan dampak peningkatan paling besar jika ditangani lebih dulu pada siklus operasional berikutnya.</small>
                        </div>
                        <span class="badge bg-light text-dark border">{{ $topImprovementAreas->count() }} area</span>
                    </div>
                </div>
                <div class="card-body pt-3">
                    <div class="d-flex flex-column gap-3">
                        @foreach ($topImprovementAreas as $area)
                            <div class="rounded-3 border bg-white p-3 report-mini-card">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                    <div class="fw-semibold">{{ $area['title'] }}</div>
                                    <span class="badge {{ $toneBadgeClass(strtolower($area['priority'])) }}">{{ $area['priority'] }}</span>
                                </div>
                                <div class="small text-muted">{{ $area['message'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-7">
            <div class="card border-0 shadow-sm dashboard-section-card h-100">
                <div class="card-header bg-transparent border-0 pb-0 dashboard-section-header">
                    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                        <div>
                            <h4 class="card-title mb-1">Perbandingan Benchmark Kualitas</h4>
                            <small class="text-muted">Membandingkan KPI kualitas utama terhadap baseline 7 hari, 1 bulan, dan 1 tahun sebelumnya.</small>
                        </div>
                        <span class="badge bg-light text-dark border">Comparative KPI</span>
                    </div>
                </div>
                <div class="card-body pt-3">
                    <div id="executive-report-quality-chart" class="apex-charts"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="card border-0 shadow-sm dashboard-section-card h-100">
                <div class="card-header bg-transparent border-0 pb-0 dashboard-section-header">
                    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                        <div>
                            <h4 class="card-title mb-1">Perbandingan Tekanan Operasional</h4>
                            <small class="text-muted">Melihat tekanan operasional lintas baseline dari volume, overdue, unassigned, dan abnormal findings.</small>
                        </div>
                        <span class="badge bg-light text-dark border">Pressure View</span>
                    </div>
                </div>
                <div class="card-body pt-3">
                    <div id="executive-report-pressure-chart" class="apex-charts"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        @foreach ($comparisons as $comparison)
            @php
                $ticketDelta = $comparison['delta']['ticket_volume'];
                $responseDelta = $comparison['delta']['response_compliance'];
                $resolutionDelta = $comparison['delta']['resolution_compliance'];
            @endphp
            <div class="col-md-6 col-xl-4">
                <div class="card border-0 shadow-sm h-100 comparison-card">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                            <div>
                                <div class="small text-uppercase fw-semibold text-muted mb-2">{{ $comparison['label'] }}</div>
                                <div class="fw-semibold">
                                    {{ \Carbon\Carbon::parse($comparison['period']['date_from'])->format('d M Y') }}
                                    - {{ \Carbon\Carbon::parse($comparison['period']['date_to'])->format('d M Y') }}
                                </div>
                            </div>
                            <span class="badge {{ $toneBadgeClass($comparison['status']) }}">{{ str($comparison['status'])->title() }}</span>
                        </div>
                        <div class="d-flex flex-column gap-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Ticket Volume</span>
                                <span class="{{ $deltaClass($ticketDelta['direction'], false) }}">
                                    <iconify-icon icon="{{ $deltaIcon($ticketDelta['direction']) }}" class="me-1"></iconify-icon>
                                    {{ $ticketDelta['change'] > 0 ? '+' : '' }}{{ number_format($ticketDelta['change']) }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Response SLA</span>
                                <span class="{{ $deltaClass($responseDelta['direction']) }}">
                                    <iconify-icon icon="{{ $deltaIcon($responseDelta['direction']) }}" class="me-1"></iconify-icon>
                                    {{ $responseDelta['change'] > 0 ? '+' : '' }}{{ number_format($responseDelta['change'], 2) }} pt
                                </span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Resolution SLA</span>
                                <span class="{{ $deltaClass($resolutionDelta['direction']) }}">
                                    <iconify-icon icon="{{ $deltaIcon($resolutionDelta['direction']) }}" class="me-1"></iconify-icon>
                                    {{ $resolutionDelta['change'] > 0 ? '+' : '' }}{{ number_format($resolutionDelta['change'], 2) }} pt
                                </span>
                            </div>
                        </div>
                        @if (!empty($comparison['drivers']))
                            <div class="mt-3 pt-3 border-top">
                                <div class="small text-muted mb-2">Sorotan utama</div>
                                <div class="small">{{ $comparison['drivers'][0]['message'] }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 pb-0">
            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                <div>
                    <h4 class="card-title mb-1">Comparative Metric Table</h4>
                    <small class="text-muted">Membandingkan kualitas operasional dan efektivitas terhadap baseline 7 hari, 1 bulan, dan 1 tahun sebelumnya.</small>
                </div>
                <span class="badge bg-light text-dark border">{{ count($metrics) }} metrik</span>
            </div>
        </div>
        <div class="card-body pt-3">
            <div class="table-responsive dashboard-table-shell">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Metric</th>
                            <th>Periode Aktif</th>
                            @foreach ($comparisons as $comparison)
                                <th>{{ $comparison['label'] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($metrics as $metric)
                            <tr>
                                <td class="fw-semibold">{{ $metric['label'] }}</td>
                                <td>{{ $metric['format']($metric['current']) }}</td>
                                @foreach ($comparisons as $comparison)
                                    @php
                                        $delta = $comparison['delta'][$metric['key']];
                                    @endphp
                                    <td>
                                        <div>{{ $metric['format']($delta['previous']) }}</div>
                                        <div class="small {{ $deltaClass($delta['direction'], $metric['higher_is_better']) }}">
                                            <iconify-icon icon="{{ $deltaIcon($delta['direction']) }}" class="me-1"></iconify-icon>
                                            {{ $delta['change'] > 0 ? '+' : '' }}{{ $metric['format']($delta['change']) }}
                                        </div>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        @foreach ($comparisons as $comparison)
            <div class="col-xl-4">
                <div class="card border-0 shadow-sm h-100 dashboard-section-card">
                    <div class="card-header bg-transparent border-0 pb-0 dashboard-section-header">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <h5 class="mb-1">{{ $comparison['label'] }}</h5>
                                <small class="text-muted">Penjelasan otomatis berdasarkan perubahan metrik aktual pada baseline ini.</small>
                            </div>
                            <span class="badge {{ $toneBadgeClass($comparison['status']) }}">{{ str($comparison['status'])->title() }}</span>
                        </div>
                    </div>
                    <div class="card-body pt-3">
                        <div class="d-flex flex-column gap-3">
                            @foreach ($comparison['drivers'] as $driver)
                                <div class="rounded-3 border bg-light-subtle p-3 report-mini-card">
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                        <div class="fw-semibold">{{ $driver['title'] }}</div>
                                        <span class="badge {{ $toneBadgeClass($driver['tone']) }}">{{ str($driver['tone'])->title() }}</span>
                                    </div>
                                    <div class="small text-muted">{{ $driver['message'] }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    </div>
@endsection

@push('styles')
    <style>
        .report-page .card,
        .report-page .report-hero-card,
        .report-page .comparison-card,
        .report-page .dashboard-section-card {
            background: #fff !important;
        }

        .report-mini-card {
            border-color: rgba(148, 163, 184, 0.18) !important;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.45);
        }

        .report-hero-card .card-body,
        .comparison-card .card-body {
            padding: 1.5rem !important;
        }
    </style>
@endpush

@push('scripts')
    <script>
        window.operationsDashboardPayload = {
            page: 'executive-report',
            executiveReport: @json($data),
        };
    </script>
    @vite(['resources/js/pages/operations-charts.js'])
@endpush
