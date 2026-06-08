<section
    wire:poll.5s
    class="w-full max-w-7xl mx-auto px-4 py-6 sm:px-6 sm:py-10 lg:px-8"
>
    <div class="mb-6 grid grid-cols-3 gap-2 sm:mb-8 sm:flex sm:flex-wrap sm:justify-center sm:gap-4">
        <a href="{{ route('waiter.dashboard') }}"
           class="min-w-0 rounded-xl bg-brand-dark px-2 py-3 text-center text-xs font-black uppercase tracking-wide text-brand-light shadow-sm transition hover:bg-brand-accent sm:min-w-40 sm:px-8 sm:py-4 sm:text-sm">
            Dashboard
        </a>
        <button type="button" disabled
                class="min-w-0 rounded-xl border border-current bg-wheat px-2 py-3 text-center text-xs font-black uppercase tracking-wide text-brand-dark shadow-sm sm:min-w-40 sm:px-8 sm:py-4 sm:text-sm">
            Stoliki
        </button>
        <a href="{{ route('waiter.stats') }}"
           class="min-w-0 rounded-xl bg-brand-dark px-2 py-3 text-center text-xs font-black uppercase tracking-wide text-brand-light shadow-sm transition hover:bg-brand-accent sm:min-w-40 sm:px-8 sm:py-4 sm:text-sm">
            Napiwki
        </a>
    </div>

    <div class="mb-6 flex min-w-0 flex-col gap-2 sm:mb-8">
        <span class="text-sm font-bold uppercase text-brand-accent">Panel kelnera</span>
        <h1 class="text-2xl font-black text-brand-dark sm:text-3xl">Stoliki</h1>
        <p class="max-w-3xl text-sm leading-6 text-brand-accent sm:text-base">
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

    <div class="grid gap-3 sm:grid-cols-2 sm:gap-4 xl:grid-cols-3">
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

            <article wire:key="waiter-table-{{ $table->id }}" class="flex min-w-0 flex-col justify-between rounded-lg border border-brand-dark/15 bg-white p-4 shadow-sm sm:p-5">
                <div>
                    <div class="flex flex-wrap items-start justify-between gap-3 sm:gap-4">
                        <div class="min-w-0">
                            <h2 class="text-xl font-black text-brand-dark sm:text-2xl">Stolik {{ $table->number }}</h2>
                            <p class="mt-1 text-sm text-brand-accent">Miejsca: {{ $table->seats }}</p>
                            @if($table->zone)
                                <p class="mt-1 text-xs font-bold uppercase text-brand-accent">Strefa: {{ $table->zone->name }}</p>
                            @endif
                        </div>
                        <span class="rounded-full px-3 py-1 text-xs font-bold {{ $badgeClass }}">
                            {{ $statuses[$table->status] ?? $table->status }}
                        </span>
                    </div>

                    @if($activeOrder)
                        <div class="mt-4 space-y-3 rounded-lg border border-brand-dark/10 bg-brand-light p-3 text-sm text-brand-dark">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div class="min-w-0">
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

                {{-- Przyciski akcji --}}
                <div class="mt-4 flex flex-col gap-2 sm:mt-5">
                    @if($canOpenOrder)
                        <a href="{{ route('waiter.orders.create', ['table_id' => $table->id]) }}"
                           class="block w-full rounded-md bg-brand-dark px-4 py-3 text-center text-sm font-bold text-brand-light hover:bg-brand-accent sm:py-2">
                            Rozpocznij zamówienie
                        </a>
                    @elseif($isOwnActiveOrder)
                        <a href="{{ route('waiter.orders.show', $activeOrder) }}"
                           class="block w-full rounded-md border border-brand-dark/20 bg-white px-4 py-3 text-center text-sm font-bold text-brand-dark hover:bg-brand-light sm:py-2">
                            Zobacz zamówienie
                        </a>
                        <a href="{{ route('waiter.orders.create', ['table_id' => $table->id]) }}"
                           class="block w-full rounded-md bg-brand-dark px-4 py-3 text-center text-sm font-bold text-brand-light hover:bg-brand-accent sm:py-2">
                            Dodaj pozycje
                        </a>
                    @elseif($activeOrder)
                        <button type="button" disabled
                                class="w-full cursor-not-allowed rounded-md bg-gray-200 px-4 py-3 text-sm font-bold text-gray-600 sm:py-2">
                            Obsługuje inny kelner
                        </button>
                    @else
                        <button type="button" disabled
                                class="w-full cursor-not-allowed rounded-md bg-gray-200 px-4 py-3 text-sm font-bold text-gray-600 sm:py-2">
                            Niedostępny
                        </button>
                    @endif

                    @if($openReportTableId === $table->id)
                        <button
                            type="button"
                            wire:click="closeReportForm"
                            class="inline-flex w-full cursor-pointer items-center justify-center rounded-lg border border-red-200 bg-red-50 px-3 py-3 text-xs font-bold text-red-700 transition-colors hover:bg-red-100 sm:py-2"
                        >
                            Anuluj zgłoszenie
                        </button>

                        <div wire:key="waiter-table-report-form-{{ $table->id }}" class="mt-3 rounded-xl border border-red-200 bg-red-50/60 p-3 sm:p-4">
                            <h4 class="mb-3 text-sm font-black text-red-800">
                                Zgłoś problem - Stolik {{ $table->number }}
                            </h4>

                            <form method="POST" action="{{ route('waiter.tables.report', $table) }}">
                                @csrf

                                <div class="mb-3">
                                    <label class="mb-1 block text-xs font-bold text-red-700">
                                        Typ problemu <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        name="type"
                                        wire:model.live="reportTypes.{{ $table->id }}"
                                        required
                                        class="w-full rounded-lg border border-red-200 bg-white px-3 py-2 text-sm text-brand-dark focus:outline-none focus:ring-2 focus:ring-red-400"
                                    >
                                        <option value="" disabled selected>Wybierz typ</option>
                                        <option value="brudny stolik">Brudny stolik</option>
                                        <option value="brak sztućców">Brak sztućców</option>
                                        <option value="potrzebna pomoc">Potrzebna pomoc managera</option>
                                        <option value="długi czas oczekiwania">Klient czeka zbyt długo</option>
                                        <option value="problem z zamówieniem">Problem z zamówieniem</option>
                                        <option value="inne">Inne</option>
                                    </select>
                                </div>

                                <div class="mb-4">
                                    <label class="mb-1 block text-xs font-bold text-red-700">
                                        Opis (opcjonalnie)
                                    </label>
                                    <textarea
                                        name="message"
                                        wire:model.live.debounce.300ms="reportMessages.{{ $table->id }}"
                                        rows="2"
                                        maxlength="255"
                                        placeholder="Krótki opis problemu..."
                                        class="w-full rounded-lg border border-red-200 bg-white px-3 py-2 text-sm text-brand-dark placeholder-brand-accent/50 focus:outline-none focus:ring-2 focus:ring-red-400 resize-none"
                                    ></textarea>
                                </div>

                                <button
                                    type="submit"
                                    class="w-full rounded-lg bg-red-700 px-4 py-2 text-xs font-black text-white transition-colors hover:bg-red-800"
                                >
                                    Wyślij zgłoszenie do managera
                                </button>

                            </form>
                        </div>
                    @else
                        <button
                            type="button"
                            wire:click="openReportForm({{ $table->id }})"
                            class="inline-flex w-full cursor-pointer items-center justify-center rounded-lg border border-red-200 bg-red-50 px-3 py-3 text-xs font-bold text-red-700 transition-colors hover:bg-red-100 sm:py-2"
                        >
                            Zgłoś problem ze stolikiem
                        </button>
                    @endif

                </div>
            </article>
        @empty
            <div class="rounded-lg border border-brand-dark/15 bg-white p-8 text-center text-brand-accent sm:col-span-2 xl:col-span-3">
                Nie masz aktualnie przypisanych stolików. Skontaktuj się z managerem.
            </div>
        @endforelse
    </div>
</section>
