<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Podsumowanie rachunku #{{ $order->id }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; padding: 0 !important; }
            #receipt-container { border: none !important; box-shadow: none !important; padding: 0 !important; }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen pt-10">

<div class="max-w-xl mx-auto bg-white p-8 rounded-lg shadow-md border border-gray-200" id="receipt-container">
    <div class="text-center mb-8 border-b pb-4">
        <h1 class="text-3xl font-black text-gray-900">SmakPrzeszłości</h1>
        <p class="text-gray-600 font-medium">Rachunek nr #{{ $order->id }}</p>
        <p class="text-sm text-gray-400">{{ now()->format('d.m.Y H:i') }}</p>
    </div>

    <table class="w-full text-left mb-8">
        <thead>
        <tr class="text-gray-500 uppercase text-xs">
            <th class="pb-2 px-2">Danie</th>
            <th class="pb-2 px-2 text-center">Ilość</th>
            <th class="pb-2 px-2 text-right">Cena</th>
        </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
        @foreach($order->items as $item)
            <tr class="text-gray-800">
                <td class="py-3 px-2">{{ $item->menuItem->name }}</td>
                <td class="py-3 px-2 text-center">{{ $item->quantity }}</td>
                <td class="py-3 px-2 text-right">{{ number_format($item->unit_price * $item->quantity, 2) }} zł</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="text-right text-2xl font-black text-gray-900 mb-8 pt-4 border-t border-gray-200">
        Suma: {{ number_format($order->total(), 2) }} zł
    </div>

    <div class="no-print flex flex-col gap-3">
        <button onclick="window.print()" class="w-full py-3 bg-gray-900 text-white font-bold rounded-lg hover:bg-black transition shadow-sm">
            🖨️ Drukuj rachunek
        </button>

        <form action="{{ route('waiter.orders.finish', $order) }}" method="POST" class="w-full">
            @csrf
            <button type="submit" class="w-full py-3 bg-green-600 text-white font-bold rounded-lg hover:bg-green-700 transition shadow-sm">
                ✅ Ostatecznie zakończ i zwolnij stolik
            </button>
        </form>

        <a href="{{ route('waiter.orders.show', $order) }}" class="text-center text-sm text-gray-500 hover:text-gray-800 hover:underline mt-2">
            ← Wróć do zamówienia
        </a>
    </div>
</div>

</body>
</html>
