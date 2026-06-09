<x-app>
    <x-slot:title>Zarządzanie menu - SmakPrzeszłości</x-slot>

    <section class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="mb-8 flex flex-col gap-5 border-b border-brand-dark/10 pb-6 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <span class="text-sm font-bold uppercase text-brand-accent">Panel managera</span>
                <h1 class="mt-1 text-3xl font-black text-brand-dark">Zarządzanie menu</h1>
                <p class="mt-1 text-brand-accent max-w-3xl">
                    Kategorie i pozycje menu są podstawą późniejszego procesu zamówień. Pozycji użytych w zamówieniach nie usuwamy z historii, tylko dezaktywujemy.
                </p>
            </div>

            <a href="{{ route('manager.dashboard') }}"
               class="w-full rounded-md border border-brand-dark/20 bg-white px-4 py-2 text-center text-sm font-bold text-brand-dark transition-all duration-200 ease-out hover:bg-brand-light hover:shadow-md active:scale-95 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-accent/40 sm:w-auto">
                Powrót do panelu
            </a>
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

        <div class="grid min-w-0 gap-6 lg:grid-cols-[0.9fr_1.1fr]">
            <div class="rounded-lg border border-brand-dark/15 bg-white p-4 shadow-sm sm:p-5">
                <h2 class="text-xl font-black text-brand-dark">Dodaj kategorię</h2>

                <form method="POST" action="{{ route('manager.menu-categories.store') }}" class="mt-5 space-y-4">
                    @csrf

                    <div>
                        <label for="category-name" class="block text-sm font-bold text-brand-dark">Nazwa</label>
                        <input id="category-name" name="name" value="{{ old('name') }}" required maxlength="255" class="mt-1 w-full rounded-md border border-brand-dark/20 px-3 py-2">
                    </div>

                    <div>
                        <label for="category-sort-order" class="block text-sm font-bold text-brand-dark">Kolejność</label>
                        <input id="category-sort-order" name="sort_order" type="number" min="0" max="999" value="{{ old('sort_order', 0) }}" class="mt-1 w-full rounded-md border border-brand-dark/20 px-3 py-2">
                    </div>

                    <label class="flex items-center gap-2 text-sm font-semibold text-brand-dark">
                        <input type="checkbox" name="is_active" value="1" checked class="rounded border-brand-dark/30">
                        Aktywna
                    </label>

                    <button type="submit" class="w-full rounded-md bg-brand-dark px-4 py-2 text-sm font-bold text-brand-light transition-all duration-200 ease-out hover:bg-brand-accent hover:shadow-md active:scale-95 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-accent/40 sm:w-auto">
                        Dodaj kategorię
                    </button>
                </form>
            </div>

            <div class="min-w-0 rounded-lg border border-brand-dark/15 bg-white p-4 shadow-sm sm:p-5">
                <h2 class="text-xl font-black text-brand-dark">Dodaj pozycję menu</h2>

                <form method="POST" action="{{ route('manager.menu-items.store') }}" class="mt-5 grid gap-4 sm:grid-cols-2">
                    @csrf

                    <div class="sm:col-span-2">
                        <label for="item-name" class="block text-sm font-bold text-brand-dark">Nazwa</label>
                        <input id="item-name" name="name" value="{{ old('name') }}" required maxlength="255" class="mt-1 w-full rounded-md border border-brand-dark/20 px-3 py-2">
                    </div>

                    <div>
                        <label for="item-category" class="block text-sm font-bold text-brand-dark">Kategoria</label>
                        <select id="item-category" name="menu_category_id" required class="mt-1 w-full max-w-full rounded-md border border-brand-dark/20 bg-white px-3 py-2 overflow-hidden text-ellipsis">
                            <option value="">Wybierz kategorię</option>
                            @foreach($activeCategories as $category)
                                <option value="{{ $category->id }}" @selected((int) old('menu_category_id') === $category->id)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="item-price" class="block text-sm font-bold text-brand-dark">Cena</label>
                        <input id="item-price" name="price" type="number" min="0.01" max="99999.99" step="0.01" value="{{ old('price') }}" required class="mt-1 w-full rounded-md border border-brand-dark/20 px-3 py-2">
                    </div>

                    <div>
                        <label for="item-production-area" class="block text-sm font-bold text-brand-dark">Przygotowanie</label>
                        <select id="item-production-area" name="production_area" required class="mt-1 w-full rounded-md border border-brand-dark/20 bg-white px-3 py-2">
                            <option value="kuchnia" @selected(old('production_area') === 'kuchnia')>Kuchnia</option>
                            <option value="bar" @selected(old('production_area') === 'bar')>Bar</option>
                        </select>
                    </div>

                    <label class="flex items-end gap-2 pb-2 text-sm font-semibold text-brand-dark">
                        <input type="checkbox" name="available" value="1" checked class="rounded border-brand-dark/30">
                        Dostępna w sprzedaży
                    </label>

                    <div class="sm:col-span-2">
                        <label for="item-description" class="block text-sm font-bold text-brand-dark">Opis</label>
                        <textarea id="item-description" name="description" rows="3" maxlength="1000" class="mt-1 w-full rounded-md border border-brand-dark/20 px-3 py-2">{{ old('description') }}</textarea>
                    </div>

                    <div class="sm:col-span-2">
                        <button type="submit" class="w-full rounded-md bg-brand-dark px-4 py-2 text-sm font-bold text-brand-light transition-all duration-200 ease-out hover:bg-brand-accent hover:shadow-md active:scale-95 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-accent/40 sm:w-auto">
                            Dodaj pozycję
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="mt-8 space-y-6">
            @forelse($categories as $category)
                <article class="min-w-0 rounded-lg border border-brand-dark/15 bg-white p-4 shadow-sm sm:p-5">
                    <div class="flex flex-col gap-4 border-b border-brand-dark/10 pb-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="text-2xl font-black text-brand-dark">{{ $category->name }}</h2>
                                <span class="rounded-full px-3 py-1 text-xs font-bold {{ $category->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-700' }}">
                                    {{ $category->is_active ? 'aktywna' : 'nieaktywna' }}
                                </span>
                                <span class="rounded-full bg-brand-light px-3 py-1 text-xs font-bold text-brand-dark">
                                    kolejność: {{ $category->sort_order }}
                                </span>
                            </div>
                            <p class="mt-1 text-sm text-brand-accent">Liczba pozycji: {{ $category->items->count() }}</p>
                        </div>

                        <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
                            <a href="{{ route('manager.menu-categories.edit', $category) }}" class="rounded-md border border-brand-dark/20 px-3 py-2 text-center text-sm font-bold text-brand-dark transition-all duration-200 ease-out hover:bg-brand-light hover:shadow-md active:scale-95 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-accent/40">
                                Edytuj
                            </a>
                            <form method="POST" action="{{ route('manager.menu-categories.destroy', $category) }}" onsubmit="return confirm('Usunąć kategorię?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full rounded-md border border-red-700 px-3 py-2 text-sm font-bold text-red-700 transition-all duration-200 ease-out hover:bg-red-50 hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-700/30">
                                    Usuń
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full whitespace-nowrap text-left text-sm">
                            <thead class="border-b border-brand-dark/10 text-xs uppercase text-brand-accent">
                            <tr>
                                <th class="py-3 pr-4">Nazwa</th>
                                <th class="py-3 pr-4">Cena</th>
                                <th class="py-3 pr-4">Obszar</th>
                                <th class="py-3 pr-4">Status</th>
                                <th class="py-3 pr-4 text-right">Akcje</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-brand-dark/10">
                            @forelse($category->items as $item)
                                <tr>
                                    <td class="py-3 pr-4 font-bold text-brand-dark">
                                        {{ $item->name }}
                                        @if($item->description)
                                            <p class="mt-1 max-w-xl text-xs font-normal text-brand-accent">{{ $item->description }}</p>
                                        @endif
                                    </td>
                                    <td class="py-3 pr-4">{{ number_format($item->price, 2, ',', ' ') }} zł</td>
                                    <td class="py-3 pr-4">{{ $item->production_area === 'bar' ? 'Bar' : 'Kuchnia' }}</td>
                                    <td class="py-3 pr-4">
                                            <span class="rounded-full px-3 py-1 text-xs font-bold {{ $item->available ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-700' }}">
                                                {{ $item->available ? 'dostępna' : 'niedostępna' }}
                                            </span>
                                    </td>
                                    <td class="py-3 pr-4">
                                        <div class="flex flex-col justify-end gap-2 sm:flex-row">
                                            <a href="{{ route('manager.menu-items.edit', $item) }}" class="rounded-md border border-brand-dark/20 px-3 py-2 text-center text-xs font-bold text-brand-dark transition-all duration-200 ease-out hover:bg-brand-light hover:shadow-md active:scale-95 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-accent/40">
                                                Edytuj
                                            </a>
                                            <form method="POST" action="{{ route('manager.menu-items.availability', $item) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="w-full rounded-md border border-brand-dark/20 px-3 py-2 text-xs font-bold text-brand-dark transition-all duration-200 ease-out hover:bg-brand-light hover:shadow-md active:scale-95 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-accent/40">
                                                    {{ $item->available ? 'Wyłącz' : 'Włącz' }}
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('manager.menu-items.destroy', $item) }}" onsubmit="return confirm('Usunąć lub dezaktywować pozycję menu?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="w-full rounded-md border border-red-700 px-3 py-2 text-xs font-bold text-red-700 transition-all duration-200 ease-out hover:bg-red-50 hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-700/30">
                                                    Usuń
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-5 text-center text-brand-accent">Ta kategoria nie ma jeszcze pozycji menu.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </article>
            @empty
                <div class="rounded-lg border border-brand-dark/15 bg-white p-8 text-center text-brand-accent">
                    Brak kategorii menu. Dodaj pierwszą kategorię, aby utworzyć ofertę restauracji.
                </div>
            @endforelse
        </div>
    </section>
</x-app>
