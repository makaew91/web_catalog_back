<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

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

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = Product::create($request->validated())->load('category');

        return ProductResource::make($product)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(UpdateProductRequest $request, Product $product): ProductResource
    {
        $product->update($request->validated());

        return ProductResource::make($product->load('category'));
    }

    public function destroy(Product $product): Response
    {
        $product->delete();

        return response()->noContent();
    }
}
