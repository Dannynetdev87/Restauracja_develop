<?php

namespace Tests\Feature;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RestaurantTable;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class MenuManagementTest extends TestCase
{
    use DatabaseTransactions;

    public function test_manager_can_create_menu_category(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);

        $response = $this
            ->actingAs($manager)
            ->post(route('manager.menu-categories.store'), [
                'name' => 'Przystawki',
                'sort_order' => 5,
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('manager.podglad'));

        $this->assertDatabaseHas('menu_categories', [
            'name' => 'Przystawki',
            'sort_order' => 5,
            'is_active' => true,
        ]);
    }

    #[DataProvider('menuManagerRoles')]
    public function test_manager_or_admin_cannot_create_menu_category_with_duplicate_sort_order(string $role): void
    {
        $manager = User::factory()->create(['role' => $role]);

        MenuCategory::create([
            'name' => 'Istniejace zupy',
            'sort_order' => 5,
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($manager)
            ->post(route('manager.menu-categories.store'), [
                'name' => 'Nowe przystawki',
                'sort_order' => 5,
                'is_active' => '1',
            ]);

        $response->assertSessionHasErrors('sort_order');

        $this->assertDatabaseMissing('menu_categories', [
            'name' => 'Nowe przystawki',
            'sort_order' => 5,
        ]);
    }

    #[DataProvider('menuManagerRoles')]
    public function test_manager_or_admin_can_create_menu_category_with_unique_sort_order(string $role): void
    {
        $manager = User::factory()->create(['role' => $role]);

        MenuCategory::create([
            'name' => 'Istniejace zupy',
            'sort_order' => 5,
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($manager)
            ->post(route('manager.menu-categories.store'), [
                'name' => 'Nowe przystawki',
                'sort_order' => 6,
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('manager.podglad'));

        $this->assertDatabaseHas('menu_categories', [
            'name' => 'Nowe przystawki',
            'sort_order' => 6,
            'is_active' => true,
        ]);
    }

    #[DataProvider('menuManagerRoles')]
    public function test_manager_or_admin_can_update_menu_category_without_changing_sort_order(string $role): void
    {
        $manager = User::factory()->create(['role' => $role]);
        $category = MenuCategory::create([
            'name' => 'Zupy sezonowe',
            'sort_order' => 5,
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($manager)
            ->put(route('manager.menu-categories.update', $category), [
                'name' => 'Zupy klasyczne',
                'sort_order' => 5,
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('manager.podglad'));

        $this->assertDatabaseHas('menu_categories', [
            'id' => $category->id,
            'name' => 'Zupy klasyczne',
            'sort_order' => 5,
        ]);
    }

    #[DataProvider('menuManagerRoles')]
    public function test_manager_or_admin_cannot_update_menu_category_to_duplicate_sort_order(string $role): void
    {
        $manager = User::factory()->create(['role' => $role]);

        MenuCategory::create([
            'name' => 'Zupy sezonowe',
            'sort_order' => 5,
            'is_active' => true,
        ]);

        $category = MenuCategory::create([
            'name' => 'Przystawki sezonowe',
            'sort_order' => 6,
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($manager)
            ->put(route('manager.menu-categories.update', $category), [
                'name' => 'Przystawki klasyczne',
                'sort_order' => 5,
                'is_active' => '1',
            ]);

        $response->assertSessionHasErrors('sort_order');

        $this->assertDatabaseHas('menu_categories', [
            'id' => $category->id,
            'name' => 'Przystawki sezonowe',
            'sort_order' => 6,
        ]);
    }

    public static function menuManagerRoles(): array
    {
        return [
            'manager' => [User::ROLE_MANAGER],
            'admin' => [User::ROLE_ADMIN],
        ];
    }

    public function test_waiter_cannot_access_menu_management(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);

        $this
            ->actingAs($waiter)
            ->get(route('manager.podglad'))
            ->assertForbidden();
    }

    public function test_manager_can_create_menu_item(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);
        $category = MenuCategory::create([
            'name' => 'Test dania glowne',
            'sort_order' => 10,
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($manager)
            ->post(route('manager.menu-items.store'), [
                'menu_category_id' => $category->id,
                'name' => 'Pierogi',
                'description' => 'Porcja 10 sztuk.',
                'price' => '29.90',
                'production_area' => MenuItem::AREA_KITCHEN,
                'available' => '1',
            ]);

        $response->assertRedirect(route('manager.podglad'));

        $this->assertDatabaseHas('menu_items', [
            'menu_category_id' => $category->id,
            'name' => 'Pierogi',
            'price' => '29.90',
            'production_area' => MenuItem::AREA_KITCHEN,
            'available' => true,
        ]);
    }

    public function test_public_menu_shows_only_available_items(): void
    {
        $category = MenuCategory::create([
            'name' => 'Test napoje',
            'sort_order' => 10,
            'is_active' => true,
        ]);

        MenuItem::create([
            'menu_category_id' => $category->id,
            'name' => 'Lemoniada',
            'price' => 14,
            'production_area' => MenuItem::AREA_BAR,
            'available' => true,
        ]);

        MenuItem::create([
            'menu_category_id' => $category->id,
            'name' => 'Niedostepna kawa',
            'price' => 12,
            'production_area' => MenuItem::AREA_BAR,
            'available' => false,
        ]);

        $this
            ->get(route('menu.index'))
            ->assertOk()
            ->assertSee('Lemoniada')
            ->assertDontSee('Niedostepna kawa');
    }

    public function test_menu_item_used_in_order_is_deactivated_instead_of_deleted(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $table = RestaurantTable::create([
            'number' => 901,
            'seats' => 4,
            'status' => RestaurantTable::STATUS_OCCUPIED,
        ]);
        $category = MenuCategory::create([
            'name' => 'Test zupy',
            'sort_order' => 10,
            'is_active' => true,
        ]);
        $menuItem = MenuItem::create([
            'menu_category_id' => $category->id,
            'name' => 'Rosol',
            'price' => 18,
            'production_area' => MenuItem::AREA_KITCHEN,
            'available' => true,
        ]);
        $order = Order::create([
            'restaurant_table_id' => $table->id,
            'waiter_id' => $waiter->id,
            'status' => Order::STATUS_OPEN,
            'opened_at' => now(),
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'menu_item_id' => $menuItem->id,
            'quantity' => 1,
            'unit_price' => 18,
            'status' => OrderItem::STATUS_NEW,
        ]);

        $this
            ->actingAs($manager)
            ->delete(route('manager.menu-items.destroy', $menuItem))
            ->assertRedirect(route('manager.podglad'));

        $this->assertDatabaseHas('menu_items', [
            'id' => $menuItem->id,
            'available' => false,
        ]);
    }
}
