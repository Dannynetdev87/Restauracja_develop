<?php

namespace Tests\Feature;

use App\Models\RestaurantTable;
use App\Models\TableReport;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TableReportTest extends TestCase
{
    use DatabaseTransactions;

    // Kelner może zgłosić problem dla przypisanego stolika
    public function test_waiter_can_report_problem_for_assigned_table(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $table = RestaurantTable::create([
            'number' => 801,
            'seats' => 4,
            'status' => RestaurantTable::STATUS_FREE,
            'assigned_waiter_id' => $waiter->id,
        ]);

        $this
            ->actingAs($waiter)
            ->post(route('waiter.tables.report', $table), [
                'type' => 'brudny stolik',
                'message' => 'Stolik wymaga natychmiastowego wyczyszczenia.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Zgłoszenie zostało wysłane do managera.');

        $this->assertDatabaseHas('table_reports', [
            'restaurant_table_id' => $table->id,
            'reported_by' => $waiter->id,
            'type' => 'brudny stolik',
            'message' => 'Stolik wymaga natychmiastowego wyczyszczenia.',
            'status' => 'open',
        ]);
    }

    // Kelner może zgłosić problem bez opisu (pole message jest opcjonalne)
    public function test_waiter_can_report_problem_without_message(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $table = RestaurantTable::create([
            'number' => 802,
            'seats' => 4,
            'status' => RestaurantTable::STATUS_FREE,
            'assigned_waiter_id' => $waiter->id,
        ]);

        $this
            ->actingAs($waiter)
            ->post(route('waiter.tables.report', $table), [
                'type' => 'brak sztućców',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Zgłoszenie zostało wysłane do managera.');

        $this->assertDatabaseHas('table_reports', [
            'restaurant_table_id' => $table->id,
            'reported_by' => $waiter->id,
            'type' => 'brak sztućców',
            'message' => null,
            'status' => 'open',
        ]);
    }

    // Kelner nie może zgłosić problemu dla cudzego stolika
    public function test_waiter_cannot_report_problem_for_other_waiters_table(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $otherWaiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $table = RestaurantTable::create([
            'number' => 803,
            'seats' => 4,
            'status' => RestaurantTable::STATUS_FREE,
            'assigned_waiter_id' => $otherWaiter->id,
        ]);

        $this
            ->actingAs($waiter)
            ->post(route('waiter.tables.report', $table), [
                'type' => 'brudny stolik',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('table_reports', [
            'restaurant_table_id' => $table->id,
            'reported_by' => $waiter->id,
        ]);
    }

    // Kelner nie może zgłosić problemu bez podania typu
    public function test_waiter_cannot_report_problem_without_type(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $table = RestaurantTable::create([
            'number' => 804,
            'seats' => 4,
            'status' => RestaurantTable::STATUS_FREE,
            'assigned_waiter_id' => $waiter->id,
        ]);

        $this
            ->actingAs($waiter)
            ->post(route('waiter.tables.report', $table), [
                'type' => '',
            ])
            ->assertSessionHasErrors('type');

        $this->assertDatabaseMissing('table_reports', [
            'restaurant_table_id' => $table->id,
            'reported_by' => $waiter->id,
        ]);
    }

    // Manager widzi otwarte zgłoszenia na dashboardzie
    public function test_manager_can_see_open_reports_on_dashboard(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $table = RestaurantTable::create([
            'number' => 805,
            'seats' => 4,
            'status' => RestaurantTable::STATUS_FREE,
            'assigned_waiter_id' => $waiter->id,
        ]);

        TableReport::create([
            'restaurant_table_id' => $table->id,
            'reported_by' => $waiter->id,
            'type' => 'potrzebna pomoc',
            'message' => 'Proszę o pomoc przy stoliku.',
            'status' => 'open',
        ]);

        $this
            ->actingAs($manager)
            ->get(route('manager.dashboard'))
            ->assertOk()
            ->assertSee('Problemy przy stolikach')
            ->assertSee('Stolik #805')
            ->assertSee('potrzebna pomoc')
            ->assertSee('Proszę o pomoc przy stoliku.');
    }

    // Manager może oznaczyć zgłoszenie jako rozwiązane
    public function test_manager_can_resolve_report(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $table = RestaurantTable::create([
            'number' => 806,
            'seats' => 4,
            'status' => RestaurantTable::STATUS_FREE,
            'assigned_waiter_id' => $waiter->id,
        ]);

        $report = TableReport::create([
            'restaurant_table_id' => $table->id,
            'reported_by' => $waiter->id,
            'type' => 'brudny stolik',
            'message' => null,
            'status' => 'open',
        ]);

        $this
            ->actingAs($manager)
            ->patch(route('manager.reports.resolve', $report))
            ->assertRedirect()
            ->assertSessionHas('success', 'Zgłoszenie zostało oznaczone jako rozwiązane.');

        $this->assertDatabaseHas('table_reports', [
            'id' => $report->id,
            'status' => 'resolved',
            'resolved_by' => $manager->id,
        ]);
    }

    // Admin może oznaczyć zgłoszenie jako rozwiązane
    public function test_admin_can_resolve_report(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $table = RestaurantTable::create([
            'number' => 807,
            'seats' => 4,
            'status' => RestaurantTable::STATUS_FREE,
            'assigned_waiter_id' => $waiter->id,
        ]);

        $report = TableReport::create([
            'restaurant_table_id' => $table->id,
            'reported_by' => $waiter->id,
            'type' => 'inne',
            'message' => null,
            'status' => 'open',
        ]);

        $this
            ->actingAs($admin)
            ->patch(route('manager.reports.resolve', $report))
            ->assertRedirect()
            ->assertSessionHas('success', 'Zgłoszenie zostało oznaczone jako rozwiązane.');

        $this->assertDatabaseHas('table_reports', [
            'id' => $report->id,
            'status' => 'resolved',
            'resolved_by' => $admin->id,
        ]);
    }

    // Kelner nie może oznaczyć zgłoszenia jako rozwiązanego
    public function test_waiter_cannot_resolve_report(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $table = RestaurantTable::create([
            'number' => 808,
            'seats' => 4,
            'status' => RestaurantTable::STATUS_FREE,
            'assigned_waiter_id' => $waiter->id,
        ]);

        $report = TableReport::create([
            'restaurant_table_id' => $table->id,
            'reported_by' => $waiter->id,
            'type' => 'brudny stolik',
            'message' => null,
            'status' => 'open',
        ]);

        $this
            ->actingAs($waiter)
            ->patch(route('manager.reports.resolve', $report))
            ->assertForbidden();

        $this->assertDatabaseHas('table_reports', [
            'id' => $report->id,
            'status' => 'open',
        ]);
    }

    // Kuchnia nie może oznaczyć zgłoszenia jako rozwiązanego
    public function test_kitchen_cannot_resolve_report(): void
    {
        $kitchen = User::factory()->create(['role' => User::ROLE_KITCHEN]);
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $table = RestaurantTable::create([
            'number' => 809,
            'seats' => 4,
            'status' => RestaurantTable::STATUS_FREE,
            'assigned_waiter_id' => $waiter->id,
        ]);

        $report = TableReport::create([
            'restaurant_table_id' => $table->id,
            'reported_by' => $waiter->id,
            'type' => 'brudny stolik',
            'message' => null,
            'status' => 'open',
        ]);

        $this
            ->actingAs($kitchen)
            ->patch(route('manager.reports.resolve', $report))
            ->assertForbidden();

        $this->assertDatabaseHas('table_reports', [
            'id' => $report->id,
            'status' => 'open',
        ]);
    }

    // Bar nie może oznaczyć zgłoszenia jako rozwiązanego
    public function test_bar_cannot_resolve_report(): void
    {
        $bar = User::factory()->create(['role' => User::ROLE_BAR]);
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $table = RestaurantTable::create([
            'number' => 810,
            'seats' => 4,
            'status' => RestaurantTable::STATUS_FREE,
            'assigned_waiter_id' => $waiter->id,
        ]);

        $report = TableReport::create([
            'restaurant_table_id' => $table->id,
            'reported_by' => $waiter->id,
            'type' => 'brudny stolik',
            'message' => null,
            'status' => 'open',
        ]);

        $this
            ->actingAs($bar)
            ->patch(route('manager.reports.resolve', $report))
            ->assertForbidden();

        $this->assertDatabaseHas('table_reports', [
            'id' => $report->id,
            'status' => 'open',
        ]);
    }

    // Rozwiązane zgłoszenie nie jest widoczne na dashboardzie managera
    public function test_resolved_report_is_not_visible_on_manager_dashboard(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $table = RestaurantTable::create([
            'number' => 811,
            'seats' => 4,
            'status' => RestaurantTable::STATUS_FREE,
            'assigned_waiter_id' => $waiter->id,
        ]);

        TableReport::create([
            'restaurant_table_id' => $table->id,
            'reported_by' => $waiter->id,
            'type' => 'brak sztućców',
            'message' => 'Unikalny opis rozwiązanego zgłoszenia 811.',
            'status' => 'resolved',
            'resolved_by' => $manager->id,
            'resolved_at' => now(),
        ]);

        $this
            ->actingAs($manager)
            ->get(route('manager.dashboard'))
            ->assertOk()
            ->assertDontSee('Unikalny opis rozwiązanego zgłoszenia 811.');
    }
}
