<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Branch;
use App\Models\UserBranch;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UserBranchController extends Controller
{
    /**
     * Display user-branch assignments
     */
    public function index(): View
    {
        $users = User::with(['userBranches.branch', 'roles'])->get();
        $branches = Branch::with('userBranches.user')->get();
        
        return view('user-branches.index', compact('users', 'branches'));
    }

    /**
     * Show form to assign branches to a user
     */
    public function edit(User $user): View
    {
        $branches = Branch::all();
        $userBranches = $user->userBranches()->with('branch')->get();
        
        return view('user-branches.edit', compact('user', 'branches', 'userBranches'));
    }

    /**
     * Update user branch assignments
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'branches' => 'array',
            'branches.*' => 'exists:branches,id',
            'can_request' => 'array',
            'can_manage' => 'array',
        ]);

        // Remove all existing assignments
        $user->userBranches()->delete();

        // Add new assignments
        if ($request->has('branches')) {
            foreach ($request->branches as $branchId) {
                UserBranch::create([
                    'user_id' => $user->id,
                    'branch_id' => $branchId,
                    'can_request' => in_array($branchId, $request->can_request ?? []),
                    'can_manage' => in_array($branchId, $request->can_manage ?? []),
                ]);
            }
        }

        return redirect()->route('user-branches.index')
                        ->with('success', 'تم تحديث صلاحيات الفروع للمستخدم بنجاح');
    }

    /**
     * Assign branch to user via AJAX
     */
    public function assign(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'branch_id' => 'required|exists:branches,id',
            'can_request' => 'boolean',
            'can_manage' => 'boolean',
        ]);

        $userBranch = UserBranch::updateOrCreate(
            [
                'user_id' => $request->user_id,
                'branch_id' => $request->branch_id,
            ],
            [
                'can_request' => $request->boolean('can_request', true),
                'can_manage' => $request->boolean('can_manage', false),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث صلاحيات الفرع بنجاح',
            'data' => $userBranch->load(['user', 'branch'])
        ]);
    }

    /**
     * Remove branch assignment from user
     */
    public function unassign(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'branch_id' => 'required|exists:branches,id',
        ]);

        UserBranch::where('user_id', $request->user_id)
                  ->where('branch_id', $request->branch_id)
                  ->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم إلغاء صلاحية الفرع بنجاح'
        ]);
    }

    /**
     * Get user's assigned branches (API)
     */
    public function getUserBranches(User $user)
    {
        $branches = $user->requestableBranches()->get();
        
        return response()->json([
            'success' => true,
            'branches' => $branches->map(function ($branch) {
                return [
                    'id' => $branch->id,
                    'name' => $branch->name,
                    'can_request' => $branch->pivot->can_request,
                    'can_manage' => $branch->pivot->can_manage,
                ];
            })
        ]);
    }
}
