@extends('layouts.vertical', ['subtitle' => 'Users'])

@section('content')
@include('layouts.partials.page-title', ['title' => 'Master Data', 'subtitle' => 'Users'])

<div class="card">
    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search name or email"
                    value="{{ $filters['search'] ?? '' }}">
            </div>
            <div class="col-md-3">
                <select name="role" class="form-select">
                    <option value="">All roles</option>
                    @foreach ($roleOptions as $roleOption)
                        <option value="{{ $roleOption->code }}" @selected(($filters['role'] ?? null) === $roleOption->code)>
                            {{ $roleOption->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="department_id" class="form-select">
                    <option value="">All departments</option>
                    @foreach ($departmentOptions as $departmentOption)
                        <option value="{{ $departmentOption->id }}" @selected((string) ($filters['department_id'] ?? '') === (string) $departmentOption->id)>
                            {{ $departmentOption->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 text-md-end">
                <button class="btn btn-outline-secondary" type="submit">Filter</button>
            </div>
            <div class="col-12 d-flex justify-content-between">
                <a href="{{ route('master-data.users.index') }}" class="btn btn-outline-light">Reset</a>
                <div class="d-flex gap-2">
                    <a href="{{ route('master-data.users.create') }}" class="btn btn-primary">Add User</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Department</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $userItem)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if ($userItem->profilePhotoUrl())
                                        <img
                                            src="{{ $userItem->profilePhotoUrl() }}"
                                            alt="{{ $userItem->name }}"
                                            class="rounded-circle object-fit-cover border"
                                            style="width: 36px; height: 36px;">
                                    @else
                                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold"
                                            style="width: 36px; height: 36px;">
                                            {{ collect(explode(' ', trim($userItem->name ?: 'NA')))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('') ?: 'NA' }}
                                        </div>
                                    @endif
                                    <span>{{ $userItem->name }}</span>
                                </div>
                            </td>
                            <td>{{ $userItem->email }}</td>
                            <td>{{ $userItem->phone_number ?? '-' }}</td>
                            <td>
                                @if ($userItem->role === 'engineer')
                                    <span class="badge bg-info-subtle text-info">{{ $userItem->roleRef?->name ?? $userItem->role }}</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">{{ $userItem->roleRef?->name ?? $userItem->role }}</span>
                                @endif
                            </td>
                            <td>{{ $userItem->department?->name ?? '-' }}</td>
                            <td class="text-end">
                                <a href="{{ route('master-data.users.edit', $userItem) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="POST" action="{{ route('master-data.users.destroy', $userItem) }}"
                                    class="d-inline" onsubmit="return confirm('Delete this user?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $users->links() }}</div>
    </div>
</div>
@endsection
