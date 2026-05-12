<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Machine;
use App\Models\MachineAttendance;
use Illuminate\Http\Request;

class MachineTrackerController extends Controller
{
    /**
     * Show the tracker page with machine dropdown.
     */
    public function index()
    {
        $machines = Machine::with('category', 'area')
            ->where('status', 'active')
            ->orderBy('area_id')
            ->orderBy('code')
            ->get()
            ->groupBy(function ($machine) {
                return $machine->area?->name ?? 'Unknown';
            });

        return view('pages.laporan.machine-tracker', compact('machines'));
    }

    /**
     * AJAX: look up current holder + history for a machine.
     * GET /laporan/machine-tracker/lookup?machine_id=X&from=Y&to=Z
     */
    public function lookup(Request $request)
    {
        $request->validate([
            'machine_id' => 'required|exists:machines,id',
            'from'       => 'nullable|date',
            'to'         => ['nullable', 'date', function ($attribute, $value, $fail) use ($request) {
                if ($request->filled('from') && $value < $request->from) {
                    $fail('Tanggal "sampai" tidak boleh sebelum tanggal "dari".');
                }
            }],
        ]);

        $machine = Machine::with('category', 'area')->findOrFail($request->machine_id);

        // Current active holder: attendance with photo_pergi set and photo_pulang null, for this machine
        $active = MachineAttendance::whereHas('machines', fn($q) => $q->where('machines.id', $machine->id))
            ->whereNotNull('photo_pergi')
            ->whereNull('photo_pulang')
            ->with('staff')
            ->orderByDesc('date')
            ->first();

        // History: closed attendances filtered by optional date range
        $historyQuery = MachineAttendance::whereHas('machines', fn($q) => $q->where('machines.id', $machine->id))
            ->whereNotNull('photo_pulang')
            ->with('staff');

        if ($request->filled('from')) {
            $historyQuery->whereDate('date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $historyQuery->whereDate('date', '<=', $request->to);
        }

        $history = $historyQuery->orderByDesc('date')->limit(50)->get();

        // Build photo URLs
        $makeUrl = fn($path) => $path ? asset('storage/' . $path) : null;

        $activeData = null;
        if ($active) {
            $activeData = [
                'staff_name'     => $active->staff?->name ?? 'Unknown',
                'date'           => $active->date->format('d M Y'),
                'jam_pergi'      => $active->photo_pergi_at ? $active->photo_pergi_at->format('H:i') : '-',
                'photo_pergi'    => $makeUrl($active->photo_pergi),
                'catatan'        => $active->catatan ?? '-',
            ];
        }

        $historyData = $history->map(fn($att) => [
            'staff_name'   => $att->staff?->name ?? 'Unknown',
            'date'         => $att->date->format('d M Y'),
            'jam_pergi'    => $att->photo_pergi_at ? $att->photo_pergi_at->format('H:i') : '-',
            'jam_pulang'   => $att->photo_pulang_at ? $att->photo_pulang_at->format('H:i') : '-',
            'durasi'       => ($att->photo_pergi_at && $att->photo_pulang_at)
                                ? $this->formatDurasi($att->photo_pergi_at, $att->photo_pulang_at)
                                : '-',
            'photo_pergi'  => $makeUrl($att->photo_pergi),
            'photo_pulang' => $makeUrl($att->photo_pulang),
            'catatan'      => $att->catatan ?? '-',
        ]);

        return response()->json([
            'machine' => [
                'code'     => $machine->code,
                'name'     => $machine->name,
                'area'     => $machine->area?->name ?? 'Unknown',
                'category' => $machine->category?->name ?? '-',
                'status'   => $machine->status,
            ],
            'active'  => $activeData,
            'history' => $historyData,
        ]);
    }

    private function formatDurasi($from, $to): string
    {
        $diff = $from->diff($to);
        if ($diff->h > 0) {
            return $diff->h . 'j ' . $diff->i . 'm';
        }
        return $diff->i . ' menit';
    }
}
