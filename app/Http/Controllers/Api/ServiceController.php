<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Service;
use App\Http\Resources\ServiceResource; // <-- Tambahkan Http di sini
use Illuminate\Validation\Rule; // <-- TAMBAHKAN BARIS INI

class ServiceController extends Controller
{
    // Method index()
    public function index()
    {
        // Mengambil semua service dan memuat relasi 'category'
        return ServiceResource::collection(Service::with('category')->get());
    }

    // Method store()
    public function store(Request $request)
    {
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
        // Memuat relasi category untuk satu data
        return new ServiceResource($service->load('category'));
    }

    // Method update()
    public function update(Request $request, Service $service)
    {
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
        $service->delete();
        return response()->noContent();
    }
}
