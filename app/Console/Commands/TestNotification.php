<?php

namespace App\Console\Commands;

use App\Events\ServiceOrderStatusUpdated;
use App\Models\ServiceOrder;
use Illuminate\Console\Command;

class TestNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the notification system by updating a service order and triggering a notification.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $serviceOrder = ServiceOrder::first();

        if (!$serviceOrder) {
            $this->error('No service orders found in the database.');
            return Command::FAILURE;
        }

        $this->info("Found service order with ID: {$serviceOrder->id}");

        $serviceOrder->status = ServiceOrder::STATUS_INVOICED;
        $serviceOrder->save();

        event(new ServiceOrderStatusUpdated($serviceOrder));

        $this->info("Service order status updated to 'invoiced' and notification event dispatched.");

        return Command::SUCCESS;
    }
}
