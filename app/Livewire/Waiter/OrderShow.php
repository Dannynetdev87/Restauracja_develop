<?php

namespace App\Livewire\Waiter;

use App\Models\Order;
use Livewire\Attributes\Locked;
use Livewire\Component;

class OrderShow extends Component
{
    #[Locked]
    public int $orderId;

    public function mount(Order $order): void
    {
        if ($order->waiter_id !== request()->user()->id) {
            abort(403);
        }

        $this->orderId = $order->id;
    }

    public function render()
    {
        return view('livewire.waiter.order-show', [
            'order' => Order::query()
                ->whereKey($this->orderId)
                ->where('waiter_id', request()->user()->id)
                ->with(['table', 'items.menuItem'])
                ->firstOrFail(),
        ]);
    }
}
