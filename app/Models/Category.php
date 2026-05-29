<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_ar', 'name_en', 'slug_ar', 'slug_en', 'icon', 'icon_color',
        'parent_id', 'is_active', 'order', 'description'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer'
    ];

    /**
     * Get the parent category
     */
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Get the sub-categories
     */
    public function subCategories(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * Get the name based on locale
     */
    public function getNameAttribute()
    {
        $locale = app()->getLocale();
        return $locale == 'ar' ? ($this->name_ar ?? $this->name_en) : ($this->name_en ?? $this->name_ar);
    }

    // في app/Models/Category.php

// نطاق لجلب الفئات الرئيسية فقط
public function scopeMainCategories($query)
{
    return $query->whereNull('parent_id')->orWhere('parent_id', 0);
}

// دالة للتحقق من أن الاسم عربي
public function isArabicName()
{
    return preg_match('/[\x{0600}-\x{06FF}]/u', $this->name_ar);
}
}