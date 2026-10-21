<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


//admin routes
Route::middleware(['web', 'auth', 'admin'])->prefix('admin')->name('admin.')->group(function() {
    //categories
    Route::prefix('categories')->name('categories.')->group(function() {
        Route::get('new', [\App\Http\Controllers\Admin\CategoryController::class, 'newCategory'])->name('new');
        Route::get('', [\App\Http\Controllers\Admin\CategoryController::class, 'index'])->name('index');
        Route::get('all', [\App\Http\Controllers\Admin\CategoryController::class, 'all'])->name('all');
        Route::get('{id}', [\App\Http\Controllers\Admin\CategoryController::class, 'show'])->name('show');
        Route::post('create', [\App\Http\Controllers\Admin\CategoryController::class, 'create'])->name('create');
        Route::put('{id}/update', [\App\Http\Controllers\Admin\CategoryController::class, 'update'])->name('update');
    });
    //products
    Route::prefix('products')->name('products.')->group(function() {
        Route::get('new', [\App\Http\Controllers\Admin\ProductController::class, 'newProduct'])->name('new');
        Route::get('', [\App\Http\Controllers\Admin\ProductController::class, 'index'])->name('index');
        Route::get('all', [\App\Http\Controllers\Admin\ProductController::class, 'all'])->name('all');
        Route::get('{id}', [\App\Http\Controllers\Admin\ProductController::class, 'show'])->name('show');
        Route::post('create', [\App\Http\Controllers\Admin\ProductController::class, 'create'])->name('create');
        Route::put('{id}/update', [\App\Http\Controllers\Admin\ProductController::class, 'update'])->name('update');
    });
    //modifier groups
    Route::prefix('modifier-groups')->name('modifier-groups.')->group(function() {
        Route::get('new', [\App\Http\Controllers\Admin\ModifierGroupController::class, 'newModifierGroup'])->name('new');
        Route::get('', [\App\Http\Controllers\Admin\ModifierGroupController::class, 'index'])->name('index');
        Route::get('all', [\App\Http\Controllers\Admin\ModifierGroupController::class, 'all'])->name('all');
        Route::get('{id}', [\App\Http\Controllers\Admin\ModifierGroupController::class, 'show'])->name('show');
        Route::post('create', [\App\Http\Controllers\Admin\ModifierGroupController::class, 'create'])->name('create');
        Route::put('{id}/update', [\App\Http\Controllers\Admin\ModifierGroupController::class, 'update'])->name('update');
    });
    //modifiers
    Route::prefix('modifiers')->name('modifiers.')->group(function() {
        Route::get('new', [\App\Http\Controllers\Admin\ModifierController::class, 'newModifier'])->name('new');
        Route::get('', [\App\Http\Controllers\Admin\ModifierController::class, 'index'])->name('index');
        Route::get('all', [\App\Http\Controllers\Admin\ModifierController::class, 'all'])->name('all');
        Route::get('{id}', [\App\Http\Controllers\Admin\ModifierController::class, 'show'])->name('show');
        Route::post('create', [\App\Http\Controllers\Admin\ModifierController::class, 'create'])->name('create');
        Route::put('{id}/update', [\App\Http\Controllers\Admin\ModifierController::class, 'update'])->name('update');
    });
    //images
    Route::prefix('{entity}/images')->name('images.')->group(function () {
        Route::get('', [\App\Http\Controllers\Admin\ImageController::class, 'index'])->name('index');
        Route::post('upload', [\App\Http\Controllers\Admin\ImageController::class, 'upload'])->name('upload');
        Route::delete('{id}/delete', [\App\Http\Controllers\Admin\ImageController::class, 'delete'])->name('delete');
        Route::get('all', [\App\Http\Controllers\Admin\ImageController::class, 'all'])->name('all');
    });
    //orders
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('', [\App\Http\Controllers\Admin\OrderController::class, 'index'])->name('index');
        Route::get('all', [\App\Http\Controllers\Admin\OrderController::class, 'all'])->name('all');
        Route::get('{id}', [\App\Http\Controllers\Admin\OrderController::class, 'show'])->name('show');
        Route::patch('update-status/{orderId}', [\App\Http\Controllers\Admin\OrderController::class, 'updateStatus'])->name('update-order-status');
    });
    Route::get('table-orders', [\App\Http\Controllers\Admin\OrderController::class, 'tableOrders'])->name('tableOrders');
});

require __DIR__.'/auth.php';
