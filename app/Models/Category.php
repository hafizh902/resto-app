<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'cat_name',
        'description',
        'created_at',
        'updated_at'
    ];
    protected $dates = ['deleted_at'];

    public function items()
    {
        return $this->hasMany(Item::class, 'category_id');
    }

    public function orderItems()
    {
        return $this->hasMany(Order_item::class, 'category_id');
    }
}