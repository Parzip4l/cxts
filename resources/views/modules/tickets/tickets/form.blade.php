@extends('layouts.vertical', ['subtitle' => 'Create Ticket'])

@section('content')
@include('layouts.partials.page-title', ['title' => 'Ticketing', 'subtitle' => 'Create Ticket'])

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

    $selectedPriorityId = old('ticket_priority_id', $ticket->ticket_priority_id ?: $defaultPriorityId);
    $selectedSource = old('source', $ticket->source ?: 'web');
    $selectedImpact = old('impact', $ticket->impact ?: 'medium');
    $selectedUrgency = old('urgency', $ticket->urgency ?: 'medium');
    $selectedPriorityLabel = optional($priorityOptions->firstWhere('id', $selectedPriorityId))->name ?? 'Medium';

    $initialStep = 1;
    if ($errors->hasAny(['service_id', 'asset_id', 'asset_location_id', 'context_mode'])) {
        $initialStep = 2;
    }
    if ($errors->hasAny(['requester_id', 'requester_department_id', 'ticket_priority_id', 'source', 'impact', 'urgency', 'ticket_detail_subcategory_id'])) {
        $initialStep = 3;
    }
    if ($errors->hasAny(['attachments', 'attachments.*'])) {
        $initialStep = 1;
    }
