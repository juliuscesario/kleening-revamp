<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\FormOrderParser;
use Illuminate\Http\Request;

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
}
