<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRestaurantTableRequest;
use App\Http\Requests\UpdateRestaurantTableRequest;
use App\Models\RestaurantTable;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class RestaurantTableController extends Controller
{
    /**
     * Lista stolików w panelu managera
     */
    public function index()
    {
        return view('manager.tables.index', [
            'tables' => RestaurantTable::query()
                ->withCount('orders')
                ->orderBy('number')
                ->get(),
            'statuses' => $this->statuses(),
        ]);
    }

    /**
     * Zapis nowego stolika do bazy danych
     */
    public function store(StoreRestaurantTableRequest $request)
    {
        RestaurantTable::create($request->validated());

        return redirect()
            ->route('manager.tables.index')
            ->with('success', 'Stolik został dodany.');
    }

    /**
     * Formularz edycji stolika
     */
    public function edit(RestaurantTable $restaurantTable)
    {
        return view('manager.tables.edit', [
            'table' => $restaurantTable,
            'statuses' => $this->statuses(),
        ]);
    }

    /**
     * Aktualizacja danych stolika
     */
    public function update(UpdateRestaurantTableRequest $request, RestaurantTable $restaurantTable)
    {
        $restaurantTable->update($request->validated());

        return redirect()
            ->route('manager.tables.index')
            ->with('success', 'Stolik został zaktualizowany.');
    }

    /**
     * Bezpieczne usuwanie stolika (z blokadą historii)
     */
    public function destroy(RestaurantTable $restaurantTable)
    {
        if ($restaurantTable->orders()->exists()) {
            return redirect()
                ->route('manager.tables.index')
                ->with('error', 'Nie można usunąć stolika, który ma historię zamówień. Ustaw status nieaktywny.');
        }

        $restaurantTable->delete();

        return redirect()
            ->route('manager.tables.index')
            ->with('success', 'Stolik został usunięty.');
    }

    /**
     * NOWOŚĆ: Natychmiastowe zwolnienie wszystkich stolików na sali
     */
    public function resetAllTables()
    {
        try {
            // Bezpieczne wykonanie operacji w atomowej transakcji bazy danych
            DB::transaction(function () {
                // 1. Zmień status wszystkich aktywnych (wolnych, zajętych, zarezerwowanych) stolików na 'wolny'
                RestaurantTable::where('status', '!=', RestaurantTable::STATUS_INACTIVE)
                    ->update(['status' => RestaurantTable::STATUS_FREE]);

                // 2. Anuluj i domknij wszystkie otwarte zamówienia, aby zwolnić kelnerom kontekst POS
                Order::whereIn('status', ['open', 'in_progress', 'ready', 'served'])
                    ->update([
                        'status' => 'cancelled',
                        'closed_at' => now()
                    ]);
            });

            return redirect()
                ->route('manager.tables.index')
                ->with('success', 'Wszystkie stoliki na sali zostały zwolnione, a aktywne zamówienia pomyślnie anulowane.');

        } catch (\Exception $e) {
            return redirect()
                ->route('manager.tables.index')
                ->with('error', 'Wystąpił błąd podczas resetowania sali: ' . $e->getMessage());
        }
    }

    /**
     * Słownik statusów stolików
     */
    private function statuses(): array
    {
        return [
            RestaurantTable::STATUS_FREE => 'Wolny',
            RestaurantTable::STATUS_OCCUPIED => 'Zajęty',
            RestaurantTable::STATUS_RESERVED => 'Zarezerwowany',
            RestaurantTable::STATUS_INACTIVE => 'Nieaktywny',
        ];
    }
}
