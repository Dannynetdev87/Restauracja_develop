<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EmployeeAccountsSeederTest extends TestCase
{
    use DatabaseTransactions;

    public function test_user_seeder_creates_employee_accounts_with_hashed_passwords(): void
    {
        $this->seed(UserSeeder::class);

        $expectedUsers = [
            'admin@example.com' => User::ROLE_ADMIN,
            'manager@example.com' => User::ROLE_MANAGER,
            'kelner@example.com' => User::ROLE_WAITER,
            'kelner1@example.com' => User::ROLE_WAITER,
            'kelner2@example.com' => User::ROLE_WAITER,
            'kelner3@example.com' => User::ROLE_WAITER,
            'kuchnia@example.com' => User::ROLE_KITCHEN,
            'bar@example.com' => User::ROLE_BAR,
        ];

        foreach ($expectedUsers as $email => $role) {
            $user = User::where('email', $email)->first();

            $this->assertNotNull($user, "Missing test user: {$email}");
            $this->assertSame($role, $user->role);
            $this->assertTrue($user->is_active);
            $this->assertNotSame('password', $user->password);
            $this->assertTrue(Hash::check('password', $user->password));
        }

        $this->assertGreaterThan(1, User::where('role', User::ROLE_WAITER)->count());
    }
}
