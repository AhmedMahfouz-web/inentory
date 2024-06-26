<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\IncreasedProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductAddedController;
use App\Http\Controllers\ProductBranchController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\SellController;
use App\Http\Controllers\StartController;
use App\Http\Controllers\StartInventoryController;
use App\Http\Controllers\SubCategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UsersController;
use App\Models\IncreasedProduct;
use App\Models\Product_branch;
use App\Models\ProductAdded;
use App\Models\Start;
use Illuminate\Support\Facades\Auth;
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

Route::group(['middleware' => 'auth'], function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');

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

    Route::group(['prefix' => 'sub_category', 'controller' => SubCategoryController::class], function ($router) {
        Route::get('/', 'index')->name('show sub_categories');
        Route::get('/create', 'create')->name('create sub_category');
        Route::post('/store', 'store')->name('store sub_category');
        Route::get('/edit/{category}', 'edit')->name('edit sub_category');
        Route::post('/update/{category}', 'update')->name('update sub_category');
    });

    Route::group(['prefix' => 'product', 'controller' => ProductController::class], function ($router) {
        Route::get('/', 'index')->name('show products');
        Route::get('/create', 'create')->name('create product');
        Route::post('/store', 'store')->name('store product');
        Route::get('/edit/{product}', 'edit')->name('edit product');
        Route::post('/update/{product}', 'update')->name('update product');
        Route::get('/inventory', 'inventory')->name('product inventory');
        Route::post('/inventory', 'inventory')->name('product inventory date');
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

    Route::group(['prefix' => 'order', 'controller' => OrderController::class], function ($router) {
        Route::get('/', 'index')->name('show order');
        Route::post('/', 'index')->name('show order date');
        Route::post('/update/{order}', 'update')->name('update order');
        Route::get('/edit/{order}', 'edit')->name('edit product_added');
        Route::get('/print/{order}', 'print')->name('print');
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
        Route::get('/edit/{product_increased}', 'edit')->name('edit increase product');
        Route::post('/update/{product_increased}', 'update')->name('update increase product');
    });

    Route::group(['prefix' => 'branch-inventory', 'controller' => ProductBranchController::class], function ($router) {
        Route::get('/{branch_id}', 'index')->name('inventory');
        Route::post('/{branch_id}', 'index')->name('inventory date');
        Route::get('/{branc_id}/create', 'create')->name('create inventory');
        Route::post('/{branch_id}/store', 'store')->name('store inventory');
    });

    Route::group(['prefix' => 'start', 'controller' => StartController::class], function ($router) {
        Route::get('{branch_id}', 'index')->name('start');
        Route::post('store/{branch_id}', 'store')->name('store start');
        Route::get('store/auto', 'store_auto')->name('store start auto');
    });

    Route::group(['prefix' => 'inventory', 'controller' => StartInventoryController::class], function ($router) {
        Route::get('/start', 'index')->name('start inventory');
        Route::post('/start', 'store')->name('store start_inventory');
        Route::get('/qty_start', 'qty_store');
    });

    Route::group(['prefix' => 'sell', 'controller' => SellController::class], function ($router) {
        Route::get('{branch_id}', 'index')->name('sell');
        Route::post('{branch_id}', 'store')->name('sell product');
    });

    Route::group(['prefix' => 'users', 'controller' => UsersController::class], function ($router) {
        Route::get('users', 'index')->name('show users');
        Route::get('/create', 'create')->name('create user');
        Route::post('/store', 'store')->name('store user');
        Route::get('/edit/{user}', 'edit')->name('edit user');
        Route::post('/update/{user}', 'update')->name('update user');
    });

    Route::group(['prefix' => 'roles', 'controller' => RolesController::class], function ($router) {
        Route::get('/', 'index')->name('show roles');
        Route::get('/create', 'create')->name('create role');
        Route::post('/store', 'store')->name('store role');
        Route::get('/edit/{role}', 'edit')->name('edit role');
        Route::post('/update/{role}', 'update')->name('update role');
    });

    Route::group(['controller' => LoginController::class], function ($router) {
        Route::post('/logout', 'logout')->name('logout');
    });
});

Route::group(['controller' => LoginController::class], function ($router) {
    Route::get('/login', 'login')->name('login');
    Route::post('/login', 'post_login')->name('post login');
});
