<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, mixed> */
    private function validPayload(array $override = []): array
    {
        return array_merge([
            'nama' => 'Kaos Polos',
            'harga' => 75000,
            'deskripsi' => 'Kaos katun nyaman',
            'foto' => UploadedFile::fake()->image('kaos.jpg'),
        ], $override);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/products')->assertRedirect('/login');
    }

    public function test_authenticated_user_can_see_product_list(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['nama' => 'Sepatu Lari']);

        $this->actingAs($user)
            ->get('/products')
            ->assertOk()
            ->assertSee('Sepatu Lari');
    }

    public function test_user_can_create_product_and_becomes_its_owner(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($user)->post('/products', $this->validPayload([
            'category_id' => $category->id,
        ]));

        $response->assertRedirect(route('products.index'));
        $product = Product::firstWhere('nama', 'Kaos Polos');
        $this->assertNotNull($product);
        $this->assertSame($user->id, $product->user_id);
        $this->assertSame($category->id, $product->category_id);
        Storage::disk('public')->assertExists($product->foto);
    }

    public function test_description_is_optional_on_create_and_update(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/products', $this->validPayload(['deskripsi' => '']))
            ->assertRedirect(route('products.index'));

        $product = Product::firstWhere('nama', 'Kaos Polos');
        $this->assertNull($product->deskripsi);

        $this->actingAs($user)
            ->put("/products/{$product->id}", ['nama' => 'Kaos Polos', 'harga' => 1000, 'deskripsi' => ''])
            ->assertRedirect(route('products.index'));
    }

    public function test_create_is_rejected_when_name_is_missing(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/products', $this->validPayload(['nama' => '']))
            ->assertSessionHasErrors('nama');

        $this->assertDatabaseCount('products', 0);
    }

    public function test_create_is_rejected_when_price_is_not_numeric(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/products', $this->validPayload(['harga' => 'mahal']))
            ->assertSessionHasErrors('harga');
    }

    public function test_create_is_rejected_when_file_is_not_an_image(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/products', $this->validPayload([
                'foto' => UploadedFile::fake()->create('dokumen.pdf', 10, 'application/pdf'),
            ]))
            ->assertSessionHasErrors('foto');
    }

    public function test_create_is_rejected_when_category_does_not_exist(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/products', $this->validPayload(['category_id' => 999]))
            ->assertSessionHasErrors('category_id');
    }

    public function test_owner_can_update_product_and_dots_are_removed_from_price(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->for($user)->create();

        $this->actingAs($user)
            ->put("/products/{$product->id}", [
                'nama' => 'Nama Baru',
                'harga' => '125.000',
            ])
            ->assertRedirect(route('products.index'));

        $product->refresh();
        $this->assertSame('Nama Baru', $product->nama);
        $this->assertSame(125000, $product->harga);
    }

    public function test_other_seller_cannot_edit_update_or_delete_product(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $product = Product::factory()->for($owner)->create();

        $this->actingAs($intruder)->get("/products/{$product->id}/edit")->assertForbidden();
        $this->actingAs($intruder)
            ->put("/products/{$product->id}", ['nama' => 'x', 'harga' => 1])
            ->assertForbidden();
        $this->actingAs($intruder)->delete("/products/{$product->id}")->assertForbidden();

        $this->assertDatabaseHas('products', ['id' => $product->id, 'nama' => $product->nama]);
    }

    public function test_admin_can_delete_any_product_and_its_photo_is_removed(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/foto.jpg', 'isi');
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create(['foto' => 'products/foto.jpg']);

        $this->actingAs($admin)
            ->delete("/products/{$product->id}")
            ->assertRedirect(route('products.index'));

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        Storage::disk('public')->assertMissing('products/foto.jpg');
    }

    public function test_default_photo_is_not_deleted_from_disk(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/noimage.png', 'isi');
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create(['foto' => 'products/noimage.png']);

        $this->actingAs($admin)->delete("/products/{$product->id}");

        Storage::disk('public')->assertExists('products/noimage.png');
    }
}
