<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/test', [\App\Http\Controllers\TestController::class, 'test']);

Route::get('/config/menu', [\App\Http\Controllers\EcommerceFrontendController::class, 'loadMenu']);

Route::get('/product/{slug}', [\App\Http\Controllers\EcommerceFrontendController::class, 'loadProduct']);

Route::get('/category/{slug}', [\App\Http\Controllers\EcommerceCategoryController::class, 'loadCategory']);
