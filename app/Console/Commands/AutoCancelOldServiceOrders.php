<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ServiceOrder;
use Carbon\Carbon;

class AutoCancelOldServiceOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'service-orders:auto-cancel-old';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically cancels service orders that are \'baru\' and more than 7 days past their work_date.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $thresholdDate = Carbon::now()->subDays(7)->toDateString();

        $ordersToCancel = ServiceOrder::where('status', ServiceOrder::STATUS_BOOKED)
                                      ->where('work_date', '<', $thresholdDate)
                                      ->get();

        if ($ordersToCancel->isEmpty()) {
            $this->info('No old service orders found to cancel.');
            return Command::SUCCESS;
        }

        $count = 0;
        foreach ($ordersToCancel as $order) {
            $order->status = ServiceOrder::STATUS_CANCELLED;
            $order->save();
            $count++;
        }

        $this->info("Successfully cancelled {$count} old service orders.");

        return Command::SUCCESS;
    }
}
