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
                'tables' => [1, 2, 3, 4],
                'legacy_names' => [],
            ],
            'Sala boczna' => [
                'assigned_waiter_id' => $waiters['kelner1@example.com'] ?? null,
                'tables' => [5, 6],
                'legacy_names' => ['Antresola'],
            ],
            'Taras' => [
                'assigned_waiter_id' => $waiters['kelner2@example.com'] ?? null,
                'tables' => [7, 8, 9, 10],
                'legacy_names' => [],
            ],
            'Ogródek' => [
                'assigned_waiter_id' => $waiters['kelner3@example.com'] ?? null,
                'tables' => [11, 12],
                'legacy_names' => [],
            ],
            'Sala rodzinna' => [
                'assigned_waiter_id' => null,
                'tables' => [13, 14, 15],
                'legacy_names' => [],
            ],
        ];

        RestaurantTable::query()
            ->whereIn('number', collect($zones)->flatMap(fn (array $zoneData) => $zoneData['tables'])->all())
            ->update(['zone_id' => null]);

        foreach ($zones as $name => $zoneData) {
            $zone = $this->zoneFor($name, $zoneData['legacy_names']);

            $zone->fill([
                'name' => $name,
                'assigned_waiter_id' => $zoneData['assigned_waiter_id'],
                'is_active' => true,
            ])->save();

            Zone::query()
                ->whereIn('name', $zoneData['legacy_names'])
                ->whereKeyNot($zone->id)
                ->get()
                ->each(function (Zone $legacyZone) use ($zone): void {
                    RestaurantTable::query()
                        ->where('zone_id', $legacyZone->id)
                        ->update(['zone_id' => $zone->id]);

                    $legacyZone->delete();
                });

            RestaurantTable::query()
                ->whereIn('number', $zoneData['tables'])
                ->update(['zone_id' => $zone->id]);
        }
    }

    private function zoneFor(string $name, array $legacyNames): Zone
    {
        $zone = Zone::query()->where('name', $name)->first();

        if ($zone) {
            return $zone;
        }

        return Zone::query()
            ->whereIn('name', $legacyNames)
            ->first()
            ?? new Zone(['name' => $name]);
    }
}
