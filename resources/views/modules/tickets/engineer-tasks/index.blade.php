@extends('layouts.vertical', ['subtitle' => 'My Tasks'])

@section('content')
@include('layouts.partials.page-title', ['title' => 'Engineer', 'subtitle' => 'My Tasks'])

<div class="card">
    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search task"
                    value="{{ $filters['search'] ?? '' }}">
            </div>
            <div class="col-md-3">
                <select name="ticket_status_id" class="form-select">
                    <option value="">All status</option>
                    @foreach ($statusOptions as $option)
                        <option value="{{ $option->id }}" @selected((string) ($filters['ticket_status_id'] ?? '') === (string) $option->id)>
                            {{ $option->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-5 text-md-end">
                <button class="btn btn-outline-secondary" type="submit">Filter</button>
                <a href="{{ route('engineer-tasks.index') }}" class="btn btn-outline-light">Reset</a>
                <a href="{{ route('engineer-tasks.history') }}" class="btn btn-outline-primary">History</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Ticket</th>
                        <th>Title</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Updated</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tasks as $task)
                        <tr>
                            <td>{{ $task->ticket_number }}</td>
                            <td>{{ $task->title }}</td>
                            <td>{{ $task->priority?->name ?? '-' }}</td>
                            <td>{{ $task->status?->name ?? '-' }}</td>
                            <td>{{ optional($task->updated_at)->format('Y-m-d H:i') }}</td>
                            <td class="text-end">
                                <a href="{{ route('engineer-tasks.show', $task) }}" class="btn btn-sm btn-primary">Open</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No tasks assigned.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $tasks->links() }}</div>
    </div>
</div>
@endsection
