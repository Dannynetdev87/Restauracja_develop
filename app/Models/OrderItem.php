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

    /**
     * Zaktualizowana relacja z obsługą pola 'quantity' w tabeli pivot
     */
    public function payments(): BelongsToMany
    {
        return $this->belongsToMany(Payment::class)
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function subtotal(): float
    {
        return $this->quantity * $this->unit_price;
    }

    /**
     * Zwraca liczbę sztuk tej pozycji, które zostały już opłacone
     */
    public function paidQuantity(): int
    {
        if ($this->relationLoaded('payments')) {
            return (int) $this->payments
                ->where('status', Payment::STATUS_PAID)
                ->sum(fn ($payment) => $payment->pivot->quantity ?? 0);
        }

        return (int) $this->payments()
            ->where('status', Payment::STATUS_PAID)
            ->sum('quantity');
    }

    /**
     * Zwraca liczbę sztuk, które pozostały jeszcze do opłacenia
     */
    public function remainingQuantity(): int
    {
        if ($this->status === self::STATUS_CANCELLED) {
            return 0;
        }

        return max(0, $this->quantity - $this->paidQuantity());
    }

    /**
     * Pozycja jest w pełni opłacona tylko wtedy, gdy suma opłaconych sztuk
     * zgadza się z całkowitą ilością w zamówieniu.
     */
    public function isPaid(): bool
    {
        if ($this->quantity <= 0) {
            return true;
        }

        return $this->paidQuantity() >= $this->quantity;
    }

    protected function status(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => strtolower($value)
        );
    }
}
