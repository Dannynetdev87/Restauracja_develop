<x-app>
    <x-slot:title>Edycja pozycji menu - SmakPrzeszłości</x-slot>

    <section class="w-full max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <a href="{{ route('manager.podglad') }}" class="text-sm font-bold text-brand-accent hover:text-brand-dark">← Wróć do menu</a>

        <div class="mt-6 rounded-lg border border-brand-dark/15 bg-white p-6 shadow-sm">
            <h1 class="text-3xl font-black text-brand-dark">Edytuj pozycję menu</h1>

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

            <form method="POST" action="{{ route('manager.menu-items.update', $menuItem) }}" class="mt-6 grid gap-4 sm:grid-cols-2">
                @csrf
                @method('PUT')

                <div class="sm:col-span-2">
                    <label for="name" class="block text-sm font-bold text-brand-dark">Nazwa</label>
                    <input id="name" name="name" value="{{ old('name', $menuItem->name) }}" required maxlength="255" class="mt-1 w-full rounded-md border border-brand-dark/20 px-3 py-2">
                </div>

                <div>
                    <label for="menu_category_id" class="block text-sm font-bold text-brand-dark">Kategoria</label>
                    <select id="menu_category_id" name="menu_category_id" required class="mt-1 w-full rounded-md border border-brand-dark/20 px-3 py-2">
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected((int) old('menu_category_id', $menuItem->menu_category_id) === $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="price" class="block text-sm font-bold text-brand-dark">Cena</label>
                    <input id="price" name="price" type="number" min="0.01" max="99999.99" step="0.01" value="{{ old('price', $menuItem->price) }}" required class="mt-1 w-full rounded-md border border-brand-dark/20 px-3 py-2">
                </div>

                <div>
                    <label for="production_area" class="block text-sm font-bold text-brand-dark">Przygotowanie</label>
                    <select id="production_area" name="production_area" required class="mt-1 w-full rounded-md border border-brand-dark/20 px-3 py-2">
                        <option value="kuchnia" @selected(old('production_area', $menuItem->production_area) === 'kuchnia')>Kuchnia</option>
                        <option value="bar" @selected(old('production_area', $menuItem->production_area) === 'bar')>Bar</option>
                    </select>
                </div>

                <label class="flex items-end gap-2 pb-2 text-sm font-semibold text-brand-dark">
                    <input type="checkbox" name="available" value="1" @checked(old('available', $menuItem->available)) class="rounded border-brand-dark/30">
                    Dostępna w sprzedaży
                </label>

                <div class="sm:col-span-2">
                    <label for="description" class="block text-sm font-bold text-brand-dark">Opis</label>
                    <textarea id="description" name="description" rows="4" maxlength="1000" class="mt-1 w-full rounded-md border border-brand-dark/20 px-3 py-2">{{ old('description', $menuItem->description) }}</textarea>
                </div>

                <div class="sm:col-span-2">
                    <button type="submit" class="rounded-md bg-brand-dark px-4 py-2 text-sm font-bold text-brand-light hover:bg-brand-accent">
                        Zapisz zmiany
                    </button>
                </div>
            </form>
        </div>
    </section>
</x-app>
