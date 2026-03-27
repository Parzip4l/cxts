@extends('layouts.vertical', ['subtitle' => 'My Performance'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Engineer', 'subtitle' => 'My Performance'])

    @php
        $engineer = $data['engineer'];
        $sla = $data['sla'];
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
                        <h3 class="mb-0">{{ number_format($engineer['assigned_tickets']) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <p class="text-muted mb-1">Completed Tickets</p>
                        <h3 class="mb-0">{{ number_format($engineer['completed_tickets']) }}</h3>
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
