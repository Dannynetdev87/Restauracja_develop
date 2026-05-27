<x-app>
    <x-slot:title>Edycja stolika - SmakPrzeszłości</x-slot>

    <section class="w-full max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <a href="{{ route('manager.tables.index') }}" class="text-sm font-bold text-brand-accent hover:text-brand-dark">← Wróć do stolików</a>

        <div class="mt-6 rounded-lg border border-brand-dark/15 bg-white p-6 shadow-sm">
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
                    <select id="status" name="status" required class="mt-1 w-full rounded-md border border-brand-dark/20 px-3 py-2">
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $table->status) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="assigned_waiter_id" class="block text-sm font-bold text-brand-dark">Przypisany kelner</label>
                    <select id="assigned_waiter_id" name="assigned_waiter_id" class="mt-1 w-full rounded-md border border-brand-dark/20 px-3 py-2">
                        <option value="">Bez przypisania</option>
                        @foreach($waiters as $waiter)
                            <option value="{{ $waiter->id }}" @selected((int) old('assigned_waiter_id', $table->assigned_waiter_id) === $waiter->id)>
                                {{ $waiter->name }} ({{ $waiter->email }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="rounded-md bg-brand-dark px-4 py-2 text-sm font-bold text-brand-light hover:bg-brand-accent">
                    Zapisz zmiany
                </button>
            </form>
        </div>
    </section>
</x-app>
