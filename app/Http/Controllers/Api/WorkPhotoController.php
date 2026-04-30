<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\WorkPhotoResource;
use App\Models\ServiceOrder; // <-- INI KUNCINYA
use App\Models\WorkPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WorkPhotoController extends Controller
{
    use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    public function store(Request $request, ServiceOrder $serviceOrder)
    {
        $this->authorize('create', [WorkPhoto::class, $serviceOrder]);
        $validated = $request->validate([
            'type' => 'required|string|in:arrival,before,after',
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Admin/Owner: skip sequential validation, allow any type freely
        $user = $request->user();
        $isAdminOrOwner = in_array($user->role, ['admin', 'owner']);

        if (!$isAdminOrOwner) {
            // Sequential validation for staff only
            $existingTypes = $serviceOrder->workPhotos->pluck('type')->toArray();
            $order = ['arrival', 'before', 'after'];
            $nextRequired = [];
            foreach ($order as $t) {
                if (!in_array($t, $existingTypes)) {
                    $nextRequired[] = $t;
                    break;
                }
            }
            if (!empty($nextRequired) && !in_array($validated['type'], $nextRequired)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Harap upload foto secara berurutan: ' . implode(', ', $nextRequired),
                ], 422);
            }
        }

        // Replace existing photo of the same type
        $existingPhoto = $serviceOrder->workPhotos()->where('type', $validated['type'])->first();
        if ($existingPhoto) {
            Storage::disk('public')->delete($existingPhoto->file_path);
            $existingPhoto->delete();
        }

        $path = $request->file('photo')->store('work_photos', 'public');

        $photo = $serviceOrder->workPhotos()->create([
            'type' => $validated['type'],
            'file_path' => $path,
            'uploaded_by' => $user->id,
        ]);

        return response()->json(['success' => true, 'data' => new WorkPhotoResource($photo)]);
    }

    public function index(ServiceOrder $serviceOrder)
    {
        return WorkPhotoResource::collection($serviceOrder->workPhotos);
    }

    public function destroy(ServiceOrder $serviceOrder, WorkPhoto $workPhoto)
    {
        $this->authorize('delete', $workPhoto);

        if ($workPhoto->service_order_id !== $serviceOrder->id) {
            return response()->json(['success' => false, 'message' => 'Foto tidak belong to service order ini.'], 403);
        }

        Storage::disk('public')->delete($workPhoto->file_path);
        $workPhoto->delete();

        return response()->json(['success' => true]);
    }
}