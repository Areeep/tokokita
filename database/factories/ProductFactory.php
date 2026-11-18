<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => fake()->words(3, true),
            'harga' => fake()->numberBetween(10, 500) * 1000,
            'deskripsi' => fake()->sentence(),
            'foto' => 'products/noimage.png',
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
        ];
    }
}
