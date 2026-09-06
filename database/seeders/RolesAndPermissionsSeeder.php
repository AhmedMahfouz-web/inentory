<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Clear existing permissions and roles
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('model_has_permissions')->truncate();
        DB::table('model_has_roles')->truncate();
        DB::table('role_has_permissions')->truncate();
        Permission::query()->delete();
        Role::query()->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Define all permissions grouped by module
        $permissions = [
            // Dashboard
            'dashboard-show' => 'عرض لوحة التحكم',

            // Products
            'product-show' => 'عرض المنتجات',
            'product-create' => 'إضافة منتج',
            'product-edit' => 'تعديل منتج',
            'product-delete' => 'حذف منتج',

            // Branches
            'branch-show' => 'عرض الفروع',
            'branch-create' => 'إضافة فرع',
            'branch-edit' => 'تعديل فرع',
            'branch-delete' => 'حذف فرع',

            // Categories
            'category-show' => 'عرض الأقسام',
            'category-create' => 'إضافة قسم',
            'category-edit' => 'تعديل قسم',
            'category-delete' => 'حذف قسم',

            // Units
            'unit-show' => 'عرض الوحدات',
            'unit-create' => 'إضافة وحدة',
            'unit-edit' => 'تعديل وحدة',
            'unit-delete' => 'حذف وحدة',

            // Suppliers
            'supplier-show' => 'عرض الموردين',
            'supplier-create' => 'إضافة مورد',
            'supplier-edit' => 'تعديل مورد',
            'supplier-delete' => 'حذف مورد',

            // Inventory
            'inventory-show' => 'عرض المخزون',
            'inventory-edit' => 'تعديل المخزون',

            // Sells (Sales)
            'sell-show' => 'عرض المبيعات',
            'sell-create' => 'إضافة عملية بيع',
            'sell-edit' => 'تعديل عملية بيع',
            'sell-delete' => 'حذف عملية بيع',

            // Orders
            'order-show' => 'عرض الطلبات',
            'order-create' => 'إنشاء طلب',
            'order-edit' => 'تعديل طلب',
            'order-delete' => 'حذف طلب',
            'order-approve' => 'الموافقة على الطلب',

            // Exchanges (Transfers)
            'exchange-show' => 'عرض التحويلات',
            'exchange-create' => 'إنشاء تحويل',
            'exchange-edit' => 'تعديل تحويل',
            'exchange-delete' => 'حذف تحويل',

            // Increases (Product Additions)
            'increase-show' => 'عرض الإضافات',
            'increase-create' => 'إضافة منتجات للمخزن',
            'increase-edit' => 'تعديل إضافة',
            'increase-delete' => 'حذف إضافة',

            // Monthly Starts
            'start-show' => 'عرض بداية الشهر',
            'start-create' => 'إنشاء بداية شهر',
            'start-edit' => 'تعديل بداية شهر',
            'start-delete' => 'حذف بداية شهر',

            // Product Requests
            'product-request-show' => 'عرض طلبات المنتجات',
            'product-request-create' => 'إنشاء طلب منتجات',
            'product-request-edit' => 'تعديل طلب منتجات',
            'product-request-delete' => 'حذف طلب منتجات',
            'product-request-approve' => 'الموافقة على طلبات المنتجات',
            'product-request-fulfill' => 'تنفيذ طلبات المنتجات',

            // Users
            'user-show' => 'عرض المستخدمين',
            'user-create' => 'إضافة مستخدم',
            'user-edit' => 'تعديل مستخدم',
            'user-delete' => 'حذف مستخدم',

            // Roles & Permissions
            'role-show' => 'عرض الأدوار',
            'role-create' => 'إضافة دور',
            'role-edit' => 'تعديل دور',
            'role-delete' => 'حذف دور',

            // Reports
            'report-show' => 'عرض التقارير',
            'report-export' => 'تصدير التقارير',

            // Settings
            'setting-show' => 'عرض الإعدادات',
            'setting-edit' => 'تعديل الإعدادات',
        ];

        // Create all permissions
        foreach ($permissions as $name => $label) {
            Permission::create([
                'name' => $name,
                'guard_name' => 'web'
            ]);
        }

        // Create roles and assign permissions

        // 1. Admin - Full access to everything
        $admin = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $admin->givePermissionTo(Permission::all());

        // 2. Manager - Most permissions except user/role management
        $manager = Role::create(['name' => 'manager', 'guard_name' => 'web']);
        $manager->givePermissionTo([
            'dashboard-show',
            'product-show',
            'product-create',
            'product-edit',
            'product-delete',
            'branch-show',
            'branch-create',
            'branch-edit',
            'category-show',
            'category-create',
            'category-edit',
            'unit-show',
            'unit-create',
            'unit-edit',
            'supplier-show',
            'supplier-create',
            'supplier-edit',
            'inventory-show',
            'inventory-edit',
            'sell-show',
            'sell-create',
            'sell-edit',
            'sell-delete',
            'order-show',
            'order-create',
            'order-edit',
            'order-delete',
            'exchange-show',
            'exchange-create',
            'exchange-edit',
            'increase-show',
            'increase-create',
            'increase-edit',
            'start-show',
            'start-create',
            'start-edit',
            'product-request-show',
            'product-request-create',
            'product-request-edit',
            'product-request-approve',
            'product-request-fulfill',
            'report-show',
            'report-export',
            'setting-show',
        ]);

        // 3. Warehouse Keeper - Inventory and approval permissions
        $warehouseKeeper = Role::create(['name' => 'warehouse_keeper', 'guard_name' => 'web']);
        $warehouseKeeper->givePermissionTo([
            'dashboard-show',
            'product-show',
            'inventory-show',
            'inventory-edit',
            'increase-show',
            'increase-create',
            'start-show',
            'start-create',
            'start-edit',
            'product-request-show',
            'product-request-approve',
            'report-show',
        ]);

        // 4. Branch Manager - Branch-specific permissions
        $branchManager = Role::create(['name' => 'branch_manager', 'guard_name' => 'web']);
        $branchManager->givePermissionTo([
            'dashboard-show',
            'product-show',
            'inventory-show',
            'sell-show',
            'sell-create',
            'sell-edit',
            'order-show',
            'order-create',
            'order-edit',
            'exchange-show',
            'exchange-create',
            'product-request-show',
            'product-request-create',
            'product-request-edit',
            'report-show',
        ]);



        $this->command->info('Roles and permissions created successfully!');
        $this->command->info('Created roles: admin, manager, warehouse_keeper, branch_manager, cashier, viewer');
        $this->command->info('Total permissions: ' . count($permissions));
    }
}
