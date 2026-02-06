<?php

namespace Tests\Shared;

use App\Models\Product;
use App\Models\User;

trait PreparesData
{
    protected function validRegistrationData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], $overrides);
    }

    protected function validLoginData(array $overrides = []): array
    {
        return array_merge([
            'email' => 'john@example.com',
            'password' => 'password123',
        ], $overrides);
    }

    protected function validWishlistData(array $overrides = []): array
    {
        $product = Product::factory()->create();

        return array_merge([
            'product_id' => $product->id,
        ], $overrides);
    }

    protected function createUser(array $attributes = []): User
    {
        return User::factory()->create($attributes);
    }

    protected function createProduct(array $attributes = []): Product
    {
        return Product::factory()->create($attributes);
    }
}
