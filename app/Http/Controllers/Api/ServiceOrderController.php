<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ImageCompressor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ServiceOrder;
use App\Models\MachineAttendance;
use App\Http\Resources\ServiceOrderResource;
use App\Http\Resources\StaffServiceOrderResource;
use App\Http\Resources\UserResource;
use App\Models\Staff;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ServiceOrderController extends Controller
{
    use AuthorizesRequests;

    public function __construct(protected ImageCompressor $imageCompressor)
    {
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $query = ServiceOrder::query();

        if ($user->role == 'staff' && $user->staff) {
            // Filter orders where this staff is assigned to ANY session
            $query->whereHas('sessions.staff', function ($q) use ($user) {
                $q->withoutGlobalScopes()->where('staff.id', $user->staff->id);
            });
            $serviceOrders = $query->with(['customer', 'address', 'items.service', 'sessions.staff', 'creator'])->get();

            // Map session staff to a 'staff' relation so the Resource can read it
            $serviceOrders->each(function ($so) {
                $so->setRelation('staff', $so->allAssignedStaff());
            });

            return StaffServiceOrderResource::collection($serviceOrders);
        }

        $serviceOrders = $query->with(['customer', 'address', 'items.service', 'sessions.staff'])->get();

        // Map session staff to a 'staff' relation so the Resource can read it
        $serviceOrders->each(function ($so) {
            $so->setRelation('staff', $so->allAssignedStaff());
        });

        return ServiceOrderResource::collection($serviceOrders);
    }

    public function store(Request $request)
    {
        $this->authorize('create', ServiceOrder::class);
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'address_id' => 'required|exists:addresses,id',
            'work_date' => 'required|date',
            'work_time' => 'required|date_format:H:i',
            'work_notes' => 'nullable|string',
            'staff_notes' => 'nullable|string',
            'items' => 'sometimes|array|min:1',
            'items.*.service_id' => 'sometimes|required|exists:services,id',
            'items.*.quantity' => 'sometimes|required|integer|min:1',
            'staff_ids' => 'required|array|min:1',
            'staff_ids.*' => 'required|exists:staff,id',
        ]);

        $soData = [
            'customer_id' => $validated['customer_id'],
            'address_id' => $validated['address_id'],
            'work_date' => $validated['work_date'],
            'work_time' => $validated['work_time'],
            'work_notes' => $validated['work_notes'] ?? null,
            'staff_notes' => $validated['staff_notes'] ?? null,
            'services' => $validated['items'] ?? [],
        ];

        $serviceOrder = app(\App\Actions\CreateServiceOrderAction::class)->execute(
            $soData,
            $validated['staff_ids'],
            $request->user()->id
        );

        // Kembalikan data lengkap dengan relasinya
        $serviceOrder->load(['customer', 'address', 'items.service', 'sessions.staff']);
        $serviceOrder->setRelation('staff', $serviceOrder->allAssignedStaff());
        return new ServiceOrderResource($serviceOrder);
    }

    /**
     * Display the specified resource.
     */
    public function show(ServiceOrder $serviceOrder)
    {
        $user = request()->user();

        if ($user->role == 'staff') {
            $this->authorize('viewStaffDetails', $serviceOrder);
            $serviceOrder->load(['customer', 'address', 'items.service', 'sessions.staff', 'creator']);
            $serviceOrder->setRelation('staff', $serviceOrder->allAssignedStaff());
            return new StaffServiceOrderResource($serviceOrder);
        } else {
            $this->authorize('view', $serviceOrder);
            $serviceOrder->load(['customer', 'address', 'items.service', 'sessions.staff', 'creator']);
            $serviceOrder->setRelation('staff', $serviceOrder->allAssignedStaff());
            return new ServiceOrderResource($serviceOrder);
        }
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
            'work_time' => 'sometimes|required|date_format:H:i',
            'status' => ['sometimes', 'required', 'string', Rule::in([ServiceOrder::STATUS_BOOKED, ServiceOrder::STATUS_PROSES, 'cancel', ServiceOrder::STATUS_DONE, ServiceOrder::STATUS_INVOICED])],
            'work_notes' => 'nullable|string',
            'staff_notes' => 'nullable|string',
            'items' => 'sometimes|array|min:1',
            'items.*.service_id' => 'sometimes|required|exists:services,id',
            'items.*.quantity' => 'sometimes|required|integer|min:1',
            'staff_ids' => 'sometimes|required|array|min:1',
            'staff_ids.*' => 'required|exists:staff,id',
            'owner_password' => 'nullable|string',
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

        $soData = [
            'work_notes' => $validated['work_notes'] ?? null,
            'staff_notes' => $validated['staff_notes'] ?? null,
            'status' => $newStatus,
            'services' => $validated['items'] ?? [],
        ];

        // Only include optional fields when present
        if (isset($validated['customer_id'])) $soData['customer_id'] = $validated['customer_id'];
        if (isset($validated['address_id'])) $soData['address_id'] = $validated['address_id'];
        if (isset($validated['work_date'])) $soData['work_date'] = $validated['work_date'];
        if (isset($validated['work_time'])) $soData['work_time'] = $validated['work_time'];

        $updatedServiceOrder = app(\App\Actions\UpdateServiceOrderAction::class)->execute(
            $serviceOrder,
            $soData,
            $validated['staff_ids'] ?? null
        );

        $updatedServiceOrder->load(['customer', 'address', 'items.service', 'sessions.staff']);
        $updatedServiceOrder->setRelation('staff', $updatedServiceOrder->allAssignedStaff());
        return new ServiceOrderResource($updatedServiceOrder);
    }

    /**
     * Method khusus untuk staff mengubah status pekerjaan.
     */
    public function updateStatus(Request $request, ServiceOrder $serviceOrder)
    {
        $this->authorize('updateStatus', $serviceOrder);

        $validated = $request->validate([
            'status' => [
                'required',
                'string',
                Rule::in([
                    ServiceOrder::STATUS_BOOKED,
                    ServiceOrder::STATUS_PROSES,
                    ServiceOrder::STATUS_DONE,
                    'cancel',
                    ServiceOrder::STATUS_INVOICED,
                ])
            ],
        ]);

        $originalStatus = $serviceOrder->status;
        $newStatus = $validated['status'];
        $user = $request->user();

        $transition = $serviceOrder->canTransitionTo($newStatus, $user, $request->owner_password);

        if (!$transition['allowed']) {
            return response()->json(['success' => false, 'message' => $transition['message']], 400);
        }

        $serviceOrder->update($validated);

        // Update customer's last_order_date when status changes
        $serviceOrder->load('customer');
        $serviceOrder->customer->syncLastOrderDate();

        return response()->json(['success' => true, 'message' => 'Service Order status updated successfully.', 'service_order' => new ServiceOrderResource($serviceOrder)]);
    }

    /**
     * Method khusus untuk staff memulai pekerjaan (mengubah status ke 'proses' dan mengunggah foto).
     */
    public function startWork(Request $request, ServiceOrder $serviceOrder)
    {
        $this->authorize('startWork', $serviceOrder);

        // SO Gate: Staff must have Mesin Pergi today before uploading proofs
        $user = $request->user();
        if (strtolower(trim($user->role)) === 'staff') {
            $staff = $user->staff;
            if ($staff && !MachineAttendance::hasActiveAttendanceToday($staff->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Upload Mesin Pergi dulu sebelum mulai kerjaan',
                ], 422);
            }
        }

        $validated = $request->validate([
            // 128 MB max, allow common image types including HEIC/HEIF
            'photo' => 'required|file|mimes:jpeg,png,jpg,gif,svg,bmp,webp,heic,heif|max:128000',
        ]);

        if ($serviceOrder->status !== ServiceOrder::STATUS_BOOKED) {
            return response()->json(['success' => false, 'message' => 'Service Order must be in booked status to start work.'], 400);
        }

        $staffId = $request->user()->staff?->id;

        if (!$staffId) {
            return response()->json(['success' => false, 'message' => 'Authenticated user has no staff record.'], 400);
        }

        DB::transaction(function () use ($request, $serviceOrder, $validated, $staffId) {
            // Store the arrival photo (compressed)
            $path = $this->imageCompressor->compress($request->file('photo'), 'work_photos');

            $serviceOrder->workPhotos()->create([
                'uploaded_by' => $request->user()->id,
                'file_path' => $path,
                'type' => 'arrival',
            ]);

            // Find today's session for this order assigned to this staff
            $session = \App\Models\OrderSession::where('service_order_id', $serviceOrder->id)
                ->whereDate('tanggal', today())
                ->whereHas('staff', function ($q) use ($staffId) {
                    $q->withoutGlobalScopes()->where('staff.id', $staffId);
                })
                ->where('status', 'booked')
                ->first();

            if ($session) {
                app(\App\Actions\UpdateOrderSessionAction::class)->execute($session, [
                    'status' => 'proses',
                ]);
            }
        });

        return response()->json(['success' => true, 'message' => 'Work started and photo uploaded successfully.']);
    }

    /**
     * Method khusus untuk staff mengunggah bukti kerja (foto before/after).
     */
    public function uploadWorkProof(Request $request, ServiceOrder $serviceOrder)
    {
        $this->authorize('uploadWorkProof', $serviceOrder);

        // SO Gate: Staff must have Mesin Pergi today before uploading proofs
        $user = $request->user();
        if (strtolower(trim($user->role)) === 'staff') {
            $staff = $user->staff;
            if ($staff && !MachineAttendance::hasActiveAttendanceToday($staff->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Upload Mesin Pergi dulu sebelum mulai kerjaan',
                ], 422);
            }
        }

        $validated = $request->validate([
            'type' => 'required|string|in:before,after',
            // Align with front-end validation to allow HEIC/HEIF up to 128 MB
            'photo' => 'required|file|mimes:jpeg,png,jpg,gif,svg,bmp,webp,heic,heif|max:128000',
        ]);

        if ($serviceOrder->status !== ServiceOrder::STATUS_PROSES) {
            return response()->json(['success' => false, 'message' => 'Service Order must be in proses status to upload work proof.'], 400);
        }

        DB::transaction(function () use ($request, $serviceOrder, $validated) {
            // Store the photo (compressed)
            $path = $this->imageCompressor->compress($request->file('photo'), 'work_photos');

            // Create WorkPhoto record
            $serviceOrder->workPhotos()->create([
                'uploaded_by' => $request->user()->id,
                'file_path' => $path,
                'type' => $validated['type'],
            ]);

            // Check if both 'before' and 'after' photos exist
            $hasBeforePhoto = $serviceOrder->workPhotos()->where('type', 'before')->exists();
            $hasAfterPhoto = $serviceOrder->workPhotos()->where('type', 'after')->exists();

            if ($hasBeforePhoto && $hasAfterPhoto) {
                $serviceOrder->work_proof_completed_at = now();
                $serviceOrder->save();
            }
        });

        return response()->json(['success' => true, 'message' => 'Work proof photo uploaded successfully.']);
    }

    /**
     * Method khusus untuk staff menyelesaikan pekerjaan (mark today's session as done).
     */
    public function completeWork(Request $request, ServiceOrder $serviceOrder)
    {
        $this->authorize('startWork', $serviceOrder);

        $staffId = $request->user()->staff?->id;

        if (!$staffId) {
            return response()->json(['success' => false, 'message' => 'Authenticated user has no staff record.'], 400);
        }

        // Find today's session for this order assigned to this staff that is in 'proses'
        $session = \App\Models\OrderSession::where('service_order_id', $serviceOrder->id)
            ->whereDate('tanggal', today())
            ->whereHas('staff', function ($q) use ($staffId) {
                $q->withoutGlobalScopes()->where('staff.id', $staffId);
            })
            ->where('status', 'proses')
            ->first();

        if (!$session) {
            return response()->json(['success' => false, 'message' => 'No active session found for today.'], 400);
        }

        app(\App\Actions\UpdateOrderSessionAction::class)->execute($session, [
            'status' => 'done',
        ]);

        return response()->json(['success' => true, 'message' => 'Pekerjaan berhasil diselesaikan.']);
    }

    /**
     * Method khusus untuk staff menerima tanda tangan customer sebagai bukti selesai kerja.
     */
    public function submitCustomerSignature(Request $request, ServiceOrder $serviceOrder)
    {
        $this->authorize('startWork', $serviceOrder);

        $validated = $request->validate([
            'signature_image' => 'required|string',
        ]);

        $staffId = $request->user()->staff?->id;

        if (!$staffId) {
            return response()->json(['success' => false, 'message' => 'Authenticated user has no staff record.'], 400);
        }

        // Decode base64 signature image and save as file
        $signatureData = $validated['signature_image'];

        // Extract base64 data from data URI
        if (preg_match('/^data:image\/(\w+);base64,/', $signatureData, $matches)) {
            $imageData = base64_decode(substr($signatureData, strpos($signatureData, ',') + 1));
            $extension = $matches[1] === 'jpeg' ? 'jpg' : $matches[1];
        } else {
            // Fallback: assume PNG
            $imageData = base64_decode($signatureData);
            $extension = 'png';
        }

        if (!$imageData) {
            return response()->json(['success' => false, 'message' => 'Gambar tanda tangan tidak valid.'], 400);
        }

        // Save to storage using public disk (same as ImageCompressor)
        $fileName = 'signature_' . time() . '_' . uniqid() . '.' . $extension;
        $path = 'work_photos/' . $fileName;
        \Illuminate\Support\Facades\Storage::disk('public')->put($path, $imageData);

        // Create WorkPhoto record with type='signature'
        $serviceOrder->workPhotos()->create([
            'uploaded_by' => $request->user()->id,
            'file_path' => $path,
            'type' => 'signature',
        ]);

        // Mark ALL incomplete sessions of this order as done
        $allSessions = \App\Models\OrderSession::where('service_order_id', $serviceOrder->id)
            ->whereIn('status', ['booked', 'proses'])
            ->get();

        foreach ($allSessions as $session) {
            app(\App\Actions\UpdateOrderSessionAction::class)->execute($session, [
                'status' => 'done',
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Tanda tangan customer berhasil disimpan.']);
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
