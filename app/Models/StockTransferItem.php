<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTransferItem extends Model
{
    use HasFactory;

    /**
     * الحقول القابلة للتعبئة (Mass Assignment)
     */
    protected $fillable = [
        'stock_transfer_id',
        'product_id',
        'quantity'
    ];

    /**
     * علاقة التفاصيل بأمر التحويل الرئيسي
     * كل سجل هنا ينتمي لأمر تحويل واحد
     */
    public function transfer()
    {
        return $this->belongsTo(StockTransfer::class, 'stock_transfer_id');
    }

    /**
     * علاقة الصنف بالمنتج
     * لجلب اسم المنتج أو بياناته في جدول التحويلات
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}