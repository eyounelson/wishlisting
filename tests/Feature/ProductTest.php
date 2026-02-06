<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_list_products(): void
    {
        $user = $this->createUser();
        $this->createProduct(['name' => 'Product 1']);
        $this->createProduct(['name' => 'Product 2']);

        $response = $this->actingAs($user)
            ->getJson('/api/products');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'price', 'description', 'created_at'],
            ],
            'links',
            'meta',
        ]);
        $response->assertJsonCount(2, 'data');
    }

    public function test_products_are_paginated_with_15_per_page(): void
    {
        $user = $this->createUser();
        Product::factory(20)->create();

        $response = $this->actingAs($user)
            ->getJson('/api/products');

        $response->assertStatus(200);
        $response->assertJsonCount(15, 'data');
        $response->assertJsonPath('meta.per_page', 15);
        $response->assertJsonPath('meta.total', 20);
    }

    public function test_unauthenticated_user_cannot_list_products(): void
    {
        $response = $this->getJson('/api/products');

        $response->assertStatus(401);
    }
}
