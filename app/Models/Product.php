<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
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
}
