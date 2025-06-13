<?php

namespace App\Http\Controllers\Ecom;


use App\Models\Product\Product;

use App\Services\ProductService;

class EcommerceProductController
{

    public function loadProduct($slug, ProductService $productService)
    {
        $fieldArr = [];

        $product = Product::where('slug', $slug)->firstOrFail();
        $brand = $product->brand()->first();
        $category = $product->categories()->first();
        $productArray = $product->toArray();
        $productArray['brand'] = $brand->name;
        $productArray['brand_logo'] = $brand->image;
        $productArray['brand_slug'] = $brand->slug;
        $productArray['category'] = $category->name;
        $productArray['category_slug'] = $category->slug;

        // Handle Properties
        $prodProperties = $productService->generateCustomProperties($product);
        $productArray['properties'] = $prodProperties['custom_properties'];
        $productArray['featuredProperties'] = $prodProperties['featured_properties'];

        $priceOptionsArr = $productService->handlePriceGroups($product);

        $fieldArr = $productService->handleOptions($product, $fieldArr);
        $fieldArr = $productService->handleAddons($product, $fieldArr);
        $fieldArr = $productService->handleUpsellCategories($product, $fieldArr);

        $productArray['fields'] = array_values(array_merge($priceOptionsArr, $fieldArr));

        return response()->json($productArray);
    }

}
