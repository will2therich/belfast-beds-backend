<?php

namespace App\Http\Controllers\Checkout;

use App\Models\Ecom\LineItem;
use App\Models\Product\Product;
use App\Services\CartService;
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



        return response()
            ->json($responseArr)
            ->withCookie($cookie);
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
