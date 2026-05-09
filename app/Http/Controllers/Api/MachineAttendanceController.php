<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Machine;
use App\Models\MachineAttendance;
use App\Models\MachineAttendanceItem;
use App\Services\ImageCompressor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MachineAttendanceController extends Controller
{
    public function __construct(protected ImageCompressor $imageCompressor)
    {
    }

    /**
     * GET /api/machine-attendance/status
     * Returns the current staff's attendance status for today.
     */
    public function status(Request $request)
    {
        $staff = $request->user()->staff;
        if (!$staff) {
            return response()->json([
                'success' => false,
                'message' => 'Staff profile not found',
            ], 403);
        }
        $today = now(config('app.timezone'))->toDateString();

        $attendance = MachineAttendance::where('staff_id', $staff->id)
            ->whereDate('date', $today)
            ->with('machines.category')
            ->first();

        if (!$attendance) {
            return response()->json([
                'status' => 'no_attendance',
                'message' => 'Belum upload Mesin Pergi hari ini',
            ]);
        }

        if ($attendance->photo_pergi_at && !$attendance->photo_pulang_at) {
            return response()->json([
                'status' => 'active',
                'attendance_id' => $attendance->id,
                'photo_pergi_at' => $attendance->photo_pergi_at->format('H:i'),
                'machines' => $attendance->machines->map(fn ($m) => [
                    'id' => $m->id,
                    'code' => $m->code,
                    'category' => $m->category->name,
                ]),
                'catatan' => $attendance->catatan,
            ]);
        }

        if ($attendance->photo_pergi_at && $attendance->photo_pulang_at) {
            return response()->json([
                'status' => 'completed',
                'photo_pergi_at' => $attendance->photo_pergi_at->format('H:i'),
                'photo_pulang_at' => $attendance->photo_pulang_at->format('H:i'),
                'machines' => $attendance->machines->map(fn ($m) => [
                    'id' => $m->id,
                    'code' => $m->code,
                    'category' => $m->category->name,
                ]),
                'catatan' => $attendance->catatan,
            ]);
        }
    }

    /**
     * GET /api/machine-attendance/available-machines
     * Returns machines available for selection, filtered by the staff's area.
     */
    public function availableMachines(Request $request)
    {
        $staff = $request->user()->staff;
        if (!$staff) {
            return response()->json([
                'success' => false,
                'message' => 'Staff profile not found',
            ], 403);
        }
        $today = now(config('app.timezone'))->toDateString();

        // Get all active machines in staff's area
        $machines = Machine::where('area_id', $staff->area_id)
            ->where('status', 'active')
            ->with('category', 'pairedMachine')
            ->orderBy('category_id')
            ->orderBy('code')
            ->get();

        // Find machine IDs currently checked out today (pergi done, pulang not done)
        $checkedOutItems = MachineAttendanceItem::whereHas('attendance', function ($q) use ($today) {
            $q->whereDate('date', $today)
                ->whereNotNull('photo_pergi_at')
                ->whereNull('photo_pulang_at');
        })->with('attendance.staff')->get();

        $checkedOutMap = [];
        foreach ($checkedOutItems as $item) {
            $checkedOutMap[$item->machine_id] = $item->attendance->staff->name ?? 'Staff lain';
        }

        $result = $machines->map(function ($machine) use ($checkedOutMap) {
            $available = !isset($checkedOutMap[$machine->id]);
            return [
                'id' => $machine->id,
                'code' => $machine->code,
                'category_id' => $machine->category_id,
                'category' => $machine->category->name,
                'available' => $available,
                'used_by' => $available ? null : $checkedOutMap[$machine->id],
                'paired_machine_id' => $machine->paired_machine_id,
            ];
        });

        // Group by category for frontend display
        $grouped = $result->groupBy('category');

        return response()->json($grouped);
    }

    /**
     * POST /api/machine-attendance/pergi
     * Submit Mesin Pergi. Accepts: photo (file) + machine_ids (array) + catatan (optional string).
     */
    public function pergi(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|max:5120',          // max 5MB
            'machine_ids' => 'required|array|min:1',
            'machine_ids.*' => 'exists:machines,id',
            'catatan' => 'nullable|string|max:500',
        ]);

        $staff = $request->user()->staff;
        if (!$staff) {
            return response()->json([
                'success' => false,
                'message' => 'Staff profile not found',
            ], 403);
        }
        $today = now(config('app.timezone'))->toDateString();
        $now = now(config('app.timezone'));

        // Validation 1: Staff cannot have two attendances on the same day
        $existing = MachineAttendance::where('staff_id', $staff->id)
            ->whereDate('date', $today)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Sudah upload Mesin Pergi hari ini',
            ], 422);
        }

        // Validation 2: All selected machines must be active
        $machines = Machine::whereIn('id', $request->machine_ids)->get();
        $inactiveMachines = $machines->where('status', '!=', 'active');
        if ($inactiveMachines->isNotEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Mesin ' . $inactiveMachines->pluck('code')->join(', ') . ' tidak aktif',
            ], 422);
        }

        // Validation 3: All selected machines must not be checked out by another staff
        $checkedOutIds = MachineAttendanceItem::whereIn('machine_id', $request->machine_ids)
            ->whereHas('attendance', function ($q) use ($today) {
                $q->whereDate('date', $today)
                    ->whereNotNull('photo_pergi_at')
                    ->whereNull('photo_pulang_at');
            })->pluck('machine_id')->toArray();

        if (!empty($checkedOutIds)) {
            $codes = Machine::whereIn('id', $checkedOutIds)->pluck('code')->join(', ');
            return response()->json([
                'success' => false,
                'message' => 'Mesin ' . $codes . ' sedang dibawa staff lain',
            ], 422);
        }

        // Save exactly the machines the staff selected — no auto-pairing
        $allMachineIds = collect($request->machine_ids)->unique()->values();

        // Use database transaction to prevent race conditions
        return DB::transaction(function () use ($request, $staff, $today, $now, $allMachineIds) {
            // Double-check inside transaction: machines still available
            $stillCheckedOut = MachineAttendanceItem::whereIn('machine_id', $allMachineIds)
                ->whereHas('attendance', function ($q) use ($today) {
                    $q->whereDate('date', $today)
                        ->whereNotNull('photo_pergi_at')
                        ->whereNull('photo_pulang_at');
                })->exists();

            if ($stillCheckedOut) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ada mesin yang baru saja diambil staff lain. Silakan coba lagi.',
                ], 422);
            }

            // Store photo using ImageCompressor
            $year = $now->format('Y');
            $month = $now->format('m');
            $filename = $staff->id . '_' . $today . '_pergi_' . $now->timestamp;
            $path = $this->imageCompressor->compress(
                $request->file('photo'),
                "machine-attendance/{$year}/{$month}",
                $filename
            );

            // Create attendance record
            $attendance = MachineAttendance::create([
                'staff_id' => $staff->id,
                'date' => $today,
                'photo_pergi' => $path,
                'photo_pergi_at' => $now,
                'catatan' => $request->catatan,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            // Create attendance items
            foreach ($allMachineIds as $machineId) {
                MachineAttendanceItem::create([
                    'machine_attendance_id' => $attendance->id,
                    'machine_id' => $machineId,
                ]);
            }

            $attendance->load('machines.category');

            return response()->json([
                'success' => true,
                'message' => 'Mesin Pergi berhasil disimpan',
                'attendance' => [
                    'id' => $attendance->id,
                    'photo_pergi_at' => $attendance->photo_pergi_at->format('H:i'),
                    'machines' => $attendance->machines->map(fn ($m) => [
                        'code' => $m->code,
                        'category' => $m->category->name,
                    ]),
                ],
            ]);
        });
    }

    /**
     * POST /api/machine-attendance/{id}/pulang
     * Submit Mesin Pulang. Accepts: photo (file).
     */
    public function pulang(Request $request, $id)
    {
        $request->validate([
            'photo' => 'required|image|max:5120',
        ]);

        $staff = $request->user()->staff;
        if (!$staff) {
            return response()->json([
                'success' => false,
                'message' => 'Staff profile not found',
            ], 403);
        }
        $now = now(config('app.timezone'));

        $attendance = MachineAttendance::where('id', $id)
            ->where('staff_id', $staff->id)
            ->whereNotNull('photo_pergi_at')
            ->whereNull('photo_pulang_at')
            ->first();

        if (!$attendance) {
            return response()->json([
                'success' => false,
                'message' => 'Data attendance tidak ditemukan atau sudah selesai',
            ], 422);
        }

        $year = $now->format('Y');
        $month = $now->format('m');
        $today = $attendance->date->format('Y-m-d');
        $filename = $staff->id . '_' . $today . '_pulang_' . $now->timestamp;
        $path = $this->imageCompressor->compress(
            $request->file('photo'),
            "machine-attendance/{$year}/{$month}",
            $filename
        );

        $attendance->update([
            'photo_pulang' => $path,
            'photo_pulang_at' => $now,
            'updated_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Mesin Pulang berhasil disimpan',
            'photo_pulang_at' => $attendance->photo_pulang_at->format('H:i'),
        ]);
    }
}
