<?php

namespace Tests\Feature;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RestaurantTable;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GuestHomeViewTest extends TestCase
{
    use DatabaseTransactions;

    public function test_guest_can_see_all_tables_and_waiting_time_without_order_items(): void
    {
        $this->travelTo(now()->setTime(12, 0));
        $zone = Zone::create(['name' => 'Widok ogrodu']);

        RestaurantTable::create([
            'number' => 951,
            'seats' => 2,
            'status' => RestaurantTable::STATUS_FREE,
            'zone_id' => $zone->id,
        ]);

        $occupiedTable = RestaurantTable::create([
            'number' => 952,
            'seats' => 4,
            'status' => RestaurantTable::STATUS_OCCUPIED,
        ]);

        RestaurantTable::create([
            'number' => 953,
            'seats' => 6,
            'status' => RestaurantTable::STATUS_RESERVED,
        ]);

        RestaurantTable::create([
            'number' => 954,
            'seats' => 2,
            'status' => RestaurantTable::STATUS_INACTIVE,
        ]);

        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $order = Order::create([
            'restaurant_table_id' => $occupiedTable->id,
            'waiter_id' => $waiter->id,
            'status' => Order::STATUS_OPEN,
            'opened_at' => now()->subMinutes(23),
        ]);
        $this->createOrderItem($order, 'Tajne pierogi goscia');

        DB::enableQueryLog();

        $this
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Dostępność stolików')
            ->assertSee('Stolik nr 951')
            ->assertSee('Widok ogrodu')
            ->assertSee('Stolik nr 952')
            ->assertSee('Stolik nr 953')
            ->assertSee('Stolik nr 954')
            ->assertSee('Wolny')
            ->assertSee('Zajęty')
            ->assertSee('Zarezerwowany')
            ->assertSee('Nieaktywny')
            ->assertSee('Czas oczekiwania')
            ->assertSee('23 min')
            ->assertDontSee('Tajne pierogi goscia');

        $queries = collect(DB::getQueryLog())->pluck('query')->implode(' ');

        $this->assertStringNotContainsString('order_items', $queries);
        $this->assertStringNotContainsString('menu_items', $queries);
    }

    public function test_manager_home_view_uses_database_tables(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);

        RestaurantTable::create([
            'number' => 955,
            'seats' => 8,
            'status' => RestaurantTable::STATUS_RESERVED,
        ]);

        $this
            ->actingAs($manager)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Zarządzanie stolikami')
            ->assertSee('Stolik nr 955')
            ->assertSee('8')
            ->assertSee('Zarezerwowany');
    }

    private function createOrderItem(Order $order, string $name): OrderItem
    {
        $category = MenuCategory::create([
            'name' => 'Testowa kategoria widoku goscia',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $menuItem = MenuItem::create([
            'menu_category_id' => $category->id,
            'name' => $name,
            'description' => null,
            'price' => 24.00,
            'production_area' => MenuItem::AREA_KITCHEN,
            'available' => true,
        ]);

        return OrderItem::create([
            'order_id' => $order->id,
            'menu_item_id' => $menuItem->id,
            'quantity' => 1,
            'unit_price' => 24.00,
            'notes' => null,
            'status' => OrderItem::STATUS_NEW,
        ]);
    }
}
