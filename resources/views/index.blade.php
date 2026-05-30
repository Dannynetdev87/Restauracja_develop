<x-app>
    <x-slot:title>Panel - SmakPrzeszłości</x-slot>

    @auth
        @if(auth()->user()->isManager())
            <section id="stoliki" class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center border-b border-brand-dark/10 pb-6 mb-8">
                    <div>
                        <h1 class="text-3xl font-black tracking-tight text-brand-dark">Zarządzanie stolikami</h1>
                        <p class="text-brand-accent text-sm mt-1">Podgląd statusu sal, liczby miejsc oraz rezerwacji.</p>
                    </div>
                    <a href="{{ route('manager.tables.index') }}" class="mt-4 sm:mt-0 bg-brand-dark text-brand-light hover:bg-brand-accent font-bold px-5 py-2.5 rounded-xl transition shadow-sm text-sm flex items-center gap-2">
                        <span>+ Dodaj nowy stolik</span>
                    </a>
                </div>

                <div class="tables-grid">
                    @forelse($tables as $table)
                        <div class="table-card">
                            <div>
                                <div class="table-number">Stolik nr {{ $table->number }}</div>
                                <div class="table-seats">Miejsca: <strong class="text-brand-dark">{{ $table->seats }}</strong></div>
                            </div>

                            <span class="status-badge {{ $table->status_class }}">
                                {{ $table->status_label }}
                            </span>
                        </div>
                    @empty
                        <p class="text-brand-accent text-sm col-span-full">Brak stolików do wyświetlenia.</p>
                    @endforelse
                </div>
            </section>
        @else
            <section class="welcome-section">
                <span class="welcome-badge">Witaj, {{ auth()->user()->full_name }}</span>

                <h1 class="welcome-title">
                    {{ auth()->user()->role === 'kelner' ? 'Panel kelnera' : 'Panel pracownika' }}
                </h1>

                <p class="welcome-desc">
                    Przejdź do swojego panelu, aby korzystać z funkcji przypisanych do roli.
                </p>

                <div class="welcome-actions">
                    <a href="{{ route('dashboard') }}" class="btn-welcome-primary">
                        Otwórz panel
                    </a>
                </div>
            </section>
        @endif
    @else
        <section class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10">
                <span class="welcome-badge">Witaj w SmakPrzeszłości</span>
                <h1 class="welcome-title mt-4">Dostępność stolików</h1>
                <p class="welcome-desc">
                    Sprawdź aktualny status stolików i orientacyjny czas oczekiwania bez podglądu szczegółów zamówień.
                </p>
            </div>

            <div class="flex flex-wrap justify-center gap-4 mb-8">
                <span class="flex items-center gap-2 text-sm text-brand-dark">
                    <span class="inline-block w-3 h-3 rounded-full bg-emerald-500"></span> Wolny
                </span>
                <span class="flex items-center gap-2 text-sm text-brand-dark">
                    <span class="inline-block w-3 h-3 rounded-full bg-amber-500"></span> Zajęty
                </span>
                <span class="flex items-center gap-2 text-sm text-brand-dark">
                    <span class="inline-block w-3 h-3 rounded-full bg-blue-500"></span> Zarezerwowany
                </span>
                <span class="flex items-center gap-2 text-sm text-brand-dark">
                    <span class="inline-block w-3 h-3 rounded-full bg-slate-400"></span> Nieaktywny
                </span>
            </div>

            <div class="tables-grid">
                @forelse($tables as $table)
                    <div class="table-card">
                        <div class="flex flex-col gap-1">
                            <div class="table-number">Stolik nr {{ $table->number }}</div>
                            <div class="table-seats">
                                Miejsca: <strong class="text-brand-dark">{{ $table->seats }}</strong>
                            </div>

                            @if($table->waiting_minutes !== null)
                                <div class="mt-1 text-xs text-brand-accent">
                                    Czas oczekiwania: <strong>{{ $table->waiting_minutes }} min</strong>
                                </div>
                            @endif
                        </div>

                        <span class="status-badge {{ $table->status_class }}">
                            {{ $table->status_label }}
                        </span>
                    </div>
                @empty
                    <p class="text-brand-accent text-sm col-span-full text-center">
                        Brak stolików do wyświetlenia.
                    </p>
                @endforelse
            </div>

            <div class="welcome-actions mt-10">
                <a href="{{ route('menu.index') }}" class="btn-welcome-primary">
                    Zobacz menu
                </a>
                <a href="{{ route('login') }}" class="btn-welcome-secondary">
                    Zaloguj się do panelu
                </a>
            </div>
        </section>
    @endauth
</x-app>
