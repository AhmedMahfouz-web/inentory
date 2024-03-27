<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'desc',
        'address',
        'segel_togary',
        'segel_togary_image',
        'betaqa_drebya',
        'betaqa_drebya_image',
        'has_delivery',
    ];

    public function category()
    {
        return $this->belongsToMany(Category::class, 'supplier_categories', 'supplier_id', 'categories_id');
    }
}
