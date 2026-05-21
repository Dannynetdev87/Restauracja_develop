<x-app>
    <x-slot:title>Stoliki kelnera - SmakPrzeszłości</x-slot>

    <section class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="flex flex-col gap-2 mb-8">
            <span class="text-sm font-bold uppercase text-brand-accent">Panel kelnera</span>
            <h1 class="text-3xl font-black text-brand-dark">Stoliki</h1>
            <p class="text-brand-accent max-w-3xl">
                Wybierz stolik, aby rozpocząć nowe zamówienie lub dodać pozycje do już istniejącego, aktywnego rachunku.
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

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @forelse($tables as $table)
                @php
                    // Bezpieczna ekstrakcja pojedynczego modelu zamówienia z kolekcji relacji
                    $activeOrder = null;

                    if (!empty($table->activeOrders) && $table->activeOrders instanceof \Illuminate\Support\Collection) {
                        $activeOrder = $table->activeOrders->first();
                    } elseif (!empty($table->orders) && $table->orders instanceof \Illuminate\Support\Collection) {
                        $activeOrder = $table->orders->whereIn('status', ['open', 'in_progress', 'ready', 'served'])->first();
                    }

                    // Mapowanie klas dla odznak statusów
                    $badgeClass = match ($table->status) {
                        \App\Models\RestaurantTable::STATUS_FREE => 'bg-green-100 text-green-800',
                        \App\Models\RestaurantTable::STATUS_OCCUPIED => 'bg-yellow-100 text-yellow-800',
                        \App\Models\RestaurantTable::STATUS_RESERVED => 'bg-blue-100 text-blue-800',
                        default => 'bg-gray-200 text-gray-700',
                    };
                @endphp

                <article class="rounded-lg border border-brand-dark/15 bg-white p-5 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h2 class="text-2xl font-black text-brand-dark">Stolik {{ $table->number }}</h2>
                                <p class="mt-1 text-sm text-brand-accent">Miejsca: {{ $table->seats }}</p>
                            </div>
                            <span class="rounded-full px-3 py-1 text-xs font-bold {{ $badgeClass }}">
                                {{ $statuses[$table->status] ?? $table->status }}
                            </span>
                        </div>

                        @if($activeOrder)
                            <div class="mt-4 rounded-md bg-brand-light px-3 py-2 text-sm font-bold text-brand-dark">
                                Aktywne zamówienie #{{ $activeOrder->id }}
                            </div>
                        @endif
                    </div>

                    <div class="mt-5">
                        @if($table->status === 'wolny')
                            <a href="{{ route('waiter.orders.create', ['table_id' => $table->id]) }}"
                               class="block w-full text-center rounded-md bg-brand-dark px-4 py-2 text-sm font-bold text-brand-light hover:bg-brand-accent transition no-underline">
                                Rozpocznij zamówienie
                            </a>
                        @elseif($table->status === 'zajety')
                            @if($activeOrder)
                                <a href="{{ route('waiter.orders.show', $activeOrder) }}"
                                   class="block w-full text-center rounded-md bg-brand-accent px-4 py-2 text-sm font-bold text-brand-light hover:bg-brand-dark transition no-underline">
                                    Zobacz / Dodaj pozycje
                                </a>
                            @else
                                <a href="{{ route('waiter.orders.create', ['table_id' => $table->id]) }}"
                                   class="block w-full text-center rounded-md bg-amber-600 px-4 py-2 text-sm font-bold text-white hover:bg-amber-700 transition no-underline">
                                    Otwórz nowe zamówienie
                                </a>
                            @endif
                        @else
                            <button type="button" disabled class="w-full cursor-not-allowed rounded-md bg-gray-200 px-4 py-2 text-sm font-bold text-gray-400">
                                Niedostępny
                            </button>
                        @endif
                    </div>
                </article>
            @empty
                <div class="rounded-lg border border-brand-dark/15 bg-white p-8 text-center text-brand-accent sm:col-span-2 xl:col-span-3">
                    Brak stolików w bazie.
                </div>
            @endforelse
        </div>
    </section>
</x-app>
