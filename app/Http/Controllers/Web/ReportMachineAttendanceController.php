<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\MachineCategory;
use App\Models\Staff;

class ReportMachineAttendanceController extends Controller
{
    public function index()
    {
        $staff = Staff::orderBy('name')->get();
        $areas = Area::orderBy('name')->get();
        $categories = MachineCategory::where('is_active', true)->orderBy('sort_order')->get();

        return view('pages.reports.machine-attendance', compact('staff', 'areas', 'categories'));
    }
}
