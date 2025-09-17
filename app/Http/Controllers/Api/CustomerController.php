<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Http\Resources\CustomerResource; // <-- Tambahkan Http di sini
use Illuminate\Validation\Rule; // <-- TAMBAHKAN BARIS INI
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // <-- 1. TAMBAHKAN INI

class CustomerController extends Controller
{
    use AuthorizesRequests; // <-- 2. TAMBAHKAN INI
    // Di app/Http/Controllers/Api/CustomerController.php
    // Pastikan semua 'use' statement yang dibutuhkan sudah ada
    // (Customer, CustomerResource, Request, Rule)

    public function index()
    {
        // Cek izin: apakah user boleh melihat daftar Customer?
        $this->authorize('viewAny', Customer::class);
        
        // Eager load relasi addresses untuk efisiensi
        return CustomerResource::collection(Customer::with('addresses')->get());
    }

    public function store(Request $request)
    {
        // Cek izin: apakah user boleh membuat Customer baru?
        $this->authorize('create', Customer::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|unique:customers,phone_number|max:255',
        ]);

        $customer = Customer::create($validated);
        return new CustomerResource($customer);
    }

    public function show(Customer $customer)
    {
        // Cek izin: apakah user boleh melihat Customer ini?
        $this->authorize('view', $customer);

        // Memuat relasi alamat untuk satu customer
        return new CustomerResource($customer->load('addresses'));
    }

    public function update(Request $request, Customer $customer)
    {
        // Cek izin: apakah user boleh mengupdate Customer ini?
        $this->authorize('update', $customer);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'phone_number' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('customers')->ignore($customer->id),
            ],
        ]);

        $customer->update($validated);
        return new CustomerResource($customer);
    }

    public function destroy(Customer $customer)
    {
        // Cek izin: apakah user boleh menghapus Customer ini?
        $this->authorize('delete', $customer);

        $customer->delete();
        return response()->noContent();
    }
}
