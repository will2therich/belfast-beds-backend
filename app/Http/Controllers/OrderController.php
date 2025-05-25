<?php

namespace App\Http\Controllers;


use App\Models\Ecom\Order;

class OrderController
{

    public function loadOrder($uuid) {
        $order = Order::where('uuid', $uuid)->firstOrFail();
        $auth = false;

        if (!$order->confirmation_viewed)  {
            $auth = true;
            $order->confirmation_viewed = true;
            $order->save();
        }

        if (!$auth && auth('vue')->check()) {
            $user = auth('vue')->get();
            if ($order->customer_id == $user->id) $auth = true;
        }

        $order->shippingAddress;
        $order->billingAdderss;
        $lineItems = $order->lineItems;

        foreach ($lineItems as $lineItem) $lineItem->product;

        if ($auth) {
            return response()->json($order);
        }

        return response()->json([]);

    }
}
