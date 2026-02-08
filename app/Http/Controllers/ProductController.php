<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index(): JsonResource
    {
        $products = Product::withWishlistStatus(Auth::user())->paginate(15);

        return ProductResource::collection($products);
    }
}
