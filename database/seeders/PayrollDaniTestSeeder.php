<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Staff;
use App\Models\Customer;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceOrder;
use App\Models\ServiceOrderItem;
use App\Models\Invoice;
use App\Models\Address;
use Carbon\Carbon;

class PayrollDaniTestSeeder extends Seeder
{
    // Order definitions: [day, time, categorySpec, staffCount, transport, discount, discountType]
    // categorySpec = [['category_name', quantity], ...]
    private array $orders = [
        // DAY 1 (May 1) — IWWWW + IBNU HAFIDZ already exist, add 1 more
        [1, '08:00', [['Poles', 2]], 1, 50000, 0, 'fixed'],

        // DAY 2
        [2, '08:00', [['Hydrovacuum', 3]], 2, 100000, 0, 'fixed'],
        [2, '10:30', [['General Cleaning', 2]], 1, 50000, 50000, 'fixed'],
        [2, '14:00', [['Hydrovacuum', 2], ['General Cleaning', 1]], 3, 75000, 0, 'fixed'],

        // DAY 3
        [3, '08:00', [['Car Interior Detailing', 1]], 1, 100000, 0, 'fixed'],
        [3, '11:00', [['Hydrovacuum', 3], ['Poles', 2]], 2, 50000, 100000, 'fixed'],
        [3, '15:00', [['Premium Wash', 2]], 1, 50000, 0, 'fixed'],

        // DAY 4
        [4, '09:00', [['General Cleaning', 3]], 2, 100000, 0, 'fixed'],
        [4, '12:00', [['Hydrovacuum', 1]], 1, 50000, 0, 'fixed'],
        [4, '16:00', [['Hydrovacuum', 2], ['General Cleaning', 1], ['Poles', 1]], 2, 75000, 150000, 'fixed'],

        // DAY 5
        [5, '08:00', [['Poles Lantai', 1]], 1, 50000, 0, 'fixed'],
        [5, '10:00', [['Hydrovacuum', 1], ['General Cleaning', 2]], 1, 100000, 10, 'percentage'],
        [5, '14:00', [['Hydrovacuum', 4]], 2, 50000, 0, 'fixed'],

        // DAY 6
        [6, '09:00', [['Premium Wash', 1], ['Poles', 1]], 1, 50000, 50000, 'fixed'],
        [6, '11:00', [['General Cleaning', 1]], 3, 75000, 0, 'fixed'],
        [6, '15:00', [['Hydrovacuum', 2]], 1, 50000, 0, 'fixed'],

        // DAY 7
        [7, '08:00', [['Car Interior Detailing', 1], ['General Cleaning', 1]], 2, 100000, 0, 'fixed'],
        [7, '11:00', [['Hydrovacuum', 5]], 1, 50000, 100000, 'fixed'],
        [7, '14:00', [['Poles', 3]], 1, 50000, 0, 'fixed'],

        // DAY 8
        [8, '09:00', [['Hydrovacuum', 1], ['Poles', 1], ['General Cleaning', 2]], 1, 75000, 15, 'percentage'],
        [8, '12:00', [['Hydrovacuum', 2]], 2, 50000, 0, 'fixed'],
        [8, '16:00', [['General Cleaning', 1]], 1, 100000, 0, 'fixed'],

        // DAY 9
        [9, '08:00', [['Premium Wash', 1]], 1, 50000, 0, 'fixed'],
        [9, '10:00', [['Hydrovacuum', 3], ['General Cleaning', 1]], 2, 50000, 0, 'fixed'],
        [9, '14:00', [['Poles Lantai', 2], ['General Cleaning', 1]], 1, 100000, 50000, 'fixed'],

        // DAY 10
        [10, '08:00', [['Hydrovacuum', 2]], 1, 50000, 0, 'fixed'],
        [10, '11:00', [['Hydrovacuum', 2], ['Poles', 2], ['General Cleaning', 2]], 3, 100000, 200000, 'fixed'],
        [10, '15:00', [['Car Interior Detailing', 1]], 1, 50000, 0, 'fixed'],
    ];

