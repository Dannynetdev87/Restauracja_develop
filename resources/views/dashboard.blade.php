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
            <a href="{{ route('menu.index') }}" class="btn-welcome-primary">
                Zobacz menu
            </a>
        </div>
    </section>
</x-app>
