<?php

namespace App\Listeners;

use App\Events\InvoiceStatusUpdated;
use App\Models\Invoice;
use App\Models\User;
use App\Notifications\InvoiceOverdueNotification;
use App\Notifications\InvoicePaidNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendInvoiceNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(InvoiceStatusUpdated $event): void
    {
        $invoice = $event->invoice;

        if ($invoice->status === Invoice::STATUS_OVERDUE) {
            $this->notifyCustomerAdminOwnerAndCoOwner($invoice);
        } elseif ($invoice->status === Invoice::STATUS_PAID) {
            $this->notifyOwnerCoOwnerAndAdmin($invoice);
        }
    }

    private function notifyCustomerAdminOwnerAndCoOwner(Invoice $invoice)
    {
        $customerPhoneNumber = $invoice->serviceOrder->customer->phone_number;
        $customerUser = User::where('phone_number', $customerPhoneNumber)->first();

        $adminsAndOwners = User::whereIn('role', ['admin', 'owner', 'co-owner'])->get();

        if ($customerUser) {
            Notification::send($customerUser, new InvoiceOverdueNotification($invoice));
        }
        Notification::send($adminsAndOwners, new InvoiceOverdueNotification($invoice));
    }

    private function notifyOwnerCoOwnerAndAdmin(Invoice $invoice)
    {
        $adminsAndOwners = User::whereIn('role', ['owner', 'co-owner', 'admin'])->get();
        Notification::send($adminsAndOwners, new InvoicePaidNotification($invoice));
    }
}
