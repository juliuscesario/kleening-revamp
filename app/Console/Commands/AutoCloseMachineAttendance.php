<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MachineAttendance;
use Carbon\Carbon;

class AutoCloseMachineAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'machine-attendance:auto-close';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-close all open machine attendances at end of day';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now(config('app.timezone'));

        $openAttendances = MachineAttendance::whereNull('photo_pulang_at')
            ->whereDate('date', '<=', $now->toDateString())
            ->get();

        foreach ($openAttendances as $attendance) {
            $attendance->update([
                'photo_pulang_at' => $now,
                'catatan' => trim(($attendance->catatan ?? '') . "\n[Auto-closed oleh sistem pada " . $now->format('d/m/Y H:i') . "]"),
                'updated_by' => null,
            ]);
        }

        $this->info("Auto-closed {$openAttendances->count()} attendance(s).");

        return Command::SUCCESS;
    }
}
