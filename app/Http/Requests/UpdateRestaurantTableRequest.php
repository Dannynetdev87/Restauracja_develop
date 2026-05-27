<?php

namespace App\Http\Requests;

use App\Models\RestaurantTable;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRestaurantTableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $restaurantTable = $this->route('restaurantTable');

        return [
            'number' => [
                'required',
                'integer',
                'min:1',
                'max:999',
                Rule::unique('restaurant_tables', 'number')->ignore($restaurantTable),
            ],
            'seats' => ['required', 'integer', 'min:1', 'max:50'],
            'assigned_waiter_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')
                    ->where('role', User::ROLE_WAITER)
                    ->where('is_active', true),
            ],
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
