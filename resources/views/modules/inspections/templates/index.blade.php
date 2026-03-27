@extends('layouts.vertical', ['subtitle' => 'Inspection Templates'])

@section('content')
@include('layouts.partials.page-title', ['title' => 'Master Data', 'subtitle' => 'Inspection Templates'])

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
                <select name="asset_category_id" class="form-select">
                    <option value="">All asset categories</option>
                    @foreach ($assetCategoryOptions as $option)
                        <option value="{{ $option->id }}" @selected((string) ($filters['asset_category_id'] ?? '') === (string) $option->id)>
                            {{ $option->name }}
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
                <a href="{{ route('master-data.inspection-templates.index') }}" class="btn btn-outline-light">Reset</a>
                <a href="{{ route('master-data.inspection-templates.create') }}" class="btn btn-primary">Add Template</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Asset Category</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($inspectionTemplates as $inspectionTemplate)
                        <tr>
                            <td>{{ $inspectionTemplate->code }}</td>
                            <td>{{ $inspectionTemplate->name }}</td>
                            <td>{{ $inspectionTemplate->assetCategory?->name ?? '-' }}</td>
                            <td>
                                @if ($inspectionTemplate->is_active)
                                    <span class="badge bg-success-subtle text-success">Active</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('master-data.inspection-templates.edit', $inspectionTemplate) }}"
                                    class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="POST"
                                    action="{{ route('master-data.inspection-templates.destroy', $inspectionTemplate) }}"
                                    class="d-inline" onsubmit="return confirm('Delete this inspection template?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No inspection templates found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $inspectionTemplates->links() }}</div>
    </div>
</div>
@endsection
