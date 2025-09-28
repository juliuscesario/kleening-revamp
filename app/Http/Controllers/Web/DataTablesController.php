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

use Illuminate\Support\Facades\DB;
use Carbon\CarbonPeriod;

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
                return $service->category ? $service->category->name : 'N/A';
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
                    $actions .= '<a href="' . route('web.invoices.show', $so->invoice->id) . '" class="btn btn-sm btn-success">Invoice</a> ';
                } else if ($so->status === \App\Models\ServiceOrder::STATUS_DONE) {
                    $actions .= '<button class="btn btn-sm btn-primary create-invoice" data-id="' . $so->id . '">Create Invoice</button> ';
                }

                return $actions;
            })
            ->rawColumns(['action', 'customer_name', 'status'])
            ->make(true);
    }

    public function invoices()
    {
        $this->authorize('viewAny', \App\Models\Invoice::class);

        $query = \App\Models\Invoice::with('serviceOrder.customer');

        if (auth()->user()->role === 'co_owner') {
            $query->whereHas('serviceOrder.address', function ($q) {
                $q->where('area_id', auth()->user()->area_id);
            });
        }

        return DataTables::of($query)
            ->addColumn('so_number', function ($invoice) {
                return $invoice->serviceOrder->so_number;
            })
            ->editColumn('issue_date', function ($invoice) {
                return Carbon::parse($invoice->issue_date)->format('d M Y');
            })
            ->editColumn('due_date', function ($invoice) {
                return Carbon::parse($invoice->due_date)->format('d M Y');
            })
            ->editColumn('grand_total', function ($invoice) {
                return 'Rp ' . number_format($invoice->grand_total, 2, ',', '.');
            })
            ->editColumn('status', function ($invoice) {
                $statusBadgeClass = '';
                switch ($invoice->status) {
                    case 'new': $statusBadgeClass = 'bg-primary'; break;
                    case 'sent': $statusBadgeClass = 'bg-info'; break;
                    case 'overdue': $statusBadgeClass = 'bg-warning'; break;
                    case 'paid': $statusBadgeClass = 'bg-success'; break;
                    default: $statusBadgeClass = 'bg-secondary'; break;
                }
                return '<span class="badge ' . $statusBadgeClass . ' text-bg-secondary">' . ucfirst($invoice->status) . '</span>';
            })
            ->addColumn('action', function ($invoice) {
                $detailUrl = route('web.invoices.show', $invoice->id);
                $actions = '<a href="' . $detailUrl . '" class="btn btn-sm btn-secondary">Detail</a> ';

                switch ($invoice->status) {
                    case 'new':
                        $actions .= '<button class="btn btn-sm btn-info change-status-btn" data-id="' . $invoice->id . '" data-new-status="' . \App\Models\Invoice::STATUS_SENT . '">Mark as Sent</button> ';
                        break;
                    case 'sent':
                        $actions .= '<button class="btn btn-sm btn-success change-status-btn" data-id="' . $invoice->id . '" data-new-status="' . \App\Models\Invoice::STATUS_PAID . '">Mark as Paid</button> ';
                        break;
                    case 'overdue':
                        $actions .= '<button class="btn btn-sm btn-success change-status-btn" data-id="' . $invoice->id . '" data-new-status="' . \App\Models\Invoice::STATUS_PAID . '">Mark as Paid</button> ';
                        break;
                }

                return $actions;
            })
            ->rawColumns(['action', 'status'])
            ->make(true);
    }

    public function payments()
    {
        $this->authorize('viewAny', \App\Models\Payment::class);

        $query = \App\Models\Payment::with('invoice.serviceOrder.customer');

        if (auth()->user()->role === 'co_owner') {
            $query->whereHas('invoice.serviceOrder.address', function ($q) {
                $q->where('area_id', auth()->user()->area_id);
            });
        }

        return DataTables::of($query)
            ->addColumn('invoice_number', function ($payment) {
                return $payment->invoice->invoice_number;
            })
            ->editColumn('payment_date', function ($payment) {
                return Carbon::parse($payment->payment_date)->format('d M Y');
            })
            ->editColumn('amount', function ($payment) {
                return 'Rp ' . number_format($payment->amount, 0, ',', '.');
            })
            ->addColumn('action', function ($payment) {
                $detailUrl = route('web.payments.show', $payment->id);
                return '<a href="' . $detailUrl . '" class="btn btn-sm btn-secondary">Detail</a>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function revenueReportData(Request $request)
    {
        $this->authorize('viewAny', \App\Models\Invoice::class);

        $query = \App\Models\ServiceCategory::query()
            ->withCount(['serviceOrderItems as total_orders' => function ($query) use ($request) {
                $query->whereHas('serviceOrder.invoice', function ($q) use ($request) {
                    $q->where('status', \App\Models\Invoice::STATUS_PAID);
                    if ($request->filled('start_date') && $request->filled('end_date')) {
                        $q->whereHas('payments', function ($paymentQuery) use ($request) {
                            $paymentQuery->whereBetween('payment_date', [$request->start_date, $request->end_date]);
                        });
                    }
                });

                if (auth()->user()->role === 'co_owner' || ($request->filled('area_id') && $request->area_id !== 'all')) {
                    $areaId = auth()->user()->role === 'co_owner' ? auth()->user()->area_id : $request->area_id;
                    $query->whereHas('serviceOrder.address', function ($q) use ($areaId) {
                        $q->where('area_id', $areaId);
                    });
                }
            }])
            ->withSum(['serviceOrderItems as total_revenue' => function ($query) use ($request) {
                $query->whereHas('serviceOrder.invoice', function ($q) use ($request) {
                    $q->where('status', \App\Models\Invoice::STATUS_PAID);
                    if ($request->filled('start_date') && $request->filled('end_date')) {
                        $q->whereHas('payments', function ($paymentQuery) use ($request) {
                            $paymentQuery->whereBetween('payment_date', [$request->start_date, $request->end_date]);
                        });
                    }
                });

                if (auth()->user()->role === 'co_owner' || ($request->filled('area_id') && $request->area_id !== 'all')) {
                    $areaId = auth()->user()->role === 'co_owner' ? auth()->user()->area_id : $request->area_id;
                    $query->whereHas('serviceOrder.address', function ($q) use ($areaId) {
                        $q->where('area_id', $areaId);
                    });
                }
            }], 'total');

        $dataTable = DataTables::of($query)
            ->editColumn('total_revenue', function ($category) {
                return 'Rp ' . number_format($category->total_revenue ?? 0, 0, ',', '.');
            })
            ->orderColumn('total_revenue', function ($query, $order) {
                $query->orderBy('total_revenue', $order);
            })
            ->orderColumn('total_orders', function ($query, $order) {
                $query->orderBy('total_orders', $order);
            });

        // Calculate summary data
        $summaryQuery = \App\Models\Invoice::query()
            ->where('status', \App\Models\Invoice::STATUS_PAID);
        
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $summaryQuery->whereHas('payments', function ($paymentQuery) use ($request) {
                $paymentQuery->whereBetween('payment_date', [$request->start_date, $request->end_date]);
            });
        }

        if (auth()->user()->role === 'co_owner') {
            $areaId = auth()->user()->area_id;
            $summaryQuery->whereHas('serviceOrder.address', function ($q) use ($areaId) {
                $q->where('area_id', $areaId);
            });
        } elseif ($request->filled('area_id') && $request->area_id !== 'all') {
            $areaId = $request->area_id;
            $summaryQuery->whereHas('serviceOrder.address', function ($q) use ($areaId) {
                $q->where('area_id', $areaId);
            });
        }

        $totalRevenue = $summaryQuery->sum('grand_total');
        $totalOrders = $summaryQuery->count();
        $avgRevenue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        return $dataTable->with('summary', [
            'total_revenue' => 'Rp ' . number_format($totalRevenue, 0, ',', '.'),
            'total_orders' => number_format($totalOrders, 0, ',', '.'),
            'avg_revenue' => 'Rp ' . number_format($avgRevenue, 0, ',', '.'),
        ])->make(true);
    }

    public function staffPerformanceReportData(Request $request)
    {
        $this->authorize('viewAny', \App\Models\Staff::class);

        $query = \App\Models\Staff::with('user', 'area')->select('staff.*');

        if (auth()->user()->role === 'co_owner') {
            $query->where('area_id', auth()->user()->area_id);
        } elseif ($request->filled('area_id') && $request->area_id !== 'all') {
            $query->where('area_id', $request->area_id);
        }

        if ($request->filled('staff_id') && $request->staff_id !== 'all') {
            $query->where('id', $request->staff_id);
        }

        $dataTable = DataTables::of($query)
            ->addColumn('name', function($staff) {
                return $staff->name;
            })
            ->addColumn('area_name', function($staff) {
                return $staff->area->name ?? 'N/A';
            })
            ->addColumn('jobs_completed', function ($staff) use ($request) {
                $jobsQuery = $staff->serviceOrders()
                    ->whereIn('status', [\App\Models\ServiceOrder::STATUS_DONE, \App\Models\ServiceOrder::STATUS_INVOICED]);
                if ($request->filled('start_date') && $request->filled('end_date')) {
                    $jobsQuery->whereBetween('work_date', [$request->start_date, $request->end_date]);
                }
                return $jobsQuery->count();
            })
            ->addColumn('total_revenue', function ($staff) use ($request) {
                $revenueQuery = \App\Models\Invoice::where('status', \App\Models\Invoice::STATUS_PAID)
                    ->whereHas('serviceOrder', function($soQuery) use ($staff) {
                        $soQuery->whereHas('staff', function($staffQuery) use ($staff) {
                            $staffQuery->where('staff.id', $staff->id);
                        });
                    });

                if ($request->filled('start_date') && $request->filled('end_date')) {
                    $revenueQuery->whereHas('payments', function ($paymentQuery) use ($request) {
                        $paymentQuery->whereBetween('payment_date', [$request->start_date, $request->end_date]);
                    });
                }
                return 'Rp ' . number_format($revenueQuery->sum('grand_total'), 0, ',', '.');
            });

        return $dataTable->make(true);
    }

    public function customerGrowthReportData(Request $request)
    {
        $this->authorize('viewAny', \App\Models\Customer::class);

        // 1. Create the base subquery with calculated columns
        $subQuery = \App\Models\Customer::query()->select('customers.*')->withTrashed();

        $user = auth()->user();
        if ($user->role == 'co-owner') {
            $subQuery->whereHas('addresses', function ($q) use ($user) {
                $q->where('area_id', $user->area_id);
            });
        } elseif ($request->filled('area_id') && $request->area_id !== 'all') {
            $areaId = $request->area_id;
            $subQuery->whereHas('addresses', function ($q) use ($areaId) {
                $q->where('area_id', $areaId);
            });
        }

        $subQuery->withSum(['invoices as total_revenue' => function ($q) use ($request) {
            $q->where('invoices.status', \App\Models\Invoice::STATUS_PAID);
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $q->whereHas('payments', function ($paymentQuery) use ($request) {
                    $paymentQuery->whereBetween('payment_date', [$request->start_date, $request->end_date]);
                });
            }
        }], 'grand_total')
        ->withCount(['invoices as total_orders' => function ($q) use ($request) {
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $q->whereHas('payments', function ($paymentQuery) use ($request) {
                    $paymentQuery->whereBetween('payment_date', [$request->start_date, $request->end_date]);
                });
            }
        }]);

        // 2. Create the main query from the subquery, which allows WHERE on aliases
        $query = \App\Models\Customer::fromSub($subQuery, 'customers')
            ->where('total_revenue', '>', 0);

        return DataTables::of($query)
            ->addColumn('name', function($customer) {
                $name = $customer->name;
                // Manually check for soft delete, since we don't have an Eloquent model
                if (!empty($customer->deleted_at)) {
                    $name .= ' <span class="badge bg-danger text-bg-secondary">Archived</span>';
                }
                return $name;
            })
            ->editColumn('total_revenue', function ($customer) {
                return 'Rp ' . number_format($customer->total_revenue ?? 0, 0, ',', '.');
            })
            ->orderColumn('total_revenue', function ($query, $order) {
                $query->orderBy('total_revenue', $order);
            })
            ->orderColumn('total_orders', function ($query, $order) {
                $query->orderBy('total_orders', $order);
            })
            ->rawColumns(['name'])
            ->make(true);
    }

    public function revenueTrendChartData(Request $request, \App\Models\ServiceCategory $serviceCategory)
    {
        $this->authorize('viewAny', \App\Models\Invoice::class);

        $query = \App\Models\ServiceOrderItem::query()
            ->whereHas('service', function ($query) use ($serviceCategory) { $query->where('category_id', $serviceCategory->id); })
            ->whereHas('serviceOrder.invoice', function ($q) use ($request) {
                $q->where('status', \App\Models\Invoice::STATUS_PAID);
                if ($request->filled('start_date') && $request->filled('end_date')) {
                    $q->whereHas('payments', function ($paymentQuery) use ($request) {
                        $paymentQuery->whereBetween('payment_date', [$request->start_date, $request->end_date]);
                    });
                }
            });

        if (auth()->user()->role === 'co_owner') {
            $areaId = auth()->user()->area_id;
            $query->whereHas('serviceOrder.address', function ($q) use ($areaId) {
                $q->where('area_id', $areaId);
            });
        } elseif ($request->filled('area_id') && $request->area_id !== 'all') {
            $areaId = $request->area_id;
            $query->whereHas('serviceOrder.address', function ($q) use ($areaId) {
                $q->where('area_id', $areaId);
            });
        }

        $revenueData = $query->join('service_orders', 'service_order_items.service_order_id', '=', 'service_orders.id')
            ->select(
                DB::raw('DATE(service_orders.work_date) as date'),
                DB::raw('SUM(service_order_items.total) as daily_revenue')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->keyBy('date');

        $period = CarbonPeriod::create($request->start_date, $request->end_date);
        $labels = [];
        $data = [];

        foreach ($period as $date) {
            $formattedDate = $date->format('Y-m-d');
            $labels[] = $date->format('d M');
            $data[] = $revenueData->get($formattedDate)->daily_revenue ?? 0;
        }

        return response()->json([
            'labels' => $labels,
            'data' => $data,
        ]);
    }

    public function revenueAreaChartData(Request $request, \App\Models\ServiceCategory $serviceCategory)
    {
        $this->authorize('viewAny', \App\Models\Invoice::class);

        $query = \App\Models\ServiceOrderItem::query()
            ->whereHas('service', function ($query) use ($serviceCategory) { $query->where('category_id', $serviceCategory->id); })
            ->whereHas('serviceOrder.invoice', function ($q) use ($request) {
                $q->where('status', \App\Models\Invoice::STATUS_PAID);
                if ($request->filled('start_date') && $request->filled('end_date')) {
                    $q->whereHas('payments', function ($paymentQuery) use ($request) {
                        $paymentQuery->whereBetween('payment_date', [$request->start_date, $request->end_date]);
                    });
                }
            });

        if (auth()->user()->role === 'owner' && $request->filled('area_id') && $request->area_id !== 'all') {
             $areaId = $request->area_id;
             $query->whereHas('serviceOrder.address', function ($q) use ($areaId) {
                $q->where('area_id', $areaId);
            });
        } elseif (auth()->user()->role === 'co_owner') {
            $areaId = auth()->user()->area_id;
            $query->whereHas('serviceOrder.address', function ($q) use ($areaId) {
                $q->where('area_id', $areaId);
            });
        }

        $revenueByArea = $query->join('service_orders', 'service_order_items.service_order_id', '=', 'service_orders.id')
            ->join('addresses', 'service_orders.address_id', '=', 'addresses.id')
            ->join('areas', 'addresses.area_id', '=', 'areas.id')
            ->select('areas.name as area_name', DB::raw('SUM(service_order_items.total) as total_revenue'))
            ->groupBy('areas.name')
            ->orderBy('total_revenue', 'desc')
            ->get();

        return response()->json([
            'labels' => $revenueByArea->pluck('area_name'),
            'data' => $revenueByArea->pluck('total_revenue'),
        ]);
    }

    public function revenueDrilldownTableData(Request $request, \App\Models\ServiceCategory $serviceCategory)
    {
        $this->authorize('viewAny', \App\Models\Invoice::class);

        $query = \App\Models\ServiceOrderItem::with(['serviceOrder', 'service', 'serviceOrder.customer'])
            ->whereHas('service', function ($query) use ($serviceCategory) { $query->where('category_id', $serviceCategory->id); })
            ->whereHas('serviceOrder.invoice', function ($q) use ($request) {
                $q->where('status', \App\Models\Invoice::STATUS_PAID);
                if ($request->filled('start_date') && $request->filled('end_date')) {
                    $q->whereHas('payments', function ($paymentQuery) use ($request) {
                        $paymentQuery->whereBetween('payment_date', [$request->start_date, $request->end_date]);
                    });
                }
            });

        if (auth()->user()->role === 'co_owner') {
            $areaId = auth()->user()->area_id;
            $query->whereHas('serviceOrder.address', function ($q) use ($areaId) {
                $q->where('area_id', $areaId);
            });
        } elseif ($request->filled('area_id') && $request->area_id !== 'all') {
            $areaId = $request->area_id;
            $query->whereHas('serviceOrder.address', function ($q) use ($areaId) {
                $q->where('area_id', $areaId);
            });
        }

        return DataTables::of($query)
            ->addColumn('so_number', function($item) {
                return $item->serviceOrder->so_number;
            })
            ->addColumn('customer_name', function($item) {
                return $item->serviceOrder->customer->name ?? 'N/A';
            })
            ->editColumn('work_date', function($item) {
                return Carbon::parse($item->serviceOrder->work_date)->format('d M Y');
            })
            ->addColumn('service_name', function($item) {
                return $item->service->name;
            })
            ->editColumn('total', function($item) {
                return 'Rp ' . number_format($item->total, 0, ',', '.');
            })
            ->orderColumn('work_date', function ($query, $order) {
                $query->orderBy(function($q) {
                    return $q->from('service_orders')->whereColumn('id', 'service_order_items.service_order_id')->select('work_date');
                }, $order);
            })
            ->make(true);
    }

    public function staffWorkloadChartData(Request $request, \App\Models\Staff $staff)
    {
        $this->authorize('view', $staff);

        if (empty($request->start_date) || empty($request->end_date)) {
            $request->merge([
                'start_date' => now()->subDays(30)->toDateString(),
                'end_date' => now()->toDateString(),
            ]);
        }

        $query = \App\Models\ServiceOrder::query()
            ->join('service_order_staff', 'service_orders.id', '=', 'service_order_staff.service_order_id')
            ->where('service_order_staff.staff_id', $staff->id)
            ->whereIn('status', [\App\Models\ServiceOrder::STATUS_DONE, \App\Models\ServiceOrder::STATUS_INVOICED])
            ->whereBetween('work_date', [$request->start_date, $request->end_date]);

        $workload = $query->select(
                DB::raw('DATE_TRUNC(\'week\', work_date) as week_start'),
                DB::raw('COUNT(service_orders.id) as jobs_count')
            )
            ->groupBy('week_start')
            ->orderBy('week_start', 'asc')
            ->get();

        return response()->json([
            'labels' => $workload->map(function($item) {
                return 'Minggu ' . Carbon::parse($item->week_start)->format('W');
            }),
            'data' => $workload->pluck('jobs_count'),
        ]);
    }

    public function staffSpecializationChartData(Request $request, \App\Models\Staff $staff)
    {
        $this->authorize('view', $staff);

        if (empty($request->start_date) || empty($request->end_date)) {
            $request->merge([
                'start_date' => now()->subDays(30)->toDateString(),
                'end_date' => now()->toDateString(),
            ]);
        }

        $specialization = \App\Models\ServiceOrderItem::query()
            ->whereHas('serviceOrder', function($q) use ($staff, $request) {
                $q->whereHas('staff', function($sq) use ($staff) {
                    $sq->where('staff.id', $staff->id);
                })
                ->whereIn('status', [\App\Models\ServiceOrder::STATUS_DONE, \App\Models\ServiceOrder::STATUS_INVOICED])
                ->whereBetween('work_date', [$request->start_date, $request->end_date]);
            })
            ->join('services', 'service_order_items.service_id', '=', 'services.id')
            ->join('service_categories', 'services.category_id', '=', 'service_categories.id')
            ->select('service_categories.name', DB::raw('COUNT(service_order_items.id) as items_count'))
            ->groupBy('service_categories.name')
            ->orderBy('items_count', 'desc')
            ->get();

        return response()->json([
            'labels' => $specialization->pluck('name'),
            'data' => $specialization->pluck('items_count'),
        ]);
    }

    public function staffDrilldownTableData(Request $request, \App\Models\Staff $staff)
    {
        $this->authorize('view', $staff);

        if (empty($request->start_date) || empty($request->end_date)) {
            $request->merge([
                'start_date' => now()->subDays(30)->toDateString(),
                'end_date' => now()->toDateString(),
            ]);
        }

        $query = $staff->serviceOrders()
            ->with('customer')
            ->whereIn('status', [\App\Models\ServiceOrder::STATUS_DONE, \App\Models\ServiceOrder::STATUS_INVOICED])
            ->whereBetween('work_date', [$request->start_date, $request->end_date]);

        return DataTables::of($query)
            ->addColumn('customer_name', function($so) {
                return $so->customer->name ?? 'N/A';
            })
            ->editColumn('work_date', function($so) {
                return Carbon::parse($so->work_date)->format('d M Y');
            })
            ->editColumn('status', function($so) {
                return '<span class="badge bg-success text-bg-secondary">' . ucfirst($so->status) . '</span>';
            })
            ->rawColumns(['status'])
            ->make(true);
    }

    public function customerSpendingTimelineData($customerId)
    {
        $customer = \App\Models\Customer::withTrashed()->findOrFail($customerId);
        $this->authorize('view', $customer);

        $spending = \App\Models\Invoice::query()
            ->join('service_orders', 'invoices.service_order_id', '=', 'service_orders.id')
            ->where('service_orders.customer_id', $customer->id)
            ->where('invoices.status', 'paid')
            ->select(
                DB::raw("TO_CHAR(issue_date, 'YYYY-MM') as month"),
                DB::raw('SUM(grand_total) as total_spent')
            )
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        return response()->json([
            'labels' => $spending->map(function($item) {
                return Carbon::createFromFormat('Y-m', $item->month)->format('M Y');
            }),
            'data' => $spending->pluck('total_spent'),
        ]);
    }

    public function customerKeyMetricsData($customerId)
    {
        $customer = \App\Models\Customer::withTrashed()->findOrFail($customerId);
        $this->authorize('view', $customer);

        $orders = $customer->serviceOrders()->orderBy('work_date', 'asc')->get();
        $paidInvoices = $customer->invoices()->where('invoices.status', 'paid')->get();

        // Avg days between orders
        $avgDays = 0;
        if ($orders->count() > 1) {
            $diffs = [];
            for ($i = 1; $i < $orders->count(); $i++) {
                $date1 = Carbon::parse($orders[$i-1]->work_date);
                $date2 = Carbon::parse($orders[$i]->work_date);
                $diffs[] = $date2->diffInDays($date1);
            }
            $avgDays = count($diffs) > 0 ? array_sum($diffs) / count($diffs) : 0;
        }

        // Most frequent service
        $mostFrequentService = \App\Models\ServiceOrderItem::query()
            ->whereIn('service_order_id', $orders->pluck('id'))
            ->select('service_id', DB::raw('COUNT(id) as count'))
            ->groupBy('service_id')
            ->orderBy('count', 'desc')
            ->first();

        $serviceName = 'N/A';
        if ($mostFrequentService) {
            $serviceName = \App\Models\Service::find($mostFrequentService->service_id)->name ?? 'N/A';
        }

        return response()->json([
            'total_spent' => 'Rp ' . number_format($paidInvoices->sum('grand_total'), 0, ',', '.'),
            'total_orders' => $orders->count(),
            'avg_days_between_orders' => round($avgDays, 1) . ' hari',
            'most_frequent_service' => $serviceName,
        ]);
    }

    public function customerServiceFrequencyData($customerId)
    {
        $customer = \App\Models\Customer::withTrashed()->findOrFail($customerId);
        $this->authorize('view', $customer);

        $query = \App\Models\ServiceOrderItem::query()
            ->whereIn('service_order_id', $customer->serviceOrders()->pluck('id'))
            ->join('services', 'service_order_items.service_id', '=', 'services.id')
            ->select('services.name as service_name', DB::raw('COUNT(service_order_items.id) as count'))
            ->groupBy('services.name')
            ->orderBy('count', 'desc');

        return DataTables::of($query)->make(true);
    }

    public function customerOrderHistoryData($customerId)
    {
        $customer = \App\Models\Customer::withTrashed()->findOrFail($customerId);
        $this->authorize('view', $customer);

        $query = $customer->serviceOrders()->with('address.area');

        return DataTables::of($query)
            ->editColumn('work_date', function($so) {
                return Carbon::parse($so->work_date)->format('d M Y');
            })
            ->addColumn('area', function($so) {
                return $so->address->area->name ?? 'N/A';
            })
            ->editColumn('status', function($so) {
                // Simple status badge
                return '<span class="badge">' . ucfirst($so->status) . '</span>';
            })
            ->addColumn('action', function ($so) {
                $detailUrl = route('web.service-orders.show', $so->id);
                return '<a href="' . $detailUrl . '" class="btn btn-sm btn-secondary">Detail</a>';
            })
            ->rawColumns(['status', 'action'])
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
