<?php

namespace App\Actions\Wishlist;

use App\Models\Wishlist;

class AddToWishlist
{
    public function execute(array $data): Wishlist
    {
        return Wishlist::firstOrCreate([
            'user_id' => $data['user_id'],
            'product_id' => $data['product_id'],
        ]);
    }
}
