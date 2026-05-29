<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    protected $fillable = [
    'name', 
    'code', 
    'type', 
    'governorate_id', 
    'parent_id', 
    'manager_name', 
    'manager_phone', 
    'address', 
    'status'
];
    // العلاقة مع المحافظة
    public function governorate()
    {
        return $this->belongsTo(Governorate::class);
    }

    // علاقة المخزن الفرعي بالأب (الرئيسي)
    public function parentMainWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'parent_id');
    }

    // علاقة المخزن الرئيسي بالأبناء (الفرعيين)
    public function subWarehouses()
    {
        return $this->hasMany(Warehouse::class, 'parent_id');
    }

    // Scope لجلب المخازن الرئيسية فقط بسهولة
    public function scopeMain($query)
    {
        return $query->where('type', 'main');
    }
}
