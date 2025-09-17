<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // <-- PENTING untuk transaksi
use App\Models\ServiceOrder;
use App\Models\Service;
use App\Http\Resources\ServiceOrderResource; // <-- Tambahkan Http di sini
use Illuminate\Validation\Rule; // <-- TAMBAHKAN BARIS INI


class ServiceOrderController extends Controller
{
    public function index()
    {
        // Eager load semua relasi yang dibutuhkan
        $serviceOrders = ServiceOrder::with(['customer', 'address', 'items.service', 'staff'])->get();
        return ServiceOrderResource::collection($serviceOrders);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'address_id' => 'required|exists:addresses,id',
            'work_date' => 'required|date',
            'work_notes' => 'nullable|string',
            'staff_notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.service_id' => 'required|exists:services,id',
            'items.*.quantity' => 'required|integer|min:1',
            'staff_ids' => 'required|array|min:1',
            'staff_ids.*' => 'required|exists:staff,id',
        ]);

        // Kita gunakan DB::transaction untuk memastikan semua query berhasil
        // atau tidak sama sekali, demi keamanan data.
        $serviceOrder = DB::transaction(function () use ($validated, $request) {
            // 1. Buat Service Order utama
            $so = ServiceOrder::create([
                'customer_id' => $validated['customer_id'],
                'address_id' => $validated['address_id'],
                'work_date' => $validated['work_date'],
                'work_notes' => $validated['work_notes'] ?? null,
                'staff_notes' => $validated['staff_notes'] ?? null,
                'so_number' => 'SO-' . time(), // Nanti bisa dibuat lebih canggih
                'status' => 'confirmed', // Status awal
                'created_by' => $request->user()->id, // User yang sedang login
            ]);

            // 2. Loop dan simpan item-item layanannya
            foreach ($validated['items'] as $item) {
                $service = Service::find($item['service_id']);
                $so->items()->create([
                    'service_id' => $item['service_id'],
                    'quantity' => $item['quantity'],
                    'price' => $service->price, // Ambil harga dari master service
                    'total' => $item['quantity'] * $service->price,
                ]);
            }

            // 3. Tugaskan staff ke service order ini
            $so->staff()->attach($validated['staff_ids']);

            return $so;
        });

        // Kembalikan data lengkap dengan relasinya
        return new ServiceOrderResource($serviceOrder->load(['customer', 'address', 'items.service', 'staff']));
    }

    /**
     * Display the specified resource.
     */
    public function show(ServiceOrder $serviceOrder)
    {
        // Muat semua relasi yang dibutuhkan untuk ditampilkan
        return new ServiceOrderResource($serviceOrder->load(['customer', 'address', 'items.service', 'staff', 'creator']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ServiceOrder $serviceOrder)
    {
        $validated = $request->validate([
            'customer_id' => 'sometimes|required|exists:customers,id',
            'address_id' => 'sometimes|required|exists:addresses,id',
            'work_date' => 'sometimes|required|date',
            'status' => 'sometimes|required|string', // Untuk mengubah status
            'work_notes' => 'nullable|string',
            'staff_notes' => 'nullable|string',
            'items' => 'sometimes|required|array|min:1',
            'items.*.service_id' => 'required|exists:services,id',
            'items.*.quantity' => 'required|integer|min:1',
            'staff_ids' => 'sometimes|required|array|min:1',
            'staff_ids.*' => 'required|exists:staff,id',
        ]);

        $updatedServiceOrder = DB::transaction(function () use ($validated, $serviceOrder) {
            // 1. Update data utama Service Order
            $serviceOrder->update($validated);

            // 2. Jika ada data 'items' yang dikirim, sinkronisasi item-itemnya
            if (isset($validated['items'])) {
                // Hapus item lama
                $serviceOrder->items()->delete();
                // Buat item baru
                foreach ($validated['items'] as $item) {
                    $service = Service::find($item['service_id']);
                    $serviceOrder->items()->create([
                        'service_id' => $item['service_id'],
                        'quantity' => $item['quantity'],
                        'price' => $service->price,
                        'total' => $item['quantity'] * $service->price,
                    ]);
                }
            }

            // 3. Jika ada data 'staff_ids' yang dikirim, sinkronisasi staffnya
            if (isset($validated['staff_ids'])) {
                $serviceOrder->staff()->sync($validated['staff_ids']);
            }

            return $serviceOrder;
        });

        return new ServiceOrderResource($updatedServiceOrder->load(['customer', 'address', 'items.service', 'staff']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ServiceOrder $serviceOrder)
    {
        $serviceOrder->delete();

        return response()->noContent();
    }
}
