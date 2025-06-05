<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/test', [\App\Http\Controllers\TestController::class, 'test']);



Route::get('/config/home', [\App\Http\Controllers\EcommerceFrontendController::class, 'loadHomePage']);
Route::get('/config/menu', [\App\Http\Controllers\EcommerceFrontendController::class, 'loadMenu']);
Route::get('/product/{slug}', [\App\Http\Controllers\EcommerceFrontendController::class, 'loadProduct']);
Route::get('/page/{slug}', [\App\Http\Controllers\EcommerceFrontendController::class, 'loadPage']);

Route::get('/category/{slug}', [\App\Http\Controllers\EcommerceCategoryController::class, 'loadCategory']);
Route::get('/collection/{slug}', [\App\Http\Controllers\EcommerceCategoryController::class, 'loadCollection']);
Route::get('/brand/{slug}', [\App\Http\Controllers\EcommerceCategoryController::class, 'loadBrand']);

Route::post('/stock/{productId}', [\App\Http\Controllers\EcommerceStockController::class, 'checkStock']);


Route::post('/checkout/update', [\App\Http\Controllers\CheckoutController::class, 'updateCheckout']);
Route::get('/payment/intent', [\App\Http\Controllers\CheckoutController::class, 'getPaymentIntentForCart']);
Route::get('/stripe/process', [\App\Http\Controllers\CheckoutController::class, 'handleStripeReturn']);
Route::get('/postcode/{postcode}', [\App\Http\Controllers\CheckoutController::class, 'postcodeLookup']);

Route::get('/order/{uuid}', [\App\Http\Controllers\OrderController::class, 'loadOrder']);

Route::get('/search', [\App\Http\Controllers\EcommerceCategoryController::class, 'searchProducts']);

Route::post('/login', [\App\Http\Controllers\AuthenticationController::class, 'login']);
Route::post('/register', [\App\Http\Controllers\AuthenticationController::class, 'register']);
Route::get('/me', [\App\Http\Controllers\AuthenticationController::class, 'me']);


Route::get('/cart', [\App\Http\Controllers\CartController::class, 'loadCart']);
Route::post('/cart/add', [\App\Http\Controllers\CartController::class, 'addToCart']);
Route::post('/cart/quantity/{lineItemId}/{quantity}', [\App\Http\Controllers\CartController::class, 'updateQuantity']);
