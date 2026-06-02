@php
    $allItems = collect($columns)
        ->flatMap(fn (array $column) => $column['items'])
        ->values();
    $queueOrders = $allItems
        ->groupBy(fn ($item) => $item->order->id)
        ->sortKeysDesc();
    $formatMinutes = fn (int $minutes) => $minutes === 1 ? '1 min' : $minutes.' min';
    $formatItemsCount = fn (int $count) => $count === 1
        ? '1 pozycja'
        : ($count >= 2 && $count <= 4 ? $count.' pozycje' : $count.' pozycji');
    $columnFrameClass = [
        \App\Models\OrderItem::STATUS_NEW => 'border-brand-dark',
        \App\Models\OrderItem::STATUS_PREPARING => 'border-amber-800',
        \App\Models\OrderItem::STATUS_READY => 'border-emerald-900',
    ];
    $columnHeadingClass = [
        \App\Models\OrderItem::STATUS_NEW => 'border-brand-dark text-brand-dark',
        \App\Models\OrderItem::STATUS_PREPARING => 'border-amber-800 text-amber-950',
        \App\Models\OrderItem::STATUS_READY => 'border-emerald-900 text-emerald-950',
    ];
@endphp

<section
    wire:poll.5s
    class="{{ $containerClass ?? 'w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10' }}"
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

    <div class="rounded-xl border border-brand-dark/15 bg-white/35 p-5 shadow-sm lg:p-7">
        <div class="mb-8 border-b-2 border-brand-dark/20 pb-6">
            <span class="text-sm font-bold uppercase text-brand-accent">{{ $panelLabel }}</span>
            <div class="mt-2 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <h1 class="text-3xl font-black text-brand-dark">{{ $title }}</h1>
                <span class="w-fit rounded-md bg-brand-light px-3 py-1 text-xs font-bold text-brand-dark">
                    Odświeżanie co 5 s
                </span>
            </div>
            <p class="mt-2 max-w-3xl text-sm text-brand-accent">{{ $description }}</p>
        </div>

        <div class="flex flex-col gap-6 lg:grid lg:grid-cols-[minmax(0,1fr)_320px]">
            <div class="space-y-5 order-2 lg:order-none">
                <div class="grid gap-4 md:grid-cols-3">
                    @foreach($columns as $column)
                        <div class="rounded-xl border border-brand-dark/15 bg-white/80 p-4 shadow-sm">
                            <div class="flex items-center justify-between gap-3">
                                <h2 class="text-base font-black text-brand-dark">{{ $column['title'] }}</h2>
                                <span class="rounded-md bg-white px-3 py-1 text-xs font-bold text-brand-dark">
                                    {{ $formatItemsCount($column['items']->count()) }}
                                </span>
                            </div>
                            <p class="mt-2 text-xs text-brand-accent">{{ $column['description'] }}</p>
                        </div>
                    @endforeach
                </div>

                {{--Kreska na mobile--}}
                <hr class="border-t-2 border-brand-dark/20 my-4 md:hidden">

                <div class="grid gap-5 xl:grid-cols-3">
                    @foreach($columns as $status => $column)
                        @php
                            $groupedItems = $column['items']
                                ->groupBy(fn ($item) => $item->order->id)
                                ->sortKeysDesc();
                        @endphp

                        <section class="rounded-xl border-2 bg-brand-light/40 p-5 {{ $columnFrameClass[$status] ?? 'border-brand-dark' }}">
                            <div class="mb-4">
                                <h2 class="border-b-2 pb-3 text-center text-sm font-black uppercase tracking-[0.14em] {{ $columnHeadingClass[$status] ?? 'border-brand-dark text-brand-dark' }}">
                                    {{ $column['title'] }}
                                </h2>
                                <p class="mt-1 text-sm text-brand-accent">{{ $column['description'] }}</p>
                            </div>

                            <div class="space-y-4">
                                @forelse($groupedItems as $orderItems)
                                    @php
                                        $order = $orderItems->first()->order;
                                        $oldestItem = $orderItems->sortBy('created_at')->first();
                                        $oldestItemAt = $oldestItem?->created_at;
                                        $orderWaitingMinutes = $oldestItemAt ? (int) round($oldestItemAt->diffInMinutes(now())) : 0;
                                    @endphp

                                    <article class="rounded-lg border border-brand-dark/20 bg-white/85 p-4 shadow-sm">
                                        <div class="mb-4 flex items-start justify-between gap-4 border-b border-brand-dark/10 pb-3">
                                            <div>
                                                <h3 class="font-black text-brand-dark">Zamówienie #{{ $order->id }}</h3>
                                                <p class="mt-1 text-sm text-brand-accent">
                                                    Stolik {{ $order->table->number }} · {{ $formatItemsCount($orderItems->count()) }}
                                                </p>
                                            </div>
                                            <span class="rounded-md bg-amber-50 px-2 py-1 text-xs font-bold text-amber-700">
                                                {{ $formatMinutes($orderWaitingMinutes) }}
                                            </span>
                                        </div>

                                        <div class="space-y-4">
                                            @foreach($orderItems as $item)
                                                @php
                                                    $placedAt = $item->created_at;
                                                    $waitingMinutes = $placedAt ? (int) round($placedAt->diffInMinutes(now())) : 0;
                                                    $isDelayed = $waitingMinutes >= 10 && $item->status !== \App\Models\OrderItem::STATUS_READY;
                                                    $startedHistory = $item->statusHistory->firstWhere('new_status', \App\Models\OrderItem::STATUS_PREPARING);
                                                    $startedAt = $startedHistory?->created_at;
                                                @endphp

                                                <section class="rounded-lg border border-brand-dark/10 bg-white/70 p-4">
                                                    <div class="flex items-start justify-between gap-3">
                                                        <div class="min-w-0">
                                                            <div class="flex flex-wrap items-center gap-2">
                                                                <span class="rounded-md bg-brand-dark px-2.5 py-1 text-xs font-black text-brand-light">
                                                                    {{ $item->quantity }}x
                                                                </span>
                                                                <p class="font-black text-brand-dark">{{ $item->menuItem->name }}</p>
                                                            </div>

                                                            @if($item->notes)
                                                                <p class="mt-2 rounded-md bg-white px-3 py-2 text-sm text-brand-dark">
                                                                    {{ $item->notes }}
                                                                </p>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    <div class="mt-3 grid gap-2 text-xs font-semibold text-brand-accent sm:grid-cols-2">
                                                        <div class="rounded-md bg-white px-3 py-2">
                                                            Godzina złożenia:
                                                            <strong class="text-brand-dark">{{ $placedAt?->format('H:i') ?? '--:--' }}</strong>
                                                        </div>
                                                        <div class="rounded-md px-3 py-2 {{ $isDelayed ? 'bg-red-50 text-red-700' : 'bg-white text-brand-accent' }}">
                                                            Czas oczekiwania:
                                                            <strong>{{ $formatMinutes($waitingMinutes) }}</strong>
                                                        </div>
                                                        @if($startedAt)
                                                            <div class="rounded-md bg-green-50 px-3 py-2 text-green-800 sm:col-span-2">
                                                                Start przygotowania:
                                                                <strong>{{ $startedAt->format('H:i') }}</strong>
                                                            </div>
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

                                                        @if(isset($selectCurrentRouteName))
                                                            <form method="POST" action="{{ route($selectCurrentRouteName, $item) }}">
                                                                @csrf
                                                                <button type="submit" class="w-full rounded-md border border-brand-dark/20 bg-white px-4 py-2 text-sm font-bold text-brand-dark hover:bg-brand-light">
                                                                    Przenieś do aktualnych
                                                                </button>
                                                            </form>
                                                        @endif

                                                        @if(isset($cancelRouteName) && in_array($item->status, [\App\Models\OrderItem::STATUS_NEW, \App\Models\OrderItem::STATUS_PREPARING], true))
                                                            <form method="POST" action="{{ route($cancelRouteName, $item) }}">
                                                                @csrf
                                                                @method('PATCH')
                                                                <button type="submit" class="btn-production-cancel">
                                                                    Nie można przygotować
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                </section>
                                            @endforeach
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

            {{--Spacer dla tabletów--}}
            <hr class="border-t-2 border-brand-dark/20 my-2 hidden md:block lg:hidden">

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
                                $oldestItem = $orderItems->sortBy('created_at')->first();
                                $oldestItemAt = $oldestItem?->created_at;
                                $waitingMinutes = $oldestItemAt ? (int) round($oldestItemAt->diffInMinutes(now())) : 0;
                                $isDelayed = $waitingMinutes >= 10;
                            @endphp
                            <div class="rounded-lg border border-brand-dark/10 bg-white p-4 shadow-sm">
                                <div class="flex items-center justify-between gap-3">
                                    <h3 class="font-black text-brand-dark">Zamówienie #{{ $order->id }}</h3>
                                    <span class="rounded-md bg-amber-50 px-2 py-1 text-xs font-bold text-amber-700">
                                        {{ $formatItemsCount($orderItems->count()) }}
                                    </span>
                                </div>
                                <div class="mt-2 flex flex-wrap items-center justify-between gap-2 text-sm text-brand-accent">
                                    <span>Stolik {{ $order->table->number }} · od {{ $oldestItemAt?->format('H:i') ?? '--:--' }}</span>
                                    <span class="font-bold {{ $isDelayed ? 'text-red-700' : 'text-brand-accent' }}">Czeka {{ $formatMinutes($waitingMinutes) }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-lg border border-dashed border-brand-dark/20 bg-white px-4 py-8 text-center text-sm font-semibold text-brand-accent">
                                Brak aktywnych zlecen.
                            </div>
                        @endforelse
                    </div>
                </div>
            </aside>
        </div>
    </div>
</section>
