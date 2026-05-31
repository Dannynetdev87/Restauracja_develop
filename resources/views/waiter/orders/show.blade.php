<x-app>
    <x-slot:title>Zamówienie #{{ $order->id }} - SmakPrzeszłości</x-slot>

    @livewire('waiter.order-show', ['order' => $order], key('waiter-order-show-'.$order->id))
</x-app>
