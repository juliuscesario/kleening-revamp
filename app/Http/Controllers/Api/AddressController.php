<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule; // Penting untuk validasi update

class AddressController extends Controller
{
    /**
     * Menampilkan semua alamat yang dimiliki oleh customer tertentu.
     * Dipanggil melalui: GET /api/customers/{customer}/addresses
     */
    public function indexByCustomer(Customer $customer)
    {
        return AddressResource::collection($customer->addresses);
    }

    /**
     * Menyimpan alamat baru untuk customer tertentu.
     * Dipanggil melalui: POST /api/customers/{customer}/addresses
     */
    public function storeForCustomer(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'contact_name' => 'required|string|max:255',
            'contact_phone' => 'required|string|max:255',
            'full_address' => 'required|string',
            'google_maps_link' => 'nullable|url',
        ]);

        // Menggunakan relasi untuk membuat alamat baru,
        // customer_id akan terisi secara otomatis.
        $address = $customer->addresses()->create($validated);

        // Mengembalikan data yang baru dibuat dengan status 201 Created
        return (new AddressResource($address))->response()->setStatusCode(201);
    }

    /**
     * Menampilkan satu alamat spesifik berdasarkan ID-nya.
     * Dipanggil melalui: GET /api/addresses/{address}
     */
    public function show(Address $address)
    {
        return new AddressResource($address);
    }

    /**
     * Mengubah data alamat yang sudah ada.
     * Dipanggil melalui: PUT/PATCH /api/addresses/{address}
     */
    public function update(Request $request, Address $address)
    {
        $validated = $request->validate([
            'label' => 'sometimes|required|string|max:255',
            'contact_name' => 'sometimes|required|string|max:255',
            'contact_phone' => 'sometimes|required|string|max:255',
            'full_address' => 'sometimes|required|string',
            'google_maps_link' => 'nullable|url',
        ]);

        $address->update($validated);

        return new AddressResource($address);
    }

    /**
     * Menghapus sebuah alamat.
     * Dipanggil melalui: DELETE /api/addresses/{address}
     */
    public function destroy(Address $address)
    {
        $address->delete();

        // Mengembalikan response "204 No Content" yang merupakan standar
        // untuk operasi delete yang sukses.
        return response()->noContent();
    }
}