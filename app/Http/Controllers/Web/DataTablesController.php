<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\ServiceCategory;
use App\Models\Customer; // <-- Tambahkan model lain jika perlu
use App\Models\Staff;
use App\Models\Scopes\AreaScope;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request; // <-- ADD THIS LINE

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
            ->orderColumn('created_at', function ($query, $order) {
                $query->orderBy('created_at', $order);
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
            ->orderColumn('created_at', function ($query, $order) {
                $query->orderBy('created_at', $order);
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
            ->orderColumn('created_at', function ($query, $order) {
                $query->orderBy('created_at', $order);
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
            ->orderColumn('created_at', function ($query, $order) {
                $query->orderBy('created_at', $order);
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

    public function customers(Request $request)
    {
        $this->authorize('viewAny', Customer::class);

        $user = auth()->user();
        $query = Customer::query()
            ->select('customers.*')
            ->withCount('addresses') // Re-add this
            ->with('addresses.area') // Keep this for area_name
            ->leftJoin('service_orders', 'customers.id', '=', 'service_orders.customer_id') // Re-add this
            ->groupBy('customers.id') // Re-add this
            ->selectRaw('MAX(service_orders.work_date) as last_order_date_raw'); // Re-add this

        if ($user->role == 'co-owner') {
            $query->whereHas('addresses', function ($query) use ($user) {
                $query->where('area_id', $user->area_id);
            });
        }

        if ($request->has('q')) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }

        return DataTables::of($query)
            ->order(function ($query) { // Re-add this
                $query->orderBy('last_order_date_raw', 'desc');
            })
            ->addColumn('latest_order_date', function ($customer) { // Re-add this
                return $customer->last_order_date_raw ? \Carbon\Carbon::parse($customer->last_order_date_raw)->format('d M Y') : 'N/A';
            })
            ->orderColumn('latest_order_date', function ($query, $order) { // Re-add this
                $query->orderBy('last_order_date_raw', $order);
            })
            ->editColumn('created_at', function ($customer) {
                return \Carbon\Carbon::parse($customer->created_at)->format('d M Y');
            })
            ->orderColumn('created_at', function ($query, $order) {
                $query->orderBy('created_at', $order);
            })
            ->addColumn('area_name', function (Customer $customer) {
                return $customer->addresses->first()?->area->name ?? 'N/A';
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

    public function addresses(Request $request)
    {
        $this->authorize('viewAny', \App\Models\Address::class);

        $query = \App\Models\Address::with(['customer', 'area']);

        return DataTables::of($query)
            ->addColumn('customer_name', function ($address) {
                return $address->customer ? $address->customer->name : 'N/A';
            })
            ->addColumn('area_name', function ($address) {
                return $address->area ? $address->area->name : 'N/A';
            })
            ->addColumn('action', function ($address) {
                $actions = '';
                if (auth()->user()->can('update', $address)) {
                    $actions .= '<button class="btn btn-sm btn-warning edit-address" data-id="' . $address->id . '">Edit</button> ';
                }
                if ($address->google_maps_link) {
                    $actions .= '<a href="' . $address->google_maps_link . '" target="_blank" class="btn btn-sm btn-info">Peta</a> ';
                }
                if (auth()->user()->can('delete', $address)) { // Pass the $address instance
                    $actions .= '<button class="btn btn-sm btn-danger delete-address" data-id="' . $address->id . '">Hapus</button>';
                }
                return $actions;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function serviceOrders(Request $request)
    {
        $this->authorize('viewAny', \App\Models\ServiceOrder::class);

        $query = \App\Models\ServiceOrder::with(['customer' => function ($query) {
            $query->withoutGlobalScope(AreaScope::class)->withTrashed();
        }, 'address.area']);

        // Apply status filter if present in the request
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        return DataTables::of($query)
            ->order(function ($query) {
                $query->orderBy('work_date', 'desc');
            })
            ->addColumn('customer_name', function ($so) {
                if ($so->customer) {
                    $customerName = $so->customer->name;
                    if ($so->customer->trashed()) {
                        $customerName .= ' <span class="badge bg-danger text-bg-secondary">Archived</span>';
                    }
                    return $customerName;
                }
                return 'N/A';
            })
            ->editColumn('work_date', function ($so) {
                return \Carbon\Carbon::parse($so->work_date)->format('d M Y');
            })
            ->orderColumn('work_date', function ($query, $order) {
                $query->orderBy('work_date', $order);
            })
            ->orderColumn('status', function ($query, $order) {
                $query->orderBy('status', $order);
            })
            ->addColumn('action', function ($so) {
                $detailUrl = route('web.service-orders.show', $so->id);
                $actions = '<a href="' . $detailUrl . '" class="btn btn-sm btn-secondary">Detail</a> ';
                
                // Conditional buttons for status transitions
                switch ($so->status) {
                    case \App\Models\ServiceOrder::STATUS_BOOKED:
                        $actions .= '<button class="btn btn-sm btn-primary change-status-btn" data-id="' . $so->id . '" data-new-status="' . \App\Models\ServiceOrder::STATUS_PROSES . '">Proses</button> ';
                        $actions .= '<button class="btn btn-sm btn-danger change-status-btn" data-id="' . $so->id . '" data-new-status="' . \App\Models\ServiceOrder::STATUS_CANCELLED . '">Cancel</button> ';
                        break;
                    case \App\Models\ServiceOrder::STATUS_PROSES:
                        $actions .= '<button class="btn btn-sm btn-success change-status-btn" data-id="' . $so->id . '" data-new-status="' . \App\Models\ServiceOrder::STATUS_DONE . '">Done</button> ';
                        if (auth()->user()->role === 'owner') {
                            $actions .= '<button class="btn btn-sm btn-danger change-status-btn" data-id="' . $so->id . '" data-new-status="' . \App\Models\ServiceOrder::STATUS_CANCELLED . '">Cancel</button> ';
                        }
                        break;
                    // For CANCELLED, DONE, and INVOICED, no further status transitions are allowed from the UI
                }

                if ($so->invoice) {
                    $actions .= '<a href="#" class="btn btn-sm btn-success">Invoice</a> ';
                } else if ($so->status === \App\Models\ServiceOrder::STATUS_DONE) {
                    $actions .= '<button class="btn btn-sm btn-primary create-invoice" data-id="' . $so->id . '">Create Invoice</button> ';
                }

                return $actions;
            })
            ->rawColumns(['action', 'customer_name', 'status'])
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
