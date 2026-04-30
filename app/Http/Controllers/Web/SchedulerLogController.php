<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SchedulerLog;
use App\Models\WorkPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

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

    public function previewDeletePhotos()
    {
        $oldestRecord = WorkPhoto::orderBy('created_at', 'asc')->first();

        if (!$oldestRecord) {
            return back()->with('error', 'No photos found.');
        }

        $oldestDate = $oldestRecord->created_at;
        $endDate = $oldestDate->copy()->addMonth();

        $photos = WorkPhoto::where('created_at', '>=', $oldestDate)
            ->where('created_at', '<=', $endDate)
            ->get();

        $totalCount = $photos->count();
        $missingFiles = 0;
        $totalSize = 0;

        foreach ($photos as $photo) {
            try {
                if (Storage::disk('public')->exists($photo->file_path)) {
                    $totalSize += Storage::disk('public')->size($photo->file_path);
                } else {
                    $missingFiles++;
                }
            } catch (\Exception $e) {
                $missingFiles++;
            }
        }

        $affectedOrders = WorkPhoto::where('created_at', '>=', $oldestDate)
            ->where('created_at', '<=', $endDate)
            ->select('service_order_id', DB::raw('count(*) as photo_count'))
            ->with(['serviceOrder' => function ($query) {
                $query->select('id', 'so_number', 'work_date');
            }])
            ->groupBy('service_order_id')
            ->get();

        session()->put('delete_photos_start', $oldestDate->toDateTimeString());
        session()->put('delete_photos_end', $endDate->toDateTimeString());

        return view('pages.scheduler-logs.delete-photos-preview', [
            'oldestDate' => $oldestDate,
            'endDate' => $endDate,
            'totalCount' => $totalCount,
            'totalSize' => $totalSize,
            'missingFiles' => $missingFiles,
            'affectedOrders' => $affectedOrders,
        ]);
    }

    public function downloadPhotoBackup()
    {
        $startDate = session('delete_photos_start');
        $endDate = session('delete_photos_end');

        if (!$startDate || !$endDate) {
            return back()->with('error', 'Session expired. Please start the deletion process again.');
        }

        $photos = WorkPhoto::where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->get();

        if ($photos->isEmpty()) {
            return back()->with('error', 'No photos found for the selected date range.');
        }

        Storage::makeDirectory('temp');

        $startDateFormatted = date('Y-m-d', strtotime($startDate));
        $endDateFormatted = date('Y-m-d', strtotime($endDate));
        $zipFilename = "work_photos_backup_{$startDateFormatted}_to_{$endDateFormatted}.zip";
        $zipPath = storage_path('app/temp/' . $zipFilename);

        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach ($photos as $photo) {
            try {
                $fullPath = Storage::disk('public')->path($photo->file_path);
                if (file_exists($fullPath)) {
                    $zip->addFile($fullPath, $photo->file_path);
                }
            } catch (\Exception $e) {
                // Skip files that can't be added
                continue;
            }
        }

        $zip->close();

        return response()->download($zipPath)->deleteFileAfterSend(false);
    }

    public function confirmDeletePhotos()
    {
        $startDate = session('delete_photos_start');
        $endDate = session('delete_photos_end');

        if (!$startDate || !$endDate) {
            return back()->with('error', 'Session expired. Please start the deletion process again.');
        }

        $startTime = now();
        $photos = WorkPhoto::where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->get();

        $deletedCount = 0;

        foreach ($photos as $photo) {
            try {
                Storage::disk('public')->delete($photo->file_path);
            } catch (\Exception $e) {
                // File may already be deleted, continue anyway
            }
            $photo->delete();
            $deletedCount++;
        }

        $endTime = now();

        SchedulerLog::create([
            'command' => 'work-photos:delete-oldest',
            'start_time' => $startTime,
            'end_time' => $endTime,
            'items_processed' => $deletedCount,
        ]);

        // Clean up temp ZIP files
        $tempDir = storage_path('app/temp');
        if (is_dir($tempDir)) {
            $files = glob($tempDir . '/work_photos_backup_*.zip');
            foreach ($files as $file) {
                try {
                    unlink($file);
                } catch (\Exception $e) {
                    // Skip files that can't be deleted
                }
            }
        }

        session()->forget(['delete_photos_start', 'delete_photos_end']);

        $startDateFormatted = date('d M Y', strtotime($startDate));
        $endDateFormatted = date('d M Y', strtotime($endDate));

        return redirect()->route('scheduler-logs.index')
            ->with('success', "Deleted {$deletedCount} photos from {$startDateFormatted} to {$endDateFormatted}");
    }
}