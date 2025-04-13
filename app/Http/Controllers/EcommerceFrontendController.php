<?php

namespace App\Http\Controllers;

use App\Models\Product\ProductCategory;

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

        // Return JSON response
        return response()->json([
            'menu' => $formattedMenu
        ]);
    }
}
