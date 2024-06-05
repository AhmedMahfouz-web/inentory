<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Start extends Model
{
    use HasFactory;

    protected $fillable = ['product_branch_id', 'month', 'qty'];
}
