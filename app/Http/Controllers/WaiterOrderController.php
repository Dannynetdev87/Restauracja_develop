<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\RestaurantTable;
use Illuminate\Support\Facades\DB;

class WaiterOrderController extends Controller
{
    /**
     * Rozpoczęcie nowego zamówienia dla stolika.
     */
    public function store(RestaurantTable $restaurantTable)
    {
        if (! $restaurantTable->canOpenOrder()) {
            return redirect()
                ->route('waiter.tables.index')
                ->with('error', 'Zamówienie można rozpocząć tylko dla wolnego stolika bez aktywnego zamówienia.');
        }

        $order = DB::transaction(function () use ($restaurantTable) {
            $order = Order::create([
                'restaurant_table_id' => $restaurantTable->id,
                'waiter_id' => request()->user()->id,
                'status' => Order::STATUS_OPEN,
                'opened_at' => now(),
            ]);

            $restaurantTable->update([
                'status' => RestaurantTable::STATUS_OCCUPIED,
            ]);

            return $order;
        });

        return redirect()
            ->route('waiter.orders.show', $order)
            ->with('success', 'Zamówienie zostało rozpoczęte.');
    }

    /**
     * Wyświetlenie szczegółów zamówienia.
     */
    public function show(Order $order)
    {
        if ($order->waiter_id !== request()->user()->id) {
            abort(403);
        }

        return view('waiter.orders.show', [
            'order' => $order->load(['table', 'items.menuItem']),
        ]);
    }

    /**
     * Generowanie widoku rachunku do druku.
     */
    public function receipt(Order $order)
    {
        // Sprawdzenie, czy zamówienie należy do aktualnego kelnera
        if ($order->waiter_id !== request()->user()->id) {
            abort(403);
        }

        // Załadowanie relacji: stolik i pozycje zamówienia z daniami
        $order->load(['table', 'items.menuItem']);

        return view('waiter.orders.receipt', [
            'order' => $order
        ]);
    }

    /**
     * Zamyka zamówienie, zwalnia stolik i przygotowuje rachunek.
     */
    public function finish(Order $order)
    {
        // Sprawdzenie czy kelner ma uprawnienia do tego zamówienia
        if ($order->waiter_id !== request()->user()->id) {
            abort(403, 'Brak uprawnień do tego zamówienia.');
        }

        // Używamy transakcji, aby mieć pewność, że oba kroki się wykonają
        DB::transaction(function () use ($order) {
            // 1. Aktualizacja statusu zamówienia na zamknięte
            $order->update([
                'status' => 'zamkniete',
            ]);

            // 2. Zwolnienie stolika (zmiana statusu na wolny)
            // Upewnij się, że używasz odpowiedniej stałej dla statusu wolnego
            $order->table->update([
                'status' => RestaurantTable::STATUS_FREE,
            ]);
        });

        // Wczytanie powiązanych danych dla widoku
        $order->load(['table', 'items.menuItem']);

        // Przekazanie danych do widoku rachunku
        return view('waiter.orders.receipt', [
            'order' => $order
        ]);
    }
}
