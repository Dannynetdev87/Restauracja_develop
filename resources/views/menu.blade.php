<x-app>
    <x-slot:title>Menu - SmakPrzeszłości</x-slot>

    <section class="w-full max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="mb-8 text-center">
            <span class="text-sm font-bold uppercase text-brand-accent">Aktualna oferta</span>
            <h1 class="mt-2 text-4xl font-black text-brand-dark">Menu</h1>
        </div>

        <div class="space-y-6">
            @forelse($categories as $category)
                <article class="rounded-lg border border-brand-dark/15 bg-white p-5 shadow-sm">
                    <h2 class="text-2xl font-black text-brand-dark">{{ $category->name }}</h2>

                    <div class="mt-4 divide-y divide-brand-dark/10">
                        @forelse($category->availableItems as $item)
                            <div class="flex flex-col gap-2 py-4 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <h3 class="font-bold text-brand-dark">{{ $item->name }}</h3>
                                    @if($item->description)
                                        <p class="mt-1 text-sm text-brand-accent">{{ $item->description }}</p>
                                    @endif
                                </div>
                                <div class="shrink-0 font-black text-brand-dark">
                                    {{ number_format($item->price, 2, ',', ' ') }} zł
                                </div>
                            </div>
                        @empty
                            <p class="py-4 text-sm text-brand-accent">Brak dostępnych pozycji w tej kategorii.</p>
                        @endforelse
                    </div>
                </article>
            @empty
                <div class="rounded-lg border border-brand-dark/15 bg-white p-8 text-center text-brand-accent">
                    Menu nie jest jeszcze dostępne.
                </div>
            @endforelse
        </div>
    </section>
</x-app>
