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

            // Set SO status directly to done
            $serviceOrder->update(['status' => 'done']);

            return $serviceOrder->fresh();
        });
    }
}
