<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TenantController extends Controller
{
    public function index()
    {
        \Illuminate\Support\Facades\Log::info('TenantController index method hit by:', ['user_id' => auth()->id()]);
        $tenants = Tenant::all();
        return view('superadmin.tenants.index', compact('tenants'));
    }

    public function create()
    {
        return view('superadmin.tenants.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:tenants,slug',
            'domain' => 'nullable|string|max:255|unique:tenants,domain',
            'owner_name' => 'required|string|max:255',
            'owner_phone' => 'required|string|max:255',
            'password' => 'required|string|min:6',
            'areas' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($validated) {
            // Create tenant
            $tenant = Tenant::create([
                'name' => $validated['name'],
                'slug' => $validated['slug'],
                'domain' => $validated['domain'],
            ]);

            // Create owner user
            $tenant->users()->create([
                'name' => $validated['owner_name'],
                'phone_number' => $validated['owner_phone'],
                'password' => $validated['password'], // Likely hashed by model cast
                'role' => 'owner',
            ]);

            // Create areas
            if (!empty($validated['areas'])) {
                $areaNames = array_filter(array_map('trim', explode("\n", $validated['areas'])));
                foreach ($areaNames as $name) {
                    $tenant->areas()->create(['name' => $name]);
                }
            }

            return redirect()->route('superadmin.tenants.index')->with('success', 'Tenant, owner, and areas created successfully.');
        });
    }

    public function edit(Tenant $tenant)
    {
        $owner = $tenant->users()->where('role', 'owner')->first();
        return view('superadmin.tenants.edit', compact('tenant', 'owner'));
    }

    public function update(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:tenants,slug,' . $tenant->id,
            'domain' => 'nullable|string|max:255|unique:tenants,domain,' . $tenant->id,
            'owner_name' => 'nullable|string|max:255',
            'owner_phone' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        return DB::transaction(function () use ($validated, $tenant) {
            $tenant->update([
                'name' => $validated['name'],
                'slug' => $validated['slug'],
                'domain' => $validated['domain'],
            ]);

            // Update owner if provided
            if (!empty($validated['owner_name']) || !empty($validated['owner_phone']) || !empty($validated['password'])) {
                $owner = $tenant->users()->where('role', 'owner')->first();
                if ($owner) {
                    $ownerData = [];
                    if (!empty($validated['owner_name'])) $ownerData['name'] = $validated['owner_name'];
                    if (!empty($validated['owner_phone'])) $ownerData['phone_number'] = $validated['owner_phone'];
                    if (!empty($validated['password'])) $ownerData['password'] = $validated['password'];
                    $owner->update($ownerData);
                }
            }

            $msg = 'Tenant updated successfully.';
            if (!empty($validated['password'])) {
                $msg = 'Tenant updated and password reset successfully.';
            }

            return redirect()->route('superadmin.tenants.index')->with('success', $msg);
        });
    }

    public function destroy(Tenant $tenant)
    {
        $tenant->delete();
        return redirect()->route('superadmin.tenants.index')->with('success', 'Tenant deleted successfully.');
    }
}
