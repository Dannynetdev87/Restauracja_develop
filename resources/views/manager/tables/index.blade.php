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
                                        @if($table->assignedWaiter)
                                            <span class="font-bold text-brand-dark">{{ $table->assignedWaiter->name }}</span>
                                            <span class="mt-1 block text-xs text-brand-accent">{{ $table->assignedWaiter->email }}</span>
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
                                    <td colspan="6" class="py-5 text-center text-brand-accent">Brak stolików w bazie.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</x-app>
