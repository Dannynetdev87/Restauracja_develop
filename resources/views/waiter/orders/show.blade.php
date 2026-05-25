<x-app>
    <x-slot:title>Zamówienie #{{ $order->id }} - SmakPrzeszłości</x-slot>

    <section class="w-full max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="mb-8 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <a href="{{ route('waiter.tables.index') }}" class="text-sm font-bold text-brand-accent hover:text-brand-dark">Wróć do stolików</a>
                <h1 class="mt-3 text-3xl font-black text-brand-dark">Zamówienie #{{ $order->id }}</h1>
                <p class="mt-1 text-brand-accent">
                    Stolik {{ $order->table->number }} · {{ $order->opened_at->format('d.m.Y H:i') }}
                </p>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row">
                <a href="{{ route('waiter.orders.bill', $order) }}"
                   class="inline-flex w-fit rounded-md border border-brand-dark/20 bg-white px-4 py-2 text-sm font-bold text-brand-dark hover:bg-brand-light">
                    Rachunek
                </a>

                @if($order->canAcceptItems())
                    <a href="{{ route('waiter.orders.create', ['table_id' => $order->table->id]) }}"
                       class="inline-flex w-fit rounded-md bg-brand-dark px-4 py-2 text-sm font-bold text-brand-light hover:bg-brand-accent">
                        Dodaj pozycje
                    </a>
                @endif
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 rounded-lg border border-green-700 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 rounded-lg border border-red-700 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-[1fr_320px]">
            <div class="rounded-lg border border-brand-dark/15 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <span class="text-sm font-bold uppercase text-brand-accent">Pozycje zamówienia</span>
                        <h2 class="mt-2 text-2xl font-black text-brand-dark">Aktualny rachunek</h2>
                    </div>
                    <span class="rounded-md bg-brand-light px-3 py-1 text-xs font-bold uppercase text-brand-dark">
                        {{ $order->status }}
                    </span>
                </div>

                <div class="mt-6 divide-y divide-brand-dark/10">
                    @forelse($order->items as $item)
                        @php
                            $isCancelled = $item->status === \App\Models\OrderItem::STATUS_CANCELLED;
                            $statusLabel = match ($item->status) {
                                \App\Models\OrderItem::STATUS_NEW => 'Nowe',
                                \App\Models\OrderItem::STATUS_PREPARING => 'W przygotowaniu',
                                \App\Models\OrderItem::STATUS_READY => 'Gotowe do wydania',
                                \App\Models\OrderItem::STATUS_DELIVERED => 'Dostarczone',
                                \App\Models\OrderItem::STATUS_CANCELLED => 'Anulowane',
                                default => $item->status,
                            };
                            $statusClass = match ($item->status) {
                                \App\Models\OrderItem::STATUS_READY => 'bg-green-100 text-green-800',
                                \App\Models\OrderItem::STATUS_DELIVERED => 'bg-brand-light text-brand-dark',
                                \App\Models\OrderItem::STATUS_PREPARING => 'bg-yellow-100 text-yellow-800',
                                \App\Models\OrderItem::STATUS_CANCELLED => 'bg-red-100 text-red-800',
                                default => 'bg-gray-100 text-gray-700',
                            };
                        @endphp

                        <div class="grid gap-4 py-4 md:grid-cols-[72px_1fr_160px] md:items-start {{ $isCancelled ? 'opacity-70' : '' }}">
                            <div class="w-fit rounded-md px-3 py-2 text-sm font-black text-brand-light {{ $isCancelled ? 'bg-gray-500' : 'bg-brand-dark' }}">
                                {{ $item->quantity }}x
                            </div>

                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="font-black text-brand-dark {{ $isCancelled ? 'line-through' : '' }}">{{ $item->menuItem->name }}</h3>
                                    <span class="rounded-md px-2.5 py-1 text-xs font-bold {{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                </div>

                                @if($item->notes)
                                    <p class="mt-2 rounded-md bg-brand-light px-3 py-2 text-sm text-brand-dark">
                                        {{ $item->notes }}
                                    </p>
                                @endif

                                @if($item->status === \App\Models\OrderItem::STATUS_READY)
                                    <form method="POST" action="{{ route('waiter.order-items.deliver', $item) }}" class="mt-3">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="rounded-md bg-green-700 px-4 py-2 text-sm font-bold text-white hover:bg-green-800">
                                            Oznacz jako dostarczone
                                        </button>
                                    </form>
                                @endif
                            </div>

                            <div class="text-left md:text-right">
                                <p class="text-sm text-brand-accent">{{ number_format($item->unit_price, 2, ',', ' ') }} zł / szt.</p>
                                <p class="mt-1 text-lg font-black text-brand-dark">
                                    {{ $isCancelled ? '0,00' : number_format($item->subtotal(), 2, ',', ' ') }} zł
                                </p>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-md bg-brand-light px-4 py-3 text-sm text-brand-dark">
                            Zamówienie nie ma jeszcze żadnych pozycji.
                        </div>
                    @endforelse
                </div>
            </div>

            <aside class="h-fit rounded-lg border border-brand-dark/15 bg-white p-5 shadow-sm">
                <span class="text-sm font-bold uppercase text-brand-accent">Podsumowanie</span>
                <div class="mt-4 space-y-3 text-sm text-brand-dark">
                    <div class="flex justify-between gap-4">
                        <span>Stolik</span>
                        <strong>{{ $order->table->number }}</strong>
                    </div>
                    <div class="flex justify-between gap-4">
                        <span>Liczba pozycji</span>
                        <strong>{{ $order->items->where('status', '!=', \App\Models\OrderItem::STATUS_CANCELLED)->sum('quantity') }}</strong>
                    </div>
                    <div class="flex justify-between gap-4 border-t border-brand-dark/10 pt-3 text-lg">
                        <span>Razem</span>
                        <strong>{{ number_format($order->total(), 2, ',', ' ') }} zł</strong>
                    </div>
                </div>
            </aside>
        </div>
    </section>
</x-app>
