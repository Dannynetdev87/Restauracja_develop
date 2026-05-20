<?php

namespace Database\Seeders;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Zupy',
                'sort_order' => 10,
                'items' => [
                    ['name' => 'Rosol z makaronem', 'price' => 18.00, 'production_area' => MenuItem::AREA_KITCHEN],
                    ['name' => 'Krem z pomidorow', 'price' => 20.00, 'production_area' => MenuItem::AREA_KITCHEN],
                ],
            ],
            [
                'name' => 'Dania glowne',
                'sort_order' => 20,
                'items' => [
                    ['name' => 'Kotlet schabowy', 'price' => 36.00, 'production_area' => MenuItem::AREA_KITCHEN],
                    ['name' => 'Pierogi ruskie', 'price' => 29.00, 'production_area' => MenuItem::AREA_KITCHEN],
                    ['name' => 'Filet z lososia', 'price' => 48.00, 'production_area' => MenuItem::AREA_KITCHEN],
                ],
            ],
            [
                'name' => 'Desery',
                'sort_order' => 30,
                'items' => [
                    ['name' => 'Szarlotka', 'price' => 19.00, 'production_area' => MenuItem::AREA_KITCHEN],
                    ['name' => 'Sernik', 'price' => 21.00, 'production_area' => MenuItem::AREA_KITCHEN],
                ],
            ],
            [
                'name' => 'Napoje',
                'sort_order' => 40,
                'items' => [
                    ['name' => 'Lemoniada domowa', 'price' => 14.00, 'production_area' => MenuItem::AREA_BAR],
                    ['name' => 'Kawa czarna', 'price' => 12.00, 'production_area' => MenuItem::AREA_BAR],
                    ['name' => 'Herbata', 'price' => 10.00, 'production_area' => MenuItem::AREA_BAR],
                ],
            ],
        ];

        foreach ($categories as $categoryData) {
            $category = MenuCategory::updateOrCreate(
                ['name' => $categoryData['name']],
                [
                    'sort_order' => $categoryData['sort_order'],
                    'is_active' => true,
                ],
            );

            foreach ($categoryData['items'] as $itemData) {
                MenuItem::updateOrCreate(
                    [
                        'menu_category_id' => $category->id,
                        'name' => $itemData['name'],
                    ],
                    [
                        'description' => null,
                        'price' => $itemData['price'],
                        'production_area' => $itemData['production_area'],
                        'available' => true,
                    ],
                );
            }
        }
    }
}
