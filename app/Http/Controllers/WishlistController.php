<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddToWishlistRequest;
use App\Http\Resources\WishlistResource;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class WishlistController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $wishlists = auth()->user()->wishlists()->with('product')->get();

        return WishlistResource::collection($wishlists);
    }

    public function store(AddToWishlistRequest $request): WishlistResource
    {
        $wishlist = Wishlist::firstOrCreate([
            'user_id' => $request->user()->id,
            'product_id' => $request->product_id,
        ]);

        return new WishlistResource($wishlist->load('product'));
    }

    public function destroy(Wishlist $wishlist): JsonResponse
    {
        if ($wishlist->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $wishlist->delete();

        return response()->json(null, 204);
    }
}
