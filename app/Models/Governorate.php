<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Governorate extends Model
{
    use HasFactory;
    protected $fillable = [
    'name', 
    'code', 
    'manager_name', 
    'manager_phone', 
    'status', 
    'sort_order', 
    'notes'
];

public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function departments(){
        return $this->belongsToMany(Department::class);
    }
}
