<?php

namespace Database\Seeders;

use App\Models\RestaurantTable;
use Illuminate\Database\Seeder;

class RestaurantTableSeeder extends Seeder
{
    public function run(): void
    {
        $tables = [
            ['number' => 1, 'seats' => 2, 'status' => RestaurantTable::STATUS_FREE, 'assigned_waiter_id' => null],
            ['number' => 2, 'seats' => 4, 'status' => RestaurantTable::STATUS_OCCUPIED, 'assigned_waiter_id' => null],
            ['number' => 3, 'seats' => 4, 'status' => RestaurantTable::STATUS_FREE, 'assigned_waiter_id' => null],
            ['number' => 4, 'seats' => 6, 'status' => RestaurantTable::STATUS_RESERVED, 'assigned_waiter_id' => null],
            ['number' => 5, 'seats' => 8, 'status' => RestaurantTable::STATUS_INACTIVE, 'assigned_waiter_id' => null],
            ['number' => 6, 'seats' => 2, 'status' => RestaurantTable::STATUS_FREE, 'assigned_waiter_id' => null],
            ['number' => 7, 'seats' => 2, 'status' => RestaurantTable::STATUS_OCCUPIED, 'assigned_waiter_id' => null],
            ['number' => 8, 'seats' => 4, 'status' => RestaurantTable::STATUS_FREE, 'assigned_waiter_id' => null],
            ['number' => 9, 'seats' => 4, 'status' => RestaurantTable::STATUS_RESERVED, 'assigned_waiter_id' => null],
            ['number' => 10, 'seats' => 4, 'status' => RestaurantTable::STATUS_FREE, 'assigned_waiter_id' => null],
            ['number' => 11, 'seats' => 6, 'status' => RestaurantTable::STATUS_FREE, 'assigned_waiter_id' => null],
            ['number' => 12, 'seats' => 6, 'status' => RestaurantTable::STATUS_OCCUPIED, 'assigned_waiter_id' => null],
            ['number' => 13, 'seats' => 8, 'status' => RestaurantTable::STATUS_RESERVED, 'assigned_waiter_id' => null],
            ['number' => 14, 'seats' => 10, 'status' => RestaurantTable::STATUS_FREE, 'assigned_waiter_id' => null],
            ['number' => 15, 'seats' => 12, 'status' => RestaurantTable::STATUS_INACTIVE, 'assigned_waiter_id' => null],
        ];

        foreach ($tables as $table) {
            RestaurantTable::updateOrCreate(
                ['number' => $table['number']],
                $table,
            );
        }
    }
}
