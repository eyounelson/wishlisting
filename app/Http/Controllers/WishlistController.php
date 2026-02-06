<?php

namespace App\Http\Controllers;

use App\Actions\Wishlist\AddToWishlist;
use App\Http\Requests\AddToWishlistRequest;
use App\Http\Resources\WishlistResource;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

class WishlistController extends Controller
{
    public function index(): JsonResource
    {
        $wishlists = auth()->user()->wishlists()->with('product')->get();

        return WishlistResource::collection($wishlists);
    }

    public function store(AddToWishlistRequest $request, AddToWishlist $addToWishlist): JsonResponse
    {
        $wishlist = $addToWishlist->execute([
            'user_id' => $request->user()->id,
            'product_id' => $request->product_id,
        ]);

        return WishlistResource::make($wishlist->load(['product']))
            ->response()
            ->setStatusCode(201);
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
