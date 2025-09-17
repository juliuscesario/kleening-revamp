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
    public function store(Request $request, ServiceOrder $serviceOrder)
    {
        $validated = $request->validate([
            'type' => 'required|string|in:arrival,before,after',
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Maks 2MB
        ]);
        
        // Simpan file dan dapatkan path-nya
        $path = $request->file('photo')->store('work_photos', 'public');
        
        $photo = $serviceOrder->workPhotos()->create([
            'type' => $validated['type'],
            'file_path' => $path,
            'uploaded_by' => $request->user()->id, // Menggunakan ID user yang sedang login
        ]);

        return new WorkPhotoResource($photo);
    }

    public function index(ServiceOrder $serviceOrder)
    {
        return WorkPhotoResource::collection($serviceOrder->workPhotos);
    }



    public function destroy(WorkPhoto $workPhoto)
    {
        // Hapus file dari storage
        Storage::disk('public')->delete($workPhoto->file_path);
        // Hapus record dari database
        $workPhoto->delete();

        return response()->noContent();
    }
}