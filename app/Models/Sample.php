<?php
// app/Models/Sample.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sample extends Model
{
    use HasFactory;

    protected $fillable = [
        'receiving_order_id',
        'warehouse_id',
        'department_id',
        'product_id',
        'quantity',
        'received_quantity',   // ✅ جديد
        'consumed_quantity',   // ✅ جديد
        'status',
        'notes',
        'user_id',
        'tested_at',
        'destroyed_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'received_quantity' => 'integer',
        'consumed_quantity' => 'integer',
        'tested_at' => 'datetime',
        'destroyed_at' => 'datetime',
    ];

    // ✅ المتاح من العينة (مش هيكون سالب أبداً)
    public function getAvailableQuantityAttribute(): int
    {
        return max(0, $this->received_quantity - $this->consumed_quantity);
    }

    // ✅ Scope: العينات المتاحة (لسه ما اتصرفتش بالكامل)
    public function scopeAvailable($query)
    {
        return $query->whereIn('status', ['pending', 'tested', 'approved'])
                     ->whereColumn('consumed_quantity', '<', 'received_quantity');
    }

    // باقي العلاقات زي ما هي
    public function receivingOrder()
    {
        return $this->belongsTo(ReceivingOrder::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}