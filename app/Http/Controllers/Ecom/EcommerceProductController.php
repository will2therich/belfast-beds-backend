<?php

namespace App\Http\Controllers\Ecom;

use App\Helper\IconHelper;
use App\Helper\ImageHelper;
use App\Helper\StringHelper;
use App\Models\Core\Pages;
use App\Models\Product\AddOn;
use App\Models\Product\PriceGroup;
use App\Models\Product\Product;
use App\Models\Product\ProductCategory;
use App\Models\Product\Properties;
use App\Models\Settings;
use App\Models\Supplier;
use Illuminate\Support\Facades\Cache;

class EcommerceProductController
{

    public function loadProduct($slug)
    {
        $product = Product::where('slug', $slug)->firstOrFail();
        $brand = $product->brand()->first();
        $category = $product->categories()->first();
        $productArray = $product->toArray();
        $productArray['brand'] = $brand->name;
        $productArray['brand_logo'] = $brand->image;
        $productArray['brand_slug'] = $brand->slug;
        $productArray['category'] = $category->name;
        $productArray['category_slug'] = $category->slug;
        $properties = [];
        $priceOptionsArr = [];

        $options = $product->options;
        $addons = $product->addons;
        $priceOptions = $product->priceOptions()->orderBy('price')->get();
        $featuredProperties = [];
        $custProperties = [];

        foreach ($product->customProperties as $customProperty) {
            $propertyDetails  = $customProperty->customProperty;

            $tempArr = [
                'title' => $propertyDetails->name,
                'value' => $customProperty->name,
                'icon' => IconHelper::generateSvgIcon($customProperty->icon),
                'description' => $customProperty->description
            ];

            if ($propertyDetails->display_on_product_page) $custProperties[] = $tempArr;
            if ($propertyDetails->featured_on_product_page) $featuredProperties[] = $tempArr;
        }

        $productArray['properties'] = $custProperties;
        $productArray['featuredProperties'] = $featuredProperties;

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
            if (!isset($properties['property_' . $option->property_id])) {
                $propertyObj = Properties::find($option->property_id);

                if ($propertyObj instanceof Properties) {
                    $properties['property_' . $option->property_id] = [
                        'id' => $propertyObj->id,
                        'rs_id' => $propertyObj->rs_id,
                        'name' => $propertyObj->name,
                        'type' => 'Option',
                        'values' => []
                    ];
                }
            }

            $properties['property_' . $option->property_id]['values'][] = $option->toArray();
        }

        foreach  ($addons as $addon) {
            if (!isset($properties['addon_' . $addon->add_on_id])) {
                $addonObj = AddOn::find($addon->add_on_id);

                if ($addonObj instanceof AddOn) {
                    $properties['addon_' . $addon->add_on_id] = [
                        'id' => $addonObj->id,
                        'rs_id' => $addonObj->rs_id,
                        'name' => $addonObj->name,
                        'type' => 'Addon',
                        'values' => []
                    ];
                }
            }

            $properties['addon_' . $addon->add_on_id]['values'][] = $addon->toArray();
        }

        $productArray['fields'] = array_values(array_merge($priceOptionsArr, $properties));


        return response()->json($productArray);
    }

}
