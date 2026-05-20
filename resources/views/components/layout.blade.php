<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'SmakPrzeszłości' }}</title>

    @vite('resources/css/main.css')
    {{ $styles ?? '' }}
</head>
<body class="bg-brand-light text-brand-dark flex flex-col min-h-screen">

<nav class="bg-white shadow-sm border-b border-brand-dark/10 py-4 px-6 flex justify-between items-center">
    <div class="flex items-center gap-8">
        <a href="/" class="text-xl font-black tracking-tight text-brand-dark hover:text-brand-accent transition">
            SmakPrzeszłości
        </a>
        <div class="hidden md:flex items-center gap-6 text-sm font-medium">
            <a href="{{ route('menu.index') }}" class="hover:text-brand-accent transition">Menu</a>
            @auth
                <a href="{{ route('dashboard') }}" class="hover:text-brand-accent transition">Dashboard</a>
            @endauth
        </div>
    </div>

    <div class="flex items-center gap-4">
        @guest
            <a href="{{ route('login') }}" class="bg-brand-dark text-brand-light hover:bg-brand-accent text-sm font-bold px-4 py-2 rounded-xl transition shadow-sm">
                Zaloguj się
            </a>
        @endguest

        @auth
            <div class="flex items-center gap-4 text-sm">
                    <span class="text-brand-accent">
                        Zalogowany jako: <strong>{{ Auth::user()->name ?? 'Pracownik' }}</strong>
                        ({{ ucfirst(Auth::user()->role ?? '') }})
                    </span>

                @if(Auth::user()->role === 'manager')
                    <a href="{{ route('manager.podglad') }}" class="bg-amber-100 text-amber-800 px-2.5 py-1 rounded-lg text-xs font-bold hover:bg-amber-200 transition">
                        Podgląd Managera
                    </a>
                @endif

                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-red-600 hover:text-red-800 font-medium hover:underline transition">
                        Wyloguj się
                    </button>
                </form>
            </div>
        @endauth
    </div>
</nav>

<main class="flex-grow">
    {{ $slot }}
</main>

<footer class="bg-brand-dark text-white py-6 text-center text-xs mt-auto">
    <p>&copy; {{ date('Y') }} SmakPrzeszłości — System obsługi restauracji. Wszystkie prawa zastrzeżone.</p>
</footer>

{{ $scripts ?? '' }}
</body>
</html>
