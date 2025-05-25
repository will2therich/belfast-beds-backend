<?php

namespace App\Http\Controllers;

use App\Models\Ecom\Address;
use App\Models\Ecom\Cart;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
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

    public function handleStripeReturn(Request $request)
    {
        $stripe = new StripeClient(env('STRIPE_SECRET_KEY'));
        $paymentIntentId = $request->query('payment_intent');
        $cart = Cart::findOrFail($request->cartId);

        dd($request->all(),$cart);

        if (!$paymentIntentId) {
            Log::error('Stripe Return: Missing Payment Intent ID.');
            return Redirect::to(env('FRONTEND_URL', 'http://localhost:8080') . '/checkout?error=payment_intent_missing');
        }

        try {
            // Retrieve the PaymentIntent from Stripe to get its authoritative status
            $paymentIntent = $stripe->paymentIntents->retrieve($paymentIntentId);
            Log::info('Retrieved PaymentIntent from Stripe:', ['id' => $paymentIntent->id, 'status' => $paymentIntent->status]);


            // Handle different PaymentIntent statuses
            switch ($paymentIntent->status) {
                case 'succeeded':
                    Log::info('PaymentIntent Succeeded:', ['id' => $paymentIntent->id]);
                    // ACTUALLY HANDLE ORDER


                    // 4. Redirect to your frontend order confirmation page with a unique token/ID
                    $mockOrderToken = 'mock_order_token_' . $paymentIntent->id; // Replace with real token
                    $frontendSuccessUrl = env('FRONTEND_URL', 'http://localhost:5173') . '/order-confirmation?token=' . $mockOrderToken;
                    Log::info('Redirecting to success URL:', ['url' => $frontendSuccessUrl]);
                    return Redirect::to($frontendSuccessUrl);

                default:
                    Log::warning('Unhandled PaymentIntent Status:', ['id' => $paymentIntent->id, 'status' => $paymentIntent->status]);
                    // Handle other statuses as needed
                    $frontendFailureUrl = env('FRONTEND_URL', 'http://localhost:5173') . '/checkout?error=unexpected_status&status=' . $paymentIntent->status;
                    return Redirect::to($frontendFailureUrl);
            }
        } catch (ApiErrorException $e) {
            Log::error('Stripe API Error on return:', ['message' => $e->getMessage(), 'payment_intent_id' => $paymentIntentId]);
            return Redirect::to(env('FRONTEND_URL', 'http://localhost:5173') . '/checkout?error=stripe_api_error&message=' . urlencode($e->getMessage()));
        } catch (\Exception $e) {
            Log::error('Generic Error on Stripe return:', ['message' => $e->getMessage(), 'payment_intent_id' => $paymentIntentId]);
            return Redirect::to(env('FRONTEND_URL', 'http://localhost:5173') . '/checkout?error=server_error');
        }
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
