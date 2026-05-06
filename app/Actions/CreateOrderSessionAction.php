<?php

namespace App\Actions;

use App\Models\OrderSession;
use App\Models\ServiceOrder;
use Illuminate\Support\Facades\DB;

class CreateOrderSessionAction
{
    public function execute(ServiceOrder $serviceOrder, array $data, array $staffIds = []): OrderSession
    {
        return DB::transaction(function () use ($serviceOrder, $data, $staffIds) {
            // 1. Calculate next session_number
            $maxSession = $serviceOrder->sessions()->max('session_number') ?? 0;

            // 2. Create the session
            $jam = !empty($data['jam']) ? (strlen($data['jam']) === 5 ? $data['jam'] . ':00' : $data['jam']) : null;
            $session = $serviceOrder->sessions()->create([
                'session_number' => $maxSession + 1,
                'tanggal'        => $data['tanggal'] ?? null,
                'jam'            => $jam,
                'type'           => $data['type'] ?? 'kerja',
                'status'         => $data['status'] ?? 'booked',
                'notes'          => $data['notes'] ?? null,
            ]);

            // 3. Assign staff
            if (!empty($staffIds)) {
                $session->staff()->attach($staffIds);
            }

            // 4. Update is_multi_session flag
            $serviceOrder->update(['is_multi_session' => true]);

            // 5. Recalculate parent SO status
            (new SyncServiceOrderStatusAction())->execute($serviceOrder);

            return $session->fresh()->load('staff');
        });
    }
}
