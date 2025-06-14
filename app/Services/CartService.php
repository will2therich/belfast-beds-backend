<?php

namespace App\Services;

use App\Models\Ecom\AdditionalService;
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
            $cartLookup = Cart::where('uuid', $uuid)->where('ordered', false)->first();

            if ($cartLookup instanceof Cart) {
                return $cartLookup;
            } else {
                return $this->createCart();
            }
        }

        return $this->createCart();
    }

    public function updateCartValue(Cart $cart = null)
    {
        if ($cart == null) $cart = $this->loadCart();
        $value = 0;

        foreach ($cart->lineItems as $lineItem) {
            $value += (float) $lineItem->price * (int) $lineItem->quantity;
        }

        if (is_iterable($cart->selected_services)) {
            foreach ($cart->selected_services as $selected_service) {
                $service = AdditionalService::find($selected_service);
                if ($service instanceof AdditionalService) $value += (float)$service->price;
            }
        }

        $cart->value = $value;
        $cart->save();

        return $cart;
    }

    public function calculatePriceForItem($itemData)
    {
        $price = 0.0;

        foreach ($itemData['detailedSelections'] as $selection) {


            if ($selection['fieldType'] == 'PriceGroup') {
                $priceGroupLookup = ProductPriceGroup::query()
                    ->where('rs_price_group_option_id', $selection['selectedValueId'])
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
