<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tests\TestCase;

class ProductModelTest extends TestCase
{
    public function test_product_fillable_contains_expected_attributes(): void
    {
        $this->assertEqualsCanonicalizing(
            ['nama', 'harga', 'deskripsi', 'foto', 'user_id', 'category_id'],
            (new Product)->getFillable()
        );
    }

    public function test_product_relations_are_defined(): void
    {
        $product = new Product;

        $this->assertInstanceOf(BelongsTo::class, $product->user());
        $this->assertInstanceOf(BelongsTo::class, $product->category());
        $this->assertInstanceOf(HasMany::class, (new Category)->products());
        $this->assertInstanceOf(HasMany::class, (new User)->products());
    }

    public function test_is_admin_checks_role(): void
    {
        $this->assertTrue((new User(['role' => 'admin']))->isAdmin());
        $this->assertFalse((new User(['role' => 'user']))->isAdmin());
        $this->assertFalse((new User)->isAdmin());
    }
}
