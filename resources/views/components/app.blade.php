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

                <a href="{{ route('about') }}" class="nav-link {{ request()->routeIs('about') ? $activeNavClass : '' }}">O nas</a>

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

@guest
    <footer class="bg-brand-dark text-brand-light/80 border-t border-white/20 mt-auto w-full">

        <div class="mx-auto py-12 px-4" style="width: 95%;">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-y-12 gap-x-16 items-start pb-8 border-b border-white/20 w-full">

                <div class="space-y-4 w-full">
                    <div>
                        <span class="text-white font-black tracking-tight" style="font-size: 32px;">
                            Smak<span class="text-brand-accent">Przeszłości</span>
                        </span>
                        <p class="text-brand-light/70 leading-relaxed mt-3" style="font-size: 16px;">
                            Tradycyjne polskie receptury podane w nowoczesnej i eleganckiej formie. Dbamy o najwyższą jakość lokalnych składników, autorski smak oraz niepowtarzalną atmosferę minionych epok, która pozwala odkryć magię dawnych potraw zupełnie na nowo.
                        </p>
                    </div>

                    <div class="w-full text-brand-light/60">
                        <h4 class="text-white font-bold uppercase tracking-wide mb-2" style="font-size: 14px;">Godziny otwarcia</h4>
                        <div class="space-y-1" style="font-size: 15px;">
                            <div>Wtorek - Czwartek: <span class="text-brand-accent">12:00 - 21:00</span></div>
                            <div>Piątek - Sobota: <span class="text-brand-accent">12:00 - 23:00</span></div>
                            <div>Niedziela: <span class="text-brand-accent">12:00 - 20:00</span></div>
                            <div class="text-white/40 italic">Poniedziałek: Zamknięte</div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col items-center justify-center w-full">
                    <div class="w-full rounded-2xl overflow-hidden shadow-md border border-white/10" style="height: 320px;">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2564.0579242229696!2d22.671087476808744!3d50.010269518959646!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x473c9bc687dd4e2f%3A0x1e439e2250d349f3!2sPANS%20im.%20ks.%20B.%20Markiewicza.%20Instytut%20Humanistyczny!5e0!3m2!1spl!2spl!4v1780909364060!5m2!1spl!2spl" class="w-full h-full" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                    <span class="text-brand-light/40 mt-2 tracking-wide uppercase font-semibold" style="font-size: 11px;">Nasza lokalizacja</span>
                </div>

                <div class="w-full flex flex-col items-start gap-4">

                    <div class="w-full">
                        <h3 class="text-white font-bold uppercase tracking-wider mb-2" style="font-size: 18px;">Nawigacja</h3>
                        <ul class="space-y-2" style="font-size: 16px;">
                            <li><a href="{{ route('home') }}" class="hover:text-brand-accent transition">Strona główna</a></li>
                            <li><a href="{{ route('menu.index') }}" class="hover:text-brand-accent transition">Nasze Menu</a></li>
                            <li><a href="{{ route('about') }}" class="hover:text-brand-accent transition">O nas</a></li>
                        </ul>
                    </div>

                    <div class="w-full text-brand-light/60">
                        <h4 class="text-white font-bold uppercase tracking-wide mb-2" style="font-size: 14px;">Kontakt</h4>
                        <div class="space-y-1" style="font-size: 15px;">
                            <div><span class="text-white">Adres:</span> ul. Staromiejska 12, Jarosław</div>
                            <div><span class="text-white">Telefon:</span> +48 123 456 789</div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 mt-2">
                        <a href="#" class="w-12 h-12 rounded-xl bg-white/5 flex items-center justify-center text-brand-light hover:bg-brand-accent hover:text-brand-dark transition" title="Facebook">
                            <svg class="w-6 h-6" style="fill: currentColor; width: 26px; height: 26px;" viewBox="0 0 24 24">
                                <path d="M9 8H7v3h2v9h3v9h4v-9h3l.5-3H16V8c0-.5.5-1 1-1h2V4h-3a4 4 0 0 0-4 4v2H9V8z"/>
                            </svg>
                        </a>
                        <a href="#" class="w-12 h-12 rounded-xl bg-white/5 flex items-center justify-center text-brand-light hover:bg-brand-accent hover:text-brand-dark transition" title="Instagram">
                            <svg class="w-6 h-6" style="fill: currentColor; width: 26px; height: 26px;" viewBox="0 0 24 24">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.051.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 0 0 0 0-12.324zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881z"/>
                            </svg>
                        </a>
                        <a href="#" class="w-12 h-12 rounded-xl bg-white/5 flex items-center justify-center text-brand-light hover:bg-brand-accent hover:text-brand-dark transition" title="TikTok">
                            <svg class="w-6 h-6" style="fill: currentColor; width: 26px; height: 26px;" viewBox="0 0 24 24">
                                <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.02 1.63 4.15 1.13 1.23 2.69 1.95 4.34 2.13v3.86c-1.61-.03-3.2-.44-4.61-1.26a7.61 7.61 0 0 1-2.91-2.96v7.35c.16 2.37-1.04 4.67-3.08 5.87-2.14 1.34-4.96 1.4-7.15.17-2.31-1.21-3.69-3.76-3.48-6.37.2-2.73 2.35-5 5.09-5.26 1-.13 2.02.11 2.9.68v3.91a4.26 4.26 0 0 0-3.07-.46c-1.34.28-2.37 1.43-2.48 2.79-.16 1.56.91 2.99 2.45 3.28 1.47.33 3.06-.51 3.51-1.95.14-.38.19-.78.19-1.18V.02z"/>
                            </svg>
                        </a>
                    </div>
                </div>

            </div>

            <div class="pt-6 flex flex-col sm:flex-row justify-between items-center gap-4 text-xs text-brand-light/40 w-full">
                <div>
                    © {{ date('Y') }} SmakPrzeszłości. Wszelkie prawa zastrzeżone.
                </div>
                <div class="flex gap-4">
                    <span class="hover:text-brand-light transition cursor-help" title="Wersja systemu v2.4.1">System POS v2.4</span>
                </div>
            </div>
        </div>
    </footer>
