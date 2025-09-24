<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // <-- PENTING untuk transaksi
use App\Models\ServiceOrder;
use App\Models\Service;
use App\Http\Resources\ServiceOrderResource; // <-- Tambahkan Http di sini
use Illuminate\Validation\Rule; // <-- TAMBAHKAN BARIS INI
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ServiceOrderController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request) 
    {
        $user = $request->user(); 
        $query = ServiceOrder::query();

        if ($user->role == 'staff' && $user->staff) {
            $query->whereHas('staff', function ($q) use ($user) {
                $q->where('staff.id', $user->staff->id);
            });
        }
        
        $serviceOrders = $query->with(['customer', 'address', 'items.service', 'staff'])->get();
        return ServiceOrderResource::collection($serviceOrders);
    }

    public function store(Request $request)
    {
        $this->authorize('create', ServiceOrder::class);
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'address_id' => 'required|exists:addresses,id',
            'work_date' => 'required|date',
            'work_notes' => 'nullable|string',
            'staff_notes' => 'nullable|string',
            'items' => 'sometimes|array|min:1',
            'items.*.service_id' => 'sometimes|required|exists:services,id',
            'items.*.quantity' => 'sometimes|required|integer|min:1',
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
                'status' => ServiceOrder::STATUS_BOOKED, // Status awal
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
        $this->authorize('view', $serviceOrder);
        // Muat semua relasi yang dibutuhkan untuk ditampilkan
        return new ServiceOrderResource($serviceOrder->load(['customer', 'address', 'items.service', 'staff', 'creator']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ServiceOrder $serviceOrder)
    {
        $this->authorize('update', $serviceOrder);
        $rules = [
            'customer_id' => 'sometimes|required|exists:customers,id',
            'address_id' => 'sometimes|required|exists:addresses,id',
            'work_date' => 'sometimes|required|date',
            'status' => ['sometimes', 'required', 'string', Rule::in([ServiceOrder::STATUS_BOOKED, ServiceOrder::STATUS_PROSES, ServiceOrder::STATUS_CANCELLED, ServiceOrder::STATUS_DONE, ServiceOrder::STATUS_INVOICED])],
            'work_notes' => 'nullable|string',
            'staff_notes' => 'nullable|string',
            'items' => 'sometimes|array|min:1',
            'items.*.service_id' => 'sometimes|required|exists:services,id',
            'items.*.quantity' => 'sometimes|required|integer|min:1',
            'staff_ids' => 'sometimes|required|array|min:1',
            'staff_ids.*' => 'required|exists:staff,id',
            'owner_password' => 'nullable|string', // Default to nullable
        ];

        $originalStatus = $serviceOrder->status;
        $newStatus = $request->status ?? $originalStatus;

        $validated = $request->validate($rules);

        $user = $request->user();

        // --- Status Transition Logic ---
        if ($originalStatus !== $newStatus) {
            $transition = $serviceOrder->canTransitionTo($newStatus, $user, $validated['owner_password'] ?? null);

            if (!$transition['allowed']) {
                return response()->json(['success' => false, 'message' => $transition['message']], 400);
            }
        }
        // --- End Status Transition Logic ---

        $updatedServiceOrder = DB::transaction(function () use ($validated, $serviceOrder, $newStatus) {
            // 1. Update data utama Service Order
            $serviceOrder->update(array_merge($validated, ['status': $newStatus]));

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
     * Method khusus untuk staff mengubah status pekerjaan.
     */
    public function updateStatus(Request $request, ServiceOrder $serviceOrder)
    {
        $this->authorize('updateStatus', $serviceOrder);

        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in([
                ServiceOrder::STATUS_BOOKED,
                ServiceOrder::STATUS_PROSES,
                ServiceOrder::STATUS_DONE,
                ServiceOrder::STATUS_CANCELLED,
                ServiceOrder::STATUS_INVOICED,
            ])],
        ]);

        $originalStatus = $serviceOrder->status;
        $newStatus = $validated['status'];
        $user = $request->user();

        $transition = $serviceOrder->canTransitionTo($newStatus, $user, $request->owner_password);

        if (!$transition['allowed']) {
            return response()->json(['success' => false, 'message' => $transition['message']], 400);
        }

        $serviceOrder->update($validated);

        return new ServiceOrderResource($serviceOrder);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ServiceOrder $serviceOrder)
    {
        $this->authorize('delete', $serviceOrder);
        $serviceOrder->delete();

        return response()->noContent();
    }
}