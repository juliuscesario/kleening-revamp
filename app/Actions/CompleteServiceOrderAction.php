<?php

namespace App\Actions;

use App\Models\ServiceOrder;
use Illuminate\Support\Facades\DB;

class CompleteServiceOrderAction
{
    public function execute(ServiceOrder $serviceOrder): ServiceOrder
    {
        return DB::transaction(function () use ($serviceOrder) {
            // Mark all non-cancel, non-done sessions as done
            $serviceOrder->sessions()
                ->whereNotIn('status', ['cancel', 'done'])
                ->update([
                    'status' => 'done',
                    'completed_at' => now(),
                ]);

            // Derive SO status from session statuses (canonical path)
            (new \App\Actions\SyncServiceOrderStatusAction())->execute($serviceOrder);

            return $serviceOrder->fresh();
        });
    }
}
