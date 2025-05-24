<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/test', [\App\Http\Controllers\TestController::class, 'test']);



Route::get('/config/menu', [\App\Http\Controllers\EcommerceFrontendController::class, 'loadMenu']);
Route::get('/product/{slug}', [\App\Http\Controllers\EcommerceFrontendController::class, 'loadProduct']);
Route::get('/category/{slug}', [\App\Http\Controllers\EcommerceCategoryController::class, 'loadCategory']);
Route::get('/collection/{slug}', [\App\Http\Controllers\EcommerceCategoryController::class, 'loadCollection']);
Route::get('/brand/{slug}', [\App\Http\Controllers\EcommerceCategoryController::class, 'loadBrand']);


Route::get('/payment/intent', [\App\Http\Controllers\CheckoutController::class, 'getPaymentIntentForCart']);
Route::post('/checkout/update', [\App\Http\Controllers\CheckoutController::class, 'updateCheckout']);

Route::get('/search', [\App\Http\Controllers\EcommerceCategoryController::class, 'searchProducts']);


Route::get('/cart', [\App\Http\Controllers\CartController::class, 'loadCart']);
Route::post('/cart/add', [\App\Http\Controllers\CartController::class, 'addToCart']);
