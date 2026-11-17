<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DistributionOrder extends Model
{
   protected $fillable = [
        'order_number',
        'school_id',
        'order_date',
        'total_quantity',
        'created_by',
        'notes',
        'delivery_agent',
        'receite_date'
    ];

        protected $casts = [
        'order_date' => 'date',
    ];
    // علاقة مع التفاصيل (الأصناف المنصرفة)
    public function school()
    {
        return $this->belongsTo(School::class);
    }
    
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    public function details()
    {
        return $this->hasMany(DistributionOrderDetail::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

       public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}