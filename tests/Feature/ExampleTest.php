<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /** -----------------------------------------------------------------------
     * GET /products (INDEX)
     * ------------------------------------------------------------------------ */

    public function test_index_list()
    {
        Product::factory()->count(3)->create();
        $this->getJson('/api/products')
            ->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_index_empty()
    {
        $this->getJson('/api/products')
            ->assertStatus(200)
            ->assertJson([]);
    }

    public function test_index_structure()
    {
        Product::factory()->create();
        $this->getJson('/api/products')
            ->assertJsonStructure([['id', 'name', 'price', 'created_at']]);
    }

    /** -----------------------------------------------------------------------
     * POST /products (STORE)
     * ------------------------------------------------------------------------ */

    public function test_store_success()
    {
        $data = ['name' => 'Laptop', 'price' => 500000];
        $this->postJson('/api/products', $data)
            ->assertStatus(201)
            ->assertJsonPath('name', 'Laptop');
        $this->assertDatabaseHas('products', ['name' => 'Laptop']);
    }

    public function test_store_missing_name()
    {
        $this->postJson('/api/products', ['price' => 100])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_store_negative_price()
    {
        $this->postJson('/api/products', ['name' => 'Ingyen', 'price' => -10])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['price']);
    }

    /** -----------------------------------------------------------------------
     * GET /products/{id} (SHOW)
     * ------------------------------------------------------------------------ */

    public function test_show_success()
    {
        $product = Product::factory()->create(['name' => 'Monitor']);
        $this->getJson("/api/products/{$product->id}")
            ->assertStatus(200)
            ->assertJsonPath('name', 'Monitor');
    }

    public function test_show_not_found()
    {
        $this->getJson('/api/products/999999')
            ->assertStatus(404);
    }

    public function test_show_contains_id()
    {
        $product = Product::factory()->create();
        $this->getJson("/api/products/{$product->id}")
            ->assertJsonFragment(['id' => $product->id]);
    }

    /** -----------------------------------------------------------------------
     * PUT /products/{id} (UPDATE)
     * ------------------------------------------------------------------------ */

    public function test_update_success()
    {
        $product = Product::factory()->create(['name' => 'Régi Gép']);
        $this->putJson("/api/products/{$product->id}", ['name' => 'Új Gép', 'price' => 150])
            ->assertStatus(200);
        $this->assertDatabaseHas('products', ['name' => 'Új Gép']);
    }

    public function test_update_not_found()
    {
        $this->putJson('/api/products/999', ['name' => 'Hiba'])
            ->assertStatus(404);
    }

    public function test_update_short_name()
    {
        $product = Product::factory()->create();
        $this->putJson("/api/products/{$product->id}", ['name' => 'a'])
            ->assertStatus(422);
    }

    /** -----------------------------------------------------------------------
     * DELETE /products/{id} (DESTROY)
     * ------------------------------------------------------------------------ */

    public function test_destroy_success()
    {
        $product = Product::factory()->create();
        $this->deleteJson("/api/products/{$product->id}")
            ->assertStatus(204);
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_destroy_not_found()
    {
        $this->deleteJson('/api/products/888')
            ->assertStatus(404);
    }

    public function test_destroy_removed()
    {
        $product = Product::factory()->create();
        $this->deleteJson("/api/products/{$product->id}");
        $this->assertNull(Product::find($product->id));
    }
}
