<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Customer;
use App\Models\Area;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Address>
 */
class AddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'area_id' => Area::inRandomOrder()->first()->id,
            'label' => fake()->randomElement(['Rumah', 'Kantor', 'Apartemen']),
            'contact_name' => fake()->name(),
            'contact_phone' => fake()->e164PhoneNumber(),
            'full_address' => fake()->address(),
            'google_maps_link' => fake()->url(),
        ];
    }
}
