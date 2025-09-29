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
        $staffMembers = Staff::all(); // Assuming Staff model has a user_id to link to User model

        if ($customers->isEmpty()) {
            $this->command->error('No customers found. Please run other seeders first.');
            return;
        }
        if ($staffUsers->isEmpty() || $staffMembers->isEmpty()) {
            $this->command->error('No staff users or staff members found. Please run other seeders first.');
            return;
        }

        for ($i = 0; $i < 10; $i++) {
            $customer = $customers->random();
            $address = $customer->addresses->random(); // Get a random address for the customer
            $creatorUser = $staffUsers->random(); // A staff user creates the order

            $serviceOrder = ServiceOrder::factory()->create([
                'customer_id' => $customer->id,
                'address_id' => $address->id,
                'work_date' => Carbon::today(),
                'status' => ServiceOrder::STATUS_BOOKED,
                'created_by' => $creatorUser->id,
                'work_notes' => 'Daily booked service order ' . ($i + 1),
            ]);

            // Attach a random staff member to the service order
            $randomStaff = $staffMembers->random();
            $serviceOrder->staff()->sync([$randomStaff->id]);

            $this->command->info("Created Service Order #{$serviceOrder->id} for Customer {$customer->name} in Area {$address->area->name}");
        }

        $this->command->info('Daily booked service orders created successfully.');
    }
}
