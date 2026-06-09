<x-app>
    <x-slot:title>Moje napiwki - SmakPrzeszłości</x-slot>

    <section class="w-full px-4 py-6 sm:px-6 sm:py-10 lg:px-8">
        <div class="mx-auto max-w-3xl min-w-0">
            <div class="mb-6 grid grid-cols-3 gap-2 sm:mb-8 sm:flex sm:flex-wrap sm:justify-center sm:gap-4">
                <a href="{{ route('waiter.dashboard') }}"
                   class="min-w-0 rounded-xl bg-brand-dark px-2 py-3 text-center text-xs font-black uppercase tracking-wide text-brand-light shadow-sm transition-all duration-200 ease-out hover:bg-brand-accent hover:shadow-md active:scale-95 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-accent/40 sm:min-w-40 sm:px-8 sm:py-4 sm:text-sm">
                    Dashboard
                </a>
                <a href="{{ route('waiter.tables.index') }}"
                   class="min-w-0 rounded-xl bg-brand-dark px-2 py-3 text-center text-xs font-black uppercase tracking-wide text-brand-light shadow-sm transition-all duration-200 ease-out hover:bg-brand-accent hover:shadow-md active:scale-95 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-accent/40 sm:min-w-40 sm:px-8 sm:py-4 sm:text-sm">
                    Stoliki
                </a>
                <button type="button" disabled
                        class="min-w-0 rounded-xl border border-current bg-wheat px-2 py-3 text-center text-xs font-black uppercase tracking-wide text-brand-dark shadow-sm sm:min-w-40 sm:px-8 sm:py-4 sm:text-sm">
                    Napiwki
                </button>
            </div>
            <div class="mb-6 sm:mb-8">
                <h1 class="text-2xl font-black text-brand-dark sm:text-3xl">Moje napiwki</h1>
                <p class="mt-1 text-sm text-brand-accent sm:text-base">{{ now()->locale('pl')->translatedFormat('l, d F Y') }}</p>
            </div>

            <div class="mb-6 grid gap-4 sm:grid-cols-2 sm:gap-5">
                <section class="rounded-xl border-2 border-brand-dark bg-white p-5 shadow-sm sm:p-6">
                    <span class="text-xs font-black uppercase tracking-wide text-brand-accent sm:tracking-[0.16em]">
                        Wszystkie zmiany
                    </span>
                    <p class="mt-3 text-3xl font-black text-brand-dark sm:text-4xl">
                        {{ number_format($totalTips, 2, ',', ' ') }} zł
                    </p>
                    <p class="mt-1 text-sm font-bold text-brand-accent">
                        Łączna suma napiwków
                    </p>
                </section>

                <section class="rounded-xl border-2 {{ $hasActiveShift ? 'border-emerald-700 bg-emerald-50/30' : 'border-brand-dark/30 bg-white/60' }} p-5 shadow-sm sm:p-6">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <span class="text-xs font-black uppercase tracking-wide sm:tracking-[0.16em] {{ $hasActiveShift ? 'text-emerald-700' : 'text-brand-accent' }}">
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
                        <p class="mt-3 text-3xl font-black text-emerald-800 sm:text-4xl">
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
                    <span class="text-xs font-black uppercase tracking-wide text-brand-accent sm:tracking-[0.16em]">
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
