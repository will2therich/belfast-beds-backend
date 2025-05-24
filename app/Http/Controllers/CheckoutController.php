<?php

namespace App\Http\Controllers;

use App\Models\Ecom\Address;
use App\Services\CartService;
use Illuminate\Http\Request;
use Stripe\StripeClient;

class CheckoutController
{

    public function updateCheckout(Request $request, CartService $cartService)
    {
        $cart = $cartService->loadCart();
        $requestData = $request->all();

        if (!empty($requestData['shippingAddress']) && !empty($requestData['shippingAddress']['addressLine1'])) {
            $shippingAddress = new Address();
            $shippingAddress->cart_id = $cart->id;

            if (!empty($cart->shipping_address_id)) $shippingAddress = $cart->shippingAddress;
            $shippingAddress = $this->createOrUpdateAddress($shippingAddress, $requestData['shippingAddress']);
            $cart->shipping_address_id = $shippingAddress->id;
        }
        unset($requestData['shippingAddress']);

        if (!empty($requestData['billingAddress']) && !empty($requestData['billingAddress']['addressLine1'])) {
            if (!empty($requestData['billingSameAsShipping']) && $requestData['billingSameAsShipping']) {
                $cart->billing_address_id = $cart->shipping_address_id;
            } else {
                $billingAddress = new Address();
                $billingAddress->cart_id = $cart->id;

                if (!empty($cart->billing_address_id)) $billingAddress = $cart->billingAddress;
                $billingAddress = $this->createOrUpdateAddress($billingAddress, $requestData['billingAddress']);
                $cart->billing_address_id = $billingAddress->id;

            }
        }
        unset($requestData['billingAddress']);
        unset($requestData['billingSameAsShipping']);

        foreach ($requestData as $key => $value) {
            if (!empty($value)) {
                $cart->{$key} = $value;
            }
        }

        $cart->save();

        return response()->json($cart);
    }

    public function getPaymentIntentForCart(CartService $cartService)
    {
        $cart = $cartService->loadCart();
        $stripe = new StripeClient(env('STRIPE_SECRET_KEY'));

        $paymentIntent = $stripe->paymentIntents->create([
            'amount' => 2000,
            'currency' => 'gbp',
            'automatic_payment_methods' => ['enabled' => true],
        ]);


        return response()->json([
            'payment_intent' => $paymentIntent->toArray()['client_secret']
        ]);
    }

    private function createOrUpdateAddress(Address $address, $data)
    {
        $address->address_line_one = $data['addressLine1'];
        $address->address_line_two = $data['addressLine2'];
        $address->town_city = $data['townCity'];
        $address->county = $data['county'];
        $address->postcode = $data['postcode'];
        $address->country = $data['country'];
        $address->save();

        return $address;
    }
}
