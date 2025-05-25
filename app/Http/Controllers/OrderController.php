<?php

namespace App\Http\Controllers;


use App\Models\Ecom\Order;

class OrderController
{

    public function loadOrder($uuid) {
        $order = Order::where('uuid', $uuid)->firstOrFail();
        $auth = false;

        if (!$order->confirmation_viewed) $auth = true;

        if ($auth) {
            return response()->json($order);
        }

        return response()->json([]);

    }
}
