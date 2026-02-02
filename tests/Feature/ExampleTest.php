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

    public function test_index_listazza_az_osszes_terméket()
    {
        Product::factory()->count(3)->create();
        $this->getJson('/api/products')
            ->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_index_ures_listat_ad_vissza_ha_nincs_termek()
    {
        $this->getJson('/api/products')
            ->assertStatus(200)
            ->assertJson([]);
    }

    public function test_index_a_valaszstruktura_megfelelo_mezokat_tartalmaz()
    {
        Product::factory()->create();
        $this->getJson('/api/products')
            ->assertJsonStructure([['id', 'name', 'price', 'created_at']]);
    }

    /** -----------------------------------------------------------------------
     * POST /products (STORE)
     * ------------------------------------------------------------------------ */

    public function test_store_sikeresen_letrehoz_egy_terméket_valid_adatokkal()
    {
        $data = ['name' => 'Laptop', 'price' => 500000];
        $this->postJson('/api/products', $data)
            ->assertStatus(201)
            ->assertJsonPath('name', 'Laptop');
        $this->assertDatabaseHas('products', ['name' => 'Laptop']);
    }

    public function test_store_hibauzenetet_ad_ha_hianyzik_a_kotelez_nev_mezo()
    {
        $this->postJson('/api/products', ['price' => 100])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_store_nem_enged_negativ_arat_megadni()
    {
        $this->postJson('/api/products', ['name' => 'Ingyen', 'price' => -10])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['price']);
    }

    /** -----------------------------------------------------------------------
     * GET /products/{id} (SHOW)
     * ------------------------------------------------------------------------ */

    public function test_show_megjeleníti_a_kert_terméket()
    {
        $product = Product::factory()->create(['name' => 'Monitor']);
        $this->getJson("/api/products/{$product->id}")
            ->assertStatus(200)
            ->assertJsonPath('name', 'Monitor');
    }

    public function test_show_404_et_dob_ha_a_termek_nem_letezik()
    {
        $this->getJson('/api/products/999999')
            ->assertStatus(404);
    }

    public function test_show_a_valasz_tartalmazza_a_kert_id_t()
    {
        $product = Product::factory()->create();
        $this->getJson("/api/products/{$product->id}")
            ->assertJsonFragment(['id' => $product->id]);
    }

    /** -----------------------------------------------------------------------
     * PUT /products/{id} (UPDATE)
     * ------------------------------------------------------------------------ */

    public function test_update_sikeresen_frissiti_a_terméket()
    {
        $product = Product::factory()->create(['name' => 'Régi Gép']);
        $this->putJson("/api/products/{$product->id}", ['name' => 'Új Gép', 'price' => 150])
            ->assertStatus(200);
        $this->assertDatabaseHas('products', ['name' => 'Új Gép']);
    }

    public function test_update_404_et_dob_ha_nem_letezо_terméket_akarunk_frissiteni()
    {
        $this->putJson('/api/products/999', ['name' => 'Hiba'])
            ->assertStatus(404);
    }

    public function test_update_hiba_ha_tul_rovid_nevet_adunk_meg_frissíteskor()
    {
        $product = Product::factory()->create();
        $this->putJson("/api/products/{$product->id}", ['name' => 'a'])
            ->assertStatus(422);
    }

    /** -----------------------------------------------------------------------
     * DELETE /products/{id} (DESTROY)
     * ------------------------------------------------------------------------ */

    public function test_destroy_sikeresen_torlai_a_terméket_es_204_et_ad()
    {
        $product = Product::factory()->create();
        $this->deleteJson("/api/products/{$product->id}")
            ->assertStatus(204);
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_destroy_404_et_dob_ha_mar_torolt_vagy_nem_letezо_terméket_torolnenk()
    {
        $this->deleteJson('/api/products/888')
            ->assertStatus(404);
    }

    public function test_destroy_a_torles_utan_a_termek_tenyleges_eltunnik_az_adatbazisbol()
    {
        $product = Product::factory()->create();
        $this->deleteJson("/api/products/{$product->id}");
        $this->assertNull(Product::find($product->id));
    }
}
