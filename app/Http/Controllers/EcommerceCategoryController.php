<?php

namespace App\Http\Controllers;

use App\Models\Product\Product;
use App\Models\Product\ProductCategory;
use Illuminate\Http\Request;

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
            ->whereNotNull('brand')
            ->with('brand')
            ->get();


        if (empty($category->parent_category_id)) {
            $parentCategory = $category;
        } else {
            $parentCategory = $category->parentCategory;
        }

        $additionalFilters = $parentCategory->filters()->get()->toArray();

        foreach ($additionalFilters as &$additionalFilter) {
            foreach ($additionalFilter['options'] as &$option) {
                $option['label'] = str_replace('{{ category }}', $parentCategory->name, $option['label']);
            }
        }

        return response()->json([
            'category' => $category->toArray(),
            'products' => $products,
            'additionalFilters' => $additionalFilters
        ]);

//        dd($category, $childCategories, $products);
    }

    public function searchProducts(Request $request)
    {
        $q = null;

        if ($request->has('q')) {
            $q = $request->q;

            $products = Product::query()
                ->where('name', 'like', '%' . $q . '%')
                ->whereNotNull('starting_price')
                ->whereNotNull('brand')
                ->with('brand')
                ->get();

            return response()->json([
                'category' => [],
                'products' => $products,
                'additionalFilters' => []
            ]);

        }

    }
}
