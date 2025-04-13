<?php

namespace App\Http\Controllers; // Adjust namespace if needed

use App\Models\Product\ProductCategory;
use Illuminate\Routing\Controller;
class TestController extends Controller
{
    public function test()
    {
        $category = ProductCategory::find(119);

        dd($category->products()->get());
    }
}
