<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Nasza Restauracja' }}</title>
    @vite(['resources/css/main.css'])
    @vite(['resources/css/index.css'])
</head>
<body class="site-body">

<div class="bg-brand-dark text-white text-xs py-2 px-4 flex justify-between items-center border-b border-white/10">
    <span>Przeglądasz jako: <strong class="uppercase text-brand-light">{{ request('role', 'gosc') }}</strong></span>
    <div class="flex gap-2">
        <a href="?role=gosc" class="px-2 py-0.5 rounded bg-white/20 hover:bg-white/30 transition">Niezalogowany</a>
        <a href="?role=manager" class="px-2 py-0.5 rounded bg-amber-600 hover:bg-amber-500 transition">Manager</a>
    </div>
</div>

<header class="main-header">
    <div class="header-container">

        <a href="/{{ request()->has('role') ? '?role='.request('role') : '' }}" class="site-logo">
            Smak<span class="logo-accent">Przeszłości</span>
        </a>

        <nav class="main-nav">
            <a href="/{{ request()->has('role') ? '?role='.request('role') : '' }}" class="nav-link">Start</a>
            <a href="/menu" class="nav-link">Menu</a>

            @if(request('role') === 'manager')
                <a href="#stoliki" class="nav-link font-bold text-brand-dark border-b-2 border-brand-accent">Stoliki</a>

                @auth
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm bg-brand-dark text-brand-light px-4 py-2 rounded-xl hover:bg-brand-accent transition">
                            Wyloguj
                        </button>
                    </form>
                @else
                    <a href="/login" class="text-sm bg-brand-dark text-brand-light px-4 py-2 rounded-xl hover:bg-brand-accent transition">
                        Zaloguj
                    </a>
                @endauth
            @else
                <a href="/login" class="bg-brand-dark text-brand-light font-bold px-5 py-2.5 rounded-xl transition hover:bg-brand-accent shadow-sm text-sm">
                    Zaloguj się
                </a>
            @endif
        </nav>

    </div>
</header>

<main class="flex-grow">
    {{ $slot }}
</main>

<footer class="site-footer">
    <div class="footer-container">
        &copy; 2026 SmakPrzeszłości. Panel: <span class="uppercase font-bold text-white">{{ request('role', 'Gość (Niezalogowany)') }}</span>
    </div>
</footer>

</body>
</html>
