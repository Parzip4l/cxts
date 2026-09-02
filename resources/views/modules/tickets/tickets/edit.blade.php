@extends('layouts.vertical', ['subtitle' => 'Edit Ticket'])

@section('content')
@include('layouts.partials.page-title', ['title' => 'Ticketing', 'subtitle' => 'Edit ' . $ticket->ticket_number])

@php
    $activeAssignedEngineers = $ticket->assignedEngineers->isNotEmpty()
        ? $ticket->assignedEngineers
        : collect([$ticket->assignedEngineer])->filter();
    $selectedAssignedEngineerIds = collect(old('assigned_engineer_ids', $activeAssignedEngineers->pluck('id')->all()))
        ->map(fn ($id) => (string) $id)
        ->all();
    $latestAssignmentNotes = old('assignment_notes', $ticket->assignments->first()?->notes);
    $groupedEngineerOptions = $engineerOptions->groupBy(
        fn ($engineer) => $engineer->department?->name ?? 'No Engineer Team'
    );
    $engineerTeamNameOptions = $groupedEngineerOptions->keys()->filter()->values();
    $selectedProcessType = old('process_type', $ticket->process_type ?: \App\Models\Ticket::PROCESS_TYPE_INCIDENT);
    $selectedDetectionSource = old('incident_detection_source', $ticket->incident_detection_source);
    $selectedResolutionCode = old('incident_resolution_code', $ticket->incident_resolution_code);
    $selectedChangeRisk = old('change_risk_level', $ticket->change_risk_level);
    $selectedChangeReviewResult = old('change_review_result', $ticket->change_review_result);
