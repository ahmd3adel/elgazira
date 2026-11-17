<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'school_id',
        'department_id',
        'type',
        'reference_number',
        'quantity',
        'quantity_before',   // ✅
        'quantity_after',    // ✅
        'balance_after',
        'notes',
        'movement_date',     // ✅
        'user_id',
    ];

    protected $casts = [
        'movement_date' => 'datetime',
        'quantity' => 'integer',
        'quantity_before' => 'integer',
        'quantity_after' => 'integer',
        'balance_after' => 'integer',
    ];

    // ✅ العلاقات
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);  // ✅ يستخدم user_id تلقائياً
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'user_id');  // ✅ نفس الشيء
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}