@extends('layouts.vertical', ['subtitle' => 'Engineering'])

@section('content')
@include('layouts.partials.page-title', ['title' => 'Operations', 'subtitle' => 'Engineering'])

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('engineering.index') }}" class="row g-3 align-items-end">
            <div class="col-lg-5">
                <label for="engineering-search" class="form-label small text-muted mb-1">Search</label>
                <input
                    type="text"
                    id="engineering-search"
                    name="search"
                    class="form-control"
                    value="{{ $search }}"
                    placeholder="Search engineer name, email, phone, or department">
            </div>
            <div class="col-lg-3">
                <label for="engineering-availability" class="form-label small text-muted mb-1">Availability</label>
                <select id="engineering-availability" name="availability" class="form-select">
                    <option value="">All statuses</option>
                    <option value="available" @selected($availabilityFilter === 'available')>Available</option>
                    <option value="busy" @selected($availabilityFilter === 'busy')>Busy</option>
                    <option value="off" @selected($availabilityFilter === 'off')>Off</option>
                    <option value="unscheduled" @selected($availabilityFilter === 'unscheduled')>Unscheduled</option>
                </select>
            </div>
            <div class="col-lg-4">
                <div class="d-flex gap-2 justify-content-lg-end">
                    <a href="{{ route('engineering.index') }}" class="btn btn-outline-light text-nowrap">Reset</a>
                    <button type="submit" class="btn btn-primary text-nowrap">Apply Filter</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <span class="badge bg-primary-subtle text-primary mb-3">Today</span>
                <h3 class="mb-1">{{ $summary['total_engineers'] }}</h3>
                <p class="text-muted mb-0">Engineers tracked for {{ $today->format('d M Y') }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <span class="badge bg-success-subtle text-success mb-3">Available</span>
                <h3 class="mb-1">{{ $summary['available'] }}</h3>
                <p class="text-muted mb-0">Ready to receive assignment</p>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <span class="badge bg-danger-subtle text-danger mb-3">Busy</span>
                <h3 class="mb-1">{{ $summary['busy'] }}</h3>
                <p class="text-muted mb-0">Currently handling active work</p>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <span class="badge bg-secondary-subtle text-secondary mb-3">Unavailable</span>
                <h3 class="mb-1">{{ $summary['off'] + $summary['unscheduled'] }}</h3>
                <p class="text-muted mb-0">Off duty, leave, sick, or unscheduled</p>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent border-0 pt-4 pb-0">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="mb-1">Department Workload</h5>
                <p class="text-muted mb-0 small">Ringkasan kapasitas dan beban engineer per department.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-dark border">{{ $departmentSummary->count() }} Departments</span>
                <button
                    class="btn btn-outline-light btn-sm text-nowrap"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#departmentWorkloadPanel"
                    aria-expanded="true"
                    aria-controls="departmentWorkloadPanel">
                    Tampilkan / Sembunyikan
                </button>
            </div>
        </div>
    </div>
    <div class="collapse show" id="departmentWorkloadPanel">
    <div class="card-body pt-3">
        <div class="row g-3">
            @forelse ($departmentSummary as $department)
                <div class="col-md-6 col-xl-4">
                    <div class="rounded-3 border bg-light-subtle p-3 h-100">
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                            <div>
                                <h6 class="mb-1">{{ $department['department'] }}</h6>
                                <div class="text-muted small">{{ $department['engineer_count'] }} engineers</div>
                            </div>
                            <span class="badge bg-primary-subtle text-primary">{{ $department['active_ticket_count'] }} active</span>
                        </div>
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <span class="badge bg-success-subtle text-success">{{ $department['available_count'] }} available</span>
                            <span class="badge bg-danger-subtle text-danger">{{ $department['busy_count'] }} busy</span>
                            <span class="badge bg-info-subtle text-info">{{ $department['in_progress_ticket_count'] }} in progress</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="text-muted small">No department workload data available for the current filter.</div>
                </div>
            @endforelse
        </div>
    </div>
    </div>
</div>

<div class="row g-4">
    @forelse ($cards as $card)
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column gap-3">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div class="d-flex align-items-center gap-3">
                            @if ($card['profile_photo_url'])
                                <img
                                    src="{{ $card['profile_photo_url'] }}"
                                    alt="{{ $card['engineer']->name }}"
                                    class="rounded-circle object-fit-cover border"
                                    style="width: 56px; height: 56px;">
                            @else
                                <div class="avatar-md rounded-circle bg-{{ $card['avatar_class'] }} bg-opacity-10 text-{{ $card['avatar_class'] }} d-flex align-items-center justify-content-center fw-bold fs-5">
                                    {{ $card['avatar_initials'] }}
                                </div>
                            @endif
                            <div>
                                <h5 class="mb-1">{{ $card['engineer']->name }}</h5>
                                <p class="text-muted mb-0">{{ $card['engineer']->department?->name ?? 'No department' }}</p>
                            </div>
                        </div>
                        <span class="badge bg-{{ $card['availability_class'] }}-subtle text-{{ $card['availability_class'] }}">
                            {{ $card['availability_label'] }}
                        </span>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-light text-dark border">{{ $card['schedule_status_label'] }}</span>
                        <span class="badge bg-{{ $card['schedule_status_class'] }}-subtle text-{{ $card['schedule_status_class'] }}">
                            {{ $card['shift_label'] }}
                        </span>
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <div class="rounded-3 bg-light p-3 h-100">
                                <div class="text-muted small mb-1">Active Tickets</div>
                                <div class="fs-4 fw-semibold">{{ $card['active_ticket_count'] }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="rounded-3 bg-light p-3 h-100">
                                <div class="text-muted small mb-1">In Progress</div>
                                <div class="fs-4 fw-semibold">{{ $card['in_progress_ticket_count'] }}</div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="text-muted small text-uppercase fw-semibold">Workload</div>
                            <span class="small fw-semibold">{{ $card['workload_label'] }} · {{ $card['workload_percent'] }}%</span>
                        </div>
                        <div class="progress bg-light" style="height: 10px;">
                            <div
                                class="progress-bar bg-{{ $card['availability_class'] }}"
                                role="progressbar"
                                style="width: {{ $card['workload_percent'] }}%;"
                                aria-valuenow="{{ $card['workload_percent'] }}"
                                aria-valuemin="0"
                                aria-valuemax="100">
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="text-muted small text-uppercase fw-semibold mb-2">Skills</div>
                        <div class="d-flex flex-wrap gap-2">
                            @forelse ($card['skill_names'] as $skillName)
                                <span class="badge bg-info-subtle text-info">{{ $skillName }}</span>
                            @empty
                                <span class="text-muted small">No skills mapped yet</span>
                            @endforelse
                        </div>
                    </div>

                    <div class="mt-auto pt-2 border-top">
                        <div class="small text-muted mb-1">{{ $card['engineer']->email }}</div>
                        @if ($card['engineer']->phone_number)
                            <div class="small text-muted mb-1">{{ $card['engineer']->phone_number }}</div>
                        @endif
                        @if ($card['schedule_notes'])
                            <div class="small text-muted">Notes: {{ $card['schedule_notes'] }}</div>
                        @endif
                        <div class="mt-3 d-flex gap-2 flex-wrap">
                            @if ($card['whatsapp_url'])
                                <a href="{{ $card['whatsapp_url'] }}" target="_blank" rel="noopener noreferrer" class="btn btn-success btn-sm text-nowrap">
                                    WhatsApp
                                </a>
                            @endif
                            @if ($card['tel_url'])
                                <a href="{{ $card['tel_url'] }}" class="btn btn-outline-primary btn-sm text-nowrap">
                                    Telepon
                                </a>
                            @elseif (! $card['whatsapp_url'])
                                <a href="mailto:{{ $card['engineer']->email }}" class="btn btn-outline-primary btn-sm text-nowrap">
                                    Email
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <h5 class="mb-2">No engineer data found</h5>
                    <p class="text-muted mb-0">Add engineer users and schedules first to populate this board.</p>
                </div>
            </div>
        </div>
    @endforelse
</div>

@if ($cards->hasPages())
    <div class="mt-4 d-flex justify-content-end">
        {{ $cards->links() }}
    </div>
@endif
@endsection
