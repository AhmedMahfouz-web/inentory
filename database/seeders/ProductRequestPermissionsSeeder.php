<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ProductRequestPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create product request permissions
        $permissions = [
            'product-request-show' => 'عرض طلبات المنتجات',
            'product-request-create' => 'إنشاء طلبات المنتجات',
            'product-request-edit' => 'تعديل طلبات المنتجات',
            'product-request-delete' => 'حذف طلبات المنتجات',
            'product-request-approve' => 'الموافقة على طلبات المنتجات',
            'product-request-reject' => 'رفض طلبات المنتجات',
            'product-request-fulfill' => 'تنفيذ طلبات المنتجات',
            'product-request-cancel' => 'إلغاء طلبات المنتجات',
            
            // User-Branch management permissions
            'user-branch-show' => 'عرض صلاحيات الفروع',
            'user-branch-create' => 'إنشاء صلاحيات الفروع',
            'user-branch-edit' => 'تعديل صلاحيات الفروع',
            'user-branch-delete' => 'حذف صلاحيات الفروع',
        ];

        foreach ($permissions as $name => $displayName) {
            Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web'
            ]);
        }

        $this->command->info('Product request permissions created successfully!');

        // Assign permissions to existing roles
        $this->assignPermissionsToRoles();
    }

    /**
     * Assign permissions to existing roles
     */
    private function assignPermissionsToRoles()
    {
        // Get existing roles
        $adminRole = Role::where('name', 'admin')->first();
        $managerRole = Role::where('name', 'manager')->first();
        $employeeRole = Role::where('name', 'employee')->first();
        $warehouseKeeperRole = Role::where('name', 'warehouse_keeper')->first();

        // Admin gets all permissions
        if ($adminRole) {
            $adminRole->givePermissionTo([
                'product-request-show',
                'product-request-create',
                'product-request-edit',
                'product-request-delete',
                'product-request-approve',
                'product-request-reject',
                'product-request-fulfill',
                'product-request-cancel',
                'user-branch-show',
                'user-branch-create',
                'user-branch-edit',
                'user-branch-delete',
            ]);
            $this->command->info('Assigned all permissions to Admin role');
        }

        // Manager gets most permissions except user-branch management
        if ($managerRole) {
            $managerRole->givePermissionTo([
                'product-request-show',
                'product-request-create',
                'product-request-edit',
                'product-request-approve',
                'product-request-reject',
                'product-request-fulfill',
                'product-request-cancel',
            ]);
            $this->command->info('Assigned permissions to Manager role');
        }

        // Employee can create and view their own requests
        if ($employeeRole) {
            $employeeRole->givePermissionTo([
                'product-request-show',
                'product-request-create',
                'product-request-edit', // Only their own
                'product-request-cancel', // Only their own
            ]);
            $this->command->info('Assigned permissions to Employee role');
        }

        // Warehouse keeper can approve and fulfill requests
        if ($warehouseKeeperRole) {
            $warehouseKeeperRole->givePermissionTo([
                'product-request-show',
                'product-request-approve',
                'product-request-reject',
                'product-request-fulfill',
            ]);
            $this->command->info('Assigned permissions to Warehouse Keeper role');
        }

        // Create roles if they don't exist
        if (!$adminRole && !$managerRole && !$employeeRole && !$warehouseKeeperRole) {
            $this->createDefaultRoles();
        }
    }

    /**
     * Create default roles if they don't exist
     */
    private function createDefaultRoles()
    {
        $roles = [
            'admin' => 'مدير النظام',
            'manager' => 'مدير',
            'employee' => 'موظف',
            'warehouse_keeper' => 'أمين المخزن',
            'branch_manager' => 'مدير فرع',
        ];

        foreach ($roles as $name => $displayName) {
            $role = Role::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web'
            ]);
        }

        $this->command->info('Created default roles');
        
        // Re-run permission assignment
        $this->assignPermissionsToRoles();
    }
}
