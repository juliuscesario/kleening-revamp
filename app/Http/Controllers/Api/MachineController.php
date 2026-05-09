<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Machine;
use App\Models\MachineCategory;
use App\Models\Area;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class MachineController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Machine::class);

        $query = Machine::with(['category', 'area', 'pairedMachine']);

        // Support filter parameters
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('area_id')) {
            $query->where('area_id', $request->area_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $query->get();
    }

    public function store(Request $request)
    {
        $this->authorize('create', Machine::class);

        $validated = $request->validate([
            'code' => 'required|string|unique:machines,code|max:255',
            'name' => 'nullable|string|max:255',
            'category_id' => 'required|exists:machine_categories,id',
            'area_id' => 'required|exists:areas,id',
            'status' => 'required|in:active,maintenance,retired',
            'paired_machine_id' => 'nullable|exists:machines,id',
            'notes' => 'nullable|string',
        ]);

        // paired_machine_id must not be self
        if (!empty($validated['paired_machine_id'])) {
            // We don't have the ID yet since it's a new record, so this is fine
        }

        $machine = Machine::create($validated);

        return $machine->load(['category', 'area', 'pairedMachine']);
    }

    public function show(Machine $machine)
    {
        $this->authorize('view', $machine);

        $machine->load(['category', 'area', 'pairedMachine']);

        return $machine;
    }

    public function update(Request $request, Machine $machine)
    {
        $this->authorize('update', $machine);

        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('machines')->ignore($machine->id),
            ],
            'name' => 'nullable|string|max:255',
            'category_id' => 'required|exists:machine_categories,id',
            'area_id' => 'required|exists:areas,id',
            'status' => 'required|in:active,maintenance,retired',
            'paired_machine_id' => [
                'nullable',
                'exists:machines,id',
            ],
            'notes' => 'nullable|string',
        ]);

        // paired_machine_id must not be self
        if (!empty($validated['paired_machine_id']) && $validated['paired_machine_id'] == $machine->id) {
            return response()->json([
                'message' => 'Mesin tidak bisa dipasangkan dengan dirinya sendiri',
            ], 422);
        }

        $machine->update($validated);

        return $machine->load(['category', 'area', 'pairedMachine']);
    }

    public function destroy(Machine $machine)
    {
        $this->authorize('delete', $machine);

        // Before deleting, set paired_machine_id = null on any machine that references this one
        Machine::where('paired_machine_id', $machine->id)->update(['paired_machine_id' => null]);

        $machine->delete();

        return response()->noContent();
    }

    /**
     * Suggest the next machine code based on category and area.
     */
    public function nextCode(Request $request)
    {
        $this->authorize('viewAny', Machine::class);

        $categoryId = $request->query('category_id');
        $areaId = $request->query('area_id');

        if (!$categoryId || !$areaId) {
            return response()->json(['suggested_code' => '']);
        }

        $category = MachineCategory::find($categoryId);
        $area = Area::find($areaId);

        if (!$category || !$area) {
            return response()->json(['suggested_code' => '']);
        }

        $prefix = $category->code_prefix;

        // Derive area suffix — case-insensitive mapping
        $areaName = strtoupper(trim($area->name));
        $areaSuffixMap = [
            'JADETABEK' => 'jkt',
            'SERANG' => 'srg',
            'MALANG' => 'mlg',
        ];

        $suffix = null;
        foreach ($areaSuffixMap as $key => $val) {
            if (strpos($areaName, $key) !== false) {
                $suffix = $val;
                break;
            }
        }

        // Fallback: first 3 lowercase letters
        if (!$suffix) {
            $suffix = strtolower(substr(preg_replace('/[^a-zA-Z]/', '', $area->name), 0, 3));
        }

        // Find highest existing number for this prefix+area combo
        $pattern = "^{$prefix}-{$suffix}";
        $highestMachine = Machine::where('code', 'ILIKE', "{$prefix}-{$suffix}%")
            ->orderByRaw("LENGTH(code) DESC, code DESC")
            ->first();

        $nextNumber = 1;
        if ($highestMachine) {
            // Extract the number from the code
            if (preg_match('/(\d+)$/', $highestMachine->code, $matches)) {
                $nextNumber = (int) $matches[1] + 1;
            }
        }

        $suggestedCode = "{$prefix}-{$suffix}{$nextNumber}";

        return response()->json(['suggested_code' => $suggestedCode]);
    }
}
