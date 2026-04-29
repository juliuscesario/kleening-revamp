<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\FormOrderParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FormOrderController extends Controller
{
    public function parse(Request $request)
    {
        $request->validate([
            'raw_text' => 'required|string|min:10',
        ]);

        // Step 1: Parse raw text (basic cleanup, NO geocoding)
        $parser = new FormOrderParser();
        $result = $parser->parse($request->raw_text);

        // Step 2: Check if customer exists by phone
        $phone = $result['no_hp'];
        $customer = null;
        $addressData = [];

        if (!empty($phone)) {
            $searchVariants = [
                $phone,                          // 628123333333
                '+' . $phone,                    // +628123333333
                '0' . substr($phone, 2),        // 08123333333
                substr($phone, 2),              // 8123333333
            ];

            $customer = Customer::withoutGlobalScopes()
                ->where(function ($query) use ($searchVariants) {
                    foreach ($searchVariants as $variant) {
                        $query->orWhere('phone_number', $variant);
                    }
                })
                ->first();
        }

        if ($customer) {
            // Step 3a: Customer FOUND — skip geocoding, load their saved addresses
            $addresses = $customer->addresses()->withoutGlobalScopes()->get();

            $addressData = $addresses->map(function ($address) {
                $label = $address->full_address;
                if ($address->lokasi) {
                    $label .= ' (' . $address->lokasi . ')';
                }
                if (strlen($label) > 100) {
                    $label = substr($label, 0, 97) . '...';
                }

                return [
                    'id' => $address->id,
                    'address' => $address->full_address,
                    'lokasi' => $address->lokasi,
                    'label' => $label,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $result,
                'customer_found' => true,
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'phone' => $customer->phone_number,
                ],
                'addresses' => $addressData,
            ]);
        }

        // Step 3b: Customer NOT FOUND — geocode the address
        $result = $parser->enrichWithGeocoding($result);

        return response()->json([
            'success' => true,
            'data' => $result,
            'customer_found' => false,
            'customer' => null,
            'addresses' => [],
        ]);
    }

    public function createCustomer(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address.label' => 'required|string|max:255',
            'address.area_id' => 'required|exists:areas,id',
            'address.lokasi' => 'required|string|max:100',
            'address.full_address' => 'required|string',
            'address.google_maps_link' => 'nullable|string',
            'address.contact_name' => 'required|string|max:255',
            'address.contact_phone' => 'required|string|max:20',
        ]);

        // Check if phone already exists (prevent duplicates)
        $phone = $request->phone;
        $searchVariants = [
            $phone,
            '+' . $phone,
            '0' . substr($phone, 2),
            substr($phone, 2),
        ];

        $existingCustomer = Customer::withoutGlobalScopes()
            ->where(function ($query) use ($searchVariants) {
                foreach ($searchVariants as $variant) {
                    $query->orWhere('phone_number', $variant);
                }
            })
            ->first();

        if ($existingCustomer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer dengan nomor telepon ini sudah terdaftar: ' . $existingCustomer->name,
            ], 422);
        }

        // Create customer + address in a transaction
        $result = DB::transaction(function () use ($request) {
            $customer = Customer::create([
                'name' => $request->name,
                'phone_number' => $request->phone,
            ]);

            $addressData = $request->address;
            $address = $customer->addresses()->create([
                'area_id' => $addressData['area_id'],
                'label' => $addressData['label'],
                'contact_name' => $addressData['contact_name'],
                'contact_phone' => $addressData['contact_phone'],
                'full_address' => $addressData['full_address'],
                'lokasi' => $addressData['lokasi'],
                'google_maps_link' => $addressData['google_maps_link'] ?? null,
            ]);

            return compact('customer', 'address');
        });

        return response()->json([
            'success' => true,
            'customer' => [
                'id' => $result['customer']->id,
                'name' => $result['customer']->name,
                'phone' => $result['customer']->phone_number,
            ],
            'address_id' => $result['address']->id,
        ]);
    }
}
