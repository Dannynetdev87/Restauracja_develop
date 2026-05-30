<?php

namespace Database\Seeders;

use App\Models\RestaurantTable;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Database\Seeder;

class ZoneSeeder extends Seeder
{
    public function run(): void
    {
        $waiters = User::query()
            ->whereIn('email', [
                'kelner@example.com',
                'kelner1@example.com',
                'kelner2@example.com',
                'kelner3@example.com',
            ])
            ->pluck('id', 'email');

        $zones = [
            'Sala główna' => [
                'assigned_waiter_id' => $waiters['kelner@example.com'] ?? null,
                'tables' => [1, 2, 3, 4, 5],
            ],
            'Antresola' => [
                'assigned_waiter_id' => $waiters['kelner1@example.com'] ?? null,
                'tables' => [6, 7, 8, 9],
            ],
            'Ogródek' => [
                'assigned_waiter_id' => $waiters['kelner2@example.com'] ?? null,
                'tables' => [10, 11, 12],
            ],
            'Sala rodzinna' => [
                'assigned_waiter_id' => $waiters['kelner3@example.com'] ?? null,
                'tables' => [13, 14, 15],
            ],
        ];

        foreach ($zones as $name => $zoneData) {
            $zone = Zone::updateOrCreate(
                ['name' => $name],
                [
                    'assigned_waiter_id' => $zoneData['assigned_waiter_id'],
                    'is_active' => true,
                ],
            );

            RestaurantTable::query()
                ->whereIn('number', $zoneData['tables'])
                ->update(['zone_id' => $zone->id]);
        }
    }
}
