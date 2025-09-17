<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Service;
use App\Http\Resources\ServiceResource; // <-- Tambahkan Http di sini
use Illuminate\Validation\Rule; // <-- TAMBAHKAN BARIS INI
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // <-- 1. TAMBAHKAN INI

class ServiceController extends Controller
{
    use AuthorizesRequests; // <-- 2. TAMBAHKAN INI
    // Method index()
    public function index()
    {
        // Cek izin: apakah user boleh melihat daftar Service ?
        $this->authorize('viewAny', Service::class);

        // Mengambil semua service dan memuat relasi 'category'
        return ServiceResource::collection(Service::with('category')->get());
    }

    // Method store()
    public function store(Request $request)
    {
        // Cek izin: apakah user boleh melihat daftar Service?
        $this->authorize('create', Service::class);

        $validated = $request->validate([
            'category_id' => 'required|integer|exists:service_categories,id', // Memastikan category_id ada di tabel service_categories
            'name' => 'required|string|unique:services|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $service = Service::create($validated);

        return new ServiceResource($service);
    }

    // Method show()
    public function show(Service $service)
    {
        // Cek izin: apakah user boleh melihat Service ini?
        $this->authorize('view', $service);
        // Memuat relasi category untuk satu data
        return new ServiceResource($service->load('category'));
    }

    // Method update()
    public function update(Request $request, Service $service)
    {
        // Cek izin: apakah user boleh mengupdate Service ini?
        $this->authorize('update', $service);
        $validated = $request->validate([
            'category_id' => 'sometimes|required|integer|exists:service_categories,id',
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('services')->ignore($service->id),
            ],
            'price' => 'sometimes|required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $service->update($validated);

        return new ServiceResource($service);
    }

    // Method destroy()
    public function destroy(Service $service)
    {
        // Cek izin: apakah user boleh menghapus Service ini?
        $this->authorize('delete', $service);
        $service->delete();
        return response()->noContent();
    }
}
