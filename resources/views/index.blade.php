<x-app>
    <x-slot:title>Panel - SmakPrzeszłości</x-slot>

    @if(request('role') === 'manager')
        <section id="stoliki" class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center border-b border-brand-dark/10 pb-6 mb-8">
                <div>
                    <h1 class="text-3xl font-black tracking-tight text-brand-dark">Zarządzanie Stolikami</h1>
                    <p class="text-brand-accent text-sm mt-1">Podgląd statusu sal, liczby miejsc oraz rezerwacji w czasie rzeczywistym.</p>
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
                    ['number' => 5, 'seats' => 2, 'status' => 'zajęty', 'class' => 'status-occupied'],
                    ['number' => 6, 'seats' => 8, 'status' => 'wolny', 'class' => 'status-free'],
                ];
            @endphp

            <div class="tables-grid">
                @foreach($mockupTables as $table)
                    <div class="table-card">
                        <div>
                            <div class="table-number">
                                <svg class="w-5 h-5 text-brand-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                                </svg>
                                Stolik nr {{ $table['number'] }}
                            </div>
                            <div class="table-seats">Miejsca: <strong class="text-brand-dark">{{ $table['seats'] }}</strong></div>
                        </div>

                        <div class="flex justify-between items-center mt-6">
                            <span class="status-badge {{ $table['class'] }}">
                                {{ $table['status'] }}
                            </span>

                            <div class="flex gap-2">
                                <button title="Edytuj stolik" class="text-brand-accent hover:text-brand-dark p-1 rounded transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @else
        <section class="welcome-section">
            <span class="welcome-badge">Witaj w SmakPrzeszłości</span>

            <h1 class="welcome-title">
                Odkryj menu naszej restauracji
            </h1>

            <p class="welcome-desc">
                Przeglądaj kartę dań online. Aby móc dokonać rezerwacji stolika lub złożyć zamówienie, zaloguj się na swoje konto.
            </p>

            <div class="welcome-actions">
                <a href="{{ route('menu.index') }}" class="btn-welcome-primary">
                    Zobacz Menu
                </a>
                <a href="{{ route('login') }}" class="btn-welcome-secondary">
                    Zaloguj się do panelu
                </a>
            </div>
        </section>
    @endif
</x-app>
