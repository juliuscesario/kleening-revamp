<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Area;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function revenue()
    {
        $this->authorize('viewAny', \App\Models\Invoice::class); // Reuse Invoice policy for report access
        $areas = Area::all();
        return view('pages.reports.revenue', compact('areas'));
    }
}

class ReportController extends Controller
{
    //
}
