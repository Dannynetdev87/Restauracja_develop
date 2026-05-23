<?php

namespace App\Http\Controllers;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RestaurantTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WaiterOrderController extends Controller
{
    public function create(Request $request)
    {
        $selectedTable = null;
        $activeOrder = null;

        if ($request->filled('table_id')) {
            $selectedTable = RestaurantTable::query()
                ->with(['activeOrders' => fn ($query) => $query->latest('opened_at')])
                ->whereKey($request->integer('table_id'))
                ->where('status', '!=', RestaurantTable::STATUS_INACTIVE)
                ->firstOrFail();

            $activeOrder = $selectedTable->activeOrders->first();
        }

        return view('waiter.orders.create', [
            'tables' => RestaurantTable::query()
                ->where('status', '!=', RestaurantTable::STATUS_INACTIVE)
                ->with(['activeOrders' => fn ($query) => $query->latest('opened_at')])
                ->orderBy('number')
                ->get(),
            'categories' => MenuCategory::query()
                ->where('is_active', true)
                ->with(['availableItems' => fn ($query) => $query->orderBy('name')])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'selectedTable' => $selectedTable,
            'activeOrder' => $activeOrder,
        ]);
    }

    public function store(Request $request, RestaurantTable $restaurantTable)
    {
        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*.quantity' => ['nullable', 'integer', 'min:0', 'max:99'],
            'items.*.notes' => ['nullable', 'string', 'max:500'],
        ]);

        $selectedItems = collect($validated['items'])
            ->map(function (array $item, string|int $menuItemId): ?array {
                if (! ctype_digit((string) $menuItemId)) {
                    return null;
                }

                $quantity = (int) ($item['quantity'] ?? 0);

                if ($quantity < 1) {
                    return null;
                }

                $notes = trim((string) ($item['notes'] ?? ''));

                return [
                    'menu_item_id' => (int) $menuItemId,
                    'quantity' => $quantity,
                    'notes' => $notes !== '' ? $notes : null,
                ];
            })
            ->filter()
            ->values();

        if ($selectedItems->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => 'Wybierz przynajmniej jedną pozycję menu i podaj ilość większą od zera.',
            ]);
        }

        $menuItems = MenuItem::query()
            ->whereIn('id', $selectedItems->pluck('menu_item_id'))
            ->where('available', true)
            ->get()
            ->keyBy('id');

        if ($menuItems->count() !== $selectedItems->pluck('menu_item_id')->unique()->count()) {
            throw ValidationException::withMessages([
                'items' => 'Wybrano pozycję menu, która nie istnieje albo nie jest obecnie dostępna.',
            ]);
        }

        $order = DB::transaction(function () use ($restaurantTable, $selectedItems, $menuItems) {
            $table = RestaurantTable::query()
                ->lockForUpdate()
                ->findOrFail($restaurantTable->id);

            $order = Order::query()
                ->where('restaurant_table_id', $table->id)
                ->whereIn('status', Order::activeStatuses())
                ->latest('opened_at')
                ->first();

            if ($order && $order->waiter_id !== request()->user()->id) {
                throw ValidationException::withMessages([
                    'table' => 'Ten stolik ma aktywne zamówienie przypisane do innego kelnera.',
                ]);
            }

            if (! $order) {
                if (! $table->canOpenOrder()) {
                    throw ValidationException::withMessages([
                        'table' => 'Zamówienie można rozpocząć tylko dla wolnego stolika bez aktywnego zamówienia.',
                    ]);
                }

                $order = Order::create([
                    'restaurant_table_id' => $table->id,
                    'waiter_id' => request()->user()->id,
                    'status' => Order::STATUS_OPEN,
                    'opened_at' => now(),
                ]);

                $table->update([
                    'status' => RestaurantTable::STATUS_OCCUPIED,
                ]);
            }

            foreach ($selectedItems as $selectedItem) {
                $menuItem = $menuItems->get($selectedItem['menu_item_id']);

                $orderItem = $order->items()
                    ->where('menu_item_id', $menuItem->id)
                    ->where('status', OrderItem::STATUS_NEW)
                    ->where('notes', $selectedItem['notes'])
                    ->first();

                if ($orderItem) {
                    $orderItem->increment('quantity', $selectedItem['quantity']);

                    continue;
                }

                $orderItem = $order->items()->create([
                    'menu_item_id' => $menuItem->id,
                    'quantity' => $selectedItem['quantity'],
                    'unit_price' => $menuItem->price,
                    'notes' => $selectedItem['notes'],
                    'status' => OrderItem::STATUS_NEW,
                ]);

                $orderItem->statusHistory()->create([
                    'changed_by' => request()->user()->id,
                    'old_status' => null,
                    'new_status' => OrderItem::STATUS_NEW,
                ]);
            }

            return $order;
        });

        return redirect()
            ->route('waiter.orders.show', $order)
            ->with('success', 'Pozycje zostały zapisane w zamówieniu.');
    }

    public function show(Order $order)
    {
        if ($order->waiter_id !== request()->user()->id) {
            abort(403);
        }

        return view('waiter.orders.show', [
            'order' => $order->load(['table', 'items.menuItem']),
        ]);
    }

    public function deliverItem(OrderItem $orderItem)
    {
        $orderItem->load('order.items');

        if ($orderItem->order->waiter_id !== request()->user()->id) {
            abort(403);
        }

        if ($orderItem->status !== OrderItem::STATUS_READY) {
            throw ValidationException::withMessages([
                'status' => 'Dostarczyć można tylko pozycję oznaczoną jako gotowa.',
            ]);
        }

        $order = DB::transaction(function () use ($orderItem) {
            $orderItem->refresh();
            $oldStatus = $orderItem->status;

            if ($oldStatus !== OrderItem::STATUS_READY) {
                throw ValidationException::withMessages([
                    'status' => 'Dostarczyć można tylko pozycję oznaczoną jako gotowa.',
                ]);
            }

            $orderItem->update([
                'status' => OrderItem::STATUS_DELIVERED,
            ]);

            $orderItem->statusHistory()->create([
                'changed_by' => request()->user()->id,
                'old_status' => $oldStatus,
                'new_status' => OrderItem::STATUS_DELIVERED,
            ]);

            $order = $orderItem->order()->with('items')->firstOrFail();

            if ($order->items->isNotEmpty() && $order->items->every(fn (OrderItem $item) => $item->status === OrderItem::STATUS_DELIVERED)) {
                $order->update([
                    'status' => Order::STATUS_SERVED,
                ]);
            }

            return $order;
        });

        return redirect()
            ->route('waiter.orders.show', $order)
            ->with('success', 'Pozycja została oznaczona jako dostarczona.');
    }
}
