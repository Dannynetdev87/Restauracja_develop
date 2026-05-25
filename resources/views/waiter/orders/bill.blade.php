<x-app>
    <x-slot:title>Rachunek zamówienia #{{ $order->id }} - SmakPrzeszłości</x-slot>

    @php
        $paidPayment = $order->payments->firstWhere('status', \App\Models\Payment::STATUS_PAID);
        $canPay = $order->status === \App\Models\Order::STATUS_SERVED && ! $paidPayment;
        $statusLabel = match ($order->status) {
            \App\Models\Order::STATUS_OPEN => 'Otwarte',
            \App\Models\Order::STATUS_IN_PROGRESS => 'W przygotowaniu',
            \App\Models\Order::STATUS_READY => 'Gotowe',
            \App\Models\Order::STATUS_SERVED => 'Wydane',
            \App\Models\Order::STATUS_PAID => 'Opłacone',
            \App\Models\Order::STATUS_CLOSED => 'Zamknięte',
            \App\Models\Order::STATUS_CANCELLED => 'Anulowane',
            default => $order->status,
        };
    @endphp

    <section class="w-full max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="mb-8 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <a href="{{ route('waiter.orders.show', $order) }}" class="text-sm font-bold text-brand-accent hover:text-brand-dark">Wróć do zamówienia</a>
                <h1 class="mt-3 text-3xl font-black text-brand-dark">Rachunek #{{ $order->id }}</h1>
                <p class="mt-1 text-brand-accent">
                    Stolik {{ $order->table->number }} · kelner: {{ $order->waiter->full_name }} · {{ $order->opened_at->format('d.m.Y H:i') }}
                </p>
            </div>

            <span class="w-fit rounded-md bg-brand-light px-3 py-1 text-xs font-bold uppercase text-brand-dark">
                {{ $statusLabel }}
            </span>
        </div>

        @if(session('success'))
            <div class="mb-6 rounded-lg border border-green-700 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 rounded-lg border border-red-700 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-[1fr_320px]">
            <div class="rounded-lg border border-brand-dark/15 bg-white p-6 shadow-sm">
                <div class="mb-6 border-b border-brand-dark/10 pb-5">
                    <span class="text-sm font-bold uppercase text-brand-accent">Pozycje rachunku</span>
                    <h2 class="mt-2 text-2xl font-black text-brand-dark">Podsumowanie zamówienia</h2>
                </div>

                <div class="divide-y divide-brand-dark/10">
                    @forelse($order->items as $item)
                        @php
                            $isCancelled = $item->status === \App\Models\OrderItem::STATUS_CANCELLED;
                        @endphp

                        <div class="grid gap-4 py-4 md:grid-cols-[72px_1fr_150px_150px] md:items-start {{ $isCancelled ? 'opacity-70' : '' }}">
                            <div class="w-fit rounded-md px-3 py-2 text-sm font-black text-brand-light {{ $isCancelled ? 'bg-gray-500' : 'bg-brand-dark' }}">
                                {{ $item->quantity }}x
                            </div>

                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="font-black text-brand-dark {{ $isCancelled ? 'line-through' : '' }}">{{ $item->menuItem->name }}</h3>
                                    @if($isCancelled)
                                        <span class="rounded-md bg-red-100 px-2.5 py-1 text-xs font-bold text-red-800">
                                            Anulowane
                                        </span>
                                    @endif
                                </div>
                                @if($item->notes)
                                    <p class="mt-2 rounded-md bg-brand-light px-3 py-2 text-sm text-brand-dark">
                                        {{ $item->notes }}
                                    </p>
                                @endif
                            </div>

                            <div class="text-left md:text-right">
                                <span class="block text-xs font-bold uppercase text-brand-accent">Cena</span>
                                <strong class="text-brand-dark">{{ number_format($item->unit_price, 2, ',', ' ') }} zł</strong>
                            </div>

                            <div class="text-left md:text-right">
                                <span class="block text-xs font-bold uppercase text-brand-accent">Suma</span>
                                <strong class="text-lg text-brand-dark">
                                    {{ $isCancelled ? '0,00' : number_format($item->subtotal(), 2, ',', ' ') }} zł
                                </strong>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-md bg-brand-light px-4 py-3 text-sm text-brand-dark">
                            Zamówienie nie ma pozycji do rozliczenia.
                        </div>
                    @endforelse
                </div>
            </div>

            <aside class="h-fit rounded-lg border border-brand-dark/15 bg-white p-5 shadow-sm lg:sticky lg:top-28">
                <span class="text-sm font-bold uppercase text-brand-accent">Do zapłaty</span>
                <div class="mt-4 space-y-3 text-sm text-brand-dark">
                    <div class="flex justify-between gap-4">
                        <span>Liczba pozycji</span>
                        <strong>{{ $order->items->where('status', '!=', \App\Models\OrderItem::STATUS_CANCELLED)->sum('quantity') }}</strong>
                    </div>
                    <div class="flex justify-between gap-4 border-t border-brand-dark/10 pt-3 text-lg">
                        <span>Razem</span>
                        <strong>{{ number_format($order->total(), 2, ',', ' ') }} zł</strong>
                    </div>
                </div>

                @if($paidPayment)
                    <div class="mt-5 rounded-md bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">
                        Opłacono {{ number_format($paidPayment->amount, 2, ',', ' ') }} zł metodą {{ $paymentMethods[$paidPayment->payment_method] ?? $paidPayment->payment_method }}.
                    </div>
                @elseif($canPay)
                    <form method="POST" action="{{ route('waiter.orders.payments.store', $order) }}" class="mt-5 space-y-4">
                        @csrf

                        <div>
                            <label for="payment_method" class="block text-sm font-bold text-brand-dark">Metoda płatności</label>
                            <select id="payment_method"
                                    name="payment_method"
                                    required
                                    class="mt-1 w-full rounded-md border border-brand-dark/20 bg-white px-3 py-2 text-sm text-brand-dark focus:border-brand-dark focus:outline-none">
                                @foreach($paymentMethods as $method => $label)
                                    <option value="{{ $method }}" @selected(old('payment_method') === $method)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="w-full rounded-md bg-brand-dark px-4 py-3 text-sm font-bold text-brand-light hover:bg-brand-accent">
                            Zatwierdź płatność
                        </button>
                    </form>
                @else
                    <div class="mt-5 rounded-md bg-yellow-50 px-4 py-3 text-sm font-semibold text-yellow-800">
                        Płatność będzie dostępna po dostarczeniu wszystkich pozycji.
                    </div>
                @endif
            </aside>
        </div>
    </section>
</x-app>
