<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ReceivingOrder extends Model
{
    use HasFactory;

    // ✅ الحقول القابلة للتعبئة
    protected $fillable = [
        'document_number',
        'batch_number',
        'warehouse_id',
        'product_id',
        'supplier_id',
        'quantity',
        'samples_quantity',
        'arrival_time',
        'departure_time',
        'notes',
        'supplier_receipt', // ✅ صورة إذن المورد
        'user_id',
        'production_date', // ✅ تاريخ الإنتاج
    ];

    // ✅ تحويل أنواع البيانات
    protected $casts = [
        'arrival_time' => 'datetime',
        'departure_time' => 'datetime',
        'quantity' => 'integer',
        'samples_quantity' => 'integer',
        'production_date' => 'date', // ✅ تحويل تاريخ الإنتاج إلى نوع التاريخ
    ];

    // ✅ العلاقات

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

    // ✅ دوال مساعدة

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

    // ✅ أكسسور للحصول على رابط صورة إذن المورد
    public function getSupplierReceiptUrlAttribute()
    {
        if ($this->supplier_receipt) {
            return asset('storage/' . $this->supplier_receipt);
        }
        return null;
    }

    // ✅ أكسسور للحصول على اسم الملف
    public function getSupplierReceiptNameAttribute()
    {
        if ($this->supplier_receipt) {
            return basename($this->supplier_receipt);
        }
        return null;
    }

    // ✅ دالة للتحقق من وجود صورة
    public function hasSupplierReceipt()
    {
        return !is_null($this->supplier_receipt);
    }

    // ✅ دالة لحذف الصورة
    public function deleteSupplierReceipt()
    {
        if ($this->supplier_receipt && Storage::disk('public')->exists($this->supplier_receipt)) {
            Storage::disk('public')->delete($this->supplier_receipt);
        }
        $this->update(['supplier_receipt' => null]);
    }

    // ✅ Scope للبحث عن الشحنات التي تحتوي على صور
    public function scopeWithReceipt($query)
    {
        return $query->whereNotNull('supplier_receipt');
    }

    // ✅ Scope للبحث عن الشحنات بدون صور
    public function scopeWithoutReceipt($query)
    {
        return $query->whereNull('supplier_receipt');
    }
}