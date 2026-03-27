@extends('layouts.vertical', ['subtitle' => 'SLA Performance'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Operations', 'subtitle' => 'SLA Performance'])

    @php
        $summary = $data['summary'];
    @endphp

    <div class="card">
        <div class="card-body">
            @include('modules.dashboard.operations.partials.filter', ['routeName' => 'dashboard.sla-performance'])
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-1">Response SLA Compliance</p>
                    <h3 class="mb-0">{{ number_format($summary['response']['compliance_rate'], 2) }}%</h3>
                    <small class="text-muted">On time {{ number_format($summary['response']['on_time']) }} / Breached {{ number_format($summary['response']['breached']) }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-1">Resolution SLA Compliance</p>
                    <h3 class="mb-0">{{ number_format($summary['resolution']['compliance_rate'], 2) }}%</h3>
                    <small class="text-muted">On time {{ number_format($summary['resolution']['on_time']) }} / Breached {{ number_format($summary['resolution']['breached']) }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-1">Response Pending</p>
                    <h3 class="mb-0">{{ number_format($summary['response']['pending']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-1">Resolution Pending</p>
                    <h3 class="mb-0">{{ number_format($summary['resolution']['pending']) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-7">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Daily Breach Trend (Chart)</h4>
                </div>
                <div class="card-body">
                    <div id="sla-breach-trend-chart" class="apex-charts"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Late Minutes by Ticket (Chart)</h4>
                </div>
                <div class="card-body">
                    <div id="sla-late-minutes-chart" class="apex-charts"></div>
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
                    <div id="sla-response-breakdown-chart" class="apex-charts"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Resolution SLA Breakdown</h4>
                </div>
                <div class="card-body">
                    <div id="sla-resolution-breakdown-chart" class="apex-charts"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h4 class="card-title mb-0">Breach Ticket List</h4>
        </div>
        <div class="card-body table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Ticket</th>
                        <th>Engineer</th>
                        <th>Status</th>
                        <th>Late Details</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($data['breach_tickets'] as $ticket)
                        <tr>
                            <td>
                                <div>{{ $ticket['ticket_number'] }}</div>
                                <small class="text-muted">{{ $ticket['title'] }}</small>
                            </td>
                            <td>{{ $ticket['assigned_engineer_name'] ?? '-' }}</td>
                            <td>{{ $ticket['status_name'] ?? '-' }}</td>
                            <td>
                                @if ($ticket['response_breached'])
                                    <div class="text-warning">Response late {{ number_format($ticket['response_late_minutes'] ?? 0) }} min</div>
                                @endif
                                @if ($ticket['resolution_breached'])
                                    <div class="text-danger">Resolution late {{ number_format($ticket['resolution_late_minutes'] ?? 0) }} min</div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">No breached tickets in selected period.</td>
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
            page: 'sla-performance',
            slaPerformance: @json($data),
        };
    </script>
    @vite(['resources/js/pages/operations-charts.js'])
@endpush
