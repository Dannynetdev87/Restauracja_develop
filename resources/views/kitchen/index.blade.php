<x-app>
    <x-slot:title>Panel kuchni - SmakPrzeszłości</x-slot>

    @include('production.dashboard', [
        'panelLabel' => 'Panel kuchni',
        'title' => 'Pozycje do przygotowania',
        'description' => 'Podgląd pozycji przypisanych do kuchni oraz zmiana statusów przygotowania.',
        'queueDescription' => 'Zamówienia zawierające pozycje kuchenne do obsłużenia.',
        'statusRouteName' => 'kitchen.order-items.status',
    ])
</x-app>
