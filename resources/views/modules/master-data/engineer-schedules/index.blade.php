@extends('layouts.vertical', ['subtitle' => 'Engineer Schedules'])

@section('content')
@include('layouts.partials.page-title', ['title' => 'Master Data', 'subtitle' => 'Engineer Schedules'])

<div class="card">
    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="GET" class="vstack gap-3 mb-4">
            <div class="row g-3">
                <div class="col-12 col-xl-4">
                    <label for="schedule-search" class="form-label small text-muted mb-1">Search</label>
                    <input
                        id="schedule-search"
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Search engineer"
                        value="{{ $filters['search'] ?? '' }}"
                    >
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <label for="schedule-engineer" class="form-label small text-muted mb-1">Engineer</label>
                    <select
                        id="schedule-engineer"
                        name="user_id"
                        class="form-select"
                        data-searchable-select
                        data-search-placeholder="Search engineer"
                    >
                        <option value="">All engineers</option>
                        @foreach ($engineerOptions as $engineer)
                            <option value="{{ $engineer->id }}" @selected((string) ($filters['user_id'] ?? '') === (string) $engineer->id)>
                                {{ $engineer->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3 col-xl-2">
                    <label for="schedule-date" class="form-label small text-muted mb-1">Work date</label>
                    <input
                        id="schedule-date"
                        type="date"
                        name="work_date"
                        class="form-control"
                        value="{{ $filters['work_date'] ?? '' }}"
                    >
                </div>
                <div class="col-6 col-md-3 col-xl-3">
                    <label for="schedule-status" class="form-label small text-muted mb-1">Status</label>
                    <select id="schedule-status" name="status" class="form-select">
                        <option value="">All status</option>
                        <option value="assigned" @selected(($filters['status'] ?? '') === 'assigned')>Assigned</option>
                        <option value="off" @selected(($filters['status'] ?? '') === 'off')>Off</option>
                        <option value="leave" @selected(($filters['status'] ?? '') === 'leave')>Leave</option>
                        <option value="sick" @selected(($filters['status'] ?? '') === 'sick')>Sick</option>
                    </select>
                </div>
            </div>

            <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center justify-content-between gap-2">
                <div class="small text-muted">
                    Filter engineer schedules by name, date, or availability status before opening the roster detail.
                </div>
                <div class="d-flex flex-column flex-sm-row justify-content-md-end gap-2">
                    <button class="btn btn-outline-secondary text-nowrap" type="submit">Apply Filter</button>
                    <a href="{{ route('master-data.engineer-schedules.index') }}" class="btn btn-outline-light text-nowrap">Reset</a>
                    <a href="{{ route('master-data.engineer-schedules.create') }}" class="btn btn-primary text-nowrap">Add Schedule</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-nowrap align-middle mb-0" id="engineer-schedule-table">
                <thead class="table-light">
                    <tr>
                        <th width="60">No</th>
                        <th>Engineer</th>
                        <th>Date</th>
                        <th>Shift</th>
                        <th>Status</th>
                        <th>Assigned By</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($schedules as $schedule)
                        @php
                            $rowNumber = ($schedules->firstItem() ?? 1) + $loop->index;
                        @endphp
                        <tr>
                            <td>{{ $rowNumber }}</td>
                            <td>{{ $schedule->engineer?->name ?? '-' }}</td>
                            <td>{{ optional($schedule->work_date)->format('Y-m-d') }}</td>
                            <td>
                                {{ $schedule->shift?->name ?? '-' }}
                                @if ($schedule->shift)
                                    <div class="text-muted small">
                                        {{ substr($schedule->shift->start_time, 0, 5) }} - {{ substr($schedule->shift->end_time, 0, 5) }}
                                    </div>
                                @endif
                            </td>
                            <td><span class="badge bg-info-subtle text-info text-uppercase">{{ $schedule->status }}</span></td>
                            <td>{{ $schedule->assignedBy?->name ?? '-' }}</td>
                            <td class="text-end">
                                <a href="{{ route('master-data.engineer-schedules.edit', $schedule) }}"
                                    class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="POST" action="{{ route('master-data.engineer-schedules.destroy', $schedule) }}"
                                    class="d-inline" onsubmit="return confirm('Delete this schedule?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No schedules found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $schedules->links() }}</div>
    </div>
</div>
@endsection
