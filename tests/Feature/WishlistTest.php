<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistTest extends TestCase
{
    use RefreshDatabase;

    // ========================================
    // VIEW WISHLIST TESTS
    // ========================================

    public function test_authenticated_user_can_view_their_wishlist(): void
    {
        $user = $this->createUser();
        $product = $this->createProduct();
        Wishlist::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $response = $this->actingAs($user)
            ->getJson('/api/wishlist');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'user_id',
                    'product' => ['id', 'name', 'price', 'description'],
                    'created_at',
                ],
            ],
        ]);
        $response->assertJsonCount(1, 'data');
    }

    public function test_wishlist_returns_only_authenticated_users_items(): void
    {
        $userA = $this->createUser(['email' => 'usera@example.com']);
        $userB = $this->createUser(['email' => 'userb@example.com']);
        $product1 = $this->createProduct();
        $product2 = $this->createProduct();

        Wishlist::create(['user_id' => $userA->id, 'product_id' => $product1->id]);
        Wishlist::create(['user_id' => $userB->id, 'product_id' => $product2->id]);

        $response = $this->actingAs($userA)
            ->getJson('/api/wishlist');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.user_id', $userA->id);
    }

    public function test_unauthenticated_user_cannot_view_wishlist(): void
    {
        $response = $this->getJson('/api/wishlist');

        $response->assertStatus(401);
    }

    // ========================================
    // ADD TO WISHLIST TESTS
    // ========================================

    public function test_add_to_wishlist_validation_passes_with_valid_product_id(): void
    {
        $user = $this->createUser();
        $product = $this->createProduct();

        $response = $this->actingAs($user)
            ->postJson('/api/wishlist', ['product_id' => $product->id]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'user_id',
                'product' => ['id', 'name', 'price', 'description'],
                'created_at',
            ],
        ]);
        $this->assertDatabaseHas('wishlists', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_add_to_wishlist_product_id_is_required(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)
            ->postJson('/api/wishlist', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['product_id' => 'The product id field is required.']);
    }

    public function test_add_to_wishlist_product_id_must_exist_in_database(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)
            ->postJson('/api/wishlist', ['product_id' => 99999]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['product_id' => 'The selected product id is invalid.']);
    }

    public function test_adding_duplicate_product_to_wishlist_is_idempotent(): void
    {
        $user = $this->createUser();
        $product = $this->createProduct();

        // Add product first time
        $response1 = $this->actingAs($user)
            ->postJson('/api/wishlist', ['product_id' => $product->id]);
        $response1->assertStatus(201);

        // Add same product second time
        $response2 = $this->actingAs($user)
            ->postJson('/api/wishlist', ['product_id' => $product->id]);
        $response2->assertStatus(201);

        // Verify only one record exists
        $this->assertDatabaseCount('wishlists', 1);
    }

    public function test_unauthenticated_user_cannot_add_to_wishlist(): void
    {
        $product = $this->createProduct();

        $response = $this->postJson('/api/wishlist', ['product_id' => $product->id]);

        $response->assertStatus(401);
    }

    // ========================================
    // REMOVE FROM WISHLIST TESTS
    // ========================================

    public function test_user_can_remove_their_own_wishlist_item(): void
    {
        $user = $this->createUser();
        $product = $this->createProduct();
        $wishlist = Wishlist::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $response = $this->actingAs($user)
            ->deleteJson("/api/wishlist/{$wishlist->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('wishlists', [
            'id' => $wishlist->id,
        ]);
    }

    public function test_user_cannot_remove_another_users_wishlist_item(): void
    {
        $userA = $this->createUser(['email' => 'usera@example.com']);
        $userB = $this->createUser(['email' => 'userb@example.com']);
        $product = $this->createProduct();
        $wishlist = Wishlist::create([
            'user_id' => $userA->id,
            'product_id' => $product->id,
        ]);

        $response = $this->actingAs($userB)
            ->deleteJson("/api/wishlist/{$wishlist->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('wishlists', [
            'id' => $wishlist->id,
        ]);
    }

    public function test_removing_nonexistent_wishlist_item_returns_404(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)
            ->deleteJson('/api/wishlist/99999');

        $response->assertStatus(404);
    }

    public function test_unauthenticated_user_cannot_remove_from_wishlist(): void
    {
        $user = $this->createUser();
        $product = $this->createProduct();
        $wishlist = Wishlist::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $response = $this->deleteJson("/api/wishlist/{$wishlist->id}");

        $response->assertStatus(401);
    }

    // ========================================
    // CASCADE DELETE TESTS (EDGE CASES)
    // ========================================

    public function test_deleting_product_removes_all_wishlist_entries(): void
    {
        $user1 = $this->createUser(['email' => 'user1@example.com']);
        $user2 = $this->createUser(['email' => 'user2@example.com']);
        $product = $this->createProduct();

        Wishlist::create(['user_id' => $user1->id, 'product_id' => $product->id]);
        Wishlist::create(['user_id' => $user2->id, 'product_id' => $product->id]);

        $this->assertDatabaseCount('wishlists', 2);

        $product->delete();

        $this->assertDatabaseCount('wishlists', 0);
    }

    public function test_deleting_user_removes_their_wishlist_entries(): void
    {
        $user = $this->createUser();
        $product1 = $this->createProduct();
        $product2 = $this->createProduct();

        Wishlist::create(['user_id' => $user->id, 'product_id' => $product1->id]);
        Wishlist::create(['user_id' => $user->id, 'product_id' => $product2->id]);

        $this->assertDatabaseCount('wishlists', 2);

        $user->delete();

        $this->assertDatabaseCount('wishlists', 0);
    }
}
