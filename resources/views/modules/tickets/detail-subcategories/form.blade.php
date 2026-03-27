@extends('layouts.vertical', ['subtitle' => $pageTitle])

@section('content')
@include('layouts.partials.page-title', ['title' => 'Master Data', 'subtitle' => $pageTitle])

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ $action }}" class="row g-3">
            @csrf
            @if ($method !== 'POST')
                @method($method)
            @endif

            <div class="col-md-4">
                <label for="ticket_subcategory_id" class="form-label">Ticket Category</label>
                <select id="ticket_subcategory_id" name="ticket_subcategory_id"
                    class="form-select @error('ticket_subcategory_id') is-invalid @enderror" required>
                    <option value="">- Select -</option>
                    @foreach ($categoryOptions as $option)
                        <option value="{{ $option->id }}" @selected((string) old('ticket_subcategory_id', $ticketDetailSubcategory->ticket_subcategory_id) === (string) $option->id)>
                            {{ $option->category?->name ? $option->category->name.' / ' : '' }}{{ $option->name }}
                        </option>
                    @endforeach
                </select>
                @error('ticket_subcategory_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="code" class="form-label">Code</label>
                <input type="text" id="code" name="code" class="form-control @error('code') is-invalid @enderror"
                    value="{{ old('code', $ticketDetailSubcategory->code) }}" required>
                @error('code')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="name" class="form-label">Sub Category Name</label>
                <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name', $ticketDetailSubcategory->name) }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label for="description" class="form-label">Description</label>
                <textarea id="description" name="description" rows="4"
                    class="form-control @error('description') is-invalid @enderror">{{ old('description', $ticketDetailSubcategory->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label for="engineer_skill_ids" class="form-label">Engineer Skills</label>
                <select id="engineer_skill_ids" name="engineer_skill_ids[]" multiple
                    class="form-select @error('engineer_skill_ids') is-invalid @enderror @error('engineer_skill_ids.*') is-invalid @enderror"
                    data-searchable-select data-searchable-force data-search-placeholder="Search engineer skills">
                    @php
                        $selectedSkillIds = collect(old('engineer_skill_ids', $ticketDetailSubcategory->engineerSkills?->pluck('id')->all() ?? []))
                            ->map(fn ($id) => (string) $id)
                            ->all();
                    @endphp
                    @foreach ($skillOptions as $option)
                        <option value="{{ $option->id }}" @selected(in_array((string) $option->id, $selectedSkillIds, true))>
                            {{ $option->name }}
                        </option>
                    @endforeach
                </select>
                <div class="form-text">Skill di level ini akan diprioritaskan untuk recommendation engineer saat ticket memilih sub category ini.</div>
                @error('engineer_skill_ids')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                @error('engineer_skill_ids.*')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <div class="border rounded p-3 bg-light-subtle" data-approval-config-root>
                    @include('modules.tickets.partials.approval-matrix', [
                        'scopeLabel' => 'Ticket Sub Category',
                        'inheritLabel' => 'Ticket Category',
                    ])
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="requires_approval" class="form-label">Approval Policy</label>
                            <select id="requires_approval" name="requires_approval" class="form-select @error('requires_approval') is-invalid @enderror">
                                <option value="" @selected(old('requires_approval', $ticketDetailSubcategory->requires_approval) === null)>Follow Parent</option>
                                <option value="1" @selected((string) old('requires_approval', $ticketDetailSubcategory->requires_approval) === '1')>Approval Required</option>
                                <option value="0" @selected((string) old('requires_approval', $ticketDetailSubcategory->requires_approval) === '0')>No Approval Required</option>
                            </select>
                            @error('requires_approval')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="allow_direct_assignment" class="form-label">Assignment Policy</label>
                            <select id="allow_direct_assignment" name="allow_direct_assignment" class="form-select @error('allow_direct_assignment') is-invalid @enderror">
                                <option value="" @selected(old('allow_direct_assignment', $ticketDetailSubcategory->allow_direct_assignment) === null)>Follow Parent</option>
                                <option value="1" @selected((string) old('allow_direct_assignment', $ticketDetailSubcategory->allow_direct_assignment) === '1')>Allow Direct Assignment</option>
                                <option value="0" @selected((string) old('allow_direct_assignment', $ticketDetailSubcategory->allow_direct_assignment) === '0')>Needs Ready Flag</option>
                            </select>
                            @error('allow_direct_assignment')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label for="approver_strategy" class="form-label">Approver Strategy</label>
                            <select id="approver_strategy" name="approver_strategy" class="form-select @error('approver_strategy') is-invalid @enderror">
                                <option value="">- Follow Parent -</option>
                                @foreach ($approverStrategyOptions as $strategyCode => $strategyLabel)
                                    <option value="{{ $strategyCode }}" @selected((string) old('approver_strategy', $ticketDetailSubcategory->approver_strategy) === (string) $strategyCode)>
                                        {{ $strategyLabel }}
                                    </option>
                                @endforeach
                            </select>
                            @error('approver_strategy')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6" data-approver-user-field>
                            <label for="approver_user_id" class="form-label">Specific Approver</label>
                            <select id="approver_user_id" name="approver_user_id" class="form-select @error('approver_user_id') is-invalid @enderror" data-searchable-select data-force-searchable-select="true" data-search-placeholder="Search approver">
                                <option value="">- Follow Parent -</option>
                                @foreach ($approverOptions as $approverOption)
                                    <option value="{{ $approverOption->id }}" @selected((string) old('approver_user_id', $ticketDetailSubcategory->approver_user_id) === (string) $approverOption->id)>
                                        {{ $approverOption->name }} - {{ str($approverOption->role)->replace('_', ' ')->title() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('approver_user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6" data-approver-role-field>
                            <label for="approver_role_code" class="form-label">Role-Based Approver</label>
                            <select id="approver_role_code" name="approver_role_code" class="form-select @error('approver_role_code') is-invalid @enderror">
                                <option value="">- Select Role -</option>
                                @foreach ($approverRoleOptions as $approverRoleCode => $approverRoleLabel)
                                    <option value="{{ $approverRoleCode }}" @selected((string) old('approver_role_code', $ticketDetailSubcategory->approver_role_code) === (string) $approverRoleCode)>
                                        {{ $approverRoleLabel }}
                                    </option>
                                @endforeach
                            </select>
                            @error('approver_role_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <input type="hidden" name="is_active" value="0">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                        @checked((bool) old('is_active', $ticketDetailSubcategory->is_active ?? true))>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>
            </div>

            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('master-data.ticket-detail-subcategories.index') }}" class="btn btn-outline-light">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
