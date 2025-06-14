<?php

use Illuminate\Support\Facades\Route;

Route::get('/test', [\App\Http\Controllers\TestController::class, 'test']);



Route::get('/config/home', [\App\Http\Controllers\Ecom\EcommerceFrontendController::class, 'loadHomePage']);
Route::get('/config/menu', [\App\Http\Controllers\Ecom\EcommerceFrontendController::class, 'loadMenu']);
Route::get('/page/{slug}', [\App\Http\Controllers\Ecom\EcommerceFrontendController::class, 'loadPage']);

Route::get('/product/{slug}', [\App\Http\Controllers\Ecom\EcommerceProductController::class, 'loadProduct']);

Route::get('/category/{slug}', [\App\Http\Controllers\Ecom\EcommerceCategoryController::class, 'loadCategory']);
Route::get('/collection/{slug}', [\App\Http\Controllers\Ecom\EcommerceCategoryController::class, 'loadCollection']);
Route::get('/brand/{slug}', [\App\Http\Controllers\Ecom\EcommerceCategoryController::class, 'loadBrand']);

Route::post('/stock/{productId}', [\App\Http\Controllers\Ecom\EcommerceStockController::class, 'checkStock']);


Route::post('/checkout/update', [\App\Http\Controllers\Checkout\CheckoutController::class, 'updateCheckout']);
Route::get('/payment/intent', [\App\Http\Controllers\Checkout\CheckoutController::class, 'getPaymentIntentForCart']);
Route::get('/stripe/process', [\App\Http\Controllers\Checkout\CheckoutController::class, 'handleStripeReturn']);
Route::get('/postcode/{postcode}', [\App\Http\Controllers\Checkout\CheckoutController::class, 'postcodeLookup']);

Route::get('/order/{uuid}', [\App\Http\Controllers\OrderController::class, 'loadOrder']);

Route::get('/search', [\App\Http\Controllers\Ecom\EcommerceCategoryController::class, 'searchProducts']);

Route::post('/login', [\App\Http\Controllers\Auth\AuthenticationController::class, 'login']);
Route::post('/register', [\App\Http\Controllers\Auth\AuthenticationController::class, 'register']);
Route::get('/me', [\App\Http\Controllers\Auth\AuthenticationController::class, 'me']);


Route::get('/cart', [\App\Http\Controllers\Checkout\CartController::class, 'loadCart']);
Route::post('/cart/update', [\App\Http\Controllers\Checkout\CartController::class, 'updateCart']);
Route::delete('/cart/{lineItemId}', [\App\Http\Controllers\Checkout\CartController::class, 'deleteItemFromCart']);
Route::post('/cart/add', [\App\Http\Controllers\Checkout\CartController::class, 'addToCart']);
Route::post('/cart/quantity/{lineItemId}/{quantity}', [\App\Http\Controllers\Checkout\CartController::class, 'updateQuantity']);
