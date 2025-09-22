<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class ProductRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_number',
        'branch_id',
        'requested_by',
        'status',
        'priority',
        'notes',
        'warehouse_notes',
        'approved_by',
        'fulfilled_by',
        'requested_at',
        'approved_at',
        'fulfilled_at'
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
        'fulfilled_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationships
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function fulfilledBy()
    {
        return $this->belongsTo(User::class, 'fulfilled_by');
    }

    public function items()
    {
        return $this->hasMany(ProductRequestItem::class);
    }

    // Scopes
    public function scopePending(Builder $query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved(Builder $query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected(Builder $query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeFulfilled(Builder $query)
    {
        return $query->where('status', 'fulfilled');
    }

    public function scopeByBranch(Builder $query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeByPriority(Builder $query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeUrgent(Builder $query)
    {
        return $query->where('priority', 'urgent');
    }

    public function scopeRecent(Builder $query, $days = 30)
    {
        return $query->where('requested_at', '>=', now()->subDays($days));
    }

    // Accessors
    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'في الانتظار',
            'approved' => 'موافق عليه',
            'rejected' => 'مرفوض',
            'partially_approved' => 'موافق عليه جزئياً',
            'fulfilled' => 'تم التنفيذ',
            'cancelled' => 'ملغي'
        ];

        return $labels[$this->status] ?? $this->status;
    }

    public function getPriorityLabelAttribute()
    {
        $labels = [
            'low' => 'منخفض',
            'medium' => 'متوسط',
            'high' => 'عالي',
            'urgent' => 'عاجل'
        ];

        return $labels[$this->priority] ?? $this->priority;
    }

    public function getPriorityColorAttribute()
    {
        $colors = [
            'low' => 'success',
            'medium' => 'info',
            'high' => 'warning',
            'urgent' => 'danger'
        ];

        return $colors[$this->priority] ?? 'secondary';
    }

    public function getStatusColorAttribute()
    {
        $colors = [
            'pending' => 'warning',
            'approved' => 'info',
            'rejected' => 'danger',
            'partially_approved' => 'primary',
            'fulfilled' => 'success',
            'cancelled' => 'secondary'
        ];

        return $colors[$this->status] ?? 'secondary';
    }

    public function getTotalItemsAttribute()
    {
        return $this->items()->count();
    }

    public function getTotalRequestedQtyAttribute()
    {
        return $this->items()->sum('requested_qty');
    }

    public function getTotalApprovedQtyAttribute()
    {
        return $this->items()->sum('approved_qty');
    }

    public function getTotalFulfilledQtyAttribute()
    {
        return $this->items()->sum('fulfilled_qty');
    }

    public function getEstimatedValueAttribute()
    {
        return $this->items()->sum(\DB::raw('requested_qty * unit_price'));
    }

    public function getIsOverdueAttribute()
    {
        if ($this->status === 'fulfilled' || $this->status === 'cancelled') {
            return false;
        }

        $daysSinceRequest = $this->requested_at->diffInDays(now());
        
        return match($this->priority) {
            'urgent' => $daysSinceRequest > 1,
            'high' => $daysSinceRequest > 3,
            'medium' => $daysSinceRequest > 7,
            'low' => $daysSinceRequest > 14,
            default => false
        };
    }

    // Methods
    public function approve($warehouseKeeperId, $notes = null)
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $warehouseKeeperId,
            'approved_at' => now(),
            'warehouse_notes' => $notes
        ]);

        return $this;
    }

    public function reject($warehouseKeeperId, $notes = null)
    {
        $this->update([
            'status' => 'rejected',
            'approved_by' => $warehouseKeeperId,
            'approved_at' => now(),
            'warehouse_notes' => $notes
        ]);

        return $this;
    }

    public function fulfill($warehouseKeeperId)
    {
        $this->update([
            'status' => 'fulfilled',
            'fulfilled_by' => $warehouseKeeperId,
            'fulfilled_at' => now()
        ]);

        return $this;
    }

    public function cancel()
    {
        $this->update([
            'status' => 'cancelled'
        ]);

        return $this;
    }

    public function canBeApproved()
    {
        return $this->status === 'pending';
    }

    public function canBeFulfilled()
    {
        return in_array($this->status, ['approved', 'partially_approved']);
    }

    public function canBeCancelled()
    {
        return !in_array($this->status, ['fulfilled', 'cancelled']);
    }

    // Generate unique request number
    public static function generateRequestNumber()
    {
        $prefix = 'REQ';
        $date = now()->format('Ymd');
        $lastRequest = static::whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();
        
        $sequence = $lastRequest ? (int) substr($lastRequest->request_number, -4) + 1 : 1;
        
        return $prefix . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    // Boot method to auto-generate request number
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->request_number)) {
                $model->request_number = static::generateRequestNumber();
            }
            
            if (empty($model->requested_at)) {
                $model->requested_at = now();
            }
        });
    }
}
