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
            ['number' => 6, 'seats' => 2, 'status' => RestaurantTable::STATUS_FREE],
            ['number' => 7, 'seats' => 2, 'status' => RestaurantTable::STATUS_OCCUPIED],
            ['number' => 8, 'seats' => 4, 'status' => RestaurantTable::STATUS_FREE],
            ['number' => 9, 'seats' => 4, 'status' => RestaurantTable::STATUS_RESERVED],
            ['number' => 10, 'seats' => 4, 'status' => RestaurantTable::STATUS_FREE],
            ['number' => 11, 'seats' => 6, 'status' => RestaurantTable::STATUS_FREE],
            ['number' => 12, 'seats' => 6, 'status' => RestaurantTable::STATUS_OCCUPIED],
            ['number' => 13, 'seats' => 8, 'status' => RestaurantTable::STATUS_RESERVED],
            ['number' => 14, 'seats' => 10, 'status' => RestaurantTable::STATUS_FREE],
            ['number' => 15, 'seats' => 12, 'status' => RestaurantTable::STATUS_INACTIVE],
        ];

        foreach ($tables as $table) {
            RestaurantTable::updateOrCreate(
                ['number' => $table['number']],
                $table,
            );
        }
    }
}
