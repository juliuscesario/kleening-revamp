<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\ServiceCategory;
use App\Models\Customer; // <-- Tambahkan model lain jika perlu
use App\Models\Staff;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class DataTablesController extends Controller
{
    use AuthorizesRequests; // <-- Gunakan trait ini untuk cek hak akses

    public function areas()
    {
        // 1. Cek hak akses (Policy) secara manual
        $this->authorize('viewAny', Area::class);

        // 2. Ambil data. Global Scope untuk co-owner akan otomatis berjalan di sini.
        $query = Area::query();

        // 3. Serahkan ke Yajra untuk diproses
        return DataTables::of($query)
            ->editColumn('created_at', function ($area) {
                return Carbon::parse($area->created_at)->format('d M Y');
            })
            ->addColumn('action', function ($area) {
                return '
                    <button class="btn btn-sm btn-warning editArea" data-id="' . $area->id . '" data-name="' . e($area->name) . '">Edit</button>
                    <button class="btn btn-sm btn-danger deleteArea" data-id="' . $area->id . '">Hapus</button>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    // --- ADD THIS NEW SERVICE CATEGORY METHOD ---
    public function serviceCategories()
    {
        // 1. Cek hak akses (Policy) secara manual
        $this->authorize('viewAny', ServiceCategory::class);

        $query = ServiceCategory::query();

        return DataTables::of($query)
            ->editColumn('created_at', function ($category) {
                return Carbon::parse($category->created_at)->format('d M Y H:i');
            })
            ->addColumn('action', function ($category) {
                $name = htmlspecialchars($category->name, ENT_QUOTES, 'UTF-8');
                $editBtn = '<button class="btn btn-sm btn-success editServiceCategory" data-id="' . $category->id . '" data-name="' . $name . '">Edit</button>';
                // $deleteBtn = '<button class="btn btn-sm btn-danger deleteServiceCategory" data-id="' . $category->id . '">Hapus</button>';
                return $editBtn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function staff()
    {
        $this->authorize('viewAny', \App\Models\Staff::class);

        $query = \App\Models\Staff::with(['area', 'user'])->select('staff.*');

        return DataTables::of($query)
            ->addColumn('phone_number', function ($staff) {
                return $staff->phone_number;
            })
            ->addColumn('area', function ($staff) {
                return $staff->area->name;
            })
            ->addColumn('role', function ($staff) {
                if ($staff->user) {
                    return $staff->user->role;
                }
                return '<span class="badge bg-warning">No Login</span>';
            })
            ->editColumn('created_at', function ($staff) {
                return \Carbon\Carbon::parse($staff->created_at)->format('d M Y');
            })
            ->addColumn('action', function ($staff) {
                $actions = '<button class="btn btn-sm btn-info edit-button" data-id="' . $staff->id . '">Edit</button>';
                if ($staff->user) {
                    $actions .= ' <button class="btn btn-sm btn-danger resign-button" data-id="' . $staff->id . '">Resign</button>';
                }
                return $actions;
            })
            ->rawColumns(['action', 'role'])
            ->make(true);
    }

    public function services()
    {
        $this->authorize('viewAny', \App\Models\Service::class);

        $query = \App\Models\Service::with('category');

        return DataTables::of($query)
            ->editColumn('price', function ($service) {
                return 'Rp ' . number_format($service->price, 0, ',', '.');
            })
            ->addColumn('category_name', function ($service) {
                return $service->category->name;
            })
            ->editColumn('created_at', function ($service) {
                return \Carbon\Carbon::parse($service->created_at)->format('d M Y H:i');
            })
            ->addColumn('action', function ($service) {
                $actions = '';
                if (auth()->user()->can('update', $service)) {
                    $actions .= '<button class="btn btn-sm btn-warning edit-service" data-id="' . $service->id . '">Edit</button>';
                }
                if (auth()->user()->can('delete', $service)) {
                    $actions .= ' <button class="btn btn-sm btn-danger delete-service" data-id="' . $service->id . '">Hapus</button>';
                }
                return $actions;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function customers()
    {
        $this->authorize('viewAny', \App\Models\Customer::class);

        $query = \App\Models\Customer::withCount('addresses')->with(['serviceOrders.staff.area']);

        return DataTables::of($query)
            ->addColumn('area', function ($customer) {
                $latestOrder = $customer->serviceOrders()->latest('work_date')->first();
                if ($latestOrder && $latestOrder->staff->isNotEmpty()) {
                    $firstStaff = $latestOrder->staff->first();
                    if ($firstStaff && $firstStaff->area) {
                        return $firstStaff->area->name;
                    }
                }
                return 'N/A';
            })
            ->addColumn('latest_order_date', function ($customer) {
                return $customer->last_order_date ? \Carbon\Carbon::parse($customer->last_order_date)->format('d M Y') : 'N/A';
            })
            ->editColumn('created_at', function ($customer) {
                return \Carbon\Carbon::parse($customer->created_at)->format('d M Y');
            })
            ->addColumn('action', function ($customer) {
                $detailUrl = route('web.customers.show', $customer->id);
                $actions = '<a href="' . $detailUrl . '" class="btn btn-sm btn-secondary">Detail</a>';
                $actions .= ' <button class="btn btn-sm btn-info show-addresses" data-id="' . $customer->id . '">Alamat</button>';
                if (auth()->user()->can('update', $customer)) {
                    $actions .= ' <button class="btn btn-sm btn-warning edit-customer" data-id="' . $customer->id . '">Edit</button>';
                }
                if (auth()->user()->can('delete', $customer)) {
                    $actions .= ' <button class="btn btn-sm btn-danger delete-customer" data-id="' . $customer->id . '">Hapus</button>';
                }
                return $actions;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function addresses()
    {
        $this->authorize('viewAny', \App\Models\Address::class);

        $query = \App\Models\Address::with('customer');

        return DataTables::of($query)
            ->addColumn('customer_name', function ($address) {
                return $address->customer ? $address->customer->name : 'N/A';
            })
            ->addColumn('action', function ($address) {
                $actions = '';
                // Per user request, edit is done from customer side. We only allow delete here.
                if (auth()->user()->can('delete', $address)) {
                    $actions .= '<button class="btn btn-sm btn-danger delete-address" data-id="' . $address->id . '">Hapus</button>';
                }
                return $actions;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    // --- CONTOH UNTUK CUSTOMER ---
    // Nanti, saat Anda membuat halaman customer, Anda tinggal tambahkan method ini
    /*
    public function customers()
    {
        $this->authorize('viewAny', Customer::class);
        $query = Customer::query();
        return DataTables::of($query)
            ->addColumn('action', function ($customer) {
                // ... tombol aksi untuk customer ...
            })
            ->rawColumns(['action'])
            ->make(true);
    }
    */
}
