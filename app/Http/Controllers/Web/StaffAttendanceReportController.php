<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Models\StaffAttendance;
use App\Services\HadirrService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StaffAttendanceReportController extends Controller
{
    public function index(Request $request)
    {
        // Default: 1st of current month to today
        $dari = $request->input('dari', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $sampai = $request->input('sampai', Carbon::now()->format('Y-m-d'));
        $filterNik = $request->input('nik');
        $filterStatus = $request->input('status');

        // Query attendance data from local DB
        $query = StaffAttendance::with('staff')
            ->whereBetween('tanggal', [$dari, $sampai])
            ->orderBy('tanggal');

        if ($filterNik) {
            $query->where('nik', $filterNik);
        }

        if ($filterStatus) {
            $query->where('status', $filterStatus);
        }

        $attendances = $query->get();

        // Build pivot data for matrix view
        $staffNames = $attendances->sortBy('nama')->pluck('nama', 'nik')->unique();
        $dates = $attendances->pluck('tanggal')->unique()->sort()->values();

        $pivot = [];
        foreach ($attendances as $att) {
            $dateKey = $att->tanggal->format('Y-m-d');
            $pivot[$att->nik][$dateKey] = $att;
        }

        // Get local staff list for filter dropdown (only those with Hadirr NIK mapped)
        $staffList = Staff::whereNotNull('hadirr_nik')
            ->orderBy('name')
            ->get();

        // Get distinct statuses for filter dropdown
        $statusList = StaffAttendance::select('status', 'raw_status')
            ->whereNotNull('status')
            ->distinct()
            ->orderBy('status')
            ->get();

        // Get latest sync time
        $lastSync = StaffAttendance::max('synced_at');

        return view('pages.laporan.absen-staff', compact(
            'attendances',
            'staffNames',
            'dates',
            'pivot',
            'dari',
            'sampai',
            'filterNik',
            'filterStatus',
            'staffList',
            'statusList',
            'lastSync'
        ));
    }

    public function sync(Request $request)
    {
        $request->validate([
            'dari' => 'required|date',
            'sampai' => 'required|date|after_or_equal:dari',
        ]);

        $dari = Carbon::parse($request->dari);
        $sampai = Carbon::parse($request->sampai);

        // Max 31 days
        if ($dari->diffInDays($sampai) > 31) {
            return redirect()->back()->with('error', 'Maksimal periode sync 31 hari.');
        }

        try {
            $service = new HadirrService();
            $result = $service->syncPeriod($dari, $sampai);

            if (empty($result['failed_dates'])) {
                $message = "Sync selesai: {$result['synced']} data berhasil diperbarui.";
                return redirect()->back()
                    ->withInput()
                    ->with('success', $message);
            } else {
                $failedCount = count($result['failed_dates']);
                $message = "Sync selesai dengan beberapa error. {$result['synced']} data berhasil, {$failedCount} tanggal gagal.";
                return redirect()->back()
                    ->withInput()
                    ->with('warning', $message);
            }
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal koneksi ke Hadirr: ' . $e->getMessage());
        }
    }
}
