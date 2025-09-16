<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ServiceCategory;
use App\Http\Resources\ServiceCategoryResource; // <-- Tambahkan Http di sini
use Illuminate\Validation\Rule; // <-- TAMBAHKAN BARIS INI

class ServiceCategoryController extends Controller
{
    public function index()
    {
        // Gunakan Resource untuk konsistensi output
        return ServiceCategoryResource::collection(ServiceCategory::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
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
        // Laravel otomatis mencari ServiceCategory berdasarkan ID dari URL.
        // Jika tidak ketemu, otomatis akan menampilkan error 404.
        return new ServiceCategoryResource($serviceCategory);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ServiceCategory $serviceCategory)
    {
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
        $serviceCategory->delete();

        // Kembalikan response "204 No Content", standar untuk delete yang sukses
        return response()->noContent();
    }
}
