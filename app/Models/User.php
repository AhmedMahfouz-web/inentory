<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'password',
        'email',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = bcrypt($password);
    }

    /**
     * Get the user's branch assignments
     */
    public function userBranches()
    {
        return $this->hasMany(UserBranch::class);
    }

    /**
     * Get branches the user can make requests for
     */
    public function requestableBranches()
    {
        return $this->belongsToMany(Branch::class, 'user_branches')
                    ->wherePivot('can_request', true)
                    ->withPivot(['can_request', 'can_manage'])
                    ->withTimestamps();
    }

    /**
     * Get branches the user can manage
     */
    public function manageableBranches()
    {
        return $this->belongsToMany(Branch::class, 'user_branches')
                    ->wherePivot('can_manage', true)
                    ->withPivot(['can_request', 'can_manage'])
                    ->withTimestamps();
    }

    /**
     * Check if user can make requests for a specific branch
     */
    public function canRequestForBranch($branchId)
    {
        return $this->userBranches()
                    ->where('branch_id', $branchId)
                    ->where('can_request', true)
                    ->exists();
    }

    /**
     * Check if user can manage a specific branch
     */
    public function canManageBranch($branchId)
    {
        return $this->userBranches()
                    ->where('branch_id', $branchId)
                    ->where('can_manage', true)
                    ->exists();
    }
}
