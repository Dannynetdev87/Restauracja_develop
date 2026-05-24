<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\RestaurantTable;
use App\Models\MenuItem;
use App\Models\MenuCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WaiterOrderController extends Controller
{
    /**
     * Ekran główny All-In-One (formularz z makiety)
     */
    public function create(Request $request)
    {
    }

    public function show(Order $order)
    {
        if ($order->waiter_id !== request()->user()->id) {
            abort(403);
        }
        return view('waiter.orders.show', ['order' => $order->load(['table', 'items.menuItem'])]);
    }

    public function receipt(Order $order)
    {
    }

    /**
     * Wyświetla podsumowanie rachunku przed ostatecznym zamknięciem.
     */
    public function showReceipt(Order $order)
    {
        if ($order->waiter_id !== request()->user()->id) {
            abort(403);
        }
        $order->load(['table', 'items.menuItem']);
        return view('waiter.orders.final-receipt', ['order' => $order]);
    }

    /**
     * Zamyka zamówienie i zwalnia stolik.
     */
    public function finish(Order $order)
    {
        if ($order->waiter_id !== request()->user()->id) {
            abort(403, 'Brak uprawnień.');
        }

        DB::transaction(function () use ($order) {
            $order->update(['status' => Order::STATUS_CLOSED, 'closed_at' => now()]);
            $order->table->update(['status' => RestaurantTable::STATUS_FREE]);
        });

        return redirect()->route('waiter.tables.index')
            ->with('success', 'Zamówienie zostało zakończone i stolik zwolniony.');
        $tables = RestaurantTable::where('status', '!=', 'nieaktywny')
            ->orderBy('number')
            ->get();

        // Pobieramy menu z aktywnymi kategoriami i dostępnymi daniami
        $categories = MenuCategory::where('is_active', true)
            ->with(['items' => function($query) {
                $query->where('available', true);
            }])
            ->orderBy('sort_order')
            ->get();

        $selectedTableId = $request->query('table_id');
        $activeOrder = null;

        // Jeżeli przekazano stolik, sprawdzamy czy ma już aktywne zamówienie (kontekst dobitki)
        if ($selectedTableId) {
            $activeOrder = Order::where('restaurant_table_id', $selectedTableId)
                ->whereIn('status', ['open', 'in_progress', 'ready', 'served'])
                ->with('items.menuItem')
                ->first();
        }

        return view('waiter.orders.create', compact('tables', 'categories', 'selectedTableId', 'activeOrder'));
    }

    /**
     * Zapis nowego zamówienia LUB aktualizacja istniejącego (Dobitka)
     */
    public function store(Request $request, RestaurantTable $restaurantTable)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1|max:99',
            'items.*.notes' => 'nullable|string|max:500',
        ]);

        try {
            $result = DB::transaction(function () use ($request, $restaurantTable) {
                // Blokada pesymistyczna wiersza stolika na czas operacji DB
                $table = RestaurantTable::lockForUpdate()->find($restaurantTable->id);

                // Szukamy istniejącego, otwartego zamówienia dla tego stolika
                $order = Order::where('restaurant_table_id', $table->id)
                    ->whereIn('status', ['open', 'in_progress', 'ready', 'served'])
                    ->first();

                // Jeżeli zamówienie nie istnieje (stolik jest wolny), tworzymy nowy nagłówek
                if (!$order) {
                    $order = Order::create([
                        'restaurant_table_id' => $table->id,
                        'waiter_id' => auth()->id(), // Przypisanie zalogowanego kelnera
                        'status' => 'open',
                        'opened_at' => now(),
                    ]);

                    // Automatyczna zmiana statusu stolika na zajęty
                    $table->update(['status' => 'zajety']);
                    $isNewOrder = true;
                } else {
                    $isNewOrder = false;
                }

                // Zapis lub naddanie pozycji z ilościami i nowymi notatkami kelnera dla kuchni/baru
                foreach ($request->items as $data) {
                    $menuItem = MenuItem::find($data['menu_item_id']);

                    // Sprawdzamy, czy dana pozycja z dokładnie taką samą notatką już leży w zamówieniu
                    // (Jeżeli tak - zwiększamy ilość, jeżeli nie lub ma inną notatkę - tworzymy nowy bon do kuchni)
                    $existingItem = $order->items()
                        ->where('menu_item_id', $menuItem->id)
                        ->where('status', 'new') // Tylko jeśli zamówienie nie poszło jeszcze w produkcję
                        ->where('notes', $data['notes'] ?? null)
                        ->first();

                    if ($existingItem) {
                        $existingItem->increment('quantity', $data['quantity']);
                    } else {
                        $order->items()->create([
                            'menu_item_id' => $menuItem->id,
                            'quantity' => $data['quantity'],
                            'unit_price' => $menuItem->price, // Zamrożenie ceny w historii rachunku
                            'notes' => $data['notes'] ?? null,
                            'status' => 'new', // Status pozycji "NEW" trafia na ekrany kuchni/baru
                        ]);
                    }
                }

                return [
                    'success' => true,
                    'order' => $order,
                    'isNew' => $isNewOrder
                ];
            });

            if (!$result['success']) {
                return redirect()->back()->with('error', $result['message']);
            }

            $message = $result['isNew']
                ? "Zamówienie #{$result['order']->id} zostało pomyślnie otwarte!"
                : "Pomyślnie dodano nowe pozycje do zamówienia #{$result['order']->id}!";

            return redirect()
                ->route('waiter.tables.index') // Powrót na kafelki sali restauracyjnej
                ->with('success', $message);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Wystąpił błąd podczas procesowania zamówienia: ' . $e->getMessage());
        }
    }

    /**
     * NOWOŚĆ: Wyświetlenie szczegółów i bonu zamówienia (Widok show.blade.php)
     */
    public function show(Order $order)
    {
        // Eager loading relacji, by zapobiec problemowi zapytań N+1
        $order->load(['table', 'items.menuItem']);

        // Przekazujemy dane zamówienia do Twojego pierwotnego widoku kelnerskiego
        return view('waiter.orders.show', compact('order'));
    }
}
