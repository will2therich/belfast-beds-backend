<?php

namespace App\Console\Commands;

use App\Models\PivotTables\ProductPriceGroup;
use App\Models\PivotTables\ProductProperties;
use App\Models\PriceGroup;
use App\Models\PriceGroupOptions;
use App\Models\Product\Product;
use App\Models\Product\ProductCategory;
use App\Models\Properties;
use App\Models\PropertyOption;
use App\Models\Supplier;
use App\Services\RetailSystemSoapService;
use Illuminate\Console\Command;

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
            if (!$category instanceof ProductCategory) $category = new ProductCategory();

            $category->rs_id = $attributes['id'];
            $category->name = $attributes['attribute'];
            $category->save();

            if (!empty($productCategory['ProductCategory'])) {
                $parentCategoryId = $category->id;

                foreach ($productCategory['ProductCategory'] as $childProductCategory) {
                    $childAttribute = $childProductCategory['@attributes'];
                    $childCategory = ProductCategory::where('rs_id', $childAttribute['id'])->first();
                    if (!$childCategory instanceof ProductCategory) $childCategory = new ProductCategory();

                    $childCategory->rs_id = $childAttribute['id'];
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
            if (!$supplier instanceof Supplier) $supplier = new Supplier();

            $supplier->rs_id = $supplierId;
            $supplier->name = $supplierName;
            $supplier->save();

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
                            if (isset($option['Photo']['@attributes'])) $option['Photo'] = [$option['Photo']];
                            foreach ($option['Photo'] as $photo) {
                                $photos[] = $photo['@attributes']['attribute'];
                            }
                        }

                        $propertyValueObj = PropertyOption::where('rs_id', $optionId)->first();
                        if (!$propertyValueObj instanceof PropertyOption) $propertyValueObj = new PropertyOption();

                        $propertyValueObj->rs_id = $optionId;
                        $propertyValueObj->name = $optionName;
                        $propertyValueObj->property_id = $propertyObj->id;
                        $propertyValueObj->photos = $photos;
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
                                        $photos[] = $photo['@attributes']['attribute'];
                                    }
                                }
                            }


                            $productObj->rs_id = $productId;
                            $productObj->name = $productName;
                            $productObj->enabled = $webEnabled;
                            $productObj->photos = $photos;
                            $productObj->save();

                            $this->syncProductCategories($productObj, $product);
                            $this->syncProductProperties($productObj, $product);

                            if (isset($product['Link'])) {
                                ProductPriceGroup::where('rs_product_id', $productObj->rs_id)->delete();

                                if (isset($product['Link']['@attributes'])) $product['Link'] = [$product['Link']];

                                foreach ($product['Link'] as $link) {
                                    $productPriceGroupObj = new ProductPriceGroup();

                                    $productPriceGroupObj->rs_product_id = $productObj->rs_id;
                                    $productPriceGroupObj->rs_price_group_id = $link['@attributes']['linkid'];
                                    if (isset($link['Prices']['@attributes'])) {
                                        $productPriceGroupObj->price = $link['Prices']['@attributes']['price'];
                                    }

                                    $productPriceGroupObj->save();
                                }
                            }
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
        }

    }
}
