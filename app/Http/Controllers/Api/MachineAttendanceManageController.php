<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MachineAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MachineAttendanceManageController extends Controller
{
    protected function requireOwnerOrCoOwner()
    {
        if (!in_array(strtolower(trim(auth()->user()->role)), ['owner', 'co_owner'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
    }

    public function index(Request $request)
    {
        $this->requireOwnerOrCoOwner();

        $query = MachineAttendance::with(['staff', 'machines'])
            ->select('machine_attendances.*');

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }
        if ($request->filled('staff_id')) {
            $query->where('staff_id', $request->staff_id);
        }
        if ($request->filled('area_id')) {
            $query->whereHas('staff', function ($q) use ($request) {
                $q->where('area_id', $request->area_id);
            });
        }
        if ($request->filled('status')) {
            if ($request->status === 'open') {
                $query->whereNull('photo_pulang_at');
            } elseif ($request->status === 'closed') {
                $query->whereNotNull('photo_pulang_at');
            }
        }

        $attendances = $query->orderBy('date', 'desc')->get();

        return response()->json([
            'data' => $attendances->map(function ($attendance) {
                return [
                    'id' => $attendance->id,
                    'date' => $attendance->date->format('d/m/Y'),
                    'staff_name' => $attendance->staff ? $attendance->staff->name : 'N/A',
                    'machines' => $attendance->machines->pluck('code')->join(', ') ?: '—',
                    'photo_pergi_at' => $attendance->photo_pergi_at ? $attendance->photo_pergi_at->format('H:i') : '—',
                    'photo_pulang_at' => $attendance->photo_pulang_at ? $attendance->photo_pulang_at->format('H:i') : 'OPEN',
                    'catatan' => $attendance->catatan ?? '—',
                    'status' => $attendance->photo_pulang_at ? 'closed' : 'open',
                ];
            }),
        ]);
    }

    public function show($id)
    {
        $this->requireOwnerOrCoOwner();

        $attendance = MachineAttendance::with(['staff', 'machines.category', 'createdBy', 'updatedBy'])
            ->findOrFail($id);

        return response()->json([
            'id' => $attendance->id,
            'staff_name' => $attendance->staff->name,
            'date' => $attendance->date->format('Y-m-d'),
            'photo_pergi' => $attendance->photo_pergi ? Storage::url($attendance->photo_pergi) : null,
            'photo_pergi_at' => $attendance->photo_pergi_at?->format('H:i'),
            'photo_pulang' => $attendance->photo_pulang ? Storage::url($attendance->photo_pulang) : null,
            'photo_pulang_at' => $attendance->photo_pulang_at?->format('H:i'),
            'catatan' => $attendance->catatan,
            'machines' => $attendance->machines->map(fn($m) => $m->code)->join(', '),
            'status' => $attendance->photo_pulang_at ? 'closed' : 'open',
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->requireOwnerOrCoOwner();

        $request->validate(['catatan' => 'nullable|string|max:500']);

        $attendance = MachineAttendance::findOrFail($id);
        $attendance->update([
            'catatan' => $request->catatan,
            'updated_by' => auth()->id(),
        ]);

        return response()->json(['success' => true, 'message' => 'Catatan berhasil diupdate']);
    }

    public function forceClose($id)
    {
        $this->requireOwnerOrCoOwner();

        $attendance = MachineAttendance::findOrFail($id);

        if ($attendance->photo_pulang_at) {
            return response()->json([
                'success' => false,
                'message' => 'Attendance ini sudah selesai',
            ], 422);
        }

        $now = now(config('app.timezone'));

        $attendance->update([
            'photo_pulang_at' => $now,
            'catatan' => trim(($attendance->catatan ?? '') . ' [Force closed oleh ' . auth()->user()->name . ' pada ' . $now->format('d/m/Y H:i') . ']'),
            'updated_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Attendance berhasil di-force close. Mesin sekarang available.',
        ]);
    }

    public function destroy($id)
    {
        $this->requireOwnerOrCoOwner();

        $attendance = MachineAttendance::findOrFail($id);

        if ($attendance->photo_pergi) {
            Storage::disk('public')->delete($attendance->photo_pergi);
        }
        if ($attendance->photo_pulang) {
            Storage::disk('public')->delete($attendance->photo_pulang);
        }

        $attendance->delete();

        return response()->json(['success' => true, 'message' => 'Attendance berhasil dihapus']);
    }
}
