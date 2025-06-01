<?php
/**
 * Controller is responsible for general category related pages including brands, categories, collections & search
 *
 */
namespace App\Http\Controllers;

use App\Models\Ecom\ProductCollections;
use App\Models\Product\Product;
use App\Models\Product\ProductCategory;
use App\Models\Supplier;
use App\Services\CategoryService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class EcommerceCategoryController
{

    public function loadBrand($slug)
    {
        $brand = Supplier::where('slug', $slug)->first();

        if ($brand instanceof Supplier) {
            $brandArr = $brand->toArray();
            $uniqueCategoriesForBrand = ProductCategory::query()
                ->whereHas('products', function (Builder $query) use ($brand) {
                    $query->where('brand', $brand->id);
                })
                ->whereNull('parent_category_id')
                ->get();

            $collections = ProductCollections::query()
                ->whereJsonContains('suppliers', '' . $brand->id)
                ->get()->toArray();

            $brandArr['collections'] = $collections;
            $brandArr['categories'] = $uniqueCategoriesForBrand->toArray();


            return response()->json($brandArr);
        }
        return response()->json([]);
    }

    public function loadCategory($slug, CategoryService $categoryService)
    {
        $category = ProductCategory::where('slug', $slug)->firstOrFail();
        $childCategories = $category->childCategories()->pluck('id')->toArray();
        $allCategoryIds = array_merge([$category->id], $childCategories);

        $products = Product::query()->whereHas('categories', function ($query) use ($allCategoryIds) {
            $query->whereIn('id', $allCategoryIds);
        })
            ->whereNotNull('starting_price')
            ->whereNotNull('brand')
            ->with([
                'brand',
                'customProperties.customProperty'
            ])
            ->get();

        if (empty($category->parent_category_id)) {
            $parentCategory = $category;
        } else {
            $parentCategory = $category->parentCategory;
        }

        $additionalFilters = $categoryService->generateAdditionalFilters($parentCategory);
        $categoryService->generateCustomPropertyFilters($products, $additionalFilters);

        return response()->json([
            'category' => $category->toArray(),
            'products' => $products,
            'additionalFilters' => $additionalFilters
        ]);
    }

    public function loadCollection($slug)
    {
        $collection = ProductCollections::where('slug', $slug)->firstOrFail();
        $products = Product::query()
            ->whereIn('id', $collection->products)
            ->with('brand')
            ->get();

        return response()->json([
            'category' => $collection,
            'products' => $products,
            'additionalFilters' => []
        ]);
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
