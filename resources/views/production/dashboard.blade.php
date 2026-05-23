@php
    $allItems = collect($columns)
        ->flatMap(fn (array $column) => $column['items'])
        ->values();
    $queueOrders = $allItems->groupBy(fn ($item) => $item->order->id);
@endphp

<section class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
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

    <div class="rounded-lg border border-brand-dark/15 bg-white p-6 shadow-sm lg:p-8">
        <div class="mb-8 border-b border-brand-dark/10 pb-6">
            <span class="text-sm font-bold uppercase text-brand-accent">{{ $panelLabel }}</span>
            <h1 class="mt-2 text-3xl font-black text-brand-dark">{{ $title }}</h1>
            <p class="mt-2 max-w-3xl text-sm text-brand-accent">{{ $description }}</p>
        </div>

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
            <div class="space-y-5">
                <div class="grid gap-4 md:grid-cols-3">
                    @foreach($columns as $column)
                        <div class="rounded-lg border border-brand-dark/10 bg-brand-card p-4">
                            <div class="flex items-center justify-between gap-3">
                                <h2 class="text-base font-black text-brand-dark">{{ $column['title'] }}</h2>
                                <span class="rounded-md bg-white px-3 py-1 text-xs font-bold text-brand-dark">
                                    {{ $column['items']->count() }} {{ $column['items']->count() === 1 ? 'pozycja' : 'pozycji' }}
                                </span>
                            </div>
                            <p class="mt-2 text-xs text-brand-accent">{{ $column['description'] }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="grid gap-5 xl:grid-cols-3">
                    @foreach($columns as $status => $column)
                        <section class="rounded-lg border border-brand-dark/15 bg-brand-card p-5">
                            <div class="mb-4">
                                <h2 class="text-xl font-black text-brand-dark">{{ $column['title'] }}</h2>
                                <p class="mt-1 text-sm text-brand-accent">{{ $column['description'] }}</p>
                            </div>

                            <div class="space-y-4">
                                @forelse($column['items'] as $item)
                                    <article class="rounded-lg border border-brand-dark/10 bg-white p-4 shadow-sm">
                                        <div class="flex items-start justify-between gap-4">
                                            <div>
                                                <h3 class="font-black text-brand-dark">Zamówienie #{{ $item->order->id }}</h3>
                                                <p class="mt-1 text-sm text-brand-accent">
                                                    Stolik {{ $item->order->table->number }} · {{ $item->created_at->format('H:i') }}
                                                </p>
                                            </div>
                                            <span class="rounded-md bg-brand-dark px-3 py-2 text-sm font-black text-brand-light">
                                                {{ $item->quantity }}x
                                            </span>
                                        </div>

                                        <div class="mt-4">
                                            <p class="font-black text-brand-dark">{{ $item->menuItem->name }}</p>
                                            @if($item->notes)
                                                <p class="mt-2 rounded-md bg-brand-light px-3 py-2 text-sm text-brand-dark">
                                                    {{ $item->notes }}
                                                </p>
                                            @endif
                                        </div>

                                        <div class="mt-4 flex flex-col gap-2">
                                            @if($item->status === \App\Models\OrderItem::STATUS_NEW)
                                                <form method="POST" action="{{ route($statusRouteName, $item) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="{{ \App\Models\OrderItem::STATUS_PREPARING }}">
                                                    <button type="submit" class="w-full rounded-md bg-brand-dark px-4 py-2 text-sm font-bold text-brand-light hover:bg-brand-accent">
                                                        Rozpocznij przygotowanie
                                                    </button>
                                                </form>
                                            @elseif($item->status === \App\Models\OrderItem::STATUS_PREPARING)
                                                <form method="POST" action="{{ route($statusRouteName, $item) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="{{ \App\Models\OrderItem::STATUS_READY }}">
                                                    <button type="submit" class="w-full rounded-md bg-green-700 px-4 py-2 text-sm font-bold text-white hover:bg-green-800">
                                                        Oznacz jako gotowe
                                                    </button>
                                                </form>
                                            @else
                                                <span class="block w-full rounded-md bg-green-50 px-4 py-2 text-center text-sm font-bold text-green-800">
                                                    Gotowe do odbioru
                                                </span>
                                            @endif
                                        </div>
                                    </article>
                                @empty
                                    <div class="rounded-lg border border-dashed border-brand-dark/20 bg-white px-4 py-8 text-center text-sm font-semibold text-brand-accent">
                                        Brak pozycji w tej sekcji.
                                    </div>
                                @endforelse
                            </div>
                        </section>
                    @endforeach
                </div>
            </div>

            <aside class="space-y-4">
                <div class="rounded-lg bg-brand-dark p-5 text-brand-light shadow-sm">
                    <h2 class="text-sm font-black uppercase tracking-wide">Aktywne zlecenia</h2>
                    <p class="mt-1 text-xs text-brand-light/80">{{ $queueDescription }}</p>
                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <div class="rounded-md bg-white/10 px-3 py-2">
                            <span class="block text-xs text-brand-light/70">Zamówienia</span>
                            <strong class="text-xl text-white">{{ $queueOrders->count() }}</strong>
                        </div>
                        <div class="rounded-md bg-white/10 px-3 py-2">
                            <span class="block text-xs text-brand-light/70">Pozycje</span>
                            <strong class="text-xl text-white">{{ $allItems->count() }}</strong>
                        </div>
                    </div>
                </div>

                <div class="border-t border-brand-dark/10 pt-4">
                    <h2 class="mb-3 text-xs font-black uppercase tracking-wide text-brand-accent">Kolejka zamówień</h2>

                    <div class="space-y-3">
                        @forelse($queueOrders as $orderItems)
                            @php
                                $order = $orderItems->first()->order;
                            @endphp
                            <div class="rounded-lg border border-brand-dark/10 bg-white p-4 shadow-sm">
                                <div class="flex items-center justify-between gap-3">
                                    <h3 class="font-black text-brand-dark">Zamówienie #{{ $order->id }}</h3>
                                    <span class="rounded-md bg-amber-50 px-2 py-1 text-xs font-bold text-amber-700">
                                        {{ $orderItems->count() }} {{ $orderItems->count() === 1 ? 'pozycja' : 'pozycji' }}
                                    </span>
                                </div>
                                <p class="mt-2 text-sm text-brand-accent">
                                    Stolik {{ $order->table->number }} · od {{ $order->opened_at->format('H:i') }}
                                </p>
                            </div>
                        @empty
                            <div class="rounded-lg border border-dashed border-brand-dark/20 bg-white px-4 py-8 text-center text-sm font-semibold text-brand-accent">
                                Brak aktywnych zleceń.
                            </div>
                        @endforelse
                    </div>
                </div>
            </aside>
        </div>
    </div>
</section>
