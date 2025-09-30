<?php

namespace App\Events;

use App\Models\ServiceOrder;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ServiceOrderStatusUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $serviceOrder;

    /**
     * Create a new event instance.
     */
    public function __construct(ServiceOrder $serviceOrder)
    {
        $this->serviceOrder = $serviceOrder;
    }
}
