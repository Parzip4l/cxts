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
                <label for="code" class="form-label">Code</label>
                <input type="text" id="code" name="code" class="form-control @error('code') is-invalid @enderror"
                    value="{{ old('code', $ticketCategory->code) }}" required>
                @error('code')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-8">
                <label for="name" class="form-label">Type Name</label>
                <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name', $ticketCategory->name) }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label for="description" class="form-label">Description</label>
                <textarea id="description" name="description" rows="4"
                    class="form-control @error('description') is-invalid @enderror">{{ old('description', $ticketCategory->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <div class="border rounded p-3 bg-light-subtle" data-approval-config-root>
                    @include('modules.tickets.partials.approval-matrix', [
                        'scopeLabel' => 'Ticket Type',
                        'inheritLabel' => null,
                    ])
                    <div class="row g-3">
                        <div class="col-md-6">
                            <input type="hidden" name="requires_approval" value="0">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="requires_approval" name="requires_approval" value="1"
                                    @checked((bool) old('requires_approval', $ticketCategory->requires_approval ?? false))>
                                <label class="form-check-label" for="requires_approval">Requires approval before assignment</label>
                            </div>
                            <div class="form-text">Gunakan untuk jenis ticket yang perlu kontrol sebelum operasional bergerak.</div>
                        </div>
                        <div class="col-md-6">
                            <input type="hidden" name="allow_direct_assignment" value="0">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="allow_direct_assignment" name="allow_direct_assignment" value="1"
                                    @checked((bool) old('allow_direct_assignment', $ticketCategory->allow_direct_assignment ?? true))>
                                <label class="form-check-label" for="allow_direct_assignment">Allow direct assignment</label>
                            </div>
                            <div class="form-text">Jika nonaktif, ticket harus ditandai siap assign lebih dulu walau approval tidak dibutuhkan.</div>
                        </div>
                        <div class="col-12">
                            <label for="approver_strategy" class="form-label">Approver Strategy</label>
                            <select id="approver_strategy" name="approver_strategy" class="form-select @error('approver_strategy') is-invalid @enderror">
                                <option value="">- Supervisor/Admin Fallback -</option>
                                @foreach ($approverStrategyOptions as $strategyCode => $strategyLabel)
                                    <option value="{{ $strategyCode }}" @selected((string) old('approver_strategy', $ticketCategory->approver_strategy) === (string) $strategyCode)>
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
                                <option value="">- Supervisor/Admin Fallback -</option>
                                @foreach ($approverOptions as $approverOption)
                                    <option value="{{ $approverOption->id }}" @selected((string) old('approver_user_id', $ticketCategory->approver_user_id) === (string) $approverOption->id)>
                                        {{ $approverOption->name }} - {{ str($approverOption->role)->replace('_', ' ')->title() }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Jika dikosongkan, approval bisa dilakukan oleh supervisor atau admin sesuai akses existing.</div>
                            @error('approver_user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6" data-approver-role-field>
                            <label for="approver_role_code" class="form-label">Role-Based Approver</label>
                            <select id="approver_role_code" name="approver_role_code" class="form-select @error('approver_role_code') is-invalid @enderror">
                                <option value="">- Select Role -</option>
                                @foreach ($approverRoleOptions as $approverRoleCode => $approverRoleLabel)
                                    <option value="{{ $approverRoleCode }}" @selected((string) old('approver_role_code', $ticketCategory->approver_role_code) === (string) $approverRoleCode)>
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
                        @checked((bool) old('is_active', $ticketCategory->is_active ?? true))>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>
            </div>

            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('master-data.ticket-categories.index') }}" class="btn btn-outline-light">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
