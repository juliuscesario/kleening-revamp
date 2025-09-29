<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ServiceCategory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => ServiceCategory::factory(),
            'name' => 'Layanan ' . fake()->words(3, true),
            'price' => fake()->numberBetween(10, 50) * 10000,
            'cost' => 100000,
            'description' => fake()->sentence(),
        ];
    }
}
