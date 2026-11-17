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
    Schema::create('distribution_order_details', function (Blueprint $table) {
        $table->id();
        
        // الربط مع رأس أمر الصرف
        $table->foreignId('distribution_order_id')
              ->constrained('distribution_orders')
              ->onDelete('cascade');
        
        // الربط مع المنتج
        $table->foreignId('product_id')->constrained('products');
        
        // الكمية المنصرفة من هذا الصنف
        $table->integer('quantity');
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distribution_order_details');
    }
};
