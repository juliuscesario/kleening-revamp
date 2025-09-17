<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Staff;
use App\Http\Resources\StaffResource; // <-- Tambahkan Http di sini
use Illuminate\Validation\Rule; // <-- TAMBAHKAN BARIS INI
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // <-- 1. TAMBAHKAN INI

class StaffController extends Controller
{
    use AuthorizesRequests; // <-- 2. TAMBAHKAN INI
    public function index()
    {
        // Cek izin: apakah user boleh melihat daftar Staff?
        $this->authorize('viewAny', Staff::class);

        // Eager load relasi area dan user
        return StaffResource::collection(Staff::with(['area', 'user'])->get());
    }

    public function store(Request $request)
    {
        // Cek izin: apakah user boleh membuat Staff baru?
        $this->authorize('create', Staff::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:255',
            'area_id' => 'required|integer|exists:areas,id',
            'user_id' => 'nullable|integer|exists:users,id|unique:staff,user_id',
            'is_active' => 'sometimes|boolean',
        ]);

        $staff = Staff::create($validated);
        return new StaffResource($staff->load('area'));
    }

    public function show(Staff $staff)
    {
        // Cek izin: apakah user boleh melihat Staff ini?
        $this->authorize('view', $staff);
        return new StaffResource($staff->load(['area', 'user']));
    }

    public function update(Request $request, Staff $staff)
    {
        // Cek izin: apakah user boleh mengupdate Staff ini?
        $this->authorize('update', $staff);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'phone_number' => 'sometimes|required|string|max:255',
            'area_id' => 'sometimes|required|integer|exists:areas,id',
            'user_id' => [
                'nullable',
                'integer',
                'exists:users,id',
                Rule::unique('staff')->ignore($staff->id),
            ],
            'is_active' => 'sometimes|boolean',
        ]);

        $staff->update($validated);
        return new StaffResource($staff->load('area'));
    }

    public function destroy(Staff $staff)
    {
        // Cek izin: apakah user boleh menghapus Staff ini?
        $this->authorize('delete', $staff);

        $staff->delete();
        return response()->noContent();
    }
}
