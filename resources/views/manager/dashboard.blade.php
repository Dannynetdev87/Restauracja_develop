<x-app>
    <x-slot:title>Panel Managera - SmakPrzeszłości</x-slot>

    <section id="manager-dashboard" class="w-full max-w-7xl mx-auto px-4 py-10 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-col gap-5 border-b border-brand-dark/10 pb-6 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <span class="text-sm font-bold uppercase text-brand-accent">Centrum dowodzenia</span>
                <h1 class="mt-1 text-3xl font-black tracking-tight text-brand-dark">Panel Managera</h1>
                <p class="mt-1 text-sm text-brand-accent">Bieżący podgląd statystyk, stanu sal oraz pracy restauracji.</p>
            </div>

            <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:flex-wrap">
                <a href="{{ route('manager.podglad') }}"
                   class="rounded-md border border-brand-dark/20 bg-white px-4 py-2 text-center text-sm font-bold text-brand-dark hover:bg-brand-light">
                    Zarządzanie menu
                </a>
                <a href="{{ route('manager.tables.index') }}"
                   class="rounded-md border border-brand-dark/20 bg-white px-4 py-2 text-center text-sm font-bold text-brand-dark hover:bg-brand-light">
                    Zarządzanie stolikami
                </a>
                <a href="{{ route('manager.discount-codes.index') }}"
                   class="rounded-md border border-brand-dark/20 bg-white px-4 py-2 text-center text-sm font-bold text-brand-dark hover:bg-brand-light">
                    Kody rabatowe
                </a>
                <a href="{{ route('manager.orders.history') }}"
                   class="rounded-md bg-brand-dark px-4 py-2 text-center text-sm font-bold text-brand-light hover:bg-brand-accent">
                    Historia zamówień
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-bold text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="space-y-8">

            {{-- Statystyki główne --}}
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border border-brand-dark/15 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase text-brand-accent">Sprzedaż (Dziś)</span>
                        <span class="rounded-full bg-green-50 px-2 py-0.5 text-[10px] font-bold text-green-600">Live</span>
                    </div>
                    <div class="mt-4 flex items-baseline gap-1 overflow-hidden">
                        <span class="truncate text-3xl font-black text-brand-dark">{{ number_format((float) $todaySales, 2, ',', ' ') }}</span>
                        <span class="shrink-0 text-sm font-bold text-brand-accent">zł</span>
                    </div>
                </div>

                <div class="rounded-xl border border-brand-dark/15 bg-white p-5 shadow-sm">
                    <span class="text-xs font-bold uppercase text-brand-accent">Zamówienia (Dziś)</span>
                    <div class="mt-4">
                        <span class="text-3xl font-black text-brand-dark">{{ $todayOrdersCount }}</span>
                    </div>
                </div>

                <div class="rounded-xl border border-brand-dark/15 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase text-brand-accent">Aktywne zamówienia</span>
                        <span class="flex h-2 w-2 animate-pulse rounded-full bg-amber-500"></span>
                    </div>
                    <div class="mt-4">
                        <span class="text-3xl font-black text-brand-dark">{{ $activeOrdersCount }}</span>
                    </div>
                </div>

                <div class="rounded-xl border border-brand-dark/15 bg-white p-5 shadow-sm">
                    <span class="text-xs font-bold uppercase text-brand-accent">Opłacone zamówienia</span>
                    <div class="mt-4">
                        <span class="text-3xl font-black text-brand-dark">{{ $paidOrdersCount }}</span>
                    </div>
                </div>
            </div>

            {{-- Stan stolików --}}
            <div>
                <h2 class="mb-4 text-xl font-bold text-brand-dark">Stan sali i stolików</h2>
                <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                    <div class="flex items-center justify-between rounded-xl border border-brand-dark/10 bg-white p-4 shadow-sm">
                        <div>
                            <p class="text-xs font-bold uppercase text-brand-accent">Wolne</p>
                            <p class="mt-1 text-2xl font-black text-brand-dark">{{ $freeTablesCount }}</p>
                        </div>
                        <span class="h-3 w-3 rounded-full bg-green-500"></span>
                    </div>
                    <div class="flex items-center justify-between rounded-xl border border-brand-dark/10 bg-white p-4 shadow-sm">
                        <div>
                            <p class="text-xs font-bold uppercase text-brand-accent">Zajęte</p>
                            <p class="mt-1 text-2xl font-black text-brand-dark">{{ $occupiedTablesCount }}</p>
                        </div>
                        <span class="h-3 w-3 rounded-full bg-red-500"></span>
                    </div>
                    <div class="flex items-center justify-between rounded-xl border border-brand-dark/10 bg-white p-4 shadow-sm">
                        <div>
                            <p class="text-xs font-bold uppercase text-brand-accent">Zarezerwowane</p>
                            <p class="mt-1 text-2xl font-black text-brand-dark">{{ $reservedTablesCount }}</p>
                        </div>
                        <span class="h-3 w-3 rounded-full bg-amber-500"></span>
                    </div>
                    <div class="flex items-center justify-between rounded-xl border border-brand-dark/10 bg-white p-4 shadow-sm">
                        <div>
                            <p class="text-xs font-bold uppercase text-brand-accent">Wyłączone z użytku</p>
                            <p class="mt-1 text-2xl font-black text-brand-dark">{{ $inactiveTablesCount }}</p>
                        </div>
                        <span class="h-3 w-3 rounded-full bg-gray-400"></span>
                    </div>
                </div>
            </div>

            {{-- Zgłoszenia z sali --}}
            <section class="rounded-xl border border-red-200 bg-white p-5 shadow-sm">
                <div class="mb-5 flex items-center justify-between gap-3 border-b border-brand-dark/10 pb-4">
                    <div>
                        <span class="text-xs font-bold uppercase text-red-700">Zgłoszenia kelnerów</span>
                        <h2 class="mt-1 text-xl font-black text-brand-dark">Problemy przy stolikach</h2>
                    </div>
                    <span class="rounded-full bg-red-50 px-3 py-1 text-xs font-bold text-red-800">
                        {{ $reports->count() }} {{ $reports->count() === 1 ? 'otwarte' : 'otwartych' }}
                    </span>
                </div>

                <div class="space-y-3">
                    @forelse($reports as $report)
                        <div class="rounded-xl border border-red-100 bg-red-50/40 p-4">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="flex-1 min-w-0">
                                    <div class="flex flex-wrap items-center gap-2 mb-1">
                                        <span class="font-black text-brand-dark">
                                            Stolik #{{ $report->table->number }}
                                        </span>
                                        <span class="rounded-md bg-red-100 px-2 py-0.5 text-xs font-bold text-red-800">
                                            {{ $report->type }}
                                        </span>
                                    </div>
                                    <p class="truncate text-sm text-brand-accent">
                                        Kelner: <strong class="text-brand-dark">{{ $report->waiter->full_name }}</strong>
                                        · <span title="{{ $report->created_at }}">{{ $report->created_at->diffForHumans() }}</span>
                                    </p>
                                    @if($report->message)
                                        <p class="mt-2 rounded-lg bg-white px-3 py-2 text-sm text-brand-dark border border-red-100">
                                            {{ $report->message }}
                                        </p>
                                    @endif
                                </div>

                                <form
                                    method="POST"
                                    action="{{ route('manager.reports.resolve', $report) }}"
                                    class="w-full shrink-0 sm:w-auto"
                                    onsubmit="return confirm('Oznaczyć zgłoszenie jako rozwiązane?')"
                                >
                                    @csrf
                                    @method('PATCH')
                                    <button
                                        type="submit"
                                        class="w-full rounded-lg bg-green-700 px-3 py-2 text-xs font-black text-white hover:bg-green-800 transition-colors"
                                    >
                                        Rozwiązane
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-xl border border-dashed border-brand-dark/20 px-4 py-8 text-center text-sm font-semibold text-brand-accent">
                            Brak otwartych zgłoszeń.
                        </div>
                    @endforelse
                </div>
            </section>

            {{-- Ostatnie zamówienia + Do sprawdzenia + Top menu --}}
            <div class="grid gap-6 xl:grid-cols-[minmax(0,1.45fr)_minmax(320px,0.85fr)]">
                <section class="rounded-xl border border-brand-dark/15 bg-white p-5 shadow-sm">
                    <div class="mb-5 border-b border-brand-dark/10 pb-4">
                        <span class="text-xs font-bold uppercase text-brand-accent">Ostatnie zamówienia</span>
                        <h2 class="mt-1 text-xl font-black text-brand-dark">Historia operacyjna</h2>
                    </div>
                    <div class="space-y-3">
                        @forelse($recentOrders as $order)
                            <div class="grid gap-3 rounded-xl border border-brand-dark/10 bg-brand-card p-4 md:grid-cols-[1fr_auto] md:items-center">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="font-black text-brand-dark">Zamówienie #{{ $order->id }}</h3>
                                        <span class="rounded-md bg-white px-2 py-1 text-xs font-bold uppercase text-brand-accent">{{ $order->status }}</span>
                                    </div>
                                    <p class="mt-1 text-sm text-brand-accent">
                                        Stolik {{ $order->table->number }} · kelner: {{ $order->waiter->full_name }}
                                    </p>
                                </div>
                                <div class="text-left md:text-right">
                                    <span class="block text-xs font-bold uppercase text-brand-accent">Suma</span>
                                    <strong class="text-lg text-brand-dark">{{ number_format($order->total(), 2, ',', ' ') }} zł</strong>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-xl border border-dashed border-brand-dark/20 px-4 py-8 text-center text-sm font-semibold text-brand-accent">
                                Brak zamówień do wyświetlenia.
                            </div>
                        @endforelse
                    </div>
                </section>

                <div class="space-y-6">
                    <section class="rounded-xl border border-amber-700/20 bg-white p-5 shadow-sm">
                        <div class="mb-5 flex items-center justify-between gap-3 border-b border-brand-dark/10 pb-4">
                            <div>
                                <span class="text-xs font-bold uppercase text-amber-700">Wymagające uwagi</span>
                                <h2 class="mt-1 text-xl font-black text-brand-dark">Do sprawdzenia</h2>
                            </div>
                            <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-800">
                                {{ $attentionOrders->count() }}
                            </span>
                        </div>
                        <div class="space-y-3">
                            @forelse($attentionOrders as $order)
                                @php
                                    $minutesOpen = $order->opened_at ? (int) round($order->opened_at->diffInMinutes(now())) : 0;
                                    $isUnpaidServed = $order->status === \App\Models\Order::STATUS_SERVED
                                        && ! $order->payments->contains('status', \App\Models\Payment::STATUS_PAID);
                                    $reason = $isUnpaidServed ? 'Nieopłacone' : 'Długo otwarte';
                                @endphp
                                <div class="rounded-xl border border-amber-700/20 bg-amber-50/60 p-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <h3 class="font-black text-brand-dark">#{{ $order->id }}</h3>
                                            <p class="mt-1 text-sm text-brand-accent">Stolik {{ $order->table->number }}</p>
                                        </div>
                                        <span class="rounded-md bg-white px-2 py-1 text-xs font-bold uppercase text-amber-800">{{ $reason }}</span>
                                    </div>
                                    <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-sm text-brand-dark">
                                        <span>{{ $minutesOpen }} min</span>
                                        <span>{{ $order->status }}</span>
                                        <strong>{{ number_format($order->total(), 2, ',', ' ') }} zł</strong>
                                    </div>
                                </div>
                            @empty
                                <div class="rounded-xl border border-dashed border-brand-dark/20 px-4 py-8 text-center text-sm font-semibold text-brand-accent">
                                    Brak zamówień wymagających uwagi.
                                </div>
                            @endforelse
                        </div>
                    </section>

                    <aside class="rounded-xl border border-brand-dark/15 bg-white p-5 shadow-sm">
                        <div class="mb-5 border-b border-brand-dark/10 pb-4">
                            <span class="text-xs font-bold uppercase text-brand-accent">Sprzedaż menu</span>
                            <h2 class="mt-1 text-xl font-black text-brand-dark">Najczęściej zamawiane dzisiaj</h2>
                        </div>
                        <div class="space-y-3">
                            @forelse($topItems as $item)
                                <div class="flex items-center justify-between gap-4 rounded-xl border border-brand-dark/10 bg-brand-card px-4 py-3">
                                    <span class="font-bold text-brand-dark">{{ $item->menuItem->name }}</span>
                                    <span class="rounded-md bg-brand-dark px-2.5 py-1 text-xs font-black text-brand-light">{{ (int) $item->quantity_sold }} szt.</span>
                                </div>
                            @empty
                                <div class="rounded-xl border border-dashed border-brand-dark/20 px-4 py-8 text-center text-sm font-semibold text-brand-accent">
                                    Brak sprzedaży z dzisiejszego dnia.
                                </div>
                            @endforelse
                        </div>
                    </aside>
                </div>
            </div>

        </div>
    </section>
</x-app>
