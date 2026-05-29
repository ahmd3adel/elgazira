<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepartmentAllocation extends Model
{
    protected $table = 'department_allocations';
    
    protected $fillable = [
        'receite_date',
        'department_id',
        'created_by',
        'notes',
        'total_meals',
    ];
    
    // ✅ أضف هذا السطر لتحويل الحقل إلى Carbon object
    protected $casts = [
        'receite_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    // ✅ أو استخدم هذه الطريقة (إذا كان $casts لا يعمل)
    // protected $dates = ['receite_date', 'created_at', 'updated_at'];
    
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    public function items()
    {
        return $this->hasMany(DepartmentAllocationItem::class, 'allocation_id');
    }
}