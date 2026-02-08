<?php

namespace Tests\Feature;

use App\Actions\Wishlist\AddToWishlist;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    private AddToWishlist $action;

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
                '*' => ['id', 'name', 'price', 'description', 'in_wishlist', 'created_at'],
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

    public function test_product_in_wishlist_returns_true(): void
    {
        $user = $this->createUser();
        $product = $this->createProduct(['name' => 'Wishlist Product']);

        $this->action->execute([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $response = $this->actingAs($user)
            ->getJson('/api/products');

        $response->assertStatus(200);
        $response->assertJsonPath('data.0.in_wishlist', true);
        $response->assertJsonPath('data.0.id', $product->id);
        $response->assertJsonPath('data.0.name', 'Wishlist Product');
    }

    public function test_product_not_in_wishlist_returns_false(): void
    {
        $user = $this->createUser();
        $product = $this->createProduct(['name' => 'Regular Product']);

        $response = $this->actingAs($user)
            ->getJson('/api/products');

        $response->assertStatus(200);
        $response->assertJsonPath('data.0.in_wishlist', false);
        $response->assertJsonPath('data.0.id', $product->id);
        $response->assertJsonPath('data.0.name', 'Regular Product');
    }

    public function test_products_list_shows_correct_wishlist_status_for_multiple_products(): void
    {
        $user = $this->createUser();
        $productInWishlist = $this->createProduct(['name' => 'Wishlisted Product']);
        $productNotInWishlist = $this->createProduct(['name' => 'Non-Wishlisted Product']);

        // Use AddToWishlist action to add only the first product
        $this->action->execute([
            'user_id' => $user->id,
            'product_id' => $productInWishlist->id,
        ]);

        $response = $this->actingAs($user)
            ->getJson('/api/products');

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data');

        $response->assertJsonPath('data.0.in_wishlist', true);
        $response->assertJsonPath('data.1.in_wishlist', false);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = app(AddToWishlist::class);
    }
}
