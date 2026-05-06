<?php

namespace App\Actions;

use App\Models\OrderSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeleteOrderSessionAction
{
    public function execute(OrderSession $session): void
    {
        $serviceOrder = $session->serviceOrder;

        // BLOCK: cannot delete the last session (regardless of status)
        $totalSessions = $serviceOrder->sessions()->count();
        if ($totalSessions <= 1) {
            throw ValidationException::withMessages([
                'session' => 'Tidak bisa menghapus sesi terakhir. Setiap order harus memiliki minimal 1 sesi.',
            ]);
        }

        DB::transaction(function () use ($session, $serviceOrder) {
            $deletedNumber = $session->session_number;

            // 1. Delete the session (cascade deletes order_session_staff rows)
            $session->delete();

            // 2. Renumber remaining sessions to be sequential
            $remaining = $serviceOrder->sessions()->orderBy('session_number')->get();
            foreach ($remaining as $index => $s) {
                $newNumber = $index + 1;
                if ($s->session_number !== $newNumber) {
                    $s->update(['session_number' => $newNumber]);
                }
            }

            // 3. Update is_multi_session flag
            $activeCount = $serviceOrder->sessions()->count();
            $serviceOrder->update(['is_multi_session' => $activeCount > 1]);

            // 4. Recalculate parent SO status
            (new SyncServiceOrderStatusAction())->execute($serviceOrder);
        });
    }
}
