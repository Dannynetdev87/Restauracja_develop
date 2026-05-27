<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\RestaurantTable;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RestaurantTableManagementTest extends TestCase
{
    use DatabaseTransactions;

    public function test_manager_can_create_restaurant_table(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);

        $response = $this
            ->actingAs($manager)
            ->post(route('manager.tables.store'), [
                'number' => 910,
                'seats' => 4,
                'status' => RestaurantTable::STATUS_FREE,
                'assigned_waiter_id' => $waiter->id,
            ]);

        $response->assertRedirect(route('manager.tables.index'));

        $this->assertDatabaseHas('restaurant_tables', [
            'number' => 910,
            'seats' => 4,
            'status' => RestaurantTable::STATUS_FREE,
            'assigned_waiter_id' => $waiter->id,
        ]);
    }

    public function test_manager_dashboard_is_available(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);

        $this
            ->actingAs($manager)
            ->get(route('manager.dashboard'))
            ->assertOk()
            ->assertSee('Centrum dowodzenia')
            ->assertSee('Panel Managera');
    }

    public function test_manager_user_is_redirected_to_manager_dashboard_after_login(): void
    {
        $manager = User::factory()->create([
            'email' => 'manager-tables@example.com',
            'password' => 'password',
            'role' => User::ROLE_MANAGER,
        ]);

        $this
            ->post(route('login'), [
                'login' => $manager->email,
                'password' => 'password',
            ])
            ->assertRedirect(route('manager.dashboard'));
    }

    public function test_manager_can_update_restaurant_table(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $table = RestaurantTable::create([
            'number' => 911,
            'seats' => 2,
            'status' => RestaurantTable::STATUS_FREE,
            'assigned_waiter_id' => null,
        ]);

        $response = $this
            ->actingAs($manager)
            ->put(route('manager.tables.update', $table), [
                'number' => 912,
                'seats' => 6,
                'status' => RestaurantTable::STATUS_RESERVED,
                'assigned_waiter_id' => $waiter->id,
            ]);

        $response->assertRedirect(route('manager.tables.index'));

        $this->assertDatabaseHas('restaurant_tables', [
            'id' => $table->id,
            'number' => 912,
            'seats' => 6,
            'status' => RestaurantTable::STATUS_RESERVED,
            'assigned_waiter_id' => $waiter->id,
        ]);
    }

    public function test_manager_can_see_assigned_waiter_on_table_list(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);
        $waiter = User::factory()->create([
            'name' => 'Kelner Przypisany',
            'role' => User::ROLE_WAITER,
        ]);
        RestaurantTable::create([
            'number' => 915,
            'seats' => 4,
            'status' => RestaurantTable::STATUS_FREE,
            'assigned_waiter_id' => $waiter->id,
        ]);

        $this
            ->actingAs($manager)
            ->get(route('manager.tables.index'))
            ->assertOk()
            ->assertSee('Kelner Przypisany')
            ->assertSee('Przypisany kelner');
    }

    public function test_manager_cannot_assign_table_to_non_waiter(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);
        $kitchenUser = User::factory()->create(['role' => User::ROLE_KITCHEN]);

        $this
            ->actingAs($manager)
            ->post(route('manager.tables.store'), [
                'number' => 916,
                'seats' => 4,
                'status' => RestaurantTable::STATUS_FREE,
                'assigned_waiter_id' => $kitchenUser->id,
            ])
            ->assertSessionHasErrors('assigned_waiter_id');

        $this->assertDatabaseMissing('restaurant_tables', [
            'number' => 916,
        ]);
    }

    public function test_waiter_cannot_access_restaurant_table_management(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);

        $this
            ->actingAs($waiter)
            ->get(route('manager.tables.index'))
            ->assertForbidden();
    }

    public function test_manager_can_delete_table_without_orders(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);
        $table = RestaurantTable::create([
            'number' => 913,
            'seats' => 2,
            'status' => RestaurantTable::STATUS_FREE,
        ]);

        $this
            ->actingAs($manager)
            ->delete(route('manager.tables.destroy', $table))
            ->assertRedirect(route('manager.tables.index'));

        $this->assertDatabaseMissing('restaurant_tables', [
            'id' => $table->id,
        ]);
    }

    public function test_table_with_order_history_cannot_be_deleted(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $table = RestaurantTable::create([
            'number' => 914,
            'seats' => 4,
            'status' => RestaurantTable::STATUS_OCCUPIED,
        ]);

        Order::create([
            'restaurant_table_id' => $table->id,
            'waiter_id' => $waiter->id,
            'status' => Order::STATUS_OPEN,
            'opened_at' => now(),
        ]);

        $this
            ->actingAs($manager)
            ->delete(route('manager.tables.destroy', $table))
            ->assertRedirect(route('manager.tables.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('restaurant_tables', [
            'id' => $table->id,
            'status' => RestaurantTable::STATUS_OCCUPIED,
        ]);
    }
}
