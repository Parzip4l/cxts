@extends('layouts.vertical', ['subtitle' => 'Engineer Effectiveness'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Operations', 'subtitle' => 'Engineer Effectiveness'])

    @php
        $summary = $data['summary'];
    @endphp

    <div class="card">
        <div class="card-body">
            @include('modules.dashboard.operations.partials.filter', ['routeName' => 'dashboard.engineer-effectiveness'])
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-1">Engineer Count</p>
                    <h3 class="mb-0">{{ number_format($summary['engineer_count']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-1">Assigned Tickets</p>
                    <h3 class="mb-0">{{ number_format($summary['total_assigned_tickets']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-1">Completion Rate</p>
                    <h3 class="mb-0">{{ number_format($summary['overall_completion_rate'], 2) }}%</h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-1">Avg Effectiveness Score</p>
                    <h3 class="mb-0">{{ number_format($summary['avg_effectiveness_score'], 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Effectiveness Score by Engineer</h4>
                </div>
                <div class="card-body">
                    <div id="eng-effectiveness-score-chart" class="apex-charts"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Completed vs Open Ticket</h4>
                </div>
                <div class="card-body">
                    <div id="eng-ticket-outcome-chart" class="apex-charts"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">SLA Compliance Radar</h4>
                </div>
                <div class="card-body">
                    <div id="eng-sla-compliance-chart" class="apex-charts"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Response vs Resolution Scatter</h4>
                </div>
                <div class="card-body">
                    <div id="eng-time-scatter-chart" class="apex-charts"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h4 class="card-title mb-0">Engineer Performance Table</h4>
        </div>
        <div class="card-body table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Engineer</th>
                        <th>Assigned</th>
                        <th>Completed</th>
                        <th>Completion %</th>
                        <th>Response SLA %</th>
                        <th>Resolution SLA %</th>
                        <th>Avg Response (min)</th>
                        <th>Avg Resolution (min)</th>
                        <th>Worklog (min)</th>
                        <th>Score</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($data['engineers'] as $engineer)
                        <tr>
                            <td>
                                <div>{{ $engineer['engineer_name'] }}</div>
                                <small class="text-muted">{{ $engineer['department_name'] ?? '-' }}</small>
                            </td>
                            <td>{{ number_format($engineer['assigned_tickets']) }}</td>
                            <td>{{ number_format($engineer['completed_tickets']) }}</td>
                            <td>{{ number_format($engineer['completion_rate'], 2) }}%</td>
                            <td>{{ number_format($engineer['response_compliance_rate'], 2) }}%</td>
                            <td>{{ number_format($engineer['resolution_compliance_rate'], 2) }}%</td>
                            <td>{{ $engineer['avg_response_minutes'] !== null ? number_format($engineer['avg_response_minutes'], 2) : '-' }}</td>
                            <td>{{ $engineer['avg_resolution_minutes'] !== null ? number_format($engineer['avg_resolution_minutes'], 2) : '-' }}</td>
                            <td>{{ number_format($engineer['total_worklog_minutes']) }}</td>
                            <td><span class="badge bg-primary-subtle text-primary">{{ number_format($engineer['effectiveness_score'], 2) }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-3">No engineering effectiveness data in selected period.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        window.operationsDashboardPayload = {
            page: 'engineer-effectiveness',
            engineerEffectiveness: @json($data),
        };
    </script>
    @vite(['resources/js/pages/operations-charts.js'])
@endpush
