<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;
        public function governorate(){
        return $this->belongsTo(Governorate::class);
    }
    public function allocations()
{
    return $this->hasMany(DepartmentAllocation::class);
}

public function mainWarehouse()
{
    // نحدد أن العلاقة مع جدول المخازن عبر حقل main_warehouse_id
    return $this->belongsTo(Warehouse::class, 'main_warehouse_id');
}

public function operationWarehouse()
{
    // نحدد أن العلاقة مع جدول المخازن عبر حقل operation_warehouse_id
    return $this->belongsTo(Warehouse::class, 'operation_warehouse_id');
}

public function schools() {
    return $this->hasMany(School::class);
}
}
