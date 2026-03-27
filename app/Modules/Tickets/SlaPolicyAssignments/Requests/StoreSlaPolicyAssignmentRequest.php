<?php

namespace App\Modules\Tickets\SlaPolicyAssignments\Requests;

use App\Models\TicketDetailSubcategory;
use App\Models\TicketSubcategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreSlaPolicyAssignmentRequest extends FormRequest
{
    private const TICKET_TYPES = [
        'incident',
        'service_request',
        'change_request',
    ];

    private const IMPACT_OPTIONS = [
        'low',
        'medium',
        'high',
    ];

    public function authorize(): bool
    {
        return $this->user()?->hasPermission('sla.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'sla_policy_id' => ['required', 'integer', Rule::exists('sla_policies', 'id')],
            'ticket_type' => ['nullable', 'string', 'max:50', Rule::in(self::TICKET_TYPES)],
            'category_id' => ['nullable', 'integer', Rule::exists('ticket_categories', 'id')],
            'subcategory_id' => ['nullable', 'integer', Rule::exists('ticket_subcategories', 'id')],
            'detail_subcategory_id' => ['nullable', 'integer', Rule::exists('ticket_detail_subcategories', 'id')],
            'service_item_id' => ['nullable', 'integer', Rule::exists('services', 'id')],
            'priority_id' => ['nullable', 'integer', Rule::exists('ticket_priorities', 'id')],
            'impact' => ['nullable', 'string', 'max:30', Rule::in(self::IMPACT_OPTIONS)],
            'urgency' => ['nullable', 'string', 'max:30', Rule::in(self::IMPACT_OPTIONS)],
            'sort_order' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $categoryId = $this->input('category_id');
            $subcategoryId = $this->input('subcategory_id');
            $detailSubcategoryId = $this->input('detail_subcategory_id');

            if ($categoryId === null || $categoryId === '' || $subcategoryId === null || $subcategoryId === '') {
                $subcategoryMatches = true;
            } else {
                $subcategoryMatches = TicketSubcategory::query()
                    ->whereKey($subcategoryId)
                    ->where('ticket_category_id', $categoryId)
                    ->exists();

                if (! $subcategoryMatches) {
                    $validator->errors()->add('subcategory_id', 'The selected subcategory does not belong to the selected category.');
                }
            }

            if ($detailSubcategoryId === null || $detailSubcategoryId === '' || $subcategoryId === null || $subcategoryId === '' || ! $subcategoryMatches) {
                return;
            }

            $isMatchingDetailSubcategory = TicketDetailSubcategory::query()
                ->whereKey($detailSubcategoryId)
                ->where('ticket_subcategory_id', $subcategoryId)
                ->exists();

            if (! $isMatchingDetailSubcategory) {
                $validator->errors()->add('detail_subcategory_id', 'The selected detail subcategory does not belong to the selected subcategory.');
            }
        });
    }
}
