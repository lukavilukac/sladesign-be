<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::middleware('api')->group(function () {
    Route::post('orders/create', [\App\Http\Controllers\Api\OrderController::class, 'create'])->middleware(\App\Http\Middleware\TrackOrderAttempt::class);

    Route::get('categories', [\App\Http\Controllers\Api\CategoryController::class, 'all']);
    Route::get('products', [\App\Http\Controllers\Api\ProductController::class, 'all']);
    Route::get('products/find-by-slug/{slug}', [\App\Http\Controllers\Api\ProductController::class, 'findBySlug']);
    Route::get('products/get-by-category-slug/{slug}', [\App\Http\Controllers\Api\ProductController::class, 'getByCategorySlug']);
    Route::get('products-and-categories', [\App\Http\Controllers\Api\ProductController::class, 'getProductsAndCategories']);
});