@endphp

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
            <div>
                <div class="text-uppercase small text-muted fw-semibold mb-1">Ticket Adjustment</div>
                <h4 class="mb-1">Edit ticket tanpa cancel</h4>
                <p class="text-muted mb-0">Ubah nama ticket, deskripsi, dan assignment agar score engineer tidak terdampak oleh cancel yang tidak perlu.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-outline-light">Back to Detail</a>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('tickets.update', $ticket) }}" class="row g-4">
            @csrf
            @method('PUT')

            <div class="col-lg-7">
                <div class="border rounded-3 p-4 h-100 bg-light-subtle">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                        <div>
                            <div class="text-uppercase small text-muted fw-semibold mb-1">Ticket Core</div>
                            <h5 class="mb-1">Summary & Description</h5>
                        </div>
                        <span class="badge bg-primary-subtle text-primary">{{ $ticket->ticket_number }}</span>
                    </div>

                    <div class="row g-3">
                        <div class="col-12">
                            <label for="process_type" class="form-label">Process Type</label>
                            <select id="process_type" name="process_type" class="form-select @error('process_type') is-invalid @enderror">
                                @foreach ($processTypeOptions as $processTypeCode => $processTypeLabel)
                                    <option value="{{ $processTypeCode }}" @selected($selectedProcessType === $processTypeCode)>
                                        {{ $processTypeLabel }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Klasifikasi ITIL ringan untuk memisahkan Incident, Service Request, dan Change Request.</div>
                            @error('process_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="title" class="form-label">Nama Ticket</label>
                            <input
                                type="text"
                                id="title"
                                name="title"
                                class="form-control @error('title') is-invalid @enderror"
                                value="{{ old('title', $ticket->title) }}"
                                maxlength="200"
                                required
                            >
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="description" class="form-label">Deskripsi</label>
                            <textarea
                                id="description"
                                name="description"
                                rows="8"
                                class="form-control @error('description') is-invalid @enderror"
                                required
                            >{{ old('description', $ticket->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6" data-process-scope="incident">
                            <label for="incident_detection_source" class="form-label">Detection Source</label>
                            <select id="incident_detection_source" name="incident_detection_source" class="form-select @error('incident_detection_source') is-invalid @enderror">
                                <option value="">- Optional -</option>
                                @foreach ($detectionSourceOptions as $sourceCode => $sourceLabel)
                                    <option value="{{ $sourceCode }}" @selected($selectedDetectionSource === $sourceCode)>
                                        {{ $sourceLabel }}
                                    </option>
                                @endforeach
                            </select>
                            @error('incident_detection_source')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6" data-process-scope="incident">
                            <label for="incident_resolution_code" class="form-label">Resolution Code</label>
                            <select id="incident_resolution_code" name="incident_resolution_code" class="form-select @error('incident_resolution_code') is-invalid @enderror">
                                <option value="">- Not Set -</option>
                                @foreach ($resolutionCodeOptions as $resolutionCode => $resolutionLabel)
                                    <option value="{{ $resolutionCode }}" @selected($selectedResolutionCode === $resolutionCode)>
                                        {{ $resolutionLabel }}
                                    </option>
                                @endforeach
                            </select>
                            @error('incident_resolution_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6" data-process-scope="incident">
                            <label for="affected_users_count" class="form-label">Affected Users</label>
                            <input
                                type="number"
                                min="0"
                                id="affected_users_count"
                                name="affected_users_count"
                                class="form-control @error('affected_users_count') is-invalid @enderror"
                                value="{{ old('affected_users_count', $ticket->affected_users_count) }}"
                            >
                            @error('affected_users_count')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6" data-process-scope="incident">
                            <input type="hidden" name="is_major_incident" value="0">
                            <div class="form-check form-switch border rounded p-3 ps-5 bg-white h-100">
                                <input
                                    type="checkbox"
                                    id="is_major_incident"
                                    name="is_major_incident"
                                    value="1"
                                    class="form-check-input @error('is_major_incident') is-invalid @enderror"
                                    @checked((bool) old('is_major_incident', $ticket->is_major_incident))
                                >
                                <label class="form-check-label fw-semibold" for="is_major_incident">Major Incident</label>
                                <div class="small text-muted">Gunakan untuk gangguan berdampak luas atau butuh perhatian manajemen.</div>
                                @error('is_major_incident')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-12" data-process-scope="incident">
                            <label for="service_impact_note" class="form-label">Service Impact Note</label>
                            <textarea
                                id="service_impact_note"
                                name="service_impact_note"
                                rows="3"
                                class="form-control @error('service_impact_note') is-invalid @enderror"
                            >{{ old('service_impact_note', $ticket->service_impact_note) }}</textarea>
                            @error('service_impact_note')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12" data-process-scope="change_request">
                            <label for="change_reason" class="form-label">Change Reason</label>
                            <textarea
                                id="change_reason"
                                name="change_reason"
                                rows="3"
                                class="form-control @error('change_reason') is-invalid @enderror"
                            >{{ old('change_reason', $ticket->change_reason) }}</textarea>
                            @error('change_reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4" data-process-scope="change_request">
                            <label for="change_risk_level" class="form-label">Change Risk</label>
                            <select id="change_risk_level" name="change_risk_level" class="form-select @error('change_risk_level') is-invalid @enderror">
                                <option value="">- Optional -</option>
                                @foreach ($changeRiskOptions as $riskCode => $riskLabel)
                                    <option value="{{ $riskCode }}" @selected($selectedChangeRisk === $riskCode)>
                                        {{ $riskLabel }}
                                    </option>
                                @endforeach
                            </select>
                            @error('change_risk_level')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4" data-process-scope="change_request">
                            <label for="change_planned_start_at" class="form-label">Planned Start</label>
                            <input
                                type="datetime-local"
                                id="change_planned_start_at"
                                name="change_planned_start_at"
                                class="form-control @error('change_planned_start_at') is-invalid @enderror"
                                value="{{ old('change_planned_start_at', optional($ticket->change_planned_start_at)->format('Y-m-d\\TH:i')) }}"
                            >
                            @error('change_planned_start_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4" data-process-scope="change_request">
                            <label for="change_planned_end_at" class="form-label">Planned End</label>
                            <input
                                type="datetime-local"
                                id="change_planned_end_at"
                                name="change_planned_end_at"
                                class="form-control @error('change_planned_end_at') is-invalid @enderror"
                                value="{{ old('change_planned_end_at', optional($ticket->change_planned_end_at)->format('Y-m-d\\TH:i')) }}"
                            >
                            @error('change_planned_end_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6" data-process-scope="change_request">
                            <label for="change_rollback_plan" class="form-label">Rollback Plan</label>
                            <textarea
                                id="change_rollback_plan"
                                name="change_rollback_plan"
                                rows="3"
                                class="form-control @error('change_rollback_plan') is-invalid @enderror"
                            >{{ old('change_rollback_plan', $ticket->change_rollback_plan) }}</textarea>
                            @error('change_rollback_plan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6" data-process-scope="change_request">
                            <label for="change_affected_scope" class="form-label">Affected Scope</label>
                            <textarea
                                id="change_affected_scope"
                                name="change_affected_scope"
                                rows="3"
                                class="form-control @error('change_affected_scope') is-invalid @enderror"
                            >{{ old('change_affected_scope', $ticket->change_affected_scope) }}</textarea>
                            @error('change_affected_scope')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4" data-process-scope="change_request">
                            <label for="change_review_result" class="form-label">PIR Result</label>
                            <select id="change_review_result" name="change_review_result" class="form-select @error('change_review_result') is-invalid @enderror">
                                <option value="">- Later -</option>
                                @foreach ($changeReviewResultOptions as $resultCode => $resultLabel)
                                    <option value="{{ $resultCode }}" @selected($selectedChangeReviewResult === $resultCode)>
                                        {{ $resultLabel }}
                                    </option>
                                @endforeach
                            </select>
                            @error('change_review_result')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-8" data-process-scope="change_request">
                            <label for="change_review_notes" class="form-label">PIR Notes</label>
                            <textarea
                                id="change_review_notes"
                                name="change_review_notes"
                                rows="2"
                                class="form-control @error('change_review_notes') is-invalid @enderror"
                            >{{ old('change_review_notes', $ticket->change_review_notes) }}</textarea>
                            @error('change_review_notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="border rounded-3 p-4 h-100 bg-light-subtle">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                        <div>
                            <div class="text-uppercase small text-muted fw-semibold mb-1">Assignment</div>
                            <h5 class="mb-1">Engineer & Team</h5>
                        </div>
                        <span class="badge bg-info-subtle text-info">{{ $activeAssignedEngineers->count() }} assigned</span>
                    </div>

                    @if ($canManageAssignment)
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="assigned_engineer_ids" class="form-label">Engineer</label>
                                <select
                                    id="assigned_engineer_ids"
                                    name="assigned_engineer_ids[]"
                                    class="form-select @error('assigned_engineer_ids') is-invalid @enderror @error('assigned_engineer_ids.*') is-invalid @enderror"
                                    data-searchable-select
                                    data-force-searchable-select="true"
                                    data-search-placeholder="Search engineer"
                                    multiple
                                >
                                    @foreach ($groupedEngineerOptions as $picTeamLabel => $groupedOptions)
                                        <optgroup label="{{ $picTeamLabel }}">
                                            @foreach ($groupedOptions as $option)
                                                <option value="{{ $option->id }}" @selected(in_array((string) $option->id, $selectedAssignedEngineerIds, true))>
                                                    {{ $option->name }}
                                                    @if ($option->department)
                                                        - {{ $option->department->name }}
                                                    @endif
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                <div class="form-text">Pilih ulang engineer aktif dengan grouping Engineer Team agar assignment lebih stabil daripada mengikuti shift.</div>
                                @error('assigned_engineer_ids')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @error('assigned_engineer_ids.*')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="assigned_team_name" class="form-label">Team Assigned</label>
                                <input
                                    type="text"
                                    id="assigned_team_name"
                                    name="assigned_team_name"
                                    class="form-control @error('assigned_team_name') is-invalid @enderror"
                                    value="{{ old('assigned_team_name', $ticket->assigned_team_name) }}"
                                    placeholder="Ops / Field Team"
                                    list="ticket-edit-engineer-team-options"
                                >
                                <datalist id="ticket-edit-engineer-team-options">
                                    @foreach ($engineerTeamNameOptions as $teamName)
                                        <option value="{{ $teamName }}"></option>
                                    @endforeach
                                </datalist>
                                @error('assigned_team_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="assignment_notes" class="form-label">Assignment Notes</label>
                                <textarea
                                    id="assignment_notes"
                                    name="assignment_notes"
                                    rows="4"
                                    class="form-control @error('assignment_notes') is-invalid @enderror"
                                    placeholder="Catatan perubahan assignment"
                                >{{ $latestAssignmentNotes }}</textarea>
                                @error('assignment_notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    @else
                        <div class="alert alert-light border mb-0">
                            Anda bisa mengubah nama dan deskripsi ticket, tetapi perubahan assignment hanya tersedia untuk user yang memiliki otorisasi dispatch engineer.
                        </div>
                    @endif
                </div>
            </div>

            <div class="col-12">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                    <div class="small text-muted">Perubahan disimpan pada ticket yang sama, jadi histori dan score engineer tetap konsisten.</div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-outline-light">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('script-bottom')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const processTypeSelect = document.getElementById('process_type');
        const scopedFields = document.querySelectorAll('[data-process-scope]');

        const syncProcessScopedFields = () => {
            const processType = processTypeSelect?.value || 'incident';
            scopedFields.forEach((field) => {
                field.classList.toggle('d-none', field.dataset.processScope !== processType);
            });
        };

        processTypeSelect?.addEventListener('change', syncProcessScopedFields);
        syncProcessScopedFields();
    });
</script>
@endsection
