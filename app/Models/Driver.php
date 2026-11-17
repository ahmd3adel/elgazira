<?php
// app/Models/Driver.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Driver extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'drivers';

    protected $fillable = [
        'name',
        'line_number',
        'national_id',
        'health_certificate_status',
        'health_certificate_image',
        'phone',
        'training_status',
        'training_date',
        'notes',
        'status',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'training_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // العلاقات
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // سكوبات للبحث
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeTrainingCompleted($query)
    {
        return $query->where('training_status', 'completed');
    }

    public function scopeHealthValid($query)
    {
        return $query->where('health_certificate_status', 'valid');
    }

    // التوابع (Accessors & Mutators)
    public function getHealthCertificateStatusBadgeAttribute()
    {
        $badges = [
            'pending' => '<span class="badge badge-warning">قيد الانتظار</span>',
            'valid' => '<span class="badge badge-success">سارية</span>',
            'expired' => '<span class="badge badge-danger">منتهية</span>',
            'not_required' => '<span class="badge badge-secondary">غير مطلوبة</span>',
        ];
        return $badges[$this->health_certificate_status] ?? '<span class="badge badge-secondary">غير محدد</span>';
    }

    public function getTrainingStatusBadgeAttribute()
    {
        $badges = [
            'pending' => '<span class="badge badge-warning">قيد الانتظار</span>',
            'completed' => '<span class="badge badge-success">مكتمل</span>',
            'failed' => '<span class="badge badge-danger">راسب</span>',
            'not_scheduled' => '<span class="badge badge-secondary">غير مجدول</span>',
        ];
        return $badges[$this->training_status] ?? '<span class="badge badge-secondary">غير محدد</span>';
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'active' => '<span class="badge badge-success">نشط</span>',
            'inactive' => '<span class="badge badge-danger">غير نشط</span>',
        ];
        return $badges[$this->status] ?? '<span class="badge badge-secondary">غير محدد</span>';
    }

    public function getHealthCertificateImageUrlAttribute()
    {
        if ($this->health_certificate_image) {
            return asset('storage/' . $this->health_certificate_image);
        }
        return null;
    }
}