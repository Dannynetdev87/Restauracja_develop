<x-app>
    <x-slot:title>Aktualne zamówienie kuchni - SmakPrzeszłości</x-slot>

    <section
        id="production-current-refresh"
        class="w-full max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10"
        data-auto-refresh
        data-refresh-url="{{ url()->current() }}"
        data-refresh-interval="8000"
    >
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

        <div class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <span class="text-sm font-bold uppercase text-brand-accent">Panel kuchni</span>
                <h1 class="mt-2 text-3xl font-black text-brand-dark">Aktualne zamówienie</h1>
                <p class="mt-2 max-w-3xl text-sm text-brand-accent">
                    Widok pokazuje najstarsze aktywne zamówienie z pozycjami kuchennymi. Pełną kolejkę znajdziesz w dashboardzie.
                </p>
                <span data-refresh-indicator class="mt-3 inline-flex rounded-md bg-white px-3 py-1 text-xs font-bold text-brand-dark">
                    Odświeżanie co 8 s
                </span>
            </div>

            <a href="{{ route('kitchen.dashboard') }}"
               class="inline-flex w-fit rounded-md border border-brand-dark/20 bg-white px-4 py-2 text-sm font-bold text-brand-dark hover:bg-brand-light">
                Pełny dashboard
            </a>
        </div>

        @if($order)
            <article class="rounded-lg border border-brand-dark/15 bg-white p-6 shadow-sm lg:p-8">
                <div class="mb-6 flex flex-col gap-4 border-b border-brand-dark/10 pb-6 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 class="text-2xl font-black text-brand-dark">Zamówienie #{{ $order->id }}</h2>
                        <p class="mt-1 text-sm text-brand-accent">
                            Stolik {{ $order->table->number }} · otwarte {{ $order->opened_at->format('H:i') }}
                        </p>
                    </div>
                    <span class="w-fit rounded-md bg-brand-light px-3 py-1 text-xs font-bold uppercase text-brand-dark">
                        {{ $order->items->count() }} {{ $order->items->count() === 1 ? 'pozycja' : 'pozycji' }}
                    </span>
                </div>

                <div class="space-y-4">
                    @foreach($order->items as $item)
                        <article class="rounded-lg border border-brand-dark/10 bg-brand-card p-4">
                            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                                <div class="flex gap-4">
                                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-md bg-brand-dark text-sm font-black text-brand-light">
                                        {{ $item->quantity }}x
                                    </span>

                                    <div>
                                        <h3 class="font-black text-brand-dark">{{ $item->menuItem->name }}</h3>
                                        <p class="mt-1 text-sm text-brand-accent">
                                            Status: {{ match ($item->status) {
                                                \App\Models\OrderItem::STATUS_NEW => 'nowe',
                                                \App\Models\OrderItem::STATUS_PREPARING => 'w przygotowaniu',
                                                \App\Models\OrderItem::STATUS_READY => 'gotowe',
                                                default => $item->status,
                                            } }}
                                        </p>

                                        @if($item->notes)
                                            <p class="mt-3 rounded-md bg-white px-3 py-2 text-sm text-brand-dark">
                                                {{ $item->notes }}
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                <div class="w-full md:w-56">
                                    @if($item->status === \App\Models\OrderItem::STATUS_NEW)
                                        <form method="POST" action="{{ route('kitchen.order-items.status', $item) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="{{ \App\Models\OrderItem::STATUS_PREPARING }}">
                                            <input type="hidden" name="redirect_to" value="kitchen.current">
                                            <button type="submit" class="w-full rounded-md bg-brand-dark px-4 py-2 text-sm font-bold text-brand-light hover:bg-brand-accent">
                                                Rozpocznij
                                            </button>
                                        </form>
                                    @elseif($item->status === \App\Models\OrderItem::STATUS_PREPARING)
                                        <form method="POST" action="{{ route('kitchen.order-items.status', $item) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="{{ \App\Models\OrderItem::STATUS_READY }}">
                                            <input type="hidden" name="redirect_to" value="kitchen.current">
                                            <button type="submit" class="w-full rounded-md bg-green-700 px-4 py-2 text-sm font-bold text-white hover:bg-green-800">
                                                Oznacz jako gotowe
                                            </button>
                                        </form>
                                    @else
                                        <span class="block w-full rounded-md bg-green-50 px-4 py-2 text-center text-sm font-bold text-green-800">
                                            Gotowe do odbioru
                                        </span>
                                    @endif

                                    @if(in_array($item->status, [\App\Models\OrderItem::STATUS_NEW, \App\Models\OrderItem::STATUS_PREPARING], true))
                                        <form method="POST" action="{{ route('kitchen.order-items.cancel', $item) }}" class="mt-2">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="redirect_to" value="kitchen.current">
                                            <button type="submit" class="btn-production-cancel">
                                                Nie można przygotować
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </article>
        @else
            <div class="rounded-lg border border-brand-dark/15 bg-white px-6 py-12 text-center shadow-sm">
                <h2 class="text-2xl font-black text-brand-dark">Brak aktywnych pozycji kuchni</h2>
                <p class="mt-2 text-sm text-brand-accent">Nowe zamówienia pojawią się tutaj automatycznie po dodaniu pozycji przez kelnera.</p>
            </div>
        @endif
    </section>
</x-app>
