<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Item>
 */
class ItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'price' => $this->faker->numberBetween(1000, 100000),
            'category_id' => $this->faker->numberBetween(1, 5), // Assuming you have 5 categories
            'image' => $this->faker->imageUrl(640, 480, 'food', true),
            'status' => $this->faker->boolean(80),
        ];
    }
}
