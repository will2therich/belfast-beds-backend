<?php

namespace App\Http\Controllers;

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

class EcommerceFrontendController
{

    public function loadHomePage()
    {
        $data = Cache::remember('home-data', now()->addHour(), function () {
            $heroSlides = Settings::where('key', 'homeHeroSlides')->first();
            $features = Settings::where('key', 'features')->first();
            $promoBlocks = Settings::where('key', 'promoBlocks')->first();
            $promoSettings = Settings::where('key', 'like', 'promotional_%')->get();
            $heroData = [];
            $featuresData = [];
            $promoBlocksData = [];
            $promotionData = [];

            if ($heroSlides instanceof Settings) $heroData = json_decode($heroSlides->value, 2);
            if ($features instanceof Settings) $featuresData = json_decode($features->value, 2);
            if ($promoBlocks instanceof Settings) $promoBlocksData = json_decode($promoBlocks->value, 2);


            foreach ($featuresData as &$featuresDatum) {
                if (isset($featuresDatum['icon'])) {
                    $featuresDatum['icon'] = IconHelper::generateSvgIcon($featuresDatum['icon']);
                }
            }

            foreach ($heroData as &$datum) {
                if (isset($datum['image'])) {
                    $datum['image'] = ImageHelper::getImageUrl($datum['image']);
                }
            }

            foreach ($promoBlocksData as &$blockData) {
                if (isset($blockData['imageUrl'])) $blockData['imageUrl'] = ImageHelper::getImageUrl($blockData['imageUrl']);
            }

            foreach ($promoSettings as $promoSetting) {
                $promotionData[str_replace('promotional_', '', $promoSetting->key)] = $promoSetting->value;
            }

            return [
                'heroSlides' => $heroData,
                'features' => $featuresData,
                'featuredProducts' => [
                    [
                        'id' => 1,
                        'brand' => 'Relyon',
                        'name' => 'Barton Ortho 1000 Mattress',
                        'price' => '£589.00',
                        'imageUrl' => 'https://placehold.co/200x150/f7fafc/cbd5e0?text=Mattress+1',
                        'discount' => 'Extra 10% Discount Code At Checkout'
                    ],
                    [
                        'id' => 2,
                        'brand' => 'Julian Bowen',
                        'name' => 'Calgary Bunk',
                        'price' => '£629.00',
                        'imageUrl' => 'https://placehold.co/200x150/f7fafc/cbd5e0?text=Bunk+Bed',
                        'discount' => 'Extra 10% Discount Code At Checkout'
                    ],
                    [
                        'id' => 3,
                        'brand' => 'Belfast Beds',
                        'name' => 'Consul Open Coil Medium Feel Mattress',
                        'price' => '£149.00',
                        'imageUrl' => 'https://placehold.co/200x150/f7fafc/cbd5e0?text=Mattress+2',
                        'discount' => 'Extra 10% Discount Code At Checkout'
                    ],
                    [
                        'id' => 4,
                        'brand' => 'Belfast Beds',
                        'name' => 'Memory Pocket Silver 1000 - Firm - 2 Drawer Set inc. Strutted Headboard',
                        'price' => '£459.00',
                        'imageUrl' => 'https://placehold.co/200x150/f7fafc/cbd5e0?text=Bed+Set',
                        'discount' => 'Extra 10% Discount Code At Checkout'
                    ],
                ],
                'promoBlocks' => $promoBlocksData,
                'promotion' => $promotionData
            ];
        });

        return response()->json($data);
    }

