<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Mengisi data awal: 1 admin, 2 penjual, 4 kategori, dan 30 produk contoh.
     * Kata sandi seluruh akun contoh adalah "password".
     */
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
        ]);

        $sellers = User::factory()->count(2)->create();

        $categories = collect(['Fashion', 'Elektronik', 'Makanan', 'Rumah Tangga'])
            ->map(fn (string $nama) => Category::create(['nama' => $nama]));

        Product::factory()
            ->count(30)
            ->state(fn () => [
                'user_id' => $sellers->random()->id,
                'category_id' => $categories->random()->id,
            ])
            ->create();
    }
}
