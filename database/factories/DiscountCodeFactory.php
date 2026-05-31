<?php

namespace Database\Factories;

use App\Models\DiscountCode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DiscountCode>
 */
class DiscountCodeFactory extends Factory
{
    protected $model = DiscountCode::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('KOD-####-??')),
            'type' => fake()->randomElement([DiscountCode::TYPE_PERCENT, DiscountCode::TYPE_FIXED]),
            'value' => fake()->randomFloat(2, 1, 50),
            'is_active' => true,
            'usage_limit' => null,
            'used_count' => 0,
            'starts_at' => null,
            'expires_at' => null,
            'created_by' => null,
        ];
    }

    public function percent(float $value = 10.00): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => DiscountCode::TYPE_PERCENT,
            'value' => $value,
        ]);
    }

    public function fixed(float $value = 20.00): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => DiscountCode::TYPE_FIXED,
            'value' => $value,
        ]);
    }
}
