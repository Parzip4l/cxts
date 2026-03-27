@extends('layouts.vertical', ['subtitle' => 'Task History'])

@section('content')
@include('layouts.partials.page-title', ['title' => 'Engineer', 'subtitle' => 'Task History'])

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-end mb-3">
            <a href="{{ route('engineer-tasks.index') }}" class="btn btn-outline-light">Back to Active Tasks</a>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Ticket</th>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Completed</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tasks as $task)
                        <tr>
                            <td>{{ $task->ticket_number }}</td>
                            <td>{{ $task->title }}</td>
                            <td>{{ $task->status?->name ?? '-' }}</td>
                            <td>{{ $task->priority?->name ?? '-' }}</td>
                            <td>{{ optional($task->completed_at)->format('Y-m-d H:i') ?? '-' }}</td>
                            <td class="text-end">
                                <a href="{{ route('engineer-tasks.show', $task) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No task history yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $tasks->links() }}</div>
    </div>
</div>
@endsection
