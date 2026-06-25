<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Device extends Model
{
    use HasFactory;

    protected $table = 'devices';

    protected $fillable = [
        'department_id',
        'line_number',
        'pos_username',
        'pos_password',
        'serial_number',
        'technical_status',
        'status',
        'notes',
        'specifications',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // العلاقات
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // الأكسسورات
    public function getTechnicalStatusBadgeAttribute(): string
    {
        $badges = [
            'working' => '<span class="badge-technical badge-working">🟢 يعمل بكفاءة</span>',
            'maintenance' => '<span class="badge-technical badge-maintenance">🟡 تحت الصيانة</span>',
            'broken' => '<span class="badge-technical badge-broken">🔴 عاطل</span>',
            'retired' => '<span class="badge-technical badge-retired">⚪ مستبعد</span>'
        ];

        return $badges[$this->technical_status] ?? '<span class="badge badge-secondary">غير محدد</span>';
    }

    public function getStatusBadgeAttribute(): string
    {
        if ($this->status === 'active') {
            return '<span class="badge-active badge-technical">✅ نشط</span>';
        }
        return '<span class="badge-inactive badge-technical">❌ غير نشط</span>';
    }
    
    // إضافة سكوبات مفيدة للبحث
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
    
    public function scopeWorking($query)
    {
        return $query->where('technical_status', 'working');
    }
    
    public function scopeUnderMaintenance($query)
    {
        return $query->where('technical_status', 'maintenance');
    }
    
    public function scopeByDepartment($query, $departmentId)
    {
        if ($departmentId) {
            return $query->where('department_id', $departmentId);
        }
        return $query;
    }
}