<?php

namespace Tests\Feature;

use App\Models\Schedule;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ScheduleManagementTest extends TestCase
{
    use DatabaseTransactions;

    public function test_manager_can_see_week_schedule_for_all_employees(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);
        $waiter = User::factory()->create(['name' => 'Kelner Grafikowy', 'role' => User::ROLE_WAITER]);
        $kitchen = User::factory()->create(['name' => 'Kuchnia Grafikowa', 'role' => User::ROLE_KITCHEN]);

        $this->createSchedule($waiter, '2026-05-25', '10:00', '18:00', 'Sala glowna');
        $this->createSchedule($kitchen, '2026-05-26', '12:00', '20:00', 'Zmiana kuchni');

        $this
            ->actingAs($manager)
            ->get(route('schedule.index', ['view' => 'week', 'date' => '2026-05-25']))
            ->assertOk()
            ->assertSee('Kalendarz pracy personelu')
            ->assertSee('Kelner Grafikowy')
            ->assertSee('Kuchnia Grafikowa')
            ->assertSee('10:00 - 18:00')
            ->assertSee('Sala glowna')
            ->assertSee('Dodaj dyżur');
    }

    public function test_manager_schedule_form_excludes_admin_accounts(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);
        $admin = User::factory()->create(['name' => 'Administrator Systemu', 'role' => User::ROLE_ADMIN]);
        $waiter = User::factory()->create(['name' => 'Kelner Operacyjny', 'role' => User::ROLE_WAITER]);

        $this->createSchedule($admin, '2026-05-25', '08:00', '16:00', 'Zmiana techniczna');
        $this->createSchedule($waiter, '2026-05-25', '10:00', '18:00', 'Zmiana sali');

        $this
            ->actingAs($manager)
            ->get(route('schedule.index', ['view' => 'week', 'date' => '2026-05-25']))
            ->assertOk()
            ->assertSee('Kelner Operacyjny')
            ->assertSee('Zmiana sali')
            ->assertDontSee('Administrator Systemu')
            ->assertDontSee('Zmiana techniczna');
    }

    public function test_employee_can_only_see_own_read_only_schedule(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $otherWaiter = User::factory()->create(['role' => User::ROLE_WAITER]);

        $this->createSchedule($waiter, '2026-05-25', '09:00', '15:00', 'Moja zmiana');
        $this->createSchedule($otherWaiter, '2026-05-25', '16:00', '22:00', 'Cudza zmiana');

        $this
            ->actingAs($waiter)
            ->get(route('schedule.index', ['view' => 'week', 'date' => '2026-05-25']))
            ->assertOk()
            ->assertSee('Harmonogram pracy')
            ->assertSee('09:00 - 15:00')
            ->assertSee('Moja zmiana')
            ->assertDontSee('Cudza zmiana')
            ->assertDontSee('Dodaj dyżur')
            ->assertDontSee('Edytuj')
            ->assertDontSee('Usuń');
    }

    public function test_month_view_shows_schedule_entries(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);
        $waiter = User::factory()->create(['name' => 'Pracownik Miesiaca', 'role' => User::ROLE_WAITER]);

        $this->createSchedule($waiter, '2026-05-25', '11:00', '19:00', 'Zmiana miesieczna');

        $this
            ->actingAs($manager)
            ->get(route('schedule.index', ['view' => 'month', 'date' => '2026-05-01']))
            ->assertOk()
            ->assertSee('Miesiąc')
            ->assertSee('Pracownik Miesiaca')
            ->assertSee('11:00 - 19:00');
    }

    public function test_manager_can_create_schedule(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);

        $this
            ->actingAs($manager)
            ->post(route('manager.schedules.store'), [
                'user_id' => $waiter->id,
                'date' => '2026-05-25',
                'start_time' => '10:00',
                'end_time' => '18:00',
                'notes' => 'Sala A',
            ])
            ->assertRedirect(route('schedule.index', ['view' => 'week', 'date' => '2026-05-25']));

        $this->assertDatabaseHas('schedules', [
            'user_id' => $waiter->id,
            'date' => '2026-05-25',
            'start_time' => '10:00',
            'end_time' => '18:00',
            'notes' => 'Sala A',
        ]);
    }

    public function test_manager_can_create_schedule_with_zone(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $zone = Zone::create(['name' => 'Sala Testowa', 'is_active' => true]);

        $this
            ->actingAs($manager)
            ->post(route('manager.schedules.store'), [
                'user_id' => $waiter->id,
                'zone_id' => $zone->id,
                'date' => '2026-05-25',
                'start_time' => '10:00',
                'end_time' => '18:00',
                'notes' => 'Dyżur w strefie',
            ])
            ->assertRedirect(route('schedule.index', ['view' => 'week', 'date' => '2026-05-25']));

        $this->assertDatabaseHas('schedules', [
            'user_id' => $waiter->id,
            'zone_id' => $zone->id,
            'date' => '2026-05-25',
            'start_time' => '10:00',
            'end_time' => '18:00',
        ]);
    }

    public function test_manager_cannot_create_schedule_with_missing_zone(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);

        $this
            ->actingAs($manager)
            ->post(route('manager.schedules.store'), [
                'user_id' => $waiter->id,
                'zone_id' => 999999,
                'date' => '2026-05-25',
                'start_time' => '10:00',
                'end_time' => '18:00',
            ])
            ->assertSessionHasErrors('zone_id');
    }

    public function test_manager_cannot_create_schedule_for_admin_account(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this
            ->actingAs($manager)
            ->post(route('manager.schedules.store'), [
                'user_id' => $admin->id,
                'date' => '2026-05-25',
                'start_time' => '10:00',
                'end_time' => '18:00',
                'notes' => 'Niepoprawny dyzur admina',
            ])
            ->assertSessionHasErrors('user_id');

        $this->assertDatabaseMissing('schedules', [
            'user_id' => $admin->id,
            'date' => '2026-05-25',
        ]);
    }

    public function test_manager_can_update_schedule(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $schedule = $this->createSchedule($waiter, '2026-05-25', '10:00', '18:00', 'Sala A');

        $this
            ->actingAs($manager)
            ->put(route('manager.schedules.update', $schedule), [
                'user_id' => $waiter->id,
                'date' => '2026-05-26',
                'start_time' => '12:00',
                'end_time' => '20:00',
                'notes' => 'Sala B',
            ])
            ->assertRedirect(route('schedule.index', ['view' => 'week', 'date' => '2026-05-26']));

        $this->assertDatabaseHas('schedules', [
            'id' => $schedule->id,
            'date' => '2026-05-26',
            'start_time' => '12:00',
            'end_time' => '20:00',
            'notes' => 'Sala B',
        ]);
    }

    public function test_manager_can_update_schedule_zone(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $oldZone = Zone::create(['name' => 'Sala Stara', 'is_active' => true]);
        $newZone = Zone::create(['name' => 'Sala Nowa', 'is_active' => true]);
        $schedule = $this->createSchedule($waiter, '2026-05-25', '10:00', '18:00', 'Sala A', $oldZone);

        $this
            ->actingAs($manager)
            ->put(route('manager.schedules.update', $schedule), [
                'user_id' => $waiter->id,
                'zone_id' => $newZone->id,
                'date' => '2026-05-25',
                'start_time' => '10:00',
                'end_time' => '18:00',
                'notes' => 'Sala A',
            ])
            ->assertRedirect(route('schedule.index', ['view' => 'week', 'date' => '2026-05-25']));

        $this->assertDatabaseHas('schedules', [
            'id' => $schedule->id,
            'zone_id' => $newZone->id,
        ]);
    }

    public function test_schedule_view_shows_zone_name(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $zone = Zone::create(['name' => 'Taras Testowy', 'is_active' => true]);
        $this->createSchedule($waiter, '2026-05-25', '10:00', '18:00', null, $zone);

        $this
            ->actingAs($manager)
            ->get(route('schedule.index', ['view' => 'week', 'date' => '2026-05-25']))
            ->assertOk()
            ->assertSee('Taras Testowy');
    }

    public function test_deleting_zone_keeps_schedule_and_clears_zone_id(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $zone = Zone::create(['name' => 'Strefa Do Usuniecia', 'is_active' => true]);
        $schedule = $this->createSchedule($waiter, '2026-05-25', '10:00', '18:00', null, $zone);

        $zone->delete();

        $schedule->refresh();

        $this->assertDatabaseHas('schedules', [
            'id' => $schedule->id,
        ]);
        $this->assertNull($schedule->zone_id);
    }

    public function test_manager_can_delete_schedule(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $schedule = $this->createSchedule($waiter, '2026-05-25', '10:00', '18:00');

        $this
            ->actingAs($manager)
            ->delete(route('manager.schedules.destroy', $schedule))
            ->assertRedirect(route('schedule.index', ['view' => 'week', 'date' => '2026-05-25']));

        $this->assertDatabaseMissing('schedules', [
            'id' => $schedule->id,
        ]);
    }

    public function test_employee_cannot_create_schedule(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);

        $this
            ->actingAs($waiter)
            ->post(route('manager.schedules.store'), [
                'user_id' => $waiter->id,
                'date' => '2026-05-25',
                'start_time' => '10:00',
                'end_time' => '18:00',
            ])
            ->assertForbidden();
    }

    public function test_schedule_cannot_overlap_for_same_employee(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $this->createSchedule($waiter, '2026-05-25', '10:00', '18:00');

        $this
            ->actingAs($manager)
            ->post(route('manager.schedules.store'), [
                'user_id' => $waiter->id,
                'date' => '2026-05-25',
                'start_time' => '17:00',
                'end_time' => '21:00',
            ])
            ->assertSessionHasErrors('start_time');

        $this->assertSame(1, Schedule::where('user_id', $waiter->id)->count());
    }

    public function test_schedule_end_time_must_be_after_start_time(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);

        $this
            ->actingAs($manager)
            ->post(route('manager.schedules.store'), [
                'user_id' => $waiter->id,
                'date' => '2026-05-25',
                'start_time' => '18:00',
                'end_time' => '10:00',
            ])
            ->assertSessionHasErrors('end_time');
    }

    private function createSchedule(
        User $user,
        string $date,
        string $startTime,
        string $endTime,
        ?string $notes = null,
        ?Zone $zone = null,
    ): Schedule {
        return Schedule::create([
            'user_id' => $user->id,
            'zone_id' => $zone?->id,
            'date' => $date,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'notes' => $notes,
        ]);
    }
}
