<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Address;
use App\Models\Service;
use App\Models\Staff;
use App\Models\ServiceOrder;
use App\Models\ServiceOrderItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ServiceOrderController extends Controller
{
    public function index()
    {
        return view('pages.service-orders.index');
    }

    public function create(Request $request)
    {
        $address = Address::with('customer')->findOrFail($request->address_id);
        $user = Auth::user();

        // Authorization
        if ($user->role === 'co_owner' && $user->area_id !== $address->area_id) {
            abort(403, 'Anda tidak diizinkan membuat pesanan untuk area ini.');
        }

        $services = Service::all();
        $staff = Staff::where('area_id', $address->area_id)->get();

        return view('pages.service-orders.create', compact('address', 'services', 'staff'));
    }

    public function show(ServiceOrder $serviceOrder)
    {
        $serviceOrder->load(['customer' => function ($query) {
            $query->withTrashed();
        }, 'address.area', 'items.service', 'staff']);
        return view('pages.service-orders.show', compact('serviceOrder'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'address_id' => 'required|exists:addresses,id',
            'work_date' => 'required|date',
            'services' => 'required|array|min:1',
            'services.*' => 'required|exists:services,id',
            'staff' => 'nullable|array',
            'staff.*' => 'exists:staff,id',
        ]);

        $user = Auth::user();

        // Authorization check again, just in case
        $address = Address::findOrFail($request->address_id);
        if ($user->role === 'co_owner' && $user->area_id !== $address->area_id) {
            abort(403, 'Anda tidak diizinkan membuat pesanan untuk area ini.');
        }

        $serviceOrder = DB::transaction(function () use ($request, $user) {
            $so = ServiceOrder::create([
                'customer_id' => $request->customer_id,
                'address_id' => $request->address_id,
                'work_date' => $request->work_date,
                'work_notes' => $request->work_notes,
                'staff_notes' => $request->staff_notes,
                'status' => 'baru',
                'created_by' => $user->id,
                'so_number' => 'SO-' . date('Ymd') . '-' . str_pad(ServiceOrder::count() + 1, 4, '0', STR_PAD_LEFT),
            ]);

            $totalPrice = 0;
            foreach ($request->services as $serviceId) {
                $service = Service::find($serviceId);
                ServiceOrderItem::create([
                    'service_order_id' => $so->id,
                    'service_id' => $service->id,
                    'price' => $service->price,
                ]);
                $totalPrice += $service->price;
            }

            if ($request->has('staff')) {
                $so->staff()->attach($request->staff);
            }

            return $so;
        });

        // For now, redirect to the customer detail page
        return redirect()->route('web.customers.show', $request->customer_id)->with('success', 'Service Order berhasil dibuat.');
    }
}
