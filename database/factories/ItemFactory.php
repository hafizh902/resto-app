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
            'name' => $this->faker->unique()->word(),
            'description' => $this->faker->sentence(),
            'price' => $this->faker->numberBetween(1000, 100000),
            'category_id' => \App\Models\Category::inRandomOrder()->first()->id,
            'img' => $this->faker->imageUrl(640, 480, 'food', true),
            'is_active' => $this->faker->boolean(80),
        ];
    }
}
