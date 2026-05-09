<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Models\Area;

class MachineAttendanceManageController extends Controller
{
    public function index()
    {
        $staff = Staff::orderBy('name')->get();
        $areas = Area::orderBy('name')->get();

        return view('pages.machine-attendances.index', compact('staff', 'areas'));
    }
}
