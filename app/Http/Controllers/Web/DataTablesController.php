<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Customer; // <-- Tambahkan model lain jika perlu
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
                    <button class="btn btn-sm btn-warning edit-button" data-id="' . $area->id . '" data-name="' . e($area->name) . '">Edit</button>
                    <button class="btn btn-sm btn-danger delete-button" data-id="' . $area->id . '">Hapus</button>
                ';
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