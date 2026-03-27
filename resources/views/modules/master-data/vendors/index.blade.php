@extends('layouts.vertical', ['subtitle' => 'Vendors'])

@section('content')
@include('layouts.partials.page-title', ['title' => 'Master Data', 'subtitle' => 'Vendors'])

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
                <select name="is_active" class="form-select">
                    <option value="">All status</option>
                    <option value="1" @selected(($filters['is_active'] ?? null) === true)>Active</option>
                    <option value="0" @selected(($filters['is_active'] ?? null) === false)>Inactive</option>
                </select>
            </div>
            <div class="col-md-4 text-md-end">
                <button class="btn btn-outline-secondary" type="submit">Filter</button>
                <a href="{{ route('master-data.vendors.index') }}" class="btn btn-outline-light">Reset</a>
                <a href="{{ route('master-data.vendors.create') }}" class="btn btn-primary">Add Vendor</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Contact Person</th>
                        <th>Contact</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($vendors as $vendor)
                        <tr>
                            <td>{{ $vendor->code }}</td>
                            <td>{{ $vendor->name }}</td>
                            <td>{{ $vendor->contact_person_name ?? '-' }}</td>
                            <td>
                                <div>{{ $vendor->contact_phone ?? '-' }}</div>
                                <small class="text-muted">{{ $vendor->contact_email ?? '-' }}</small>
                            </td>
                            <td>
                                @if ($vendor->is_active)
                                    <span class="badge bg-success-subtle text-success">Active</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('master-data.vendors.edit', $vendor) }}"
                                    class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="POST" action="{{ route('master-data.vendors.destroy', $vendor) }}"
                                    class="d-inline"
                                    onsubmit="return confirm('Delete this vendor?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No vendors found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $vendors->links() }}
        </div>
    </div>
</div>
@endsection
