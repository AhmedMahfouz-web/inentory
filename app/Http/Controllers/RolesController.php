<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:role-show|role-create|role-edit|role-delete'], ['only' => ['index', 'show']]);
        $this->middleware(['permission:role-create'], ['only' => ['create', 'store']]);
        $this->middleware(['permission:role-edit'], ['only' => ['edit', 'update']]);
        $this->middleware(['permission:role-delete'], ['only' => ['destroy']]);
    }

    public function index()
    {
        $roles = Role::all();

        return view('pages.role.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::all();

        return view('pages.role.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        $role = Role::create(['name' => $request->name]);

        foreach ($request->permissions as $permission) {
            $role->givePermissionTo($permission);
        };
        DB::commit();

        return redirect()->route('show roles')->with('success', 'تم اضافة الوظيفة بنجاح');
    }


    public function edit(Role $role)
    {
        $permissions = Permission::all();

        return view('pages.role.edit', compact('permissions', 'role'));
    }

    public function update(Request $request, Role $role)
    {
        $role->update(['name' => $request->name]);

        $submitted = array_values($request->permissions ?? []);

        // Auto-sync alias permissions
        $aliases = [
            'product_added-show' => 'exchange-show',
            'product_added-create' => 'exchange-create',
            'product_added-edit' => 'exchange-edit',
            'product_added-delete' => 'exchange-delete',
            'product_increased-show' => 'increase-show',
            'product_increased-create' => 'increase-create',
            'product_increased-edit' => 'increase-edit',
            'product_increased-delete' => 'increase-delete',
            'product_branch-show' => 'inventory-show',
            'product_branch-edit' => 'inventory-edit',
            'order_show' => 'order-show',
            'order_create' => 'order-create',
            'order_edit' => 'order-edit',
            'order_delete' => 'order-delete',
        ];

        foreach ($aliases as $from => $to) {
            if (in_array($from, $submitted)) {
                $submitted[] = $to;
                Permission::firstOrCreate(['name' => $to, 'guard_name' => 'web']);
            }
        }

        // Always ensure dashboard-show is granted
        Permission::firstOrCreate(['name' => 'dashboard-show', 'guard_name' => 'web']);
        $submitted[] = 'dashboard-show';

        $role->syncPermissions(array_unique($submitted));
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('show roles')->with('success', 'تم تعديل الوظيفة بنجاح');
    }
}
