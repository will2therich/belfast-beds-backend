<?php

namespace App\Http\Controllers\Ecom;

use App\Helper\IconHelper;
use App\Helper\ImageHelper;
use App\Models\Core\Pages;
use App\Models\Product\Product;
use App\Models\Product\ProductCategory;
use App\Models\Settings;
use App\Models\Supplier;
use App\Services\CategoryService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class EcommerceFrontendController
{

    public function loadHomePage()
    {
        $data = Cache::remember('home-data', now()->addHour(), function () {
            $allSettings = Settings::all()->pluck('value', 'key');
            $promoSettings = Settings::where('key', 'like', 'promotional_%')->get();
            $products = Product::where('featured', true)->limit(10)->get();
            $aboutText = $allSettings['about_text'];

            $heroData = [];
            $featuresData = [];
            $promoBlocksData = [];
            $promotionData = [];

            if (isset($allSettings['homeHeroSlides'])) $heroData = json_decode($allSettings['homeHeroSlides'], 1);
            if (isset($allSettings['features'])) $featuresData = json_decode($allSettings['features'], 1);
            if (isset($allSettings['promoBlocks'])) $promoBlocksData = json_decode($allSettings['promoBlocks'], 1);


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


            $promoEnd = Carbon::make($promotionData['endDate']);
            $now = Carbon::now();

            if ($now > $promoEnd) $promotionData['active'] = 0;

            return [
                'heroSlides' => $heroData,
                'features' => $featuresData,
                'featuredProducts' => $products,
                'promoBlocks' => $promoBlocksData,
                'promotion' => $promotionData,
                'aboutText' => $aboutText
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

    public function loadPage($slug)
    {
        $page = Pages::where('slug', $slug)->firstOrFail();
        return response()->json($page);
    }

    private function getCategoryMenus(&$formattedMenu) {
        $categoryService = new CategoryService();
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


            $categoryService->menuGenerateAdditionalFilters($categoryObj, $category, $tempArray);
            $categoryService->menuGenerateCustomPropertyFilters($categoryObj, $category, $tempArray);

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
