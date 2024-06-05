<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code'
    ];

    public function supplier()
    {
        return $this->belongsToMany(supplier::class, 'supplier_categories', 'categories_id', 'supplier_id');
    }

    public function product()
    {
        return $this->hasManyThrough(Product::class, SubCategory::class, 'sub_category_id', 'category_id', 'id');
    }

    public function sub_category()
    {
        return $this->hasMany(SubCategory::class);
    }
}
