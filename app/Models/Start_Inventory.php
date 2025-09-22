<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Start_Inventory extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'month', 'qty'];

    protected $dates = ['month'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
