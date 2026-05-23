<x-app>
    <x-slot:title>Nowe zamówienie - SmakPrzeszłości</x-slot>

    <section class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="mb-8 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <span class="text-sm font-bold uppercase text-brand-accent">Panel kelnera</span>
                <h1 class="mt-2 text-3xl font-black text-brand-dark">Dodawanie pozycji do zamówienia</h1>
                <p class="mt-2 max-w-3xl text-brand-accent">
                    Wybierz stolik, dodaj pozycje z menu i zapisz zamówienie. Pozycje z ilością zero zostaną pominięte.
                </p>
            </div>

            <a href="{{ route('waiter.tables.index') }}"
               class="inline-flex w-fit rounded-md border border-brand-dark/20 bg-white px-4 py-2 text-sm font-bold text-brand-dark hover:bg-brand-light">
                Wróć do stolików
            </a>
        </div>

        @if($errors->any())
            <div class="mb-6 rounded-lg border border-red-700 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="mb-8 rounded-lg border border-brand-dark/15 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-black text-brand-dark">Wybór stolika</h2>
            <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($tables as $table)
                    @php
                        $tableActiveOrder = $table->activeOrders->first();
                        $isSelected = $selectedTable?->id === $table->id;
                        $canUseTable = ($table->status === \App\Models\RestaurantTable::STATUS_FREE && $tableActiveOrder === null)
                            || ($tableActiveOrder && $tableActiveOrder->waiter_id === auth()->id());
                    @endphp

                    @if($canUseTable)
                        <a href="{{ route('waiter.orders.create', ['table_id' => $table->id]) }}"
                           class="rounded-md border px-4 py-3 text-sm font-bold {{ $isSelected ? 'border-brand-dark bg-brand-dark text-brand-light' : 'border-brand-dark/15 bg-brand-card text-brand-dark hover:bg-brand-light' }}">
                            Stolik {{ $table->number }}
                            <span class="mt-1 block text-xs font-semibold opacity-80">
                                {{ $tableActiveOrder ? 'Aktywne zamówienie #'.$tableActiveOrder->id : 'Wolny' }}
                            </span>
                        </a>
                    @else
                        <div class="rounded-md border border-gray-200 bg-gray-100 px-4 py-3 text-sm font-bold text-gray-500">
                            Stolik {{ $table->number }}
                            <span class="mt-1 block text-xs font-semibold">
                                Niedostępny
                            </span>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        @if($selectedTable)
            @php
                $activeOrderTotal = $activeOrder ? $activeOrder->items->sum(fn ($item) => $item->subtotal()) : 0;
            @endphp

            <form method="POST" action="{{ route('waiter.orders.store', $selectedTable) }}" class="grid gap-6 lg:grid-cols-[1fr_320px]" data-order-form data-current-total="{{ $activeOrderTotal }}">
                @csrf

                <div class="space-y-6">
                    @forelse($categories as $category)
                        @if($category->availableItems->isNotEmpty())
                            <section class="rounded-lg border border-brand-dark/15 bg-white p-5 shadow-sm">
                                <div class="mb-4 flex items-center justify-between gap-4">
                                    <h2 class="text-xl font-black text-brand-dark">{{ $category->name }}</h2>
                                    <span class="text-xs font-bold uppercase text-brand-accent">
                                        {{ $category->availableItems->count() }} pozycji
                                    </span>
                                </div>

                                <div class="divide-y divide-brand-dark/10">
                                    @foreach($category->availableItems as $item)
                                        <div class="grid gap-4 py-4 md:grid-cols-[1fr_112px] md:items-start">
                                            <div>
                                                <div class="flex flex-col gap-1 sm:flex-row sm:items-baseline sm:justify-between">
                                                    <h3 class="font-black text-brand-dark">{{ $item->name }}</h3>
                                                    <span class="text-sm font-bold text-brand-accent">{{ number_format($item->price, 2, ',', ' ') }} zł</span>
                                                </div>

                                                @if($item->description)
                                                    <p class="mt-1 text-sm text-brand-accent">{{ $item->description }}</p>
                                                @endif

                                                <label for="item-notes-{{ $item->id }}" class="mt-3 block text-xs font-bold uppercase text-brand-accent">
                                                    Notatka dla kuchni lub baru
                                                </label>
                                                <input id="item-notes-{{ $item->id }}"
                                                       name="items[{{ $item->id }}][notes]"
                                                       value="{{ old('items.'.$item->id.'.notes') }}"
                                                       type="text"
                                                       maxlength="500"
                                                       class="mt-1 w-full rounded-md border border-brand-dark/20 bg-white px-3 py-2 text-sm text-brand-dark focus:border-brand-dark focus:outline-none"
                                                       placeholder="np. bez cebuli, mniej lodu">
                                            </div>

                                            <div>
                                                <label for="item-quantity-{{ $item->id }}" class="block text-xs font-bold uppercase text-brand-accent">
                                                    Ilość
                                                </label>
                                                <input id="item-quantity-{{ $item->id }}"
                                                       name="items[{{ $item->id }}][quantity]"
                                                       value="{{ old('items.'.$item->id.'.quantity', 0) }}"
                                                       type="number"
                                                       min="0"
                                                       max="99"
                                                       inputmode="numeric"
                                                       data-order-quantity
                                                       data-price="{{ $item->price }}"
                                                       class="mt-1 w-full rounded-md border border-brand-dark/20 bg-white px-3 py-2 text-sm font-bold text-brand-dark focus:border-brand-dark focus:outline-none">
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </section>
                        @endif
                    @empty
                        <div class="rounded-lg border border-brand-dark/15 bg-white p-8 text-center text-brand-accent">
                            Brak dostępnych pozycji menu.
                        </div>
                    @endforelse
                </div>

                <aside class="h-fit rounded-lg border border-brand-dark/15 bg-white p-5 shadow-sm lg:sticky lg:top-28">
                    <span class="text-sm font-bold uppercase text-brand-accent">Podsumowanie</span>
                    <h2 class="mt-2 text-2xl font-black text-brand-dark">Stolik {{ $selectedTable->number }}</h2>
                    <p class="mt-1 text-sm text-brand-accent">Liczba miejsc: {{ $selectedTable->seats }}</p>

                    @if($activeOrder)
                        <div class="mt-4 rounded-md bg-brand-light px-3 py-2 text-sm text-brand-dark">
                            Dodajesz pozycje do zamówienia #{{ $activeOrder->id }}.
                        </div>
                    @else
                        <div class="mt-4 rounded-md bg-green-50 px-3 py-2 text-sm font-semibold text-green-800">
                            Po zapisaniu zostanie utworzone nowe zamówienie.
                        </div>
                    @endif

                    <div class="mt-5 space-y-3 border-t border-brand-dark/10 pt-4 text-sm text-brand-dark">
                        <div class="flex justify-between gap-4">
                            <span>Aktualny rachunek</span>
                            <strong>{{ number_format($activeOrderTotal, 2, ',', ' ') }} zł</strong>
                        </div>
                        <div class="flex justify-between gap-4">
                            <span>Nowe pozycje</span>
                            <strong><span data-selected-total>0,00</span> zł</strong>
                        </div>
                        <div class="flex justify-between gap-4 border-t border-brand-dark/10 pt-3 text-base">
                            <span>Razem po dodaniu</span>
                            <strong><span data-grand-total>{{ number_format($activeOrderTotal, 2, ',', ' ') }}</span> zł</strong>
                        </div>
                    </div>

                    <button type="submit" class="mt-5 w-full rounded-md bg-brand-dark px-4 py-3 text-sm font-bold text-brand-light hover:bg-brand-accent">
                        Zapisz pozycje
                    </button>
                </aside>
            </form>
        @else
            <div class="rounded-lg border border-brand-dark/15 bg-white p-8 text-center text-brand-accent shadow-sm">
                Wybierz stolik, aby wyświetlić formularz zamówienia.
            </div>
        @endif
    </section>

    @if($selectedTable)
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const form = document.querySelector('[data-order-form]');

                if (!form) {
                    return;
                }

                const currentTotal = Number.parseFloat(form.dataset.currentTotal || '0');
                const selectedTotalElement = form.querySelector('[data-selected-total]');
                const grandTotalElement = form.querySelector('[data-grand-total]');
                const quantityInputs = form.querySelectorAll('[data-order-quantity]');
                const formatter = new Intl.NumberFormat('pl-PL', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                });

                const updateTotals = () => {
                    let selectedTotal = 0;

                    quantityInputs.forEach((input) => {
                        const quantity = Number.parseInt(input.value || '0', 10);
                        const price = Number.parseFloat(input.dataset.price || '0');

                        if (quantity > 0 && price > 0) {
                            selectedTotal += quantity * price;
                        }
                    });

                    selectedTotalElement.textContent = formatter.format(selectedTotal);
                    grandTotalElement.textContent = formatter.format(currentTotal + selectedTotal);
                };

                quantityInputs.forEach((input) => {
                    input.addEventListener('input', updateTotals);
                    input.addEventListener('change', updateTotals);
                });

                updateTotals();
            });
        </script>
    @endif
</x-app>
