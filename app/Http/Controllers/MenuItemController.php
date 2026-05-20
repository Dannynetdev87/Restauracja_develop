<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMenuItemRequest;
use App\Http\Requests\UpdateMenuItemRequest;
use App\Models\MenuCategory;
use App\Models\MenuItem;

class MenuItemController extends Controller
{
    public function store(StoreMenuItemRequest $request)
    {
        $data = $request->validated();
        $data['available'] = $request->boolean('available');

        MenuItem::create($data);

        return redirect()
            ->route('manager.podglad')
            ->with('success', 'Pozycja menu została dodana.');
    }

    public function edit(MenuItem $menuItem)
    {
        return view('manager.menu.item-edit', [
            'menuItem' => $menuItem,
            'categories' => MenuCategory::query()
                ->where(function ($query) use ($menuItem) {
                    $query
                        ->where('is_active', true)
                        ->orWhereKey($menuItem->menu_category_id);
                })
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function update(UpdateMenuItemRequest $request, MenuItem $menuItem)
    {
        $data = $request->validated();
        $data['available'] = $request->boolean('available');

        $menuItem->update($data);

        return redirect()
            ->route('manager.podglad')
            ->with('success', 'Pozycja menu została zaktualizowana.');
    }

    public function toggleAvailability(MenuItem $menuItem)
    {
        $menuItem->update([
            'available' => ! $menuItem->available,
        ]);

        return redirect()
            ->route('manager.podglad')
            ->with('success', 'Dostępność pozycji menu została zmieniona.');
    }

    public function destroy(MenuItem $menuItem)
    {
        if ($menuItem->orderItems()->exists()) {
            $menuItem->update(['available' => false]);

            return redirect()
                ->route('manager.podglad')
                ->with('success', 'Pozycja była użyta w zamówieniach, dlatego została dezaktywowana zamiast usunięta.');
        }

        $menuItem->delete();

        return redirect()
            ->route('manager.podglad')
            ->with('success', 'Pozycja menu została usunięta.');
    }
}
