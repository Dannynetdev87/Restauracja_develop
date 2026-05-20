<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMenuCategoryRequest;
use App\Http\Requests\UpdateMenuCategoryRequest;
use App\Models\MenuCategory;

class MenuCategoryController extends Controller
{
    public function store(StoreMenuCategoryRequest $request)
    {
        $data = $request->validated();
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active'] = $request->boolean('is_active');

        MenuCategory::create($data);

        return redirect()
            ->route('manager.podglad')
            ->with('success', 'Kategoria menu została dodana.');
    }

    public function edit(MenuCategory $menuCategory)
    {
        return view('manager.menu.category-edit', [
            'category' => $menuCategory,
        ]);
    }

    public function update(UpdateMenuCategoryRequest $request, MenuCategory $menuCategory)
    {
        $data = $request->validated();
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active'] = $request->boolean('is_active');

        $menuCategory->update($data);

        return redirect()
            ->route('manager.podglad')
            ->with('success', 'Kategoria menu została zaktualizowana.');
    }

    public function destroy(MenuCategory $menuCategory)
    {
        if ($menuCategory->items()->exists()) {
            return redirect()
                ->route('manager.podglad')
                ->with('error', 'Nie można usunąć kategorii, która ma przypisane pozycje menu.');
        }

        $menuCategory->delete();

        return redirect()
            ->route('manager.podglad')
            ->with('success', 'Kategoria menu została usunięta.');
    }
}
