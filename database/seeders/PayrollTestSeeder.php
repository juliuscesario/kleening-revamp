<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\ServiceOrder;
use App\Models\ServiceOrderItem;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Staff;
use App\Models\Invoice;
use App\Models\Scopes\AreaScope;
use Carbon\Carbon;

class PayrollTestSeeder extends Seeder
{
    // Septiyan
    const STAFF_ID = 11;

    // Scenario definitions
    private array $scenarios = [
        // Day 21 — 1 booking: HV only, solo
        [
            'date' => '2026-04-21',
            'time' => '09:00:00',
            'customer' => 'Test Customer A',
            'groups' => [
                ['category' => 'HV', 'items' => [
                    ['qty' => 1, 'price' => 120000, 'total' => 120000],
                    ['qty' => 1, 'price' => 115000, 'total' => 115000],
                    ['qty' => 1, 'price' => 115000, 'total' => 115000],
                ]],
            ],
            'staff' => [self::STAFF_ID],
            'transport' => 50000,
            'discount' => 0,
            'discount_type' => 'fixed',
        ],
        // Day 22 — 2 bookings
        [
            'date' => '2026-04-22',
            'time' => '08:00:00',
            'customer' => 'Test Customer B',
            'groups' => [
                ['category' => 'CC', 'items' => [
                    ['qty' => 1, 'price' => 200000, 'total' => 200000],
                    ['qty' => 1, 'price' => 200000, 'total' => 200000],
                ]],
            ],
            'staff' => [self::STAFF_ID, 12],
            'transport' => 100000,
            'discount' => 50000,
            'discount_type' => 'fixed',
        ],
        [
            'date' => '2026-04-22',
            'time' => '10:00:00',
            'customer' => 'Test Customer C',
            'groups' => [
                ['category' => 'HV', 'items' => [
                    ['qty' => 1, 'price' => 100000, 'total' => 100000],
                ]],
            ],
            'staff' => [self::STAFF_ID],
            'transport' => 50000,
            'discount' => 0,
            'discount_type' => 'fixed',
        ],
        // Day 23 — SPLIT JOB: HV + GC
        [
            'date' => '2026-04-23',
            'time' => '09:00:00',
            'customer' => 'Test Customer D',
            'groups' => [
                ['category' => 'HV', 'items' => [
                    ['qty' => 1, 'price' => 100000, 'total' => 100000],
                    ['qty' => 1, 'price' => 100000, 'total' => 100000],
                ]],
                ['category' => 'GC', 'items' => [
                    ['qty' => 1, 'price' => 250000, 'total' => 250000],
                ]],
            ],
            'staff' => [self::STAFF_ID, 12],
            'transport' => 75000,
            'discount' => 0,
            'discount_type' => 'fixed',
        ],
        // Day 24 — 3 bookings: tests harian 80, 25, 25
        [
            'date' => '2026-04-24',
            'time' => '08:00:00',
            'customer' => 'Test Customer E',
            'groups' => [
                ['category' => 'Poles', 'items' => [
                    ['qty' => 1, 'price' => 300000, 'total' => 300000],
                ]],
            ],
            'staff' => [self::STAFF_ID],
            'transport' => 50000,
            'discount' => 0,
            'discount_type' => 'fixed',
        ],
        [
            'date' => '2026-04-24',
            'time' => '10:00:00',
            'customer' => 'Test Customer F',
            'groups' => [
                ['category' => 'CID', 'items' => [
                    ['qty' => 1, 'price' => 250000, 'total' => 250000],
                    ['qty' => 1, 'price' => 250000, 'total' => 250000],
                ]],
            ],
            'staff' => [self::STAFF_ID, 12, 13],
            'transport' => 100000,
            'discount' => 100000,
            'discount_type' => 'fixed',
        ],
        [
            'date' => '2026-04-24',
            'time' => '13:00:00',
            'customer' => 'Test Customer G',
            'groups' => [
                ['category' => 'HV', 'items' => [
                    ['qty' => 1, 'price' => 150000, 'total' => 150000],
                ]],
            ],
            'staff' => [self::STAFF_ID],
            'transport' => 50000,
            'discount' => 0,
            'discount_type' => 'fixed',
        ],
        // Day 25 — SPLIT JOB: HV + Poles with discount
        [
            'date' => '2026-04-25',
            'time' => '09:00:00',
            'customer' => 'Test Customer H',
            'groups' => [
                ['category' => 'HV', 'items' => [
                    ['qty' => 1, 'price' => 100000, 'total' => 100000],
                    ['qty' => 1, 'price' => 100000, 'total' => 100000],
                    ['qty' => 1, 'price' => 100000, 'total' => 100000],
                ]],
                ['category' => 'Poles', 'items' => [
                    ['qty' => 1, 'price' => 100000, 'total' => 100000],
                    ['qty' => 1, 'price' => 100000, 'total' => 100000],
                ]],
            ],
            'staff' => [self::STAFF_ID],
            'transport' => 50000,
            'discount' => 50000,
            'discount_type' => 'fixed',
        ],
        // Day 26 — 2 bookings
        [
            'date' => '2026-04-26',
            'time' => '08:00:00',
            'customer' => 'Test Customer I',
            'groups' => [
                ['category' => 'GC', 'items' => [
                    ['qty' => 1, 'price' => 300000, 'total' => 300000],
                    ['qty' => 1, 'price' => 300000, 'total' => 300000],
                ]],
            ],
            'staff' => [self::STAFF_ID, 12],
            'transport' => 100000,
            'discount' => 0,
            'discount_type' => 'fixed',
        ],
        [
            'date' => '2026-04-26',
            'time' => '10:00:00',
            'customer' => 'Test Customer J',
            'groups' => [
                ['category' => 'HV', 'items' => [
                    ['qty' => 1, 'price' => 160000, 'total' => 160000],
                    ['qty' => 1, 'price' => 160000, 'total' => 160000],
                    ['qty' => 1, 'price' => 160000, 'total' => 160000],
                    ['qty' => 1, 'price' => 160000, 'total' => 160000],
                    ['qty' => 1, 'price' => 160000, 'total' => 160000],
                ]],
            ],
            'staff' => [self::STAFF_ID],
            'transport' => 50000,
            'discount' => 0,
            'discount_type' => 'fixed',
        ],
        // Day 27 — no bookings (gap)
        // Day 28 — SPLIT JOB: 3 categories (HV + GC + Poles)
        [
            'date' => '2026-04-28',
            'time' => '09:00:00',
            'customer' => 'Test Customer K',
            'groups' => [
                ['category' => 'HV', 'items' => [
                    ['qty' => 1, 'price' => 100000, 'total' => 100000],
                    ['qty' => 1, 'price' => 100000, 'total' => 100000],
                ]],
                ['category' => 'GC', 'items' => [
                    ['qty' => 1, 'price' => 300000, 'total' => 300000],
                ]],
                ['category' => 'Poles', 'items' => [
                    ['qty' => 1, 'price' => 150000, 'total' => 150000],
                ]],
            ],
            'staff' => [self::STAFF_ID, 12],
            'transport' => 100000,
            'discount' => 0,
            'discount_type' => 'fixed',
        ],
        // Day 29 — 1 booking, high item count
        [
            'date' => '2026-04-29',
            'time' => '08:00:00',
            'customer' => 'Test Customer L',
            'groups' => [
                ['category' => 'HV', 'items' => [
                    ['qty' => 1, 'price' => 150000, 'total' => 150000],
                    ['qty' => 1, 'price' => 150000, 'total' => 150000],
                    ['qty' => 1, 'price' => 150000, 'total' => 150000],
                    ['qty' => 1, 'price' => 150000, 'total' => 150000],
                    ['qty' => 1, 'price' => 150000, 'total' => 150000],
                    ['qty' => 1, 'price' => 150000, 'total' => 150000],
                    ['qty' => 1, 'price' => 150000, 'total' => 150000],
                    ['qty' => 1, 'price' => 150000, 'total' => 150000],
                    ['qty' => 1, 'price' => 150000, 'total' => 150000],
                    ['qty' => 1, 'price' => 150000, 'total' => 150000],
                ]],
            ],
            'staff' => [self::STAFF_ID],
            'transport' => 50000,
            'discount' => 0,
            'discount_type' => 'fixed',
        ],
        // Day 30 — keep existing data (Ibnu Hafidz + Keilla)
    ];

