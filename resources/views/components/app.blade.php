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
    @auth
        <span>Zalogowano jako: <strong class="uppercase text-brand-light">{{ auth()->user()->full_name }}</strong></span>
        <span>Rola: <strong class="uppercase text-brand-light">{{ auth()->user()->role }}</strong></span>
    @else
        <span>Przeglądasz jako: <strong class="uppercase text-brand-light">gość</strong></span>
        <span>Dostęp do paneli wymaga logowania</span>
    @endauth
</div>

<header class="main-header">
    <div class="header-container">

        <a href="{{ route('home') }}" class="site-logo">
            Smak<span class="logo-accent">Przeszłości</span>
        </a>

        @php
            $activeNavClass = 'font-bold text-brand-dark border-b-2 border-brand-accent';
        @endphp

        <nav class="main-nav">
            <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? $activeNavClass : '' }}">Start</a>
            <a href="{{ route('menu.index') }}" class="nav-link {{ request()->routeIs('menu.index') ? $activeNavClass : '' }}">Menu</a>

            @auth
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard', 'admin.dashboard', 'manager.dashboard', 'waiter.dashboard', 'kitchen.dashboard', 'bar.dashboard') ? $activeNavClass : '' }}">Panel</a>

                @if(auth()->user()->isManager() || auth()->user()->isAdmin())
                    <a href="{{ route('manager.podglad') }}" class="nav-link {{ request()->routeIs('manager.podglad', 'manager.menu-categories.*', 'manager.menu-items.*') ? $activeNavClass : '' }}">
                        Zarządzanie menu
                    </a>
                    <a href="{{ route('manager.tables.index') }}" class="nav-link {{ request()->routeIs('manager.tables.*') ? $activeNavClass : '' }}">
                        Stoliki
                    </a>
                @endif

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm bg-brand-dark text-brand-light px-4 py-2 rounded-xl hover:bg-brand-accent transition">
                        Wyloguj
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="bg-brand-dark text-brand-light font-bold px-5 py-2.5 rounded-xl transition hover:bg-brand-accent shadow-sm text-sm">
                    Zaloguj się
                </a>
            @endauth
        </nav>

    </div>
</header>

<main class="flex-grow">
    {{ $slot }}
</main>

<footer class="site-footer">
    <div class="footer-container">
        &copy; 2026 SmakPrzeszłości. Panel: <span class="uppercase font-bold text-white">{{ auth()->user()->role ?? 'Gość' }}</span>
    </div>
</footer>

</body>
</html>
