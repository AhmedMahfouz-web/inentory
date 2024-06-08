<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesController extends Controller
{
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

        $permissions = Permission::all();

        foreach ($permissions as $permission) {
            $role->revokePermissionTo($permission);
        }

        DB::beginTransaction();
        foreach ($request->permissions as $permission) {
            $role->givePermissionTo($permission);
        };
        DB::commit();

        return redirect()->route('show roles')->with('success', 'تم تعديل الوظيفة بنجاح');
    }
}
