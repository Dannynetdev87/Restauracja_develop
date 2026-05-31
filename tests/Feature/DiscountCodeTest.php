<?php

namespace Tests\Feature;

use App\Models\DiscountCode;
use Tests\TestCase;

class DiscountCodeTest extends TestCase
{
    public function test_code_is_stored_uppercase(): void
    {
        $discountCode = DiscountCode::factory()->make([
            'code' => ' kod10 ',
        ]);

        $this->assertSame('KOD10', $discountCode->code);
    }

    public function test_percent_code_calculates_discount_amount(): void
    {
        $discountCode = DiscountCode::factory()->percent(15.00)->make();

        $this->assertSame(18.75, $discountCode->calculateDiscount(125.00));
    }

    public function test_fixed_code_does_not_exceed_base_amount(): void
    {
        $discountCode = DiscountCode::factory()->fixed(50.00)->make();

        $this->assertSame(30.00, $discountCode->calculateDiscount(30.00));
    }

    public function test_inactive_code_is_not_usable(): void
    {
        $discountCode = DiscountCode::factory()->make([
            'is_active' => false,
        ]);

        $this->assertFalse($discountCode->isUsable());
    }

    public function test_code_before_starts_at_is_not_usable(): void
    {
        $discountCode = DiscountCode::factory()->make([
            'starts_at' => now()->addDay(),
        ]);

        $this->assertFalse($discountCode->isUsable(now()));
    }

    public function test_code_after_expires_at_is_not_usable(): void
    {
        $discountCode = DiscountCode::factory()->make([
            'expires_at' => now()->subMinute(),
        ]);

        $this->assertFalse($discountCode->isUsable(now()));
    }

    public function test_code_with_exhausted_usage_limit_is_not_usable(): void
    {
        $discountCode = DiscountCode::factory()->make([
            'usage_limit' => 3,
            'used_count' => 3,
        ]);

        $this->assertFalse($discountCode->isUsable());
    }

    public function test_null_usage_limit_means_unlimited_usage(): void
    {
        $discountCode = DiscountCode::factory()->make([
            'usage_limit' => null,
            'used_count' => 999,
        ]);

        $this->assertTrue($discountCode->isUsable());
    }
}
