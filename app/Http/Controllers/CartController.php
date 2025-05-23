<?php

namespace App\Http\Controllers;

use App\Models\Ecom\LineItem;
use App\Services\CartService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;

class CartController
{


    public function loadCart(CartService $cartService)
    {

        $cart = $cartService->loadCart();
        $cart->line_items = $cart->lineItems()->with('product')->get();

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
            ->json($cart)
            ->withCookie($cookie);
    }

    public function addToCart(CartService $cartService, Request $request)
    {
        $options = [];
        $cart = $cartService->loadCart();
        $data = $request->all();

        $price = $cartService->calculatePriceForItem($data);
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
