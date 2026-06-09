<x-app>
    <x-slot:title>Statystyki managera - SmakPrzeszłości</x-slot>

    <section id="manager-statistics" class="w-full max-w-7xl mx-auto px-4 py-10 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-col gap-5 border-b border-brand-dark/10 pb-6 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <span class="text-sm font-bold uppercase text-brand-accent">Analityka restauracji</span>
                <h1 class="mt-1 text-3xl font-black tracking-tight text-brand-dark">Statystyki</h1>
                <p class="mt-1 text-sm text-brand-accent">Podsumowanie sprzedaży, zamówień, napiwków i pracy zespołu.</p>
            </div>

            <a href="{{ route('manager.dashboard') }}"
               class="w-full rounded-md border border-brand-dark/20 bg-white px-4 py-2 text-center text-sm font-bold text-brand-dark transition-all duration-200 ease-out hover:bg-brand-light hover:shadow-md active:scale-95 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-accent/40 sm:w-auto">
                Powrót do panelu
            </a>
        </div>

        <div class="space-y-8">
            <section>
                <div class="mb-4">
                    <h2 class="text-xl font-black text-brand-dark">Podsumowanie dzisiejsze</h2>
                    <p class="mt-1 text-sm text-brand-accent">Sprzedaż liczona z opłaconych płatności, nie z sum pozycji zamówień.</p>
                </div>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    <article class="rounded-xl border border-brand-dark/15 bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-xs font-bold uppercase text-brand-accent">Sprzedaż dzisiaj</span>
                            <span class="rounded-full bg-green-50 px-2 py-0.5 text-[10px] font-bold text-green-600">Live</span>
                        </div>
                        <p class="mt-4 text-3xl font-black text-brand-dark">
                            {{ number_format((float) $todaySales, 2, ',', ' ') }} zł
                        </p>
                    </article>

                    <article class="rounded-xl border border-brand-dark/15 bg-white p-5 shadow-sm">
                        <span class="text-xs font-bold uppercase text-brand-accent">Zamówienia dzisiaj</span>
                        <p class="mt-4 text-3xl font-black text-brand-dark">{{ $todayOrdersCount }}</p>
                    </article>

                    <article class="rounded-xl border border-brand-dark/15 bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-xs font-bold uppercase text-brand-accent">Aktywne zamówienia</span>
                            <span class="flex h-2 w-2 animate-pulse rounded-full bg-amber-500"></span>
                        </div>
                        <p class="mt-4 text-3xl font-black text-brand-dark">{{ $activeOrdersCount }}</p>
                    </article>

                    <article class="rounded-xl border border-brand-dark/15 bg-white p-5 shadow-sm">
                        <span class="text-xs font-bold uppercase text-brand-accent">Opłacone dzisiaj</span>
                        <p class="mt-4 text-3xl font-black text-brand-dark">{{ $paidOrdersCount }}</p>
                    </article>
                </div>
            </section>

            <section>
                <div class="mb-4">
                    <h2 class="text-xl font-black text-brand-dark">Statystyki ogólne</h2>
                    <p class="mt-1 text-sm text-brand-accent">Wartości od początku działania aplikacji.</p>
                </div>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    <article class="rounded-xl border border-brand-dark/15 bg-white p-5 shadow-sm">
                        <span class="text-xs font-bold uppercase text-brand-accent">Łączna sprzedaż</span>
                        <p class="mt-4 text-3xl font-black text-brand-dark">
                            {{ number_format((float) $totalSales, 2, ',', ' ') }} zł
                        </p>
                    </article>

                    <article class="rounded-xl border border-brand-dark/15 bg-white p-5 shadow-sm">
                        <span class="text-xs font-bold uppercase text-brand-accent">Łączne zamówienia</span>
                        <p class="mt-4 text-3xl font-black text-brand-dark">{{ $totalOrdersCount }}</p>
                    </article>

                    <article class="rounded-xl border border-brand-dark/15 bg-white p-5 shadow-sm">
                        <span class="text-xs font-bold uppercase text-brand-accent">Łączne napiwki</span>
                        <p class="mt-4 text-3xl font-black text-brand-dark">
                            {{ number_format((float) $totalTips, 2, ',', ' ') }} zł
                        </p>
                    </article>

                    <article class="rounded-xl border border-brand-dark/15 bg-white p-5 shadow-sm">
                        <span class="text-xs font-bold uppercase text-brand-accent">Liczba gości</span>
                        <p class="mt-4 text-3xl font-black text-brand-dark">
                            {{ $guestCount ?? 'Brak danych' }}
                        </p>
                    </article>
                </div>
            </section>

            <section class="grid gap-5 lg:grid-cols-2">
                <article class="rounded-xl border border-brand-dark/15 bg-white p-5 shadow-sm">
                    <span class="text-xs font-bold uppercase text-brand-accent">Top Bar dzisiaj</span>
                    @if($topBarItem)
                        <p class="mt-3 text-2xl font-black text-brand-dark">{{ $topBarItem->name }}</p>
                        <p class="mt-1 text-sm font-bold text-brand-accent">{{ (int) $topBarItem->quantity_sold }} szt.</p>
                    @else
                        <p class="mt-3 text-2xl font-black text-brand-dark">Brak danych</p>
                    @endif
                </article>

                <article class="rounded-xl border border-brand-dark/15 bg-white p-5 shadow-sm">
                    <span class="text-xs font-bold uppercase text-brand-accent">Top Kuchnia dzisiaj</span>
                    @if($topKitchenItem)
                        <p class="mt-3 text-2xl font-black text-brand-dark">{{ $topKitchenItem->name }}</p>
                        <p class="mt-1 text-sm font-bold text-brand-accent">{{ (int) $topKitchenItem->quantity_sold }} szt.</p>
                    @else
                        <p class="mt-3 text-2xl font-black text-brand-dark">Brak danych</p>
                    @endif
                </article>
            </section>

            <section class="grid gap-5 lg:grid-cols-[minmax(0,0.8fr)_minmax(0,1.2fr)]">
                <article class="rounded-xl border border-brand-dark/15 bg-white p-5 shadow-sm">
                    <span class="text-xs font-bold uppercase text-brand-accent">Top pracownik miesiąca</span>
                    @if($topEmployee)
                        <p class="mt-3 text-2xl font-black text-brand-dark">{{ $topEmployee->name }}</p>
                        <p class="mt-1 text-sm font-bold text-brand-accent">
                            Obrót z płatności: {{ number_format((float) $topEmployee->total_sales, 2, ',', ' ') }} zł
                        </p>
                    @else
                        <p class="mt-3 text-2xl font-black text-brand-dark">Brak danych</p>
                    @endif
                </article>

                <article class="rounded-xl border border-brand-dark/15 bg-white p-5 shadow-sm">
                    <div class="mb-4 flex flex-col gap-1 border-b border-brand-dark/10 pb-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <span class="text-xs font-bold uppercase text-brand-accent">Grafik miesiąca</span>
                            <h2 class="mt-1 text-xl font-black text-brand-dark">Zaplanowane godziny pracowników</h2>
                        </div>
                    </div>

                    <div class="space-y-3">
                        @forelse($employeeHours as $employee)
                            <div class="flex flex-col gap-3 rounded-lg border border-brand-dark/10 bg-brand-light/25 p-4 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-black text-brand-dark">{{ $employee['name'] }}</p>
                                    <p class="mt-1 text-xs font-bold uppercase text-brand-accent">{{ $employee['role'] }}</p>
                                </div>
                                <p class="text-sm font-black text-brand-dark">
                                    {{ number_format((float) $employee['hours'], 1, ',', ' ') }} h
                                </p>
                            </div>
                        @empty
                            <div class="rounded-lg border border-dashed border-brand-dark/20 px-4 py-8 text-center text-sm font-semibold text-brand-accent">
                                Brak danych z grafiku w tym miesiącu.
                            </div>
                        @endforelse
                    </div>
                </article>
            </section>
        </div>
    </section>
</x-app>
