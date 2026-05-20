<?php

namespace Database\Seeders;

use App\Models\RestaurantTable;
use Illuminate\Database\Seeder;

class RestaurantTableSeeder extends Seeder
{
    public function run(): void
    {
        $tables = [
            ['number' => 1, 'seats' => 2, 'status' => RestaurantTable::STATUS_FREE],
            ['number' => 2, 'seats' => 4, 'status' => RestaurantTable::STATUS_OCCUPIED],
            ['number' => 3, 'seats' => 4, 'status' => RestaurantTable::STATUS_FREE],
            ['number' => 4, 'seats' => 6, 'status' => RestaurantTable::STATUS_RESERVED],
            ['number' => 5, 'seats' => 8, 'status' => RestaurantTable::STATUS_INACTIVE],
        ];

        foreach ($tables as $table) {
            RestaurantTable::updateOrCreate(
                ['number' => $table['number']],
                $table,
            );
        }
    }
}
