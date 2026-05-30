<x-app>
    <x-slot:title>Moje napiwki - SmakPrzeszłości</x-slot>

    <section class="w-full px-4 py-10 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl">
            <div class="mb-8">
                <a href="{{ route('waiter.dashboard') }}"
                   class="text-sm font-bold text-brand-accent hover:text-brand-dark">
                    Wróć do dashboardu
                </a>
                <h1 class="mt-3 text-3xl font-black text-brand-dark">Moje napiwki</h1>
                <p class="mt-1 text-brand-accent">{{ now()->locale('pl')->translatedFormat('l, d F Y') }}</p>
            </div>

            <div class="mb-6 grid gap-5 sm:grid-cols-2">
                <section class="rounded-xl border-2 border-brand-dark bg-white p-6 shadow-sm">
                    <span class="text-xs font-black uppercase tracking-[0.16em] text-brand-accent">
                        Wszystkie zmiany
                    </span>
                    <p class="mt-3 text-4xl font-black text-brand-dark">
                        {{ number_format($totalTips, 2, ',', ' ') }} zł
                    </p>
                    <p class="mt-1 text-sm font-bold text-brand-accent">
                        Łączna suma napiwków
                    </p>
                </section>

                <section class="rounded-xl border-2 {{ $hasActiveShift ? 'border-emerald-700 bg-emerald-50/30' : 'border-brand-dark/30 bg-white/60' }} p-6 shadow-sm">
                    <div class="flex items-start justify-between gap-2">
                        <span class="text-xs font-black uppercase tracking-[0.16em] {{ $hasActiveShift ? 'text-emerald-700' : 'text-brand-accent' }}">
                            Aktualna zmiana
                        </span>
                        @if($hasActiveShift)
                            <span class="rounded-md bg-emerald-100 px-2 py-0.5 text-xs font-black text-emerald-800">
                                W toku
                            </span>
                        @else
                            <span class="rounded-md bg-gray-100 px-2 py-0.5 text-xs font-black text-gray-600">
                                Brak zmiany
                            </span>
                        @endif
                    </div>

                    @if($hasActiveShift)
                        <p class="mt-3 text-4xl font-black text-emerald-800">
                            {{ number_format($shiftTips, 2, ',', ' ') }} zł
                        </p>
                        <p class="mt-1 text-sm font-bold text-emerald-700">
                            Liczba zamówień: {{ $shiftOrdersCount }}
                        </p>
                    @else
                        <p class="mt-3 text-2xl font-black text-brand-dark/40">-</p>
                        <p class="mt-1 text-sm font-bold text-brand-accent">
                            Brak aktywnej zmiany
                        </p>
                    @endif
                </section>
            </div>

            @if($hasActiveShift && $shiftOrdersCount > 0)
                <section class="rounded-xl border border-brand-dark/15 bg-white p-5 shadow-sm">
                    <span class="text-xs font-black uppercase tracking-[0.16em] text-brand-accent">
                        Średni napiwek na zamówienie
                    </span>
                    <p class="mt-2 text-2xl font-black text-brand-dark">
                        {{ number_format($shiftTips / $shiftOrdersCount, 2, ',', ' ') }} zł
                    </p>
                </section>
            @endif
        </div>
    </section>
</x-app>
