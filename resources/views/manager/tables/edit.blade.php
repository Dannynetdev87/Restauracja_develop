<x-app>
    <x-slot:title>Edycja stolika - SmakPrzeszłości</x-slot>

    <section class="w-full max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <a href="{{ route('manager.tables.index') }}" class="inline-block text-sm font-bold text-brand-accent transition-colors duration-200 ease-out hover:text-brand-dark focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-accent/40">← Wróć do stolików</a>

        <div class="mt-6 rounded-lg border border-brand-dark/15 bg-white p-4 shadow-sm sm:p-6">
            <h1 class="text-3xl font-black text-brand-dark">Edytuj stolik</h1>

            @if($errors->any())
                <div class="mt-5 rounded-lg border border-red-700 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <strong>Popraw błędy formularza:</strong>
                    <ul class="mt-2 list-disc pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('manager.tables.update', $table) }}" class="mt-6 space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="number" class="block text-sm font-bold text-brand-dark">Numer stolika</label>
                    <input id="number" name="number" type="number" min="1" max="999" value="{{ old('number', $table->number) }}" required class="mt-1 w-full rounded-md border border-brand-dark/20 px-3 py-2">
                </div>

                <div>
                    <label for="seats" class="block text-sm font-bold text-brand-dark">Liczba miejsc</label>
                    <input id="seats" name="seats" type="number" min="1" max="50" value="{{ old('seats', $table->seats) }}" required class="mt-1 w-full rounded-md border border-brand-dark/20 px-3 py-2">
                </div>

                <div>
                    <label for="status" class="block text-sm font-bold text-brand-dark">Status</label>
                    <select id="status" name="status" required class="mt-1 w-full rounded-md border border-brand-dark/20 bg-white px-3 py-2">
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $table->status) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="assigned_waiter_id" class="block text-sm font-bold text-brand-dark">Przypisany kelner</label>
                    <select id="assigned_waiter_id" name="assigned_waiter_id" class="mt-1 w-full max-w-full rounded-md border border-brand-dark/20 bg-white px-3 py-2 overflow-hidden text-ellipsis">
                        <option value="">Bez przypisania</option>
                        @foreach($waiters as $waiter)
                            <option value="{{ $waiter->id }}" @selected((int) old('assigned_waiter_id', $table->assigned_waiter_id) === $waiter->id)>
                                {{ $waiter->name }} ({{ $waiter->email }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="zone_id" class="block text-sm font-bold text-brand-dark">Strefa</label>
                    <select id="zone_id" name="zone_id" class="mt-1 w-full max-w-full rounded-md border border-brand-dark/20 bg-white px-3 py-2 overflow-hidden text-ellipsis">
                        <option value="">Poza strefą</option>
                        @foreach($zones as $zone)
                            <option value="{{ $zone->id }}" @selected((int) old('zone_id', $table->zone_id) === $zone->id)>
                                {{ $zone->name }}{{ $zone->is_active ? '' : ' (nieaktywna)' }}
                            </option>
                        @endforeach
                    </select>
                    @if(! $table->assigned_waiter_id && $table->zone?->is_active && $table->zone?->assignedWaiter)
                        <p class="mt-2 text-xs text-brand-accent">
                            Aktualnie stolik dziedziczy kelnera ze strefy: {{ $table->zone->assignedWaiter->name }}.
                        </p>
                    @endif
                </div>

                <button type="submit" class="w-full rounded-md bg-brand-dark px-4 py-2 text-sm font-bold text-brand-light transition-all duration-200 ease-out hover:bg-brand-accent hover:shadow-md active:scale-95 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-accent/40 sm:w-auto">
                    Zapisz zmiany
                </button>
            </form>
        </div>
    </section>
</x-app>
