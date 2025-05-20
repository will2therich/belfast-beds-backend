<?php

namespace App\Http\Controllers;

use App\Models\Product\Product;
use App\Models\Product\ProductCategory;

class EcommerceCategoryController
{



    public function loadCategory($slug)
    {
        $category = ProductCategory::where('slug', $slug)->firstOrFail();
        $childCategories = $category->childCategories()->pluck('id')->toArray();

        $allCategoryIds = array_merge([$category->id], $childCategories);

        $products = Product::query()->whereHas('categories', function ($query) use ($allCategoryIds) {
            $query->whereIn('id', $allCategoryIds);
        })
            ->whereNotNull('starting_price')
            ->with('brand')
            ->get();


        return response()->json([
            'category' => $category->toArray(),
            'products' => $products
        ]);

//        dd($category, $childCategories, $products);
    }
}
