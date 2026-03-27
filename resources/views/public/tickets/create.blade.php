@extends('layouts.base', ['subtitle' => 'Public Ticket'])

@section('content')
@php
    $selectedContextMode = old('context_mode');

    if ($selectedContextMode === null) {
        if (old('asset_id')) {
            $selectedContextMode = 'asset';
        } elseif (old('service_id')) {
            $selectedContextMode = 'service';
        } elseif (old('asset_location_id')) {
            $selectedContextMode = 'location';
        } else {
            $selectedContextMode = 'none';
        }
    }

    $selectedPriorityLabel = optional($priorityOptions->firstWhere('id', old('ticket_priority_id', $defaultPriorityId)))->name ?? 'Medium';
    $initialStep = 1;
    if ($errors->hasAny(['title', 'ticket_category_id', 'ticket_subcategory_id', 'ticket_detail_subcategory_id', 'description'])) {
        $initialStep = 2;
    }
    if ($errors->hasAny(['service_id', 'asset_id', 'asset_location_id', 'context_mode'])) {
        $initialStep = 3;
    }
    if ($errors->hasAny(['attachments', 'attachments.*'])) {
        $initialStep = 2;
    }
@endphp

<div class="account-pages py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-10 col-lg-11">
                <div class="card border-0 shadow-lg">
                    <div class="card-body p-4 p-lg-5">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h4 class="fw-bold text-dark mb-1">Submit Ticket</h4>
                                <p class="text-muted mb-0">Laporkan masalah tanpa login. Cukup isi inti masalah dan pilih konteks yang paling relevan.</p>
                            </div>
                            <a href="{{ route('login') }}" class="btn btn-outline-dark">Staff Login</a>
                        </div>

                        @if (session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        <form method="POST" action="{{ route('public.tickets.store') }}" enctype="multipart/form-data" class="row g-4" id="public-ticket-form" data-initial-step="{{ $initialStep }}">
                            @csrf
                            <input type="hidden" name="ticket_priority_id" value="{{ old('ticket_priority_id', $defaultPriorityId) }}">
                            <input type="hidden" name="impact" value="{{ old('impact', 'medium') }}">
                            <input type="hidden" name="urgency" value="{{ old('urgency', 'medium') }}">
                            <input type="hidden" id="asset_location_id" name="asset_location_id" value="{{ old('asset_location_id') }}">

                            <div class="col-12">
                                <div class="d-flex flex-column flex-lg-row gap-2 gap-lg-3" data-stepper>
                                    <button type="button" class="btn btn-outline-dark text-start px-3 py-3 flex-fill" data-step-trigger="1">
                                        <div class="fw-semibold">Step 1</div>
                                        <div class="small text-muted">Reporter Info</div>
                                    </button>
                                    <button type="button" class="btn btn-outline-dark text-start px-3 py-3 flex-fill" data-step-trigger="2">
                                        <div class="fw-semibold">Step 2</div>
                                        <div class="small text-muted">Issue Basics</div>
                                    </button>
                                    <button type="button" class="btn btn-outline-dark text-start px-3 py-3 flex-fill" data-step-trigger="3">
                                        <div class="fw-semibold">Step 3</div>
                                        <div class="small text-muted">Affected Context</div>
                                    </button>
                                </div>
                            </div>

                            <div class="col-12" data-step-panel="1">
                                <div class="border rounded-3 p-3 p-lg-4 bg-light-subtle">
                                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                        <div>
                                            <div class="text-uppercase small text-muted fw-semibold mb-1">Step 1</div>
                                            <h5 class="mb-1">Reporter Info</h5>
                                            <p class="text-muted mb-0">Isi identitas pelapor agar tim bisa menghubungi kembali jika perlu klarifikasi.</p>
                                        </div>
                                        <div class="badge bg-dark-subtle text-dark border px-3 py-2">Contact Info</div>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="requester_name" class="form-label">Nama Pelapor</label>
                                            <input type="text" id="requester_name" name="requester_name"
                                                class="form-control @error('requester_name') is-invalid @enderror"
                                                value="{{ old('requester_name') }}" required>
                                            @error('requester_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label for="requester_email" class="form-label">Email Pelapor</label>
                                            <input type="email" id="requester_email" name="requester_email"
                                                class="form-control @error('requester_email') is-invalid @enderror"
                                                value="{{ old('requester_email') }}" required>
                                            @error('requester_email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label for="requester_department_id" class="form-label">Department</label>
                                            <select id="requester_department_id" name="requester_department_id"
                                                data-searchable-select data-search-placeholder="Search department"
                                                class="form-select @error('requester_department_id') is-invalid @enderror" required>
                                                <option value="">- Select -</option>
                                                @foreach ($departmentOptions as $option)
                                                    <option value="{{ $option->id }}" @selected((string) old('requester_department_id') === (string) $option->id)>
                                                        {{ $option->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('requester_department_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
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
                                            <h5 class="mb-1">Issue Basics</h5>
                                            <p class="text-muted mb-0">Ceritakan masalahnya dulu. Kategori detail dan prioritas akan dibantu oleh tim operasional.</p>
                                        </div>
                                        <div class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2">Core Input</div>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-md-8">
                                            <label for="title" class="form-label">Issue Summary</label>
                                            <input type="text" id="title" name="title" class="form-control @error('title') is-invalid @enderror"
                                                value="{{ old('title') }}" placeholder="Contoh: Printer ruang finance tidak bisa dipakai" required>
                                            @error('title')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-4">
                                            <label for="ticket_category_id" class="form-label">Ticket Type</label>
                                            <select id="ticket_category_id" name="ticket_category_id"
                                                class="form-select @error('ticket_category_id') is-invalid @enderror" required>
                                                <option value="">- Select -</option>
                                                @foreach ($categoryOptions as $option)
                                                    <option value="{{ $option->id }}" @selected((string) old('ticket_category_id') === (string) $option->id)>
                                                        {{ $option->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('ticket_category_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 d-none" data-subcategory-wrapper>
                                            <label for="ticket_subcategory_id" class="form-label">Ticket Category</label>
                                            <select id="ticket_subcategory_id" name="ticket_subcategory_id"
                                                class="form-select @error('ticket_subcategory_id') is-invalid @enderror">
                                                <option value="">- Optional -</option>
                                                @foreach ($subcategoryOptions as $option)
                                                    <option value="{{ $option->id }}" data-category-id="{{ $option->ticket_category_id }}"
                                                        @selected((string) old('ticket_subcategory_id') === (string) $option->id)>
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
                                            <select id="ticket_detail_subcategory_id" name="ticket_detail_subcategory_id"
                                                class="form-select @error('ticket_detail_subcategory_id') is-invalid @enderror">
                                                <option value="">- Optional -</option>
                                                @foreach ($detailSubcategoryOptions as $option)
                                                    <option value="{{ $option->id }}" data-subcategory-id="{{ $option->ticket_subcategory_id }}"
                                                        @selected((string) old('ticket_detail_subcategory_id') === (string) $option->id)>
                                                        {{ $option->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="form-text">Opsional. Gunakan jika Anda tahu detail klasifikasi yang lebih spesifik.</div>
                                            @error('ticket_detail_subcategory_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-12">
                                            <label for="description" class="form-label">Issue Description</label>
                                            <textarea id="description" name="description" rows="5"
                                                class="form-control @error('description') is-invalid @enderror" required>{{ old('description') }}</textarea>
                                            <div class="form-text">Jelaskan gejala masalah, dampak ke pekerjaan, dan lokasi kejadian jika tahu.</div>
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

                            <div class="col-12 d-none" data-step-panel="3">
                                <div class="border rounded-3 p-3 p-lg-4 bg-light-subtle">
                                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                        <div>
                                            <div class="text-uppercase small text-muted fw-semibold mb-1">Step 3</div>
                                            <h5 class="mb-1">What Is Affected?</h5>
                                            <p class="text-muted mb-0">Pilih satu konteks yang paling membantu tim memahami objek yang terdampak.</p>
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
                                                <div class="fw-semibold">Medium</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="border rounded p-3 h-100 bg-white">
                                                <div class="small text-muted mb-1">Urgency Default</div>
                                                <div class="fw-semibold">Medium</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex flex-wrap gap-2 mb-3">
                                        <input type="radio" class="btn-check" name="context_mode" id="public_context_none" value="none" @checked($selectedContextMode === 'none')>
                                        <label class="btn btn-outline-secondary" for="public_context_none">No Specific Context</label>

                                        <input type="radio" class="btn-check" name="context_mode" id="public_context_service" value="service" @checked($selectedContextMode === 'service')>
                                        <label class="btn btn-outline-primary" for="public_context_service">Related Service</label>

                                        <input type="radio" class="btn-check" name="context_mode" id="public_context_asset" value="asset" @checked($selectedContextMode === 'asset')>
                                        <label class="btn btn-outline-primary" for="public_context_asset">Related Asset</label>

                                        <input type="radio" class="btn-check" name="context_mode" id="public_context_location" value="location" @checked($selectedContextMode === 'location')>
                                        <label class="btn btn-outline-primary" for="public_context_location">Asset Location</label>
                                    </div>

                                    <div class="alert alert-light border mb-0" data-context-panel="none">
                                        Cocok jika Anda belum tahu service, asset, atau location spesifik yang terdampak.
                                    </div>

                                    <div class="row g-3 d-none" data-context-panel="service">
                                        <div class="col-lg-8">
                                            <label for="service_id" class="form-label">Related Service</label>
                                            <select id="service_id" name="service_id" class="form-select @error('service_id') is-invalid @enderror"
                                                data-searchable-select data-search-placeholder="Search service">
                                                <option value="">- Select Related Service -</option>
                                                @foreach ($serviceOptions as $option)
                                                    <option value="{{ $option->id }}" @selected((string) old('service_id') === (string) $option->id)>
                                                        {{ $option->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('service_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row g-3 d-none" data-context-panel="asset">
                                        <div class="col-lg-6">
                                            <label for="asset_id" class="form-label">Related Asset</label>
                                            <select id="asset_id" name="asset_id" class="form-select @error('asset_id') is-invalid @enderror"
                                                data-searchable-select data-search-placeholder="Search asset">
                                                <option value="">- Select Related Asset -</option>
                                                @foreach ($assetOptions as $option)
                                                    <option
                                                        value="{{ $option->id }}"
                                                        data-service-id="{{ $option->service_id }}"
                                                        data-location-id="{{ $option->asset_location_id }}"
                                                        @selected((string) old('asset_id') === (string) $option->id)
                                                    >
                                                        {{ $option->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('asset_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-lg-6">
                                            <label for="public_asset_location_asset_mode" class="form-label">Asset Location</label>
                                            <select id="public_asset_location_asset_mode"
                                                class="form-select @error('asset_location_id') is-invalid @enderror"
                                                data-searchable-select data-search-placeholder="Search asset location">
                                                <option value="">- Optional Location -</option>
                                                @foreach ($locationOptions as $option)
                                                    <option value="{{ $option->id }}" @selected($selectedContextMode === 'asset' && (string) old('asset_location_id') === (string) $option->id)>
                                                        {{ $option->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('asset_location_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row g-3 d-none" data-context-panel="location">
                                        <div class="col-lg-8">
                                            <label for="public_asset_location_location_mode" class="form-label">Asset Location</label>
                                            <select id="public_asset_location_location_mode"
                                                class="form-select @error('asset_location_id') is-invalid @enderror"
                                                data-searchable-select data-search-placeholder="Search asset location">
                                                <option value="">- Select Location -</option>
                                                @foreach ($locationOptions as $option)
                                                    <option value="{{ $option->id }}" @selected($selectedContextMode === 'location' && (string) old('asset_location_id') === (string) $option->id)>
                                                        {{ $option->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('asset_location_id')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div id="public-ticket-context-smart-hint" class="alert alert-info border d-none mt-3 mb-0"></div>
                                </div>
                            </div>

                            <div class="col-12 pt-1">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-outline-secondary d-none" data-step-action="prev">Back</button>
                                        <button type="button" class="btn btn-dark" data-step-action="next">Continue</button>
                                        <button type="submit" class="btn btn-success d-none" data-step-action="submit">Submit Ticket</button>
                                    </div>
                                    <a href="{{ route('public.inspections.create') }}" class="btn btn-outline-secondary">Submit Inspection Result</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('public-ticket-form');
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
        const assetModeLocationSelect = document.getElementById('public_asset_location_asset_mode');
        const locationModeSelect = document.getElementById('public_asset_location_location_mode');
        const smartHint = document.getElementById('public-ticket-context-smart-hint');
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

        const setSelectValue = (select, value) => {
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

        const syncLocationValue = () => {
            if (!sharedLocationInput) {
                return;
            }

            const activeMode = document.querySelector('input[name="context_mode"]:checked')?.value;

            if (activeMode === 'asset' && assetModeLocationSelect) {
                sharedLocationInput.value = assetModeLocationSelect.value;
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
                const relatedLocationName = getOptionByValue(assetModeLocationSelect ?? locationModeSelect, relatedLocationId)?.textContent?.trim();

                if (relatedServiceId) {
                    setSelectValue(serviceSelect, relatedServiceId);
                }

                if (relatedLocationId && assetModeLocationSelect && !assetModeLocationSelect.value) {
                    setSelectValue(assetModeLocationSelect, relatedLocationId);
                }

                syncLocationValue();

                const details = [
                    relatedServiceName ? `service <strong>${relatedServiceName}</strong>` : null,
                    relatedLocationName ? `location <strong>${relatedLocationName}</strong>` : null,
                ].filter(Boolean);

                setSmartHint(details.length > 0
                    ? `Asset ini terhubung ke ${details.join(' dan ')}. Field terkait dibantu isi otomatis jika datanya tersedia.`
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

            if (activeMode !== 'service') {
                setSelectValue(serviceSelect, '');
            }

            if (activeMode !== 'asset') {
                setSelectValue(assetSelect, '');
            }

            if (activeMode === 'none' || activeMode === 'service') {
                setSelectValue(sharedLocationInput, '');
                setSelectValue(assetModeLocationSelect, '');
                setSelectValue(locationModeSelect, '');
            }

            if (activeMode === 'asset') {
                setSelectValue(locationModeSelect, '');
            }

            if (activeMode === 'location') {
                setSelectValue(assetModeLocationSelect, '');
            }

            syncLocationValue();
            updateSmartContextHint();
        };

        const fieldsForStep = (step) => {
            if (step === 1) {
                return [
                    document.getElementById('requester_name'),
                    document.getElementById('requester_email'),
                    document.getElementById('requester_department_id'),
                ].filter(Boolean);
            }

            if (step === 2) {
                return [
                    document.getElementById('title'),
                    document.getElementById('ticket_category_id'),
                    document.getElementById('description'),
                ].filter(Boolean);
            }

            if (step === 3) {
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
                trigger.classList.toggle('btn-dark', isActive);
                trigger.classList.toggle('text-white', isActive);
                trigger.classList.toggle('btn-outline-dark', !isActive);
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
        assetModeLocationSelect?.addEventListener('change', syncLocationValue);
        assetModeLocationSelect?.addEventListener('change', updateSmartContextHint);
        locationModeSelect?.addEventListener('change', syncLocationValue);
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
