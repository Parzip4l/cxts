@extends('layouts.vertical', ['subtitle' => 'Asset Locations'])

@section('content')
@include('layouts.partials.page-title', ['title' => 'Master Data', 'subtitle' => 'Asset Locations'])

<div class="card">
    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search code or name"
                    value="{{ $filters['search'] ?? '' }}">
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
            <div class="col-md-2">
                <select name="is_active" class="form-select">
                    <option value="">All status</option>
                    <option value="1" @selected(($filters['is_active'] ?? null) === true)>Active</option>
                    <option value="0" @selected(($filters['is_active'] ?? null) === false)>Inactive</option>
                </select>
            </div>
            <div class="col-md-3 text-md-end">
                <button class="btn btn-outline-secondary" type="submit">Filter</button>
                <a href="{{ route('master-data.asset-locations.index') }}" class="btn btn-outline-light">Reset</a>
                <a href="{{ route('master-data.asset-locations.create') }}" class="btn btn-primary">Add Location</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Address</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($assetLocations as $assetLocation)
                        <tr>
                            <td>{{ $assetLocation->code }}</td>
                            <td>{{ $assetLocation->name }}</td>
                            <td>{{ $assetLocation->department?->name ?? '-' }}</td>
                            <td>{{ $assetLocation->address ?? '-' }}</td>
                            <td>
                                @if ($assetLocation->is_active)
                                    <span class="badge bg-success-subtle text-success">Active</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('master-data.asset-locations.edit', $assetLocation) }}"
                                    class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="POST" action="{{ route('master-data.asset-locations.destroy', $assetLocation) }}"
                                    class="d-inline" onsubmit="return confirm('Delete this asset location?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No asset locations found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $assetLocations->links() }}</div>
    </div>
</div>
@endsection
