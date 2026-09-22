<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    // الحقول المسموح بتعبئتها عند الإضافة أو التعديل
    protected $fillable = [
        'name',
        'phone',
        'job_title',
        'warehouse_id',
        'is_active',
        'notes',
        'health_certificate_image',
        'health_certificate_issued_at',
        'health_certificate_expires_at',
    ];

    // تحويل أنواع البيانات تلقائياً
    protected $casts = [
        'is_active' => 'boolean',
        'health_certificate_issued_at' => 'date',
        'health_certificate_expires_at' => 'date',
    ];

    /**
     * العلاقة: العامل يتبع مخزن معين
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}