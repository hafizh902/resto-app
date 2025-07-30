<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'name',
        'description',
        'created_at',
        'updated_at'
    ];
    protected $dates = ['deleted_at'];
    public function users()
    {
        return $this->hasMany(User::class, 'role_id');
    }
}