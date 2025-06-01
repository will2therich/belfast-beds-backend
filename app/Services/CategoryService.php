<?php

namespace App\Services;

class CategoryService
{


    public function generateAdditionalFilters($parentCategory)
    {
        $additionalFilters = $parentCategory->filters()->get()->toArray();

        foreach ($additionalFilters as &$additionalFilter) {
            foreach ($additionalFilter['options'] as &$option) {
                $option['label'] = str_replace('{{ category }}', $parentCategory->name, $option['label']);
            }
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
}
