<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Staff;
use App\Http\Resources\StaffResource; // <-- Tambahkan Http di sini
use Illuminate\Validation\Rule; // <-- TAMBAHKAN BARIS INI

class StaffController extends Controller
{
    public function index()
    {
        // Eager load relasi area dan user
        return StaffResource::collection(Staff::with(['area', 'user'])->get());
    }

    public function store(Request $request)
    {
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
        return new StaffResource($staff->load(['area', 'user']));
    }

    public function update(Request $request, Staff $staff)
    {
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
        $staff->delete();
        return response()->noContent();
    }
}
