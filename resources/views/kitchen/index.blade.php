<x-app>
    <x-slot:title>Panel kuchni - SmakPrzeszłości</x-slot>

    <section class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="mb-8 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <span class="text-sm font-bold uppercase text-brand-accent">Panel kuchni</span>
                <h1 class="mt-2 text-3xl font-black text-brand-dark">Pozycje do przygotowania</h1>
                <p class="mt-2 max-w-3xl text-brand-accent">
                    Widok obejmuje tylko pozycje menu przypisane do kuchni. Każda zmiana statusu zapisuje się w historii pozycji.
                </p>
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

        <div class="grid gap-6 xl:grid-cols-3">
            @foreach($columns as $status => $column)
                <section class="rounded-lg border border-brand-dark/15 bg-white p-5 shadow-sm">
                    <div class="mb-5">
                        <div class="flex flex-wrap items-center gap-3">
                            <h2 class="text-xl font-black text-brand-dark">{{ $column['title'] }}</h2>
                            <span class="rounded-md bg-brand-light px-3 py-1 text-xs font-bold uppercase text-brand-dark">
                                {{ $column['items']->count() }} {{ $column['items']->count() === 1 ? 'pozycja' : 'pozycji' }}
                            </span>
                        </div>
                        <div>
                            <p class="mt-1 text-sm text-brand-accent">{{ $column['description'] }}</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        @forelse($column['items'] as $item)
                            <article class="rounded-lg border border-brand-dark/10 bg-brand-card p-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h3 class="font-black text-brand-dark">Zamówienie #{{ $item->order->id }}</h3>
                                        <p class="mt-1 text-sm text-brand-accent">
                                            Stolik {{ $item->order->table->number }} · {{ $item->created_at->format('H:i') }}
                                        </p>
                                    </div>
                                    <span class="rounded-md bg-white px-3 py-2 text-sm font-black text-brand-dark">
                                        {{ $item->quantity }}x
                                    </span>
                                </div>

                                <div class="mt-4">
                                    <p class="font-black text-brand-dark">{{ $item->menuItem->name }}</p>
                                    @if($item->notes)
                                        <p class="mt-2 rounded-md bg-white px-3 py-2 text-sm text-brand-dark">
                                            {{ $item->notes }}
                                        </p>
                                    @endif
                                </div>

                                <div class="mt-4 flex flex-col gap-2">
                                    @if($item->status === \App\Models\OrderItem::STATUS_NEW)
                                        <form method="POST" action="{{ route('kitchen.order-items.status', $item) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="{{ \App\Models\OrderItem::STATUS_PREPARING }}">
                                            <button type="submit" class="w-full rounded-md bg-brand-dark px-4 py-2 text-sm font-bold text-brand-light hover:bg-brand-accent">
                                                Rozpocznij przygotowanie
                                            </button>
                                        </form>
                                    @elseif($item->status === \App\Models\OrderItem::STATUS_PREPARING)
                                        <form method="POST" action="{{ route('kitchen.order-items.status', $item) }}">
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
    </section>
</x-app>
