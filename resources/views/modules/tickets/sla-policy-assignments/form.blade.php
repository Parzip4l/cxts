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

            <div class="col-md-5">
                <label for="sla_policy_id" class="form-label">SLA Policy</label>
                <select id="sla_policy_id" name="sla_policy_id" class="form-select @error('sla_policy_id') is-invalid @enderror" required>
                    <option value="">- Select -</option>
                    @foreach ($policyOptions as $option)
                        <option value="{{ $option->id }}" @selected((string) old('sla_policy_id', $slaPolicyAssignment->sla_policy_id) === (string) $option->id)>
                            {{ $option->name }}
                        </option>
                    @endforeach
                </select>
                @error('sla_policy_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="ticket_type" class="form-label">Process Type Key</label>
                <select id="ticket_type" name="ticket_type" class="form-select @error('ticket_type') is-invalid @enderror">
                    <option value="">- Any -</option>
                    @foreach ($ticketTypeOptions as $option)
                        <option value="{{ $option }}" @selected(old('ticket_type', $slaPolicyAssignment->ticket_type) === $option)>
                            {{ ucwords(str_replace('_', ' ', $option)) }}
                        </option>
                    @endforeach
                </select>
                @error('ticket_type')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-2">
                <label for="sort_order" class="form-label">Sort Order</label>
                <input type="number" min="1" id="sort_order" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror"
                    value="{{ old('sort_order', $slaPolicyAssignment->sort_order ?? 100) }}" required>
                @error('sort_order')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="category_id" class="form-label">Ticket Type</label>
                <select id="category_id" name="category_id" class="form-select @error('category_id') is-invalid @enderror">
                    <option value="">- Any -</option>
                    @foreach ($categoryOptions as $option)
                        <option value="{{ $option->id }}" @selected((string) old('category_id', $slaPolicyAssignment->category_id) === (string) $option->id)>
                            {{ $option->name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="subcategory_id" class="form-label">Ticket Category</label>
                <select id="subcategory_id" name="subcategory_id" class="form-select @error('subcategory_id') is-invalid @enderror">
                    <option value="">- Any -</option>
                    @foreach ($subcategoryOptions as $option)
                        <option value="{{ $option->id }}" data-category-id="{{ $option->ticket_category_id }}"
                            @selected((string) old('subcategory_id', $slaPolicyAssignment->subcategory_id) === (string) $option->id)>
                            {{ $option->name }}
                        </option>
                    @endforeach
                </select>
                @error('subcategory_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="detail_subcategory_id" class="form-label">Ticket Sub Category</label>
                <select id="detail_subcategory_id" name="detail_subcategory_id" class="form-select @error('detail_subcategory_id') is-invalid @enderror">
                    <option value="">- Any -</option>
                    @foreach ($detailSubcategoryOptions as $option)
                        <option value="{{ $option->id }}" data-subcategory-id="{{ $option->ticket_subcategory_id }}"
                            @selected((string) old('detail_subcategory_id', $slaPolicyAssignment->detail_subcategory_id) === (string) $option->id)>
                            {{ $option->name }}
                        </option>
                    @endforeach
                </select>
                @error('detail_subcategory_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="service_item_id" class="form-label">Service Item</label>
                <select id="service_item_id" name="service_item_id" class="form-select @error('service_item_id') is-invalid @enderror">
                    <option value="">- Any -</option>
                    @foreach ($serviceOptions as $option)
                        <option value="{{ $option->id }}" @selected((string) old('service_item_id', $slaPolicyAssignment->service_item_id) === (string) $option->id)>
                            {{ $option->name }}
                        </option>
                    @endforeach
                </select>
                @error('service_item_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="priority_id" class="form-label">Priority</label>
                <select id="priority_id" name="priority_id" class="form-select @error('priority_id') is-invalid @enderror">
                    <option value="">- Any -</option>
                    @foreach ($priorityOptions as $option)
                        <option value="{{ $option->id }}" @selected((string) old('priority_id', $slaPolicyAssignment->priority_id) === (string) $option->id)>
                            {{ $option->code }} - {{ $option->name }}
                        </option>
                    @endforeach
                </select>
                @error('priority_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="impact" class="form-label">Impact</label>
                <select id="impact" name="impact" class="form-select @error('impact') is-invalid @enderror">
                    <option value="">- Any -</option>
                    @foreach ($impactOptions as $option)
                        <option value="{{ $option }}" @selected(old('impact', $slaPolicyAssignment->impact) === $option)>{{ ucfirst($option) }}</option>
                    @endforeach
                </select>
                @error('impact')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="urgency" class="form-label">Urgency</label>
                <select id="urgency" name="urgency" class="form-select @error('urgency') is-invalid @enderror">
                    <option value="">- Any -</option>
                    @foreach ($urgencyOptions as $option)
                        <option value="{{ $option }}" @selected(old('urgency', $slaPolicyAssignment->urgency) === $option)>{{ ucfirst($option) }}</option>
                    @endforeach
                </select>
                @error('urgency')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <input type="hidden" name="is_active" value="0">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                        @checked((bool) old('is_active', $slaPolicyAssignment->is_active ?? true))>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>
            </div>

            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('master-data.sla-policy-assignments.index') }}" class="btn btn-outline-light">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const categorySelect = document.getElementById('category_id');
        const subcategorySelect = document.getElementById('subcategory_id');
        const detailSubcategorySelect = document.getElementById('detail_subcategory_id');

        if (!categorySelect || !subcategorySelect || !detailSubcategorySelect) {
            return;
        }

        const toggleDetailSubcategory = () => {
            const selectedSubcategoryId = subcategorySelect.value;

            Array.from(detailSubcategorySelect.options).forEach((option, index) => {
                if (index === 0) {
                    option.hidden = false;
                    return;
                }

                const subcategoryId = option.getAttribute('data-subcategory-id');
                const visible = selectedSubcategoryId === '' || subcategoryId === selectedSubcategoryId;
                option.hidden = !visible;

                if (!visible && option.selected) {
                    option.selected = false;
                }
            });
        };

        const toggleSubcategory = () => {
            const selectedCategoryId = categorySelect.value;

            Array.from(subcategorySelect.options).forEach((option, index) => {
                if (index === 0) {
                    option.hidden = false;
                    return;
                }

                const categoryId = option.getAttribute('data-category-id');
                const visible = selectedCategoryId === '' || categoryId === selectedCategoryId;
                option.hidden = !visible;

                if (!visible && option.selected) {
                    option.selected = false;
                }
            });

            toggleDetailSubcategory();
        };

        categorySelect.addEventListener('change', toggleSubcategory);
        subcategorySelect.addEventListener('change', toggleDetailSubcategory);
        toggleSubcategory();
    });
</script>
@endpush
