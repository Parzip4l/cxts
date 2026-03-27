@extends('layouts.vertical', ['subtitle' => 'Dashboard'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Operations', 'subtitle' => 'Dashboard'])

    @php
        $ticketSummary = $overview['summary'];
        $slaSummary = $overview['sla'];
        $inspectionSummary = $overview['inspection_summary'];
        $reportStructure = $overview['report_structure'];
    @endphp

    <div class="card">
        <div class="card-body">
            @include('modules.dashboard.operations.partials.filter', ['routeName' => 'dashboard'])
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-1">Total Tickets</p>
                    <h3 class="mb-0">{{ number_format($ticketSummary['total_tickets']) }}</h3>
                    <small class="text-muted">Open: {{ number_format($ticketSummary['open_tickets']) }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-1">SLA Response</p>
                    <h3 class="mb-0">{{ number_format($slaSummary['response']['compliance_rate'], 2) }}%</h3>
                    <small class="text-danger">Breached: {{ number_format($slaSummary['response']['breached']) }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-1">SLA Resolution</p>
                    <h3 class="mb-0">{{ number_format($slaSummary['resolution']['compliance_rate'], 2) }}%</h3>
                    <small class="text-danger">Breached: {{ number_format($slaSummary['resolution']['breached']) }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-1">Inspections Submitted</p>
                    <h3 class="mb-0">{{ number_format($inspectionSummary['submitted_inspections']) }}</h3>
                    <small class="text-muted">Normal: {{ number_format($inspectionSummary['normal_inspections']) }}</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Ticket Daily Trend (Chart)</h4>
                </div>
                <div class="card-body">
                    <div id="ops-daily-trend-chart" class="apex-charts"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Inspection Normality Rate</h4>
                </div>
                <div class="card-body">
                    <div id="ops-inspection-result-chart" class="apex-charts"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Response SLA Breakdown</h4>
                </div>
                <div class="card-body">
                    <div id="ops-sla-response-chart" class="apex-charts"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Resolution SLA Breakdown</h4>
                </div>
                <div class="card-body">
                    <div id="ops-sla-resolution-chart" class="apex-charts"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-7">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title mb-0">Taxonomy Breakdown</h4>
                        <small class="text-muted">Enterprise-friendly grouping by Ticket Type, Category, and Sub Category.</small>
                    </div>
                    <span class="badge bg-primary-subtle text-primary">{{ count($reportStructure['taxonomy_breakdown']) }} rows</span>
                </div>
                <div class="card-body table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Ticket Type</th>
                                <th>Ticket Category</th>
                                <th>Ticket Sub Category</th>
                                <th class="text-center">Total</th>
                                <th class="text-center">Open</th>
                                <th class="text-center">Completed</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($reportStructure['taxonomy_breakdown'] as $row)
                                <tr>
                                    <td>{{ $row['ticket_type_name'] }}</td>
                                    <td>{{ $row['ticket_category_name'] }}</td>
                                    <td>{{ $row['ticket_sub_category_name'] }}</td>
                                    <td class="text-center">{{ number_format($row['total_tickets']) }}</td>
                                    <td class="text-center">{{ number_format($row['open_tickets']) }}</td>
                                    <td class="text-center">{{ number_format($row['completed_tickets']) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-3">No taxonomy breakdown found for selected filter.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="card h-100">
                <div class="card-header">
                    <h4 class="card-title mb-0">Reporting Query Structure</h4>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Gunakan dimensi berikut sebagai fondasi query report agar dashboard, SLA, dan trend memakai basis klasifikasi yang sama.</p>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        @foreach ($reportStructure['query_dimensions'] as $dimension)
                            <span class="badge bg-secondary-subtle text-secondary">{{ str($dimension)->replace('_', ' ')->title() }}</span>
                        @endforeach
                    </div>
                    <p class="text-muted mb-2">Recommended group by:</p>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        @foreach ($reportStructure['recommended_grouping'] as $group)
                            <span class="badge bg-primary-subtle text-primary">{{ str($group)->replace('_', ' ')->title() }}</span>
                        @endforeach
                    </div>
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="border rounded-3 p-3">
                                <h6 class="mb-2">Status Distribution</h6>
                                @forelse ($reportStructure['status_distribution'] as $status)
                                    <div class="d-flex justify-content-between align-items-center py-1">
                                        <span>{{ $status['status_name'] }}</span>
                                        <span class="badge bg-light text-dark">{{ number_format($status['total_tickets']) }}</span>
                                    </div>
                                @empty
                                    <p class="text-muted mb-0">No status data for selected filter.</p>
                                @endforelse
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="border rounded-3 p-3">
                                <h6 class="mb-2">Priority Distribution</h6>
                                @forelse ($reportStructure['priority_distribution'] as $priority)
                                    <div class="d-flex justify-content-between align-items-center py-1">
                                        <span>{{ $priority['priority_name'] }}</span>
                                        <span class="badge bg-light text-dark">{{ number_format($priority['total_tickets']) }}</span>
                                    </div>
                                @empty
                                    <p class="text-muted mb-0">No priority data for selected filter.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($isOpsRole)
        <div class="row">
            <div class="col-xl-7">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Top Engineer Effectiveness (Chart)</h4>
                        <a href="{{ route('dashboard.engineer-effectiveness', request()->query()) }}" class="btn btn-sm btn-outline-primary">View Detail</a>
                    </div>
                    <div class="card-body">
                        <div id="ops-top-engineer-score-chart" class="apex-charts"></div>
                    </div>
                </div>
            </div>

            <div class="col-xl-5">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">SLA Breach Alerts</h4>
                        <a href="{{ route('dashboard.sla-performance', request()->query()) }}" class="btn btn-sm btn-outline-primary">View Detail</a>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Ticket</th>
                                    <th>Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($slaPerformance['breach_tickets'] as $ticket)
                                    <tr>
                                        <td>
                                            <div>{{ $ticket['ticket_number'] }}</div>
                                            <small class="text-muted">{{ $ticket['title'] }}</small>
                                        </td>
                                        <td>
                                            @if ($ticket['response_breached'])
                                                <span class="badge bg-warning-subtle text-warning">Response</span>
                                            @endif
                                            @if ($ticket['resolution_breached'])
                                                <span class="badge bg-danger-subtle text-danger">Resolution</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted py-3">No SLA breach in selected period.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @elseif (auth()->user()?->role === 'engineer' && $myPerformance !== null)
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">My Engineering Effectiveness</h4>
            </div>
            <div class="card-body">
                @if ($myPerformance['engineer'] !== null)
                    <div class="row">
                        <div class="col-md-3">
                            <p class="text-muted mb-1">Assigned</p>
                            <h4>{{ number_format($myPerformance['engineer']['assigned_tickets']) }}</h4>
                        </div>
                        <div class="col-md-3">
                            <p class="text-muted mb-1">Completed</p>
                            <h4>{{ number_format($myPerformance['engineer']['completed_tickets']) }}</h4>
                        </div>
                        <div class="col-md-3">
                            <p class="text-muted mb-1">Resolution SLA</p>
                            <h4>{{ number_format($myPerformance['engineer']['resolution_compliance_rate'], 2) }}%</h4>
                        </div>
                        <div class="col-md-3">
                            <p class="text-muted mb-1">Effectiveness Score</p>
                            <h4>{{ number_format($myPerformance['engineer']['effectiveness_score'], 2) }}</h4>
                        </div>
                    </div>
                @else
                    <p class="text-muted mb-0">No assigned ticket in selected period.</p>
                @endif
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        window.operationsDashboardPayload = {
            page: 'operations-dashboard',
            overview: @json($overview),
            slaPerformance: @json($slaPerformance),
            engineerEffectiveness: @json($engineerEffectiveness),
        };
    </script>
    @vite(['resources/js/pages/operations-charts.js'])
@endpush
