<x-app>
    <x-slot:title>Grafik pracy - SmakPrzeszłości</x-slot>

    @php
        $dayNames = [
            1 => 'Poniedziałek',
            2 => 'Wtorek',
            3 => 'Środa',
            4 => 'Czwartek',
            5 => 'Piątek',
            6 => 'Sobota',
            7 => 'Niedziela',
        ];
        $shortDayNames = [
            1 => 'Pon',
            2 => 'Wt',
            3 => 'Śr',
            4 => 'Czw',
            5 => 'Pt',
            6 => 'Sob',
            7 => 'Nd',
        ];
        $stateLabels = [
            \App\Models\Schedule::STATE_PLANNED => 'Zaplanowana',
            \App\Models\Schedule::STATE_RUNNING => 'Trwa',
            \App\Models\Schedule::STATE_FINISHED => 'Zakończona',
        ];
        $stateClasses = [
            \App\Models\Schedule::STATE_PLANNED => 'border-blue-200 bg-blue-50 text-blue-900',
            \App\Models\Schedule::STATE_RUNNING => 'border-green-300 bg-green-50 text-green-900',
            \App\Models\Schedule::STATE_FINISHED => 'border-brand-dark/10 bg-brand-card text-brand-dark',
        ];
        $previousDate = $viewMode === 'month'
            ? $selectedDate->copy()->subMonthNoOverflow()
            : $selectedDate->copy()->subWeek();
        $nextDate = $viewMode === 'month'
            ? $selectedDate->copy()->addMonthNoOverflow()
            : $selectedDate->copy()->addWeek();
        $formSchedule = $editingSchedule;
    @endphp

    <section class="mx-auto w-full max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-col gap-5 border-b border-brand-dark/10 pb-6 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <span class="text-sm font-bold uppercase tracking-wide text-brand-accent">
                    {{ $isManagerOrAdmin ? 'Grafik zespołu' : 'Mój grafik' }}
                </span>
                <h1 class="mt-2 text-3xl font-black text-brand-dark">
                    {{ $isManagerOrAdmin ? 'Kalendarz pracy personelu' : 'Harmonogram pracy' }}
                </h1>
                <p class="mt-2 text-sm text-brand-accent">
                    {{ $viewMode === 'month' ? $selectedDate->translatedFormat('F Y') : $rangeStart->format('d.m.Y').' - '.$rangeEnd->format('d.m.Y') }}
                </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <div class="inline-flex rounded-lg border border-brand-dark/15 bg-white p-1">
                    <a href="{{ route('schedule.index', ['view' => 'week', 'date' => $selectedDate->toDateString()]) }}"
                       class="rounded-md px-4 py-2 text-sm font-bold {{ $viewMode === 'week' ? 'bg-brand-dark text-brand-light' : 'text-brand-dark hover:bg-brand-light' }}">
                        Tydzień
                    </a>
                    <a href="{{ route('schedule.index', ['view' => 'month', 'date' => $selectedDate->toDateString()]) }}"
                       class="rounded-md px-4 py-2 text-sm font-bold {{ $viewMode === 'month' ? 'bg-brand-dark text-brand-light' : 'text-brand-dark hover:bg-brand-light' }}">
                        Miesiąc
                    </a>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('schedule.index', ['view' => $viewMode, 'date' => $previousDate->toDateString()]) }}"
                       class="rounded-md border border-brand-dark/20 bg-white px-3 py-2 text-sm font-bold text-brand-dark hover:bg-brand-light">
                        Poprzedni
                    </a>
                    <form method="GET" action="{{ route('schedule.index') }}" class="flex items-center gap-2">
                        <input type="hidden" name="view" value="{{ $viewMode }}">
                        <input type="date" name="date" value="{{ $selectedDate->toDateString() }}"
                               class="w-36 rounded-md border border-brand-dark/20 bg-white px-3 py-2 text-sm text-brand-dark focus:border-brand-dark focus:outline-none">
                        <button type="submit" class="rounded-md bg-brand-dark px-3 py-2 text-sm font-bold text-brand-light hover:bg-brand-accent">
                            Pokaż
                        </button>
                    </form>
                    <a href="{{ route('schedule.index', ['view' => $viewMode, 'date' => $nextDate->toDateString()]) }}"
                       class="rounded-md border border-brand-dark/20 bg-white px-3 py-2 text-sm font-bold text-brand-dark hover:bg-brand-light">
                        Następny
                    </a>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 rounded-lg border border-green-700 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 rounded-lg border border-red-700 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="grid gap-6 {{ $isManagerOrAdmin ? 'xl:grid-cols-[minmax(0,1fr)_340px]' : '' }}">
            <div class="space-y-6">
                @if($viewMode === 'week')
                    @if($isManagerOrAdmin)
                        <div class="overflow-x-auto rounded-lg border border-brand-dark/15 bg-white p-4 shadow-sm">
                            <table class="min-w-[980px] w-full border-collapse text-left">
                                <thead>
                                <tr class="border-b border-brand-dark/10 text-xs uppercase tracking-wide text-brand-accent">
                                    <th class="w-48 px-3 py-4 font-bold">Pracownik</th>
                                    @foreach($weekDays as $day)
                                        <th class="min-w-32 px-2 py-4 text-center {{ $day->isToday() ? 'rounded-t-md bg-brand-light/40 text-brand-dark' : '' }}">
                                            <span class="block">{{ $shortDayNames[$day->dayOfWeekIso] }}</span>
                                            <span class="mt-1 block text-sm font-black text-brand-dark">{{ $day->format('d.m') }}</span>
                                        </th>
                                    @endforeach
                                </tr>
                                </thead>
                                <tbody class="divide-y divide-brand-dark/10">
                                @foreach($employees as $employee)
                                    <tr>
                                        <td class="px-3 py-4 align-top">
                                            <strong class="block text-sm font-black text-brand-dark">{{ $employee->name }}</strong>
                                            <span class="mt-1 inline-block rounded-md bg-brand-light px-2 py-1 text-xs font-bold uppercase text-brand-dark">
                                                {{ $employee->role }}
                                            </span>
                                        </td>
                                        @foreach($weekDays as $day)
                                            @php
                                                $dateKey = $day->toDateString();
                                                $dayShifts = $schedulesByUserAndDate[$employee->id][$dateKey] ?? collect();
                                            @endphp
                                            <td class="h-full align-top {{ $day->isToday() ? 'bg-brand-light/20' : '' }} p-2">
                                                <div class="flex min-h-24 flex-col gap-2">
                                                    @forelse($dayShifts as $shift)
                                                        @php
                                                            $state = $shift->state();
                                                        @endphp
                                                        <div class="rounded-md border px-3 py-2 text-xs {{ $stateClasses[$state] }}">
                                                            <div class="flex items-start justify-between gap-2">
                                                                <div>
                                                                    <strong class="block text-sm">{{ $shift->startsAt() }} - {{ $shift->endsAt() }}</strong>
                                                                    <span class="mt-1 block font-bold">{{ $stateLabels[$state] }}</span>
                                                                </div>
                                                                <div class="flex shrink-0 gap-1">
                                                                    <a href="{{ route('manager.schedules.edit', ['schedule' => $shift, 'view' => $viewMode, 'date' => $selectedDate->toDateString()]) }}"
                                                                       class="rounded bg-white/80 px-2 py-1 font-black text-brand-dark hover:bg-white">
                                                                        Edytuj
                                                                    </a>
                                                                    <form method="POST" action="{{ route('manager.schedules.destroy', $shift) }}" onsubmit="return confirm('Usunąć tę zmianę?');">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" class="rounded bg-white/80 px-2 py-1 font-black text-red-700 hover:bg-white">
                                                                            Usuń
                                                                        </button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                            @if($shift->zone)
                                                                <p class="mt-2 text-xs font-bold uppercase tracking-wide text-brand-accent">
                                                                    Strefa: {{ $shift->zone->name }}
                                                                </p>
                                                            @endif
                                                            @if($shift->notes)
                                                                <p class="mt-2 truncate">{{ $shift->notes }}</p>
                                                            @endif
                                                        </div>
                                                    @empty
                                                        <div class="min-h-20 flex-grow rounded-md border border-dashed border-brand-dark/10"></div>
                                                    @endforelse
                                                </div>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($weekDays as $day)
                                @php
                                    $dateKey = $day->toDateString();
                                    $dayShifts = $schedulesByDate[$dateKey] ?? collect();
                                @endphp
                                <article class="rounded-lg border bg-white p-5 shadow-sm {{ $day->isToday() ? 'border-brand-dark ring-1 ring-brand-dark/20' : 'border-brand-dark/15' }}">
                                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                        <div>
                                            <span class="text-xs font-bold uppercase tracking-wide text-brand-accent">{{ $dayNames[$day->dayOfWeekIso] }}</span>
                                            <h2 class="mt-1 text-2xl font-black text-brand-dark">{{ $day->format('d.m.Y') }}</h2>
                                        </div>
                                        @if($day->isToday())
                                            <span class="w-fit rounded-md bg-brand-dark px-3 py-1 text-xs font-bold uppercase text-brand-light">Dzisiaj</span>
                                        @endif
                                    </div>

                                    <div class="mt-4 grid gap-3 md:grid-cols-2">
                                        @forelse($dayShifts as $shift)
                                            @php
                                                $state = $shift->state();
                                            @endphp
                                            <div class="rounded-md border px-4 py-3 {{ $stateClasses[$state] }}">
                                                <span class="block text-xs font-bold uppercase">{{ $stateLabels[$state] }}</span>
                                                <strong class="mt-1 block text-xl">{{ $shift->startsAt() }} - {{ $shift->endsAt() }}</strong>
                                                @if($shift->zone)
                                                    <p class="mt-2 text-xs font-bold uppercase tracking-wide text-brand-accent">
                                                        Strefa: {{ $shift->zone->name }}
                                                    </p>
                                                @endif
                                                @if($shift->notes)
                                                    <p class="mt-2 text-sm">{{ $shift->notes }}</p>
                                                @endif
                                            </div>
                                        @empty
                                            <div class="rounded-md border border-dashed border-brand-dark/20 bg-brand-light/30 px-4 py-6 text-sm font-semibold text-brand-accent">
                                                Brak zaplanowanych godzin.
                                            </div>
                                        @endforelse
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                @else
                    <div class="overflow-x-auto rounded-lg border border-brand-dark/15 bg-white p-4 shadow-sm">
                        <div class="grid min-w-[760px] grid-cols-7 gap-px overflow-hidden rounded-lg border border-brand-dark/10 bg-brand-dark/10">
                            @foreach([1, 2, 3, 4, 5, 6, 7] as $dayNumber)
                                <div class="bg-brand-light px-3 py-2 text-center text-xs font-black uppercase text-brand-dark">
                                    {{ $shortDayNames[$dayNumber] }}
                                </div>
                            @endforeach

                            @foreach($monthWeeks as $week)
                                @foreach($week as $day)
                                    @php
                                        $dateKey = $day->toDateString();
                                        $dayShifts = $schedulesByDate[$dateKey] ?? collect();
                                    @endphp
                                    <div class="min-h-36 bg-white p-3 {{ $day->month !== $selectedDate->month ? 'opacity-50' : '' }} {{ $day->isToday() ? 'ring-2 ring-inset ring-brand-dark' : '' }}">
                                        <div class="mb-2 flex items-center justify-between gap-2">
                                            <span class="font-black text-brand-dark">{{ $day->format('d') }}</span>
                                            @if($dayShifts->isNotEmpty())
                                                <span class="rounded-md bg-brand-light px-2 py-1 text-[11px] font-bold text-brand-dark">
                                                    {{ $dayShifts->count() }}
                                                </span>
                                            @endif
                                        </div>

                                        <div class="space-y-2">
                                            @forelse($dayShifts->take(4) as $shift)
                                                @php
                                                    $state = $shift->state();
                                                @endphp
                                                <div class="rounded-md border px-2 py-1 text-xs {{ $stateClasses[$state] }}">
                                                    @if($isManagerOrAdmin)
                                                        <strong class="block truncate">{{ $shift->user->name }}</strong>
                                                    @endif
                                                    <span>{{ $shift->startsAt() }} - {{ $shift->endsAt() }}</span>
                                                    @if($shift->zone)
                                                        <span class="block truncate font-bold text-brand-accent">
                                                            {{ $shift->zone->name }}
                                                        </span>
                                                    @endif
                                                </div>
                                            @empty
                                                <span class="text-xs text-brand-accent">Brak zmian</span>
                                            @endforelse
                                            @if($dayShifts->count() > 4)
                                                <span class="block text-xs font-bold text-brand-accent">+ {{ $dayShifts->count() - 4 }} więcej</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            @if($isManagerOrAdmin)
                <aside class="h-fit rounded-lg border border-brand-dark/15 bg-white p-5 shadow-sm xl:sticky xl:top-28">
                    <span class="text-sm font-bold uppercase tracking-wide text-brand-accent">
                        {{ $formSchedule ? 'Edycja zmiany' : 'Nowa zmiana' }}
                    </span>
                    <h2 class="mt-2 text-2xl font-black text-brand-dark">
                        {{ $formSchedule ? 'Aktualizuj grafik' : 'Dodaj dyżur' }}
                    </h2>

                    <form method="POST"
                          action="{{ $formSchedule ? route('manager.schedules.update', $formSchedule) : route('manager.schedules.store') }}"
                          class="mt-6 space-y-4">
                        @csrf
                        @if($formSchedule)
                            @method('PUT')
                        @endif

                        <div>
                            <label for="user_id" class="block text-sm font-bold text-brand-dark">Pracownik</label>
                            <select id="user_id" name="user_id" required class="mt-1 w-full rounded-md border border-brand-dark/20 bg-white px-3 py-2 text-sm text-brand-dark focus:border-brand-dark focus:outline-none">
                                <option value="">Wybierz pracownika</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}" {{ (int) old('user_id', $formSchedule?->user_id) === $employee->id ? 'selected' : '' }}>
                                        {{ $employee->name }} ({{ $employee->role }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="date" class="block text-sm font-bold text-brand-dark">Data</label>
                            <input id="date" name="date" type="date" required
                                   value="{{ old('date', $formSchedule?->date?->toDateString()) }}"
                                   class="mt-1 w-full rounded-md border border-brand-dark/20 px-3 py-2 text-sm text-brand-dark focus:border-brand-dark focus:outline-none">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="start_time" class="block text-sm font-bold text-brand-dark">Od</label>
                                <input id="start_time" name="start_time" type="time" required
                                       value="{{ old('start_time', $formSchedule?->startsAt()) }}"
                                       class="mt-1 w-full rounded-md border border-brand-dark/20 px-3 py-2 text-sm text-brand-dark focus:border-brand-dark focus:outline-none">
                            </div>
                            <div>
                                <label for="end_time" class="block text-sm font-bold text-brand-dark">Do</label>
                                <input id="end_time" name="end_time" type="time" required
                                       value="{{ old('end_time', $formSchedule?->endsAt()) }}"
                                       class="mt-1 w-full rounded-md border border-brand-dark/20 px-3 py-2 text-sm text-brand-dark focus:border-brand-dark focus:outline-none">
                            </div>
                        </div>

                        <div>
                            <label for="zone_id" class="block text-sm font-bold text-brand-dark">Strefa</label>
                            <select id="zone_id" name="zone_id" class="mt-1 w-full rounded-md border border-brand-dark/20 bg-white px-3 py-2 text-sm text-brand-dark focus:border-brand-dark focus:outline-none">
                                <option value="">Bez strefy</option>
                                @foreach($zones as $zone)
                                    <option value="{{ $zone->id }}" {{ (int) old('zone_id', $formSchedule?->zone_id) === $zone->id ? 'selected' : '' }}>
                                        {{ $zone->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="notes" class="block text-sm font-bold text-brand-dark">Notatka</label>
                            <input id="notes" name="notes" type="text" maxlength="255"
                                   value="{{ old('notes', $formSchedule?->notes) }}"
                                   placeholder="np. Sala główna, zmiana poranna"
                                   class="mt-1 w-full rounded-md border border-brand-dark/20 px-3 py-2 text-sm text-brand-dark focus:border-brand-dark focus:outline-none">
                        </div>

                        <button type="submit" class="w-full rounded-md bg-brand-dark px-4 py-3 text-sm font-bold text-brand-light hover:bg-brand-accent">
                            {{ $formSchedule ? 'Zapisz zmiany' : 'Dodaj zmianę' }}
                        </button>

                        @if($formSchedule)
                            <a href="{{ route('schedule.index', ['view' => 'week', 'date' => $formSchedule->date->toDateString()]) }}"
                               class="block w-full rounded-md border border-brand-dark/20 bg-white px-4 py-3 text-center text-sm font-bold text-brand-dark hover:bg-brand-light">
                                Anuluj edycję
                            </a>
                        @endif
                    </form>
                </aside>
            @endif
        </div>
    </section>
</x-app>
