<x-app>
    <x-slot:title>{{ $title }} - SmakPrzeszłości</x-slot>

    <section class="welcome-section">
        <span class="welcome-badge">{{ auth()->user()->full_name }}</span>

        <h1 class="welcome-title">
            {{ $title }}
        </h1>

        <p class="welcome-desc">
            {{ $description }}
        </p>

        <div class="welcome-actions">
            @if(auth()->user()->isWaiter())
                <a href="{{ route('waiter.tables.index') }}" class="btn-welcome-primary">
                    Przejdź do stolików
                </a>
            @else
                <a href="{{ route('menu.index') }}" class="btn-welcome-primary">
                    Zobacz menu
                </a>
            @endif
        </div>
    </section>
</x-app>
