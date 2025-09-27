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
use Carbon\Carbon;

class DummySeptember2025Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // --- Prerequisite Data ---
        $this->command->info('Generating prerequisite data...');
        
        ServiceCategory::factory(5)->create()->each(function ($category) {
            Service::factory(rand(3, 6))->create(['category_id' => $category->id]);
        });

        Customer::factory(50)->create()->each(function ($customer) {
            Address::factory(rand(1, 3))->create(['customer_id' => $customer->id]);
        });

        Staff::factory(20)->create();

        // --- Main Seeder Logic ---
        $this->command->info('Generating dummy data for September 2025...');

        for ($i = 0; $i < 200; $i++) {
            $serviceOrder = ServiceOrder::factory()->create([
                'status' => 'invoiced',
                'work_date' => Carbon::create(2025, 9, rand(1, 30)),
            ]);

            $subtotal = $serviceOrder->items->sum('total');
            $transport_fee = rand(10000, 50000);
            $grand_total = $subtotal + $transport_fee;

            $invoice = Invoice::create([
                'service_order_id' => $serviceOrder->id,
                'invoice_number' => 'INV/' . Carbon::now()->year . '/' . ($i + 1),
                'issue_date' => $serviceOrder->work_date->addDay(),
                'due_date' => $serviceOrder->work_date->addDays(7),
                'subtotal' => $subtotal,
                'transport_fee' => $transport_fee,
                'grand_total' => $grand_total,
                'status' => 'new',
            ]);

            if (rand(1, 4) <= 3) { // 75% chance
                $invoice->status = Invoice::STATUS_PAID;
                $invoice->save();

                Payment::create([
                    'invoice_id' => $invoice->id,
                    'amount' => $invoice->grand_total,
                    'payment_date' => $invoice->issue_date->addDays(rand(1, 3)),
                    'payment_method' => 'Transfer',
                ]);
            } else { // 25% chance
                $invoice->status = Invoice::STATUS_OVERDUE;
                $invoice->save();
            }
        }

        $this->command->info('Dummy data generation for September 2025 completed.');
    }
}
