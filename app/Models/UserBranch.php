<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBranch extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'branch_id',
        'can_request',
        'can_manage',
    ];

    protected $casts = [
        'can_request' => 'boolean',
        'can_manage' => 'boolean',
    ];

    /**
     * Get the user that owns the UserBranch
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the branch that owns the UserBranch
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Scope to get users who can make requests for a branch
     */
    public function scopeCanRequest($query)
    {
        return $query->where('can_request', true);
    }

    /**
     * Scope to get users who can manage a branch
     */
    public function scopeCanManage($query)
    {
        return $query->where('can_manage', true);
    }
}
