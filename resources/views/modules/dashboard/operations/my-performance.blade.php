@extends('layouts.vertical', ['subtitle' => 'My Scoring Report'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Engineer', 'subtitle' => 'My Scoring Report'])

    @php
        $engineer = $data['engineer'];
        $sla = $data['sla'];
        $scoreReports = collect($data['score_reports'] ?? []);
    @endphp

    <div class="card">
        <div class="card-body">
            @include('modules.dashboard.operations.partials.filter', ['routeName' => 'engineer-performance'])
        </div>
    </div>

    @if ($engineer !== null)
        <div class="row">
            <div class="col-md-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <p class="text-muted mb-1">Assigned Tickets</p>
                        <h3 class="mb-0">{{ number_format($engineer['assigned_tickets'], 2) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <p class="text-muted mb-1">Completed Tickets</p>
                        <h3 class="mb-0">{{ number_format($engineer['completed_tickets'], 2) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <p class="text-muted mb-1">Resolution SLA</p>
                        <h3 class="mb-0">{{ number_format($engineer['resolution_compliance_rate'], 2) }}%</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <p class="text-muted mb-1">Effectiveness Score</p>
                        <h3 class="mb-0">{{ number_format($engineer['effectiveness_score'], 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">My SLA Snapshot</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-1">Response Compliance</p>
                        <h5>{{ number_format($sla['response']['compliance_rate'], 2) }}%</h5>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1">Resolution Compliance</p>
                        <h5>{{ number_format($sla['resolution']['compliance_rate'], 2) }}%</h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">My Scoring Report</h4>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach ($scoreReports as $report)
                        @php
                            $stats = $report['stats'];
                        @endphp
                        <div class="col-md-4">
                            <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                                    <div>
                                        <div class="text-muted small text-uppercase fw-semibold">{{ $report['label'] }}</div>
                                        <div class="small text-muted">
                                            {{ \Carbon\Carbon::parse($report['period']['date_from'])->format('d M Y') }}
                                            -
                                            {{ \Carbon\Carbon::parse($report['period']['date_to'])->format('d M Y') }}
                                        </div>
                                    </div>
                                    <span class="badge bg-primary-subtle text-primary">
                                        {{ $stats ? number_format($stats['effectiveness_score'], 2) : '0.00' }}
                                    </span>
                                </div>
                                <div class="row g-2 small">
                                    <div class="col-6">
                                        <div class="text-muted">Assigned Share</div>
                                        <div class="fw-semibold">{{ $stats ? number_format($stats['assigned_tickets'], 2) : '0.00' }}</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-muted">Completed Share</div>
                                        <div class="fw-semibold">{{ $stats ? number_format($stats['completed_tickets'], 2) : '0.00' }}</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-muted">Response SLA</div>
                                        <div class="fw-semibold">{{ $stats ? number_format($stats['response_compliance_rate'], 2) : '0.00' }}%</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-muted">Resolution SLA</div>
                                        <div class="fw-semibold">{{ $stats ? number_format($stats['resolution_compliance_rate'], 2) : '0.00' }}%</div>
                                    </div>
                                </div>
                                <div class="small text-muted mt-3">Ticket dengan multiple engineer dihitung memakai pembagian score rata.</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Recent Assigned Tickets</h4>
            </div>
            <div class="card-body table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Ticket</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data['recent_tickets'] as $ticket)
                            <tr>
                                <td>
                                    <div>{{ $ticket['ticket_number'] }}</div>
                                    <small class="text-muted">{{ $ticket['title'] }}</small>
                                </td>
                                <td>{{ $ticket['status_name'] ?? '-' }}</td>
                                <td>{{ $ticket['priority_name'] ?? '-' }}</td>
                                <td>{{ optional($ticket['created_at'])->format('Y-m-d H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">No recent ticket in selected period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-body">
                <p class="text-muted mb-0">No engineer activity found in selected period.</p>
            </div>
        </div>
    @endif
@endsection
