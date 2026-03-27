@extends('layouts.vertical', ['subtitle' => 'Manage Role Permission Matrix'])

@section('content')
@include('layouts.partials.page-title', ['title' => 'Master Data', 'subtitle' => 'Role Permission Matrix'])

@php
    $selectedPermissionIds = $roleRecord->permissions->pluck('id')->map(fn ($id) => (string) $id)->all();
@endphp

<div class="card">
    <div class="card-body">
        <div class="mb-3">
            <h5 class="mb-1">{{ $roleRecord->name }}</h5>
            <p class="text-muted mb-0">{{ $roleRecord->description ?: 'No role description.' }}</p>
        </div>

        <form method="POST" action="{{ route('master-data.role-permissions.update', $roleRecord) }}">
            @csrf
            @method('PUT')

            <div class="row g-3">
                @foreach ($permissionGroups as $groupName => $permissions)
                    <div class="col-lg-6">
                        <div class="border rounded p-3 h-100">
                            <div class="fw-semibold mb-2">{{ str($groupName)->replace('_', ' ')->title() }}</div>
                            <div class="d-flex flex-column gap-2">
                                @foreach ($permissions as $permission)
                                    <label class="form-check border rounded px-3 py-2">
                                        <input class="form-check-input me-2" type="checkbox" name="permission_ids[]"
                                            value="{{ $permission->id }}" @checked(in_array((string) $permission->id, $selectedPermissionIds, true))>
                                        <span class="fw-medium">{{ $permission->name }}</span>
                                        <span class="d-block small text-muted">{{ $permission->code }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Save Matrix</button>
                <a href="{{ route('master-data.role-permissions.index') }}" class="btn btn-outline-light">Back</a>
            </div>
        </form>
    </div>
</div>
@endsection
