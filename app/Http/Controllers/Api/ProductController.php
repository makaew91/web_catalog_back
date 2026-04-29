<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $products = Product::query()
            ->with('category')
            ->when($request->integer('category_id'), fn ($q, $id) => $q->where('category_id', $id))
            ->when($request->string('search')->trim()->value(), function ($q, $search) {
                $q->where('name', 'ilike', "%{$search}%");
            })
            ->latest('id')
            ->paginate($request->integer('per_page', 12));

        return ProductResource::collection($products);
    }

    public function show(Product $product): ProductResource
    {
        return ProductResource::make($product->load('category'));
    }
}