    public function run(): void
    {
        // Cleanup: remove any previous test data from this seeder
        $orderIds = ServiceOrder::withoutGlobalScopes()->where('so_number', 'like', 'SO-TEST-%')->pluck('id');
        if ($orderIds->isNotEmpty()) {
            Invoice::whereIn('service_order_id', $orderIds)->delete();
            ServiceOrderItem::whereIn('service_order_id', $orderIds)->delete();
            ServiceOrder::withoutGlobalScopes()->whereIn('id', $orderIds)->delete();
        }
        Service::where('name', 'like', 'Poles Lantai Service%')->delete();

        // --- Setup: get reference data ---
        $dani = Staff::withoutGlobalScopes()->where('name', 'Dani')->firstOrFail();
        $daniId = $dani->id;
        $this->command->info("Dani staff_id: {$daniId}");

        $otherStaff = Staff::withoutGlobalScopes()
            ->where('is_active', true)
            ->where('id', '!=', $daniId)
            ->inRandomOrder()
            ->limit(5)
            ->get();
        $this->command->info("Other staff: " . $otherStaff->pluck('name')->join(', '));

        // Get 30 random customers
        $customers = Customer::inRandomOrder()->limit(30)->get()->values();
        $this->command->info("Using {$customers->count()} customers");

        // Build service pools by category
        $categories = ServiceCategory::all()->keyBy('name');
        $servicePools = [];
        foreach ($categories as $name => $cat) {
            $services = Service::where('category_id', $cat->id)->get();
            if ($services->isEmpty()) {
                // Create dummy services for empty categories (Poles Lantai)
                $prices = [
                    'Poles Lantai' => [40000, 60000, 80000],
                ];
                if (isset($prices[$name])) {
                    foreach ($prices[$name] as $price) {
                        $svc = Service::create([
                            'category_id' => $cat->id,
                            'name' => "{$name} Service {$price}",
                            'price' => $price,
                            'description' => '',
                            'cost' => 0,
                        ]);
                        $services->push($svc);
                    }
                }
            }
            $servicePools[$name] = $services;
            $this->command->info("Category '{$name}': {$services->count()} services");
        }

        // --- Generate orders ---
        $orderCount = 0;
        $customerIndex = 0;
        $soCounter = ServiceOrder::withoutGlobalScopes()->max('id') + 1;

        foreach ($this->orders as $orderDef) {
            [$day, $time, $categorySpec, $staffCount, $transportFee, $discount, $discountType] = $orderDef;

            $workDate = "2026-05-" . str_pad($day, 2, '0', STR_PAD_LEFT);
            $soNumber = "SO-TEST-" . str_pad($soCounter, 8, '0', STR_PAD_LEFT);
            $soCounter++;

            // Pick a customer (cycle through the pool)
            $customer = $customers[$customerIndex % $customers->count()];
            $customerIndex++;

            // Get an address for the customer
            $addressId = $this->getAddressId($customer);

            // Pick staff: always include Dani + random others
            $staffIds = [$daniId];
            if ($staffCount > 1) {
                $othersNeeded = $staffCount - 1;
                $shuffledOthers = $otherStaff->shuffle()->take($othersNeeded);
                foreach ($shuffledOthers as $s) {
                    $staffIds[] = $s->id;
                }
            }

            // Build items from category spec
            $items = [];
            $subtotal = 0;
            foreach ($categorySpec as [$categoryName, $quantity]) {
                $pool = $servicePools[$categoryName];
                if ($pool->isEmpty()) {
                    $this->command->error("No services available for category '{$categoryName}'");
                    continue;
                }

                // Each item: qty=1, pick random service from pool
                $remaining = $quantity;
                while ($remaining > 0) {
                    $svc = $pool->random();
                    $itemTotal = $svc->price; // qty=1
                    $items[] = [
                        'service_id' => $svc->id,
                        'quantity' => 1,
                        'price' => $svc->price,
                        'total' => $itemTotal,
                    ];
                    $subtotal += $itemTotal;
                    $remaining--;
                }
            }

            // Calculate discount amount
            $discountAmount = 0;
            if ($discountType === 'percentage') {
                $discountAmount = $subtotal * ($discount / 100);
            } else {
                $discountAmount = $discount;
            }

            $grandTotal = ($subtotal - $discountAmount) + $transportFee;

            // Create service order
            $order = ServiceOrder::create([
                'so_number' => $soNumber,
                'customer_id' => $customer->id,
                'address_id' => $addressId,
                'work_date' => $workDate,
                'work_time' => $time . ':00',
                'status' => 'invoiced',
                'work_notes' => '',
                'created_by' => 1,
            ]);

            // Attach staff
            $order->staff()->attach($staffIds);

            // Create items
            foreach ($items as $itemData) {
                ServiceOrderItem::create([
                    'service_order_id' => $order->id,
                    'service_id' => $itemData['service_id'],
                    'quantity' => $itemData['quantity'],
                    'price' => $itemData['price'],
                    'total' => $itemData['total'],
                ]);
            }

            // Create invoice (status=paid)
            Invoice::create([
                'service_order_id' => $order->id,
                'invoice_number' => 'INV-PLDT-' . str_pad($order->id, 4, '0', STR_PAD_LEFT),
                'issue_date' => $workDate,
                'due_date' => $workDate,
                'subtotal' => $subtotal,
                'discount' => $discountType === 'percentage' ? $discount : $discountAmount,
                'discount_type' => $discountType,
                'transport_fee' => $transportFee,
                'grand_total' => $grandTotal,
                'status' => 'paid',
                'paid_amount' => $grandTotal,
                'notes' => '',
            ]);

            $orderCount++;
            $categoryLabels = collect($categorySpec)->map(fn($s) => $s[0])->join(' + ');
            $staffNames = Staff::withoutGlobalScopes()->whereIn('id', $staffIds)->pluck('name')->join(', ');
            $this->command->info(
                "  Day {$day} {$time} | {$customer->name} | {$categoryLabels} | " .
                "staff: {$staffNames} | sub={$subtotal} disc={$discountAmount} trans={$transportFee} gt={$grandTotal}"
            );
        }

        $this->command->info("\nTotal orders created: {$orderCount}");
    }

    private function getAddressId(Customer $customer): int
    {
        $address = $customer->addresses()->first();
        if ($address) {
            return $address->id;
        }

        // Create a dummy address if none exists
        $address = Address::create([
            'customer_id' => $customer->id,
            'address' => 'Dummy Address',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'district' => 'Central',
            'subdistrict' => '',
            'postal_code' => '10110',
            'is_primary' => true,
            'created_by' => 1,
        ]);

        return $address->id;
    }
}
