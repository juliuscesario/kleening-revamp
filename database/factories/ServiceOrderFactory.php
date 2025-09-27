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
            'status' => fake()->randomElement(['booked', 'invoiced', 'done', 'cancelled','proses']),
            'work_notes' => fake()->sentence(),
            'staff_notes' => fake()->sentence(),
            'created_by' => (User::where('role', 'admin')->first() ?? User::factory()->create(['role' => 'admin']))->id,
            'so_number' => 'SO-' . fake()->unique()->randomNumber(8),
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (ServiceOrder $so) {
            // Ambil 1-3 layanan secara acak
            $services = Service::inRandomOrder()->limit(rand(1, 3))->get();
            foreach ($services as $service) {
                $so->items()->create([
                    'service_id' => $service->id,
                    'quantity' => rand(1, 2),
                    'price' => $service->price,
                    'total' => $service->price * rand(1, 2),
                ]);
            }

            // Tugaskan 1-2 staff secara acak
            $staff = Staff::inRandomOrder()->limit(rand(1, 2))->get();
            $so->staff()->attach($staff->pluck('id'));
        });
    }
}
