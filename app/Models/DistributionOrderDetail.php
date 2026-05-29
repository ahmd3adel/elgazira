<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DistributionOrderDetail extends Model
{
    protected $fillable = [
        'distribution_order_id',
        'product_id',
        'quantity'
    ];
    
    public function order()
    {
        return $this->belongsTo(DistributionOrder::class);
    }

    // // العلاقة مع رأس الطلب
    // public function header()
    // {
    //     return $this->belongsTo(DistributionOrder::class, 'distribution_order_id');
    // }

    // العلاقة مع المنتج لمعرفة اسم الصنف المنصرف
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}