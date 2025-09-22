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
        // Create ALL permissions (including existing ones)
        $permissions = [
            // Role management
            'role-list',
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
            
            // Product added
            'product_added-show',
            'product_added-create',
            'product_added-edit',
            'product_added-delete',
            
            // Product increased
            'product_increased-show',
            'product_increased-create',
            'product_increased-edit',
            'product_increased-delete',
            
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
            
            // Product branch
            'product_branch-show',
            'product_branch-create',
            'product_branch-edit',
            'product_branch-delete',
            
            // Order management
            'order_show',
            'order_print',
            'order_edit',
            'order_delete',
            
            // Sub category
            'sub_category_show',
            'sub_category_create',
            'sub_category_edit',
            'sub_category_delete',
            
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
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
            $this->command->info("Created permission: {$permission}");
        }

        // Create basic roles if they don't exist
        $roles = ['admin', 'manager', 'employee', 'warehouse_keeper'];
        
        foreach ($roles as $roleName) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web'
            ]);
            $this->command->info("Created/found role: {$roleName}");
        }

        // Give admin all permissions
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->syncPermissions($permissions);
            $this->command->info("Assigned all permissions to admin role");
        }

        // Give employee basic permissions
        $employeeRole = Role::where('name', 'employee')->first();
        if ($employeeRole) {
            $employeeRole->givePermissionTo([
                'product-request-show',
                'product-request-create',
            ]);
            $this->command->info("Assigned basic permissions to employee role");
        }

        $this->command->info('Simple permissions seeder completed successfully!');
    }
}
