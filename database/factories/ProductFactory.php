<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'     => $this->faker->words(3, true),
            'category' => $this->faker->randomElement(['electronics', 'clothing', 'books', 'sports']),
            'brand'    => $this->faker->randomElement(['Samsung', 'Apple', 'Nike', 'Adidas', 'Sony']),
            'price'    => $this->faker->randomFloat(2, 10, 2000),
            'active'   => $this->faker->boolean(80),
        ];
    }
}
