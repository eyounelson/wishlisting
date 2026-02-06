<?php

namespace Tests\Unit\Actions\Wishlist;

use App\Actions\Wishlist\AddToWishlist;
use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddToWishlistTest extends TestCase
{
    use RefreshDatabase;

    private AddToWishlist $action;

    public function test_it_creates_wishlist_item_for_new_product(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $wishlist = $this->action->execute([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $this->assertInstanceOf(Wishlist::class, $wishlist);
        $this->assertDatabaseHas('wishlists', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_it_returns_existing_wishlist_item_if_already_exists(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $existing = Wishlist::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $wishlist = $this->action->execute([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        // Should return the same item
        $this->assertEquals($existing->id, $wishlist->id);
        $this->assertDatabaseCount('wishlists', 1);
    }

    public function test_it_handles_multiple_products_for_same_user(): void
    {
        $user = User::factory()->create();
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        $wishlist1 = $this->action->execute([
            'user_id' => $user->id,
            'product_id' => $product1->id,
        ]);

        $wishlist2 = $this->action->execute([
            'user_id' => $user->id,
            'product_id' => $product2->id,
        ]);

        $this->assertNotEquals($wishlist1->id, $wishlist2->id);
        $this->assertDatabaseCount('wishlists', 2);
        $this->assertDatabaseHas('wishlists', [
            'user_id' => $user->id,
            'product_id' => $product1->id,
        ]);
        $this->assertDatabaseHas('wishlists', [
            'user_id' => $user->id,
            'product_id' => $product2->id,
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = app(AddToWishlist::class);
    }
}
