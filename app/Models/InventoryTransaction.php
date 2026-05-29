<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryTransaction extends Model
{
    use HasFactory;
protected $fillable = [
    'receiving_order_id', 'product_id', 'warehouse_id', 
    'type', 'quantity', 'quantity_before', 
    'quantity_after', 'reference_number', 'notes', 'user_id'
];
    // الحركة الواحدة تتبع تقرير استلام واحد
public function receivingOrder() {
    return $this->belongsTo(ReceivingOrder::class);
}

public function product() {
    return $this->belongsTo(Product::class);
}
}
