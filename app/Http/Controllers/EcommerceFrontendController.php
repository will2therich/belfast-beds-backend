<?php

namespace App\Http\Controllers;

use App\Models\Product\PriceGroup;
use App\Models\Product\Product;
use App\Models\Product\ProductCategory;
use App\Models\Product\Properties;
use App\Models\Supplier;

class EcommerceFrontendController
{


    public function loadMenu()
    {
        // Get all parent categories with their sub-categories
        $categories = ProductCategory::with('childCategories') // Eager load child categories
            ->whereNull('parent_category_id') // Only fetch parent categories
            ->get()->toArray();

        $formattedMenu = [];

        foreach ($categories as $category) {
            $tempArray = [];

            $tempArray['id'] = $category['id'];
            $tempArray['name'] = $category['name'];
            $tempArray['slug'] = $category['slug'];
            $tempArray['subCategories'] = [];
            $childCategories = [];


            foreach ($category['child_categories'] as $key => $value) {
                if ($value['enabled']) $childCategories[] = $value;
            };


            // By Type Filter
            if (isset($category['child_categories'])) {
                $tempArray['subCategories'] = [
                    'name' => 'By Type',
                    'subCategories' => $childCategories
                ];
            }

            $formattedMenu[] = $tempArray;
        }



        $suppliers = Supplier::where('show_in_menu', true)->get()->toArray();

        if (is_array($suppliers) && count($suppliers) > 0) {
            $tempArray = [];
            $tempArray['id'] = 'brand';
            $tempArray['name'] = 'Brands';
            $tempArray['slug'] = 'brands';
            $tempArray['brands'] = [];

            foreach ($suppliers as $supplier) {
                $tempArray['brands'][] = [
                    'name' => $supplier['name'],
                    'image' => '/test',
                    'slug' => $supplier['slug']
                ];
            }

            $formattedMenu[] = $tempArray;
        }


        // Return JSON response
        return response()->json([
            'menu' => $formattedMenu
        ]);
    }

    public function loadProduct($slug)
    {
        $product = Product::where('slug', $slug)->firstOrFail();
        $productArray = $product->toArray();
        $properties = [];
        $priceOptionsArr = [];


        $options = $product->options;
        $priceOptions = $product->priceOptions()->orderBy('price')->get();


        foreach ($priceOptions as $priceOption) {
            if (!isset($priceOptionsArr[$priceOption->price_group_id])) {
                $priceGroup = PriceGroup::find($priceOption->price_group_id);

                if ($priceGroup instanceof PriceGroup) {
                    $priceOptionsArr[$priceOption->price_group_id] = [
                        'id' => $priceGroup->id,
                        'rs_id' => $priceGroup->rs_id,
                        'name' => $priceGroup->name,
                        'type' => 'PriceGroup',
                        'values' => []
                    ];
                }
            }

            $priceOptionsArr[$priceOption->price_group_id]['values'][] = $priceOption->toArray();
        }


        foreach  ($options as $option) {
            if (!isset($properties[$option->property_id])) {
                $propertyObj = Properties::find($option->property_id);

                if ($propertyObj instanceof Properties) {
                    $properties[$option->property_id] = [
                        'id' => $propertyObj->id,
                        'rs_id' => $propertyObj->rs_id,
                        'name' => $propertyObj->name,
                        'type' => 'Option',
                        'values' => []
                    ];
                }
            }

            $properties[$option->property_id]['values'][] = $option->toArray();

        }

        $productArray['fields'] = array_merge($priceOptionsArr, $properties);

        return response()->json($productArray);
    }
}
