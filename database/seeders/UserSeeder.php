<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $legacyEmailMap = [
            'administrator.systemu@example.com' => 'admin@example.com',
            'monika.majewska@example.com' => 'manager@example.com',
            'michal.nowak@example.com' => 'kelner@example.com',
            'agata.kowalska@example.com' => 'kelner1@example.com',
            'jacek.wisniewski@example.com' => 'kelner2@example.com',
            'marta.zielinska@example.com' => 'kelner3@example.com',
            'tomasz.wojcik@example.com' => 'kuchnia@example.com',
            'pawel.baran@example.com' => 'bar@example.com',
        ];

        foreach ($legacyEmailMap as $legacyEmail => $currentEmail) {
            if (! User::where('email', $currentEmail)->exists()) {
                User::where('email', $legacyEmail)->update(['email' => $currentEmail]);
            }
        }

        $users = [
            ['name' => 'Administrator Systemu', 'email' => 'admin@example.com', 'role' => User::ROLE_ADMIN],
            ['name' => 'Monika Majewska', 'email' => 'manager@example.com', 'role' => User::ROLE_MANAGER],
            ['name' => 'Michał Nowak', 'email' => 'kelner@example.com', 'role' => User::ROLE_WAITER],
            ['name' => 'Agata Kowalska', 'email' => 'kelner1@example.com', 'role' => User::ROLE_WAITER],
            ['name' => 'Jacek Wiśniewski', 'email' => 'kelner2@example.com', 'role' => User::ROLE_WAITER],
            ['name' => 'Marta Zielińska', 'email' => 'kelner3@example.com', 'role' => User::ROLE_WAITER],
            ['name' => 'Tomasz Wójcik', 'email' => 'kuchnia@example.com', 'role' => User::ROLE_KITCHEN],
            ['name' => 'Paweł Baran', 'email' => 'bar@example.com', 'role' => User::ROLE_BAR],
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
