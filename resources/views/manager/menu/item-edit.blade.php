<x-app>
    <x-slot:title>Edycja pozycji menu - SmakPrzeszłości</x-slot>

    <section class="w-full max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="mb-8 flex flex-col gap-5 border-b border-brand-dark/10 pb-6 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <span class="text-sm font-bold uppercase text-brand-accent">Panel managera</span>
                <h1 class="mt-1 text-3xl font-black text-brand-dark">Edytuj pozycję menu</h1>
            </div>

            <a href="{{ route('manager.dashboard') }}"
               class="w-full rounded-md border border-brand-dark/20 bg-white px-4 py-2 text-center text-sm font-bold text-brand-dark transition-all duration-200 ease-out hover:bg-brand-light hover:shadow-md active:scale-95 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-accent/40 sm:w-auto">
                Powrót do panelu
            </a>
        </div>

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

        <div class="rounded-lg border border-brand-dark/15 bg-white p-4 shadow-sm sm:p-6">
            <form method="POST" action="{{ route('manager.menu-items.update', $menuItem) }}" class="grid gap-4 sm:grid-cols-2">
                @csrf
                @method('PUT')

                <div class="sm:col-span-2">
                    <label for="name" class="block text-sm font-bold text-brand-dark">Nazwa</label>
                    <input id="name" name="name" value="{{ old('name', $menuItem->name) }}" required maxlength="255" class="mt-1 w-full rounded-md border border-brand-dark/20 px-3 py-2">
                </div>

                <div>
                    <label for="menu_category_id" class="block text-sm font-bold text-brand-dark">Kategoria</label>
                    <select id="menu_category_id" name="menu_category_id" required class="mt-1 w-full max-w-full rounded-md border border-brand-dark/20 bg-white px-3 py-2 overflow-hidden text-ellipsis">
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
                    <select id="production_area" name="production_area" required class="mt-1 w-full rounded-md border border-brand-dark/20 bg-white px-3 py-2">
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
                    <button type="submit" class="w-full rounded-md bg-brand-dark px-4 py-2 text-sm font-bold text-brand-light transition-all duration-200 ease-out hover:bg-brand-accent hover:shadow-md active:scale-95 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-accent/40 sm:w-auto">
                        Zapisz zmiany
                    </button>
                </div>
            </form>
        </div>
    </section>
</x-app>
