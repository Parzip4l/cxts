@extends('layouts.vertical', ['subtitle' => 'Permissions'])

@section('content')
@include('layouts.partials.page-title', ['title' => 'Master Data', 'subtitle' => 'Permissions'])

<div class="card">
    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control" placeholder="Search code or name"
                    value="{{ $filters['search'] ?? '' }}">
            </div>
            <div class="col-md-3">
                <select name="group_name" class="form-select">
                    <option value="">All groups</option>
                    @foreach ($groupOptions as $groupOption)
                        <option value="{{ $groupOption }}" @selected((string) ($filters['group_name'] ?? '') === (string) $groupOption)>
                            {{ str($groupOption)->replace('_', ' ')->title() }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 text-md-end">
                <button class="btn btn-outline-secondary" type="submit">Filter</button>
                <a href="{{ route('master-data.permissions.index') }}" class="btn btn-outline-light">Reset</a>
                <a href="{{ route('master-data.permissions.create') }}" class="btn btn-primary">Add Permission</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Group</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($permissions as $permission)
                        <tr>
                            <td>{{ $permission->code }}</td>
                            <td>{{ $permission->name }}</td>
                            <td>{{ $permission->group_name ? str($permission->group_name)->replace('_', ' ')->title() : '-' }}</td>
                            <td>
                                <span class="badge {{ $permission->is_active ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                                    {{ $permission->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('master-data.permissions.edit', $permission) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="POST" action="{{ route('master-data.permissions.destroy', $permission) }}" class="d-inline"
                                    onsubmit="return confirm('Delete this permission?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No permissions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $permissions->links() }}</div>
    </div>
</div>
@endsection
