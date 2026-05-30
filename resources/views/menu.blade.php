<x-app>
    <x-slot:title>Menu - SmakPrzeszłości</x-slot>

    <section class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="mb-8 text-center">
            <span class="text-sm font-bold uppercase text-brand-accent">Aktualna oferta</span>
            <h1 class="mt-2 text-4xl font-black text-brand-dark">Karta dań</h1>
            <p class="mt-2 text-sm text-brand-accent">Przeglądaj dostępne pozycje według kategorii i zakresu cen.</p>
        </div>

        @forelse($categories as $category)
            @if($loop->first)
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
            @endif

            <article class="flex min-h-[400px] flex-col rounded-2xl border-2 border-brand-dark/15 bg-[#fcf8f2] p-5 shadow-sm">
                <div class="mb-4 border-b-2 border-brand-dark/10 pb-4 text-center">
                    <span class="rounded-full bg-brand-dark/5 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-brand-accent">
                        Kategoria
                    </span>
                    <h2 class="mt-2 text-xl font-black uppercase text-brand-dark">{{ $category->name }}</h2>

                    <label class="sr-only" for="menu-filter-{{ $category->id }}">Filtr cen dla {{ $category->name }}</label>
                    <select
                        id="menu-filter-{{ $category->id }}"
                        class="mx-auto mt-3 block w-full max-w-[220px] rounded-lg border border-brand-dark/20 bg-white px-3 py-1.5 text-xs font-bold text-brand-dark shadow-sm transition focus:border-brand-accent focus:outline-none focus:ring-brand-accent"
                        data-menu-filter
                        data-menu-list="items-list-{{ $category->id }}"
                    >
                        <option value="all">Wszystkie pozycje ({{ $category->availableItems->count() }})</option>
                        <option value="budget">Dania budżetowe (&lt; 30 zł)</option>
                        <option value="premium">Dania premium (≥ 30 zł)</option>
                    </select>
                </div>

                <div id="items-list-{{ $category->id }}" class="max-h-[500px] flex-1 space-y-3 overflow-y-auto pr-1">
                    @forelse($category->availableItems as $item)
                        <div
                            class="rounded-xl border border-brand-dark/10 bg-white p-4 shadow-sm transition duration-200 hover:border-brand-accent"
                            data-menu-item
                            data-price="{{ number_format((float) $item->price, 2, '.', '') }}"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="text-sm font-bold leading-tight text-brand-dark sm:text-base">{{ $item->name }}</h3>

                                    @if($item->description)
                                        <p class="mt-1.5 text-[11px] leading-relaxed text-brand-accent">{{ $item->description }}</p>
                                    @endif
                                </div>

                                <div class="shrink-0 rounded-lg bg-brand-dark/5 px-2.5 py-1 text-right">
                                    <span class="text-base font-black text-brand-dark">
                                        {{ number_format($item->price, 2, ',', ' ') }}
                                    </span>
                                    <span class="block text-[10px] font-bold text-brand-accent">zł</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-xl border border-dashed border-brand-dark/15 bg-white p-6 text-center">
                            <p class="text-xs font-bold italic text-brand-accent">Brak dostępnych pozycji w tej kategorii.</p>
                        </div>
                    @endforelse

                    <div class="hidden rounded-xl border border-dashed border-brand-dark/15 bg-white p-6 text-center" data-empty-filter-message>
                        <p class="text-xs font-bold italic text-brand-accent">Brak pozycji w wybranym zakresie cen.</p>
                    </div>
                </div>

                <div class="mt-4 border-t border-brand-dark/10 pt-3 text-center">
                    <p class="text-[10px] font-bold uppercase tracking-tight text-brand-accent">
                        SmakPrzeszłości • Menu dostępne
                    </p>
                </div>
            </article>

            @if($loop->last)
                </div>
            @endif
        @empty
            <div class="rounded-lg border border-brand-dark/15 bg-white p-8 text-center text-brand-accent">
                Menu nie jest jeszcze dostępne.
            </div>
        @endforelse
    </section>

    <script>
        (() => {
            const updateMenuFilter = (select) => {
                const container = document.getElementById(select.dataset.menuList);

                if (!container) {
                    return;
                }

                const cards = Array.from(container.querySelectorAll('[data-menu-item]'));
                const emptyMessage = container.querySelector('[data-empty-filter-message]');
                let visibleCount = 0;

                cards.forEach((card) => {
                    const price = Number.parseFloat(card.dataset.price || '0');
                    const shouldShow = select.value === 'all'
                        || (select.value === 'budget' && price < 30)
                        || (select.value === 'premium' && price >= 30);

                    card.hidden = !shouldShow;

                    if (shouldShow) {
                        visibleCount += 1;
                    }
                });

                if (emptyMessage) {
                    emptyMessage.classList.toggle('hidden', visibleCount > 0 || cards.length === 0);
                }
            };

            document.querySelectorAll('[data-menu-filter]').forEach((select) => {
                select.addEventListener('change', () => updateMenuFilter(select));
            });
        })();
    </script>
</x-app>
