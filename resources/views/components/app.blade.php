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

@php
    $activeNavClass = 'font-bold text-brand-dark border-b-2 border-brand-accent';
    $user = auth()->user();
    $isProductionRole = $user && ($user->worksInKitchen() || $user->worksAtBar());
    $productionCurrentRoute = $user?->worksInKitchen() ? 'kitchen.current' : 'bar.current';
    $productionDashboardRoute = $user?->worksInKitchen() ? 'kitchen.dashboard' : 'bar.dashboard';
@endphp

<div class="bg-brand-dark text-white text-xs py-2 px-4 flex flex-col gap-1 sm:flex-row sm:justify-between sm:items-center border-b border-white/10">
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
        <a href="{{ $isProductionRole ? route($productionCurrentRoute) : route('home') }}" class="site-logo">
            Smak<span class="logo-accent">Przeszłości</span>
        </a>

        <nav class="main-nav">
            @auth
                @if($isProductionRole)
                    <a href="{{ route($productionCurrentRoute) }}" class="nav-link {{ request()->routeIs($productionCurrentRoute) ? $activeNavClass : '' }}">Aktualne</a>
                    <a href="{{ route($productionDashboardRoute) }}" class="nav-link {{ request()->routeIs($productionDashboardRoute) ? $activeNavClass : '' }}">Dashboard</a>
                    <a href="{{ route('schedule.index') }}" class="nav-link {{ request()->routeIs('schedule.index') ? $activeNavClass : '' }}">Grafik</a>
                @else
                    <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? $activeNavClass : '' }}">Start</a>
                    <a href="{{ route('menu.index') }}" class="nav-link {{ request()->routeIs('menu.index') ? $activeNavClass : '' }}">Menu</a>
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard', 'admin.dashboard', 'manager.dashboard', 'waiter.dashboard', 'waiter.tables.*', 'waiter.orders.*') ? $activeNavClass : '' }}">Panel</a>
                    <a href="{{ route('schedule.index') }}" class="nav-link {{ request()->routeIs('schedule.index', 'manager.schedules.*') ? $activeNavClass : '' }}">Grafik</a>

                    @if(auth()->user()->isManager() || auth()->user()->isAdmin())
                        <a href="{{ route('manager.podglad') }}" class="nav-link {{ request()->routeIs('manager.podglad', 'manager.menu-categories.*', 'manager.menu-items.*') ? $activeNavClass : '' }}">
                            Zarządzanie menu
                        </a>
                        <a href="{{ route('manager.statistics') }}" class="nav-link {{ request()->routeIs('manager.statistics') ? $activeNavClass : '' }}">
                            Statystyka
                        </a>
                        <a href="{{ route('manager.tables.index') }}" class="nav-link {{ request()->routeIs('manager.tables.*') ? $activeNavClass : '' }}">
                            Stoliki
                        </a>
                    @endif
                @endif

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm bg-brand-dark text-brand-light px-4 py-2 rounded-xl hover:bg-brand-accent transition">
                        Wyloguj
                    </button>
                </form>
            @else
                <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? $activeNavClass : '' }}">Start</a>
                <a href="{{ route('menu.index') }}" class="nav-link {{ request()->routeIs('menu.index') ? $activeNavClass : '' }}">Menu</a>
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

<script>
    (() => {
        const setupAutoRefresh = () => {
            document.querySelectorAll('[data-auto-refresh]').forEach((container) => {
                if (container.dataset.refreshReady === 'true' || !container.id) {
                    return;
                }

                container.dataset.refreshReady = 'true';

                const refreshUrl = container.dataset.refreshUrl || window.location.href;
                const refreshInterval = Number.parseInt(container.dataset.refreshInterval || '8000', 10);
                let pending = false;
                let paused = false;

                container.addEventListener('focusin', () => {
                    paused = true;
                });

                container.addEventListener('focusout', () => {
                    paused = false;
                });

                const refresh = async () => {
                    if (pending || paused || document.hidden) {
                        return;
                    }

                    pending = true;

                    try {
                        const response = await fetch(refreshUrl, {
                            cache: 'no-store',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        });

                        if (!response.ok) {
                            return;
                        }

                        const html = await response.text();
                        const documentSnapshot = new DOMParser().parseFromString(html, 'text/html');
                        const freshContainer = documentSnapshot.getElementById(container.id);

                        if (!freshContainer) {
                            return;
                        }

                        container.innerHTML = freshContainer.innerHTML;

                        const indicator = container.querySelector('[data-refresh-indicator]');
                        if (indicator) {
                            indicator.textContent = `Odświeżono ${new Date().toLocaleTimeString('pl-PL', {
                                hour: '2-digit',
                                minute: '2-digit',
                            })}`;
                        }
                    } finally {
                        pending = false;
                    }
                };

                window.setInterval(refresh, refreshInterval);
            });
        };

        document.addEventListener('DOMContentLoaded', setupAutoRefresh);
    })();
</script>

</body>
</html>
