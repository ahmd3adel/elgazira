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
Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->string('name'); // مثلاً: بسكويت سادة مدعم
    $table->string('sku')->unique();
    
    // الوحدات
    $table->string('purchase_unit')->default('كرتونة');
    $table->string('issue_unit')->default('وجبة');
    $table->integer('conversion_factor'); // الكرتونة فيها كام وجبة؟
    $table->integer('expiry_duration')->default('6');
    // migration
$table->unsignedBigInteger('companion_product_id')->nullable();
$table->foreign('companion_product_id')->references('id')->on('products');
$table->boolean('is_base')->default(false); // هل هو الصنف الأساسي (سادة 40)
    // المخزون (يفضل دائماً التخزين بأصغر وحدة وهي الوجبة)
    $table->integer('total_quantity_pax')->default(0); // إجمالي الكمية بالوجبات
    
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