    // Category name mapping for service lookup
    private array $categoryKeywords = [
        'HV' => 'Hydrovacuum',
        'CC' => 'Premium Wash',
        'GC' => 'General Cleaning',
        'CID' => 'Car Interior Detailing',
        'Poles' => 'Poles',
    ];

    public function run(): void
    {
        $this->command->info('🧹 Payroll Test Seeder — Apr 21-30, 2026 (Septiyan staff_id=' . self::STAFF_ID . ')');

        // Verify staff exists
        $staff = Staff::withoutGlobalScopes()->find(self::STAFF_ID);
        if (!$staff) {
            $this->command->error("Staff ID " . self::STAFF_ID . " (Septiyan) not found!");
            return;
        }
        $this->command->info("✅ Staff found: {$staff->name} (base_harian: {$staff->base_harian})");

        // Build service cache by category
        $services = $this->buildServiceCache();

        $created = 0;

        foreach ($this->scenarios as $scenario) {
            $date = $scenario['date'];
            $time = $scenario['time'];
            $customerName = $scenario['customer'];
            $groups = $scenario['groups'];
            $staffIds = $scenario['staff'];
            $transportFee = $scenario['transport'];
            $discount = $scenario['discount'];
            $discountType = $scenario['discount_type'];

            // 1. Create or find customer + address
            $customer = Customer::withoutGlobalScopes()->firstOrCreate(
                ['name' => $customerName],
                ['phone_number' => '08' . rand(100000000, 999999999)]
            );

            // Create address for this customer
            $areaId = $staff->area_id ?? 1;
            $address = \App\Models\Address::withoutGlobalScopes()->create([
                'customer_id' => $customer->id,
                'area_id' => $areaId,
                'label' => 'Test',
                'contact_name' => $customerName,
                'contact_phone' => '08' . rand(100000000, 999999999),
                'full_address' => "Test Address {$customerName}, Jakarta",
                'lokasi' => "Jakarta",
                'google_maps_link' => 'https://maps.google.com/?q=-6.2,106.8',
            ]);

            // 2. Calculate subtotal from all items
            $subtotal = 0;
            foreach ($groups as $group) {
                foreach ($group['items'] as $item) {
                    $subtotal += $item['total'];
                }
            }

            // 3. Calculate grand_total
            $discountAmount = $discountType === 'percentage'
                ? $subtotal * ($discount / 100)
                : $discount;
            $grandTotal = ($subtotal - $discountAmount) + $transportFee;

            // 4. Create service order
            $soNumber = 'SO-TEST-' . strtoupper(substr(md5($date . $time . $customerName), 0, 8));

            $order = ServiceOrder::withoutGlobalScopes()->create([
                'so_number' => $soNumber,
                'customer_id' => $customer->id,
                'address_id' => $address->id,
                'work_date' => $date,
                'work_time' => $time,
                'status' => ServiceOrder::STATUS_INVOICED,
                'work_notes' => "Test payroll order — {$customerName}",
                'staff_notes' => null,
                'created_by' => 1,
            ]);

            $this->command->info("📋 {$date} {$time} — {$customerName} ({$soNumber}) subtotal=" . number_format($subtotal) . " grand_total=" . number_format($grandTotal));

            // 5. Create items for each group
            $itemCount = 0;
            foreach ($groups as $group) {
                $categoryKeyword = $group['category'];
                $service = $this->findServiceForCategory($categoryKeyword, $services);

                if (!$service) {
                    $this->command->error("  ❌ No service found for category '{$categoryKeyword}'");
                    $order->delete();
                    return;
                }

                foreach ($group['items'] as $itemData) {
                    ServiceOrderItem::create([
                        'service_order_id' => $order->id,
                        'service_id' => $service->id,
                        'quantity' => $itemData['qty'],
                        'price' => $itemData['price'],
                        'total' => $itemData['total'],
                    ]);
                    $itemCount++;
                }
            }

            // 6. Assign staff
            foreach ($staffIds as $sid) {
                $order->staff()->attach($sid);
            }

            // 7. Create invoice
            Invoice::withoutGlobalScopes()->create([
                'service_order_id' => $order->id,
                'invoice_number' => 'INV-TEST-' . strtoupper(substr(md5($soNumber), 0, 8)),
                'issue_date' => $date,
                'due_date' => Carbon::parse($date)->addDays(7)->format('Y-m-d'),
                'subtotal' => $subtotal,
                'discount' => $discount,
                'discount_type' => $discountType,
                'transport_fee' => $transportFee,
                'grand_total' => $grandTotal,
                'dp_type' => null,
                'dp_value' => 0,
                'total_after_dp' => $grandTotal,
                'paid_amount' => $grandTotal,
                'status' => 'paid',
                'notes' => 'Test payroll invoice',
                'signature' => null,
            ]);

            $created++;
            $groupDesc = implode(' + ', array_map(fn($g) => $g['category'], $groups));
            $this->command->info("   ✅ {$itemCount} items ({$groupDesc}), staff=" . count($staffIds) . ", transport=" . number_format($transportFee) . ", discount=" . number_format($discount));
        }

        $this->command->info("\n🎉 Done! Created {$created} service orders for Septiyan.");
        $this->command->info("Download payroll from: /payroll → select Septiyan → Apr 2026, Period 3");
    }

