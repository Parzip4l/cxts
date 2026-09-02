@php
    $isModal = $isModal ?? false;
    $formId = $formId ?? 'ticket-create-form';
    $returnTo = $returnTo ?? null;
@endphp

@php
    $userRole = auth()->user()?->role;
    $canUseOperationalTriage = in_array($userRole, ['super_admin', 'operational_admin', 'supervisor'], true);
    $selectedContextMode = old('context_mode');

    if ($selectedContextMode === null) {
        if ($ticket->asset_id) {
            $selectedContextMode = 'asset';
        } elseif ($ticket->service_id) {
            $selectedContextMode = 'service';
        } elseif ($ticket->asset_location_id) {
            $selectedContextMode = 'location';
        } else {
            $selectedContextMode = 'none';
        }
    }

    $selectedPriorityId = old('ticket_priority_id', $ticket->ticket_priority_id);
    $selectedProcessType = old('process_type', $ticket->process_type ?: \App\Models\Ticket::PROCESS_TYPE_INCIDENT);
    $selectedDetectionSource = old('incident_detection_source', $ticket->incident_detection_source ?: \App\Models\Ticket::DETECTION_SOURCE_USER_REPORT);
    $selectedResolutionCode = old('incident_resolution_code', $ticket->incident_resolution_code);
    $selectedChangeRisk = old('change_risk_level', $ticket->change_risk_level ?: 'medium');
    $selectedChangeReviewResult = old('change_review_result', $ticket->change_review_result);
    $selectedSource = old('source', $ticket->source ?: 'web');
    $selectedImpact = old('impact', $ticket->impact ?: 'medium');
    $selectedUrgency = old('urgency', $ticket->urgency ?: 'medium');
    $selectedPriorityLabel = optional($priorityOptions->firstWhere('id', $selectedPriorityId))->name ?? 'Auto from Impact/Urgency';
    $selectedAssignedEngineerIds = collect(old('assigned_engineer_ids', []))
        ->map(fn ($id) => (string) $id)
        ->all();

    $initialStep = 1;
    if ($errors->hasAny(['service_id', 'asset_id', 'asset_location_id', 'context_mode'])) {
        $initialStep = 2;
    }
    if ($errors->hasAny(['requester_id', 'requester_department_id', 'process_type', 'incident_detection_source', 'affected_users_count', 'service_impact_note', 'incident_resolution_code', 'change_reason', 'change_risk_level', 'change_planned_start_at', 'change_planned_end_at', 'change_rollback_plan', 'change_affected_scope', 'change_review_result', 'change_review_notes', 'ticket_priority_id', 'source', 'impact', 'urgency', 'ticket_detail_subcategory_id'])) {
        $initialStep = 3;
    }
    if ($errors->hasAny(['assigned_engineer_ids', 'assigned_engineer_ids.*', 'assigned_team_name', 'assignment_notes'])) {
        $initialStep = 4;
    }
    if ($errors->hasAny(['attachments', 'attachments.*'])) {
        $initialStep = 1;
    }

    $groupedEngineerOptions = $engineerOptions->groupBy(
        fn ($engineer) => $engineer->department?->name ?? 'No Engineer Team'
    );
    $engineerTeamNameOptions = $groupedEngineerOptions->keys()->filter()->values();
@endphp

@if (! $isModal)
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-lg-center">
                <div>
                    <div class="text-uppercase small text-muted fw-semibold mb-1">Simplified Ticket Creation</div>
                    <h4 class="mb-2">Create Ticket In 4 Steps</h4>
                </div>
                <div class="small text-muted">
                    <div>1. Masalah apa yang terjadi</div>
                    <div>2. Apa yang terdampak</div>
                    <div>3. Review dan triage operasional</div>
                    <div>4. Assignment engineer opsional</div>
                </div>
            </div>
        </div>
    </div>
@endif

