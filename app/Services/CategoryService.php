<?php

namespace App\Services;

use App\Helper\StringHelper;
use App\Models\Product\CustomProperties;
use App\Models\Product\CustomPropertiesOptions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CategoryService
{


    public function generateAdditionalFilters($parentCategory)
    {
        $additionalFilters = $parentCategory->filters()->get()->toArray();

        foreach ($additionalFilters as &$additionalFilter) {
            foreach ($additionalFilter['options'] as &$option) {
                $option['label'] = str_replace('{{ category }}', $parentCategory->name, $option['label']);
            }
            $additionalFilter['slug'] = 'fil_' . $additionalFilter['id'] . '_' . StringHelper::generateSlug($additionalFilter['name']);
        }

        return $additionalFilters;
    }

    public function generateCustomPropertyFilters($products, &$additionalFilters)
    {
        $allCustomProperties = $products->flatMap(function ($product) {
            return $product->customProperties->map(function ($customPropertyOption) {
                return $customPropertyOption->customProperty;
            });
        })
            ->filter()
            ->unique('id')
            ->values();

        foreach ($allCustomProperties as $customProperty) {
            if ($customProperty->display_in_filters) {
                $filterArr = [
                    'name' => $customProperty->name,
                    'option_name' => $customProperty->slug,
                    'slug' => $customProperty->slug,
                    'options' => []
                ];

                foreach ($customProperty->options as $option) {
                    $filterArr['options'][] = [
                        'label' => $option->name,
                        'search' => $option->name
                    ];
                }

                $additionalFilters[] = $filterArr;
            }
        }
    }

    public function handlePropertySearch(Request $request, $productQuery)
    {
        $all = $request->all();

        foreach ($all as $key => $value) {
            if (str_starts_with($key, 'prop_')) {
                $options = explode(',', $value);

                $customPropertyQuery = CustomProperties::where('slug', $key)->first();

                if ($customPropertyQuery instanceof CustomProperties) {
                    $options = CustomPropertiesOptions::query()
                        ->where('custom_property_id', $customPropertyQuery->id)
                        ->where(function (Builder $query) use ($options) {
                            foreach ($options as $option) {
                                $query->orWhere('name', $option);
                            }
                        })->get()->pluck('id')->toArray();

                    $productQuery->whereHas('customProperties', function (Builder $query) use ($options) {
                        $query->whereIn('id', $options);
                    });
                }
            }
        }
    }
}
