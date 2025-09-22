<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ProductRequestItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_request_id',
        'product_id',
        'requested_qty',
        'approved_qty',
        'fulfilled_qty',
        'unit_price',
        'notes',
        'status'
    ];

    protected $casts = [
        'requested_qty' => 'decimal:2',
        'approved_qty' => 'decimal:2',
        'fulfilled_qty' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationships
    public function productRequest()
    {
        return $this->belongsTo(ProductRequest::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
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

    // Accessors
    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'في الانتظار',
            'approved' => 'موافق عليه',
            'rejected' => 'مرفوض',
            'fulfilled' => 'تم التنفيذ'
        ];

        return $labels[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute()
    {
        $colors = [
            'pending' => 'warning',
            'approved' => 'info',
            'rejected' => 'danger',
            'fulfilled' => 'success'
        ];

        return $colors[$this->status] ?? 'secondary';
    }

    public function getTotalRequestedValueAttribute()
    {
        return $this->requested_qty * $this->unit_price;
    }

    public function getTotalApprovedValueAttribute()
    {
        return $this->approved_qty * $this->unit_price;
    }

    public function getTotalFulfilledValueAttribute()
    {
        return $this->fulfilled_qty * $this->unit_price;
    }

    public function getIsPartiallyFulfilledAttribute()
    {
        return $this->fulfilled_qty > 0 && $this->fulfilled_qty < $this->approved_qty;
    }

    public function getIsFullyFulfilledAttribute()
    {
        return $this->fulfilled_qty >= $this->approved_qty;
    }

    public function getRemainingQtyAttribute()
    {
        return max(0, ($this->approved_qty ?? $this->requested_qty) - $this->fulfilled_qty);
    }

    // Methods
    public function approve($approvedQty, $notes = null)
    {
        $this->update([
            'approved_qty' => $approvedQty,
            'status' => 'approved',
            'notes' => $notes
        ]);

        return $this;
    }

    public function reject($notes = null)
    {
        $this->update([
            'approved_qty' => 0,
            'status' => 'rejected',
            'notes' => $notes
        ]);

        return $this;
    }

    public function fulfill($fulfilledQty)
    {
        $this->update([
            'fulfilled_qty' => $fulfilledQty,
            'status' => 'fulfilled'
        ]);

        return $this;
    }

    public function canBeApproved()
    {
        return $this->status === 'pending';
    }

    public function canBeFulfilled()
    {
        return $this->status === 'approved' && $this->approved_qty > 0;
    }

    public function hasAvailableStock()
    {
        return $this->product && $this->product->stock >= $this->requested_qty;
    }

    public function getAvailableStock()
    {
        return $this->product ? $this->product->stock : 0;
    }
}
