<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemStatusHistory;
use App\Models\Payment;
use App\Models\RestaurantTable;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('order_item_status_histories')->delete();
        DB::table('payments')->delete();
        DB::table('order_items')->delete();
        DB::table('orders')->delete();

        $defaultWaiter = User::query()
            ->where('email', 'kelner@example.com')
            ->firstOr(fn () => User::where('role', User::ROLE_WAITER)->firstOrFail());

        $table2 = RestaurantTable::where('number', 2)->firstOrFail();
        $table3 = RestaurantTable::where('number', 3)->firstOrFail();
        $table4 = RestaurantTable::where('number', 4)->firstOrFail();
        $table7 = RestaurantTable::where('number', 7)->firstOrFail();
        $table12 = RestaurantTable::where('number', 12)->firstOrFail();

        $smallActiveWaiter = $this->waiterForTable($table7, $defaultWaiter);
        $table7->update(['status' => RestaurantTable::STATUS_OCCUPIED]);
        $smallActiveOrder = Order::create([
            'restaurant_table_id' => $table7->id,
            'waiter_id' => $smallActiveWaiter->id,
            'status' => Order::STATUS_IN_PROGRESS,
            'opened_at' => now()->subMinutes(20),
        ]);
        $this->addItem($smallActiveOrder, 'Tradycyjna zupa pomidorowa z makaronem', 1, OrderItem::STATUS_PREPARING, null, $smallActiveWaiter);
        $this->addItem($smallActiveOrder, 'Pizza Margherita', 1, OrderItem::STATUS_NEW, 'Dodatkowy ser.', $smallActiveWaiter);
        $this->addItem($smallActiveOrder, 'Lemoniada domowa 0.4L', 2, OrderItem::STATUS_READY, null, $smallActiveWaiter);

        $mediumActiveWaiter = $this->waiterForTable($table2, $defaultWaiter);
        $table2->update(['status' => RestaurantTable::STATUS_OCCUPIED]);
        $mediumActiveOrder = Order::create([
            'restaurant_table_id' => $table2->id,
            'waiter_id' => $mediumActiveWaiter->id,
            'status' => Order::STATUS_IN_PROGRESS,
            'opened_at' => now()->subMinutes(45),
        ]);
        $this->addItem($mediumActiveOrder, 'Kotlet schabowy smazony na smalcu', 2, OrderItem::STATUS_PREPARING, 'Bez surowki.', $mediumActiveWaiter);
        $this->addItem($mediumActiveOrder, 'Rosol z wiejskiej kury z makaronem', 2, OrderItem::STATUS_PREPARING, null, $mediumActiveWaiter);
        $this->addItem($mediumActiveOrder, 'Pierogi ruskie ze skwarkami i cebula', 2, OrderItem::STATUS_NEW, null, $mediumActiveWaiter);
        $this->addItem($mediumActiveOrder, 'Coca-Cola 0.25L', 4, OrderItem::STATUS_READY, 'Z lodem i cytryna.', $mediumActiveWaiter);

        $vipActiveWaiter = $this->waiterForTable($table12, $defaultWaiter);
        $table12->update(['status' => RestaurantTable::STATUS_OCCUPIED]);
        $vipActiveOrder = Order::create([
            'restaurant_table_id' => $table12->id,
            'waiter_id' => $vipActiveWaiter->id,
            'status' => Order::STATUS_IN_PROGRESS,
            'opened_at' => now()->subMinutes(60),
        ]);
        $this->addItem($vipActiveOrder, 'Stek wolowy z maslem czosnkowym', 3, OrderItem::STATUS_PREPARING, 'Medium rare.', $vipActiveWaiter);
        $this->addItem($vipActiveOrder, 'Pol kaczki pieczonej z jablkami', 2, OrderItem::STATUS_NEW, null, $vipActiveWaiter);
        $this->addItem($vipActiveOrder, 'Aperol Spritz', 4, OrderItem::STATUS_PREPARING, 'Duzo lodu.', $vipActiveWaiter);
        $this->addItem($vipActiveOrder, 'Piwo IPA rzemieslnicze 0.5L', 3, OrderItem::STATUS_READY, null, $vipActiveWaiter);
        $this->addItem($vipActiveOrder, 'Woda mineralna Perlage 0.3L', 6, OrderItem::STATUS_READY, null, $vipActiveWaiter);
        $this->addItem($vipActiveOrder, 'Szarlotka domowa na cieplo', 3, OrderItem::STATUS_NEW, 'Z lodami.', $vipActiveWaiter);

        $paidFamilyWaiter = $this->waiterForTable($table4, $defaultWaiter);
        $paidFamilyOrder = Order::create([
            'restaurant_table_id' => $table4->id,
            'waiter_id' => $paidFamilyWaiter->id,
            'status' => Order::STATUS_PAID,
            'opened_at' => now()->subDays(3),
            'closed_at' => now()->subDays(3)->addHours(2),
            'paid_at' => now()->subDays(3)->addHours(2),
        ]);
        $this->addItem($paidFamilyOrder, 'Kotlet schabowy smazony na smalcu', 5, OrderItem::STATUS_DELIVERED, null, $paidFamilyWaiter);
        $this->addItem($paidFamilyOrder, 'Rosol z wiejskiej kury z makaronem', 5, OrderItem::STATUS_DELIVERED, null, $paidFamilyWaiter);
        $this->addPayment($paidFamilyOrder, Payment::METHOD_CARD);

        $paidCoffeeWaiter = $this->waiterForTable($table4, $defaultWaiter);
        $paidCoffeeOrder = Order::create([
            'restaurant_table_id' => $table4->id,
            'waiter_id' => $paidCoffeeWaiter->id,
            'status' => Order::STATUS_PAID,
            'opened_at' => now()->subDays(1),
            'closed_at' => now()->subDays(1)->addHour(),
            'paid_at' => now()->subDays(1)->addHour(),
        ]);
        $this->addItem($paidCoffeeOrder, 'Sernik tradycyjny puszysty', 4, OrderItem::STATUS_DELIVERED, null, $paidCoffeeWaiter);
        $this->addItem($paidCoffeeOrder, 'Kawa czarna Americano', 4, OrderItem::STATUS_DELIVERED, null, $paidCoffeeWaiter);
        $this->addPayment($paidCoffeeOrder, Payment::METHOD_CASH);

        $paidSimpleWaiter = $this->waiterForTable($table3, $defaultWaiter);
        $paidSimpleOrder = Order::create([
            'restaurant_table_id' => $table3->id,
            'waiter_id' => $paidSimpleWaiter->id,
            'status' => Order::STATUS_PAID,
            'opened_at' => now()->subHours(4),
            'closed_at' => now()->subHours(3),
            'paid_at' => now()->subHours(3),
        ]);
        $this->addItem($paidSimpleOrder, 'Pierogi z kapusta i lesnymi grzybami', 1, OrderItem::STATUS_DELIVERED, null, $paidSimpleWaiter);
        $this->addItem($paidSimpleOrder, 'Herbata w dzbanku', 1, OrderItem::STATUS_DELIVERED, null, $paidSimpleWaiter);
        $this->addPayment($paidSimpleOrder, Payment::METHOD_BLIK);
    }

    private function waiterForTable(RestaurantTable $table, User $fallbackWaiter): User
    {
        $table->loadMissing(['assignedWaiter', 'zone.assignedWaiter']);

        return $table->effectiveAssignedWaiter() ?? $fallbackWaiter;
    }

    private function addItem(
        Order $order,
        string $menuItemName,
        int $quantity,
        string $status,
        ?string $notes,
        User $changedBy,
    ): void {
        $menuItem = MenuItem::where('name', $menuItemName)->firstOrFail();

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'menu_item_id' => $menuItem->id,
            'quantity' => $quantity,
            'unit_price' => $menuItem->price,
            'notes' => $notes,
            'status' => $status,
        ]);

        OrderItemStatusHistory::create([
            'order_item_id' => $orderItem->id,
            'changed_by' => $changedBy->id,
            'old_status' => null,
            'new_status' => $status,
        ]);
    }

    private function addPayment(Order $order, string $method): void
    {
        Payment::create([
            'order_id' => $order->id,
            'amount' => $order->total(),
            'payment_method' => $method,
            'status' => Payment::STATUS_PAID,
            'paid_at' => $order->paid_at,
        ]);
    }
}
