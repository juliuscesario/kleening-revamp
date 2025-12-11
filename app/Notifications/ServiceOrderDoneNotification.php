<?php

namespace App\Notifications;

use App\Models\ServiceOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ServiceOrderDoneNotification extends Notification
{
    use Queueable;

    protected $serviceOrder;

    /**
     * Create a new notification instance.
     */
    public function __construct(ServiceOrder $serviceOrder)
    {
        $this->serviceOrder = $serviceOrder;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'service_order_id' => $this->serviceOrder->id,
            'so_number' => $this->serviceOrder->so_number,
            'message' => 'Service order ' . $this->serviceOrder->so_number . ' has been marked as done.',
        ];
    }
}
