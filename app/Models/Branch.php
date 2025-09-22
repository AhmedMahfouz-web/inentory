<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];


    public function product_branches()
    {
        return $this->hasMany(Product_branch::class);
    }

    /**
     * Get the branch's user assignments
     */
    public function userBranches()
    {
        return $this->hasMany(UserBranch::class);
    }

    /**
     * Get users who can make requests for this branch
     */
    public function requestableUsers()
    {
        return $this->belongsToMany(User::class, 'user_branches')
                    ->wherePivot('can_request', true)
                    ->withPivot(['can_request', 'can_manage'])
                    ->withTimestamps();
    }

    /**
     * Get users who can manage this branch
     */
    public function manageableUsers()
    {
        return $this->belongsToMany(User::class, 'user_branches')
                    ->wherePivot('can_manage', true)
                    ->withPivot(['can_request', 'can_manage'])
                    ->withTimestamps();
    }
}
