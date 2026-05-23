<x-app>
    <x-slot:title>Panel baru - SmakPrzeszłości</x-slot>

    @include('production.dashboard', [
        'panelLabel' => 'Panel baru',
        'title' => 'Napoje do przygotowania',
        'description' => 'Podgląd pozycji przypisanych do baru oraz zmiana statusów przygotowania.',
        'queueDescription' => 'Zamówienia zawierające napoje do obsłużenia.',
        'statusRouteName' => 'bar.order-items.status',
    ])
</x-app>
