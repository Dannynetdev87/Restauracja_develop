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
                    ['name' => 'Rosol z wiejskiej kury z makaronem', 'price' => 30.99, 'production_area' => MenuItem::AREA_KITCHEN],
                    ['name' => 'Barszcz czerwony z ziemniaczanym puree', 'price' => 34.99, 'production_area' => MenuItem::AREA_KITCHEN],
                    ['name' => 'Zur na domowym zakwasie z kielbasa i jajkiem', 'price' => 38.99, 'production_area' => MenuItem::AREA_KITCHEN],
                    ['name' => 'Tradycyjna zupa pomidorowa z makaronem', 'price' => 24.99, 'production_area' => MenuItem::AREA_KITCHEN],
                    ['name' => 'Flaki po warszawsku', 'price' => 38.99, 'production_area' => MenuItem::AREA_KITCHEN],
                ],
            ],
            [
                'name' => 'Przekaski',
                'sort_order' => 15,
                'items' => [
                    ['name' => 'Tatar z dojrzewajacej wolowiny', 'price' => 66.99, 'production_area' => MenuItem::AREA_KITCHEN],
                    ['name' => 'Sledz w oleju lnianym z cebula', 'price' => 40.99, 'production_area' => MenuItem::AREA_KITCHEN],
                    ['name' => 'Placki ziemniaczane ze smietana', 'price' => 31.99, 'production_area' => MenuItem::AREA_KITCHEN],
                    ['name' => 'Deska tradycyjnych polskich wedlin', 'price' => 44.99, 'production_area' => MenuItem::AREA_KITCHEN],
                    ['name' => 'Bigos staropolski podawany z chlebem', 'price' => 47.99, 'production_area' => MenuItem::AREA_KITCHEN],
                    ['name' => 'Oscypek z grilla z zurawina', 'price' => 28.99, 'production_area' => MenuItem::AREA_KITCHEN],
                    ['name' => 'Carpaccio wolowe z rukola i parmezanem', 'price' => 53.99, 'production_area' => MenuItem::AREA_KITCHEN],
                    ['name' => 'Chrupiące kalmary z sosem aioli', 'price' => 41.99, 'production_area' => MenuItem::AREA_KITCHEN],
                    ['name' => 'Bruschetta z pomidorami i bazylia', 'price' => 25.99, 'production_area' => MenuItem::AREA_KITCHEN],
                ],
            ],
            [
                'name' => 'Dania glowne',
                'sort_order' => 20,
                'items' => [
                    ['name' => 'Kotlet schabowy smazony na smalcu', 'price' => 56.99, 'production_area' => MenuItem::AREA_KITCHEN],
                    ['name' => 'Klasyczny kotlet de volaille', 'price' => 62.99, 'production_area' => MenuItem::AREA_KITCHEN],
                    ['name' => 'Pol kaczki pieczonej z jablkami', 'price' => 96.99, 'production_area' => MenuItem::AREA_KITCHEN],
                    ['name' => 'Golonka glazurowana miodem lipowym', 'price' => 78.99, 'production_area' => MenuItem::AREA_KITCHEN],
                    ['name' => 'Poliki wolowe duszone na wolnym ogniu', 'price' => 84.99, 'production_area' => MenuItem::AREA_KITCHEN],
                    ['name' => 'Stek wolowy z maslem czosnkowym', 'price' => 118.99, 'production_area' => MenuItem::AREA_KITCHEN],
                ],
            ],
            [
                'name' => 'Pierogi',
                'sort_order' => 22,
                'items' => [
                    ['name' => 'Pierogi ruskie ze skwarkami i cebula', 'price' => 36.99, 'production_area' => MenuItem::AREA_KITCHEN],
                    ['name' => 'Pierogi z miesem wolowym', 'price' => 35.99, 'production_area' => MenuItem::AREA_KITCHEN],
                    ['name' => 'Pierogi z kaczka i zurawina', 'price' => 48.99, 'production_area' => MenuItem::AREA_KITCHEN],
                    ['name' => 'Pierogi z kapusta i lesnymi grzybami', 'price' => 30.99, 'production_area' => MenuItem::AREA_KITCHEN],
                ],
            ],
            [
                'name' => 'Pizza',
                'sort_order' => 25,
                'items' => [
                    ['name' => 'Pizza Margherita', 'price' => 31.99, 'production_area' => MenuItem::AREA_KITCHEN],
                    ['name' => 'Pizza Pepperoni', 'price' => 37.99, 'production_area' => MenuItem::AREA_KITCHEN],
                    ['name' => 'Pizza Cztery Sery', 'price' => 41.99, 'production_area' => MenuItem::AREA_KITCHEN],
                    ['name' => 'Pizza Capricciosa', 'price' => 38.99, 'production_area' => MenuItem::AREA_KITCHEN],
                    ['name' => 'Pizza Diavola', 'price' => 40.99, 'production_area' => MenuItem::AREA_KITCHEN],
                    ['name' => 'Pizza Prosciutto e Funghi', 'price' => 39.99, 'production_area' => MenuItem::AREA_KITCHEN],
                    ['name' => 'Pizza Parma z szynka dojrzewajaca', 'price' => 45.99, 'production_area' => MenuItem::AREA_KITCHEN],
                    ['name' => 'Pizza Wegetarianska', 'price' => 36.99, 'production_area' => MenuItem::AREA_KITCHEN],
                    ['name' => 'Pizza BBQ z kurczakiem', 'price' => 42.99, 'production_area' => MenuItem::AREA_KITCHEN],
                ],
            ],
            [
                'name' => 'Desery',
                'sort_order' => 30,
                'items' => [
                    ['name' => 'Szarlotka domowa na cieplo', 'price' => 22.99, 'production_area' => MenuItem::AREA_KITCHEN],
                    ['name' => 'Sernik tradycyjny puszysty', 'price' => 22.99, 'production_area' => MenuItem::AREA_KITCHEN],
                    ['name' => 'Mazurek kajmakowy', 'price' => 22.99, 'production_area' => MenuItem::AREA_KITCHEN],
                    ['name' => 'Tort czekoladowo-pralinowy', 'price' => 26.99, 'production_area' => MenuItem::AREA_KITCHEN],
                    ['name' => 'Fondant czekoladowy z lodami', 'price' => 25.99, 'production_area' => MenuItem::AREA_KITCHEN],
                    ['name' => 'Panna Cotta z musem malinowym', 'price' => 21.99, 'production_area' => MenuItem::AREA_KITCHEN],
                    ['name' => 'Creme Brulee', 'price' => 23.99, 'production_area' => MenuItem::AREA_KITCHEN],
                    ['name' => 'Pucharek lodowy z owocami', 'price' => 24.99, 'production_area' => MenuItem::AREA_KITCHEN],
                ],
            ],
            [
                'name' => 'Napoje bezalkoholowe',
                'sort_order' => 40,
                'items' => [
                    ['name' => 'Lemoniada domowa 0.4L', 'price' => 16.99, 'production_area' => MenuItem::AREA_BAR],
                    ['name' => 'Coca-Cola 0.25L', 'price' => 9.99, 'production_area' => MenuItem::AREA_BAR],
                    ['name' => 'Sprite 0.25L', 'price' => 9.99, 'production_area' => MenuItem::AREA_BAR],
                    ['name' => 'Sok ze swiezych pomaranczy', 'price' => 14.99, 'production_area' => MenuItem::AREA_BAR],
                    ['name' => 'Woda mineralna Perlage 0.3L', 'price' => 10.99, 'production_area' => MenuItem::AREA_BAR],
                    ['name' => 'Espresso', 'price' => 9.99, 'production_area' => MenuItem::AREA_BAR],
                    ['name' => 'Kawa czarna Americano', 'price' => 10.99, 'production_area' => MenuItem::AREA_BAR],
                    ['name' => 'Cappuccino', 'price' => 12.99, 'production_area' => MenuItem::AREA_BAR],
                    ['name' => 'Herbata w dzbanku', 'price' => 14.99, 'production_area' => MenuItem::AREA_BAR],
                ],
            ],
            [
                'name' => 'Alkohole',
                'sort_order' => 50,
                'items' => [
                    ['name' => 'Piwo z nalewaka Okocim 0.5L', 'price' => 13.99, 'production_area' => MenuItem::AREA_BAR],
                    ['name' => 'Piwo pszeniczne 0.5L', 'price' => 15.99, 'production_area' => MenuItem::AREA_BAR],
                    ['name' => 'Piwo IPA rzemieslnicze 0.5L', 'price' => 18.99, 'production_area' => MenuItem::AREA_BAR],
                    ['name' => 'Kieliszek wina domowego 150ml', 'price' => 17.99, 'production_area' => MenuItem::AREA_BAR],
                    ['name' => 'Butelka wina domowego 1L', 'price' => 78.99, 'production_area' => MenuItem::AREA_BAR],
                    ['name' => 'Prosecco kieliszek 100ml', 'price' => 19.99, 'production_area' => MenuItem::AREA_BAR],
                    ['name' => 'Aperol Spritz', 'price' => 31.99, 'production_area' => MenuItem::AREA_BAR],
                    ['name' => 'Mojito', 'price' => 27.99, 'production_area' => MenuItem::AREA_BAR],
                    ['name' => 'Whisky Sour', 'price' => 29.99, 'production_area' => MenuItem::AREA_BAR],
                    ['name' => 'Shot wodki wyborowej 40ml', 'price' => 11.99, 'production_area' => MenuItem::AREA_BAR],
                    ['name' => 'Jagermeister 40ml', 'price' => 15.99, 'production_area' => MenuItem::AREA_BAR],
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
