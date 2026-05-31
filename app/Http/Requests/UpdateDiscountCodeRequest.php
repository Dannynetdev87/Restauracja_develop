<?php

namespace App\Http\Requests;

use App\Models\DiscountCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDiscountCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in([DiscountCode::TYPE_PERCENT, DiscountCode::TYPE_FIXED])],
            'value' => [
                'required',
                'numeric',
                'min:0.01',
                Rule::when($this->input('type') === DiscountCode::TYPE_PERCENT, ['max:100']),
            ],
            'is_active' => ['boolean'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after:starts_at'],
        ];
    }
}
