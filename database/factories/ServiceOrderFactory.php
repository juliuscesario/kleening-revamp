<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Address;
use App\Models\Customer;
use App\Models\Service;
use App\Models\ServiceOrder;
use App\Models\Staff;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ServiceOrder>
 */
class ServiceOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    public function definition(): array
    {
        return [
            'customer_id' => (Customer::inRandomOrder()->first() ?? Customer::factory()->create())->id,
            'address_id' => (Address::inRandomOrder()->first() ?? Address::factory()->create())->id,
            'work_date' => fake()->dateTimeBetween('-1 month', '+1 month'),
            'work_time' => fake()->time('H:i:s'),
            'status' => fake()->randomElement(['booked', 'invoiced', 'done', 'cancelled','proses']),
            'work_notes' => fake()->sentence(),
            'staff_notes' => fake()->sentence(),
            'created_by' => (User::where('role', 'admin')->first() ?? User::factory()->create(['role' => 'admin']))->id,
            'so_number' => 'SO-' . date('Ymd') . '-' . str_pad(\App\Models\ServiceOrder::count() + 1, 4, '0', STR_PAD_LEFT),
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (ServiceOrder $so) {
            // Ambil 1-4 layanan secara acak
            $quantity = rand(1,2);
            $services = Service::inRandomOrder()->limit(rand(1, 4))->get();
            foreach ($services as $service) {
                $so->items()->create([
                    'service_id' => $service->id,
                    'quantity' => $quantity,
                    'price' => $service->price,
                    'total' => $service->price * $quantity,
                ]);
            }

            // Tugaskan 1-2 staff secara acak
            // Tugaskan 1-2 staff secara acak berdasarkan role 'staff' dan area pelanggan
            $customerAddress = $so->address;
            $customerAreaId = $customerAddress ? $customerAddress->area_id : null;

            if ($customerAreaId) {
                $staff = Staff::whereHas('user', function ($query) {
                    $query->where('role', 'staff');
                })
                    ->where('area_id', $customerAreaId)
                    ->inRandomOrder()
                    ->limit(rand(1, 2))
                    ->get();
                $so->staff()->attach($staff->pluck('id'));
            } else {
                // Fallback if no area is found, assign any staff with 'staff' role
                $staff = Staff::whereHas('user', function ($query) {
                    $query->where('role', 'staff');
                })->inRandomOrder()->limit(rand(1, 2))->get();
                $so->staff()->attach($staff->pluck('id'));
            }
        });
    }
}
