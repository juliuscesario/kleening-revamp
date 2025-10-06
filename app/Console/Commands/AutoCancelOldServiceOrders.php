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
    protected $description = "Automatically cancel service orders with 'booked' status that were created more than 7 days ago.";

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startTime = Carbon::now();
        $count = 0;

        try {
            $thresholdDate = Carbon::now()->subDays(7);

            $ordersToCancel = ServiceOrder::where('status', ServiceOrder::STATUS_BOOKED)
                                        ->where('work_date', '<', $thresholdDate)
                                        ->get();

            if ($ordersToCancel->isEmpty()) {
                $this->info('No old service orders found to cancel.');
                return Command::SUCCESS;
            }

            foreach ($ordersToCancel as $order) {
                $order->status = ServiceOrder::STATUS_CANCELLED;
                $order->save();
                $count++;
            }

            $this->info("Successfully cancelled {$count} old service orders.");
        } finally {
            \App\Models\SchedulerLog::create([
                'command' => $this->signature,
                'start_time' => $startTime,
                'end_time' => Carbon::now(),
                'items_processed' => $count,
            ]);
        }

        return Command::SUCCESS;
    }
}
