<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ServiceOrder;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\ServiceCategory;
use App\Models\Service;
use App\Models\Customer;
use App\Models\Address;
use App\Models\Staff;
use App\Models\Area;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class DummySeptember2025Seeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('--- Starting Structured Dummy Data Generation ---');

        // Clean up previous transactional data
        $this->command->warn('Clearing previous transactional data...');
        DB::statement('TRUNCATE TABLE payments, invoices, service_order_items, service_order_staff, service_orders RESTART IDENTITY CASCADE');

        // --- Step 1: Create Prerequisite Data Sequentially ---
        $this->command->info('1. Seeding Core Data (Categories, Services, Staff, Customers)...');
        ServiceCategory::factory(5)->create()->each(function ($category) {
            Service::factory(rand(3, 6))->create(['category_id' => $category->id]);
        });
        Customer::factory(50)->create()->each(function ($customer) {
            Address::factory(rand(1, 3))->create(['customer_id' => $customer->id]);
        });
        // Ensure we have enough staff, bypassing the global scope just in case.
        if (Staff::withoutGlobalScope(AreaScope::class)->count() < 20) {
             Staff::factory(20 - Staff::withoutGlobalScope(AreaScope::class)->count())->create();
        }

        // --- Step 2: Fetch all data needed for the main loop ---
        $this->command->info('2. Fetching data for transaction generation...');
        $customers = Customer::all();
        $staff = Staff::withoutGlobalScope(AreaScope::class)->get();

        if ($staff->isEmpty()) {
            $this->command->error('Staff collection is empty after seeding. Cannot proceed.');
            return;
        }

        // --- Step 3: Main Seeder Logic ---
        $this->command->info('3. Generating transactional data from Jan to Sep 2025...');
        $period = CarbonPeriod::create('2025-01-01', '2025-09-28');
        $invoiceCounter = 1;
        $delayedMayInvoices = [];
        $totalOrdersCreated = 0;

        $monthlyWeights = [ 1 => 1.0, 2 => 0.5, 3 => 1.0, 4 => 1.5, 5 => 1.0, 6 => 1.5, 7 => 1.0, 8 => 1.0, 9 => 1.0 ];
        $baseOrdersPerDay = 7.75;

        foreach ($period as $date) {
            $monthWeight = $monthlyWeights[$date->month] ?? 1.0;
            $ordersPerDay = round($baseOrdersPerDay * $monthWeight * (rand(80, 120) / 100));

            for ($i = 0; $i < $ordersPerDay; $i++) {
                $totalOrdersCreated++;
                $isCancelled = (rand(1, 1000) <= 285); // ~28.5% cancellation rate

                $serviceOrder = ServiceOrder::factory()->create([
                    'status' => $isCancelled ? 'cancelled' : 'done',
                    'work_date' => $date,
                    'customer_id' => $customers->random()->id,
                ]);

                $serviceOrder->staff()->sync($staff->random(rand(1, min(2, $staff->count())))->pluck('id')->toArray());

                if ($isCancelled) continue;

                $subtotal = $serviceOrder->items->sum('total');
                $transport_fee = rand(10000, 50000);
                $invoice = Invoice::create([
                    'service_order_id' => $serviceOrder->id,
                    'invoice_number' => 'INV/' . $date->year . '/' . $invoiceCounter++,
                    'issue_date' => $serviceOrder->work_date->addDays(rand(1, 2)),
                    'due_date' => $serviceOrder->work_date->addDays(7),
                    'subtotal' => $subtotal,
                    'transport_fee' => $transport_fee,
                    'grand_total' => $subtotal + $transport_fee,
                    'status' => 'sent',
                ]);

                $isMay = $date->month == 5;
                $shouldPay = $isMay ? (rand(1, 100) <= 30) : (rand(1, 100) <= 90);

                if ($shouldPay) {
                    Payment::create([
                        'invoice_id' => $invoice->id,
                        'amount' => $invoice->grand_total,
                        'payment_date' => $date->copy()->addDays(rand(2, 6)),
                        'payment_method' => 'Transfer',
                    ]);
                    $invoice->update(['status' => 'paid']);
                } elseif ($isMay) {
                    $delayedMayInvoices[] = $invoice;
                }
            }
        }

        // --- Step 4: Process Delayed Payments ---
        $this->command->info('4. Processing delayed May payments...');
        foreach ($delayedMayInvoices as $invoice) {
            Payment::create([
                'invoice_id' => $invoice->id,
                'amount' => $invoice->grand_total,
                'payment_date' => Carbon::create(2025, 6, rand(1, 30)),
                'payment_method' => 'Transfer',
            ]);
            $invoice->update(['status' => 'paid']);
        }

        $this->command->info("Total orders created: $totalOrdersCreated");
        $this->command->info('--- Structured Dummy Data Generation Completed ---');
    }
}