    public function loadMenu()
    {

        $headerMenu = [];
        $this->getCategoryMenus($headerMenu);
        $this->getSupplierMenu($headerMenu);

        $footer = [
            'locations' => [
                [
                    'id' => 'cambridge',
                    'name' => 'Cambridge',
                    'openingHours' => [
                        [
                            'day' => 'Monday - Friday',
                            'hours' => '9:30am - 5:30pm'
                        ],
                        [
                            'day' => 'Saturday',
                            'hours' => '9:00am - 5:30pm'
                        ],
                        [
                            'day' => 'Sunday',
                            'hours' => '10:30am - 4:30pm'
                        ]
                    ],
                    'salesPhone' => '01223 411311',
                    'mapDetails' => [
                        'embedSrc' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2444.9194505018118!2d0.14694751632768477!3d52.20851126701803!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47d870616b1754fb%3A0xcf2a68dacebbd6b6!2sBelfast%20Bed%20Superstore!5e0!3m2!1sen!2suk!4v1607447857516!5m2!1sen!2suk',
                        'viewLargerLink' => 'https://www.google.com/maps?ll=52.208517,0.149156&z=15&t=m&hl=en&gl=GB&mapclient=embed&cid=14927859203853506230',
                        'storeName' => 'Belfast Bed Superstore',
                        'fullAddress' => 'Restwell House, Coldham’s Rd, Cambridge CB1 3EW',
                        'rating' => '4.2',
                        'reviews' => '74 reviews'
                    ]
                ]
            ],
            'sharedContactEmail' => 'support@belfastbeds.co.uk',
            'termsLinks' => [
                [
                    'name' => 'Contact Us',
                    'path' => '/contact'
                ]
            ],
            'paymentMethodAlts' => [
                'Visa',
                'Mastercard',
                'Maestro',
                'Amex',
                'PayPal'
            ]
        ];

        $footerPages = Pages::where('show_in_footer', true)->get();

        foreach ($footerPages as $page) {
            $tempArr = [];

            $tempArr['name'] = $page->name;
            $tempArr['path'] = [
                'name' => 'DynamicPage',
                'params' => [
                    'slug' => $page->slug
                ]
            ];

            $footer['termsLinks'][] = $tempArr;
        }


        // Return JSON response
        return response()->json([
            'menu' => $headerMenu,
            'footer' => $footer
        ]);
    }

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

    public function loadPage($slug)
    {
        $page = Pages::where('slug', $slug)->firstOrFail();
        return response()->json($page);
    }

    private function getCategoryMenus(&$formattedMenu) {
        // Get all parent categories with their sub-categories
        $categories = ProductCategory::with('childCategories') // Eager load child categories
            ->whereNull('parent_category_id') // Only fetch parent categories
            ->get()->toArray();

        foreach ($categories as $category) {
            $categoryObj = ProductCategory::find($category['id']);
            $tempArray = [];

            $tempArray['id'] = $category['id'];
            $tempArray['name'] = $category['name'];
            $tempArray['slug'] = $category['slug'];
            $tempArray['subCategories'] = [];
            $tempArray['featured_sections'] = $category['featured_sections'];
            $childCategories = [];


            foreach ($category['child_categories'] as $key => $value) {
                if ($value['enabled']) $childCategories[] = $value;
            };


            // By Type Filter
            if (isset($category['child_categories'])) {
                $tempArray['subCategories'][] = [
                    'name' => 'By Type',
                    'subCategories' => $childCategories
                ];
            }

            $additionalFilters = $categoryObj->filters()->get();

            foreach ($additionalFilters as $filter) {
                $filterArr = [
                    'name' => $filter->name,
                    'subCategories' => []
                ];

                foreach ($filter->options as $option) {
                    $filterArr['subCategories'][] = [
                        'name' => str_replace('{{ category }}', $category['name'], $option['label']),
                        'slug' => $category['slug'] . '?' . $filter->name . '=' . StringHelper::generateSlug($option['search'])
                    ];
                }

                $tempArray['subCategories'][] = $filterArr;
            }

            $formattedMenu[] = $tempArray;
        }
    }

    private function getSupplierMenu(&$formattedMenu)
    {
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
                    'image' => $supplier['image'],
                    'slug' => $supplier['slug']
                ];
            }

            $formattedMenu[] = $tempArray;
        }
    }
}
