@extends('layouts.vertical', ['subtitle' => 'Ticket Detail'])

@section('content')
@include('layouts.partials.page-title', ['title' => 'Ticketing', 'subtitle' => $ticket->ticket_number])
@php
    $workEndedAt = $ticket->completed_at ?? $ticket->resolved_at ?? $ticket->closed_at;
    $workDurationMinutes = ($ticket->started_at && $workEndedAt) ? $ticket->started_at->diffInMinutes($workEndedAt) : null;
    $currentUser = auth()->user();
    $approvalActivities = $ticket->activities->filter(fn ($activity) => in_array($activity->activity_type, ['ticket_approved', 'ticket_rejected', 'ticket_ready_for_assignment'], true))->values();
    $strategyLabels = \App\Models\TicketCategory::approverStrategies();
    $roleLabel = fn ($roleCode) => \App\Models\TicketCategory::approverRoleLabel($roleCode) ?? '-';
    $approvalPolicyLabel = function ($value, $inheritLabel = 'Follow Parent') {
        if ($value === null) {
            return $inheritLabel;
        }

        return $value ? 'Approval Required' : 'No Approval Required';
    };
    $assignmentPolicyLabel = function ($value, $inheritLabel = 'Follow Parent') {
        if ($value === null) {
            return $inheritLabel;
        }

        return $value ? 'Direct Assignment Allowed' : 'Needs Ready Flag';
    };
    $approverStrategyLabel = function ($model, $inheritLabel = 'Follow Parent') use ($strategyLabels) {
        if ($model === null) {
            return '-';
        }

        if (! $model->approver_strategy) {
            return $inheritLabel;
        }

        return $strategyLabels[$model->approver_strategy] ?? str($model->approver_strategy)->replace('_', ' ')->title()->toString();
    };
    $approverTargetLabel = function ($model, $inheritLabel = 'Follow Parent') use ($roleLabel) {
        if ($model === null) {
            return '-';
        }

        if (! $model->approver_strategy) {
            return $inheritLabel;
        }

        return $model->approver?->name
            ?? $roleLabel($model->approver_role_code)
            ?? $inheritLabel;
    };
    $policySourceBadge = fn ($source, $currentSource) => $source === $currentSource
        ? 'bg-primary-subtle text-primary'
        : 'bg-light text-muted border';
    $scoreBadgeClass = function ($score) {
        if ($score >= 80) {
            return 'bg-success-subtle text-success';
        }

        if ($score >= 55) {
            return 'bg-warning-subtle text-warning';
        }

        return 'bg-danger-subtle text-danger';
    };
    $availabilityBadgeClass = function ($status) {
        return match ($status) {
            'available' => 'bg-success-subtle text-success',
            'unavailable' => 'bg-danger-subtle text-danger',
            default => 'bg-secondary-subtle text-secondary',
        };
    };
    $engineerCustomProperties = function ($option) {
        return [
            'department_name' => $option->department_name ?? 'No department',
            'team_label' => $option->team_label ?? 'No team/shift',
            'availability_label' => $option->availability_label ?? 'Unknown',
            'availability_status' => $option->availability_status ?? 'unknown',
            'workload_label' => $option->workload_label ?? 'Light',
            'workload_status' => $option->workload_status ?? 'light',
            'workload_open_tickets' => (int) ($option->workload_open_tickets ?? 0),
            'recommendation_score' => (int) ($option->recommendation_score ?? 0),
        ];
    };
