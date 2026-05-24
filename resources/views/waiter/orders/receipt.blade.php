<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Rachunek #{{ $order->id }}</title>
    <style>
        body { font-family: sans-serif; padding: 20px; color: #333; }
        .receipt-box { max-width: 400px; margin: auto; border: 1px solid #ccc; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { text-align: left; padding: 8px; border-bottom: 1px solid #eee; }
        .total { font-size: 1.2em; font-weight: bold; text-align: right; margin-top: 20px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
<div class="receipt-box">
    <h2 style="text-align: center;">SmakPrzeszłości</h2>
    <h1 style="text-align: center; font-size: 1.5rem;">Rachunek nr #{{ $order->id }}</h1>
    <p style="text-align: center;">Stolik: {{ $order->table->number }}</p>

    <table>
        <thead>
        <tr>
            <th>Danie</th>
            <th>Ilość</th>
            <th>Cena</th>
        </tr>
        </thead>
        <tbody>
        @foreach($order->items as $item)
            <tr>
                <td>{{ $item->menuItem->name }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ number_format($item->unit_price * $item->quantity, 2) }} zł</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="total">
        Suma końcowa: {{ number_format($order->total(), 2) }} zł
    </div>

    <button class="no-print" onclick="window.print()" style="margin-top: 20px; width: 100%; padding: 10px; cursor: pointer; background: #000; color: #fff; border: none;">
        Drukuj rachunek
    </button>
</div>
</body>
</html>
