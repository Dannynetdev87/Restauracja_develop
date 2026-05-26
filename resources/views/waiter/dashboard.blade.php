<x-app>
    <x-slot:title>Panel kelnera - SmakPrzeszłości</x-slot>

    @php
        $formatItemTitle = function ($item) {
            $name = $item->menuItem?->name ?? 'Pozycja menu';

            return $item->quantity.'x '.$name;
        };

        $itemStatusLabel = fn ($item) => match ($item->status) {
            \App\Models\OrderItem::STATUS_NEW => 'Oczekuje',
            \App\Models\OrderItem::STATUS_PREPARING => 'Wytwarzanie',
            \App\Models\OrderItem::STATUS_READY => 'Gotowe',
            \App\Models\OrderItem::STATUS_CANCELLED => 'Brak towaru',
            default => $item->status,
        };

        $formatShiftDate = fn () => now()->locale('pl')->translatedFormat('l, d F');
    @endphp

    <section
        id="waiter-dashboard-refresh"
        class="w-full px-4 py-8 sm:px-6 lg:px-8"
        data-auto-refresh
        data-refresh-url="{{ route('waiter.dashboard') }}"
        data-refresh-interval="8000"
    >
        <div class="mx-auto max-w-6xl">
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

            <div class="grid gap-5 lg:grid-cols-3">
                <section class="rounded-xl border-2 border-brand-dark bg-brand-light/50 p-5">
                    <h2 class="border-b-2 border-brand-dark pb-3 text-center text-sm font-black uppercase tracking-[0.16em] text-brand-dark">
                        W realizacji
                    </h2>

                    <div class="mt-4 space-y-3">
                        @forelse($inProgressItems->take(4) as $item)
                            <article class="rounded-lg border border-brand-dark/20 bg-white/80 p-4 shadow-sm">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h3 class="font-black text-brand-dark">{{ $formatItemTitle($item) }}</h3>
                                        <p class="mt-1 text-xs font-bold text-amber-800">
                                            Status: {{ $itemStatusLabel($item) }}
                                        </p>
                                    </div>
                                    <span class="shrink-0 rounded-md border border-brand-dark/20 bg-white px-2 py-1 text-xs font-black text-brand-dark">
                                        Stolik {{ $item->order->table->number }}
                                    </span>
                                </div>
                            </article>
                        @empty
                            <div class="rounded-lg border border-dashed border-brand-dark/30 bg-white/40 px-4 py-8 text-center text-sm font-bold text-brand-accent">
                                Brak pozycji w realizacji.
                            </div>
                        @endforelse
                    </div>
                </section>

                <section class="rounded-xl border-2 border-red-900 bg-red-50/20 p-5">
                    <h2 class="border-b-2 border-red-900 pb-3 text-center text-sm font-black uppercase tracking-[0.16em] text-red-950">
                        Anulowane / braki
                    </h2>

                    <div class="mt-4 space-y-3">
                        @forelse($cancelledItems->take(4) as $item)
                            <article class="rounded-lg border border-red-200 bg-white/90 p-4 shadow-sm">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h3 class="font-black text-brand-dark line-through decoration-red-700/60">
                                            {{ $formatItemTitle($item) }}
                                        </h3>
                                        <p class="mt-1 text-xs font-bold text-red-800">
                                            Powód: {{ $itemStatusLabel($item) }}
                                        </p>
                                    </div>
                                    <span class="shrink-0 rounded-md border border-red-300 bg-red-50 px-2 py-1 text-xs font-black text-red-800">
                                        Stolik {{ $item->order->table->number }}
                                    </span>
                                </div>
                                <a href="{{ route('waiter.orders.show', $item->order) }}"
                                   class="mt-3 block rounded-md bg-brand-dark px-4 py-2 text-center text-xs font-black uppercase tracking-wide text-brand-light transition hover:bg-brand-accent">
                                    Sprawdź zamówienie
                                </a>
                            </article>
                        @empty
                            <div class="rounded-lg border border-dashed border-red-900/30 bg-white/40 px-4 py-8 text-center text-sm font-bold text-red-900">
                                Brak anulowanych pozycji.
                            </div>
                        @endforelse
                    </div>
                </section>

                <section class="rounded-xl border-2 border-emerald-900 bg-emerald-50/20 p-5">
                    <h2 class="border-b-2 border-emerald-900 pb-3 text-center text-sm font-black uppercase tracking-[0.16em] text-emerald-950">
                        Do odbioru
                    </h2>

                    <div class="mt-4 space-y-3">
                        @forelse($readyItems->take(4) as $item)
                            <article class="rounded-lg border border-emerald-200 bg-white/90 p-4 shadow-sm">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h3 class="font-black text-brand-dark">{{ $formatItemTitle($item) }}</h3>
                                        <p class="mt-1 text-xs font-bold text-emerald-800">
                                            Stan: Gotowe do dostarczenia
                                        </p>
                                    </div>
                                    <span class="shrink-0 rounded-md border border-emerald-300 bg-emerald-50 px-2 py-1 text-xs font-black text-emerald-800">
                                        Stolik {{ $item->order->table->number }}
                                    </span>
                                </div>
                                <form method="POST" action="{{ route('waiter.order-items.deliver', $item) }}" class="mt-3">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="w-full rounded-md bg-brand-dark px-4 py-2 text-xs font-black uppercase tracking-wide text-brand-light transition hover:bg-brand-accent">
                                        Odbierz z kuchni
                                    </button>
                                </form>
                            </article>
                        @empty
                            <div class="rounded-lg border border-dashed border-emerald-900/30 bg-white/40 px-4 py-8 text-center text-sm font-bold text-emerald-900">
                                Brak pozycji do odbioru.
                            </div>
                        @endforelse
                    </div>
                </section>
            </div>

            <div class="mt-6 grid gap-5 lg:grid-cols-2">
                <section class="rounded-xl border-2 border-brand-dark bg-brand-light/50 p-5">
                    <span class="text-xs font-black uppercase tracking-[0.16em] text-brand-accent">
                        Twoja strefa operacyjna
                    </span>
                    <h2 class="mt-2 text-lg font-black text-brand-dark">
                        Aktywne stoliki i dostępne miejsca
                    </h2>

                    <div class="mt-8 flex flex-wrap gap-2">
                        @forelse($tables as $table)
                            @php
                                $activeOrder = $table->activeOrders->first();
                                $isDisabled = $table->status !== \App\Models\RestaurantTable::STATUS_FREE && ! $activeOrder;
                                $chipClass = $isDisabled
                                    ? 'border-brand-dark/20 bg-white/50 text-brand-accent'
                                    : 'bg-brand-dark text-brand-light hover:bg-brand-accent';
                            @endphp

                            @if($activeOrder)
                                <a href="{{ route('waiter.orders.show', $activeOrder) }}"
                                   class="rounded-lg px-4 py-2 text-xs font-black transition {{ $chipClass }}">
                                    Stolik {{ $table->number }}
                                </a>
                            @elseif($table->status === \App\Models\RestaurantTable::STATUS_FREE)
                                <a href="{{ route('waiter.orders.create', ['table_id' => $table->id]) }}"
                                   class="rounded-lg px-4 py-2 text-xs font-black transition {{ $chipClass }}">
                                    Stolik {{ $table->number }}
                                </a>
                            @else
                                <span class="rounded-lg border px-4 py-2 text-xs font-black {{ $chipClass }}">
                                    Stolik {{ $table->number }}
                                </span>
                            @endif
                        @empty
                            <span class="text-sm font-bold text-brand-accent">Brak stolików do wyświetlenia.</span>
                        @endforelse
                    </div>
                </section>

                <section class="rounded-xl border-2 border-brand-dark bg-brand-light/50 p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <span class="text-xs font-black uppercase tracking-[0.16em] text-brand-accent">
                                Dzisiejsza zmiana
                            </span>
                            <h2 class="mt-2 text-lg font-black text-brand-dark">
                                {{ $formatShiftDate() }}
                            </h2>
                        </div>
                        <span class="rounded-md border border-emerald-300 bg-emerald-50 px-3 py-1 text-xs font-black uppercase text-emerald-800">
                            W toku
                        </span>
                    </div>

                    <div class="mt-6 grid gap-4 rounded-lg border border-brand-dark/20 bg-white/70 p-4 sm:grid-cols-2">
                        <div>
                            <span class="block text-xs font-black uppercase tracking-wide text-brand-accent">
                                Godziny pracy
                            </span>
                            <strong class="mt-1 block text-xl text-brand-dark">
                                @if($todaySchedule)
                                    {{ $todaySchedule->startsAt() }} - {{ $todaySchedule->endsAt() }}
                                @else
                                    Brak wpisu
                                @endif
                            </strong>
                        </div>
                        <div class="sm:text-right">
                            <span class="block text-xs font-black uppercase tracking-wide text-brand-accent">
                                Manager zmiany
                            </span>
                            <strong class="mt-1 block text-brand-dark">
                                {{ $shiftManager?->name ?? 'Nie przypisano' }}
                            </strong>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </section>
</x-app>
