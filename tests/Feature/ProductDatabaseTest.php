<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Pengujian integritas basis data dan performa query akses produk.
 */
class ProductDatabaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_deleting_owner_sets_product_owner_to_null(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->for($user)->create();

        $user->delete();

        $this->assertNull($product->fresh()->user_id);
    }

    public function test_deleting_category_sets_product_category_to_null(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->for($category)->create();

        $category->delete();

        $this->assertNull($product->fresh()->category_id);
    }

    public function test_product_cannot_reference_missing_user(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        Product::factory()->create(['user_id' => 999999]);
    }

    public function test_category_name_must_be_unique(): void
    {
        Category::factory()->create(['nama' => 'Fashion']);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Category::factory()->create(['nama' => 'Fashion']);
    }

    public function test_seeder_creates_admin_sellers_categories_and_products(): void
    {
        $this->seed();

        $this->assertSame(1, User::where('role', 'admin')->count());
        $this->assertSame(3, User::count());
        $this->assertSame(4, Category::count());
        $this->assertSame(30, Product::count());
    }

    public function test_product_list_uses_constant_number_of_queries_and_is_fast(): void
    {
        $user = User::factory()->create();
        Product::factory()->count(60)->create();

        $times = [];
        $tables = [];
        DB::listen(function ($query) use (&$times, &$tables) {
            $times[] = $query->time;
            if (preg_match('/from [`"]?(\w+)[`"]?/i', $query->sql, $m)) {
                $tables[] = $m[1];
            }
        });

        $this->actingAs($user)->get('/products')->assertOk();

        $productQueries = count(array_filter($tables, fn ($t) => $t === 'products'));
        $categoryQueries = count(array_filter($tables, fn ($t) => $t === 'categories'));

        // count + select untuk paginasi, 1 query eager load kategori (bukan 12 query)
        $this->assertSame(2, $productQueries);
        $this->assertSame(1, $categoryQueries);
        $this->assertLessThan(500, array_sum($times), 'Total waktu query melebihi 500 ms');
    }
}
