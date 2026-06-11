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

<style>
    @keyframes itemPop {
        0% {
            opacity: 0;
            transform: translateY(8px) scale(0.98);
        }

        100% {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    @keyframes buttonGlow {
        0%, 100% {
            transform: scale(1);
            box-shadow: 0 1px 2px 0 rgba(4, 120, 87, 0.3);
        }

        50% {
            transform: scale(1.01);
            box-shadow: 0 4px 10px 0 rgba(4, 120, 87, 0.5);
        }
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    .animate-item-pop {
        animation: itemPop 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    .animate-button-glow {
        animation: buttonGlow 2s infinite ease-in-out;
    }

    .animate-fade-in {
        animation: fadeIn 0.3s ease-out forwards;
    }
</style>

<section
    id="waiter-dashboard-refresh"
    wire:poll.visible.5s
    class="w-full px-4 py-6 sm:px-6 sm:py-8 lg:px-8"
>
    <div class="mx-auto max-w-6xl min-w-0">
        @if(session('success'))
            <div class="mb-6 rounded-lg border border-green-700 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800 animate-fade-in">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 rounded-lg border border-red-700 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800 animate-fade-in">
                {{ session('error') }}
            </div>
        @endif

        <div class="mb-6 grid grid-cols-3 gap-2 sm:mb-8 sm:flex sm:flex-wrap sm:justify-center sm:gap-4">
            <button type="button" disabled
               class="min-w-0 rounded-xl border border-current bg-wheat px-2 py-3 text-center text-xs font-black uppercase tracking-wide text-brand-dark shadow-sm sm:min-w-40 sm:px-8 sm:py-4 sm:text-sm">
                Dashboard
            </button>
            <a href="{{ route('waiter.tables.index') }}"
               class="min-w-0 rounded-xl bg-brand-dark px-2 py-3 text-center text-xs font-black uppercase tracking-wide text-brand-light shadow-sm transition-all duration-200 ease-out hover:bg-brand-accent hover:shadow-md active:scale-95 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-accent/40 sm:min-w-40 sm:px-8 sm:py-4 sm:text-sm">
                Stoliki
            </a>
            <a href="{{ route('waiter.stats') }}"
               class="min-w-0 rounded-xl bg-brand-dark px-2 py-3 text-center text-xs font-black uppercase tracking-wide text-brand-light shadow-sm transition-all duration-200 ease-out hover:bg-brand-accent hover:shadow-md active:scale-95 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-accent/40 sm:min-w-40 sm:px-8 sm:py-4 sm:text-sm">
                Napiwki
            </a>
        </div>

        <div class="grid gap-4 lg:grid-cols-3 lg:gap-5">
            <section class="rounded-xl border-2 border-brand-dark bg-brand-light/50 p-4 sm:p-5">
                <h2 class="border-b-2 border-brand-dark pb-3 text-center text-sm font-black uppercase tracking-wide text-brand-dark sm:tracking-[0.16em]">
                    W realizacji
                </h2>

                <div class="mt-4 space-y-3">
                    @forelse($inProgressItems->take(4) as $item)
                        <article wire:key="waiter-dashboard-progress-{{ $item->id }}" class="animate-item-pop rounded-lg border border-brand-dark/20 bg-white/80 p-4 shadow-sm transition-all duration-200 hover:scale-[1.01] hover:shadow-md">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h3 class="break-words font-black text-brand-dark">{{ $formatItemTitle($item) }}</h3>
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

            <section class="rounded-xl border-2 border-red-900 bg-red-50/20 p-4 sm:p-5">
                <h2 class="border-b-2 border-red-900 pb-3 text-center text-sm font-black uppercase tracking-wide text-red-950 sm:tracking-[0.16em]">
                    Anulowane / braki
                </h2>

                <div class="mt-4 space-y-3">
                    @forelse($cancelledItems->take(4) as $item)
                        <article wire:key="waiter-dashboard-cancelled-{{ $item->id }}" class="animate-item-pop rounded-lg border border-red-200 bg-white/90 p-4 shadow-sm transition-all duration-200 hover:scale-[1.01]">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h3 class="break-words font-black text-brand-dark line-through decoration-red-700/60">
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
                               class="mt-3 block rounded-md bg-brand-dark px-4 py-2 text-center text-xs font-black uppercase tracking-wide text-brand-light transition-all duration-200 ease-out hover:bg-brand-accent hover:shadow-md active:scale-95 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-accent/40">
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

            <section class="rounded-xl border-2 border-emerald-900 bg-emerald-50/20 p-4 transition-all duration-300 sm:p-5">
                <h2 class="border-b-2 border-emerald-900 pb-3 text-center text-sm font-black uppercase tracking-wide text-emerald-950 sm:tracking-[0.16em]">
                    Do odbioru
                </h2>

                <div class="mt-4 space-y-3">
                    @forelse($readyItems->take(4) as $item)
                        <article wire:key="waiter-dashboard-ready-{{ $item->id }}" class="animate-item-pop rounded-lg border border-emerald-200 bg-white/90 p-4 shadow-sm transition-all duration-200 hover:scale-[1.01] ring-1 ring-emerald-500/10">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h3 class="break-words font-black text-brand-dark">{{ $formatItemTitle($item) }}</h3>
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
                                        class="w-full rounded-md bg-brand-dark px-4 py-2 text-xs font-black uppercase tracking-wide text-brand-light transition-all duration-200 ease-out hover:bg-brand-accent hover:shadow-md active:scale-95 animate-button-glow focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-accent/40">
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

        <div class="mt-5 grid gap-4 lg:mt-6 lg:grid-cols-2 lg:gap-5">
            <section class="rounded-xl border-2 border-brand-dark bg-brand-light/50 p-4 sm:p-5">
                <span class="text-xs font-black uppercase tracking-wide text-brand-accent sm:tracking-[0.16em]">
                    Twoja strefa operacyjna
                </span>
                <h2 class="mt-2 text-lg font-black text-brand-dark">
                    Aktywne stoliki i dostępne miejsca
                </h2>

                <div class="mt-5 flex flex-wrap gap-2 sm:mt-8">
                    @forelse($tables as $table)
                        @php
                            $activeOrder = $table->activeOrders->first();
                            $isDisabled = $table->status !== \App\Models\RestaurantTable::STATUS_FREE && ! $activeOrder;
                            $chipClass = $isDisabled
                                ? 'border-brand-dark/20 bg-white/50 text-brand-accent'
                                : 'bg-brand-dark text-brand-light hover:bg-brand-accent';
                        @endphp

                        @if($activeOrder)
                            <a wire:key="waiter-dashboard-table-order-{{ $table->id }}" href="{{ route('waiter.orders.show', $activeOrder) }}"
                               class="rounded-lg px-4 py-2 text-xs font-black transition-all duration-200 ease-out hover:shadow-md active:scale-95 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-accent/40 {{ $chipClass }}">
                                Stolik {{ $table->number }}
                            </a>
                        @elseif($table->status === \App\Models\RestaurantTable::STATUS_FREE)
                            <a wire:key="waiter-dashboard-table-free-{{ $table->id }}" href="{{ route('waiter.orders.create', ['table_id' => $table->id]) }}"
                               class="rounded-lg px-4 py-2 text-xs font-black transition-all duration-200 ease-out hover:shadow-md active:scale-95 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-accent/40 {{ $chipClass }}">
                                Stolik {{ $table->number }}
                            </a>
                        @else
                            <span wire:key="waiter-dashboard-table-disabled-{{ $table->id }}" class="rounded-lg border px-4 py-2 text-xs font-black {{ $chipClass }}">
                                Stolik {{ $table->number }}
                            </span>
                        @endif
                    @empty
                        <span class="text-sm font-bold text-brand-accent">Nie masz aktualnie przypisanych stolików. Skontaktuj się z managerem.</span>
                    @endforelse
                </div>
            </section>

            <section class="rounded-xl border-2 border-brand-dark bg-brand-light/50 p-4 sm:p-5">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <span class="text-xs font-black uppercase tracking-wide text-brand-accent sm:tracking-[0.16em]">
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
