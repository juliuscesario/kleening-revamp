<?php

namespace App\Actions;

use App\Models\OrderSession;
use Illuminate\Support\Facades\DB;

class UpdateOrderSessionAction
{
    public function execute(OrderSession $session, array $data, ?array $staffIds = null): OrderSession
    {
        return DB::transaction(function () use ($session, $data, $staffIds) {
            $oldStatus = $session->status;
            $newStatus = $data['status'] ?? $oldStatus;

            // Normalize jam field if present
            if (isset($data['jam']) && !empty($data['jam']) && strlen($data['jam']) === 5) {
                $data['jam'] = $data['jam'] . ':00';
            }

            // Handle timestamps based on status change
            if ($newStatus !== $oldStatus) {
                if ($newStatus === 'proses' && !$session->started_at) {
                    $data['started_at'] = now();
                }
                if ($newStatus === 'done' && !$session->completed_at) {
                    $data['completed_at'] = now();
                }
            }

            // 1. Update session fields (single update call)
            if (!empty($data)) {
                $session->update($data);
            }

            // 2. Sync staff if provided
            if ($staffIds !== null) {
                $session->staff()->sync($staffIds);
            }

            // 3. Recalculate parent SO status
            (new SyncServiceOrderStatusAction())->execute($session->serviceOrder);

            return $session->fresh()->load('staff');
        });
    }
}