<div class="card {{ $isModal ? 'border-0 shadow-none mb-0' : '' }}">
    <div class="card-body {{ $isModal ? 'p-0' : 'p-4' }}">
        <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="row g-4" id="{{ $formId }}" data-ticket-create-form data-initial-step="{{ $initialStep }}">
            @csrf
            @if ($returnTo)
                <input type="hidden" name="return_to" value="{{ $returnTo }}">
            @endif

            <div class="col-12">
                <div class="d-flex flex-column flex-lg-row gap-2 gap-lg-3" data-stepper>
                    <button type="button" class="btn btn-outline-primary text-start px-3 py-3 flex-fill" data-step-trigger="1">
                        <div class="fw-semibold">Step 1</div>
                        <div class="small text-muted">Issue Basics</div>
                    </button>
                    <button type="button" class="btn btn-outline-primary text-start px-3 py-3 flex-fill" data-step-trigger="2">
                        <div class="fw-semibold">Step 2</div>
                        <div class="small text-muted">Affected Context</div>
                    </button>
                    <button type="button" class="btn btn-outline-primary text-start px-3 py-3 flex-fill" data-step-trigger="3">
                        <div class="fw-semibold">Step 3</div>
                        <div class="small text-muted">Review & Triage</div>
                    </button>
                    <button type="button" class="btn btn-outline-primary text-start px-3 py-3 flex-fill" data-step-trigger="4">
                        <div class="fw-semibold">Step 4</div>
                        <div class="small text-muted">Engineer Assignment</div>
                    </button>
                </div>
            </div>

            <div class="col-12" data-step-panel="1">
                <div class="border rounded-3 p-3 p-lg-4 bg-light-subtle">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                        <div>
                            <div class="text-uppercase small text-muted fw-semibold mb-1">Step 1</div>
                            <h5 class="mb-1">Report The Issue</h5>
                        </div>
                        <div class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2">Core Input</div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="{{ $formId }}-title" class="form-label">Issue Summary</label>
                            <input
                                type="text"
                                id="{{ $formId }}-title"
                                name="title"
                                class="form-control @error('title') is-invalid @enderror"
                                value="{{ old('title', $ticket->title) }}"
                                placeholder="Contoh: Internet kantor lantai 3 putus"
                                required
                            >
                            <div class="form-text">Gunakan kalimat singkat yang langsung menjelaskan masalah utama.</div>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="{{ $formId }}-ticket_category_id" class="form-label">Ticket Type</label>
                            <select id="{{ $formId }}-ticket_category_id" name="ticket_category_id" class="form-select @error('ticket_category_id') is-invalid @enderror" required>
                                <option value="">- Select -</option>
                                @foreach ($categoryOptions as $option)
                                    <option value="{{ $option->id }}" @selected((string) old('ticket_category_id', $ticket->ticket_category_id) === (string) $option->id)>
                                        {{ $option->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Pilih jenis ticket yang paling mendekati kebutuhan atau gangguan.</div>
                            @error('ticket_category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 d-none" data-subcategory-wrapper>
                            <label for="{{ $formId }}-ticket_subcategory_id" class="form-label">Ticket Category</label>
                            <select id="{{ $formId }}-ticket_subcategory_id" name="ticket_subcategory_id" class="form-select @error('ticket_subcategory_id') is-invalid @enderror">
                                <option value="">- Optional -</option>
                                @foreach ($subcategoryOptions as $option)
                                    <option value="{{ $option->id }}" data-category-id="{{ $option->ticket_category_id }}" @selected((string) old('ticket_subcategory_id', $ticket->ticket_subcategory_id) === (string) $option->id)>
                                        {{ $option->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Opsional. Pilih jika Anda sudah tahu kategori yang lebih spesifik.</div>
                            @error('ticket_subcategory_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 d-none" data-detail-subcategory-wrapper>
                            <label for="{{ $formId }}-ticket_detail_subcategory_id" class="form-label">Ticket Sub Category</label>
                            <select id="{{ $formId }}-ticket_detail_subcategory_id" name="ticket_detail_subcategory_id" class="form-select @error('ticket_detail_subcategory_id') is-invalid @enderror">
                                <option value="">- Optional -</option>
                                @foreach ($detailSubcategoryOptions as $option)
                                    <option value="{{ $option->id }}" data-subcategory-id="{{ $option->ticket_subcategory_id }}" @selected((string) old('ticket_detail_subcategory_id', $ticket->ticket_detail_subcategory_id) === (string) $option->id)>
                                        {{ $option->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Opsional. Gunakan jika Anda ingin klasifikasi yang lebih detail untuk reporting dan analisis.</div>
                            @error('ticket_detail_subcategory_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="{{ $formId }}-description" class="form-label">Issue Description</label>
                            <textarea
                                id="{{ $formId }}-description"
                                name="description"
                                rows="5"
                                class="form-control @error('description') is-invalid @enderror"
                                placeholder="Jelaskan gejala masalah, dampak ke user, dan kapan mulai terjadi"
                                required
                            >{{ old('description', $ticket->description) }}</textarea>
                            <div class="form-text">Tulis gejala yang terlihat, dampak ke pekerjaan user, dan petunjuk apa pun yang bisa membantu tim.</div>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="{{ $formId }}-attachments" class="form-label">Lampiran Foto</label>
                            <input
                                type="file"
                                id="{{ $formId }}-attachments"
                                name="attachments[]"
                                class="form-control @error('attachments') is-invalid @enderror @error('attachments.*') is-invalid @enderror"
                                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                multiple
                            >
                            <div class="form-text">Maksimal 1MB. Format yang diizinkan: JPG, PNG, WEBP.</div>
                            @error('attachments')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @error('attachments.*')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 d-none" data-step-panel="2">
                <div class="border rounded-3 p-3 p-lg-4 bg-light-subtle">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                        <div>
                            <div class="text-uppercase small text-muted fw-semibold mb-1">Step 2</div>
                            <h5 class="mb-1">What Is Affected?</h5>
                            <p class="text-muted mb-0">Pilih satu konteks utama agar tim operasional lebih cepat memahami area yang terdampak.</p>
                        </div>
                        <div class="badge bg-info-subtle text-info border border-info-subtle px-3 py-2">Optional Context</div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <input type="radio" class="btn-check" name="context_mode" id="{{ $formId }}-context_mode_none" value="none" @checked($selectedContextMode === 'none')>
                        <label class="btn btn-outline-secondary" for="{{ $formId }}-context_mode_none">No Specific Context</label>

                        <input type="radio" class="btn-check" name="context_mode" id="{{ $formId }}-context_mode_service" value="service" @checked($selectedContextMode === 'service')>
                        <label class="btn btn-outline-primary" for="{{ $formId }}-context_mode_service">Related Service</label>

                        <input type="radio" class="btn-check" name="context_mode" id="{{ $formId }}-context_mode_asset" value="asset" @checked($selectedContextMode === 'asset')>
                        <label class="btn btn-outline-primary" for="{{ $formId }}-context_mode_asset">Related Asset</label>

                        <input type="radio" class="btn-check" name="context_mode" id="{{ $formId }}-context_mode_location" value="location" @checked($selectedContextMode === 'location')>
                        <label class="btn btn-outline-primary" for="{{ $formId }}-context_mode_location">Asset Location</label>
                    </div>

                    <input type="hidden" id="{{ $formId }}-asset_location_id" name="asset_location_id" value="{{ old('asset_location_id', $ticket->asset_location_id) }}">

                    <div class="alert alert-light border mb-0" data-context-panel="none">
                        Ticket akan dibuat tanpa service, asset, atau lokasi spesifik. Cocok untuk permintaan umum atau kendala yang objek terdampaknya belum jelas.
                    </div>

                    <div class="row g-3 d-none" data-context-panel="service">
                        <div class="col-lg-8">
                            <label for="{{ $formId }}-service_id" class="form-label">Related Service</label>
                            <select id="{{ $formId }}-service_id" name="service_id" class="form-select @error('service_id') is-invalid @enderror" data-searchable-select data-search-placeholder="Search service">
                                <option value="">- Select Related Service -</option>
                                @foreach ($serviceOptions as $option)
                                    <option
                                        value="{{ $option->id }}"
                                        data-is-requestable="{{ $option->is_requestable ? '1' : '0' }}"
                                        data-default-approval="{{ $option->default_request_approval_required === null ? '' : ($option->default_request_approval_required ? '1' : '0') }}"
                                        data-default-sla-id="{{ $option->default_request_sla_policy_id }}"
                                        data-fulfillment-team="{{ $option->fulfillment_team_name }}"
                                        data-request-form-schema='@json($option->request_form_schema ?? [])'
                                        @selected((string) old('service_id', $ticket->service_id) === (string) $option->id)
                                    >
                                        {{ $option->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Gunakan jika gangguan atau request terkait layanan tertentu.</div>
                            @error('service_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row g-3 d-none" data-context-panel="asset">
                        <div class="col-lg-6">
                            <label for="{{ $formId }}-asset_id" class="form-label">Related Asset</label>
                            <select id="{{ $formId }}-asset_id" name="asset_id" class="form-select @error('asset_id') is-invalid @enderror" data-searchable-select data-search-placeholder="Search asset">
                                <option value="">- Select Related Asset -</option>
                                @foreach ($assetOptions as $option)
                                    <option
                                        value="{{ $option->id }}"
                                        data-service-id="{{ $option->service_id }}"
                                        data-location-id="{{ $option->asset_location_id }}"
                                        @selected((string) old('asset_id', $ticket->asset_id) === (string) $option->id)
                                    >
                                        {{ $option->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Pilih perangkat atau unit yang paling dekat dengan masalah.</div>
                            @error('asset_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-lg-6">
                            <label for="{{ $formId }}-asset_location_id_asset_mode" class="form-label">Asset Location</label>
                            <select id="{{ $formId }}-asset_location_id_asset_mode" class="form-select @error('asset_location_id') is-invalid @enderror" data-searchable-select data-search-placeholder="Search asset location">
                                <option value="">- Optional Location -</option>
                                @foreach ($locationOptions as $option)
                                    <option value="{{ $option->id }}" @selected($selectedContextMode === 'asset' && (string) old('asset_location_id', $ticket->asset_location_id) === (string) $option->id)>
                                        {{ $option->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Opsional. Isi jika aset berada di site atau area yang spesifik.</div>
                            @error('asset_location_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row g-3 d-none" data-context-panel="location">
                        <div class="col-lg-8">
                            <label for="{{ $formId }}-asset_location_id_location_mode" class="form-label">Asset Location</label>
                            <select id="{{ $formId }}-asset_location_id_location_mode" class="form-select @error('asset_location_id') is-invalid @enderror" data-searchable-select data-search-placeholder="Search asset location">
                                <option value="">- Select Location -</option>
                                @foreach ($locationOptions as $option)
                                    <option value="{{ $option->id }}" @selected($selectedContextMode === 'location' && (string) old('asset_location_id', $ticket->asset_location_id) === (string) $option->id)>
                                        {{ $option->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Gunakan jika user hanya tahu site, area, atau ruang yang terdampak.</div>
                            @error('asset_location_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div id="{{ $formId }}-ticket-context-smart-hint" class="alert alert-info border d-none mt-3 mb-0"></div>
                    <div id="{{ $formId }}-request-form-fields" class="row g-3 mt-1 d-none" data-request-form-fields></div>
                </div>
            </div>

            <div class="col-12 d-none" data-step-panel="3">
                <div class="border rounded-3 p-3 p-lg-4 bg-light-subtle">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                        <div>
                            <div class="text-uppercase small text-muted fw-semibold mb-1">Step 3</div>
                            <h5 class="mb-1">Review & Operational Triage</h5>
                            <p class="text-muted mb-0">Langkah terakhir untuk memastikan ticket siap dibuat. User biasa cukup review ringkas, supervisor bisa menambahkan triage operasional.</p>
                        </div>
                        <div class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">Ready To Submit</div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <div class="border rounded p-3 h-100 bg-white">
                                <div class="small text-muted mb-1">Process Type</div>
                                <div class="fw-semibold">{{ $processTypeOptions[$selectedProcessType] ?? 'Incident' }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3 h-100 bg-white">
                                <div class="small text-muted mb-1">Priority Default</div>
                                <div class="fw-semibold">{{ $selectedPriorityLabel }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3 h-100 bg-white">
                                <div class="small text-muted mb-1">Impact Default</div>
                                <div class="fw-semibold text-capitalize">{{ $selectedImpact }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3 h-100 bg-white">
                                <div class="small text-muted mb-1">Urgency Default</div>
                                <div class="fw-semibold text-capitalize">{{ $selectedUrgency }}</div>
                            </div>
                        </div>
                    </div>

                    @if ($canUseOperationalTriage)
                        <div class="alert alert-info border mb-3">
                            Anda login sebagai role operasional, jadi pengaturan requester override, priority, source, impact, dan urgency tetap tersedia di bawah ini.
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="{{ $formId }}-process_type" class="form-label">Process Type</label>
                                <select id="{{ $formId }}-process_type" name="process_type" class="form-select @error('process_type') is-invalid @enderror">
                                    @foreach ($processTypeOptions as $processTypeCode => $processTypeLabel)
                                        <option value="{{ $processTypeCode }}" @selected($selectedProcessType === $processTypeCode)>
                                            {{ $processTypeLabel }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">Gunakan Incident untuk gangguan, Service Request untuk permintaan standar, Change Request untuk perubahan terencana.</div>
                                @error('process_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6" data-process-scope="incident">
                                <label for="{{ $formId }}-incident_detection_source" class="form-label">Detection Source</label>
                                <select id="{{ $formId }}-incident_detection_source" name="incident_detection_source" class="form-select @error('incident_detection_source') is-invalid @enderror">
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

                            <div class="col-md-6">
                                <label for="{{ $formId }}-requester_id" class="form-label">Requester Override</label>
                                <select id="{{ $formId }}-requester_id" name="requester_id" class="form-select @error('requester_id') is-invalid @enderror" data-searchable-select data-search-placeholder="Search requester">
                                    <option value="">- Auto Current User -</option>
                                    @foreach ($requesterOptions as $option)
                                        <option value="{{ $option->id }}" @selected((string) old('requester_id', $ticket->requester_id ?? $defaultRequesterId) === (string) $option->id)>
                                            {{ $option->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('requester_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="{{ $formId }}-requester_department_id" class="form-label">Requester Department Override</label>
                                <select id="{{ $formId }}-requester_department_id" name="requester_department_id" class="form-select @error('requester_department_id') is-invalid @enderror" data-searchable-select data-search-placeholder="Search department">
                                    <option value="">- Auto Current User Department -</option>
                                    @foreach ($requesterDepartmentOptions as $option)
                                        <option value="{{ $option->id }}" @selected((string) old('requester_department_id', $ticket->requester_department_id ?? $defaultRequesterDepartmentId) === (string) $option->id)>
                                            {{ $option->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('requester_department_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="{{ $formId }}-ticket_priority_id" class="form-label">Priority</label>
                                <select id="{{ $formId }}-ticket_priority_id" name="ticket_priority_id" class="form-select @error('ticket_priority_id') is-invalid @enderror">
                                    <option value="">Auto from Impact/Urgency</option>
                                    @foreach ($priorityOptions as $option)
                                        <option value="{{ $option->id }}" @selected((string) $selectedPriorityId === (string) $option->id)>
                                            {{ $option->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">Kosongkan untuk memakai matrix otomatis: High+High = Critical, salah satu High = High, Low+Low = Low.</div>
                                @error('ticket_priority_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="{{ $formId }}-source" class="form-label">Source</label>
                                <select id="{{ $formId }}-source" name="source" class="form-select @error('source') is-invalid @enderror">
                                    <option value="web" @selected($selectedSource === 'web')>Web</option>
                                    <option value="email" @selected($selectedSource === 'email')>Email</option>
                                    <option value="phone" @selected($selectedSource === 'phone')>Phone</option>
                                    <option value="api" @selected($selectedSource === 'api')>API</option>
                                </select>
                                @error('source')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="{{ $formId }}-impact" class="form-label">Impact</label>
                                <select id="{{ $formId }}-impact" name="impact" class="form-select @error('impact') is-invalid @enderror">
                                    <option value="low" @selected($selectedImpact === 'low')>Low</option>
                                    <option value="medium" @selected($selectedImpact === 'medium')>Medium</option>
                                    <option value="high" @selected($selectedImpact === 'high')>High</option>
                                </select>
                                @error('impact')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="{{ $formId }}-urgency" class="form-label">Urgency</label>
                                <select id="{{ $formId }}-urgency" name="urgency" class="form-select @error('urgency') is-invalid @enderror">
                                    <option value="low" @selected($selectedUrgency === 'low')>Low</option>
                                    <option value="medium" @selected($selectedUrgency === 'medium')>Medium</option>
                                    <option value="high" @selected($selectedUrgency === 'high')>High</option>
                                </select>
                                @error('urgency')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3" data-process-scope="incident">
                                <label for="{{ $formId }}-affected_users_count" class="form-label">Affected Users</label>
                                <input
                                    type="number"
                                    min="0"
                                    id="{{ $formId }}-affected_users_count"
                                    name="affected_users_count"
                                    class="form-control @error('affected_users_count') is-invalid @enderror"
                                    value="{{ old('affected_users_count', $ticket->affected_users_count) }}"
                                    placeholder="0"
                                >
                                @error('affected_users_count')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3" data-process-scope="incident">
                                <label for="{{ $formId }}-incident_resolution_code" class="form-label">Resolution Code</label>
                                <select id="{{ $formId }}-incident_resolution_code" name="incident_resolution_code" class="form-select @error('incident_resolution_code') is-invalid @enderror">
                                    <option value="">- Later -</option>
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
                                <input type="hidden" name="is_major_incident" value="0">
                                <div class="form-check form-switch border rounded p-3 ps-5 bg-white h-100">
                                    <input
                                        type="checkbox"
                                        id="{{ $formId }}-is_major_incident"
                                        name="is_major_incident"
                                        value="1"
                                        class="form-check-input @error('is_major_incident') is-invalid @enderror"
                                        @checked((bool) old('is_major_incident', $ticket->is_major_incident))
                                    >
                                    <label class="form-check-label fw-semibold" for="{{ $formId }}-is_major_incident">Major Incident</label>
                                    <div class="small text-muted">Aktifkan hanya jika dampak layanan luas atau butuh perhatian manajemen.</div>
                                    @error('is_major_incident')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6" data-process-scope="incident">
                                <label for="{{ $formId }}-service_impact_note" class="form-label">Service Impact Note</label>
                                <textarea
                                    id="{{ $formId }}-service_impact_note"
                                    name="service_impact_note"
                                    rows="3"
                                    class="form-control @error('service_impact_note') is-invalid @enderror"
                                    placeholder="Ringkas dampak ke layanan atau user"
                                >{{ old('service_impact_note', $ticket->service_impact_note) }}</textarea>
                                @error('service_impact_note')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6" data-process-scope="change_request">
                                <label for="{{ $formId }}-change_reason" class="form-label">Change Reason</label>
                                <textarea
                                    id="{{ $formId }}-change_reason"
                                    name="change_reason"
                                    rows="3"
                                    class="form-control @error('change_reason') is-invalid @enderror"
                                    placeholder="Alasan bisnis/operasional perubahan"
                                >{{ old('change_reason', $ticket->change_reason) }}</textarea>
                                @error('change_reason')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3" data-process-scope="change_request">
                                <label for="{{ $formId }}-change_risk_level" class="form-label">Change Risk</label>
                                <select id="{{ $formId }}-change_risk_level" name="change_risk_level" class="form-select @error('change_risk_level') is-invalid @enderror">
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

                            <div class="col-md-3" data-process-scope="change_request">
                                <label for="{{ $formId }}-change_planned_start_at" class="form-label">Planned Start</label>
                                <input
                                    type="datetime-local"
                                    id="{{ $formId }}-change_planned_start_at"
                                    name="change_planned_start_at"
                                    class="form-control @error('change_planned_start_at') is-invalid @enderror"
                                    value="{{ old('change_planned_start_at', optional($ticket->change_planned_start_at)->format('Y-m-d\\TH:i')) }}"
                                >
                                @error('change_planned_start_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3" data-process-scope="change_request">
                                <label for="{{ $formId }}-change_planned_end_at" class="form-label">Planned End</label>
                                <input
                                    type="datetime-local"
                                    id="{{ $formId }}-change_planned_end_at"
                                    name="change_planned_end_at"
                                    class="form-control @error('change_planned_end_at') is-invalid @enderror"
                                    value="{{ old('change_planned_end_at', optional($ticket->change_planned_end_at)->format('Y-m-d\\TH:i')) }}"
                                >
                                @error('change_planned_end_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6" data-process-scope="change_request">
                                <label for="{{ $formId }}-change_rollback_plan" class="form-label">Rollback Plan</label>
                                <textarea
                                    id="{{ $formId }}-change_rollback_plan"
                                    name="change_rollback_plan"
                                    rows="3"
                                    class="form-control @error('change_rollback_plan') is-invalid @enderror"
                                    placeholder="Langkah rollback jika perubahan gagal"
                                >{{ old('change_rollback_plan', $ticket->change_rollback_plan) }}</textarea>
                                @error('change_rollback_plan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6" data-process-scope="change_request">
                                <label for="{{ $formId }}-change_affected_scope" class="form-label">Affected Scope</label>
                                <textarea
                                    id="{{ $formId }}-change_affected_scope"
                                    name="change_affected_scope"
                                    rows="3"
                                    class="form-control @error('change_affected_scope') is-invalid @enderror"
                                    placeholder="Service, asset, user, atau lokasi terdampak"
                                >{{ old('change_affected_scope', $ticket->change_affected_scope) }}</textarea>
                                @error('change_affected_scope')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4" data-process-scope="change_request">
                                <label for="{{ $formId }}-change_review_result" class="form-label">PIR Result</label>
                                <select id="{{ $formId }}-change_review_result" name="change_review_result" class="form-select @error('change_review_result') is-invalid @enderror">
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
                                <label for="{{ $formId }}-change_review_notes" class="form-label">PIR Notes</label>
                                <textarea
                                    id="{{ $formId }}-change_review_notes"
                                    name="change_review_notes"
                                    rows="2"
                                    class="form-control @error('change_review_notes') is-invalid @enderror"
                                    placeholder="Catatan hasil implementasi setelah change selesai"
                                >{{ old('change_review_notes', $ticket->change_review_notes) }}</textarea>
                                @error('change_review_notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    @else
                        <div class="alert alert-light border mb-0">
                            Ticket akan menggunakan default operasional agar user tidak perlu mengisi terlalu banyak field. Tim service desk tetap bisa melakukan triage setelah ticket dibuat.
                        </div>
                        <input type="hidden" name="process_type" value="{{ $selectedProcessType }}">
                        <input type="hidden" name="source" value="{{ $selectedSource }}">
                        <input type="hidden" name="impact" value="{{ $selectedImpact }}">
                        <input type="hidden" name="urgency" value="{{ $selectedUrgency }}">
                    @endif
                </div>
            </div>

            <div class="col-12 d-none" data-step-panel="4">
                <div class="border rounded-3 p-3 p-lg-4 bg-light-subtle">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                        <div>
                            <div class="text-uppercase small text-muted fw-semibold mb-1">Step 4</div>
                            <h5 class="mb-1">Assign Engineer</h5>
                            <p class="text-muted mb-0">Pilih satu atau beberapa engineer jika ticket langsung boleh di-assign setelah dibuat.</p>
                        </div>
                        <div class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2">Optional Dispatch</div>
                    </div>

                    @if ($canUseOperationalTriage)
                        <div class="alert alert-info border">
                            Assignment di step ini akan diproses otomatis jika ticket tidak tertahan approval atau readiness gate. Jika ticket butuh approval, pilihan engineer tetap tidak memaksa bypass governance.
                        </div>

                        <div class="row g-3">
                            <div class="col-12">
                                <label for="{{ $formId }}-assigned_engineer_ids" class="form-label">Engineer</label>
                                <select
                                    id="{{ $formId }}-assigned_engineer_ids"
                                    name="assigned_engineer_ids[]"
                                    class="form-select @error('assigned_engineer_ids') is-invalid @enderror @error('assigned_engineer_ids.*') is-invalid @enderror"
                                    data-searchable-select
                                    data-force-searchable-select="true"
                                    data-search-placeholder="Search engineer"
                                    multiple
                                >
                                    @foreach ($groupedEngineerOptions as $teamLabel => $groupedOptions)
                                        <optgroup label="{{ $teamLabel }}">
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
                                <div class="form-text">Bisa pilih lebih dari satu engineer. Daftar dikelompokkan per master Engineer Team agar tidak bergantung pada shift harian.</div>
                                @error('assigned_engineer_ids')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @error('assigned_engineer_ids.*')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="{{ $formId }}-assigned_team_name" class="form-label">Team</label>
                                <input
                                    type="text"
                                    id="{{ $formId }}-assigned_team_name"
                                    name="assigned_team_name"
                                    class="form-control @error('assigned_team_name') is-invalid @enderror"
                                    value="{{ old('assigned_team_name') }}"
                                    placeholder="Ops / Field Team"
                                    list="{{ $formId }}-engineer-team-options"
                                >
                                <datalist id="{{ $formId }}-engineer-team-options">
                                    @foreach ($engineerTeamNameOptions as $teamName)
                                        <option value="{{ $teamName }}"></option>
                                    @endforeach
                                </datalist>
                                @error('assigned_team_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="{{ $formId }}-assignment_notes" class="form-label">Assignment Notes</label>
                                <input
                                    type="text"
                                    id="{{ $formId }}-assignment_notes"
                                    name="assignment_notes"
                                    class="form-control @error('assignment_notes') is-invalid @enderror"
                                    value="{{ old('assignment_notes') }}"
                                    placeholder="Instruksi singkat untuk engineer"
                                >
                                @error('assignment_notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    @else
                        <div class="alert alert-light border mb-0">
                            Assignment engineer dilakukan oleh supervisor atau admin setelah ticket dibuat.
                        </div>
                    @endif
                </div>
            </div>

            <div class="col-12 pt-1">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary d-none" data-step-action="prev">Back</button>
                        <button type="button" class="btn btn-primary" data-step-action="next">Continue</button>
                        <button type="submit" class="btn btn-success d-none" data-step-action="submit">Create Ticket</button>
                    </div>
                    @if ($isModal)
                        <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Close</button>
                    @else
                        <a href="{{ route('tickets.index') }}" class="btn btn-outline-light">Cancel</a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>
