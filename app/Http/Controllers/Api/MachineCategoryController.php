<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MachineCategory;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class MachineCategoryController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('viewAny', MachineCategory::class);

        return MachineCategory::withCount('machines')->orderBy('sort_order')->get();
    }

    public function store(Request $request)
    {
        $this->authorize('create', MachineCategory::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code_prefix' => 'required|string|unique:machine_categories,code_prefix|max:50',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_active'] = $validated['is_active'] ?? true;

        $category = MachineCategory::create($validated);

        return $category;
    }

    public function show(MachineCategory $machineCategory)
    {
        $this->authorize('view', $machineCategory);

        $machineCategory->loadCount('machines');

        return $machineCategory;
    }

    public function update(Request $request, MachineCategory $machineCategory)
    {
        $this->authorize('update', $machineCategory);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code_prefix' => [
                'required',
                'string',
                'max:50',
                Rule::unique('machine_categories')->ignore($machineCategory->id),
            ],
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $machineCategory->update($validated);

        return $machineCategory;
    }

    public function destroy(MachineCategory $machineCategory)
    {
        $this->authorize('delete', $machineCategory);

        if ($machineCategory->machines()->count() > 0) {
            return response()->json([
                'message' => 'Kategori ini masih memiliki mesin terkait',
            ], 422);
        }

        $machineCategory->delete();

        return response()->noContent();
    }
}
