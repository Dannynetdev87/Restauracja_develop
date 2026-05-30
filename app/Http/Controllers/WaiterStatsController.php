<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class WaiterStatsController extends Controller
{
    public function stats(Request $request)
    {
        return view('waiter.stats', $this->statsData($request->user()));
    }

    public function statsData(User $user): array
    {
        $paidPayments = Payment::query()
            ->whereHas('order', fn (Builder $query) => $query->where('waiter_id', $user->id))
            ->where('status', Payment::STATUS_PAID);

        $currentShift = $this->currentShift($user);

        $shiftTips = 0.0;
        $shiftOrdersCount = 0;

        if ($currentShift) {
            $shiftPayments = (clone $paidPayments)
                ->whereBetween('paid_at', [
                    $currentShift->startsAtDateTime(),
                    $currentShift->endsAtDateTime(),
                ]);

            $shiftTips = (float) (clone $shiftPayments)->sum('tip_amount');
            $shiftOrdersCount = (clone $shiftPayments)
                ->distinct('order_id')
                ->count('order_id');
        }

        return [
            'totalTips' => (float) (clone $paidPayments)->sum('tip_amount'),
            'shiftTips' => $shiftTips,
            'shiftOrdersCount' => $shiftOrdersCount,
            'hasActiveShift' => (bool) $currentShift,
        ];
    }

    private function currentShift(User $user): ?Schedule
    {
        return Schedule::query()
            ->where('user_id', $user->id)
            ->whereDate('date', now()->toDateString())
            ->get()
            ->first(fn (Schedule $schedule) => $schedule->state() === Schedule::STATE_RUNNING);
    }
}
