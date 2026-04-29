<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_paginated_products_with_category(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(15)->for($category)->create();

        $response = $this->getJson('/api/products?per_page=10');

        $response
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    ['id', 'name', 'price', 'category_id', 'category' => ['id', 'name']],
                ],
                'meta' => ['current_page', 'total', 'per_page'],
            ])
            ->assertJsonPath('meta.total', 15)
            ->assertJsonPath('meta.per_page', 10);
    }

    public function test_index_filters_by_category(): void
    {
        $first = Category::factory()->create();
        $second = Category::factory()->create();
        Product::factory()->count(3)->for($first)->create();
        Product::factory()->count(2)->for($second)->create();

        $this->getJson("/api/products?category_id={$first->id}")
            ->assertOk()
            ->assertJsonPath('meta.total', 3);
    }

    public function test_show_returns_product(): void
    {
        $product = Product::factory()->for(Category::factory())->create();

        $this->getJson("/api/products/{$product->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $product->id)
            ->assertJsonPath('data.name', $product->name);
    }

    public function test_show_returns_404_for_missing_product(): void
    {
        $this->getJson('/api/products/9999')->assertNotFound();
    }

    public function test_store_requires_authentication(): void
    {
        $this->postJson('/api/products', [])->assertUnauthorized();
    }

    public function test_store_creates_product_with_valid_data(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $category = Category::factory()->create();

        $payload = [
            'name' => 'Apple iPhone 16',
            'description' => 'Smartphone',
            'price' => 999.99,
            'category_id' => $category->id,
        ];

        $this->postJson('/api/products', $payload)
            ->assertCreated()
            ->assertJsonPath('data.name', 'Apple iPhone 16')
            ->assertJsonPath('data.category.id', $category->id);

        $this->assertDatabaseHas('products', [
            'name' => 'Apple iPhone 16',
            'category_id' => $category->id,
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/products', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'price', 'category_id']);
    }

    public function test_store_validates_price_is_positive(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $category = Category::factory()->create();

        $this->postJson('/api/products', [
            'name' => 'Cheap',
            'price' => 0,
            'category_id' => $category->id,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('price');
    }

    public function test_store_validates_category_exists(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/products', [
            'name' => 'Test',
            'price' => 1.0,
            'category_id' => 99999,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('category_id');
    }

    public function test_update_changes_product(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $product = Product::factory()->for(Category::factory())->create();

        $this->patchJson("/api/products/{$product->id}", ['name' => 'Updated'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Updated');

        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Updated']);
    }

    public function test_destroy_soft_deletes_product(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $product = Product::factory()->for(Category::factory())->create();

        $this->deleteJson("/api/products/{$product->id}")->assertNoContent();

        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }
}
