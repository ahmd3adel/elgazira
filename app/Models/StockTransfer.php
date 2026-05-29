<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_warehouse_id',
        'to_warehouse_id',
        'type',
        'notes',
    ];

    /**
     * العلاقة مع المخزن المصدر (الذي خرجت منه البضاعة)
     */
    public function fromWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    /**
     * العلاقة مع المخزن المستلم (الذي استقبل البضاعة)
     */
    public function toWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    /**
     * العلاقة مع الأصناف المحولة (تفاصيل التحويل)
     */
    public function items()
    {
        return $this->hasMany(StockTransferItem::class);
    }
}