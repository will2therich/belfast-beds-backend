<?php

namespace App\Http\Controllers\Checkout;

use App\Models\Ecom\AdditionalService;
use App\Models\Ecom\LineItem;
use App\Models\Product\Product;
use App\Services\CartService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;

class CartController
{


    public function loadCart(CartService $cartService)
    {

        $cart = $cartService->updateCartValue();
        $lineItems = $cart->lineItems()->with('product')->get();

        $responseArr = $cart->toArray();
        foreach ($lineItems as $lineItem) {
            $lineItem->product;
        }

        $responseArr['line_items'] = $lineItems->toArray();

        $cookie = new Cookie(
            'belfast-beds-cart-token',
            $cart->uuid,
            time() + (env('CART_LIFETIME_COOKIE', 2880) * 60), // Expiration time in seconds
            '/',
            null,
            true,
            true,
            false,
            'none',
            true
        );

        $includedCategoryIds = [];

        foreach ($responseArr['line_items'] as $lineItem) {
            $product = Product::find($lineItem['product_id']);

            $includedCategoryIds = array_merge(
                $includedCategoryIds,
                $product->categories()->get()->pluck('id')->toArray()
            );
        }

        $includedCategoryIds = array_unique($includedCategoryIds);

        $additionalServices = AdditionalService::query()->where(function (Builder $query) use ($includedCategoryIds) {
            foreach ($includedCategoryIds as $categoryId) {
                $query->orWhereJsonContains('category_ids', '' . $categoryId);
            }
        })->get()->toArray();

        $responseArr['additionalServices'] = $additionalServices;
        $responseArr['selectedServices'] = $responseArr['selected_services'];
        unset($responseArr['selected_services']);

        return response()
            ->json($responseArr)
            ->withCookie($cookie);
    }

    public function updateCart(Request $request, CartService $cartService)
    {
        $cart = $cartService->loadCart();

        if ($request->has('selectedServices')) {
            $cart->selected_services = $request->selectedServices;
            $cart->save();

        }

        return $this->loadCart($cartService);
    }

    public function deleteItemFromCart(CartService $cartService, $lineItemId)
    {
        $cart = $cartService->loadCart();
        $lineItem = LineItem::find($lineItemId);

        if ($cart->lineItems()->where('id', $lineItemId)->exists()) {
            $lineItem->delete();
        }

        return $this->loadCart($cartService);
    }

    public function updateQuantity(CartService $cartService, $lineItemId, $quantity)
    {
        $lineItem = LineItem::find($lineItemId);

        if ($lineItem instanceof LineItem) {
            $lineItem->quantity = $quantity;
        }

        return $cartService->updateCartValue();
    }

    public function addToCart(CartService $cartService, Request $request)
    {
        $options = [];
        $cart = $cartService->loadCart();
        $data = $request->all();

        $price = $cartService->calculatePriceForItem($data);

        if (empty($price)) {
            $product = Product::find($data['productId']);
            $price = $product->starting_price;
        }

        foreach ($data['detailedSelections'] as $selection) $options[$selection['fieldName']] = $selection['selectedName'];

        $lineItem = new LineItem();
        $lineItem->item_name = $data['productName'];
        $lineItem->product_id = $data['productId'];
        $lineItem->slug = $data['slug'];
        $lineItem->price = $price;
        $lineItem->options = $options;
        $lineItem->selections = $data['detailedSelections'];
        $lineItem->save();

        $cart->lineItems()->attach($lineItem->id);


        return $this->loadCart($cartService);

    }

}
