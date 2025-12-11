<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ServiceCategory;
use App\Http\Resources\ServiceCategoryResource; // <-- Tambahkan Http di sini
use Illuminate\Validation\Rule; // <-- TAMBAHKAN BARIS INI
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // <-- 1. TAMBAHKAN INI

class ServiceCategoryController extends Controller
{
    use AuthorizesRequests; // <-- 2. TAMBAHKAN INI
    public function index()
    {
        // Cek izin: apakah user boleh melihat daftar Service Category?
        $this->authorize('viewAny', ServiceCategory::class);

        // Gunakan Resource untuk konsistensi output
        return ServiceCategoryResource::collection(ServiceCategory::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Cek izin: apakah user boleh melihat daftar Service Category?
        $this->authorize('create', ServiceCategory::class);

        // 1. Validasi input
        $validated = $request->validate([
            'name' => 'required|string|unique:service_categories|max:255',
        ]);

        // 2. Buat data baru
        $serviceCategory = ServiceCategory::create($validated);

        // 3. Kembalikan data yang baru dibuat dengan format Resource
        return new ServiceCategoryResource($serviceCategory);
    }

    /**
     * Display the specified resource.
     */
    /**
     * Display the specified resource.
     */
    public function show(ServiceCategory $serviceCategory)
    {
        // Cek izin: apakah user boleh melihat Service Category ini?
        $this->authorize('view', $serviceCategory);

        // Laravel otomatis mencari ServiceCategory berdasarkan ID dari URL.
        // Jika tidak ketemu, otomatis akan menampilkan error 404.
        return new ServiceCategoryResource($serviceCategory);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ServiceCategory $serviceCategory)
    {
        // Cek izin: apakah user boleh melihat Service Category ini?
        $this->authorize('update', $serviceCategory);

        // Validasi, dengan aturan 'unique' yang mengabaikan ID serviceCategory saat ini
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',Rule::unique('service_categories')->ignore($serviceCategory->id),
            ],
        ]);

        // Update data 
        $serviceCategory->update($validated);

        // Kembalikan data yang sudah di-update
        return new ServiceCategoryResource($serviceCategory);
    }

     /**
     * Remove the specified resource from storage.
     */
    public function destroy(ServiceCategory $serviceCategory)
    {
        // Cek izin: apakah user boleh menghapus Service Category ini?
        $this->authorize('delete', $serviceCategory);
        $serviceCategory->delete();

        // Kembalikan response "204 No Content", standar untuk delete yang sukses
        return response()->noContent();
    }
}
