<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SimplePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Comprehensive list of ALL permissions used across the application
        $permissions = [
            // Dashboard
            'dashboard-show',

            // Role management
            'role-list',
            'role-show',
            'role-create',
            'role-edit',
            'role-delete',
            
            // Product management
            'product-show',
            'product-create',
            'product-edit',
            'product-delete',
            
            // User management
            'user-show',
            'user-create',
            'user-edit',
            'user-delete',
            
            // Product added / Exchanges
            'product_added-show',
            'product_added-create',
            'product_added-edit',
            'product_added-delete',
            'exchange-show',
            'exchange-create',
            'exchange-edit',
            'exchange-delete',
            
            // Product increased / Increases
            'product_increased-show',
            'product_increased-create',
            'product_increased-edit',
            'product_increased-delete',
            'increase-show',
            'increase-create',
            'increase-edit',
            'increase-delete',
            
            // Unit management
            'unit-show',
            'unit-create',
            'unit-edit',
            'unit-delete',
            
            // Supplier management
            'supplier-show',
            'supplier-create',
            'supplier-edit',
            'supplier-delete',
            
            // Supplier category
            'supplier_category-show',
            'supplier_category-create',
            'supplier_category-edit',
            'supplier_category-delete',
            
            // Category management
            'category-show',
            'category-create',
            'category-edit',
            'category-delete',
            
            // Branch management
            'branch-show',
            'branch-create',
            'branch-edit',
            'branch-delete',
            
            // Product branch / Inventory
            'product_branch-show',
            'product_branch-create',
            'product_branch-edit',
            'product_branch-delete',
            'inventory-show',
            'inventory-edit',
            
            // Sells
            'sell-show',
            'sell-create',
            'sell-edit',
            'sell-delete',
            
            // Order management
            'order-show',
            'order-create',
            'order-edit',
            'order-delete',
            'order-approve',
            'order_show',
            'order_print',
            'order_edit',
            'order_delete',
            
            // Sub category
            'sub_category_show',
            'sub_category_create',
            'sub_category_edit',
            'sub_category_delete',
            
            // Starts
            'start-show',
            'start-create',
            'start-edit',
            'start-delete',
            
            // Reports
            'report-show',
            'report-export',
            
            // Settings
            'setting-show',
            'setting-edit',
            
            // Product request permissions
            'product-request-show',
            'product-request-create',
            'product-request-edit',
            'product-request-delete',
            'product-request-approve',
            'product-request-reject',
            'product-request-fulfill',
            'product-request-cancel',
            
            // User-branch management
            'user-branch-show',
            'user-branch-create',
            'user-branch-edit',
            'user-branch-delete',
            
            // Import permissions
            'import-products',
            'import-categories',
            'import-units',
            'import-sub-categories',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }
        $this->command->info("Verified/Created " . count($permissions) . " permissions.");

        // Create basic roles if they don't exist
        $roles = ['admin', 'manager', 'employee', 'warehouse_keeper', 'branch_manager'];
        
        foreach ($roles as $roleName) {
            Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web'
            ]);
        }

        // Give admin ALL permissions in the database
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $allPerms = Permission::all();
            $adminRole->syncPermissions($allPerms);
            $this->command->info("Assigned all " . $allPerms->count() . " permissions to admin role");
        }

        // Give employee basic permissions
        $employeeRole = Role::where('name', 'employee')->first();
        if ($employeeRole) {
            $employeeRole->givePermissionTo([
                'product-request-show',
                'product-request-create',
            ]);
        }

        // Create default admin user if none exists
        if (\App\Models\User::where('username', 'admin')->doesntExist()) {
            $user = \App\Models\User::create([
                'name' => 'Super Admin',
                'username' => 'admin',
                'email' => 'admin@example.com',
                'password' => 'password',
            ]);
            $user->assignRole('admin');
            $this->command->info("Created default admin user (username: admin / password: password)");
        }

        // Ensure all key admin accounts have the admin role assigned
        $adminUsers = \App\Models\User::whereIn('username', ['admin', 'abdallah'])
            ->orWhereIn('id', [1, 4])
            ->get();

        if ($adminUsers->isEmpty() && \App\Models\User::count() > 0) {
            $adminUsers = collect([\App\Models\User::first()]);
        }

        foreach ($adminUsers as $user) {
            $user->assignRole('admin');
            $this->command->info("Assigned admin role to user: {$user->name} ({$user->username})");
        }

        // Reset permission cache once again
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info('Permissions setup completed successfully!');
    }
}
