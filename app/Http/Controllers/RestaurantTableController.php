<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRestaurantTableRequest;
use App\Http\Requests\UpdateRestaurantTableRequest;
use App\Models\RestaurantTable;
use App\Models\User;
use App\Models\Zone;

class RestaurantTableController extends Controller
{
    public function index()
    {
        return view('manager.tables.index', [
            'tables' => RestaurantTable::query()
                ->with(['assignedWaiter', 'zone.assignedWaiter'])
                ->withCount('orders')
                ->orderBy('number')
                ->get(),
            'statuses' => $this->statuses(),
            'waiters' => $this->waiters(),
            'zones' => $this->zones(),
        ]);
    }

    public function store(StoreRestaurantTableRequest $request)
    {
        RestaurantTable::create($request->validated());

        return redirect()
            ->route('manager.tables.index')
            ->with('success', 'Stolik został dodany.');
    }

    public function edit(RestaurantTable $restaurantTable)
    {
        return view('manager.tables.edit', [
            'table' => $restaurantTable->load(['assignedWaiter', 'zone.assignedWaiter']),
            'statuses' => $this->statuses(),
            'waiters' => $this->waiters(),
            'zones' => $this->zones(),
        ]);
    }

    public function update(UpdateRestaurantTableRequest $request, RestaurantTable $restaurantTable)
    {
        $restaurantTable->update($request->validated());

        return redirect()
            ->route('manager.tables.index')
            ->with('success', 'Stolik został zaktualizowany.');
    }

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

    private function statuses(): array
    {
        return [
            RestaurantTable::STATUS_FREE => 'Wolny',
            RestaurantTable::STATUS_OCCUPIED => 'Zajęty',
            RestaurantTable::STATUS_RESERVED => 'Zarezerwowany',
            RestaurantTable::STATUS_INACTIVE => 'Nieaktywny',
        ];
    }

    private function waiters()
    {
        return User::query()
            ->where('role', User::ROLE_WAITER)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    private function zones()
    {
        return Zone::query()
            ->with(['assignedWaiter'])
            ->withCount('tables')
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();
    }
}
