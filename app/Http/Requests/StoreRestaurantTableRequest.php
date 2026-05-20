<?php

namespace App\Http\Requests;

use App\Models\RestaurantTable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRestaurantTableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'number' => ['required', 'integer', 'min:1', 'max:999', 'unique:restaurant_tables,number'],
            'seats' => ['required', 'integer', 'min:1', 'max:50'],
            'status' => [
                'required',
                Rule::in([
                    RestaurantTable::STATUS_FREE,
                    RestaurantTable::STATUS_OCCUPIED,
                    RestaurantTable::STATUS_RESERVED,
                    RestaurantTable::STATUS_INACTIVE,
                ]),
            ],
        ];
    }
}
