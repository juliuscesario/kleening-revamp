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
