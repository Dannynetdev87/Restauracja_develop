<?php

namespace App\Http\Requests;

use App\Models\MenuItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $menuItem = $this->route('menuItem');

        return [
            'menu_category_id' => ['required', 'integer', 'exists:menu_categories,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('menu_items', 'name')
                    ->where('menu_category_id', $this->input('menu_category_id'))
                    ->ignore($menuItem),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'min:0.01', 'max:99999.99'],
            'production_area' => ['required', Rule::in([MenuItem::AREA_KITCHEN, MenuItem::AREA_BAR])],
            'available' => ['sometimes', 'boolean'],
        ];
    }
}
