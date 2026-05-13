<?php

namespace App\Console\Commands;

use App\Services\HadirrService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SyncHadirrAttendances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hadirr:sync-attendances
                            {--days=31 : Sync last N days until today}
                            {--from= : Start date (YYYY-MM-DD)}
                            {--to= : End date (YYYY-MM-DD)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync staff attendance data from Hadirr API';

    public function __construct(
        protected HadirrService $hadirrService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $from = $this->option('from');
        $to = $this->option('to');
        $days = (int) $this->option('days');

        if ($from && $to) {
            $fromDate = Carbon::parse($from);
            $toDate = Carbon::parse($to);
        } else {
            $toDate = Carbon::today();
            $fromDate = Carbon::today()->subDays($days);
        }

        $rangeDays = $fromDate->diffInDays($toDate) + 1;

        if ($rangeDays > 93) {
            $this->error("Maksimal rentang sync adalah 93 hari (3 bulan). Rentang saat ini: {$rangeDays} hari.");
            return Command::FAILURE;
        }

        $this->info("Syncing {$fromDate->format('Y-m-d')} to {$toDate->format('Y-m-d')} ({$rangeDays} days)...");

        $result = $this->hadirrService->syncPeriod($fromDate, $toDate);

        $this->info("Sync selesai: {$result['synced']} data berhasil diperbarui.");

        if (!empty($result['failed_dates'])) {
            $this->error("Tanggal gagal sync: " . implode(', ', $result['failed_dates']));
        }

        return Command::SUCCESS;
    }
}
