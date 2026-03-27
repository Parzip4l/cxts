@extends('layouts.vertical', ['subtitle' => $pageTitle])

@section('content')
@include('layouts.partials.page-title', ['title' => 'Master Data', 'subtitle' => $pageTitle])

@php
    $selectedEngineerSkillIds = collect(old('engineer_skill_ids', $service->relationLoaded('engineerSkills') ? $service->engineerSkills->pluck('id')->all() : $service->engineerSkills()->pluck('engineer_skills.id')->all()))
        ->map(fn ($value) => (string) $value)
        ->all();
@endphp

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ $action }}" class="row g-3">
            @csrf
            @if ($method !== 'POST')
                @method($method)
            @endif

            <div class="col-md-4">
                <label for="code" class="form-label">Code</label>
                <input type="text" id="code" name="code" class="form-control @error('code') is-invalid @enderror"
                    value="{{ old('code', $service->code) }}" required>
                @error('code')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-8">
                <label for="name" class="form-label">Name</label>
                <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name', $service->name) }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="service_category" class="form-label">Service Category</label>
                <input type="text" id="service_category" name="service_category"
                    class="form-control @error('service_category') is-invalid @enderror"
                    value="{{ old('service_category', $service->service_category) }}">
                @error('service_category')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="ownership_model" class="form-label">Ownership Model</label>
                <select id="ownership_model" name="ownership_model"
                    class="form-select @error('ownership_model') is-invalid @enderror" required>
                    @foreach ($ownershipOptions as $ownershipOption)
                        <option value="{{ $ownershipOption }}" @selected(old('ownership_model', $service->ownership_model ?: 'internal') === $ownershipOption)>
                            {{ ucfirst($ownershipOption) }}
                        </option>
                    @endforeach
                </select>
                @error('ownership_model')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="service_manager_user_id" class="form-label">Service Manager</label>
                <select id="service_manager_user_id" name="service_manager_user_id"
                    data-searchable-select data-search-placeholder="Search service manager"
                    class="form-select @error('service_manager_user_id') is-invalid @enderror">
                    <option value="">- None -</option>
                    @foreach ($managerOptions as $option)
                        <option value="{{ $option->id }}" @selected((string) old('service_manager_user_id', $service->service_manager_user_id) === (string) $option->id)>
                            {{ $option->name }}
                        </option>
                    @endforeach
                </select>
                @error('service_manager_user_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="department_owner_id" class="form-label">Owner Department</label>
                <select id="department_owner_id" name="department_owner_id"
                    data-searchable-select data-search-placeholder="Search department"
                    class="form-select @error('department_owner_id') is-invalid @enderror">
                    <option value="">- None -</option>
                    @foreach ($departmentOptions as $option)
                        <option value="{{ $option->id }}" @selected((string) old('department_owner_id', $service->department_owner_id) === (string) $option->id)>
                            {{ $option->name }}
                        </option>
                    @endforeach
                </select>
                @error('department_owner_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="vendor_id" class="form-label">Vendor</label>
                <select id="vendor_id" name="vendor_id" class="form-select @error('vendor_id') is-invalid @enderror"
                    data-searchable-select data-search-placeholder="Search vendor">
                    <option value="">- None -</option>
                    @foreach ($vendorOptions as $option)
                        <option value="{{ $option->id }}" @selected((string) old('vendor_id', $service->vendor_id) === (string) $option->id)>
                            {{ $option->name }}
                        </option>
                    @endforeach
                </select>
                @error('vendor_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label for="engineer_skill_ids" class="form-label">Related Engineer Skills</label>
                <select id="engineer_skill_ids" name="engineer_skill_ids[]"
                    class="form-select @error('engineer_skill_ids') is-invalid @enderror @error('engineer_skill_ids.*') is-invalid @enderror"
                    data-searchable-select data-force-searchable-select="true"
                    data-search-placeholder="Search engineer skill" multiple>
                    @foreach ($engineerSkillOptions as $skillOption)
                        <option value="{{ $skillOption->id }}" @selected(in_array((string) $skillOption->id, $selectedEngineerSkillIds, true))>
                            {{ $skillOption->name }}
                        </option>
                    @endforeach
                </select>
                <div class="form-text">Skill ini dipakai sebagai dasar rekomendasi engineer ketika ticket terkait service ini dibuat.</div>
                @error('engineer_skill_ids')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
                @error('engineer_skill_ids.*')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label for="description" class="form-label">Description</label>
                <textarea id="description" name="description" rows="4"
                    class="form-control @error('description') is-invalid @enderror">{{ old('description', $service->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <input type="hidden" name="is_active" value="0">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                        @checked((bool) old('is_active', $service->is_active ?? true))>
                    <label class="form-check-label" for="is_active">
                        Active
                    </label>
                </div>
            </div>

            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('master-data.services.index') }}" class="btn btn-outline-light">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
