<?php

namespace App\Http\Controllers; // Adjust namespace if needed

use App\Services\RetailSystemSoapService;
use Illuminate\Routing\Controller;
class TestController extends Controller
{
    public function test(RetailSystemSoapService $retailSystemSoapService)
    {
        dd($retailSystemSoapService->getCatalog());
    }
}
