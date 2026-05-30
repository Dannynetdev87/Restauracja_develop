<x-app>
    <x-slot:title>Zarządzanie stolikami - SmakPrzeszłości</x-slot>

    <section class="w-full max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="flex flex-col gap-2 mb-8">
            <span class="text-sm font-bold uppercase text-brand-accent">Panel managera</span>
            <h1 class="text-3xl font-black text-brand-dark">Zarządzanie stolikami</h1>
            <p class="text-brand-accent max-w-3xl">
                Stolik z historią zamówień nie powinien być usuwany. W takim przypadku ustaw status nieaktywny, aby zachować spójność danych.
                Przypisany kelner decyduje o tym, kto widzi stolik w swoim panelu.
            </p>
        </div>

        @if(session('success'))
            <div class="mb-6 rounded-lg border border-green-700 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 rounded-lg border border-red-700 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 rounded-lg border border-red-700 bg-red-50 px-4 py-3 text-sm text-red-800">
                <strong>Popraw błędy formularza:</strong>
                <ul class="mt-2 list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-[0.8fr_1.2fr]">
            <div class="rounded-lg border border-brand-dark/15 bg-white p-5 shadow-sm">
                <h2 class="text-xl font-black text-brand-dark">Dodaj stolik</h2>

                <form method="POST" action="{{ route('manager.tables.store') }}" class="mt-5 space-y-4">
                    @csrf

                    <div>
                        <label for="number" class="block text-sm font-bold text-brand-dark">Numer stolika</label>
                        <input id="number" name="number" type="number" min="1" max="999" value="{{ old('number') }}" required class="mt-1 w-full rounded-md border border-brand-dark/20 px-3 py-2">
                    </div>

                    <div>
                        <label for="seats" class="block text-sm font-bold text-brand-dark">Liczba miejsc</label>
                        <input id="seats" name="seats" type="number" min="1" max="50" value="{{ old('seats') }}" required class="mt-1 w-full rounded-md border border-brand-dark/20 px-3 py-2">
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-bold text-brand-dark">Status</label>
                        <select id="status" name="status" required class="mt-1 w-full rounded-md border border-brand-dark/20 px-3 py-2">
                            @foreach($statuses as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', 'wolny') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="assigned_waiter_id" class="block text-sm font-bold text-brand-dark">Przypisany kelner</label>
                        <select id="assigned_waiter_id" name="assigned_waiter_id" class="mt-1 w-full rounded-md border border-brand-dark/20 px-3 py-2">
                            <option value="">Bez przypisania</option>
                            @foreach($waiters as $waiter)
                                <option value="{{ $waiter->id }}" @selected((int) old('assigned_waiter_id') === $waiter->id)>
                                    {{ $waiter->name }} ({{ $waiter->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="zone_id" class="block text-sm font-bold text-brand-dark">Strefa</label>
                        <select id="zone_id" name="zone_id" class="mt-1 w-full rounded-md border border-brand-dark/20 px-3 py-2">
                            <option value="">Poza strefą</option>
                            @foreach($zones as $zone)
                                <option value="{{ $zone->id }}" @selected((int) old('zone_id') === $zone->id)>
                                    {{ $zone->name }}{{ $zone->is_active ? '' : ' (nieaktywna)' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="rounded-md bg-brand-dark px-4 py-2 text-sm font-bold text-brand-light hover:bg-brand-accent">
                        Dodaj stolik
                    </button>
                </form>
            </div>

            <div class="rounded-lg border border-brand-dark/15 bg-white p-5 shadow-sm">
                <h2 class="text-xl font-black text-brand-dark">Lista stolików</h2>

                <div class="mt-5 overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="border-b border-brand-dark/10 text-xs uppercase text-brand-accent">
                            <tr>
                                <th class="py-3 pr-4">Numer</th>
                                <th class="py-3 pr-4">Miejsca</th>
                                <th class="py-3 pr-4">Status</th>
                                <th class="py-3 pr-4">Strefa</th>
                                <th class="py-3 pr-4">Kelner</th>
                                <th class="py-3 pr-4">Zamówienia</th>
                                <th class="py-3 pr-4 text-right">Akcje</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-brand-dark/10">
                            @forelse($tables as $table)
                                <tr>
                                    <td class="py-3 pr-4 font-black text-brand-dark">Stolik {{ $table->number }}</td>
                                    <td class="py-3 pr-4">{{ $table->seats }}</td>
                                    <td class="py-3 pr-4">
                                        <span class="rounded-full px-3 py-1 text-xs font-bold {{ $table->status === 'wolny' ? 'bg-green-100 text-green-800' : ($table->status === 'nieaktywny' ? 'bg-gray-200 text-gray-700' : 'bg-yellow-100 text-yellow-800') }}">
                                            {{ $statuses[$table->status] ?? $table->status }}
                                        </span>
                                    </td>
                                    <td class="py-3 pr-4">
                                        @if($table->zone)
                                            <span class="font-bold text-brand-dark">{{ $table->zone->name }}</span>
                                            <span class="mt-1 block text-xs {{ $table->zone->is_active ? 'text-green-700' : 'text-gray-500' }}">
                                                {{ $table->zone->is_active ? 'Aktywna' : 'Nieaktywna' }}
                                            </span>
                                        @else
                                            <span class="text-brand-accent">Poza strefą</span>
                                        @endif
                                    </td>
                                    <td class="py-3 pr-4">
                                        @if($table->assignedWaiter)
                                            <span class="font-bold text-brand-dark">{{ $table->assignedWaiter->name }}</span>
                                            <span class="mt-1 block text-xs text-brand-accent">{{ $table->assignedWaiter->email }}</span>
                                        @elseif($table->zone?->is_active && $table->zone?->assignedWaiter)
                                            <span class="font-bold text-brand-dark">{{ $table->zone->assignedWaiter->name }}</span>
                                            <span class="mt-1 block text-xs text-brand-accent">Ze strefy: {{ $table->zone->name }}</span>
                                        @else
                                            <span class="text-brand-accent">Bez przypisania</span>
                                        @endif
                                    </td>
                                    <td class="py-3 pr-4">{{ $table->orders_count }}</td>
                                    <td class="py-3 pr-4">
                                        <div class="flex flex-wrap justify-end gap-2">
                                            <a href="{{ route('manager.tables.edit', $table) }}" class="rounded-md border border-brand-dark/20 px-3 py-2 text-xs font-bold text-brand-dark hover:bg-brand-light">
                                                Edytuj
                                            </a>
                                            <form method="POST" action="{{ route('manager.tables.destroy', $table) }}" onsubmit="return confirm('Usunąć stolik?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-md border border-red-700 px-3 py-2 text-xs font-bold text-red-700 hover:bg-red-50">
                                                    Usuń
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-5 text-center text-brand-accent">Brak stolików w bazie.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-8 rounded-lg border border-brand-dark/15 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 class="text-xl font-black text-brand-dark">Strefy stolików</h2>
                    <p class="mt-1 text-sm text-brand-accent">
                        Strefa grupuje stoliki i może wskazywać kelnera domyślnego. Bezpośrednie przypisanie kelnera do stolika ma pierwszeństwo.
                    </p>
                </div>
            </div>

            <form method="POST" action="{{ route('manager.zones.store') }}" class="mt-5 grid gap-3 md:grid-cols-[1fr_1fr_auto] md:items-end">
                @csrf

                <div>
                    <label for="zone_name" class="block text-sm font-bold text-brand-dark">Nazwa strefy</label>
                    <input id="zone_name" name="name" type="text" maxlength="80" value="{{ old('name') }}" required class="mt-1 w-full rounded-md border border-brand-dark/20 px-3 py-2">
                </div>

                <div>
                    <label for="zone_assigned_waiter_id" class="block text-sm font-bold text-brand-dark">Kelner domyślny</label>
                    <select id="zone_assigned_waiter_id" name="assigned_waiter_id" class="mt-1 w-full rounded-md border border-brand-dark/20 px-3 py-2">
                        <option value="">Bez przypisania</option>
                        @foreach($waiters as $waiter)
                            <option value="{{ $waiter->id }}" @selected((int) old('assigned_waiter_id') === $waiter->id)>
                                {{ $waiter->name }} ({{ $waiter->email }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="rounded-md bg-brand-dark px-4 py-2 text-sm font-bold text-brand-light hover:bg-brand-accent">
                    Dodaj strefę
                </button>
            </form>

            <div class="mt-6 grid gap-4 md:grid-cols-2">
                @forelse($zones as $zone)
                    <div class="rounded-lg border border-brand-dark/10 bg-brand-light/30 p-4">
                        <form method="POST" action="{{ route('manager.zones.update', $zone) }}" class="space-y-3">
                            @csrf
                            @method('PUT')

                            <div>
                                <label for="zone_{{ $zone->id }}_name" class="block text-xs font-bold uppercase text-brand-accent">Nazwa</label>
                                <input id="zone_{{ $zone->id }}_name" name="name" type="text" maxlength="80" value="{{ $zone->name }}" required class="mt-1 w-full rounded-md border border-brand-dark/20 bg-white px-3 py-2">
                            </div>

                            <div>
                                <label for="zone_{{ $zone->id }}_waiter" class="block text-xs font-bold uppercase text-brand-accent">Kelner domyślny</label>
                                <select id="zone_{{ $zone->id }}_waiter" name="assigned_waiter_id" class="mt-1 w-full rounded-md border border-brand-dark/20 bg-white px-3 py-2">
                                    <option value="">Bez przypisania</option>
                                    @foreach($waiters as $waiter)
                                        <option value="{{ $waiter->id }}" @selected($zone->assigned_waiter_id === $waiter->id)>
                                            {{ $waiter->name }} ({{ $waiter->email }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <span class="rounded-full px-3 py-1 text-xs font-bold {{ $zone->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-700' }}">
                                    {{ $zone->is_active ? 'Aktywna' : 'Nieaktywna' }} · {{ $zone->tables_count }} stolików
                                </span>

                                <button type="submit" class="rounded-md border border-brand-dark/20 bg-white px-3 py-2 text-xs font-bold text-brand-dark hover:bg-brand-light">
                                    Zapisz
                                </button>
                            </div>
                        </form>

                        <div class="mt-3 flex flex-wrap justify-end gap-2">
                            <form method="POST" action="{{ route('manager.zones.toggle', $zone) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="rounded-md border border-brand-dark/20 bg-white px-3 py-2 text-xs font-bold text-brand-dark hover:bg-brand-light">
                                    {{ $zone->is_active ? 'Wyłącz' : 'Aktywuj' }}
                                </button>
                            </form>

                            <form method="POST" action="{{ route('manager.zones.destroy', $zone) }}" onsubmit="return confirm('Usunąć strefę? Stoliki zostaną przeniesione poza strefę.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-md border border-red-700 bg-white px-3 py-2 text-xs font-bold text-red-700 hover:bg-red-50">
                                    Usuń
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-brand-accent">Nie ma jeszcze żadnych stref.</p>
                @endforelse
            </div>
        </div>
    </section>
</x-app>
