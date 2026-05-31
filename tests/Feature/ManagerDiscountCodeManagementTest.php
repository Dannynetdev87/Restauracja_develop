<?php

namespace Tests\Feature;

use App\Models\DiscountCode;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ManagerDiscountCodeManagementTest extends TestCase
{
    use DatabaseTransactions;

    public function test_manager_can_see_discount_code_list(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);
        DiscountCode::factory()->percent(15.00)->create(['code' => 'LIST15']);

        $this
            ->actingAs($manager)
            ->get(route('manager.discount-codes.index'))
            ->assertOk()
            ->assertSee('Kody rabatowe')
            ->assertSee('LIST15')
            ->assertSee('Dodaj kod');
    }

    public function test_admin_can_see_discount_code_list(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this
            ->actingAs($admin)
            ->get(route('manager.discount-codes.index'))
            ->assertOk()
            ->assertSee('Kody rabatowe');
    }

    public function test_non_manager_roles_cannot_access_discount_code_panel(): void
    {
        foreach ([User::ROLE_WAITER, User::ROLE_KITCHEN, User::ROLE_BAR] as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this
                ->actingAs($user)
                ->get(route('manager.discount-codes.index'))
                ->assertForbidden();
        }
    }

    public function test_manager_can_create_percent_discount_code(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);

        $this
            ->actingAs($manager)
            ->post(route('manager.discount-codes.store'), [
                'code' => ' save10 ',
                'type' => DiscountCode::TYPE_PERCENT,
                'value' => '10.00',
                'is_active' => '1',
                'usage_limit' => '5',
                'starts_at' => now()->format('Y-m-d H:i:s'),
                'expires_at' => now()->addDay()->format('Y-m-d H:i:s'),
            ])
            ->assertRedirect(route('manager.discount-codes.index'));

        $this->assertDatabaseHas('discount_codes', [
            'code' => 'SAVE10',
            'type' => DiscountCode::TYPE_PERCENT,
            'value' => '10.00',
            'is_active' => true,
            'usage_limit' => 5,
            'used_count' => 0,
            'created_by' => $manager->id,
        ]);
    }

    public function test_manager_can_create_fixed_discount_code_with_large_value(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);

        $this
            ->actingAs($manager)
            ->post(route('manager.discount-codes.store'), [
                'code' => 'BIG500',
                'type' => DiscountCode::TYPE_FIXED,
                'value' => '500.00',
                'is_active' => '1',
            ])
            ->assertRedirect(route('manager.discount-codes.index'));

        $this->assertDatabaseHas('discount_codes', [
            'code' => 'BIG500',
            'type' => DiscountCode::TYPE_FIXED,
            'value' => '500.00',
            'created_by' => $manager->id,
        ]);
    }

    public function test_percent_discount_value_cannot_exceed_one_hundred(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);

        $this
            ->actingAs($manager)
            ->post(route('manager.discount-codes.store'), [
                'code' => 'BAD101',
                'type' => DiscountCode::TYPE_PERCENT,
                'value' => '101.00',
                'is_active' => '1',
            ])
            ->assertSessionHasErrors('value');

        $this->assertDatabaseMissing('discount_codes', [
            'code' => 'BAD101',
        ]);
    }

    public function test_discount_code_must_be_unique_after_normalization(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);
        DiscountCode::factory()->create(['code' => 'UNIQUE10']);

        $this
            ->actingAs($manager)
            ->post(route('manager.discount-codes.store'), [
                'code' => ' unique10 ',
                'type' => DiscountCode::TYPE_PERCENT,
                'value' => '10.00',
                'is_active' => '1',
            ])
            ->assertSessionHasErrors('code');
    }

    public function test_expires_at_must_be_after_starts_at(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);

        $this
            ->actingAs($manager)
            ->post(route('manager.discount-codes.store'), [
                'code' => 'DATE10',
                'type' => DiscountCode::TYPE_PERCENT,
                'value' => '10.00',
                'is_active' => '1',
                'starts_at' => now()->addDay()->format('Y-m-d H:i:s'),
                'expires_at' => now()->format('Y-m-d H:i:s'),
            ])
            ->assertSessionHasErrors('expires_at');
    }

    public function test_manager_can_update_discount_code_without_changing_code(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);
        $discountCode = DiscountCode::factory()->percent(10.00)->create([
            'code' => 'EDIT10',
            'is_active' => true,
            'usage_limit' => 5,
        ]);

        $this
            ->actingAs($manager)
            ->put(route('manager.discount-codes.update', $discountCode), [
                'code' => 'CHANGED',
                'type' => DiscountCode::TYPE_FIXED,
                'value' => '45.50',
                'usage_limit' => '12',
                'starts_at' => now()->format('Y-m-d H:i:s'),
                'expires_at' => now()->addDays(2)->format('Y-m-d H:i:s'),
            ])
            ->assertRedirect(route('manager.discount-codes.index'));

        $discountCode->refresh();

        $this->assertSame('EDIT10', $discountCode->code);
        $this->assertSame(DiscountCode::TYPE_FIXED, $discountCode->type);
        $this->assertSame('45.50', $discountCode->value);
        $this->assertSame(12, $discountCode->usage_limit);
        $this->assertFalse($discountCode->is_active);
        $this->assertNotNull($discountCode->starts_at);
        $this->assertNotNull($discountCode->expires_at);
    }

    public function test_used_count_does_not_change_during_update(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);
        $discountCode = DiscountCode::factory()->percent(10.00)->create([
            'code' => 'USED10',
            'used_count' => 7,
        ]);

        $this
            ->actingAs($manager)
            ->put(route('manager.discount-codes.update', $discountCode), [
                'type' => DiscountCode::TYPE_PERCENT,
                'value' => '20.00',
                'is_active' => '1',
            ])
            ->assertRedirect(route('manager.discount-codes.index'));

        $this->assertSame(7, $discountCode->refresh()->used_count);
    }

    public function test_toggle_changes_discount_code_active_state(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);
        $discountCode = DiscountCode::factory()->create([
            'code' => 'TOGGLE10',
            'is_active' => true,
        ]);

        $this
            ->actingAs($manager)
            ->patch(route('manager.discount-codes.toggle', $discountCode))
            ->assertRedirect(route('manager.discount-codes.index'));

        $this->assertFalse($discountCode->refresh()->is_active);
    }
}
