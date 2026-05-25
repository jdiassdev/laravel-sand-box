<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ProductController extends Controller
{
    public function index()
    {
        $products = QueryBuilder::for(Product::class)
            ->allowedFilters(
                AllowedFilter::exact('category'),
                AllowedFilter::exact('brand'),
                AllowedFilter::exact('active'),
                AllowedFilter::partial('name'),
            )
            ->allowedSorts('name', 'price', 'created_at')
            ->allowedIncludes('reviews')
            ->paginate(15)
            ->appends((array) request()->query());

        return response()->json($products);
    }

    public function show(Product $product)
    {
        $product = QueryBuilder::for(Product::where('id', $product->id))
            ->allowedIncludes('reviews')
            ->firstOrFail();

        return response()->json($product);
    }
}
