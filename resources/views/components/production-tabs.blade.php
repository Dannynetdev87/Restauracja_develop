@php
    $isKitchenRoute = request()->routeIs('kitchen.*');
    $dashboardRouteName = $isKitchenRoute ? 'kitchen.dashboard' : 'bar.dashboard';
    $currentRouteName = $isKitchenRoute ? 'kitchen.current' : 'bar.current';
    $baseTabClass = 'inline-flex min-h-12 w-full items-center justify-center rounded-xl border px-6 py-3 text-sm font-black uppercase text-center transition-all duration-200 ease-out focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-accent/40';
    $activeTabClass = 'border-brand-dark bg-brand-dark text-brand-light shadow-sm';
    $inactiveTabClass = 'border-brand-dark bg-brand-light/50 text-brand-dark hover:bg-white hover:shadow-sm';
@endphp

<nav class="mb-8 grid w-full max-w-md grid-cols-1 gap-3 sm:mx-auto sm:grid-cols-2" aria-label="Widoki produkcji">
    <a href="{{ route($currentRouteName) }}"
       class="{{ $baseTabClass }} {{ request()->routeIs($currentRouteName) ? $activeTabClass : $inactiveTabClass }}">
        AKTUALNOŚCI
    </a>
    <a href="{{ route($dashboardRouteName) }}"
       class="{{ $baseTabClass }} {{ request()->routeIs($dashboardRouteName) ? $activeTabClass : $inactiveTabClass }}">
        DASHBOARD
    </a>
</nav>
