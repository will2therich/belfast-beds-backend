<?php

namespace App\Http\Controllers;

use App\Models\Product\Product;
use App\Models\StockItem;
use Illuminate\Http\Request;

class EcommerceStockController
{

    public function checkStock(Request $request, $productId)
    {
        $product = Product::where('rs_id', $productId)->first();
        $requestData = $request->all();
        $responseData = [
            'inStock' => false,
            'leadTime' => '1-2 Weeks'
        ];

        $hashRaw = $product->slug;
        $hashNum = 0;
        foreach ($requestData as $datum) $hashNum += (int) $datum;
        $hashRaw .= '_' . $hashNum;

        $stockItem = StockItem::where('item_id',$hashRaw)->first();

        $product = Product::where('rs_id', $productId)->first();
        $brand = $product->brand()->first();

        if ($brand->lead_time >= 7 && $brand->lead_time <= 14) {
            $responseData['leadTime'] = '1-2 Weeks';
        } elseif ($brand->lead_time >= 14 && $brand->lead_time <= 28) {
            $responseData['leadTime'] = '2-4 Weeks';
        } else {
            $responseData['leadTime'] = '4-6 Weeks';
        }


        if ($stockItem instanceof StockItem) {
            $responseData['inStock'] = true;
        }

        return response()->json($responseData);
    }
}
