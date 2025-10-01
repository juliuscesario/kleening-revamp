<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SchedulerLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SchedulerLogController extends Controller
{
    public function index()
    {
        $logs = SchedulerLog::orderBy('start_time', 'desc')->paginate(20);
        return view('pages.scheduler-logs.index', compact('logs'));
    }

    public function runCommand(Request $request)
    {
        $request->validate([
            'command' => 'required|string',
        ]);

        $command = $request->input('command');

        // Validate the command to prevent arbitrary command execution
        if (!in_array($command, ['invoices:mark-overdue', 'service-orders:auto-cancel-old'])) {
            return back()->with('error', 'Invalid command.');
        }

        try {
            Artisan::call($command);
            return back()->with('success', "Command '{$command}' executed successfully.");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to execute command: ' . $e->getMessage());
        }
    }
}