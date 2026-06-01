<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMenuCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('sort_order')) {
            $this->merge([
                'sort_order' => 0,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:menu_categories,name'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999', Rule::unique('menu_categories', 'sort_order')],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'sort_order.unique' => 'Ta kolejność jest już używana przez inną kategorię menu.',
        ];
    }
}
