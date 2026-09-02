@extends('layouts.vertical', ['subtitle' => 'Monitoring Events'])

@section('content')
@include('layouts.partials.page-title', ['title' => 'Ticketing', 'subtitle' => 'Monitoring Events'])

<div class="card">
    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search event, source, message"
                    value="{{ $filters['search'] ?? '' }}">
            </div>
            <div class="col-md-2">
                <select name="severity" class="form-select">
                    <option value="">All severity</option>
                    @foreach ($severityOptions as $severityCode => $severityLabel)
                        <option value="{{ $severityCode }}" @selected(($filters['severity'] ?? null) === $severityCode)>
                            {{ $severityLabel }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">All status</option>
                    @foreach ($statusOptions as $statusCode => $statusLabel)
                        <option value="{{ $statusCode }}" @selected(($filters['status'] ?? null) === $statusCode)>
                            {{ $statusLabel }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="source" class="form-select">
                    <option value="">All source</option>
                    @foreach ($sourceOptions as $source)
                        <option value="{{ $source }}" @selected(($filters['source'] ?? null) === $source)>{{ $source }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 text-md-end">
                <button class="btn btn-outline-secondary" type="submit">Filter</button>
                <a href="{{ route('monitoring-events.index') }}" class="btn btn-outline-light">Reset</a>
            </div>
            <div class="col-12 text-end">
                <a href="{{ route('monitoring-events.create') }}" class="btn btn-primary">Create Event</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Severity</th>
                        <th>Status</th>
                        <th>Service / Asset</th>
                        <th>Duplicate</th>
                        <th>Occurred</th>
                        <th>Incident</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($events as $event)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $event->event_number }}</div>
                                <div class="small text-muted">{{ $event->source }} - {{ $event->message }}</div>
                            </td>
                            <td>
                                <span class="badge {{ $event->severity === \App\Models\MonitoringEvent::SEVERITY_CRITICAL ? 'bg-danger-subtle text-danger' : ($event->severity === \App\Models\MonitoringEvent::SEVERITY_HIGH ? 'bg-warning-subtle text-warning' : 'bg-light text-dark border') }}">
                                    {{ $event->severityLabel() }}
                                </span>
                            </td>
                            <td>{{ $event->statusLabel() }}</td>
                            <td>
                                <div>{{ $event->service?->name ?? '-' }}</div>
                                <div class="small text-muted">{{ $event->asset?->name ?? '-' }}</div>
                            </td>
                            <td>{{ number_format($event->duplicate_count) }}</td>
                            <td>{{ optional($event->occurred_at)->format('d M Y H:i') ?? '-' }}</td>
                            <td>
                                @if ($event->convertedTicket)
                                    <a href="{{ route('tickets.show', $event->convertedTicket) }}">{{ $event->convertedTicket->ticket_number }}</a>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('monitoring-events.show', $event) }}" class="btn btn-sm btn-outline-secondary">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No monitoring event found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $events->links() }}
        </div>
    </div>
</div>
@endsection