@endphp

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body p-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-lg-center">
            <div>
                <div class="text-uppercase small text-muted fw-semibold mb-1">Simplified Ticket Creation</div>
                <h4 class="mb-2">Create Ticket In 3 Steps</h4>
            </div>
            <div class="small text-muted">
                <div>1. Masalah apa yang terjadi</div>
                <div>2. Apa yang terdampak</div>
                <div>3. Review dan triage operasional</div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body p-4">
        <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="row g-4" id="ticket-create-form" data-initial-step="{{ $initialStep }}">
            @csrf

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
                            <label for="title" class="form-label">Issue Summary</label>
                            <input
                                type="text"
                                id="title"
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
                            <label for="ticket_category_id" class="form-label">Ticket Type</label>
                            <select id="ticket_category_id" name="ticket_category_id" class="form-select @error('ticket_category_id') is-invalid @enderror" required>
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
                            <label for="ticket_subcategory_id" class="form-label">Ticket Category</label>
                            <select id="ticket_subcategory_id" name="ticket_subcategory_id" class="form-select @error('ticket_subcategory_id') is-invalid @enderror">
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
                            <label for="ticket_detail_subcategory_id" class="form-label">Ticket Sub Category</label>
                            <select id="ticket_detail_subcategory_id" name="ticket_detail_subcategory_id" class="form-select @error('ticket_detail_subcategory_id') is-invalid @enderror">
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
                            <label for="description" class="form-label">Issue Description</label>
                            <textarea
                                id="description"
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
                            <label for="attachments" class="form-label">Lampiran Foto</label>
                            <input
                                type="file"
                                id="attachments"
                                name="attachments[]"
                                class="form-control @error('attachments') is-invalid @enderror @error('attachments.*') is-invalid @enderror"
                                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                multiple
                            >
                            <div class="form-text">Opsional. Maksimal 5 foto, masing-masing maksimal 5MB. Format yang diizinkan: JPG, PNG, WEBP.</div>
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
                        <input type="radio" class="btn-check" name="context_mode" id="context_mode_none" value="none" @checked($selectedContextMode === 'none')>
                        <label class="btn btn-outline-secondary" for="context_mode_none">No Specific Context</label>

                        <input type="radio" class="btn-check" name="context_mode" id="context_mode_service" value="service" @checked($selectedContextMode === 'service')>
                        <label class="btn btn-outline-primary" for="context_mode_service">Related Service</label>

                        <input type="radio" class="btn-check" name="context_mode" id="context_mode_asset" value="asset" @checked($selectedContextMode === 'asset')>
                        <label class="btn btn-outline-primary" for="context_mode_asset">Related Asset</label>

                        <input type="radio" class="btn-check" name="context_mode" id="context_mode_location" value="location" @checked($selectedContextMode === 'location')>
                        <label class="btn btn-outline-primary" for="context_mode_location">Asset Location</label>
                    </div>

                    <input type="hidden" id="asset_location_id" name="asset_location_id" value="{{ old('asset_location_id', $ticket->asset_location_id) }}">

                    <div class="alert alert-light border mb-0" data-context-panel="none">
                        Ticket akan dibuat tanpa service, asset, atau lokasi spesifik. Cocok untuk permintaan umum atau kendala yang objek terdampaknya belum jelas.
                    </div>

                    <div class="row g-3 d-none" data-context-panel="service">
                        <div class="col-lg-8">
                            <label for="service_id" class="form-label">Related Service</label>
                            <select id="service_id" name="service_id" class="form-select @error('service_id') is-invalid @enderror" data-searchable-select data-search-placeholder="Search service">
                                <option value="">- Select Related Service -</option>
                                @foreach ($serviceOptions as $option)
                                    <option value="{{ $option->id }}" @selected((string) old('service_id', $ticket->service_id) === (string) $option->id)>
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
                            <label for="asset_id" class="form-label">Related Asset</label>
                            <select id="asset_id" name="asset_id" class="form-select @error('asset_id') is-invalid @enderror" data-searchable-select data-search-placeholder="Search asset">
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
                            <label for="asset_location_id_asset_mode" class="form-label">Asset Location</label>
                            <select id="asset_location_id_asset_mode" class="form-select @error('asset_location_id') is-invalid @enderror" data-searchable-select data-search-placeholder="Search asset location">
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
                            <label for="asset_location_id_location_mode" class="form-label">Asset Location</label>
                            <select id="asset_location_id_location_mode" class="form-select @error('asset_location_id') is-invalid @enderror" data-searchable-select data-search-placeholder="Search asset location">
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

                    <div id="ticket-context-smart-hint" class="alert alert-info border d-none mt-3 mb-0"></div>
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
                                <label for="requester_id" class="form-label">Requester Override</label>
                                <select id="requester_id" name="requester_id" class="form-select @error('requester_id') is-invalid @enderror" data-searchable-select data-search-placeholder="Search requester">
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
                                <label for="requester_department_id" class="form-label">Requester Department Override</label>
                                <select id="requester_department_id" name="requester_department_id" class="form-select @error('requester_department_id') is-invalid @enderror" data-searchable-select data-search-placeholder="Search department">
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
                                <label for="ticket_priority_id" class="form-label">Priority</label>
                                <select id="ticket_priority_id" name="ticket_priority_id" class="form-select @error('ticket_priority_id') is-invalid @enderror" required>
                                    <option value="">- Select -</option>
                                    @foreach ($priorityOptions as $option)
                                        <option value="{{ $option->id }}" @selected((string) $selectedPriorityId === (string) $option->id)>
                                            {{ $option->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('ticket_priority_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="source" class="form-label">Source</label>
                                <select id="source" name="source" class="form-select @error('source') is-invalid @enderror">
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
                                <label for="impact" class="form-label">Impact</label>
                                <select id="impact" name="impact" class="form-select @error('impact') is-invalid @enderror">
                                    <option value="low" @selected($selectedImpact === 'low')>Low</option>
                                    <option value="medium" @selected($selectedImpact === 'medium')>Medium</option>
                                    <option value="high" @selected($selectedImpact === 'high')>High</option>
                                </select>
                                @error('impact')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="urgency" class="form-label">Urgency</label>
                                <select id="urgency" name="urgency" class="form-select @error('urgency') is-invalid @enderror">
                                    <option value="low" @selected($selectedUrgency === 'low')>Low</option>
                                    <option value="medium" @selected($selectedUrgency === 'medium')>Medium</option>
                                    <option value="high" @selected($selectedUrgency === 'high')>High</option>
                                </select>
                                @error('urgency')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    @else
                        <div class="alert alert-light border mb-0">
                            Ticket akan menggunakan default operasional agar user tidak perlu mengisi terlalu banyak field. Tim service desk tetap bisa melakukan triage setelah ticket dibuat.
                        </div>
                        <input type="hidden" name="ticket_priority_id" value="{{ $selectedPriorityId }}">
                        <input type="hidden" name="source" value="{{ $selectedSource }}">
                        <input type="hidden" name="impact" value="{{ $selectedImpact }}">
                        <input type="hidden" name="urgency" value="{{ $selectedUrgency }}">
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
                    <a href="{{ route('tickets.index') }}" class="btn btn-outline-light">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('ticket-create-form');
        if (!form) {
            return;
        }

        const categorySelect = document.getElementById('ticket_category_id');
        const subcategorySelect = document.getElementById('ticket_subcategory_id');
        const subcategoryWrapper = form.querySelector('[data-subcategory-wrapper]');
        const detailSubcategorySelect = document.getElementById('ticket_detail_subcategory_id');
        const detailSubcategoryWrapper = form.querySelector('[data-detail-subcategory-wrapper]');
        const contextInputs = document.querySelectorAll('input[name="context_mode"]');
        const contextPanels = document.querySelectorAll('[data-context-panel]');
        const serviceSelect = document.getElementById('service_id');
        const assetSelect = document.getElementById('asset_id');
        const sharedLocationInput = document.getElementById('asset_location_id');
        const locationAssetModeSelect = document.getElementById('asset_location_id_asset_mode');
        const locationModeSelect = document.getElementById('asset_location_id_location_mode');
        const smartHint = document.getElementById('ticket-context-smart-hint');
        const stepPanels = Array.from(form.querySelectorAll('[data-step-panel]'));
        const stepTriggers = Array.from(form.querySelectorAll('[data-step-trigger]'));
        const prevButton = form.querySelector('[data-step-action="prev"]');
        const nextButton = form.querySelector('[data-step-action="next"]');
        const submitButton = form.querySelector('[data-step-action="submit"]');
        const maxStep = stepPanels.length;
        let currentStep = Number(form.dataset.initialStep || 1);

        const toggleSubcategory = () => {
            if (!categorySelect || !subcategorySelect) {
                return;
            }

            const selectedCategoryId = categorySelect.value;
            let hasVisibleSubcategory = false;

            Array.from(subcategorySelect.options).forEach((option, index) => {
                if (index === 0) {
                    option.hidden = false;
                    return;
                }

                const categoryId = option.getAttribute('data-category-id');
                const visible = selectedCategoryId === '' || categoryId === selectedCategoryId;
                option.hidden = !visible;
                hasVisibleSubcategory = hasVisibleSubcategory || (selectedCategoryId !== '' && visible);

                if (!visible && option.selected) {
                    option.selected = false;
                }
            });

            if (subcategoryWrapper) {
                subcategoryWrapper.classList.toggle('d-none', selectedCategoryId === '' || !hasVisibleSubcategory);
            }

            if (selectedCategoryId === '') {
                subcategorySelect.value = '';
                if (subcategorySelect._choices) {
                    subcategorySelect._choices.removeActiveItems();
                }
            }

            if (!detailSubcategorySelect) {
                return;
            }

            const selectedSubcategoryId = subcategorySelect.value;
            let hasVisibleDetailSubcategory = false;

            Array.from(detailSubcategorySelect.options).forEach((option, index) => {
                if (index === 0) {
                    option.hidden = false;
                    return;
                }

                const parentSubcategoryId = option.getAttribute('data-subcategory-id');
                const visible = selectedSubcategoryId !== '' && parentSubcategoryId === selectedSubcategoryId;
                option.hidden = !visible;
                hasVisibleDetailSubcategory = hasVisibleDetailSubcategory || visible;

                if (!visible && option.selected) {
                    option.selected = false;
                }
            });

            if (detailSubcategoryWrapper) {
                detailSubcategoryWrapper.classList.toggle('d-none', selectedSubcategoryId === '' || !hasVisibleDetailSubcategory);
            }

            if (selectedSubcategoryId === '') {
                detailSubcategorySelect.value = '';
                if (detailSubcategorySelect._choices) {
                    detailSubcategorySelect._choices.removeActiveItems();
                }
            }
        };

        const syncChoicesSelect = (select, value) => {
            if (!select) {
                return;
            }

            select.value = value || '';

            if (select._choices) {
                select._choices.removeActiveItems();
                if (value) {
                    select._choices.setChoiceByValue(String(value));
                }
            }
        };

        const clearInactiveContext = (mode) => {
            if (mode !== 'service') {
                syncChoicesSelect(serviceSelect, '');
            }

            if (mode !== 'asset') {
                syncChoicesSelect(assetSelect, '');
            }

            if (mode === 'none' || mode === 'service') {
                if (sharedLocationInput) {
                    sharedLocationInput.value = '';
                }
                syncChoicesSelect(locationAssetModeSelect, '');
                syncChoicesSelect(locationModeSelect, '');
            }

            if (mode === 'asset') {
                syncChoicesSelect(locationModeSelect, '');
            }

            if (mode === 'location') {
                syncChoicesSelect(locationAssetModeSelect, '');
            }
        };

        const syncLocationMirror = () => {
            if (!sharedLocationInput) {
                return;
            }

            const activeMode = document.querySelector('input[name="context_mode"]:checked')?.value || 'none';

            if (activeMode === 'asset' && locationAssetModeSelect) {
                sharedLocationInput.value = locationAssetModeSelect.value;
                return;
            }

            if (activeMode === 'location' && locationModeSelect) {
                sharedLocationInput.value = locationModeSelect.value;
                return;
            }

            sharedLocationInput.value = '';
        };

        const getOptionByValue = (select, value) => {
            if (!select || !value) {
                return null;
            }

            return Array.from(select.options).find((option) => option.value === String(value)) ?? null;
        };

        const setSmartHint = (message) => {
            if (!smartHint) {
                return;
            }

            smartHint.innerHTML = message || '';
            smartHint.classList.toggle('d-none', !message);
        };

        const updateSmartContextHint = () => {
            const activeMode = document.querySelector('input[name="context_mode"]:checked')?.value || 'none';

            if (activeMode === 'service' && serviceSelect?.value) {
                const relatedAssets = Array.from(assetSelect?.options ?? [])
                    .filter((option) => option.value !== '' && option.dataset.serviceId === serviceSelect.value)
                    .map((option) => option.textContent.trim());

                if (relatedAssets.length > 0) {
                    setSmartHint(`Service ini terhubung ke ${relatedAssets.length} asset. Contoh terkait: <strong>${relatedAssets.slice(0, 3).join(', ')}</strong>.`);
                } else {
                    setSmartHint('Belum ada asset aktif yang terhubung langsung ke service ini.');
                }

                return;
            }

            if (activeMode === 'asset' && assetSelect?.value) {
                const selectedAssetOption = getOptionByValue(assetSelect, assetSelect.value);
                const relatedServiceId = selectedAssetOption?.dataset.serviceId || '';
                const relatedLocationId = selectedAssetOption?.dataset.locationId || '';
                const relatedServiceName = getOptionByValue(serviceSelect, relatedServiceId)?.textContent?.trim();
                const relatedLocationName = getOptionByValue(locationAssetModeSelect ?? locationModeSelect, relatedLocationId)?.textContent?.trim();

                if (relatedServiceId) {
                    syncChoicesSelect(serviceSelect, relatedServiceId);
                }

                if (relatedLocationId && locationAssetModeSelect && !locationAssetModeSelect.value) {
                    syncChoicesSelect(locationAssetModeSelect, relatedLocationId);
                }

                syncLocationMirror();

                const details = [
                    relatedServiceName ? `service <strong>${relatedServiceName}</strong>` : null,
                    relatedLocationName ? `location <strong>${relatedLocationName}</strong>` : null,
                ].filter(Boolean);

                setSmartHint(details.length > 0
                    ? `Asset ini terhubung ke ${details.join(' dan ')}. Field terkait sudah dibantu isi otomatis jika datanya tersedia.`
                    : 'Asset ini belum punya relasi service atau location yang lengkap di master data.');

                return;
            }

            if (activeMode === 'location' && locationModeSelect?.value) {
                const relatedAssets = Array.from(assetSelect?.options ?? [])
                    .filter((option) => option.value !== '' && option.dataset.locationId === locationModeSelect.value)
                    .map((option) => option.textContent.trim());

                if (relatedAssets.length > 0) {
                    setSmartHint(`Di location ini ada ${relatedAssets.length} asset terkait. Contoh: <strong>${relatedAssets.slice(0, 3).join(', ')}</strong>.`);
                } else {
                    setSmartHint('Belum ada asset aktif yang dipetakan ke location ini.');
                }

                return;
            }

            setSmartHint('');
        };

        const syncContextPanels = () => {
            const activeMode = document.querySelector('input[name="context_mode"]:checked')?.value || 'none';

            contextPanels.forEach((panel) => {
                panel.classList.toggle('d-none', panel.dataset.contextPanel !== activeMode);
            });

            if (serviceSelect) {
                serviceSelect.required = activeMode === 'service';
            }

            if (assetSelect) {
                assetSelect.required = activeMode === 'asset';
            }

            if (locationModeSelect) {
                locationModeSelect.required = activeMode === 'location';
            }

            clearInactiveContext(activeMode);
            syncLocationMirror();
            updateSmartContextHint();
        };

        const fieldsForStep = (step) => {
            if (step === 1) {
                return [
                    document.getElementById('title'),
                    document.getElementById('ticket_category_id'),
                    document.getElementById('description'),
                ].filter(Boolean);
            }

            if (step === 2) {
                const activeMode = document.querySelector('input[name="context_mode"]:checked')?.value || 'none';
                const fields = [];

                if (activeMode === 'service' && serviceSelect) {
                    fields.push(serviceSelect);
                }
                if (activeMode === 'asset' && assetSelect) {
                    fields.push(assetSelect);
                }
                if (activeMode === 'location' && locationModeSelect) {
                    fields.push(locationModeSelect);
                }

                return fields;
            }

            return [];
        };

        const validateStep = (step) => {
            const fields = fieldsForStep(step);
            for (const field of fields) {
                if (!field.checkValidity()) {
                    field.reportValidity();
                    return false;
                }
            }

            return true;
        };

        const showStep = (step) => {
            currentStep = Math.min(Math.max(step, 1), maxStep);

            stepPanels.forEach((panel) => {
                panel.classList.toggle('d-none', Number(panel.dataset.stepPanel) !== currentStep);
            });

            stepTriggers.forEach((trigger) => {
                const stepNumber = Number(trigger.dataset.stepTrigger);
                const isActive = stepNumber === currentStep;
                trigger.classList.toggle('btn-primary', isActive);
                trigger.classList.toggle('text-white', isActive);
                trigger.classList.toggle('btn-outline-primary', !isActive);
            });

            prevButton?.classList.toggle('d-none', currentStep === 1);
            nextButton?.classList.toggle('d-none', currentStep === maxStep);
            submitButton?.classList.toggle('d-none', currentStep !== maxStep);
        };

        categorySelect?.addEventListener('change', toggleSubcategory);
        subcategorySelect?.addEventListener('change', toggleSubcategory);
        contextInputs.forEach((input) => input.addEventListener('change', syncContextPanels));
        serviceSelect?.addEventListener('change', updateSmartContextHint);
        assetSelect?.addEventListener('change', updateSmartContextHint);
        locationAssetModeSelect?.addEventListener('change', syncLocationMirror);
        locationAssetModeSelect?.addEventListener('change', updateSmartContextHint);
        locationModeSelect?.addEventListener('change', syncLocationMirror);
        locationModeSelect?.addEventListener('change', updateSmartContextHint);

        stepTriggers.forEach((trigger) => {
            trigger.addEventListener('click', () => {
                const targetStep = Number(trigger.dataset.stepTrigger);
                if (targetStep > currentStep && !validateStep(currentStep)) {
                    return;
                }
                showStep(targetStep);
            });
        });

        nextButton?.addEventListener('click', () => {
            if (!validateStep(currentStep)) {
                return;
            }
            showStep(currentStep + 1);
        });

        prevButton?.addEventListener('click', () => showStep(currentStep - 1));

        toggleSubcategory();
        syncContextPanels();
        updateSmartContextHint();
        showStep(currentStep);
    });
</script>
@endpush
