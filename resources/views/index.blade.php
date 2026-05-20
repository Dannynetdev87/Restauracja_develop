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
                    <button class="mt-4 sm:mt-0 bg-brand-dark text-brand-light hover:bg-brand-accent font-bold px-5 py-2.5 rounded-xl transition shadow-sm text-sm flex items-center gap-2">
                        <span>+ Dodaj nowy stolik</span>
                    </button>
                </div>

                @php
                    $mockupTables = [
                        ['number' => 1, 'seats' => 2, 'status' => 'wolny', 'class' => 'status-free'],
                        ['number' => 2, 'seats' => 4, 'status' => 'zajęty', 'class' => 'status-occupied'],
                        ['number' => 3, 'seats' => 4, 'status' => 'wolny', 'class' => 'status-free'],
                        ['number' => 4, 'seats' => 6, 'status' => 'zarezerwowany', 'class' => 'status-reserved'],
                    ];
                @endphp

                <div class="tables-grid">
                    @foreach($mockupTables as $table)
                        <div class="table-card">
                            <div>
                                <div class="table-number">
                                    Stolik nr {{ $table['number'] }}
                                </div>
                                <div class="table-seats">Miejsca: <strong class="text-brand-dark">{{ $table['seats'] }}</strong></div>
                            </div>

                            <span class="status-badge {{ $table['class'] }}">
                                {{ $table['status'] }}
                            </span>
                        </div>
                    @endforeach
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
        <section class="welcome-section">
            <span class="welcome-badge">Witaj w SmakPrzeszłości</span>

            <h1 class="welcome-title">
                Odkryj menu naszej restauracji
            </h1>

            <p class="welcome-desc">
                Przeglądaj kartę dań online. Aby obsługiwać stoliki, zamówienia lub panel managera, zaloguj się na konto pracownika.
            </p>

            <div class="welcome-actions">
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
