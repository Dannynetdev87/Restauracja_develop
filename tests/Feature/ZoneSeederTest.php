<?php

namespace Tests\Feature;

use App\Models\RestaurantTable;
use App\Models\Zone;
use Database\Seeders\RestaurantTableSeeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\ZoneSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ZoneSeederTest extends TestCase
{
    use DatabaseTransactions;

    public function test_zone_seeder_assigns_example_tables_to_restaurant_zones(): void
    {
        $this->seed(UserSeeder::class);
        $this->seed(RestaurantTableSeeder::class);

        Zone::create(['name' => 'Antresola']);

        $this->seed(ZoneSeeder::class);
        $this->seed(ZoneSeeder::class);

        $expectedAssignments = [
            'Sala główna' => [1, 2, 3, 4],
            'Sala boczna' => [5, 6],
            'Taras' => [7, 8, 9, 10],
            'Ogródek' => [11, 12],
            'Sala rodzinna' => [13, 14, 15],
        ];

        foreach ($expectedAssignments as $zoneName => $tableNumbers) {
            $zone = Zone::where('name', $zoneName)->first();

            $this->assertNotNull($zone, "Missing seeded zone: {$zoneName}");

            foreach ($tableNumbers as $tableNumber) {
                $this->assertSame(
                    $zone->id,
                    RestaurantTable::where('number', $tableNumber)->value('zone_id'),
                    "Table {$tableNumber} should be assigned to {$zoneName}.",
                );
            }

            $this->assertSame(1, Zone::where('name', $zoneName)->count());
        }

        $this->assertDatabaseMissing('zones', [
            'name' => 'Antresola',
        ]);
    }
}
