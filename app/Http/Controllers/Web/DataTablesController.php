<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\ServiceCategory;
use App\Models\ServiceOrder;
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

        // 2. Ambil data. Global Scope untuk co_owner akan otomatis berjalan di sini.
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

    public function machineCategories()
    {
        $this->authorize('viewAny', \App\Models\MachineCategory::class);

        $query = \App\Models\MachineCategory::withCount('machines');

        return DataTables::of($query)
            ->editColumn('is_active', function ($category) {
                if ($category->is_active) {
                    return '<span class="badge bg-success text-bg-secondary">Aktif</span>';
                }
                return '<span class="badge bg-secondary text-bg-secondary">Nonaktif</span>';
            })
            ->addColumn('action', function ($category) {
                return '
                    <button class="btn btn-sm btn-warning editMachineCategory" data-id="' . $category->id . '" data-name="' . e($category->name) . '" data-code-prefix="' . e($category->code_prefix) . '" data-sort-order="' . $category->sort_order . '" data-is-active="' . ($category->is_active ? '1' : '0') . '">Edit</button>
                    <button class="btn btn-sm btn-danger deleteMachineCategory" data-id="' . $category->id . '">Hapus</button>
                ';
            })
            ->rawColumns(['action', 'is_active'])
            ->make(true);
    }

    public function machines(Request $request)
    {
        $this->authorize('viewAny', \App\Models\Machine::class);

        $query = \App\Models\Machine::with(['category', 'area', 'pairedMachine']);

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

        return DataTables::of($query)
            ->addColumn('category_name', function ($machine) {
                return $machine->category ? $machine->category->name : 'N/A';
            })
            ->addColumn('area_name', function ($machine) {
                return $machine->area ? $machine->area->name : 'N/A';
            })
            ->editColumn('status', function ($machine) {
                $badgeClass = match ($machine->status) {
                    'active' => 'bg-success',
                    'maintenance' => 'bg-warning',
                    'retired' => 'bg-danger',
                    default => 'bg-secondary',
                };
                return '<span class="badge ' . $badgeClass . ' text-bg-secondary">' . ucfirst($machine->status) . '</span>';
            })
            ->addColumn('paired_machine', function ($machine) {
                if ($machine->pairedMachine) {
                    return '<span title="' . e($machine->pairedMachine->code . ($machine->pairedMachine->name ? ' — ' . $machine->pairedMachine->name : '')) . '">' . e($machine->pairedMachine->code) . '</span>';
                }
                return '—';
            })
            ->addColumn('notes', function ($machine) {
                if ($machine->notes) {
                    $truncated = strlen($machine->notes) > 40 ? substr($machine->notes, 0, 40) . '...' : $machine->notes;
                    return '<span title="' . e($machine->notes) . '">' . e($truncated) . '</span>';
                }
                return '—';
            })
            ->addColumn('action', function ($machine) {
                return '
                    <button class="btn btn-sm btn-warning editMachine" data-id="' . $machine->id . '" data-code="' . e($machine->code) . '" data-name="' . e($machine->name ?? '') . '" data-category-id="' . $machine->category_id . '" data-area-id="' . $machine->area_id . '" data-status="' . e($machine->status) . '" data-paired-machine-id="' . ($machine->paired_machine_id ?? '') . '" data-notes="' . e($machine->notes ?? '') . '">Edit</button>
                    <button class="btn btn-sm btn-danger deleteMachine" data-id="' . $machine->id . '">Hapus</button>
                ';
            })
            ->rawColumns(['action', 'status', 'paired_machine', 'notes'])
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
            ->editColumn('commission_rate', function ($category) {
                return number_format($category->commission_rate, 1) . '%';
            })
            ->addColumn('action', function ($category) {
                $name = htmlspecialchars($category->name, ENT_QUOTES, 'UTF-8');
                $rate = $category->commission_rate ?? 10.00;
                $editBtn = '<button class="btn btn-sm btn-success editServiceCategory" data-id="' . $category->id . '" data-name="' . $name . '" data-commission-rate="' . $rate . '">Edit</button>';
                // $deleteBtn = '<button class="btn btn-sm btn-danger deleteServiceCategory" data-id="' . $category->id . '">Hapus</button>';
                return $editBtn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function staff(Request $request)
    {
        $this->authorize('viewAny', \App\Models\Staff::class);

        $query = \App\Models\Staff::with(['area', 'user'])->select('staff.*');

        if ($request->has('show_resigned') && $request->show_resigned == 'true') {
            $query->withTrashed();
        }

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
            ->editColumn('name', function ($staff) {
                $name = $staff->name;
                if ($staff->trashed()) {
                    $name .= ' <span class="badge bg-danger ms-2">Resigned</span>';
                }
                return $name;
            })
            ->editColumn('created_at', function ($staff) {
                return \Carbon\Carbon::parse($staff->created_at)->format('d M Y');
            })
            ->orderColumn('created_at', function ($query, $order) {
                $query->orderBy('created_at', $order);
            })
            ->addColumn('action', function ($staff) {
                if ($staff->trashed()) {
                    return '<span class="text-muted small">No actions available</span>';
                }
                
                $actions = '<button class="btn btn-sm btn-info edit-button" data-id="' . $staff->id . '">Edit</button>';
                if ($staff->user) {
                    $actions .= ' <button class="btn btn-sm btn-danger resign-button" data-id="' . $staff->id . '">Resign</button>';
                }
                return $actions;
            })
            ->rawColumns(['action', 'role', 'name'])
            ->make(true);
    }

    public function services()
    {
        $this->authorize('viewAny', \App\Models\Service::class);

        $query = \App\Models\Service::with('category');

        $dataTable = DataTables::of($query)
            ->editColumn('price', function ($service) {
                return 'Rp ' . number_format($service->price, 0, ',', '.');
            });

        if (in_array(auth()->user()->role, ['owner', 'co_owner'])) {
            $dataTable->addColumn('cost', function ($service) {
                return 'Rp ' . number_format($service->cost, 0, ',', '.');
            });
        }

        $dataTable->addColumn('category_name', function ($service) {
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
            ->rawColumns(['action']);

        return $dataTable->make(true);
    }

    public function customers(Request $request)
    {
        $this->authorize('viewAny', Customer::class);

        $user = auth()->user();
        $query = Customer::query()->withCount('addresses')->with('addresses.area');



        if ($request->has('q')) {
            $query->where('name', 'like', '%' . strtoupper($request->q) . '%');
        }

        return DataTables::of($query)
            ->addColumn('last_order_date', function ($customer) {
                return $customer->last_order_date ? \Carbon\Carbon::parse($customer->last_order_date)->format('d M Y') : 'N/A';
            })
            ->orderColumn('last_order_date', function ($query, $order) {
                $query->orderBy('last_order_date', $order);
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
                $detailUrl = route('web.customers.show', $customer->id);
                $addAddressUrl = route('web.addresses.create', ['customer' => $customer->id]);

                $actions = '<a href="' . $detailUrl . '" class="btn btn-sm btn-secondary">Detail</a>';
                $actions .= ' <a href="' . $addAddressUrl . '" class="btn btn-sm btn-primary">+ Alamat</a>';
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

        $query = \App\Models\ServiceOrder::with([
            'customer' => function ($query) {
                $query->withoutGlobalScope(AreaScope::class)->withTrashed();
            },
            'address.area',
            'invoice'
        ]);

        // Apply status filter if present in the request
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if ($startDate) {
            $query->where('work_date', '>=', Carbon::parse($startDate)->format('Y-m-d'));
        }

        if ($endDate) {
            $query->where('work_date', '<=', Carbon::parse($endDate)->format('Y-m-d'));
        }

        return DataTables::of($query)
            ->order(function ($query) {
                $query->orderBy('work_date', 'desc')
                    ->orderByRaw("COALESCE(work_time, '23:59:59'::time) asc");
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
            ->addColumn('customer_phone', function ($so) {
                return $so->customer && $so->customer->phone_number
                    ? $so->customer->phone_number
                    : 'N/A';
            })
            ->editColumn('work_date', function ($so) {
                $date = $so->work_date instanceof Carbon
                    ? $so->work_date
                    : Carbon::parse($so->work_date);
                $timeLabel = $so->work_time_formatted ? $so->work_time_formatted . ' WIB' : null;

                return trim($date->format('d M Y') . ($timeLabel ? ' • ' . $timeLabel : ''));
            })
            ->orderColumn('work_date', function ($query, $order) {
                $query->orderBy('work_date', $order)
                    ->orderByRaw("COALESCE(work_time, '23:59:59'::time) asc");
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
                        $actions .= '<button class="btn btn-sm btn-danger change-status-btn" data-id="' . $so->id . '" data-new-status="cancel">Cancel</button> ';
                        break;
                    case \App\Models\ServiceOrder::STATUS_PROSES:
                        $actions .= '<button class="btn btn-sm btn-success change-status-btn" data-id="' . $so->id . '" data-new-status="' . \App\Models\ServiceOrder::STATUS_DONE . '">Done</button> ';
                        if (auth()->user()->role === 'owner') {
                            $actions .= '<button class="btn btn-sm btn-danger change-status-btn" data-id="' . $so->id . '" data-new-status="cancel">Cancel</button> ';
                        }
                        break;
                    // For CANCELLED, DONE, and INVOICED, no further status transitions are allowed from the UI
                }

                if ($so->invoice && $so->invoice->status !== \App\Models\Invoice::STATUS_CANCELLED) {
                    $actions .= '<a href="' . route('web.invoices.show', $so->invoice) . '" class="btn btn-sm btn-success">Invoice</a> ';
                } else if ($so->status === \App\Models\ServiceOrder::STATUS_DONE) {
                    // Check if all non-cancel sessions are done
                    $allSessionsDone = $so->sessions()
                        ->where('status', '!=', 'cancel')
                        ->where('status', '!=', 'done')
                        ->doesntExist();

                    if ($allSessionsDone) {
                        $actions .= '<button class="btn btn-sm btn-primary create-invoice" data-id="' . $so->id . '">Buat Invoice</button> ';
                    } else {
                        $actions .= '<button class="btn btn-sm btn-secondary" disabled title="Selesaikan semua sesi terlebih dahulu"><i class="ti ti-file-invoice"></i> Invoice</button> ';
                    }
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

        if (request()->has('status') && request()->status !== 'all') {
            $query->where('status', request()->status);
        }

        return DataTables::of($query)
            ->addColumn('so_number', function ($invoice) {
                return $invoice->serviceOrder->so_number;
            })
            ->addColumn('customer_name', function ($invoice) {
                return $invoice->serviceOrder && $invoice->serviceOrder->customer
                    ? $invoice->serviceOrder->customer->name
                    : 'N/A';
            })
            ->addColumn('customer_phone', function ($invoice) {
                return $invoice->serviceOrder && $invoice->serviceOrder->customer
                    ? $invoice->serviceOrder->customer->phone_number
                    : 'N/A';
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
            ->addColumn('balance', function ($invoice) {
                $balance = $invoice->grand_total - $invoice->paid_amount;
                return 'Rp ' . number_format($balance, 2, ',', '.');
            })
            ->editColumn('status', function ($invoice) {
                $statusBadgeClass = '';
                switch ($invoice->status) {
                    case \App\Models\Invoice::STATUS_NEW:
                        $statusBadgeClass = 'bg-primary';
                        break;
                    case \App\Models\Invoice::STATUS_SENT:
                        $statusBadgeClass = 'bg-info';
                        break;
                    case \App\Models\Invoice::STATUS_OVERDUE:
                        $statusBadgeClass = 'bg-warning';
                        break;
                    case \App\Models\Invoice::STATUS_PAID:
                        $statusBadgeClass = 'bg-success';
                        break;
                    case \App\Models\Invoice::STATUS_CANCELLED:
                        $statusBadgeClass = 'bg-secondary';
                        break;
                    default:
                        $statusBadgeClass = 'bg-dark';
                        break;
                }
                return '<span class="badge ' . $statusBadgeClass . ' text-bg-secondary">' . ucfirst($invoice->status) . '</span>';
            })
            ->addColumn('action', function ($invoice) {
                $detailUrl = route('web.invoices.show', $invoice);
                $actions = '<a href="' . $detailUrl . '" class="btn btn-sm btn-secondary">Detail</a> ';

                switch ($invoice->status) {
                    case 'new':
                        $actions .= '<button class="btn btn-sm btn-info change-status-btn" data-id="' . $invoice->id . '" data-new-status="' . \App\Models\Invoice::STATUS_SENT . '">Mark as Sent</button> ';
                        break;
                    case 'sent':
                        $balance = $invoice->grand_total - $invoice->paid_amount;
                        $actions .= '<button class="btn btn-sm btn-success change-status-btn" data-id="' . $invoice->id . '" data-balance="' . $balance . '" data-new-status="' . \App\Models\Invoice::STATUS_PAID . '">Mark as Paid</button> ';
                        break;
                    case 'overdue':
                        $balance = $invoice->grand_total - $invoice->paid_amount;
                        $actions .= '<button class="btn btn-sm btn-success change-status-btn" data-id="' . $invoice->id . '" data-balance="' . $balance . '" data-new-status="' . \App\Models\Invoice::STATUS_PAID . '">Mark as Paid</button> ';
                        break;
                }

                return $actions;
            })
            ->rawColumns(['action', 'status'])
            ->make(true);
    }

    public function payments(Request $request)
    {
        $this->authorize('viewAny', \App\Models\Payment::class);

        $query = \App\Models\Payment::query()
            ->select('payments.*')
            ->with('invoice.serviceOrder.customer');

        if ($request->filled('start_date')) {
            $query->whereDate('payment_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('payment_date', '<=', $request->end_date);
        }

        if ($request->filled('payment_method') && $request->payment_method !== 'all') {
            $query->where('payment_method', $request->payment_method);
        }

        // Calculate summary metrics on the filtered query
        $summaryQuery = $query->clone();
        $totalRevenue = $summaryQuery->sum('amount');
        $totalTransactions = $summaryQuery->count();
        $avgTransaction = $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0;

        return DataTables::of($query)
            ->addColumn('invoice_number', function ($payment) {
                return $payment->invoice ? $payment->invoice->invoice_number : 'N/A';
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
            ->with('summary', [
                'total_revenue' => 'Rp ' . number_format($totalRevenue, 0, ',', '.'),
                'total_transactions' => number_format($totalTransactions, 0, ',', '.'),
                'avg_transaction' => 'Rp ' . number_format($avgTransaction, 0, ',', '.'),
            ])
            ->rawColumns(['action'])
            ->make(true);
    }

    public function revenueReportData(Request $request)
    {
        $this->authorize('viewAny', \App\Models\Invoice::class);

        $query = \App\Models\ServiceCategory::query()
            ->withCount([
                'serviceOrderItems as total_orders' => function ($query) use ($request) {
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
                }
            ])
            ->withSum([
                'serviceOrderItems as total_revenue' => function ($query) use ($request) {
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
                }
            ], 'total');

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

        // Calculate Expenses
        $expenseQuery = \App\Models\Expense::query();

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $expenseQuery->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        if (auth()->user()->role === 'co_owner') {
            $areaId = auth()->user()->area_id;
            $expenseQuery->whereHas('user', function ($q) use ($areaId) {
                $q->where('area_id', $areaId);
            });
        } elseif ($request->filled('area_id') && $request->area_id !== 'all') {
            $areaId = $request->area_id;
            $expenseQuery->whereHas('user', function ($q) use ($areaId) {
                $q->where('area_id', $areaId);
            });
        }

        $totalExpenses = $expenseQuery->sum('amount');
        $netProfit = $totalRevenue - $totalExpenses;

        return $dataTable->with('summary', [

            'total_revenue' => 'Rp ' . number_format($totalRevenue, 0, ',', '.'),
            'total_orders' => number_format($totalOrders, 0, ',', '.'),
            'avg_revenue' => 'Rp ' . number_format($avgRevenue, 0, ',', '.'),
            'total_expenses' => 'Rp ' . number_format($totalExpenses, 0, ',', '.'),
            'net_profit' => 'Rp ' . number_format($netProfit, 0, ',', '.'),
        ])->make(true);
    }

    public function expenseReportData(Request $request)
    {
        // Permission check
        if (!in_array(auth()->user()->role, ['owner', 'admin', 'co_owner'])) {
             abort(403);
        }

        $query = \App\Models\Expense::with(['category', 'user']);

        // Date Filter
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        // Area Filter
        if (auth()->user()->role === 'co_owner') {
             $areaId = auth()->user()->area_id;
             $query->whereHas('user', function ($q) use ($areaId) {
                 $q->where('area_id', $areaId);
             });
        } elseif ($request->filled('area_id') && $request->area_id !== 'all') {
             $areaId = $request->area_id;
             $query->whereHas('user', function ($q) use ($areaId) {
                 $q->where('area_id', $areaId);
             });
        }

        // Category Filter
        if ($request->filled('category_id') && $request->category_id !== 'all') {
            $query->where('category_id', $request->category_id);
        }

        // Calculate Summary
        $summaryQuery = $query->clone();
        $totalExpenses = $summaryQuery->sum('amount');
        $expenseCount = $summaryQuery->count();
        
        // Initializing most expensive category as 'N/A'
        $mostExpensiveCategory = 'N/A';
        
        // Only run the grouping query if there are expenses
        if ($expenseCount > 0) {
             $topCategory = \App\Models\Expense::select('category_id', DB::raw('SUM(amount) as total'))
                ->whereIn('id', $summaryQuery->pluck('id')) // Filter by the same scope
                ->groupBy('category_id')
                ->orderByDesc('total')
                ->first();
             
             if ($topCategory && $topCategory->category) {
                 $mostExpensiveCategory = $topCategory->category->name;
             }
        }

        return DataTables::of($query)
            ->editColumn('date', function ($expense) {
                return $expense->date->format('d M Y');
            })
            ->editColumn('amount', function ($expense) {
                return 'Rp ' . number_format($expense->amount, 0, ',', '.');
            })
            ->addColumn('category_name', function ($expense) {
                return $expense->category->name;
            })
            ->addColumn('user_name', function ($expense) {
                return $expense->user->name;
            })
            ->with('summary', [
                'total_expenses' => 'Rp ' . number_format($totalExpenses, 0, ',', '.'),
                'expense_count' => number_format($expenseCount, 0, ',', '.'),
                'most_expensive_category' => $mostExpensiveCategory,
            ])
            ->make(true);
    }

    public function staffPerformanceReportData(Request $request)
    {
        $this->authorize('viewAny', \App\Models\Staff::class);

        $query = \App\Models\Staff::with('user', 'area')
            ->select('staff.*')
            ->whereHas('user', function ($userQuery) {
                $userQuery->where('role', 'staff');
            });

        if (auth()->user()->role === 'co_owner') {
            $query->where('area_id', auth()->user()->area_id);
        } elseif ($request->filled('area_id') && $request->area_id !== 'all') {
            $query->where('area_id', $request->area_id);
        }

        if ($request->filled('staff_id') && $request->staff_id !== 'all') {
            $query->where('id', $request->staff_id);
        }

        $dataTable = DataTables::of($query)
            ->addColumn('name', function ($staff) {
                return $staff->name;
            })
            ->addColumn('area_name', function ($staff) {
                return $staff->area->name ?? 'N/A';
            })
            ->addColumn('jobs_completed', function ($staff) use ($request) {
                $jobsQuery = \App\Models\Invoice::where('status', \App\Models\Invoice::STATUS_PAID)
                    ->whereHas('serviceOrder', function ($soQuery) use ($staff) {
                        $soQuery->whereHas('sessions.staff', function ($staffQuery) use ($staff) {
                            $staffQuery->where('staff.id', $staff->id);
                        });
                    });

                if ($request->filled('start_date') && $request->filled('end_date')) {
                    $jobsQuery->whereHas('payments', function ($paymentQuery) use ($request) {
                        $paymentQuery->whereBetween('payment_date', [$request->start_date, $request->end_date]);
                    });
                } else {
                    // Default to current month if no date range is provided
                    $jobsQuery->whereHas('payments', function ($paymentQuery) {
                        $paymentQuery->whereMonth('payment_date', now()->month)->whereYear('payment_date', now()->year);
                    });
                }

                return $jobsQuery->count();
            })
            ->addColumn('total_revenue', function ($staff) use ($request) {
                $revenueQuery = \App\Models\Invoice::where('status', \App\Models\Invoice::STATUS_PAID)
                    ->whereHas('serviceOrder', function ($soQuery) use ($staff) {
                        $soQuery->where('status', \App\Models\ServiceOrder::STATUS_INVOICED)
                            ->whereHas('sessions.staff', function ($staffQuery) use ($staff) {
                                $staffQuery->where('staff.id', $staff->id);
                            });
                    });

                if ($request->filled('start_date') && $request->filled('end_date')) {
                    $revenueQuery->whereHas('payments', function ($paymentQuery) use ($request) {
                        $paymentQuery->whereBetween('payment_date', [$request->start_date, $request->end_date]);
                    });
                } else {
                    // Default to current month if no date range is provided
                    $revenueQuery->whereHas('payments', function ($paymentQuery) {
                        $paymentQuery->whereMonth('payment_date', now()->month)->whereYear('payment_date', now()->year);
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
        if ($user->role == 'co_owner') {
            $subQuery->whereHas('addresses', function ($q) use ($user) {
                $q->where('area_id', $user->area_id);
            });
        } elseif ($request->filled('area_id') && $request->area_id !== 'all') {
            $areaId = $request->area_id;
            $subQuery->whereHas('addresses', function ($q) use ($areaId) {
                $q->where('area_id', $areaId);
            });
        }

        $subQuery = $subQuery->withSum([
            'invoices as total_revenue' => function ($q) use ($request) {
                $q->where('invoices.status', \App\Models\Invoice::STATUS_PAID)
                    ->whereHas('serviceOrder', function ($so) {
                        $so->where('status', \App\Models\ServiceOrder::STATUS_INVOICED);
                    });
                if ($request->filled('start_date') && $request->filled('end_date')) {
                    $q->whereHas('payments', function ($paymentQuery) use ($request) {
                        $paymentQuery->whereBetween('payment_date', [$request->start_date, $request->end_date]);
                    });
                }
            }
        ], 'grand_total')
            ->withCount([
                'serviceOrders as total_orders' => function ($q) use ($request) {
                    $q->where('status', '!=', 'cancel');
                    if ($request->filled('start_date') && $request->filled('end_date')) {
                        $q->whereBetween('work_date', [$request->start_date, $request->end_date]);
                    }
                }
            ])
            ->withSum([
                'invoices as total_invoice_overdue' => function ($q) use ($request) {
                    $q->where('invoices.status', \App\Models\Invoice::STATUS_OVERDUE);
                    if ($request->filled('start_date') && $request->filled('end_date')) {
                        $q->whereBetween('due_date', [$request->start_date, $request->end_date]);
                    }
                }
            ], 'grand_total')
            ->withSum([
                'invoices as total_invoice_unpaid' => function ($q) use ($request) {
                    $q->whereIn('invoices.status', ['new', 'sent']);
                    if ($request->filled('start_date') && $request->filled('end_date')) {
                        $q->whereBetween('issue_date', [$request->start_date, $request->end_date]);
                    }
                }
            ], 'grand_total')
            ->addSelect(DB::raw('(SELECT SUM(total) FROM service_order_items WHERE service_order_id IN (SELECT id FROM service_orders WHERE customer_id = customers.id AND status = \'cancelled\')) as total_cancelled_revenue_potential'));

        $potentialRevenueSubquery = \App\Models\ServiceOrderItem::selectRaw('sum(total)')
            ->join('service_orders', 'service_orders.id', '=', 'service_order_items.service_order_id')
            ->whereColumn('service_orders.customer_id', 'customers.id')
            ->whereIn('service_orders.status', ['booked', 'proses', 'done']);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $potentialRevenueSubquery->whereBetween('service_orders.work_date', [$request->start_date, $request->end_date]);
        }
        $subQuery->addSelect(['potential_revenue' => $potentialRevenueSubquery]);


        // 2. Create the main query from the subquery, which allows WHERE on aliases
        $query = \App\Models\Customer::fromSub($subQuery, 'customers');

        return DataTables::of($query)
            ->addColumn('name', function ($customer) {
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
            ->addColumn('total_cancelled_revenue_potential', function ($customer) {
                return 'Rp ' . number_format($customer->total_cancelled_revenue_potential ?? 0, 0, ',', '.');
            })
            ->addColumn('total_invoice_overdue', function ($customer) {
                return 'Rp ' . number_format($customer->total_invoice_overdue ?? 0, 0, ',', '.');
            })
            ->addColumn('total_invoice_unpaid', function ($customer) {
                return 'Rp ' . number_format($customer->total_invoice_unpaid ?? 0, 0, ',', '.');
            })
            ->addColumn('potential_revenue', function ($customer) {
                return 'Rp ' . number_format($customer->potential_revenue ?? 0, 0, ',', '.');
            })
            ->orderColumn('total_revenue', function ($query, $order) {
                $query->orderBy('total_revenue', $order);
            })
            ->orderColumn('total_orders', function ($query, $order) {
                $query->orderBy('total_orders', $order);
            })
            ->orderColumn('total_cancelled_revenue_potential', function ($query, $order) {
                $query->orderBy('total_cancelled_revenue_potential', $order);
            })
            ->orderColumn('total_invoice_overdue', function ($query, $order) {
                $query->orderBy('total_invoice_overdue', $order);
            })
            ->orderColumn('total_invoice_unpaid', function ($query, $order) {
                $query->orderBy('total_invoice_unpaid', $order);
            })
            ->orderColumn('potential_revenue', function ($query, $order) {
                $query->orderBy('potential_revenue', $order);
            })
            ->rawColumns(['name'])
            ->make(true);
    }

    public function revenueTrendChartData(Request $request, \App\Models\ServiceCategory $serviceCategory)
    {
        $this->authorize('viewAny', \App\Models\Invoice::class);

        $query = \App\Models\ServiceOrderItem::query()
            ->whereHas('service', function ($query) use ($serviceCategory) {
                $query->where('category_id', $serviceCategory->id);
            })
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
            ->whereHas('service', function ($query) use ($serviceCategory) {
                $query->where('category_id', $serviceCategory->id);
            })
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
            ->whereHas('service', function ($query) use ($serviceCategory) {
                $query->where('category_id', $serviceCategory->id);
            })
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
            ->addColumn('so_number', function ($item) {
                return $item->serviceOrder->so_number;
            })
            ->addColumn('customer_name', function ($item) {
                return $item->serviceOrder->customer->name ?? 'N/A';
            })
            ->editColumn('work_date', function ($item) {
                return Carbon::parse($item->serviceOrder->work_date)->format('d M Y');
            })
            ->addColumn('service_name', function ($item) {
                return $item->service->name;
            })
            ->editColumn('total', function ($item) {
                return 'Rp ' . number_format($item->total, 0, ',', '.');
            })
            ->orderColumn('work_date', function ($query, $order) {
                $query->orderBy(function ($q) {
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
            ->withoutGlobalScope(\App\Models\Scopes\AreaScope::class)
            ->join('order_sessions', 'order_sessions.service_order_id', '=', 'service_orders.id')
            ->join('order_session_staff', 'order_session_staff.order_session_id', '=', 'order_sessions.id')
            ->where('order_session_staff.staff_id', $staff->id)
            ->whereIn('service_orders.status', [\App\Models\ServiceOrder::STATUS_DONE, \App\Models\ServiceOrder::STATUS_INVOICED])
            ->whereBetween('order_sessions.tanggal', [$request->start_date, $request->end_date]);

        if ($request->filled('area_id') && $request->area_id !== 'all') {
            $query->whereHas('address', function ($q) use ($request) {
                $q->where('area_id', $request->area_id);
            });
        }

        $workload = $query->select(
            DB::raw('DATE_TRUNC(\'week\', order_sessions.tanggal) as week_start'),
            DB::raw('COUNT(DISTINCT service_orders.id) as jobs_count')
        )
            ->groupBy('week_start')
            ->orderBy('week_start', 'asc')
            ->get();

        return response()->json([
            'labels' => $workload->map(function ($item) {
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
            ->withoutGlobalScope(\App\Models\Scopes\AreaScope::class)
            ->whereHas('serviceOrder', function ($q) use ($staff, $request) {
                $q->whereHas('sessions.staff', function ($sq) use ($staff) {
                    $sq->where('staff.id', $staff->id);
                })
                    ->whereIn('status', [\App\Models\ServiceOrder::STATUS_DONE, \App\Models\ServiceOrder::STATUS_INVOICED])
                    ->whereBetween('work_date', [$request->start_date, $request->end_date]);

                if ($request->filled('area_id') && $request->area_id !== 'all') {
                    $q->whereHas('address', function ($a) use ($request) {
                        $a->where('area_id', $request->area_id);
                    });
                }
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

        // Pre-load all machine attendances for this staff in the date range.
        $machineAttendances = \App\Models\MachineAttendance::where('staff_id', $staff->id)
            ->whereBetween('date', [$request->start_date, $request->end_date])
            ->with('machines:id,code')
            ->get()
            ->keyBy(fn($ma) => \Carbon\Carbon::parse($ma->date)->format('Y-m-d'));

        // Pre-load staff attendances for this staff in the date range.
        $hadirrNik = $staff->hadirr_nik;
        $staffAttendances = $hadirrNik
            ? \App\Models\StaffAttendance::where('nik', $hadirrNik)
                ->whereBetween('tanggal', [$request->start_date, $request->end_date])
                ->get()
                ->keyBy(fn($sa) => \Carbon\Carbon::parse($sa->tanggal)->format('Y-m-d'))
            : collect();

        $query = \App\Models\ServiceOrder::query()
            ->select(
                'service_orders.*',
                'order_sessions.tanggal',
                'order_sessions.started_at',
                'order_sessions.completed_at',
                'order_sessions.notes as session_notes',
                'order_sessions.id as session_id'
            )
            ->withoutGlobalScope(\App\Models\Scopes\AreaScope::class)
            ->join('order_sessions', 'order_sessions.service_order_id', '=', 'service_orders.id')
            ->join('order_session_staff', 'order_session_staff.order_session_id', '=', 'order_sessions.id')
            ->where('order_session_staff.staff_id', $staff->id)
            ->with(['customer', 'invoice', 'address'])
            ->where('service_orders.status', \App\Models\ServiceOrder::STATUS_INVOICED)
            ->whereBetween('order_sessions.tanggal', [$request->start_date, $request->end_date])
            ->orderBy('order_sessions.tanggal', 'asc');

        if ($request->filled('area_id') && $request->area_id !== 'all') {
            $query->whereHas('address', function ($q) use ($request) {
                $q->where('area_id', $request->area_id);
            });
        }

        // Pre-load proofs keyed by service_order_id
        $soIds = (clone $query)->pluck('service_orders.id');
        $proofsMap = \App\Models\WorkPhoto::whereIn('service_order_id', $soIds)
            ->get()
            ->groupBy('service_order_id');

        return DataTables::of($query)
            ->addColumn('so_id', fn($so) => $so->id)
            ->addColumn('so_number', fn($so) => $so->so_number)
            ->addColumn('customer_name', fn($so) => $so->customer->name ?? 'N/A')
            ->addColumn('invoice_id', fn($so) => $so->invoice->id ?? null)
            ->addColumn('invoice_number', fn($so) => $so->invoice->invoice_number ?? 'N/A')
            ->addColumn('invoice_show_url', fn($so) => $so->invoice ? route('web.invoices.show', $so->invoice) : null)
            ->addColumn('invoice_status_plain', function ($so) {
                return $so->invoice->status ?? null;
            })
            ->addColumn('invoice_status', function ($so) {
                $status = $so->invoice->status ?? null;
                if (!$status) return '—';
                $map = [
                    'paid'      => ['bg-success-lt',  'Paid'],
                    'sent'      => ['bg-info-lt',     'Sent'],
                    'overdue'   => ['bg-warning-lt',  'Overdue'],
                    'new'       => ['bg-primary-lt',  'New'],
                    'cancelled' => ['bg-secondary-lt','Cancelled'],
                ];
                [$cls, $label] = $map[$status] ?? ['bg-secondary-lt', ucfirst($status)];
                return '<span class="badge ' . $cls . '">' . $label . '</span>';
            })
            ->addColumn('invoice_total', function ($so) {
                if (!$so->invoice) return '—';
                return 'Rp ' . number_format($so->invoice->grand_total, 0, ',', '.');
            })

            // ── Alamat + Maps link ──
            ->addColumn('alamat_maps', function ($so) {
                $address = $so->address;
                if (!$address) return '—';

                $fullAddress     = $address->full_address ?? 'N/A';
                $fullAddressEsc  = e($fullAddress);
                $shortAddress    = e(\Illuminate\Support\Str::limit($fullAddress, 50));

                $mapsUrl = !empty($address->google_maps_link)
                    ? e($address->google_maps_link)
                    : 'https://maps.google.com/?q=' . urlencode($address->full_address ?? '');

                return '<div style="font-size:11px;line-height:1.4;cursor:pointer;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:100%" '
                     . 'data-bs-toggle="popover" data-bs-trigger="click" data-bs-placement="bottom" '
                     . 'data-bs-content="' . $fullAddressEsc . '" data-full-address="' . $fullAddressEsc . '" '
                     . 'title="' . $fullAddressEsc . '">'
                     . $shortAddress . '</div>'
                     . '<a href="' . $mapsUrl . '" target="_blank" rel="noopener" '
                     . 'style="font-size:11px;color:#185FA5;display:inline-flex;align-items:center;gap:3px;margin-top:3px">'
                     . '<i class="ti ti-map-pin" style="font-size:12px"></i> Buka Maps</a>';
            })

            // ── Staff attendance (clock in / clock out stacked) ──
            ->addColumn('staff_attendance', function ($so) use ($staffAttendances) {
                $date = \Carbon\Carbon::parse($so->tanggal)->format('Y-m-d');
                $sa   = $staffAttendances->get($date);

                $clockIn  = $sa && $sa->clock_in
                    ? '<span style="font-size:11px;font-weight:500;color:#2F855A">' . \Carbon\Carbon::parse($sa->clock_in)->format('H:i') . '</span>'
                    : '<span style="font-size:11px;color:#a0aec0">—</span>';

                $clockOut = $sa && $sa->clock_out
                    ? '<span style="font-size:11px;font-weight:500;color:#C53030">' . \Carbon\Carbon::parse($sa->clock_out)->format('H:i') . '</span>'
                    : '<span style="font-size:11px;color:#a0aec0">—</span>';

                return '<div style="line-height:1.5;font-size:11px">'
                     . '<div>IN&nbsp; ' . $clockIn . '</div>'
                     . '<div>OUT ' . $clockOut . '</div>'
                     . '</div>';
            })

            // ── Mesin (stacked: code/status, pergi, pulang, notes) ──
            ->addColumn('mesin', function ($so) use ($machineAttendances) {
                $date = \Carbon\Carbon::parse($so->tanggal)->format('Y-m-d');
                $ma   = $machineAttendances->get($date);

                if (!$ma || $ma->machines->isEmpty()) {
                    return '<span style="color:var(--tblr-secondary);font-style:italic;font-size:11px">—</span>';
                }

                $html = '<div style="line-height:1.5;font-size:11px;min-width:120px">';

                // Machine codes / status
                $badges = $ma->machines
                    ->map(fn($m) => '<span class="badge bg-secondary-lt me-1 mb-1" style="font-size:10px">'
                        . e($m->code) . '</span>')
                    ->implode('');
                $html .= '<div style="margin-bottom:3px">' . $badges . '</div>';

                // Pergi / Pulang times stacked
                $pergiTime = $ma->photo_pergi_at
                    ? \Carbon\Carbon::parse($ma->photo_pergi_at)->format('H:i')
                    : '—';
                $pulangTime = $ma->photo_pulang_at
                    ? \Carbon\Carbon::parse($ma->photo_pulang_at)->format('H:i')
                    : null;

                $html .= '<div style="display:flex;gap:8px">';
                $html .= '<div style="flex:1">';
                $html .= '<div style="color:#a0aec0;font-size:9px;text-transform:uppercase">Pergi</div>';
                $html .= '<span style="font-size:12px;font-weight:500">' . $pergiTime . '</span>';
                $html .= '</div>';
                $html .= '<div style="flex:1">';
                $html .= '<div style="color:#a0aec0;font-size:9px;text-transform:uppercase">Pulang</div>';
                if ($pulangTime) {
                    $html .= '<span style="font-size:12px;font-weight:500">' . $pulangTime . '</span>';
                } else {
                    $html .= '<span style="color:#BA7517;font-size:10px"><i class="ti ti-alert-triangle" style="font-size:10px"></i> Blm pulang</span>';
                }
                $html .= '</div>';
                $html .= '</div>';

                // Machine notes stacked
                if ($ma->catatan || $ma->catatan_pulang) {
                    $html .= '<div style="border-top:1px solid #e9ecef;margin-top:4px;padding-top:3px">';
                    if ($ma->catatan) {
                        $truncated = \Illuminate\Support\Str::limit($ma->catatan, 40);
                        $html .= '<div style="color:#a0aec0;font-size:9px;text-transform:uppercase">Notes Pergi</div>';
                        $html .= '<div style="font-size:10px;line-height:1.3" title="' . e($ma->catatan) . '">' . e($truncated) . '</div>';
                    }
                    if ($ma->catatan_pulang) {
                        $truncated = \Illuminate\Support\Str::limit($ma->catatan_pulang, 40);
                        $html .= '<div style="color:#a0aec0;font-size:9px;text-transform:uppercase;margin-top:2px">Notes Pulang</div>';
                        $html .= '<div style="font-size:10px;line-height:1.3" title="' . e($ma->catatan_pulang) . '">' . e($truncated) . '</div>';
                    }
                    $html .= '</div>';
                }

                $html .= '</div>';
                return $html;
            })

            // ── Foto thumbnails ──
            ->addColumn('foto', function ($so) use ($proofsMap) {
                $proofs = $proofsMap->get($so->id, collect());
                if ($proofs->isEmpty()) {
                    return '<div style="width:32px;height:32px;border-radius:4px;border:0.5px dashed #adb5bd;'
                         . 'display:flex;align-items:center;justify-content:center">'
                         . '<i class="ti ti-camera-off" style="font-size:13px;color:#adb5bd"></i></div>'
                         . '<div style="font-size:10px;color:#adb5bd;margin-top:2px">Belum ada</div>';
                }
                $typeColors = [
                    'arrival'   => ['bg' => '#EEEDFE', 'border' => '#AFA9EC', 'color' => '#3C3489', 'label' => 'ARR'],
                    'before'    => ['bg' => '#C0DD97', 'border' => '#97C459', 'color' => '#3B6D11', 'label' => 'BFR'],
                    'after'     => ['bg' => '#9FE1CB', 'border' => '#5DCAA5', 'color' => '#085041', 'label' => 'AFT'],
                    'signature' => ['bg' => '#B5D4F4', 'border' => '#85B7EB', 'color' => '#0C447C', 'label' => 'SIG'],
                ];
                $html = '<div style="display:flex;gap:3px;flex-wrap:wrap">';
                foreach ($proofs as $proof) {
                    $url   = asset('storage/' . $proof->file_path);
                    $style = $typeColors[$proof->type] ?? ['bg' => '#e9ecef', 'border' => '#ced4da', 'color' => '#495057', 'label' => strtoupper(substr($proof->type, 0, 3))];
                    $html .= '<a href="' . $url . '" target="_blank" title="' . e($proof->type) . '">'
                           . '<img src="' . $url . '" '
                           . 'style="width:32px;height:32px;object-fit:cover;border-radius:4px;border:0.5px solid ' . $style['border'] . ';display:block" '
                           . 'onerror="this.outerHTML=\'<div style=&quot;width:32px;height:32px;border-radius:4px;background:' . $style['bg'] . ';border:0.5px solid ' . $style['border'] . ';display:flex;align-items:center;justify-content:center;font-size:9px;font-weight:500;color:' . $style['color'] . '&quot;>' . $style['label'] . '</div>\'">'
                           . '</a>';
                }
                $html .= '</div>';
                $html .= '<div style="font-size:10px;color:#6c757d;margin-top:2px">' . $proofs->count() . ' foto</div>';
                return $html;
            })

            // ── Staff notes ──
            ->addColumn('staff_notes', function ($so) {
                if (empty($so->staff_notes)) {
                    return '<span style="color:var(--tblr-secondary);font-style:italic;font-size:11px">—</span>';
                }
                return '<div style="font-size:11px;line-height:1.4;max-width:140px" title="' . e($so->staff_notes) . '">'
                     . e(\Illuminate\Support\Str::limit($so->staff_notes, 60))
                     . '</div>';
            })

            ->editColumn('tanggal', fn($so) => \Carbon\Carbon::parse($so->tanggal)->format('d M Y'))
            ->editColumn('status',  fn($so) => ucfirst($so->status))
            ->orderColumn('tanggal', fn($q, $order) => $q->orderBy('order_sessions.tanggal', $order))
            ->rawColumns(['invoice_status', 'invoice_total', 'alamat_maps', 'staff_attendance',
                          'mesin', 'foto', 'staff_notes'])
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
            'labels' => $spending->map(function ($item) {
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
                $date1 = Carbon::parse($orders[$i - 1]->work_date);
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
            ->editColumn('work_date', function ($so) {
                return Carbon::parse($so->work_date)->format('d M Y');
            })
            ->addColumn('area', function ($so) {
                return $so->address->area->name ?? 'N/A';
            })
            ->editColumn('status', function ($so) {
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

    public function profitabilityServiceData(Request $request)
    {
        $this->authorize('viewAny', \App\Models\Invoice::class);

        $query = \App\Models\Service::query()->select('services.*');

        $itemsSubQuery = \App\Models\ServiceOrderItem::query()
            ->select('service_id', DB::raw('SUM(service_order_items.quantity) as total_items'), DB::raw('SUM(service_order_items.total) as total_revenue'))
            ->whereHas('serviceOrder.invoice', function ($invoiceQuery) use ($request) {
                $invoiceQuery->where('status', \App\Models\Invoice::STATUS_PAID);
                if ($request->filled('start_date') && $request->filled('end_date')) {
                    $invoiceQuery->whereHas('payments', function ($paymentQuery) use ($request) {
                        $paymentQuery->whereBetween('payment_date', [$request->start_date, $request->end_date]);
                    });
                }
            });

        if (auth()->user()->role === 'co_owner' || ($request->filled('area_id') && $request->area_id !== 'all')) {
            $areaId = auth()->user()->role === 'co_owner' ? auth()->user()->area_id : $request->area_id;
            $itemsSubQuery->whereHas('serviceOrder.address', function ($a) use ($areaId) {
                $a->where('area_id', $areaId);
            });
        }
        $itemsSubQuery->groupBy('service_id');

        $query->joinSub($itemsSubQuery, 'items_summary', function ($join) {
            $join->on('services.id', '=', 'items_summary.service_id');
        })->select('services.*', 'items_summary.total_items', 'items_summary.total_revenue');

        return DataTables::of($query)
            ->addColumn('total_cost', function ($service) {
                return $service->cost * $service->total_items;
            })
            ->addColumn('total_profit', function ($service) {
                $revenue = $service->total_revenue ?? 0;
                $cost = $service->cost * $service->total_items;
                return $revenue - $cost;
            })
            ->editColumn('total_revenue', function ($service) {
                return 'Rp ' . number_format($service->total_revenue ?? 0, 0, ',', '.');
            })
            ->editColumn('total_cost', function ($service) {
                return 'Rp ' . number_format($service->cost * $service->total_items, 0, ',', '.');
            })
            ->editColumn('total_profit', function ($service) {
                $profit = ($service->total_revenue ?? 0) - ($service->cost * $service->total_items);
                return 'Rp ' . number_format($profit, 0, ',', '.');
            })
            ->orderColumn('total_profit', function ($query, $order) {
                $query->orderByRaw('(total_revenue - (cost * total_items)) ' . $order);
            })
            ->make(true);
    }

    public function profitabilityAreaData(Request $request)
    {
        $this->authorize('viewAny', \App\Models\Invoice::class);

        $query = \App\Models\Area::query();

        if (auth()->user()->role === 'co_owner') {
            $query->where('id', auth()->user()->area_id);
        } elseif ($request->filled('area_id') && $request->area_id !== 'all') {
            $query->where('id', $request->area_id);
        }

        $areas = $query->get();

        $data = $areas->map(function ($area) use ($request) {
            $itemsQuery = \App\Models\ServiceOrderItem::whereHas('serviceOrder.address', function ($q) use ($area) {
                $q->where('area_id', $area->id);
            })->whereHas('serviceOrder.invoice', function ($invoiceQuery) use ($request) {
                $invoiceQuery->where('status', \App\Models\Invoice::STATUS_PAID);
                if ($request->filled('start_date') && $request->filled('end_date')) {
                    $invoiceQuery->whereHas('payments', function ($paymentQuery) use ($request) {
                        $paymentQuery->whereBetween('payment_date', [$request->start_date, $request->end_date]);
                    });
                }
            });

            $totalProfit = $itemsQuery
                ->join('services', 'service_order_items.service_id', '=', 'services.id')
                ->sum(DB::raw('service_order_items.total - (services.cost * service_order_items.quantity)'));

            return [
                'name' => $area->name,
                'total_profit' => (float) ($totalProfit ?? 0)
            ];
        });

        return response()->json($data);
    }

    public function staffUtilizationReportData(Request $request)
    {
        $this->authorize('viewAny', \App\Models\Staff::class);

        $query = \App\Models\Staff::with([
            'user',
            'serviceOrders' => function ($query) use ($request) {
                if ($request->filled('start_date') && $request->filled('end_date')) {
                    $query->whereBetween('work_date', [$request->start_date, $request->end_date]);
                }
                $query->with('workPhotos'); // Eager load workPhotos for each service order
            }
        ])->select('staff.*');

        if (auth()->user()->role === 'co_owner') {
            $query->where('area_id', auth()->user()->area_id);
        } elseif ($request->filled('area_id') && $request->area_id !== 'all') {
            $query->where('area_id', $request->area_id);
        }

        return DataTables::of($query)
            ->addColumn('name', function ($staff) {
                return $staff->user->name ?? $staff->name;
            })
            ->addColumn('total_hours_worked', function ($staff) {
                $totalDuration = 0;

                $staff->serviceOrders->each(function ($order) use (&$totalDuration) {
                    $startTime = $order->workPhotos->where('type', 'arrival')->min('created_at');
                    $endTime = $order->work_proof_completed_at;

                    // Fallback for endTime if work_proof_completed_at is null but order is done/invoiced
                    if (!$endTime && in_array($order->status, [ServiceOrder::STATUS_DONE, ServiceOrder::STATUS_INVOICED])) {
                        $endTime = $order->updated_at;
                    }

                    if ($startTime && $endTime) {
                        $duration = strtotime($endTime) - strtotime($startTime);
                        $totalDuration += $duration;
                    }
                });

                return round($totalDuration / 3600, 2);
            })
            ->addColumn('utilization_rate', function ($staff) {
                $totalDuration = 0;

                $staff->serviceOrders->each(function ($order) use (&$totalDuration) {
                    $startTime = $order->workPhotos->where('type', 'arrival')->min('created_at');
                    $endTime = $order->work_proof_completed_at;

                    // Fallback for endTime if work_proof_completed_at is null but order is done/invoiced
                    if (!$endTime && in_array($order->status, [ServiceOrder::STATUS_DONE, ServiceOrder::STATUS_INVOICED])) {
                        $endTime = $order->updated_at;
                    }

                    if ($startTime && $endTime) {
                        $duration = strtotime($endTime) - strtotime($startTime);
                        $totalDuration += $duration;
                    }
                });

                $totalHoursWorked = $totalDuration / 3600;
                // Assuming a 40-hour work week for utilization calculation.
                // This could be made more dynamic in the future.
                $utilizationRate = (40 > 0) ? ($totalHoursWorked / 40) * 100 : 0;
                return round($utilizationRate, 2) . '%';
            })
            ->make(true);
    }

    public function reportMachineAttendances(Request $request)
    {
        $this->authorize('viewAny', \App\Models\MachineAttendance::class);

        $query = \App\Models\MachineAttendance::with(['staff.area', 'machines.category'])
            ->select('machine_attendances.*');

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }
        if ($request->filled('staff_id')) {
            $query->where('staff_id', $request->staff_id);
        }
        if ($request->filled('area_id')) {
            $query->whereHas('staff', function ($q) use ($request) {
                $q->where('area_id', $request->area_id);
            });
        }
        if ($request->filled('category_id')) {
            $query->whereHas('machines', function ($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }
        if ($request->filled('status')) {
            if ($request->status === 'open') {
                $query->whereNull('photo_pulang_at');
            } elseif ($request->status === 'closed') {
                $query->whereNotNull('photo_pulang_at')->whereNotNull('photo_pulang');
            } elseif ($request->status === 'force_closed') {
                $query->whereNotNull('photo_pulang_at')->whereNull('photo_pulang');
            }
        }

        return \Yajra\DataTables\DataTables::of($query)
            ->editColumn('date', function ($att) {
                $date = $att->date instanceof Carbon ? $att->date : Carbon::parse($att->date);
                return $date->format('l, d/m/Y');
            })
            ->addColumn('staff_name', function ($att) {
                return $att->staff ? $att->staff->name : 'N/A';
            })
            ->addColumn('area', function ($att) {
                return $att->staff && $att->staff->area ? $att->staff->area->name : '—';
            })
            ->addColumn('machines', function ($att) {
                return $att->machines->pluck('code')->join(', ') ?: '—';
            })
            ->addColumn('categories', function ($att) {
                return $att->machines->pluck('category.name')->unique()->join(', ') ?: '—';
            })
            ->addColumn('jam_pergi', function ($att) {
                if ($att->photo_pergi_at && $att->photo_pergi) {
                    $url = \Illuminate\Support\Facades\Storage::url($att->photo_pergi);
                    $time = $att->photo_pergi_at->format('H:i');
                    return '<div>'
                        . '<span>' . $time . '</span>'
                        . '<img src="' . $url . '" class="rounded ms-1 photo-thumb" '
                        . 'data-full="' . $url . '" '
                        . 'style="width:30px; height:30px; object-fit:cover; cursor:pointer;" '
                        . 'title="Klik untuk lihat foto">'
                        . '</div>';
                }
                return $att->photo_pergi_at ? $att->photo_pergi_at->format('H:i') : '—';
            })
            ->addColumn('jam_pulang', function ($att) {
                if ($att->photo_pulang_at && $att->photo_pulang) {
                    $url = \Illuminate\Support\Facades\Storage::url($att->photo_pulang);
                    $time = $att->photo_pulang_at->format('H:i');
                    return '<div>'
                        . '<span>' . $time . '</span>'
                        . '<img src="' . $url . '" class="rounded ms-1 photo-thumb" '
                        . 'data-full="' . $url . '" '
                        . 'style="width:30px; height:30px; object-fit:cover; cursor:pointer;" '
                        . 'title="Klik untuk lihat foto">'
                        . '</div>';
                }
                if ($att->photo_pulang_at && !$att->photo_pulang) {
                    return '<span class="badge bg-yellow-lt">Force closed</span> '
                        . $att->photo_pulang_at->format('H:i');
                }
                if ($att->photo_pulang_at) {
                    return $att->photo_pulang_at->format('H:i');
                }
                return '<span class="badge bg-red-lt">OPEN</span>';
            })
            ->addColumn('durasi', function ($att) {
                if ($att->photo_pergi_at && $att->photo_pulang_at) {
                    $diff = $att->photo_pergi_at->diff($att->photo_pulang_at);
                    return $diff->format('%Hj %Im');
                }
                if ($att->photo_pergi_at && !$att->photo_pulang_at) {
                    $diff = $att->photo_pergi_at->diff(now(config('app.timezone')));
                    return $diff->format('%Hj %Im') . ' (aktif)';
                }
                return '—';
            })
            ->addColumn('catatan', function ($att) {
                if ($att->catatan) {
                    $truncated = strlen($att->catatan) > 40
                        ? substr($att->catatan, 0, 40) . '...'
                        : $att->catatan;
                    return '<span title="' . e($att->catatan) . '">' . e($truncated) . '</span>';
                }
                return '—';
            })
            ->addColumn('catatan_pulang', function ($att) {
                if ($att->catatan_pulang) {
                    $truncated = strlen($att->catatan_pulang) > 40
                        ? substr($att->catatan_pulang, 0, 40) . '...'
                        : $att->catatan_pulang;
                    return '<span title="' . e($att->catatan_pulang) . '">' . e($truncated) . '</span>';
                }
                return '—';
            })
            ->addColumn('status', function ($att) {
                if ($att->photo_pulang_at && $att->photo_pulang) {
                    return '<span class="badge bg-green-lt">Closed</span>';
                }
                if ($att->photo_pulang_at && !$att->photo_pulang) {
                    return '<span class="badge bg-yellow-lt">Force Closed</span>';
                }
                return '<span class="badge bg-red-lt">Open</span>';
            })
            ->addColumn('warning', function ($att) {
                $warnings = [];

                // 1. Staff area doesn't match machine area
                if ($att->staff) {
                    $staffAreaId = $att->staff->area_id;
                    $mismatchMachines = $att->machines->filter(fn($m) => $m->area_id !== $staffAreaId);
                    if ($mismatchMachines->isNotEmpty()) {
                        $codes = $mismatchMachines->pluck('code')->join(', ');
                        $warnings[] = '⚠ Area mismatch: ' . $codes;
                    }
                }

                // 2. HV taken without paired steam
                $hvMachines = $att->machines->filter(function ($m) {
                    return $m->paired_machine_id !== null
                        && $m->category
                        && $m->category->slug === 'hydrovacuum';
                });
                foreach ($hvMachines as $hv) {
                    if (!$att->machines->contains('id', $hv->paired_machine_id)) {
                        $warnings[] = '⚠ ' . $hv->code . ' tanpa steam pair';
                    }
                }

                // 3. Still open (no pulang)
                if (!$att->photo_pulang_at) {
                    $warnings[] = '⚠ Belum pulang';
                }

                if (empty($warnings)) {
                    return '—';
                }

                return '<span class="text-danger" title="' . e(implode('; ', $warnings)) . '">'
                    . e($warnings[0])
                    . (count($warnings) > 1 ? ' +' . (count($warnings) - 1) : '')
                    . '</span>';
            })
            ->addColumn('action', function ($att) {
                $id = $att->id;
                $actions = '<button class="btn btn-sm btn-info viewAttendance" data-id="' . $id . '"><i class="ti ti-eye"></i> View</button> ';
                $actions .= '<button class="btn btn-sm btn-warning editAttendance" data-id="' . $id . '"><i class="ti ti-edit"></i> Edit</button> ';

                // Force Close only if open
                if (!$att->photo_pulang_at) {
                    $actions .= '<button class="btn btn-sm btn-danger forceCloseAttendance" data-id="' . $id . '"><i class="ti ti-lock"></i> Force</button> ';
                }

                $actions .= '<button class="btn btn-sm btn-danger deleteAttendance" data-id="' . $id . '"><i class="ti ti-trash"></i> Hapus</button>';

                return $actions;
            })
            ->rawColumns(['jam_pergi', 'jam_pulang', 'status', 'catatan', 'catatan_pulang', 'warning', 'action'])
            ->order(function ($query) {
                $query->orderBy('date', 'desc');
            })
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
