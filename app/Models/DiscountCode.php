<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class DiscountCode extends Model
{
    use HasFactory;

    public const TYPE_PERCENT = 'percent';

    public const TYPE_FIXED = 'fixed';

    protected $fillable = [
        'code',
        'type',
        'value',
        'is_active',
        'usage_limit',
        'used_count',
        'starts_at',
        'expires_at',
        'created_by',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'is_active' => 'boolean',
        'usage_limit' => 'integer',
        'used_count' => 'integer',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isUsable(?Carbon $at = null): bool
    {
        $at ??= now();

        if (! $this->is_active) {
            return false;
        }

        if ($this->starts_at !== null && $this->starts_at->greaterThan($at)) {
            return false;
        }

        if ($this->expires_at !== null && $this->expires_at->lessThan($at)) {
            return false;
        }

        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    public function calculateDiscount(float|int|string $baseAmount): float
    {
        $baseAmount = max(0.0, round((float) $baseAmount, 2));
        $value = max(0.0, (float) $this->value);

        $discount = match ($this->type) {
            self::TYPE_PERCENT => $baseAmount * ($value / 100),
            self::TYPE_FIXED => $value,
            default => 0.0,
        };

        return round(min(max(0.0, $discount), $baseAmount), 2);
    }

    protected function code(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => strtoupper(trim($value)),
        );
    }
}
