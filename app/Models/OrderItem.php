<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderItem extends Model
{
    public const STATUS_NEW = 'new';

    public const STATUS_PREPARING = 'preparing';

    public const STATUS_READY = 'ready';

    public const STATUS_DELIVERED = 'delivered';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'order_id',
        'menu_item_id',
        'quantity',
        'unit_price',
        'notes',
        'status',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderItemStatusHistory::class);
    }

    public function payments(): BelongsToMany
    {
        return $this->belongsToMany(Payment::class);
    }

    public function subtotal(): float
    {
        return $this->quantity * $this->unit_price;
    }

    public function isPaid(): bool
    {
        if ($this->relationLoaded('payments')) {
            return $this->payments->contains(
                fn (Payment $payment) => $payment->status === Payment::STATUS_PAID
            );
        }

        return $this->payments()
            ->where('status', Payment::STATUS_PAID)
            ->exists();
    }

    protected function status(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => strtolower($value)
        );
    }
}
