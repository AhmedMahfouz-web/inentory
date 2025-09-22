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
use App\Http\Controllers\MonthlyStartController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProductRequestController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SubCategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserBranchController;
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

    Route::get('/categories/{id}/sold-products/{date}', [CategoryController::class, 'getSoldProductsByCategory'])->name('categories.soldProducts');
    Route::get('/sold-by-category/{branch_id}', [CategoryController::class, 'soldReport'])->name('reports sold by category');

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
        Route::get('store/auto-mysql', 'store_auto_mysql')->name('store start auto mysql');
    });

    Route::group(['prefix' => 'inventory', 'controller' => StartInventoryController::class], function ($router) {
        Route::get('/start', 'index')->name('start inventory');
        Route::post('/start', 'store')->name('store start_inventory');
        Route::get('/qty_start', 'qty_store');
        Route::get('/auto_generate', 'auto_generate')->name('auto generate main inventory');
    });

    // New Monthly Start Management Routes
    Route::group(['prefix' => 'monthly-starts', 'controller' => MonthlyStartController::class], function ($router) {
        Route::get('/', 'index')->name('monthly starts');
        Route::post('/generate-current', 'generateCurrent')->name('generate current month starts');
        Route::post('/generate-month', 'generateForMonth')->name('generate month starts');
        Route::get('/report', 'report')->name('monthly starts report');
        Route::get('/category-report', 'categoryReport')->name('monthly starts category report');
        Route::get('/check-exists', 'checkExists')->name('check monthly starts exists');
        Route::get('/summary', 'getSummary')->name('monthly starts summary');
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
        Route::put('/update/{user}', 'update')->name('update user');
        Route::delete('/delete/{user}', 'destroy')->name('delete user');
        
        // Role assignment routes
        Route::get('/{user}/assign-role', 'showAssignRole')->name('users.assign-role-form');
        Route::post('/{user}/assign-role', 'assignRole')->name('users.assign-role');
    });

    Route::group(['controller' => RolesController::class, 'prefix' => 'roles'], function ($router) {
        Route::get('/', 'index')->name('show roles');
        Route::get('/create', 'create')->name('create role');
        Route::post('/create', 'store')->name('store role');
        Route::get('/{role}/edit', 'edit')->name('edit role');
        Route::put('/{role}', 'update')->name('update role');
        Route::delete('/{role}', 'destroy')->name('delete role');
    });

    // User-Branch Assignment Routes
    Route::group(['controller' => UserBranchController::class, 'prefix' => 'user-branches'], function ($router) {
        Route::get('/', 'index')->name('user-branches.index');
        Route::get('/{user}/edit', 'edit')->name('user-branches.edit');
        Route::put('/{user}', 'update')->name('user-branches.update');
        
        // API routes for AJAX operations
        Route::post('/assign', 'assign')->name('user-branches.assign');
        Route::delete('/unassign', 'unassign')->name('user-branches.unassign');
        Route::get('/{user}/branches', 'getUserBranches')->name('user-branches.get-user-branches');
    });

    Route::group(['prefix' => 'product-requests', 'controller' => ProductRequestController::class], function ($router) {
        // Branch routes
        Route::get('/', 'index')->name('product-requests.index');
        Route::get('/create', 'create')->name('product-requests.create');
        Route::post('/store', 'store')->name('product-requests.store');
        Route::get('/{productRequest}', 'show')->name('product-requests.show');
        Route::post('/{productRequest}/cancel', 'cancel')->name('product-requests.cancel');
        
        // Warehouse keeper routes
        Route::get('/warehouse/dashboard', 'warehouseDashboard')->name('product-requests.warehouse-dashboard');
        Route::get('/{productRequest}/approve', 'showApprove')->name('product-requests.show-approve');
        Route::post('/{productRequest}/approve', 'approve')->name('product-requests.approve');
        Route::get('/{productRequest}/fulfill', 'showFulfill')->name('product-requests.show-fulfill');
        Route::post('/{productRequest}/fulfill', 'fulfill')->name('product-requests.fulfill');
        
        // API routes
        Route::get('/api/{productRequest}/data', 'getRequestData')->name('product-requests.api.data');
        Route::get('/api/pending-count', 'getPendingCount')->name('product-requests.api.pending-count');
        Route::get('/api/statistics', 'getStatistics')->name('product-requests.api.statistics');
        Route::get('/api/products/search', 'searchProducts')->name('product-requests.api.search-products');
        Route::get('/api/products/{product}/stock', 'getProductStock')->name('product-requests.api.product-stock');
    });

    // API Routes for Header functionality
    Route::group(['prefix' => 'api'], function () {
        // Notifications
        Route::get('/notifications', [NotificationController::class, 'getNotifications'])->name('api.notifications');
        Route::get('/notifications/counts', [NotificationController::class, 'getNotificationCounts'])->name('api.notifications.counts');
        Route::post('/notifications/mark-read', [NotificationController::class, 'markAsRead'])->name('api.notifications.mark-read');
        Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('api.notifications.mark-all-read');
        
        // Search
        Route::get('/search', [SearchController::class, 'globalSearch'])->name('api.search');
        Route::get('/search/products', [SearchController::class, 'quickProductSearch'])->name('api.search.products');
        Route::get('/search/suggestions', [SearchController::class, 'getSearchSuggestions'])->name('api.search.suggestions');
    });

    // Debug route to test user-branch assignments
    Route::get('/debug/user-branches', function() {
        $user = auth()->user();
        $branches = \App\Models\Branch::all();
        
        // Assign first branch to current user if no assignments exist
        $existingAssignments = \App\Models\UserBranch::where('user_id', $user->id)->count();
        
        if ($existingAssignments == 0 && $branches->isNotEmpty()) {
            \App\Models\UserBranch::create([
                'user_id' => $user->id,
                'branch_id' => $branches->first()->id,
                'can_request' => true,
                'can_manage' => false,
            ]);
            
            return "Assigned branch '{$branches->first()->name}' to user '{$user->name}'";
        }
        
        $userBranches = \App\Models\UserBranch::where('user_id', $user->id)->with('branch')->get();
        
        return [
            'user' => $user->name,
            'assignments' => $userBranches->map(function($ub) {
                return [
                    'branch' => $ub->branch->name,
                    'can_request' => $ub->can_request,
                    'can_manage' => $ub->can_manage,
                ];
            })
        ];
    });

    // Debug route to setup permissions and assign admin role
    Route::get('/debug/setup-permissions', function() {
        try {
            // Run the seeder
            Artisan::call('db:seed', ['--class' => 'SimplePermissionsSeeder']);
            
            $user = auth()->user();
            
            // Assign admin role to current user
            $adminRole = \Spatie\Permission\Models\Role::where('name', 'admin')->first();
            if ($adminRole) {
                $user->assignRole('admin');
                
                // Get all permissions and assign to admin
                $allPermissions = \Spatie\Permission\Models\Permission::all();
                $adminRole->syncPermissions($allPermissions);
                
                $message = "✅ All {$allPermissions->count()} permissions created and assigned to admin role";
                $message .= "<br>✅ Admin role assigned to '{$user->name}'";
            } else {
                $message = "❌ Admin role not found after seeding";
            }
            
            // Also assign a branch
            $branches = \App\Models\Branch::all();
            if ($branches->isNotEmpty()) {
                \App\Models\UserBranch::updateOrCreate([
                    'user_id' => $user->id,
                    'branch_id' => $branches->first()->id,
                ], [
                    'can_request' => true,
                    'can_manage' => true,
                ]);
                $message .= "<br>✅ Branch '{$branches->first()->name}' assigned";
            }
            
            // Show what permissions admin has
            $adminPermissions = $adminRole->permissions->pluck('name')->toArray();
            $message .= "<br><br>📋 Admin permissions include: " . implode(', ', array_slice($adminPermissions, 0, 10));
            if (count($adminPermissions) > 10) {
                $message .= " and " . (count($adminPermissions) - 10) . " more...";
            }
            
            return $message;
            
        } catch (\Exception $e) {
            return "❌ Error: " . $e->getMessage();
        }
    });

    Route::group(['controller' => LoginController::class], function ($router) {
        Route::post('/logout', 'logout')->name('logout');
    });
});

Route::group(['controller' => LoginController::class], function ($router) {
    Route::get('/login', 'login')->name('login');
    Route::post('/login', 'post_login')->name('post login');
});
