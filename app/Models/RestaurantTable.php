<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RestaurantTable extends Model
{
    public const STATUS_FREE = 'wolny';

    public const STATUS_OCCUPIED = 'zajety';

    public const STATUS_RESERVED = 'zarezerwowany';

    public const STATUS_INACTIVE = 'nieaktywny';

    protected $fillable = [
        'number',
        'seats',
        'status',
    ];

    protected $casts = [
        'number' => 'integer',
        'seats' => 'integer',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function activeOrders(): HasMany
    {
        return $this->orders()->whereIn('status', Order::activeStatuses());
    }

    public function isFree(): bool
    {
        return $this->status === self::STATUS_FREE;
    }

    public function canOpenOrder(): bool
    {
        return $this->isFree() && ! $this->activeOrders()->exists();
    }

    protected function status(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => strtolower($value),
        );
    }
}