    private function buildServiceCache(): array
    {
        $services = [];
        $categories = ServiceCategory::all();

        foreach ($categories as $cat) {
            $services[$cat->id] = $cat->services;
        }

        return $services;
    }

    private function findServiceForCategory(string $keyword, array $services)
    {
        // Try to find by keyword match in category name
        foreach ($services as $categoryId => $serviceList) {
            $category = ServiceCategory::find($categoryId);
            if (!$category) continue;

            $catNameLower = strtolower($category->name);

            // Match keywords
            $matches = false;
            switch (strtolower($keyword)) {
                case 'hv':
                    $matches = str_contains($catNameLower, 'hydro') || str_contains($catNameLower, 'vacuum');
                    break;
                case 'cc':
                    $matches = str_contains($catNameLower, 'premium') || str_contains($catNameLower, 'car wash');
                    break;
                case 'gc':
                    $matches = str_contains($catNameLower, 'general') || str_contains($catNameLower, 'cleaning');
                    break;
                case 'cid':
                    $matches = str_contains($catNameLower, 'interior') || str_contains($catNameLower, 'detailing');
                    break;
                case 'poles':
                    $matches = str_contains($catNameLower, 'poles');
                    break;
                default:
                    $matches = str_contains($catNameLower, strtolower($keyword));
            }

            if ($matches && $serviceList->count() > 0) {
                return $serviceList->first();
            }
        }

        return null;
    }
}
