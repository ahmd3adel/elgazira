<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceivingOrder extends Model
{
    use HasFactory;

    // الحقول القابلة للتعبئة
    protected $fillable = [
        'document_number',
        'warehouse_id',
        'product_id',           // ✅ أضف هذا
        'supplier_id',          // ✅ أضف هذا (بدلاً من supplier_name)
        'quantity',             // ✅ أضف هذا
        'samples_quantity',     // ✅ أضف هذا
        'arrival_time',
        'departure_time',
        'notes',
        'user_id',
        'batch_number'
    ];

    // التحويلات (Casts)
    protected $casts = [
        'arrival_time' => 'datetime',
        'departure_time' => 'datetime',
        'quantity' => 'integer',
        'samples_quantity' => 'integer'
    ];

    // العلاقات

    // أمر الاستلام ينتمي لمستخدم
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // أمر الاستلام ينتمي لمخزن
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    // أمر الاستلام ينتمي لمنتج
public function product()
{
    return $this->belongsTo(Product::class, 'product_id');
}

    // أمر الاستلام ينتمي لمورد
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    // أمر الاستلام له حركات مخزنية
    public function transactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    // أمر الاستلام له حركة واحدة (اختصار)
    public function inventoryTransaction()
    {
        return $this->hasOne(InventoryTransaction::class);
    }

    // دوال مساعدة

    // إجمالي الكمية (أساسي + عينات)
    public function getTotalQuantityAttribute()
    {
        return $this->quantity + $this->samples_quantity;
    }

    // الحصول على اسم المورد (للتكامل مع الكود القديم)
    public function getSupplierNameAttribute()
    {
        return $this->supplier->name ?? 'غير محدد';
    }


}