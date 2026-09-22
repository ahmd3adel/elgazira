<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'quantity',
        'price',
        'expiry_date',
        'companion_product_id',
        'status',
    ];

    // العلاقات
    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class);
    }

    public function companion()
    {
        return $this->belongsTo(Product::class, 'companion_product_id');
    }

    public function baseProduct()
    {
        return $this->hasOne(Product::class, 'companion_product_id');
    }

    // Accessor لتنسيق السعر
    public function getFormattedPriceAttribute()
    {
        return number_format($this->price, 2) . ' جنيه';
    }

    // Accessor لحالة الصلاحية
    public function getExpiryStatusAttribute()
    {
        if (!$this->expiry_date) {
            return 'غير محدد';
        }

        $now = now();
        $expiry = \Carbon\Carbon::parse($this->expiry_date);
        $daysLeft = $now->diffInDays($expiry, false);

        if ($daysLeft < 0) {
            return 'منتهي الصلاحية';
        } elseif ($daysLeft <= 30) {
            return 'ينتهي خلال ' . $daysLeft . ' يوم';
        } else {
            return 'صالحة';
        }
    }
}