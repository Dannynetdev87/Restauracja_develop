<x-app>
    <x-slot:title>Historia zamówień - SmakPrzeszłości</x-slot>

    <section class="w-full max-w-7xl mx-auto px-4 py-10 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-col items-start justify-between gap-4 border-b border-brand-dark/10 pb-6 sm:flex-row sm:items-center">
            <div>
                <span class="text-sm font-bold uppercase text-brand-accent">Panel managera</span>
                <h1 class="mt-1 text-3xl font-black tracking-tight text-brand-dark">Historia zamówień</h1>
                <p class="mt-1 text-sm text-brand-accent">Przegląd zamówień z filtrowaniem po dacie, kelnerze i stoliku.</p>
            </div>

            <a href="{{ route('manager.dashboard') }}"
               class="w-full rounded-md border border-brand-dark/20 bg-white px-4 py-2 text-center text-sm font-bold text-brand-dark hover:bg-brand-light sm:w-auto">
                Wróć do dashboardu
            </a>
        </div>

        <form method="GET" action="{{ route('manager.orders.history') }}" class="mb-6 grid min-w-0 gap-4 rounded-xl border border-brand-dark/15 bg-white p-4 shadow-sm sm:p-5 md:grid-cols-5">
            <div>
                <label for="date_from" class="block text-xs font-bold uppercase text-brand-accent">Data od</label>
                <input id="date_from" name="date_from" type="date" value="{{ request('date_from') }}"
                       class="mt-1 w-full rounded-md border border-brand-dark/20 px-3 py-2 text-sm text-brand-dark focus:border-brand-dark focus:outline-none">
            </div>

            <div>
                <label for="date_to" class="block text-xs font-bold uppercase text-brand-accent">Data do</label>
                <input id="date_to" name="date_to" type="date" value="{{ request('date_to') }}"
                       class="mt-1 w-full rounded-md border border-brand-dark/20 px-3 py-2 text-sm text-brand-dark focus:border-brand-dark focus:outline-none">
            </div>

            <div>
                <label for="waiter_id" class="block text-xs font-bold uppercase text-brand-accent">Kelner</label>
                <select id="waiter_id" name="waiter_id" class="mt-1 w-full max-w-full rounded-md border border-brand-dark/20 bg-white px-3 py-2 text-sm text-brand-dark overflow-hidden text-ellipsis focus:border-brand-dark focus:outline-none">
                    <option value="">Wszyscy</option>
                    @foreach($waiters as $waiter)
                        <option value="{{ $waiter->id }}" @selected((string) request('waiter_id') === (string) $waiter->id)>
                            {{ $waiter->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="restaurant_table_id" class="block text-xs font-bold uppercase text-brand-accent">Stolik</label>
                <select id="restaurant_table_id" name="restaurant_table_id" class="mt-1 w-full max-w-full rounded-md border border-brand-dark/20 bg-white px-3 py-2 text-sm text-brand-dark overflow-hidden text-ellipsis focus:border-brand-dark focus:outline-none">
                    <option value="">Wszystkie</option>
                    @foreach($tables as $table)
                        <option value="{{ $table->id }}" @selected((string) request('restaurant_table_id') === (string) $table->id)>
                            Stolik {{ $table->number }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full rounded-md bg-brand-dark px-4 py-2 text-sm font-bold text-brand-light hover:bg-brand-accent">
                    Filtruj
                </button>
            </div>
        </form>

        <div class="rounded-xl border border-brand-dark/15 bg-white p-4 shadow-sm sm:p-5">
            <div class="w-full overflow-x-auto">
                <table class="min-w-full whitespace-nowrap text-left text-sm">
                    <thead class="border-b border-brand-dark/10 text-xs uppercase text-brand-accent">
                    <tr>
                        <th class="py-3 pr-4">Zamówienie</th>
                        <th class="py-3 pr-4">Data</th>
                        <th class="py-3 pr-4">Stolik</th>
                        <th class="py-3 pr-4">Kelner</th>
                        <th class="py-3 pr-4">Status</th>
                        <th class="py-3 pr-4">Płatność</th>
                        <th class="py-3 text-right">Suma</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-brand-dark/10">
                    @forelse($orders as $order)
                        @php
                            $paidPayment = $order->payments->firstWhere('status', $paidStatus);
                        @endphp

                        <tr>
                            <td class="py-3 pr-4">
                                <strong class="block font-black text-brand-dark">#{{ $order->id }}</strong>
                                <span class="mt-1 block max-w-xs text-xs text-brand-accent">
                                    {{ $order->items->map(fn ($item) => $item->menuItem->name)->join(', ') }}
                                </span>
                            </td>
                            <td class="py-3 pr-4">{{ $order->opened_at?->format('d.m.Y H:i') ?? '-' }}</td>
                            <td class="py-3 pr-4">Stolik {{ $order->table->number }}</td>
                            <td class="py-3 pr-4">{{ $order->waiter->full_name }}</td>
                            <td class="py-3 pr-4">{{ $order->status }}</td>
                            <td class="py-3 pr-4">{{ $paidPayment?->payment_method ?? 'brak' }}</td>
                            <td class="py-3 text-right font-black text-brand-dark">{{ number_format($order->total(), 2, ',', ' ') }} zł</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center font-semibold text-brand-accent">
                                Brak zamówień dla wybranych filtrów.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</x-app>
