<?php

namespace App\Modules\Tickets\PublicAccess\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePublicTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'requester_name' => ['required', 'string', 'max:150'],
            'requester_email' => ['required', 'email', 'max:150'],
            'requester_department_id' => ['required', 'integer', Rule::exists('departments', 'id')],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['required', 'string'],
            'ticket_type' => ['nullable', 'string', 'max:50'],
            'ticket_category_id' => ['required', 'integer', Rule::exists('ticket_categories', 'id')],
            'ticket_subcategory_id' => ['nullable', 'integer', Rule::exists('ticket_subcategories', 'id')],
            'ticket_detail_subcategory_id' => ['nullable', 'integer', Rule::exists('ticket_detail_subcategories', 'id')],
            'ticket_priority_id' => ['nullable', 'integer', Rule::exists('ticket_priorities', 'id')],
            'service_id' => ['nullable', 'integer', Rule::exists('services', 'id')],
            'asset_id' => ['nullable', 'integer', Rule::exists('assets', 'id')],
            'asset_location_id' => ['nullable', 'integer', Rule::exists('asset_locations', 'id')],
            'impact' => ['nullable', Rule::in(['low', 'medium', 'high'])],
            'urgency' => ['nullable', Rule::in(['low', 'medium', 'high'])],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['bail', 'file', 'image', 'mimetypes:image/jpeg,image/png,image/webp', 'extensions:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $categoryId = $this->input('ticket_category_id');
            $subcategoryId = $this->input('ticket_subcategory_id');
            $detailSubcategoryId = $this->input('ticket_detail_subcategory_id');

            if ($categoryId && $subcategoryId) {
                $belongsToType = \App\Models\TicketSubcategory::query()
                    ->whereKey($subcategoryId)
                    ->where('ticket_category_id', $categoryId)
                    ->exists();

                if (! $belongsToType) {
                    $validator->errors()->add('ticket_subcategory_id', 'The selected ticket category does not belong to the selected ticket type.');
                }
            }

            if ($subcategoryId && $detailSubcategoryId) {
                $belongsToCategory = \App\Models\TicketDetailSubcategory::query()
                    ->whereKey($detailSubcategoryId)
                    ->where('ticket_subcategory_id', $subcategoryId)
                    ->exists();

                if (! $belongsToCategory) {
                    $validator->errors()->add('ticket_detail_subcategory_id', 'The selected ticket sub category does not belong to the selected ticket category.');
                }
            }
        });
    }
}
