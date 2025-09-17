<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Area;
use App\Http\Resources\AreaResource; // <-- Tambahkan Http di sini
use Illuminate\Validation\Rule; // <-- TAMBAHKAN BARIS INI
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // <-- 1. TAMBAHKAN INI


class AreaController extends Controller
{
    use AuthorizesRequests; // <-- 2. TAMBAHKAN INI

    public function index()
    {
        // Cek izin: apakah user boleh melihat daftar Area?
        $this->authorize('viewAny', Area::class);

        // Gunakan Resource untuk konsistensi output
        return AreaResource::collection(Area::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Cek izin: apakah user boleh membuat Area baru?
        $this->authorize('create', Area::class);

        // 1. Validasi input
        $validated = $request->validate([
            'name' => 'required|string|unique:areas|max:255',
        ]);

        // 2. Buat data baru
        $area = Area::create($validated);

        // 3. Kembalikan data yang baru dibuat dengan format Resource
        return new AreaResource($area);
    }

    /**
     * Display the specified resource.
     */
    public function show(Area $area)
    {
        // Cek izin: apakah user boleh melihat Area ini?
        $this->authorize('view', $area);

        // Laravel otomatis mencari Area berdasarkan ID dari URL.
        // Jika tidak ketemu, otomatis akan menampilkan error 404.
        return new AreaResource($area);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Area $area)
    {
        // Cek izin: apakah user boleh mengupdate Area ini?
        $this->authorize('update', $area);

        // Validasi, dengan aturan 'unique' yang mengabaikan ID area saat ini
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('areas')->ignore($area->id),
            ],
        ]);

        // Update data area
        $area->update($validated);

        // Kembalikan data yang sudah di-update
        return new AreaResource($area);
    }

     /**
     * Remove the specified resource from storage.
     */
    public function destroy(Area $area)
    {
        // Cek izin: apakah user boleh menghapus Area ini?
        $this->authorize('delete', $area);
        
        $area->delete();

        // Kembalikan response "204 No Content", standar untuk delete yang sukses
        return response()->noContent();
    }
}
