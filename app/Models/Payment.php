<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Payment extends Model
{
    public const METHOD_CASH = 'cash';

    public const METHOD_CARD = 'card';

    public const METHOD_BLIK = 'blik';

    public const METHOD_OTHER = 'other';

    public const STATUS_PENDING = 'pending';

    public const STATUS_PAID = 'paid';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_REFUNDED = 'refunded';

    protected $fillable = [
        'order_id',
        'amount',
        'tip_amount',
        'payment_method',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'tip_amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItems(): BelongsToMany
    {
        return $this->belongsToMany(OrderItem::class);
    }

    protected function paymentMethod(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => strtolower($value)
        );
    }

    protected function status(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => strtolower($value)
        );
    }
}
