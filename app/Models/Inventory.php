<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;
    // App\Models\Inventory.php
protected $fillable = ['product_id', 'warehouse_id', 'quantity', 'last_movement_at'];
public function product()
{
    return $this->belongsTo(Product::class);
}

public function warehouse()
{
    return $this->belongsTo(Warehouse::class);
}
}
