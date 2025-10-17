<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ServiceOrder;
use App\Models\Customer;
use App\Models\Address;
use App\Models\Staff;
use App\Models\Area;
use App\Models\User;
use Carbon\Carbon;

class DailyBookedServiceOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating 10 daily booked service orders for staff...');

        // Fetch necessary data
        $customers = Customer::all();
        $staffUsers = User::where('role', 'staff')->get();

        if ($customers->isEmpty()) {
            $this->command->error('No customers found. Please run other seeders first.');
            return;
        }
        if ($staffUsers->isEmpty()) {
            $this->command->error('No staff users found. Please run other seeders first.');
            return;
        }

        for ($i = 0; $i < 10; $i++) {
            $customer = $customers->random();
            if ($customer->addresses->isEmpty()) {
                $this->command->warn("Customer {$customer->name} has no addresses, skipping.");
                continue;
            }
            $address = $customer->addresses->random(); // Get a random address for the customer
            $creatorUser = $staffUsers->random(); // A staff user creates the order

            // Find staff available in the service order's area
            $staffInArea = Staff::where('area_id', $address->area_id)
                ->whereHas('user', function ($query) {
                    $query->where('role', 'staff');
                })->get();

            if ($staffInArea->isEmpty()) {
                $this->command->warn("No staff found for area {$address->area->name}, skipping service order creation.");
                continue;
            }

            $serviceOrder = ServiceOrder::factory()->create([
                'customer_id' => $customer->id,
                'address_id' => $address->id,
                'work_date' => Carbon::today(),
                'status' => ServiceOrder::STATUS_BOOKED,
                'created_by' => $creatorUser->id,
                'work_notes' => 'Daily booked service order ' . ($i + 1),
            ]);

            // Attach a random staff member from the correct area to the service order
            $randomStaff = $staffInArea->random();
            $serviceOrder->staff()->sync([$randomStaff->id]);

            $this->command->info("Created Service Order #{$serviceOrder->id} for Customer {$customer->name} in Area {$address->area->name} with Staff {$randomStaff->name}");
        }

        $this->command->info('Daily booked service orders created successfully.');
    }
}
