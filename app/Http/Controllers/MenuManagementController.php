<?php

namespace App\Http\Controllers;

use App\Models\MenuCategory;

class MenuManagementController extends Controller
{
    public function index()
    {
        $categories = MenuCategory::query()
            ->with(['items' => fn ($query) => $query->orderBy('name')])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('menu-manager', [
            'categories' => $categories,
            'activeCategories' => $categories->where('is_active', true),
        ]);
    }
}
