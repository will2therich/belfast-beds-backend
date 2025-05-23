<?php

namespace App\Services;

use App\Models\Ecom\Cart;
use App\Models\PivotTables\ProductPriceGroup;
use App\Models\Product\PriceGroupOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CartService
{

    public function loadCart()
    {
        $request = Request::createFromGlobals();

        if ($request->hasCookie('belfast-beds-cart-token')) {
            $uuid = $request->cookie('belfast-beds-cart-token');
            $cartLookup = Cart::where('uuid', $uuid)->first();

            if ($cartLookup instanceof Cart) {
                return $cartLookup;
            } else {
                return $this->createCart();
            }
        }

        return $this->createCart();

    }

    public function calculatePriceForItem($itemData)
    {
        $price = 0.0;

        foreach ($itemData['detailedSelections'] as $selection) {


            if ($selection['fieldType'] == 'PriceGroup') {
                $priceGroupLookup = ProductPriceGroup::query()
                    ->where('rs_price_group_option_id', $selection['selectedValue'])
                    ->where('rs_product_id', $itemData['rsId'])
                    ->first();

                if ($priceGroupLookup instanceof ProductPriceGroup) {
                    $price = $priceGroupLookup->price;
                }
            }
        }

        return $price;
    }


    private function createCart()
    {
        $cart = new Cart();
        $cart->uuid = Str::uuid();
        $cart->value = 0.0;
        $cart->save();

        return $cart;
    }
}
