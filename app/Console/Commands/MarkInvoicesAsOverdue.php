<?php

namespace App\Console\Commands;

use App\Events\InvoiceStatusUpdated;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MarkInvoicesAsOverdue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:mark-overdue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark invoices as overdue if they are past their due date and not paid.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startTime = Carbon::now();
        $count = 0;

        try {
            $today = Carbon::today();

            $invoicesToMarkOverdue = Invoice::whereIn('status', [Invoice::STATUS_NEW, Invoice::STATUS_SENT])
                ->where('due_date', '<', $today)
                ->get();

            if ($invoicesToMarkOverdue->isEmpty()) {
                $this->info('No invoices to mark as overdue.');
                return Command::SUCCESS;
            }

            foreach ($invoicesToMarkOverdue as $invoice) {
                $invoice->status = Invoice::STATUS_OVERDUE;
                $invoice->save();

                event(new InvoiceStatusUpdated($invoice));

                $count++;
            }

            $this->info("Successfully marked {$count} invoices as overdue.");
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
