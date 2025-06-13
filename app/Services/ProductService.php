<?php

namespace App\Services;

use App\Helper\IconHelper;
use App\Models\Product\AddOn;
use App\Models\Product\PriceGroup;
use App\Models\Product\Product;
use App\Models\Product\ProductCategory;
use App\Models\Product\Properties;
use Illuminate\Database\Eloquent\Builder;

class ProductService
{


    public function generateCustomProperties(Product $product)
    {
        $custProperties = $featuredProperties = [];

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

        return [
            'custom_properties' => $custProperties,
            'featured_properties' => $featuredProperties
        ];

    }

    public function handlePriceGroups(Product $product)
    {
        $priceOptionsArr = [];
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

        return $priceOptionsArr;
    }

    public function handleOptions(Product $product, $fieldsArr)
    {
        $options = $product->options;

        foreach  ($options as $option) {
            if (!isset($fieldsArr['property_' . $option->property_id])) {
                $propertyObj = Properties::find($option->property_id);

                if ($propertyObj instanceof Properties) {
                    $fieldsArr['property_' . $option->property_id] = [
                        'id' => $propertyObj->id,
                        'rs_id' => $propertyObj->rs_id,
                        'name' => $propertyObj->name,
                        'type' => 'Option',
                        'values' => []
                    ];
                }
            }

            $fieldsArr['property_' . $option->property_id]['values'][] = $option->toArray();
        }

        return $fieldsArr;
    }

    public function handleAddons(Product $product, $fieldArr)
    {
        $addons = $product->addons;

        foreach  ($addons as $addon) {
            if (!isset($fieldArr['addon_' . $addon->add_on_id])) {
                $addonObj = AddOn::find($addon->add_on_id);

                if ($addonObj instanceof AddOn) {
                    $fieldArr['addon_' . $addon->add_on_id] = [
                        'id' => $addonObj->id,
                        'rs_id' => $addonObj->rs_id,
                        'name' => $addonObj->name,
                        'type' => 'Addon',
                        'values' => []
                    ];
                }
            }

            $fieldArr['addon_' . $addon->add_on_id]['values'][] = $addon->toArray();
        }

        return $fieldArr;
    }

    public function handleUpsellCategories(Product $product, $fieldArr)
    {
        $upsellCategories = [];

        foreach ($product->categories as $category) if (is_array($category->upsell_categories)) $upsellCategories = array_merge($upsellCategories, $category->upsell_categories);
        $brand = $product->brand()->first();

        $upsellCategories = array_unique($upsellCategories);

        foreach ($upsellCategories as $upsellCategory) {
            $category = ProductCategory::find($upsellCategory);
            $categoryProducts = Product::query()
                ->whereHas('categories', function (Builder $query) use ($upsellCategory) {
                    $query->where('id', $upsellCategory);
                })->where('brand', $brand->id)
                ->get();


            if ($categoryProducts->count() > 0) {
                $tempArr = [
                    'id' => $category->id,
                    'rs_id' => $category->rs_id,
                    'name' => $category->name,
                    'type' => 'additionalProduct',
                    'values' => []
                ];

                foreach ($categoryProducts as $categoryProduct) {
                    $prodTemp = [
                        'id' => $categoryProduct->id,
                        'rs_id' => $categoryProduct->rs_id,
                        'name' => $categoryProduct->name,
                        'priceOptions' => []
                    ];

                    if (is_array($categoryProduct['photos']) && !empty($categoryProduct['photos'])) {
                        $prodTemp['image'] = $categoryProduct['photos'][array_key_first($categoryProduct['photos'])];
                    }

                    foreach ($categoryProduct->priceOptions as $priceOption) {
                        $prodTemp['priceOptions'][] = [
                            'rs_id' => $priceOption->rs_price_group_option_id,
                            'price' => $priceOption->price
                        ];
                    }
                    $tempArr['values'][] = $prodTemp;
                }

                $fieldArr['addProduct_' . $category->id] = $tempArr;
            }
        }

        return $fieldArr;
    }
}
