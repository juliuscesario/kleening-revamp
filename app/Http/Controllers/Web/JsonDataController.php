<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables; // Add this import

class JsonDataController extends Controller
{


    public function customerAddresses(Customer $customer)
    {
        return $customer->addresses()->with('area')->get();
    }

    public function staffByArea(Area $area)
    {
        $user = Auth::user();
        $query = Staff::query()->where('area_id', $area->id);

        if ($user->role == 'co_owner' && $user->area_id != $area->id) {
            return response()->json([], 403);
        }

        return $query->get();
    }

    public function services(Request $request)
    {
        $query = Service::query();

        if ($request->has('q')) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }

        return $query->get();
    }
}
