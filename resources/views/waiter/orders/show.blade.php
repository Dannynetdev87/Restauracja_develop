<x-app>
    <x-slot:title>Zamówienie #{{ $order->id }} - SmakPrzeszłości</x-slot>

    <section class="w-full max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <a href="{{ route('waiter.tables.index') }}" class="text-sm font-bold text-brand-accent hover:text-brand-dark">← Wróć do stolików</a>

        @if(session('success'))
            <div class="mt-6 rounded-lg border border-green-700 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="mt-6 rounded-lg border border-brand-dark/15 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <span class="text-sm font-bold uppercase text-brand-accent">Panel kelnera</span>
                    <h1 class="mt-2 text-3xl font-black text-brand-dark">Zamówienie #{{ $order->id }}</h1>
                    <p class="mt-1 text-brand-accent">Stolik {{ $order->table->number }} · {{ $order->opened_at->format('d.m.Y H:i') }}</p>
                </div>
                <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-bold text-green-800">
                    {{ $order->status }}
                </span>
            </div>

            <div class="mt-6 rounded-md bg-brand-light px-4 py-3 text-sm text-brand-dark">
                Zamówienie zostało otwarte. Następny etap projektu to dodawanie pozycji z menu do tego zamówienia.
            </div>
        </div>
    </section>
</x-app>
