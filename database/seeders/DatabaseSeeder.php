<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = [
            ['name' => 'Manager', 'email' => 'manager@example.com', 'role' => User::ROLE_MANAGER],
            ['name' => 'Kelner', 'email' => 'kelner@example.com', 'role' => User::ROLE_WAITER],
            ['name' => 'Kuchnia', 'email' => 'kuchnia@example.com', 'role' => User::ROLE_KITCHEN],
            ['name' => 'Bar', 'email' => 'bar@example.com', 'role' => User::ROLE_BAR],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => 'password',
                    'role' => $user['role'],
                    'is_active' => true,
                ],
            );
        }
    }
}
