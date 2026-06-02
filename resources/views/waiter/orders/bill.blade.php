<x-app>
    <x-slot:title>Rachunek zamówienia #{{ $order->id }} - SmakPrzeszłości</x-slot>

    @php
        $activeItems = $order->items->where('status', '!=', \App\Models\OrderItem::STATUS_CANCELLED);

        // Obliczanie pozostałej kwoty na podstawie nieopłaconych sztuk
        $remainingAmount = $activeItems->sum(function ($item) {
            $paidQty = $item->payments->where('status', \App\Models\Payment::STATUS_PAID)->sum('pivot.quantity');
            return max(0, $item->quantity - $paidQty) * $item->unit_price;
        });

        $paidPayments = $order->payments->where('status', \App\Models\Payment::STATUS_PAID);
        $paidAmount = $paidPayments->sum(fn ($payment) => (float) $payment->amount);
        $paidTips = $paidPayments->sum(fn ($payment) => (float) ($payment->tip_amount ?? 0));
        $canPay = $order->status === \App\Models\Order::STATUS_SERVED && $remainingAmount > 0;

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

        <form method="POST" action="{{ route('waiter.orders.payments.store', $order) }}" id="payment-form">
            @csrf

            <div class="grid gap-6 lg:grid-cols-[1fr_340px]">
                <div class="rounded-lg border border-brand-dark/15 bg-white p-6 shadow-sm">
                    <div class="mb-6 border-b border-brand-dark/10 pb-5">
                        <span class="text-sm font-bold uppercase text-brand-accent">Pozycje rachunku</span>
                        <h2 class="mt-2 text-2xl font-black text-brand-dark">Wybierz pozycje i ilości do rozliczenia</h2>
                    </div>

                    <div class="divide-y divide-brand-dark/10">
                        @forelse($order->items as $item)
                            @php
                                $isCancelled = $item->status === \App\Models\OrderItem::STATUS_CANCELLED;
                                $paidQty = $item->payments->where('status', \App\Models\Payment::STATUS_PAID)->sum('pivot.quantity');
                                $remainingQty = max(0, $item->quantity - $paidQty);
                                $isFullyPaid = ! $isCancelled && $remainingQty === 0 && $item->quantity > 0;
                                $isPartiallyPaid = ! $isCancelled && $paidQty > 0 && $remainingQty > 0;
                            @endphp

                            <div class="grid gap-4 py-4 grid-cols-[auto_auto_1fr] md:grid-cols-[40px_140px_1fr_130px_130px] md:items-center {{ $isCancelled || $isFullyPaid ? 'opacity-65' : '' }}">
                                <div class="flex items-center justify-center">
                                    @if($canPay && ! $isCancelled && $remainingQty > 0)
                                        <input
                                            type="checkbox"
                                            name="item_ids[]"
                                            value="{{ $item->id }}"
                                            class="item-checkbox h-5 w-5 rounded border-brand-dark/20 text-brand-dark focus:ring-brand-dark"
                                            checked
                                        >
                                    @else
                                        <div class="flex h-5 w-5 items-center justify-center">
                                            @if($isFullyPaid)
                                                <span class="text-base font-black text-green-700">✓</span>
                                            @else
                                                <span class="text-lg text-gray-300">-</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                <div class="flex items-center gap-2">
                                    @if($canPay && ! $isCancelled && $remainingQty > 0)
                                        <input
                                            type="number"
                                            name="quantities[{{ $item->id }}]"
                                            value="{{ $remainingQty }}"
                                            min="1"
                                            max="{{ $remainingQty }}"
                                            data-item-id="{{ $item->id }}"
                                            data-unit-price="{{ number_format($item->unit_price, 2, '.', '') }}"
                                            class="item-quantity-input w-20 rounded border-brand-dark/20 px-2 py-1 text-sm font-black text-brand-dark focus:border-brand-dark focus:ring-brand-dark"
                                        >
                                        <span class="text-xs text-brand-accent font-semibold">z {{ $remainingQty }}</span>
                                    @else
                                        <div class="w-fit rounded-md px-3 py-2 text-sm font-black text-brand-light {{ $isCancelled ? 'bg-gray-400' : ($isFullyPaid ? 'bg-green-700' : 'bg-brand-dark') }}">
                                            {{ $item->quantity }}x
                                        </div>
                                    @endif
                                </div>

                                <div class="col-span-3 md:col-span-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="font-black text-brand-dark {{ $isCancelled ? 'line-through text-gray-400' : '' }} {{ $isFullyPaid ? 'text-gray-500' : '' }}">
                                            {{ $item->menuItem->name }}
                                        </h3>

                                        @if($isCancelled)
                                            <span class="rounded-md bg-red-100 px-2.5 py-1 text-xs font-bold text-red-800">Anulowane</span>
                                        @elseif($isFullyPaid)
                                            <span class="rounded-md bg-green-100 px-2.5 py-1 text-xs font-bold text-green-800">Opłacone</span>
                                        @elseif($isPartiallyPaid)
                                            <span class="rounded-md bg-yellow-100 px-2.5 py-1 text-xs font-bold text-yellow-800">Opłacono {{ $paidQty }} z {{ $item->quantity }}</span>
                                        @endif
                                    </div>

                                    @if($item->notes)
                                        <p class="mt-2 rounded-md bg-brand-light px-3 py-2 text-sm text-brand-dark">
                                            {{ $item->notes }}
                                        </p>
                                    @endif
                                </div>

                                <div class="text-left md:text-right">
                                    <span class="block text-xs font-bold uppercase text-brand-accent">Cena jedn.</span>
                                    <strong class="text-brand-dark">{{ number_format($item->unit_price, 2, ',', ' ') }} zł</strong>
                                </div>

                                <div class="text-left md:text-right">
                                    <span class="block text-xs font-bold uppercase text-brand-accent">Suma pozycji</span>
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
                    <span class="text-sm font-bold uppercase text-brand-accent">Podsumowanie rachunku</span>

                    <div class="mt-4 space-y-3 text-sm text-brand-dark">
                        <div class="flex justify-between gap-4">
                            <span>Suma całego zamówienia</span>
                            <span class="font-bold">{{ number_format($order->total(), 2, ',', ' ') }} zł</span>
                        </div>
                        <div class="flex justify-between gap-4">
                            <span>Łącznie już opłacono</span>
                            <span class="font-bold text-green-800">{{ number_format($paidAmount, 2, ',', ' ') }} zł</span>
                        </div>
                        <div class="flex justify-between gap-4">
                            <span>Pozostało do rozliczenia</span>
                            <span class="font-bold">{{ number_format($remainingAmount, 2, ',', ' ') }} zł</span>
                        </div>

                        @if($paidPayments->isNotEmpty())
                            <div class="border-t border-brand-dark/10 pt-3 space-y-1.5">
                                <span class="block text-xs font-bold uppercase text-brand-accent">Zarejestrowane płatności</span>
                                @foreach($paidPayments as $payment)
                                    <div class="rounded-md bg-green-50/70 px-2 py-1 text-xs text-green-900">
                                        <div class="flex justify-between gap-2">
                                            <span>{{ $paymentMethods[$payment->payment_method] ?? $payment->payment_method }} {{ $payment->paid_at?->format('H:i') }}</span>
                                            <strong>{{ number_format($payment->amount, 2, ',', ' ') }} zł</strong>
                                        </div>
                                        @if((float) $payment->tip_amount > 0)
                                            <div class="mt-0.5 flex justify-between gap-2 text-green-800">
                                                <span>Napiwek</span>
                                                <strong>{{ number_format($payment->tip_amount, 2, ',', ' ') }} zł</strong>
                                            </div>
                                        @endif
                                        @if((float) $payment->discount_amount > 0)
                                            <div class="mt-0.5 flex justify-between gap-2 text-green-800">
                                                <span>Rabat{{ $payment->discountCode ? ' '.$payment->discountCode->code : '' }}</span>
                                                <strong>-{{ number_format($payment->discount_amount, 2, ',', ' ') }} zł</strong>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="border-t border-brand-dark/10 pt-3">
                            <span class="block text-sm font-bold text-brand-dark">Napiwek dla kelnera</span>
                            <p class="mt-1 text-xs text-brand-accent">Wybierz procent od aktualnie zaznaczonych pozycji albo wpisz własną kwotę.</p>

                            <input
                                id="tip_amount"
                                name="tip_amount"
                                type="hidden"
                                value="{{ old('tip_amount', '0.00') }}"
                                data-initial-tip="{{ old('tip_amount', '0.00') }}"
                                @disabled(! $canPay)
                            >

                            <div class="mt-3 grid grid-cols-3 gap-2" role="group" aria-label="Wybór napiwku">
                                <button type="button" class="tip-option rounded-md border border-brand-dark/20 bg-white px-3 py-2 text-sm font-bold text-brand-dark transition hover:border-brand-accent" data-tip-type="percent" data-tip-value="5" @disabled(! $canPay)>5%</button>
                                <button type="button" class="tip-option rounded-md border border-brand-dark/20 bg-white px-3 py-2 text-sm font-bold text-brand-dark transition hover:border-brand-accent" data-tip-type="percent" data-tip-value="10" @disabled(! $canPay)>10%</button>
                                <button type="button" class="tip-option rounded-md border border-brand-dark/20 bg-white px-3 py-2 text-sm font-bold text-brand-dark transition hover:border-brand-accent" data-tip-type="percent" data-tip-value="15" @disabled(! $canPay)>15%</button>
                                <button type="button" class="tip-option rounded-md border border-brand-dark/20 bg-white px-3 py-2 text-sm font-bold text-brand-dark transition hover:border-brand-accent" data-tip-type="none" data-tip-value="0" @disabled(! $canPay)>Bez napiwku</button>
                                <button type="button" class="tip-option col-span-2 rounded-md border border-brand-dark/20 bg-white px-3 py-2 text-sm font-bold text-brand-dark transition hover:border-brand-accent" data-tip-type="custom" @disabled(! $canPay)>Własna kwota</button>
                            </div>

                            <div id="custom-tip-wrapper" class="mt-3 hidden">
                                <label for="custom_tip_amount" class="block text-xs font-bold uppercase text-brand-accent">Kwota napiwku</label>
                                <input
                                    id="custom_tip_amount"
                                    type="number"
                                    min="0"
                                    max="9999.99"
                                    step="0.01"
                                    value="{{ old('tip_amount', '0.00') }}"
                                    class="mt-1 w-full rounded-md border border-brand-dark/20 bg-white px-3 py-2 text-sm text-brand-dark focus:border-brand-dark focus:outline-none"
                                    @disabled(! $canPay)
                                >
                            </div>

                            @if($paidTips > 0)
                                <p class="mt-1 text-xs text-brand-accent">Dotychczas zapisane napiwki: {{ number_format($paidTips, 2, ',', ' ') }} zł</p>
                            @endif
                        </div>

                        <div class="flex flex-col gap-1 border-t border-brand-dark/10 pt-3">
                            <span class="text-xs font-bold uppercase text-brand-accent">Do zapłaty teraz</span>
                            <div class="space-y-1 text-sm">
                                <div class="flex justify-between">
                                    <span>Wybrane pozycje</span>
                                    <strong><span id="selected-total">0,00</span> zł</strong>
                                </div>
                                <div class="flex justify-between">
                                    <span>Napiwek</span>
                                    <strong><span id="tip-total">0,00</span> zł</strong>
                                </div>
                                <div class="flex justify-between border-t border-brand-dark/10 pt-2 text-2xl font-black text-brand-dark">
                                    <span>Razem</span>
                                    <span><span id="payment-total">0,00</span> zł</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($canPay)
                        <div class="mt-5 space-y-4">
                            <div>
                                <label for="discount_code" class="block text-sm font-bold text-brand-dark">Kod rabatowy</label>
                                <input
                                    id="discount_code"
                                    name="discount_code"
                                    type="text"
                                    value="{{ old('discount_code') }}"
                                    placeholder="Opcjonalnie"
                                    class="mt-1 w-full rounded-md border border-brand-dark/20 bg-white px-3 py-2 text-sm uppercase text-brand-dark focus:border-brand-dark focus:outline-none"
                                >
                            </div>

                            <div>
                                <label for="payment_method" class="block text-sm font-bold text-brand-dark">Metoda płatności</label>
                                <select
                                    id="payment_method"
                                    name="payment_method"
                                    required
                                    class="mt-1 w-full rounded-md border border-brand-dark/20 bg-white px-3 py-2 text-sm text-brand-dark focus:border-brand-dark focus:outline-none"
                                >
                                    <option value="">-- Wybierz metodę --</option>
                                    @foreach($paymentMethods as $method => $label)
                                        <option value="{{ $method }}" @selected(old('payment_method') === $method)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <button id="payment-submit" type="submit" class="w-full rounded-md bg-brand-dark px-4 py-3 text-sm font-bold text-brand-light shadow transition-colors hover:bg-brand-accent">
                                Zatwierdź płatność
                            </button>
                        </div>
                    @elseif(in_array($order->status, [\App\Models\Order::STATUS_PAID, \App\Models\Order::STATUS_CLOSED], true))
                        <div class="mt-5 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">
                            Zamówienie zostało w całości opłacone i zamknięte.
                        </div>
                    @else
                        <div class="mt-5 rounded-md border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm font-semibold text-yellow-800">
                            Płatność będzie dostępna po dostarczeniu wszystkich pozycji.
                        </div>
                    @endif
                </aside>
            </div>
        </form>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const checkboxes = Array.from(document.querySelectorAll('.item-checkbox'));
            const qtyInputs = Array.from(document.querySelectorAll('.item-quantity-input'));
            const tipInput = document.getElementById('tip_amount');
            const customTipWrapper = document.getElementById('custom-tip-wrapper');
            const customTipInput = document.getElementById('custom_tip_amount');
            const tipOptions = Array.from(document.querySelectorAll('.tip-option'));
            const selectedTotal = document.getElementById('selected-total');
            const tipTotal = document.getElementById('tip-total');
            const paymentTotal = document.getElementById('payment-total');
            const submitButton = document.getElementById('payment-submit');
            let selectedTipType = 'none';
            let selectedTipValue = 0;
            const formatter = new Intl.NumberFormat('pl-PL', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });

            const parseAmount = (value) => {
                const amount = Number.parseFloat(String(value || '0').replace(',', '.'));
                return Number.isFinite(amount) ? amount : 0;
            };

            const initialTipAmount = parseAmount(tipInput?.dataset.initialTip);
            selectedTipType = initialTipAmount > 0 ? 'custom' : 'none';

            const setActiveTipOption = () => {
                tipOptions.forEach((option) => {
                    const isActive = option.dataset.tipType === selectedTipType
                        && (selectedTipType === 'custom' || parseAmount(option.dataset.tipValue) === selectedTipValue);

                    option.classList.toggle('border-brand-accent', isActive);
                    option.classList.toggle('bg-brand-light', isActive);
                    option.classList.toggle('text-brand-dark', isActive);
                    option.classList.toggle('ring-2', isActive);
                    option.classList.toggle('ring-brand-accent', isActive);
                    option.classList.toggle('shadow-md', isActive);
                });

                customTipWrapper?.classList.toggle('hidden', selectedTipType !== 'custom');
            };

            const refreshTotals = () => {
                let selectedAmount = 0;

                checkboxes.forEach((checkbox) => {
                    if (checkbox.checked) {
                        const itemId = checkbox.value;
                        const qtyInput = document.querySelector(`.item-quantity-input[data-item-id="${itemId}"]`);
                        if (qtyInput) {
                            const qty = parseInt(qtyInput.value, 10) || 0;
                            const unitPrice = parseAmount(qtyInput.dataset.unitPrice);
                            selectedAmount += qty * unitPrice;
                        }
                    }
                });

                const tipAmount = selectedTipType === 'custom'
                    ? parseAmount(customTipInput?.value)
                    : selectedTipType === 'percent'
                        ? Math.round(selectedAmount * selectedTipValue) / 100
                        : 0;

                if (selectedTotal) {
                    selectedTotal.textContent = formatter.format(selectedAmount);
                }

                if (tipInput) {
                    tipInput.value = tipAmount.toFixed(2);
                }

                if (tipTotal) {
                    tipTotal.textContent = formatter.format(tipAmount);
                }

                if (paymentTotal) {
                    paymentTotal.textContent = formatter.format(selectedAmount + tipAmount);
                }

                if (submitButton) {
                    submitButton.disabled = selectedAmount <= 0;
                    submitButton.classList.toggle('opacity-60', selectedAmount <= 0);
                    submitButton.classList.toggle('cursor-not-allowed', selectedAmount <= 0);
                }
            };

            tipOptions.forEach((option) => {
                option.addEventListener('click', () => {
                    selectedTipType = option.dataset.tipType || 'none';
                    selectedTipValue = parseAmount(option.dataset.tipValue);
                    setActiveTipOption();
                    refreshTotals();
                });
            });

            checkboxes.forEach((checkbox) => checkbox.addEventListener('change', refreshTotals));
            qtyInputs.forEach((input) => {
                input.addEventListener('input', (e) => {
                    // Blokada wpisania wartości wyższych niż max w UI
                    const max = parseInt(input.max, 10);
                    if (parseInt(input.value, 10) > max) {
                        input.value = max;
                    }
                    refreshTotals();
                });
            });
            customTipInput?.addEventListener('input', refreshTotals);
            setActiveTipOption();
            refreshTotals();
        });
    </script>
</x-app>
