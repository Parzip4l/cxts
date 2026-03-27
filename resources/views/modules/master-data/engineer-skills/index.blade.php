@extends('layouts.vertical', ['subtitle' => 'Engineer Skills'])

@section('content')
@include('layouts.partials.page-title', ['title' => 'Master Data', 'subtitle' => 'Engineer Skills'])

<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('master-data.engineer-skills.index') }}" class="row g-2 align-items-end mb-3">
            <div class="col-md-6">
                <label for="search" class="form-label">Search</label>
                <input type="text" id="search" name="search" class="form-control" value="{{ $filters['search'] ?? '' }}" placeholder="Search code, name, or description">
            </div>

            <div class="col-md-3">
                <label for="is_active" class="form-label">Status</label>
                <select id="is_active" name="is_active" class="form-select">
                    <option value="">All</option>
                    <option value="1" @selected(($filters['is_active'] ?? null) === true)>Active</option>
                    <option value="0" @selected(($filters['is_active'] ?? null) === false)>Inactive</option>
                </select>
            </div>

            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('master-data.engineer-skills.index') }}" class="btn btn-outline-light">Reset</a>
                <a href="{{ route('master-data.engineer-skills.create') }}" class="btn btn-outline-primary">Add Skill</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($engineerSkills as $skill)
                        <tr>
                            <td>{{ $skill->code }}</td>
                            <td>{{ $skill->name }}</td>
                            <td>{{ $skill->description ?: '-' }}</td>
                            <td>
                                @if ($skill->is_active)
                                    <span class="badge bg-success-subtle text-success">Active</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('master-data.engineer-skills.edit', $skill) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="POST" action="{{ route('master-data.engineer-skills.destroy', $skill) }}" class="d-inline" onsubmit="return confirm('Delete this skill?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No engineer skill found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $engineerSkills->links() }}
        </div>
    </div>
</div>
@endsection
