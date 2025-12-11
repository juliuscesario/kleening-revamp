<?php

namespace App\Listeners;

use App\Events\ServiceOrderStatusUpdated;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Notifications\ServiceOrderDoneNotification;
use App\Notifications\ServiceOrderInvoicedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendServiceOrderNotification
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
    public function handle(ServiceOrderStatusUpdated $event): void
    {
        $serviceOrder = $event->serviceOrder;

        if ($serviceOrder->status === ServiceOrder::STATUS_INVOICED) {
            $this->notifyCustomerOwnerAndCoOwner($serviceOrder);
        } elseif ($serviceOrder->status === ServiceOrder::STATUS_DONE) {
            $this->notifyAdmin($serviceOrder);
        }
    }

    private function notifyCustomerOwnerAndCoOwner(ServiceOrder $serviceOrder)
    {
        $customerPhoneNumber = $serviceOrder->customer->phone_number;
        $customerUser = User::where('phone_number', $customerPhoneNumber)->first();

        $ownerAndCoOwners = User::whereIn('role', ['owner', 'co-owner'])->get();

        if ($customerUser) {
            Notification::send($customerUser, new ServiceOrderInvoicedNotification($serviceOrder));
        }
        Notification::send($ownerAndCoOwners, new ServiceOrderInvoicedNotification($serviceOrder));
    }

    private function notifyAdmin(ServiceOrder $serviceOrder)
    {
        $admins = User::where('role', 'admin')->get();
        Notification::send($admins, new ServiceOrderDoneNotification($serviceOrder));
    }
}
