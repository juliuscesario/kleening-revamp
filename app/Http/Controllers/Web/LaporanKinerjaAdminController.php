<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ServiceOrder;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LaporanKinerjaAdminController extends Controller
{
    public function index(Request $request)
    {
        $mulai = $request->input('mulai', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $sampai = $request->input('sampai', Carbon::now()->format('Y-m-d'));

        $adminUsers = User::where('role', 'admin')
            ->orderBy('name')
            ->get();

        $adminStats = $adminUsers->map(function ($admin) use ($mulai, $sampai) {
            $baseQuery = ServiceOrder::withoutGlobalScopes()
                ->where('created_by', $admin->id)
                ->whereBetween('created_at', [
                    Carbon::parse($mulai)->startOfDay(),
                    Carbon::parse($sampai)->endOfDay(),
                ]);

            $totalCreated = (clone $baseQuery)->count();

            $totalCancelled = (clone $baseQuery)
                ->where('status', 'cancelled')
                ->count();

            $totalDone = (clone $baseQuery)
                ->where('status', 'done')
                ->count();

            return [
                'name'         => $admin->name,
                'total_so'     => $totalCreated,
                'total_cancel' => $totalCancelled,
                'total_done'   => $totalDone,
            ];
        });

        return view('pages.laporan.kinerja-admin', compact(
            'adminStats', 'mulai', 'sampai'
        ));
    }
}
