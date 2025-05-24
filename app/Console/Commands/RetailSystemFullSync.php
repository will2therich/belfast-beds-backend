<?php

namespace App\Console\Commands;

use App\Models\PivotTables\ProductPriceGroup;
use App\Models\Product\PriceGroup;
use App\Models\Product\PriceGroupOptions;
use App\Models\Product\Product;
use App\Models\Product\ProductCategory;
use App\Models\Product\Properties;
use App\Models\Product\PropertyOption;
use App\Models\Supplier;
use App\Services\RetailSystemSoapService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class RetailSystemFullSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rs:full-sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a full data sync from retail system';

    /**
     * Execute the console command.
     */
    public function handle(RetailSystemSoapService $retailSystemSoapService)
    {

        $catalog = $retailSystemSoapService->getCatalog();

        $this->info('Product categories sync started - ' . time() );
        $this->handleProductCategories($catalog);
        $this->newLine();
        $this->info('Product categories sync finished - ' . time());

        $this->info('Suppliers sync started - ' . time() );
        $this->handleSuppliers($catalog);
        $this->newLine();
        $this->info('Suppliers sync finished - ' . time());


    }


    private function handleProductCategories($catalog)
    {
        $this->withProgressBar($catalog['ProductCategories']['ProductCategory'], function ($productCategory) {
            $parentCategoryId = null;
            $attributes = $productCategory['@attributes'];

            $category = ProductCategory::where('rs_id', $attributes['id'])->first();

            if (!$category instanceof ProductCategory) {
                $category = new ProductCategory();
                $category->slug = str_replace(' ', '_', strtolower($category->name));
                $category->rs_id = $attributes['id'];
            }

            $category->name = $attributes['attribute'];
            $category->save();

            if (!empty($productCategory['ProductCategory'])) {
                $parentCategoryId = $category->id;

                foreach ($productCategory['ProductCategory'] as $childProductCategory) {
                    $childAttribute = $childProductCategory['@attributes'];
                    $childCategory = ProductCategory::where('rs_id', $childAttribute['id'])->first();

                    if (!$childCategory instanceof ProductCategory)  {
                        $childCategory = new ProductCategory();
                        $childCategory->slug = $category->slug . '_' . str_replace(' ', '_', strtolower($childCategory->name));
                        $childCategory->rs_id = $childAttribute['id'];
                    }

                    $childCategory->name = $childAttribute['attribute'];
                    $childCategory->parent_category_id = $parentCategoryId;
                    $childCategory->save();
                }
            }
        });
    }

    private function handleSuppliers($catalog)
    {
        $this->withProgressBar($catalog['Suppliers']['Supplier'], function ($supplierData) {
            $supplierId = $supplierData['@attributes']['id'];

            if (empty($supplier['Brand']['WebName']['@attributes']['attribute'])) {
                $supplierName = $supplierData['@attributes']['attribute'];
            } else {
                $supplierName = $supplierData['Brand']['WebName']['@attributes']['attribute'];
            }

            $supplier = Supplier::where('rs_id', $supplierId)->first();

            if (!$supplier instanceof Supplier) {
                $supplier = new Supplier();
                $supplier->rs_id = $supplierId;
                $supplier->name = $supplierName;
                $supplier->slug = str_replace(' ', '_', strtolower(trim($supplier->name)));
                $supplier->save();
            }



            if (!empty($supplierData['Brand']['OptionGroup'])) {
                // If its not a multi-item array then force it to be one.
                if (isset($supplierData['Brand']['OptionGroup']['@attributes'])) {
                    $supplierData['Brand']['OptionGroup'] = [$supplierData['Brand']['OptionGroup']];
                }

                // Import Option Groups & their options
                foreach ($supplierData['Brand']['OptionGroup'] as $optionGroup) {

                    $optionGroupId = $optionGroup['@attributes']['id'];
                    $optionGroupName = $optionGroup['@attributes']['attribute'];
                    $propertyObj = Properties::where('rs_id', $optionGroupId)->first();
                    if (!$propertyObj instanceof Properties) $propertyObj = new Properties();


                    $propertyObj->rs_id = $optionGroupId;
                    $propertyObj->name = $optionGroupName;
                    $propertyObj->save();


                    foreach ($optionGroup['Option'] as $option) {
                        $optionId = $option['@attributes']['id'];
                        $optionName = $option['@attributes']['attribute'];
                        $photos = [];

                        if (isset($option['Photo'])) {
                            if (isset($option['Photo']['@attributes'])) $option['Photo'] = [ $option['Photo'] ];

                            foreach ($option['Photo'] as $photo) {
                                $path = 'options/' . $optionId . '/' . $photo['@attributes']['attribute'];

//                                if (!file_exists($path)) {
//                                    $this->info('Storing New Image: ' . $photo['@attributes']['attribute']);
//                                    $photoData = file_get_contents('https://retailsystem.s3-eu-west-1.amazonaws.com/' . strtoupper(substr(env('RS_GUID'), -12)) . '/' . $photo['@attributes']['id'] . '/' . urlencode($photo['@attributes']['attribute']));
//                                    Storage::put('public/' . $path, $photoData);
//                                }

                                $photos[] = $path;
                            }
                        } elseif (isset($option['PhotoHiRes'])) {
                            if (isset($option['PhotoHiRes']['@attributes'])) $option['PhotoHiRes'] = [ $option['PhotoHiRes'] ];

                            foreach ($option['PhotoHiRes'] as $photo) {
                                $path = 'options/' . $optionId . '/' . $photo['@attributes']['attribute'];

                                if (!file_exists($path)) {
//                                    $this->info('Storing New Image: ' . $photo['@attributes']['attribute']);
//                                    $photoData = file_get_contents('https://retailsystem.s3-eu-west-1.amazonaws.com/' . strtoupper(substr(env('RS_GUID'),-12)) . '/' . $photo['@attributes']['id'] . '/' . urlencode($photo['@attributes']['attribute']));
//                                    Storage::put('public/' . $path, $photoData);
                                }

                                $photos[] = $path;
                            }
                        }

                        $propertyValueObj = PropertyOption::where('rs_id', $optionId)->first();
                        if (!$propertyValueObj instanceof PropertyOption)  {
                            $propertyValueObj = new PropertyOption();
                            $propertyValueObj->photos = $photos;
                        }

                        // If there are no photos for RS sync then do not override the existing items.
                        if (!empty($photos)) {
                            $propertyValueObj->photos = $photos;
                        }

                        $propertyValueObj->rs_id = $optionId;
                        $propertyValueObj->name = $optionName;
                        $propertyValueObj->property_id = $propertyObj->id;
                        $propertyValueObj->save();
                    }
                }
            }

            if (isset($supplierData['Brand']['PriceGroup'])) {
                if (isset($supplierData['Brand']['PriceGroup']['@attributes'])) $supplierData['Brand']['PriceGroup'] = [$supplierData['Brand']['PriceGroup']];

                foreach ($supplierData['Brand']['PriceGroup'] as $priceGroup) {
                    $priceGroupObj = PriceGroup::where('rs_id', $priceGroup['@attributes']['id'])->first();
                    if (!$priceGroupObj instanceof PriceGroup) $priceGroupObj = new PriceGroup();
                    $priceGroupObj->rs_id = $priceGroup['@attributes']['id'];
                    $priceGroupObj->name = $priceGroup['@attributes']['attribute'];
                    $priceGroupObj->save();

                    foreach ($priceGroup['Option'] as $option) {
                        $priceGroupOptionObj = PriceGroupOptions::where('rs_id', $option['@attributes']['id'])->first();
                        if (!$priceGroupOptionObj instanceof PriceGroupOptions) $priceGroupOptionObj = new PriceGroupOptions();
                        $priceGroupOptionObj->rs_id = $option['@attributes']['id'];
                        $priceGroupOptionObj->name = $option['@attributes']['attribute'];
                        $priceGroupOptionObj->price_group_id = $priceGroupObj->id;
                        $priceGroupOptionObj->save();
                    }

                }
            }

            if (!empty($supplierData['Brand']['ProductRange'])) {
                // If its not a multi-item array then force it to be one.
                if (isset($supplierData['Brand']['ProductRange']['@attributes'])) $supplierData['Brand']['ProductRange'] = [$supplierData['Brand']['ProductRange']];

                // Import Option Groups & their options
                foreach ($supplierData['Brand']['ProductRange'] as $productRange) {

                    if (isset($productRange['Product'])) {
                        // Force it to array format.
                        if (isset($productRange['Product']['@attributes'])) $productRange['Product'] = [$productRange['Product']];


                        foreach ($productRange['Product'] as $product) {
                            $productId = $product['@attributes']['id'];
                            $productName = $product['@attributes']['attribute'];

                            $webEnabled = false;

                            if (isset($product['@attributes']['webenabled'])) {
                                $webEnabled = $product['@attributes']['webenabled'];
                            }

                            $productObj = Product::where('rs_id', $productId)->first();
                            if (!$productObj instanceof Product) $productObj = new Product();
                            $photos = [];

                            if (isset($product['PhotoHiRes'])) {
                                if (isset($product['PhotoHiRes']['@attributes'])) $product['PhotoHiRes'] = [$product['PhotoHiRes']];
                                foreach ($product['PhotoHiRes'] as $photo) {
                                    if (isset($photo['@attributes'])) {
                                        $path = 'products/' . $productId . '/' . $photo['@attributes']['attribute'];
//                                        $photoData = file_get_contents('https://retailsystem.s3-eu-west-1.amazonaws.com/' . strtoupper(substr(env('RS_GUID'),-12)) . '/' . $photo['@attributes']['id'] . '/' . urlencode($photo['@attributes']['attribute']));
//                                        Storage::put('public/' . $path, $photoData);
                                        $photos[] = $path;
                                    }
                                }
                            }

                            $sections = [];

                            if (isset($product['Fields'])) {
                                foreach ($product['Fields'] as $key => $field) {
                                    if (isset($field['@attributes']['a'])) {
                                        $sections[str_replace(' ', '_', strtolower($key))] = $field['@attributes']['a'];
                                    }
                                }
                            }

                            try {
                                $productObj->rs_id = $productId;
                                $productObj->name = $productName;
                                $productObj->enabled = $webEnabled;
                                $productObj->photos = $photos;
                                $productObj->brand = $supplier->id;
                                $productObj->slug = $productId . '_' . str_replace(' ', '_', strtolower($productObj->name));
                                $productObj->sections = $sections;
                                $productObj->save();

                                $this->syncProductCategories($productObj, $product);
                                $this->syncProductProperties($productObj, $product);
                                $startingPrice = null;
                            } catch (\Exception $e) {
                                dump($e->getMessage());
                                continue;
                            }



                            if (isset($product['Link'])) {

                                ProductPriceGroup::where('rs_product_id', $productObj->rs_id)->delete();

                                if (isset($product['Link']['@attributes'])) $product['Link'] = [$product['Link']];

                                foreach ($product['Link'] as $link) {
                                    $productPriceGroupObj = new ProductPriceGroup();
                                    $priceGroupOptionObj = PriceGroupOptions::where('rs_id', $link['@attributes']['linkid'])->first();
                                    $priceGroup = $priceGroupOptionObj->pricegroup;
                                    $productPriceGroupObj->rs_product_id = $productObj->rs_id;
                                    $productPriceGroupObj->rs_price_group_option_id = $link['@attributes']['linkid'];
                                    $productPriceGroupObj->price_group_id = $priceGroup->id;
                                    $productPriceGroupObj->name = $priceGroupOptionObj->name;

                                    if (isset($link['Prices']['@attributes'])) {
                                        if ($startingPrice == null)  {
                                            $startingPrice = $link['Prices']['@attributes']['price'];
                                        } elseif ($startingPrice > $link['Prices']['@attributes']['price']) {
                                            $startingPrice = $link['Prices']['@attributes']['price'];
                                        }

                                        $productPriceGroupObj->price = $link['Prices']['@attributes']['price'];
                                    }

                                    $productPriceGroupObj->save();
                                }
                            }

                            if (is_null($startingPrice)) {
                                if (isset($product['Prices']['@attributes'])) {
                                    $startingPrice = $product['Prices']['@attributes']['price'];
                                } else {
                                    $startingPrice = 0;
                                }
                            }

                            $productObj->starting_price = $startingPrice;
                            $productObj->save();
                        }
                    }
                }
            }
        });
    }

    private function syncProductCategories(Product $productObj, $productData)
    {
        if (isset($productData['IsMemberOf'])) {
            // Force the format
            if (isset($productData['IsMemberOf']['@attributes'])) $productData['IsMemberOf'] = [$productData['IsMemberOf']];

            $rsCategoryId = [];
            foreach ($productData['IsMemberOf'] as $category) {
                if (isset($category['@attributes'])) {
                    $categoryObj = ProductCategory::where('rs_id', $category['@attributes']['linkid'])->exists();
                    if ($categoryObj) $rsCategoryId[] = $category['@attributes']['linkid'];
                }
            }

            $productObj->categories()->sync($rsCategoryId);
        }
    }

    private function syncProductProperties(Product $productObj, $productData)
    {
        if (isset($productData['OptionLink'])) {
            if (isset($productData['OptionLink']['@attributes'])) $productData['OptionLink'] = [$productData['OptionLink']];
            $propertyOptions = [];

            foreach ($productData['OptionLink'] as $optionLink) {
                $exists = PropertyOption::where('rs_id', $optionLink['@attributes']['linkid'])->exists();

                if ($exists) {
                    $propertyOptions[] = $optionLink['@attributes']['linkid'];
                }
            }

            $productObj->options()->sync($propertyOptions);
        } elseif (isset($productData['OptionGroupLink'])) {
            if (isset($productData['OptionGroupLink']['@attributes'])) $productData['OptionGroupLink'] = [$productData['OptionGroupLink']];
            $propertyOptions = [];

            foreach ($productData['OptionGroupLink'] as $groupLink) {
                $property = Properties::where('rs_id', $groupLink['@attributes']['linkid'])->first();

                if ($property instanceof Properties) {
                    $propertyOptions = array_merge(
                        $propertyOptions,
                        PropertyOption::where('property_id', $property->id)->get()->pluck('rs_id')->toArray()
                    );
                }
            }

            $productObj->options()->sync($propertyOptions);
        }

    }
}
