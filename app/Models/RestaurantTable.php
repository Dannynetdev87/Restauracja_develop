<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'assigned_waiter_id',
        'zone_id',
    ];

    protected $casts = [
        'number' => 'integer',
        'seats' => 'integer',
        'assigned_waiter_id' => 'integer',
        'zone_id' => 'integer',
    ];

    public function assignedWaiter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_waiter_id');
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

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

    public function scopeVisibleForWaiter(Builder $query, int $waiterId): Builder
    {
        return $query->where(function (Builder $query) use ($waiterId) {
            $query
                ->where('assigned_waiter_id', $waiterId)
                ->orWhere(function (Builder $query) use ($waiterId) {
                    $query
                        ->whereNull('assigned_waiter_id')
                        ->whereHas('zone', fn (Builder $zoneQuery) => $zoneQuery
                            ->where('is_active', true)
                            ->where('assigned_waiter_id', $waiterId));
                });
        });
    }

    public function isVisibleForWaiter(int $waiterId): bool
    {
        if ($this->assigned_waiter_id === $waiterId) {
            return true;
        }

        return $this->assigned_waiter_id === null
            && $this->zone?->is_active
            && $this->zone?->assigned_waiter_id === $waiterId;
    }

    public function effectiveAssignedWaiter(): ?User
    {
        return $this->assignedWaiter ?: $this->zone?->assignedWaiter;
    }

    protected function status(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => strtolower($value),
        );
    }
}
