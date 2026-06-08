<?php

namespace App\Livewire\Waiter;

use App\Models\RestaurantTable;
use Livewire\Component;

class Tables extends Component
{
    public ?int $openReportTableId = null;

    public array $reportTypes = [];

    public array $reportMessages = [];

    public function openReportForm(int $tableId): void
    {
        $this->openReportTableId = $tableId;
    }

    public function closeReportForm(): void
    {
        if ($this->openReportTableId !== null) {
            unset($this->reportTypes[$this->openReportTableId], $this->reportMessages[$this->openReportTableId]);
        }

        $this->openReportTableId = null;
    }

    public function render()
    {
        $waiterId = request()->user()->id;

        return view('livewire.waiter.tables', [
            'tables' => $this->visibleTables($waiterId),
            'statuses' => $this->statuses(),
        ]);
    }

    private function visibleTables(int $waiterId)
    {
        return RestaurantTable::query()
            ->visibleForWaiter($waiterId)
            ->with([
                'zone',
                'activeOrders' => fn ($query) => $query
                    ->where('waiter_id', $waiterId)
                    ->with(['items.menuItem'])
                    ->latest('opened_at'),
            ])
            ->orderBy('number')
            ->get();
    }

    private function statuses(): array
    {
        return [
            RestaurantTable::STATUS_FREE => 'Wolny',
            RestaurantTable::STATUS_OCCUPIED => 'Zajęty',
            RestaurantTable::STATUS_RESERVED => 'Zarezerwowany',
            RestaurantTable::STATUS_INACTIVE => 'Nieaktywny',
        ];
    }
}
