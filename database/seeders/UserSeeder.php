<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Administrator', 'email' => 'admin@example.com', 'role' => User::ROLE_ADMIN],
            ['name' => 'Manager', 'email' => 'manager@example.com', 'role' => User::ROLE_MANAGER],
            ['name' => 'Kelner', 'email' => 'kelner@example.com', 'role' => User::ROLE_WAITER],
            ['name' => 'Kelner Jan', 'email' => 'kelner1@example.com', 'role' => User::ROLE_WAITER],
            ['name' => 'Kelner Anna', 'email' => 'kelner2@example.com', 'role' => User::ROLE_WAITER],
            ['name' => 'Kelner Piotr', 'email' => 'kelner3@example.com', 'role' => User::ROLE_WAITER],
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
