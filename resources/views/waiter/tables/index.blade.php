<x-app>
    <x-slot:title>Stoliki kelnera - SmakPrzeszłości</x-slot>

    <section class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="mb-8 flex flex-wrap justify-center gap-4">
            <a href="{{ route('waiter.dashboard') }}"
               class="min-w-40 rounded-xl bg-brand-dark px-8 py-4 text-center text-sm font-black uppercase tracking-wide text-brand-light shadow-sm transition hover:bg-brand-accent">
                Dashboard
            </a>
            <a href="{{ route('waiter.tables.index') }}"
               class="min-w-40 rounded-xl bg-brand-dark px-8 py-4 text-center text-sm font-black uppercase tracking-wide text-brand-light shadow-sm transition hover:bg-brand-accent">
                Stoliki
            </a>
        </div>

        <div class="flex flex-col gap-2 mb-8">
            <span class="text-sm font-bold uppercase text-brand-accent">Panel kelnera</span>
            <h1 class="text-3xl font-black text-brand-dark">Stoliki</h1>
            <p class="text-brand-accent max-w-3xl">
                Wybierz wolny stolik, aby rozpocząć zamówienie. Przy zajętym stoliku możesz wrócić do aktywnego zamówienia, sprawdzić status pozycji i dodać kolejne dania.
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
                    $activeOrder = $table->activeOrders->first();
                    $isOwnActiveOrder = $activeOrder && $activeOrder->waiter_id === auth()->id();
                    $canOpenOrder = $table->status === \App\Models\RestaurantTable::STATUS_FREE && $activeOrder === null;
                    $orderStatusLabels = [
                        \App\Models\Order::STATUS_OPEN => 'Otwarte',
                        \App\Models\Order::STATUS_IN_PROGRESS => 'W przygotowaniu',
                        \App\Models\Order::STATUS_READY => 'Gotowe',
                        \App\Models\Order::STATUS_SERVED => 'Wydane',
                    ];
                    $orderStatusClass = match ($activeOrder?->status) {
                        \App\Models\Order::STATUS_READY => 'bg-green-100 text-green-800',
                        \App\Models\Order::STATUS_SERVED => 'bg-brand-light text-brand-dark',
                        \App\Models\Order::STATUS_IN_PROGRESS => 'bg-yellow-100 text-yellow-800',
                        default => 'bg-gray-100 text-gray-700',
                    };
                    $readyItems = $activeOrder?->items->where('status', \App\Models\OrderItem::STATUS_READY) ?? collect();
                    $cancelledItems = $activeOrder?->items->where('status', \App\Models\OrderItem::STATUS_CANCELLED) ?? collect();
                    $preparingItems = $activeOrder?->items->where('status', \App\Models\OrderItem::STATUS_PREPARING) ?? collect();
                    $newItems = $activeOrder?->items->where('status', \App\Models\OrderItem::STATUS_NEW) ?? collect();
                    $deliveredItems = $activeOrder?->items->where('status', \App\Models\OrderItem::STATUS_DELIVERED) ?? collect();
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
                            <div class="mt-4 space-y-3 rounded-lg border border-brand-dark/10 bg-brand-light p-3 text-sm text-brand-dark">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <div>
                                        <p class="font-black">Aktywne zamówienie #{{ $activeOrder->id }}</p>
                                        <p class="mt-1 text-xs text-brand-accent">
                                            Otwarte {{ $activeOrder->opened_at->format('H:i') }}
                                        </p>
                                    </div>
                                    <span class="rounded-md px-2.5 py-1 text-xs font-bold {{ $orderStatusClass }}">
                                        {{ $orderStatusLabels[$activeOrder->status] ?? $activeOrder->status }}
                                    </span>
                                </div>

                                <div class="grid grid-cols-2 gap-2 text-xs sm:grid-cols-3">
                                    <div class="rounded-md bg-white px-2.5 py-2">
                                        <span class="block font-bold text-brand-accent">Nowe</span>
                                        <strong class="text-brand-dark">{{ $newItems->sum('quantity') }}</strong>
                                    </div>
                                    <div class="rounded-md bg-white px-2.5 py-2">
                                        <span class="block font-bold text-brand-accent">W przygotowaniu</span>
                                        <strong class="text-brand-dark">{{ $preparingItems->sum('quantity') }}</strong>
                                    </div>
                                    <div class="rounded-md bg-white px-2.5 py-2">
                                        <span class="block font-bold text-brand-accent">Dostarczone</span>
                                        <strong class="text-brand-dark">{{ $deliveredItems->sum('quantity') }}</strong>
                                    </div>
                                </div>

                                @if($readyItems->isNotEmpty())
                                    <div class="rounded-md border border-green-700/20 bg-green-50 px-3 py-2 text-green-800">
                                        <p class="font-black">Gotowe do dostarczenia: {{ $readyItems->sum('quantity') }}</p>
                                        <p class="mt-1 text-xs">
                                            {{ $readyItems->pluck('menuItem.name')->filter()->take(2)->implode(', ') }}
                                        </p>
                                    </div>
                                @endif

                                @if($cancelledItems->isNotEmpty())
                                    <div class="rounded-md border border-red-700/20 bg-red-50 px-3 py-2 text-red-800">
                                        <p class="font-black">Anulowane / braki: {{ $cancelledItems->sum('quantity') }}</p>
                                        <p class="mt-1 text-xs">
                                            {{ $cancelledItems->pluck('menuItem.name')->filter()->take(2)->implode(', ') }}
                                        </p>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>

                    <div class="mt-5">
                        @if($canOpenOrder)
                            <a href="{{ route('waiter.orders.create', ['table_id' => $table->id]) }}"
                               class="block w-full rounded-md bg-brand-dark px-4 py-2 text-center text-sm font-bold text-brand-light hover:bg-brand-accent">
                                Rozpocznij zamówienie
                            </a>
                        @elseif($isOwnActiveOrder)
                            <div class="grid gap-2">
                                <a href="{{ route('waiter.orders.show', $activeOrder) }}"
                                   class="block w-full rounded-md border border-brand-dark/20 bg-white px-4 py-2 text-center text-sm font-bold text-brand-dark hover:bg-brand-light">
                                    Zobacz zamówienie
                                </a>
                                <a href="{{ route('waiter.orders.create', ['table_id' => $table->id]) }}"
                                   class="block w-full rounded-md bg-brand-dark px-4 py-2 text-center text-sm font-bold text-brand-light hover:bg-brand-accent">
                                    Dodaj pozycje
                                </a>
                            </div>
                        @elseif($activeOrder)
                            <button type="button" disabled class="w-full cursor-not-allowed rounded-md bg-gray-200 px-4 py-2 text-sm font-bold text-gray-600">
                                Obsługuje inny kelner
                            </button>
                        @else
                            <button type="button" disabled class="w-full cursor-not-allowed rounded-md bg-gray-200 px-4 py-2 text-sm font-bold text-gray-600">
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
