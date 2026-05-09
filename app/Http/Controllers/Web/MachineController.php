<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MachineCategory;
use App\Models\Area;

class MachineController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = MachineCategory::where('is_active', true)->orderBy('sort_order')->get();
        $areas = Area::orderBy('name')->get();

        return view('pages.machines.index', compact('categories', 'areas'));
    }
}
