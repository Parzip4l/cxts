@extends('layouts.vertical', ['subtitle' => $pageTitle])

@section('content')
@include('layouts.partials.page-title', ['title' => 'Master Data', 'subtitle' => $pageTitle])

@php
    $selectedEngineerSkillIds = collect(old('engineer_skill_ids', $service->relationLoaded('engineerSkills') ? $service->engineerSkills->pluck('id')->all() : $service->engineerSkills()->pluck('engineer_skills.id')->all()))
        ->map(fn ($value) => (string) $value)
        ->all();
    $requestFormSchema = old('request_form_schema');
    if ($requestFormSchema === null && $service->request_form_schema !== null) {
        $requestFormSchema = json_encode($service->request_form_schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
    $defaultApprovalValue = old('default_request_approval_required');
    if ($defaultApprovalValue === null && $service->default_request_approval_required !== null) {
        $defaultApprovalValue = $service->default_request_approval_required ? '1' : '0';
    }
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
                <hr class="my-2">
                <h5 class="mb-2">Service Request Defaults</h5>
            </div>

            <div class="col-md-4">
                <input type="hidden" name="is_requestable" value="0">
                <div class="form-check mt-2">
                    <input class="form-check-input @error('is_requestable') is-invalid @enderror" type="checkbox"
                        id="is_requestable" name="is_requestable" value="1"
                        @checked((bool) old('is_requestable', $service->is_requestable ?? true))>
                    <label class="form-check-label" for="is_requestable">
                        Requestable by requester
                    </label>
                    @error('is_requestable')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-md-4">
                <label for="default_request_approval_required" class="form-label">Default Approval</label>
                <select id="default_request_approval_required" name="default_request_approval_required"
                    class="form-select @error('default_request_approval_required') is-invalid @enderror">
                    <option value="" @selected($defaultApprovalValue === null || $defaultApprovalValue === '')>Inherit ticket category policy</option>
                    <option value="1" @selected((string) $defaultApprovalValue === '1')>Approval required</option>
                    <option value="0" @selected((string) $defaultApprovalValue === '0')>No approval required</option>
                </select>
                @error('default_request_approval_required')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="default_request_sla_policy_id" class="form-label">Default Request SLA</label>
                <select id="default_request_sla_policy_id" name="default_request_sla_policy_id"
                    class="form-select @error('default_request_sla_policy_id') is-invalid @enderror">
                    <option value="">- Use SLA assignment rule -</option>
                    @foreach ($slaPolicyOptions as $option)
                        <option value="{{ $option->id }}" @selected((string) old('default_request_sla_policy_id', $service->default_request_sla_policy_id) === (string) $option->id)>
                            {{ $option->name }}
                        </option>
                    @endforeach
                </select>
                @error('default_request_sla_policy_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="fulfillment_team_name" class="form-label">Fulfillment Team</label>
                <input type="text" id="fulfillment_team_name" name="fulfillment_team_name"
                    class="form-control @error('fulfillment_team_name') is-invalid @enderror"
                    value="{{ old('fulfillment_team_name', $service->fulfillment_team_name) }}">
                @error('fulfillment_team_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="request_form_schema" class="form-label">Request Form Schema JSON</label>
                <textarea id="request_form_schema" name="request_form_schema" rows="3"
                    class="form-control font-monospace @error('request_form_schema') is-invalid @enderror"
                    placeholder='[{"name":"employee_id","label":"Employee ID","type":"text"}]'>{{ $requestFormSchema }}</textarea>
                @error('request_form_schema')
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
