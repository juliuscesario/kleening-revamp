<?php

namespace App\View\Composers;

use App\Models\Invoice;
use App\Models\ServiceOrder;
use Illuminate\View\View;

class PendingCountComposer
{
    public function compose(View $view)
    {
        $user = auth()->user();

        if (!$user || !in_array(strtolower(trim($user->role)), ['admin', 'owner'])) {
            $view->with('pendingCount', 0);
            return;
        }

        $pendingSOCount = ServiceOrder::whereIn('status', [
                ServiceOrder::STATUS_PROSES,
                ServiceOrder::STATUS_DONE,
            ])
            ->whereDoesntHave('invoice')
            ->count();

        $unpaidInvoiceCount = Invoice::whereIn('status', [
                Invoice::STATUS_NEW,
                Invoice::STATUS_SENT,
                Invoice::STATUS_OVERDUE,
            ])
            ->count();

        $view->with('pendingCount', $pendingSOCount + $unpaidInvoiceCount);
    }
}
