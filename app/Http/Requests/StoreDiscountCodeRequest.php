<?php

namespace App\Http\Requests;

use App\Models\DiscountCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDiscountCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $code = trim((string) $this->input('code'));

        $this->merge([
            'code' => $code === '' ? null : strtoupper($code),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        return [
            'code' => ['nullable', 'string', 'max:50', 'unique:discount_codes,code'],
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
