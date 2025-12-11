<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Staff;
use App\Http\Resources\StaffResource; // <-- Tambahkan Http di sini
use Illuminate\Validation\Rule; // <-- TAMBAHKAN BARIS INI
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // <-- 1. TAMBAHKAN INI

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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
        $this->authorize('create', Staff::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:15|unique:users,phone_number',
            'area_id' => 'required|integer|exists:areas,id',
            'role' => 'required|string|in:admin,staff', // Assuming these are the roles
            'password' => 'required|string|min:8',
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $validated['name'],
                'phone_number' => $validated['phone_number'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'],
                'area_id' => $validated['area_id'],
            ]);

            $staff = Staff::create([
                'name' => $validated['name'],
                'phone_number' => $validated['phone_number'],
                'area_id' => $validated['area_id'],
                'user_id' => $user->id,
            ]);

            DB::commit();

            return new StaffResource($staff->load(['area', 'user']));

        } catch (\Exception $e) {
            DB::rollBack();
            // Log the error message
            
            return response()->json(['message' => 'Failed to create staff and user.', 'error' => $e->getMessage()], 500);
        }
    }

    public function show(Staff $staff)
    {
        // Cek izin: apakah user boleh melihat Staff ini?
        $this->authorize('view', $staff);
        return new StaffResource($staff->load(['area', 'user']));
    }

    public function update(Request $request, Staff $staff)
    {
        $this->authorize('update', $staff);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'phone_number' => ['sometimes', 'required', 'string', 'max:15', Rule::unique('users', 'phone_number')->ignore($staff->user_id)],
            'area_id' => 'sometimes|required|integer|exists:areas,id',
            'role' => 'sometimes|required|string|in:admin,staff',
            'password' => 'nullable|string|min:8',
        ]);

        DB::beginTransaction();
        try {
            $staff->update([
                'name' => $validated['name'] ?? $staff->name,
                'phone_number' => $validated['phone_number'] ?? $staff->phone_number,
                'area_id' => $validated['area_id'] ?? $staff->area_id,
            ]);

            if ($staff->user) {
                $userData = [
                    'name' => $validated['name'] ?? $staff->user->name,
                    'phone_number' => $validated['phone_number'] ?? $staff->user->phone_number,
                    'role' => $validated['role'] ?? $staff->user->role,
                    'area_id' => $validated['area_id'] ?? $staff->user->area_id,
                ];

                if (!empty($validated['password'])) {
                    $userData['password'] = Hash::make($validated['password']);
                }

                $staff->user->update($userData);
            }

            DB::commit();

            return new StaffResource($staff->load(['area', 'user']));

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update staff and user.', 'error' => $e->getMessage()], 500);
        }
    }

    public function resign(Staff $staff)
    {
        // Cek izin: apakah user boleh me-resign Staff ini?
        $this->authorize('resign', $staff);

        if ($staff->user) {
            $staff->user->delete(); // This will set user_id to null because of onDelete('set null')
            return response()->json(['message' => 'Staff has been resigned and their login has been removed.']);
        }

        return response()->json(['message' => 'This staff member does not have a login to remove.'], 404);
    }
}
