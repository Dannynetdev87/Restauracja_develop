<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreScheduleRequest;
use App\Http\Requests\UpdateScheduleRequest;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Schedule::class);

        return $this->renderSchedule($request);
    }

    public function store(StoreScheduleRequest $request)
    {
        $schedule = Schedule::create($request->validated());

        return redirect()
            ->route('schedule.index', [
                'view' => 'week',
                'date' => $schedule->date->toDateString(),
            ])
            ->with('success', 'Zmiana została dodana do grafiku.');
    }

    public function edit(Request $request, Schedule $schedule)
    {
        Gate::authorize('update', $schedule);

        if (! $request->filled('date')) {
            $request->merge([
                'date' => $schedule->date->toDateString(),
                'view' => 'week',
            ]);
        }

        return $this->renderSchedule($request, $schedule);
    }

    public function update(UpdateScheduleRequest $request, Schedule $schedule)
    {
        $schedule->update($request->validated());

        return redirect()
            ->route('schedule.index', [
                'view' => 'week',
                'date' => $schedule->date->toDateString(),
            ])
            ->with('success', 'Zmiana w grafiku została zaktualizowana.');
    }

    public function destroy(Schedule $schedule)
    {
        Gate::authorize('delete', $schedule);

        $date = $schedule->date->toDateString();
        $schedule->delete();

        return redirect()
            ->route('schedule.index', [
                'view' => 'week',
                'date' => $date,
            ])
            ->with('success', 'Zmiana została usunięta z grafiku.');
    }

    private function renderSchedule(Request $request, ?Schedule $editingSchedule = null)
    {
        $user = $request->user();
        $isManagerOrAdmin = $user->isManager() || $user->isAdmin();
        $viewMode = $request->query('view') === 'month' ? 'month' : 'week';
        $selectedDate = $this->selectedDate($request);

        if ($viewMode === 'month') {
            $rangeStart = $selectedDate->copy()->startOfMonth()->startOfWeek(CarbonInterface::MONDAY);
            $rangeEnd = $selectedDate->copy()->endOfMonth()->endOfWeek(CarbonInterface::SUNDAY);
        } else {
            $rangeStart = $selectedDate->copy()->startOfWeek(CarbonInterface::MONDAY);
            $rangeEnd = $selectedDate->copy()->endOfWeek(CarbonInterface::SUNDAY);
        }

        $employees = $isManagerOrAdmin
            ? User::query()
                ->where('is_active', true)
                ->orderBy('role')
                ->orderBy('name')
                ->get()
            : collect([$user]);

        $schedules = Schedule::query()
            ->with('user')
            ->whereBetween('date', [$rangeStart->toDateString(), $rangeEnd->toDateString()])
            ->when(! $isManagerOrAdmin, fn ($query) => $query->where('user_id', $user->id))
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        return view('schedule', [
            'viewMode' => $viewMode,
            'selectedDate' => $selectedDate,
            'rangeStart' => $rangeStart,
            'rangeEnd' => $rangeEnd,
            'weekDays' => $this->daysBetween(
                $selectedDate->copy()->startOfWeek(CarbonInterface::MONDAY),
                $selectedDate->copy()->endOfWeek(CarbonInterface::SUNDAY),
            ),
            'monthWeeks' => $this->monthWeeks($rangeStart, $rangeEnd),
            'isManagerOrAdmin' => $isManagerOrAdmin,
            'employees' => $employees,
            'schedulesByUserAndDate' => $schedules->groupBy([
                'user_id',
                fn (Schedule $schedule) => $schedule->date->toDateString(),
            ]),
            'schedulesByDate' => $schedules->groupBy(fn (Schedule $schedule) => $schedule->date->toDateString()),
            'editingSchedule' => $editingSchedule?->load('user'),
        ]);
    }

    private function selectedDate(Request $request): Carbon
    {
        if (! $request->filled('date')) {
            return now();
        }

        try {
            return Carbon::parse($request->query('date'));
        } catch (\Throwable) {
            return now();
        }
    }

    private function daysBetween(Carbon $start, Carbon $end): Collection
    {
        $days = collect();
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $days->push($cursor->copy());
            $cursor->addDay();
        }

        return $days;
    }

    private function monthWeeks(Carbon $rangeStart, Carbon $rangeEnd): Collection
    {
        return $this->daysBetween($rangeStart, $rangeEnd)
            ->chunk(7)
            ->values();
    }
}
