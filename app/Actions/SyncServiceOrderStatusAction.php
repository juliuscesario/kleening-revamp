<?php

namespace App\Actions;

use App\Models\ServiceOrder;
use Illuminate\Support\Facades\DB;

class SyncServiceOrderStatusAction
{
    public function execute(ServiceOrder $serviceOrder): ServiceOrder
    {
        return DB::transaction(function () use ($serviceOrder) {
            $sessions = $serviceOrder->sessions()->get();
            $statuses = $sessions->pluck('status');

            // If somehow no sessions exist, default to booked
            if ($statuses->isEmpty()) {
                $serviceOrder->update(['status' => 'booked']);
                return $serviceOrder->fresh();
            }

            // All cancel → cancel
            if ($statuses->every(fn ($s) => $s === 'cancel')) {
                $newStatus = 'cancel';
            }
            // All non-cancel are done → done
            elseif ($statuses->filter(fn ($s) => $s !== 'cancel')->every(fn ($s) => $s === 'done')) {
                $newStatus = 'done';
            }
            // Any proses, OR mix of done + booked → proses
            elseif ($statuses->contains('proses') ||
                ($statuses->contains('done') && $statuses->contains('booked'))) {
                $newStatus = 'proses';
            }
            // Default (all booked, or cancel + booked)
            else {
                $newStatus = 'booked';
            }

            $serviceOrder->update(['status' => $newStatus]);

            // Also update is_multi_session flag
            $activeCount = $sessions->where('status', '!=', 'cancel')->count();
            $serviceOrder->update(['is_multi_session' => $activeCount > 1]);

            return $serviceOrder->fresh();
        });
    }
}
