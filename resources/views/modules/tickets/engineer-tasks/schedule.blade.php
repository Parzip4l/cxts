@extends('layouts.vertical', ['subtitle' => 'My Schedule'])

@section('content')
@include('layouts.partials.page-title', ['title' => 'Engineer', 'subtitle' => 'My Schedule'])

<div class="card">
    <div class="card-body">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-3">
                <input type="date" name="work_date_from" class="form-control" value="{{ $filters['work_date_from'] ?? '' }}">
            </div>
            <div class="col-md-3">
                <input type="date" name="work_date_to" class="form-control" value="{{ $filters['work_date_to'] ?? '' }}">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All status</option>
                    <option value="assigned" @selected(($filters['status'] ?? '') === 'assigned')>Assigned</option>
                    <option value="off" @selected(($filters['status'] ?? '') === 'off')>Off</option>
                    <option value="leave" @selected(($filters['status'] ?? '') === 'leave')>Leave</option>
                    <option value="sick" @selected(($filters['status'] ?? '') === 'sick')>Sick</option>
                </select>
            </div>
            <div class="col-md-3 text-md-end">
                <button class="btn btn-outline-secondary" type="submit">Filter</button>
                <a href="{{ route('engineer-tasks.schedule') }}" class="btn btn-outline-light">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Shift</th>
                        <th>Status</th>
                        <th>Assigned By</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($schedules as $schedule)
                        <tr>
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
                            <td>{{ $schedule->notes ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No schedules found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $schedules->links() }}</div>
    </div>
</div>
@endsection