@else
    <footer class="bg-brand-dark text-brand-light/40 border-t border-white/20 mt-auto py-4 px-4 sm:px-6 lg:px-8 w-full">
        <div class="mx-auto flex flex-col sm:flex-row justify-between items-center gap-2 text-xs" style="width: 95%;">
            <div>
                © {{ date('Y') }} SmakPrzeszłości. Panel: <span class="uppercase font-bold text-white/60">{{ auth()->user()->role }}</span>
            </div>
            <div>
                <span class="cursor-help" title="Wersja systemu v2.4.1">System POS v2.4</span>
            </div>
        </div>
    </footer>
@endguest

<script>
    (() => {
        const refreshIntervals = window.restaurantAutoRefreshIntervals ?? new Map();
        window.restaurantAutoRefreshIntervals = refreshIntervals;

        const hasActiveFormControl = (container) => {
            const activeElement = document.activeElement;

            return activeElement
                && container.contains(activeElement)
                && activeElement.matches('input, textarea, select, button, [contenteditable="true"]');
        };

        const setupAutoRefresh = () => {
            document.querySelectorAll('[data-auto-refresh]').forEach((container) => {
                if (!container.id || refreshIntervals.has(container.id)) {
                    return;
                }

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
                    if (pending || paused || document.hidden || hasActiveFormControl(container)) {
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

                        if (container.innerHTML === freshContainer.innerHTML) {
                            return;
                        }

                        const scrollX = window.scrollX;
                        const scrollY = window.scrollY;

                        container.innerHTML = freshContainer.innerHTML;
                        window.requestAnimationFrame(() => window.scrollTo(scrollX, scrollY));

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

                refreshIntervals.set(container.id, window.setInterval(refresh, refreshInterval));
            });
        };

        document.addEventListener('DOMContentLoaded', setupAutoRefresh);
    })();
</script>

</body>
</html>
