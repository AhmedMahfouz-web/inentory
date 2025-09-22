<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UsersController extends Controller
{
    function __construct()
    {
        $this->middleware(['permission:user-show|user-create|user-edit|user-delete'], ['only' => ['index', 'show']]);
        $this->middleware(['permission:user-create'], ['only' => ['create', 'store']]);
        $this->middleware(['permission:user-edit'], ['only' => ['edit', 'update']]);
        $this->middleware(['permission:user-delete'], ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::all();
        $roles = Role::all();

        return view('pages.user.index', compact('users', 'roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::all();

        return view('pages.user.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => $request->password,
        ]);

        $user->assignRole([$request->role]);

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
        $roles = Role::all();

        return view('pages.user.edit', compact('roles', 'user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $user->update([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
        ]);

        if ($request->filled('password')) {
            $user->update(['password' => $request->password]);
        }

        if ($request->filled('role')) {
            $user->syncRoles([$request->role]);
        }

        return redirect()->route('show users')->with('success', 'تم تحديث المستخدم بنجاح');
    }

    /**
     * Show role assignment form
     */
    public function showAssignRole(User $user)
    {
        $roles = Role::with('permissions')->get();
        return view('users.assign-role', compact('user', 'roles'));
    }

    /**
     * Assign role to user
     */
    public function assignRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'nullable|exists:roles,name'
        ]);

        if ($request->filled('role')) {
            $user->syncRoles([$request->role]);
            $message = "تم تعيين دور '{$request->role}' للمستخدم '{$user->name}' بنجاح";
        } else {
            $user->syncRoles([]);
            $message = "تم إزالة جميع الأدوار من المستخدم '{$user->name}' بنجاح";
        }

        return redirect()->route('show users')->with('success', $message);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('show users')->with('success', 'تم حذف المستخدم بنجاح');
    }
}
