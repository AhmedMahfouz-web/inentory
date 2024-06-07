<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::all();

        return view('pages.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $permissions = Permission::all();
        $roles = Role::all();

        return view('pages.user.create', compact('permissions', 'roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // $user = User::create([
        //     'name' => $request->name,
        //     'username' => $request->username,
        //     'email' => $request->email,
        //     'password' => Hash::make($request->password),
        // ]);
        $user = User::find(11);
        return $user->getPermissionNames();
        foreach ($request->permissions as $permission) {
            $user->givePermissionTo($permission);
        };

        return redirect()->route('home')->with('success', 'تم اضافة المستخدم بنجاح');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }
}
