<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Warehouse extends Model
{
  use SoftDeletes; // أضف هذا السطر
    
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
    
    protected $casts = [
        'status' => 'boolean',
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

    // Scope لجلب المخازن الرئيسية فقط
    public function scopeMain($query)
    {
        return $query->where('type', 'main');
    }
    
    // Scope لجلب المخازن النشطة فقط
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
    
    // Scope لجلب المخازن الفرعية فقط
    public function scopeSub($query)
    {
        return $query->where('type', 'sub');
    }
    
    /**
     * التحقق من إمكانية حذف المخزن
     * ملاحظة: تم تعليق التحقق من المنتجات والحركات مؤقتاً لحين إنشاء الجداول
     */

    
    /**
     * الحصول على رسالة منع الحذف المناسبة
     */


    public function products()
{
    return $this->hasMany(Product::class);
}

/**
 * التحقق من إمكانية حذف المخزن
 */
public function canBeDeleted()
{
    // منع الحذف إذا توجد مخازن فرعية
    if ($this->subWarehouses()->count() > 0) {
        return false;
    }
    
    // ✅ فعل فحص المنتجات
    if ($this->products()->count() > 0) {
        return false;
    }
    
    // TODO: أضف هذه الشروط لاحقاً عند إنشاء جدول stock_movements
    // if ($this->movements()->count() > 0) {
    //     return false;
    // }
    
    return true;
}

/**
 * الحصول على رسالة منع الحذف المناسبة
 */
public function getDeleteRestrictionMessage()
{
    if ($this->subWarehouses()->count() > 0) {
        $count = $this->subWarehouses()->count();
        return "لا يمكن حذف هذا المخزن لأنه يحتوي على {$count} مخازن فرعية";
    }
    
    // ✅ أضف رسالة المنتجات
    if ($this->products()->count() > 0) {
        $count = $this->products()->count();
        return "لا يمكن حذف هذا المخزن لأنه مرتبط بـ {$count} منتجات";
    }
    
    return 'لا يمكن حذف هذا المخزن لوجود قيود عليه';
}
}