<?php

namespace App\Modules\MasterData\Vendors\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVendorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $vendorId = (int) $this->route('vendor')?->id;

        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('vendors', 'code')->ignore($vendorId)],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'contact_person_name' => ['nullable', 'string', 'max:150'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
