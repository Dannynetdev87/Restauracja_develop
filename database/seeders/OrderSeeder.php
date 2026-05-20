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

        $waiter = User::where('role', User::ROLE_WAITER)->firstOrFail();

        $occupiedTable = RestaurantTable::where('number', 2)->firstOrFail();
        $freeTable = RestaurantTable::where('number', 3)->firstOrFail();

        $activeOrder = Order::create([
            'restaurant_table_id' => $occupiedTable->id,
            'waiter_id' => $waiter->id,
            'status' => Order::STATUS_IN_PROGRESS,
            'opened_at' => now()->subMinutes(35),
        ]);

        $this->addItem($activeOrder, 'Kotlet schabowy', 2, OrderItem::STATUS_PREPARING, 'Bez surowki dla jednej porcji.', $waiter);
        $this->addItem($activeOrder, 'Lemoniada domowa', 2, OrderItem::STATUS_READY, null, $waiter);

        $closedOrder = Order::create([
            'restaurant_table_id' => $freeTable->id,
            'waiter_id' => $waiter->id,
            'status' => Order::STATUS_PAID,
            'opened_at' => now()->subHours(3),
            'closed_at' => now()->subHours(2),
            'paid_at' => now()->subHours(2),
        ]);

        $this->addItem($closedOrder, 'Rosol z makaronem', 1, OrderItem::STATUS_DELIVERED, null, $waiter);
        $this->addItem($closedOrder, 'Pierogi ruskie', 1, OrderItem::STATUS_DELIVERED, null, $waiter);
        $this->addItem($closedOrder, 'Kawa czarna', 2, OrderItem::STATUS_DELIVERED, null, $waiter);

        Payment::create([
            'order_id' => $closedOrder->id,
            'amount' => $closedOrder->total(),
            'payment_method' => Payment::METHOD_CARD,
            'status' => Payment::STATUS_PAID,
            'paid_at' => $closedOrder->paid_at,
        ]);
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
}
