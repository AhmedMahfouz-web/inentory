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
        // Create product request permissions
        $permissions = [
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
