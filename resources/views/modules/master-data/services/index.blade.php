@extends('layouts.vertical', ['subtitle' => 'Service Catalog'])

@section('content')
@include('layouts.partials.page-title', ['title' => 'Master Data', 'subtitle' => 'Service Catalog'])

<div class="card">
    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search code, name, category"
                    value="{{ $filters['search'] ?? '' }}">
            </div>
            <div class="col-md-3">
                <select name="ownership_model" class="form-select">
                    <option value="">All ownership</option>
                    @foreach ($ownershipOptions as $ownershipOption)
                        <option value="{{ $ownershipOption }}" @selected(($filters['ownership_model'] ?? null) === $ownershipOption)>
                            {{ ucfirst($ownershipOption) }}
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
                <a href="{{ route('master-data.services.index') }}" class="btn btn-outline-light">Reset</a>
                <a href="{{ route('master-data.services.create') }}" class="btn btn-primary">Add Service</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Ownership</th>
                        <th>Owner Department</th>
                        <th>Vendor</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($services as $service)
                        <tr>
                            <td>{{ $service->code }}</td>
                            <td>{{ $service->name }}</td>
                            <td>{{ $service->service_category ?? '-' }}</td>
                            <td><span class="badge bg-info-subtle text-info">{{ ucfirst($service->ownership_model) }}</span></td>
                            <td>{{ $service->ownerDepartment?->name ?? '-' }}</td>
                            <td>{{ $service->vendor?->name ?? '-' }}</td>
                            <td>
                                @if ($service->is_active)
                                    <span class="badge bg-success-subtle text-success">Active</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('master-data.services.edit', $service) }}"
                                    class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="POST" action="{{ route('master-data.services.destroy', $service) }}"
                                    class="d-inline"
                                    onsubmit="return confirm('Delete this service catalog?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No service catalog found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $services->links() }}
        </div>
    </div>
</div>
@endsection
