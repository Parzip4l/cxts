@extends('layouts.base', ['subtitle' => 'Public Inspection Result'])

@section('content')
<div class="account-pages py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-10 col-lg-11">
                <div class="card border-0 shadow-lg">
                    <div class="card-body p-4 p-lg-5">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h4 class="fw-bold text-dark mb-1">Public Inspection Result Submission</h4>
                                <p class="text-muted mb-0">Kirim hasil inspeksi lapangan tanpa login internal.</p>
                            </div>
                            <a href="{{ route('public.tickets.create') }}" class="btn btn-outline-dark">Submit Ticket</a>
                        </div>

                        @if (session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        <form method="POST" action="{{ route('public.inspections.store') }}" class="row g-3" id="publicInspectionForm" enctype="multipart/form-data">
                            @csrf

                            <div class="col-md-6">
                                <label for="reporter_name" class="form-label">Nama Pelapor</label>
                                <input type="text" id="reporter_name" name="reporter_name"
                                    class="form-control @error('reporter_name') is-invalid @enderror"
                                    value="{{ old('reporter_name') }}" required>
                                @error('reporter_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="reporter_email" class="form-label">Email Pelapor</label>
                                <input type="email" id="reporter_email" name="reporter_email"
                                    class="form-control @error('reporter_email') is-invalid @enderror"
                                    value="{{ old('reporter_email') }}" required>
                                @error('reporter_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="inspection_template_id" class="form-label">Inspection Template</label>
                                <select id="inspection_template_id" name="inspection_template_id"
                                    data-searchable-select data-search-placeholder="Search inspection template"
                                    class="form-select @error('inspection_template_id') is-invalid @enderror" required>
                                    <option value="">- Select -</option>
                                    @foreach ($templates as $template)
                                        <option value="{{ $template->id }}" @selected((string) old('inspection_template_id') === (string) $template->id)>
                                            {{ $template->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('inspection_template_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="asset_id" class="form-label">Related Asset</label>
                                <select id="asset_id" name="asset_id" class="form-select @error('asset_id') is-invalid @enderror"
                                    data-searchable-select data-search-placeholder="Search asset">
                                    <option value="">- Optional -</option>
                                    @foreach ($assetOptions as $asset)
                                        <option value="{{ $asset->id }}" @selected((string) old('asset_id') === (string) $asset->id)>
                                            {{ $asset->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">Pilih aset yang diperiksa jika hasil inspeksi terkait perangkat tertentu.</div>
                                @error('asset_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="asset_location_id" class="form-label">Asset Location</label>
                                <select id="asset_location_id" name="asset_location_id"
                                    data-searchable-select data-search-placeholder="Search asset location"
                                    class="form-select @error('asset_location_id') is-invalid @enderror">
                                    <option value="">- Optional -</option>
                                    @foreach ($locationOptions as $location)
                                        <option value="{{ $location->id }}" @selected((string) old('asset_location_id') === (string) $location->id)>
                                            {{ $location->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">Pilih site atau area inspeksi jika lebih relevan daripada aset spesifik.</div>
                                @error('asset_location_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="inspection_date" class="form-label">Inspection Date</label>
                                <input type="date" id="inspection_date" name="inspection_date"
                                    class="form-control @error('inspection_date') is-invalid @enderror"
                                    value="{{ old('inspection_date', now()->format('Y-m-d')) }}" required>
                                @error('inspection_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="final_result" class="form-label">Final Result</label>
                                @php $finalResult = old('final_result', \App\Models\Inspection::FINAL_RESULT_NORMAL); @endphp
                                <select id="final_result" name="final_result" class="form-select @error('final_result') is-invalid @enderror" required>
                                    <option value="{{ \App\Models\Inspection::FINAL_RESULT_NORMAL }}" @selected($finalResult === \App\Models\Inspection::FINAL_RESULT_NORMAL)>Normal</option>
                                    <option value="{{ \App\Models\Inspection::FINAL_RESULT_ABNORMAL }}" @selected($finalResult === \App\Models\Inspection::FINAL_RESULT_ABNORMAL)>Abnormal</option>
                                </select>
                                @error('final_result')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="summary_notes" class="form-label">Summary Notes</label>
                                <textarea id="summary_notes" name="summary_notes" rows="3"
                                    class="form-control @error('summary_notes') is-invalid @enderror">{{ old('summary_notes') }}</textarea>
                                @error('summary_notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <div class="border rounded p-3">
                                    <div class="fw-semibold mb-2">Checklist Items</div>
                                    <div id="inspection-items-container" class="text-muted">Pilih template untuk memunculkan item inspeksi.</div>
                                    @error('items')
                                        <div class="text-danger small mt-2">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-12">
                                <label for="supporting_files" class="form-label">Supporting Files (required if Abnormal)</label>
                                <input type="file" id="supporting_files" name="supporting_files[]"
                                    class="form-control @error('supporting_files') is-invalid @enderror @error('supporting_files.*') is-invalid @enderror"
                                    multiple>
                                @error('supporting_files')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                                @error('supporting_files.*')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 d-flex gap-2">
                                <button type="submit" class="btn btn-dark">Submit Inspection Result</button>
                                <a href="{{ route('login') }}" class="btn btn-outline-secondary">Staff Login</a>
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
        const templateSelect = document.getElementById('inspection_template_id');
        const finalResultSelect = document.getElementById('final_result');
        const supportingFilesInput = document.getElementById('supporting_files');
        const itemsContainer = document.getElementById('inspection-items-container');
        const templates = @json($templatePayload);

        const oldItems = @json(old('items', []));

        const getOldItem = (templateItemId) => {
            return oldItems.find((entry) => String(entry.inspection_template_item_id) === String(templateItemId)) || {};
        };

        const renderItems = () => {
            const selectedTemplateId = templateSelect.value;
            const selectedTemplate = templates.find((template) => String(template.id) === String(selectedTemplateId));

            if (!selectedTemplate) {
                itemsContainer.innerHTML = '<span class="text-muted">Pilih template untuk memunculkan item inspeksi.</span>';
                return;
            }

            if (!selectedTemplate.items.length) {
                itemsContainer.innerHTML = '<span class="text-muted">Template ini belum memiliki item aktif.</span>';
                return;
            }

            let html = '<div class="table-responsive"><table class="table table-sm align-middle mb-0">';
            html += '<thead><tr><th width="40">#</th><th>Item</th><th width="160">Status</th><th width="180">Value</th><th>Notes</th></tr></thead><tbody>';

            selectedTemplate.items.forEach((item, index) => {
                const oldItem = getOldItem(item.id);
                const oldStatus = oldItem.result_status || '';
                const oldValue = oldItem.result_value || '';
                const oldNotes = oldItem.notes || '';

                html += '<tr>';
                html += `<td>${item.sequence || index + 1}</td>`;
                html += `<td><div class="fw-semibold">${item.label}</div><small class="text-muted">Expected: ${item.expected || '-'}${item.required ? ' | Required' : ''}</small></td>`;
                html += '<td>';
                html += `<input type="hidden" name="items[${index}][inspection_template_item_id]" value="${item.id}">`;
                html += `<select name="items[${index}][result_status]" class="form-select form-select-sm">`;
                html += `<option value="" ${oldStatus === '' ? 'selected' : ''}>-</option>`;
                html += `<option value="pass" ${oldStatus === 'pass' ? 'selected' : ''}>Pass</option>`;
                html += `<option value="fail" ${oldStatus === 'fail' ? 'selected' : ''}>Fail</option>`;
                html += `<option value="na" ${oldStatus === 'na' ? 'selected' : ''}>N/A</option>`;
                html += '</select>';
                html += '</td>';
                html += `<td><input type="text" name="items[${index}][result_value]" value="${oldValue}" class="form-control form-control-sm" maxlength="120"></td>`;
                html += `<td><input type="text" name="items[${index}][notes]" value="${oldNotes}" class="form-control form-control-sm"></td>`;
                html += '</tr>';
            });

            html += '</tbody></table></div>';
            itemsContainer.innerHTML = html;
        };

        templateSelect.addEventListener('change', renderItems);
        renderItems();

        const toggleSupportingFilesRequirement = () => {
            if (!finalResultSelect || !supportingFilesInput) {
                return;
            }

            const isAbnormal = finalResultSelect.value === '{{ \App\Models\Inspection::FINAL_RESULT_ABNORMAL }}';
            supportingFilesInput.required = isAbnormal;
        };

        if (finalResultSelect && supportingFilesInput) {
            finalResultSelect.addEventListener('change', toggleSupportingFilesRequirement);
            toggleSupportingFilesRequirement();
        }
    });
</script>
@endpush
