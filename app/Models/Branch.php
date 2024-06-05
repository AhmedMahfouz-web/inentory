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
}
