<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class OrderItemStatusHistory extends Model
{

    const UPDATED_AT = null;

    protected $fillable = [
        'order_item_id',
        'changed_by',
        'old_status',
        'new_status',
    ];

    protected function oldStatus(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => $value ? strtolower($value) : null
        );
    }

    protected function newStatus(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => strtolower($value)
        );
    }
}
