<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

    Schema::create('department_allocation_items', function (Blueprint $table) {
        $table->id();
        
        // الربط مع رأس الجدول
        $table->foreignId('allocation_id')->constrained('department_allocations')->onDelete('cascade');
        
        // الربط مع جدول الأصناف (سادة، عجوة، ويفر، إلخ)
        $table->foreignId('product_id')->constrained('products');
        
        // الكمية بالكرتونة
        $table->integer('quantity'); 
        
        // إجمالي الوجبات (حاصل ضرب الكراتين في معامل القطع للصنف)
        $table->integer('total_meals'); 
        
        $table->timestamps();
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('department_allocation_items');
    }
};