@endphp

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $message)
                                <li>{{ $message }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h5 class="mb-1">{{ $ticket->title }}</h5>
                        <p class="text-muted mb-0">{{ $ticket->ticket_number }}</p>
                    </div>
                    <span class="badge bg-secondary-subtle text-secondary">{{ $ticket->status?->name ?? '-' }}</span>
                </div>

                <p class="mb-3">{{ $ticket->description }}</p>

                <div class="mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="mb-0">Photo Attachments</h6>
                        <span class="badge bg-light text-muted border">{{ $ticket->attachments->count() }} file(s)</span>
                    </div>
                    @if ($ticket->attachments->isEmpty())
                        <div class="alert alert-light border mb-0">Belum ada lampiran foto pada ticket ini.</div>
                    @else
                        <div class="row g-3">
                            @foreach ($ticket->attachments as $attachment)
                                <div class="col-md-4">
                                    <a
                                        href="{{ route('tickets.attachments.show', [$ticket, $attachment]) }}"
                                        target="_blank"
                                        class="card h-100 border text-decoration-none"
                                    >
                                        <img
                                            src="{{ route('tickets.attachments.show', [$ticket, $attachment]) }}"
                                            alt="{{ $attachment->original_name }}"
                                            class="card-img-top"
                                            style="height: 180px; object-fit: cover;"
                                        >
                                        <div class="card-body">
                                            <div class="fw-semibold text-dark text-truncate">{{ $attachment->original_name }}</div>
                                            <div class="small text-muted">
                                                {{ strtoupper(str_replace('image/', '', $attachment->mime_type)) }}
                                                · {{ number_format($attachment->size_bytes / 1024, 0) }} KB
                                            </div>
                                            <div class="small text-muted">
                                                Uploaded by {{ $attachment->uploadedBy?->name ?? 'System' }}
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="row g-2 small">
                    <div class="col-md-6"><strong>Requester:</strong> {{ $ticket->requester?->name ?? '-' }}</div>
                    <div class="col-md-6"><strong>Department:</strong> {{ $ticket->requesterDepartment?->name ?? '-' }}</div>
                    <div class="col-md-6"><strong>Ticket Type:</strong> {{ $ticket->category?->name ?? '-' }}</div>
                    <div class="col-md-6"><strong>Ticket Category:</strong> {{ $ticket->subcategory?->name ?? '-' }}</div>
                    <div class="col-md-6"><strong>Ticket Sub Category:</strong> {{ $ticket->detailSubcategory?->name ?? '-' }}</div>
                    <div class="col-md-6"><strong>Priority:</strong> {{ $ticket->priority?->name ?? '-' }}</div>
                    <div class="col-md-6"><strong>Related Service:</strong> {{ $ticket->service?->name ?? '-' }}</div>
                    <div class="col-md-6"><strong>Related Asset:</strong> {{ $ticket->asset?->name ?? '-' }}</div>
                    <div class="col-md-6"><strong>Asset Location:</strong> {{ $ticket->assetLocation?->name ?? '-' }}</div>
                    <div class="col-md-6"><strong>Linked Inspection Task:</strong> {{ $ticket->inspection?->inspection_number ?? '-' }}</div>
                    <div class="col-md-6"><strong>Assigned Team:</strong> {{ $ticket->assigned_team_name ?? '-' }}</div>
                    <div class="col-md-6"><strong>Assigned Engineer:</strong> {{ $ticket->assignedEngineer?->name ?? '-' }}</div>
                    <div class="col-md-6">
                        <strong>Approval Rule:</strong>
                        @if ($ticket->requires_approval)
                            <span class="badge bg-warning-subtle text-warning">Approval Required</span>
                        @else
                            <span class="badge bg-success-subtle text-success">No Approval Needed</span>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <strong>Assignment Rule:</strong>
                        @if ($ticket->allow_direct_assignment)
                            <span class="badge bg-success-subtle text-success">Direct Assignment Allowed</span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary">Needs Ready Flag</span>
                        @endif
                    </div>
                    <div class="col-md-6"><strong>Approval Status:</strong> {{ str($ticket->approval_status ?? 'not_required')->replace('_', ' ')->title() }}</div>
                    <div class="col-md-6"><strong>Rule Source:</strong> {{ str($ticket->flow_policy_source ?? 'system_default')->replace('_', ' ')->title() }}</div>
                    <div class="col-md-6"><strong>Expected Approver:</strong> {{ $ticket->expectedApprover?->name ?? $ticket->expected_approver_name_snapshot ?? 'Supervisor/Admin Fallback' }}</div>
                    <div class="col-md-6"><strong>Approved By:</strong> {{ $ticket->approvedBy?->name ?? '-' }}</div>
                    <div class="col-md-6"><strong>Ready For Assignment By:</strong> {{ $ticket->assignmentReadyBy?->name ?? '-' }}</div>
                    <div class="col-md-6"><strong>Approval Notes:</strong> {{ $ticket->approval_notes ?: '-' }}</div>
                    <div class="col-md-6"><strong>Response Due:</strong> {{ optional($ticket->response_due_at)->format('Y-m-d H:i') ?? '-' }}</div>
                    <div class="col-md-6"><strong>Resolution Due:</strong> {{ optional($ticket->resolution_due_at)->format('Y-m-d H:i') ?? '-' }}</div>
                    <div class="col-md-6"><strong>Started At:</strong> {{ optional($ticket->started_at)->format('Y-m-d H:i') ?? '-' }}</div>
                    <div class="col-md-6"><strong>Completed At:</strong> {{ optional($ticket->completed_at)->format('Y-m-d H:i') ?? '-' }}</div>
                    <div class="col-md-6"><strong>Work Duration:</strong> {{ $workDurationMinutes !== null ? $workDurationMinutes.' minute(s)' : '-' }}</div>
                </div>

                <div class="mt-4">
                    <a href="{{ route('tickets.index') }}" class="btn btn-outline-light">Back</a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Approval Matrix Summary</h5>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100 bg-light-subtle">
                            <div class="small text-muted mb-1">Effective Approval</div>
                            <div class="fw-semibold">{{ $ticket->requires_approval ? 'Approval Required' : 'No Approval Required' }}</div>
                            <div class="small text-muted mt-1">Status: {{ $ticket->approvalStatusLabel() }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100 bg-light-subtle">
                            <div class="small text-muted mb-1">Effective Assignment</div>
                            <div class="fw-semibold">{{ $ticket->allow_direct_assignment ? 'Direct Assignment Allowed' : 'Needs Ready Flag' }}</div>
                            <div class="small text-muted mt-1">{{ $ticket->assignmentGateMessage() ?? 'Ticket is ready for assignment flow.' }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100 bg-light-subtle">
                            <div class="small text-muted mb-1">Resolved Approver</div>
                            <div class="fw-semibold">{{ $ticket->expectedApproverDisplayName() }}</div>
                            <div class="small text-muted mt-1">
                                {{ $strategyLabels[$ticket->expected_approver_strategy ?? \App\Models\TicketCategory::APPROVER_STRATEGY_FALLBACK] ?? 'Supervisor/Admin Fallback' }}
                                @if ($ticket->expected_approver_role_code)
                                    · {{ $roleLabel($ticket->expected_approver_role_code) }}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Level</th>
                                <th>Selected Value</th>
                                <th>Approval Policy</th>
                                <th>Assignment Policy</th>
                                <th>Approver Strategy</th>
                                <th>Approver Target</th>
                                <th>Rule Source</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="fw-semibold">Ticket Type</td>
                                <td>{{ $ticket->category?->name ?? '-' }}</td>
                                <td>{{ $ticket->category ? $approvalPolicyLabel($ticket->category->requires_approval, 'System Default') : '-' }}</td>
                                <td>{{ $ticket->category ? $assignmentPolicyLabel($ticket->category->allow_direct_assignment, 'System Default') : '-' }}</td>
                                <td>{{ $approverStrategyLabel($ticket->category, 'Supervisor/Admin Fallback') }}</td>
                                <td>{{ $approverTargetLabel($ticket->category, 'Supervisor/Admin Fallback') }}</td>
                                <td><span class="badge {{ $policySourceBadge('ticket_type', $ticket->flow_policy_source) }}">{{ $ticket->flow_policy_source === 'ticket_type' ? 'Effective Source' : 'Base Rule' }}</span></td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Ticket Category</td>
                                <td>{{ $ticket->subcategory?->name ?? '-' }}</td>
                                <td>{{ $ticket->subcategory ? $approvalPolicyLabel($ticket->subcategory->requires_approval, 'Follow Ticket Type') : '-' }}</td>
                                <td>{{ $ticket->subcategory ? $assignmentPolicyLabel($ticket->subcategory->allow_direct_assignment, 'Follow Ticket Type') : '-' }}</td>
                                <td>{{ $approverStrategyLabel($ticket->subcategory, 'Follow Ticket Type') }}</td>
                                <td>{{ $approverTargetLabel($ticket->subcategory, 'Follow Ticket Type') }}</td>
                                <td><span class="badge {{ $policySourceBadge('ticket_category', $ticket->flow_policy_source) }}">{{ $ticket->flow_policy_source === 'ticket_category' ? 'Effective Source' : 'Override Layer' }}</span></td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Ticket Sub Category</td>
                                <td>{{ $ticket->detailSubcategory?->name ?? '-' }}</td>
                                <td>{{ $ticket->detailSubcategory ? $approvalPolicyLabel($ticket->detailSubcategory->requires_approval, 'Follow Ticket Category') : '-' }}</td>
                                <td>{{ $ticket->detailSubcategory ? $assignmentPolicyLabel($ticket->detailSubcategory->allow_direct_assignment, 'Follow Ticket Category') : '-' }}</td>
                                <td>{{ $approverStrategyLabel($ticket->detailSubcategory, 'Follow Ticket Category') }}</td>
                                <td>{{ $approverTargetLabel($ticket->detailSubcategory, 'Follow Ticket Category') }}</td>
                                <td><span class="badge {{ $policySourceBadge('ticket_sub_category', $ticket->flow_policy_source) }}">{{ $ticket->flow_policy_source === 'ticket_sub_category' ? 'Effective Source' : 'Override Layer' }}</span></td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Snapshot Applied</td>
                                <td>{{ $ticket->flowPolicySourceLabel() }}</td>
                                <td>{{ $ticket->requires_approval ? 'Approval Required' : 'No Approval Required' }}</td>
                                <td>{{ $ticket->allow_direct_assignment ? 'Direct Assignment Allowed' : 'Needs Ready Flag' }}</td>
                                <td>{{ $strategyLabels[$ticket->expected_approver_strategy ?? \App\Models\TicketCategory::APPROVER_STRATEGY_FALLBACK] ?? 'Supervisor/Admin Fallback' }}</td>
                                <td>
                                    {{ $ticket->expectedApproverDisplayName() }}
                                    @if ($ticket->expected_approver_role_code)
                                        <div class="small text-muted">{{ $roleLabel($ticket->expected_approver_role_code) }}</div>
                                    @endif
                                </td>
                                <td><span class="badge bg-success-subtle text-success">Ticket Snapshot</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Approval History</h5>
            </div>
            <div class="card-body">
                @forelse ($approvalActivities as $approvalActivity)
                    @php
                        $approvalLabel = match ($approvalActivity->activity_type) {
                            'ticket_approved' => 'Approved',
                            'ticket_rejected' => 'Rejected',
                            'ticket_ready_for_assignment' => 'Marked Ready For Assignment',
                            default => str($approvalActivity->activity_type)->replace('_', ' ')->title(),
                        };
                    @endphp
                    <div class="border-bottom pb-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                            <div>
                                <div class="fw-semibold">{{ $approvalLabel }}</div>
                                <div class="small text-muted">
                                    {{ optional($approvalActivity->created_at)->format('Y-m-d H:i') }}
                                    by {{ $approvalActivity->actor?->name ?? 'System' }}
                                </div>
                            </div>
                            <span class="badge {{ $approvalActivity->activity_type === 'ticket_approved' ? 'bg-success-subtle text-success' : ($approvalActivity->activity_type === 'ticket_rejected' ? 'bg-danger-subtle text-danger' : 'bg-primary-subtle text-primary') }}">
                                {{ $approvalLabel }}
                            </span>
                        </div>
                        <div class="small text-muted mt-2">
                            {{ data_get($approvalActivity->metadata, 'notes') ?: 'No notes provided.' }}
                        </div>
                    </div>
                @empty
                    <div class="text-muted">Belum ada approval history khusus pada ticket ini.</div>
                @endforelse
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Worklogs</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>User</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Duration</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($ticket->worklogs as $worklog)
                                <tr>
                                    <td>{{ optional($worklog->created_at)->format('Y-m-d H:i') }}</td>
                                    <td>{{ $worklog->user?->name ?? '-' }}</td>
                                    <td>{{ ucfirst($worklog->log_type) }}</td>
                                    <td>{{ $worklog->description }}</td>
                                    <td>{{ $worklog->duration_minutes !== null ? $worklog->duration_minutes.' min' : '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">No worklog yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Assign Engineer</h5>
            </div>
            <div class="card-body">
                <div class="border rounded p-3 mb-3 bg-light-subtle">
                    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                        <div>
                            <div class="fw-semibold mb-1">Assignment Gate</div>
                            @if ($ticket->canBeAssigned())
                                <div class="text-success">Ticket ini sudah siap untuk assignment.</div>
                            @else
                                <div class="text-warning">{{ $ticket->assignmentGateMessage() }}</div>
                            @endif
                            <div class="small text-muted mt-1">
                                Policy source: {{ str($ticket->flow_policy_source ?? 'system_default')->replace('_', ' ')->title() }}.
                            </div>
                            @if ($ticket->requires_approval)
                                <div class="small text-muted mt-1">
                                    Approver: {{ $ticket->expectedApprover?->name ?? $ticket->expected_approver_name_snapshot ?? 'Supervisor/Admin Fallback' }}
                                </div>
                            @endif
                        </div>
                    </div>

                    @if (($ticket->requires_approval && ! $ticket->isApproved() && ! $ticket->isRejected()) || (! $ticket->allow_direct_assignment && ! $ticket->isAssignmentReady() && ! $ticket->isRejected() && (! $ticket->requires_approval || $ticket->isApproved())))
                        <form method="POST" action="{{ route('tickets.approve', $ticket) }}" class="mt-3">
                            @csrf
                            <input type="hidden" name="decision" id="ticket_decision" value="approve">
                            <div class="mb-3">
                                <label for="approval_notes" class="form-label">Approval / Rejection / Readiness Notes</label>
                                <textarea id="approval_notes" name="notes" rows="3" class="form-control" placeholder="Tambahkan catatan approval, alasan reject, atau alasan ticket siap di-assign.">{{ old('notes') }}</textarea>
                                <div class="form-text">Reject dan Mark Ready sebaiknya selalu disertai alasan agar audit trail lebih jelas.</div>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                @if ($ticket->requires_approval && ! $ticket->isApproved() && ! $ticket->isRejected())
                                    <button type="submit" class="btn btn-success" name="decision" value="approve"
                                        @disabled(! $ticket->canBeApprovedBy($currentUser))>
                                        Approve Ticket
                                    </button>
                                    <button type="submit" class="btn btn-outline-danger" formaction="{{ route('tickets.reject', $ticket) }}" name="decision" value="reject"
                                        @disabled(! $ticket->canBeApprovedBy($currentUser))>
                                        Reject Ticket
                                    </button>
                                @endif

                                @if (! $ticket->allow_direct_assignment && ! $ticket->isAssignmentReady() && ! $ticket->isRejected() && (! $ticket->requires_approval || $ticket->isApproved()))
                                    <button type="submit" class="btn btn-outline-primary" formaction="{{ route('tickets.mark-ready', $ticket) }}" name="decision" value="mark_ready">
                                        Mark Ready For Assignment
                                    </button>
                                @endif
                            </div>
                            @if ($ticket->requires_approval && ! $ticket->canBeApprovedBy($currentUser))
                                <div class="small text-muted mt-2">Aksi approve/reject hanya tersedia untuk approver yang ditetapkan atau fallback supervisor/admin.</div>
                            @endif
                        </form>
                    @endif
                </div>

                <form method="GET" action="{{ route('tickets.show', $ticket) }}" class="row g-3 mb-3">
                    <div class="col-12">
                        <div class="small text-muted">Saring shortlist engineer berdasarkan department dan shift operasional sebelum assign.</div>
                    </div>
                    <div class="col-12 col-md-12">
                        <label for="assignment_department_id" class="form-label">Filter Department</label>
                        <select id="assignment_department_id" name="assignment_department_id"
                            class="form-select" data-searchable-select data-force-searchable-select="true"
                            data-search-placeholder="Search department">
                            <option value="">- All Departments -</option>
                            @foreach ($assignmentDepartmentOptions as $departmentOption)
                                <option value="{{ $departmentOption['id'] }}" @selected((string) ($assignmentFilters['department_id'] ?? '') === (string) $departmentOption['id'])>
                                    {{ $departmentOption['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-12">
                        <label for="assignment_team_label" class="form-label">Filter Team / Shift</label>
                        <select id="assignment_team_label" name="assignment_team_label"
                            class="form-select" data-searchable-select data-force-searchable-select="true"
                            data-search-placeholder="Search team or shift">
                            <option value="">- All Teams / Shifts -</option>
                            @foreach ($assignmentTeamOptions as $teamOption)
                                <option value="{{ $teamOption }}" @selected((string) ($assignmentFilters['team_label'] ?? '') === (string) $teamOption)>
                                    {{ $teamOption }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 d-flex flex-wrap justify-content-end gap-2">
                        @if (($assignmentFilters['department_id'] ?? null) || ($assignmentFilters['team_label'] ?? null))
                            <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-outline-light text-nowrap">Reset Filter</a>
                        @endif
                        <button type="submit" class="btn btn-outline-secondary text-nowrap">Apply Filters</button>
                    </div>
                </form>

                @if (($engineerRecommendation['required_skill_labels'] ?? []) !== [])
                    <div class="alert alert-info border">
                        <div class="fw-semibold mb-1">Recommended By Skill Match</div>
                        <div class="small text-muted mb-2">Skor rekomendasi sekarang menggabungkan skill match, availability schedule hari ini, dan workload ticket yang masih terbuka.</div>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach ($engineerRecommendation['required_skill_labels'] as $skillLabel)
                                <span class="badge bg-primary-subtle text-primary">{{ $skillLabel }}</span>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="alert alert-light border">
                        Belum ada skill mapping yang cocok untuk ticket ini, jadi sistem masih menampilkan engineer fallback.
                    </div>
                @endif

                @if (($engineerOptions->count() ?? 0) === 0 && ($fallbackEngineerOptions->count() ?? 0) === 0)
                    <div class="alert alert-warning border">
                        Tidak ada engineer yang cocok dengan filter department/team saat ini.
                    </div>
                @endif

                <form method="POST" action="{{ route('tickets.assign', $ticket) }}" class="row g-3">
                    @csrf

                    <input type="hidden" id="assigned_engineer_id" name="assigned_engineer_id" value="{{ old('assigned_engineer_id', $ticket->assigned_engineer_id) }}">

                    @if ($engineerRecommendation['has_recommendation'] ?? false)
                        <div class="col-12">
                            <div class="row g-2">
                                @foreach ($engineerOptions->take(3) as $option)
                                    <div class="col-12">
                                        <div class="border rounded p-2">
                                            <div class="d-flex justify-content-between align-items-start gap-2">
                                                <div>
                                                    <div class="fw-semibold">{{ $option->name }}</div>
                                                    <div class="small text-muted">
                                                        {{ $option->department_name ?? 'No department' }}
                                                        · {{ $option->team_label ?? 'No team/shift' }}
                                                        · {{ $option->workload_open_tickets ?? 0 }} open ticket(s)
                                                    </div>
                                                    <div class="small text-muted">
                                                        {{ $option->availability_reason ?? ($option->availability_label ?? 'Unknown availability') }}
                                                        @if (!empty($option->today_shift_name))
                                                            ({{ $option->today_shift_name }})
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="d-flex flex-column align-items-end gap-1">
                                                    <span class="badge {{ $scoreBadgeClass((int) ($option->recommendation_score ?? 0)) }}">Score {{ $option->recommendation_score ?? 0 }}</span>
                                                    <span class="badge {{ $availabilityBadgeClass($option->availability_status ?? null) }}">{{ $option->availability_label ?? 'Unknown' }}</span>
                                                    <span class="badge {{ ($option->workload_status ?? 'light') === 'busy' ? 'bg-danger-subtle text-danger' : (($option->workload_status ?? 'light') === 'moderate' ? 'bg-warning-subtle text-warning' : 'bg-info-subtle text-info') }}">{{ $option->workload_label ?? 'Light' }}</span>
                                                </div>
                                            </div>
                                            @if (!empty($option->matched_skill_names))
                                                <div class="d-flex flex-wrap gap-1 mt-2">
                                                    @foreach ($option->matched_skill_names as $matchedSkillName)
                                                        <span class="badge bg-primary-subtle text-primary">{{ $matchedSkillName }}</span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="border rounded p-3 bg-light-subtle h-100">
                                <div class="fw-semibold mb-2">Recommended Engineers</div>
                                <label for="recommended_engineer_id_ui" class="form-label">Best Match By Score</label>
                                <select id="recommended_engineer_id_ui"
                                    data-searchable-select data-force-searchable-select="true"
                                    data-engineer-picker="true" data-search-placeholder="Search recommended engineer"
                                    class="form-select @error('assigned_engineer_id') is-invalid @enderror" data-assignment-source="recommended">
                                    <option value="">- Select Recommended Engineer -</option>
                                    @foreach ($engineerOptions as $option)
                                        <option value="{{ $option->id }}"
                                            data-custom-properties='@json($engineerCustomProperties($option))'
                                            @selected((string) old('assigned_engineer_id', $ticket->assigned_engineer_id) === (string) $option->id)>
                                            {{ $option->name }}
                                            @if (!empty($option->matched_skill_names))
                                                - {{ implode(', ', $option->matched_skill_names) }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">Daftar ini diurutkan berdasarkan total skor recommendation, bukan hanya skill yang cocok.</div>
                            </div>
                        </div>

                        @if (($fallbackEngineerOptions->count() ?? 0) > 0)
                            <div class="col-12">
                                <div class="border rounded p-3 h-100">
                                    <div class="fw-semibold mb-2">Fallback Engineers</div>
                                    <label for="fallback_engineer_id_ui" class="form-label">Alternative Engineer Pool</label>
                                    <select id="fallback_engineer_id_ui"
                                        data-searchable-select data-force-searchable-select="true"
                                        data-engineer-picker="true" data-search-placeholder="Search fallback engineer"
                                        class="form-select" data-assignment-source="fallback">
                                        <option value="">- Use Recommended List -</option>
                                        @foreach ($fallbackEngineerOptions as $option)
                                            <option value="{{ $option->id }}"
                                                data-custom-properties='@json($engineerCustomProperties($option))'
                                                @selected((string) old('assigned_engineer_id', $ticket->assigned_engineer_id) === (string) $option->id)>
                                                {{ $option->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="form-text">Dipakai jika supervisor perlu override karena pertimbangan kapasitas, shift, atau kebutuhan lapangan.</div>
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="col-12">
                            <label for="fallback_engineer_id_ui" class="form-label">Engineer</label>
                            <select id="fallback_engineer_id_ui"
                                data-searchable-select data-force-searchable-select="true"
                                data-engineer-picker="true" data-search-placeholder="Search engineer"
                                class="form-select @error('assigned_engineer_id') is-invalid @enderror" data-assignment-source="fallback" required>
                                <option value="">- Select Engineer -</option>
                                @foreach ($fallbackEngineerOptions as $option)
                                    <option value="{{ $option->id }}"
                                        data-custom-properties='@json($engineerCustomProperties($option))'
                                        @selected((string) old('assigned_engineer_id', $ticket->assigned_engineer_id) === (string) $option->id)>
                                        {{ $option->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Belum ada skill mapping yang match. Urutan fallback tetap mempertimbangkan availability schedule dan workload engineer.</div>
                        </div>
                    @endif

                    @error('assigned_engineer_id')
                        <div class="col-12">
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        </div>
                    @enderror

                    <div class="col-12">
                        <label for="assigned_team_name" class="form-label">Team</label>
                        <input type="text" id="assigned_team_name" name="assigned_team_name"
                            class="form-control @error('assigned_team_name') is-invalid @enderror"
                            value="{{ old('assigned_team_name', $ticket->assigned_team_name) }}" placeholder="Ops / Field Team">
                        @error('assigned_team_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea id="notes" name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary w-100" @disabled(! $ticket->canBeAssigned())>Assign / Reassign</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Activity Timeline</h5>
            </div>
            <div class="card-body">
                @forelse ($ticket->activities as $activity)
                    <div class="border-bottom pb-2 mb-2">
                        <div class="fw-semibold">{{ str_replace('_', ' ', strtoupper($activity->activity_type)) }}</div>
                        <div class="small text-muted">
                            {{ optional($activity->created_at)->format('Y-m-d H:i') }}
                            by {{ $activity->actor?->name ?? 'System' }}
                        </div>
                        <div class="small">
                            {{ $activity->oldStatus?->name ?? '-' }}
                            <span class="text-muted">to</span>
                            {{ $activity->newStatus?->name ?? '-' }}
                        </div>
                    </div>
                @empty
                    <p class="text-muted mb-0">No activity yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const hiddenAssignedEngineerInput = document.getElementById('assigned_engineer_id');
        const recommendedSelect = document.getElementById('recommended_engineer_id_ui');
        const fallbackSelect = document.getElementById('fallback_engineer_id_ui');

        if (!hiddenAssignedEngineerInput) {
            return;
        }

        const syncAssignmentValue = (source) => {
            if (source === 'recommended' && recommendedSelect) {
                hiddenAssignedEngineerInput.value = recommendedSelect.value || '';
                if (fallbackSelect && recommendedSelect.value) {
                    fallbackSelect.value = '';
                    if (fallbackSelect._choices) {
                        fallbackSelect._choices.removeActiveItems();
                    }
                }
                return;
            }

            if (source === 'fallback' && fallbackSelect) {
                hiddenAssignedEngineerInput.value = fallbackSelect.value || '';
                if (recommendedSelect && fallbackSelect.value) {
                    recommendedSelect.value = '';
                    if (recommendedSelect._choices) {
                        recommendedSelect._choices.removeActiveItems();
                    }
                }
            }
        };

        const bootstrapSelectedState = () => {
            const currentValue = hiddenAssignedEngineerInput.value;
            if (!currentValue) {
                return;
            }

            if (recommendedSelect && Array.from(recommendedSelect.options).some((option) => option.value === currentValue)) {
                recommendedSelect.value = currentValue;
                if (recommendedSelect._choices) {
                    recommendedSelect._choices.setChoiceByValue(currentValue);
                }
                return;
            }

            if (fallbackSelect && Array.from(fallbackSelect.options).some((option) => option.value === currentValue)) {
                fallbackSelect.value = currentValue;
                if (fallbackSelect._choices) {
                    fallbackSelect._choices.setChoiceByValue(currentValue);
                }
            }
        };

        recommendedSelect?.addEventListener('change', function() {
            syncAssignmentValue('recommended');
        });

        fallbackSelect?.addEventListener('change', function() {
            syncAssignmentValue('fallback');
        });

        bootstrapSelectedState();
    });
</script>
@endpush
