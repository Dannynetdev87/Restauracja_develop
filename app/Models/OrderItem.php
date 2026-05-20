<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'menu_item_id',
        'quantity',
        'unit_price',
        'notes',
        'status',
    ];

    protected function status(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => strtolower($value)
        );
    }
}
