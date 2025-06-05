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

        if ($stockItem instanceof StockItem) {
            $responseData['inStock'] = true;
        }



        return response()->json($responseData);
    }
}
