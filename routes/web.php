<?php

use App\Http\Controllers\BranchController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\IncreasedProductController;
use App\Http\Controllers\ProductAddedController;
use App\Http\Controllers\ProductBranchController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UnitController;
use App\Models\IncreasedProduct;
use App\Models\Product_branch;
use App\Models\ProductAdded;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::group(['prefix' => 'unit', 'controller' => UnitController::class], function ($router) {
    Route::get('/', 'index')->name('show units');
    Route::get('/create', 'create')->name('create unit');
    Route::post('/store', 'store')->name('store unit');
    Route::get('/edit/{unit}', 'edit')->name('edit unit');
    Route::post('/update/{unit}', 'update')->name('update unit');
});

Route::group(['prefix' => 'category', 'controller' => CategoryController::class], function ($router) {
    Route::get('/', 'index')->name('show categories');
    Route::get('/create', 'create')->name('create category');
    Route::post('/store', 'store')->name('store category');
    Route::get('/edit/{category}', 'edit')->name('edit category');
    Route::post('/update/{category}', 'update')->name('update category');
});

Route::group(['prefix' => 'product', 'controller' => ProductController::class], function ($router) {
    Route::get('/', 'index')->name('show products');
    Route::get('/create', 'create')->name('create product');
    Route::post('/store', 'store')->name('store product');
    Route::get('/edit/{product}', 'edit')->name('edit product');
    Route::post('/update/{product}', 'update')->name('update product');
});

Route::group(['prefix' => 'branch', 'controller' => BranchController::class], function ($router) {
    Route::get('/', 'index')->name('show branches');
    Route::get('/create', 'create')->name('create branch');
    Route::post('/store', 'store')->name('store branch');
    Route::get('/edit/{branch}', 'edit')->name('edit branch');
    Route::post('/update/{branch}', 'update')->name('update branch');
});

Route::group(['prefix' => 'supplier', 'controller' => SupplierController::class], function ($router) {
    Route::get('/', 'index')->name('show suppliers');
    Route::get('/create', 'create')->name('create supplier');
    Route::post('/store', 'store')->name('store supplier');
    Route::get('/edit/{supplier}', 'edit')->name('edit supplier');
    Route::post('/update/{supplier}', 'update')->name('update supplier');
});

Route::group(['prefix' => 'product_exchange', 'controller' => ProductAddedController::class], function ($router) {
    Route::get('/', 'index')->name('exchanged product');
    Route::post('/', 'index')->name('exchanged product date');
    Route::get('/create', 'create')->name('create exchange product');
    Route::post('/store', 'store')->name('exchange product');
});

Route::group(['prefix' => 'product_increase', 'controller' => IncreasedProductController::class], function ($router) {
    Route::get('/', 'index')->name('increased product');
    Route::post('/', 'index')->name('increased product date');
    Route::get('/create', 'create')->name('create increase product');
    Route::post('/store', 'store')->name('increase product');
});

Route::group(['prefix' => 'branch-inventory', 'controller' => ProductBranchController::class], function ($router) {
    Route::get('/{branch_id}', 'index')->name('inventory');
    Route::get('/{branc_id}/create', 'create')->name('create inventory');
    Route::post('/{branch_id}/store', 'store')->name('store inventory');
});
