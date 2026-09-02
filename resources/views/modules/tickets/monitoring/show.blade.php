@extends('layouts.vertical', ['subtitle' => 'Monitoring Event Detail'])

@section('content')
@include('layouts.partials.page-title', ['title' => 'Monitoring Events', 'subtitle' => $event->event_number])

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
            <div>
                <div class="text-uppercase small text-muted fw-semibold mb-1">{{ $event->event_number }}</div>
                <h4 class="mb-2">{{ $event->message }}</h4>
                <div class="d-flex flex-wrap gap-2">
                    <span class="badge bg-light text-dark border">{{ $event->source }}</span>
                    <span class="badge {{ $event->severity === \App\Models\MonitoringEvent::SEVERITY_CRITICAL ? 'bg-danger-subtle text-danger' : ($event->severity === \App\Models\MonitoringEvent::SEVERITY_HIGH ? 'bg-warning-subtle text-warning' : 'bg-light text-dark border') }}">
                        {{ $event->severityLabel() }}
                    </span>
                    <span class="badge bg-primary-subtle text-primary">{{ $event->statusLabel() }}</span>
                </div>
            </div>
            <div class="d-flex gap-2">
                @if ($event->status === \App\Models\MonitoringEvent::STATUS_OPEN)
                    <form method="POST" action="{{ route('monitoring-events.convert', $event) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary">Create Incident</button>
                    </form>
                    <form method="POST" action="{{ route('monitoring-events.ignore', $event) }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary">Ignore</button>
                    </form>
                @endif
                <a href="{{ route('monitoring-events.index') }}" class="btn btn-outline-light">Back</a>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title mb-3">Event Details</h5>
                <div class="vstack gap-3">
                    <div>
                        <div class="small text-muted mb-1">Details</div>
                        <div class="fw-semibold" style="white-space: pre-line;">{{ $event->details ?: '-' }}</div>
                    </div>
                    <div>
                        <div class="small text-muted mb-1">Deduplication Key</div>
                        <div class="fw-semibold">{{ $event->deduplication_key ?: '-' }}</div>
                    </div>
                    <div>
                        <div class="small text-muted mb-1">Mapped Incident Priority</div>
                        <div class="fw-semibold">{{ ucfirst($mappedPriority['impact']) }} impact / {{ ucfirst($mappedPriority['urgency']) }} urgency</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title mb-3">Context</h5>
                <div class="vstack gap-2">
                    <div class="d-flex justify-content-between gap-3">
                        <span class="text-muted">Service</span>
                        <span class="fw-semibold text-end">{{ $event->service?->name ?? '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between gap-3">
                        <span class="text-muted">Asset</span>
                        <span class="fw-semibold text-end">{{ $event->asset?->name ?? '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between gap-3">
                        <span class="text-muted">Occurred</span>
                        <span class="fw-semibold text-end">{{ optional($event->occurred_at)->format('d M Y H:i') ?? '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between gap-3">
                        <span class="text-muted">Last Seen</span>
                        <span class="fw-semibold text-end">{{ optional($event->last_seen_at)->format('d M Y H:i') ?? '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between gap-3">
                        <span class="text-muted">Duplicate Count</span>
                        <span class="fw-semibold text-end">{{ number_format($event->duplicate_count) }}</span>
                    </div>
                    <div class="d-flex justify-content-between gap-3">
                        <span class="text-muted">Incident</span>
                        <span class="fw-semibold text-end">
                            @if ($event->convertedTicket)
                                <a href="{{ route('tickets.show', $event->convertedTicket) }}">{{ $event->convertedTicket->ticket_number }}</a>
                            @else
                                -
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
