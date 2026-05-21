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

                <div class="flex flex-col gap-2 items-end">
                    <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-bold text-green-800">
                        {{ $order->status }}
                    </span>

                    <a href="{{ route('waiter.orders.receipt', $order) }}" target="_blank"
                       class="px-4 py-2 text-sm font-bold bg-brand-dark text-white rounded hover:bg-brand-accent transition">
                        🖨️ Drukuj rachunek
                    </a>
                </div>
            </div>

            <div class="mt-8">
                <h2 class="text-lg font-bold text-brand-dark mb-4">Pozycje zamówienia</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 text-gray-700 uppercase font-bold">
                        <tr>
                            <th class="px-4 py-3">Danie</th>
                            <th class="px-4 py-3">Ilość</th>
                            <th class="px-4 py-3">Cena jedn.</th>
                            <th class="px-4 py-3 text-right">Suma</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                        @forelse($order->items as $item)
                            <tr>
                                <td class="px-4 py-3">{{ $item->menuItem->name }}</td>
                                <td class="px-4 py-3">{{ $item->quantity }}</td>
                                <td class="px-4 py-3">{{ number_format($item->unit_price, 2) }} zł</td>
                                <td class="px-4 py-3 text-right">{{ number_format($item->quantity * $item->unit_price, 2) }} zł</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-3 text-center text-gray-500">Brak pozycji w zamówieniu.</td>
                            </tr>
                        @endforelse
                        </tbody>
                        <tfoot class="border-t-2 border-brand-dark">
                        <tr>
                            <td colspan="3" class="px-4 py-3 text-right font-bold text-lg">Razem:</td>
                            <td class="px-4 py-3 text-right font-black text-lg">{{ number_format($order->total(), 2) }} zł</td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-100 flex justify-end">
                @if($order->status !== 'zamknięte')
                    <form action="{{ route('waiter.orders.finish', $order) }}" method="POST">
                        @csrf
                        <button type="submit"
                                class="px-6 py-3 bg-green-600 text-white font-bold rounded-lg hover:bg-green-700 transition shadow-md">
                            ✅ Zakończ zamówienie i generuj rachunek
                        </button>
                    </form>
                @else
                    <div class="px-6 py-3 bg-gray-100 text-gray-600 font-bold rounded-lg">
                        Zamówienie jest już zamknięte
                    </div>
                @endif
            </div>
        </div>
    </section>
</x-app>
