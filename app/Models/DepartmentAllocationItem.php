<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartmentAllocationItem extends Model
{
    use HasFactory;
        protected $fillable = [
        'allocation_id',
        'product_id',
        'quantity',
        'total_meals'
    ];
   public function allocation()
    {
        return $this->belongsTo(DepartmentAllocation::class, 'allocation_id');
    }
    
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
    // Accessor لعرض الوحدة
    public function getUnitAttribute(): string
    {
        return 'كرتونة';
    }
}
