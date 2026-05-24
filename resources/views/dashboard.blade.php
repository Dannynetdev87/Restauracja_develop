<x-app>
    <x-slot:title>{{ $title }} - SmakPrzeszłości</x-slot>

    <section class="welcome-section">
        <span class="welcome-badge">{{ auth()->user()->name }}</span>

        <h1 class="welcome-title">
            {{ $title }}
        </h1>

        <p class="welcome-desc">
            {{ $description }}
        </p>

        <div class="welcome-actions">
            @if(auth()->user()->role === 'kelner')
                <a href="{{ route('waiter.tables.index') }}" class="btn-welcome-secondary no-underline">
                    Przejdź do stolików
                </a>

                <a href="{{ route('waiter.orders.create') }}" class="btn-welcome-primary no-underline">
                    Otwórz nowy POS / Zamówienie
                </a>
            @else
                <a href="{{ route('menu.index') }}" class="btn-welcome-primary no-underline">
                    Zobacz menu
                </a>
            @endif
        </div>
    </section>
</x-app>
