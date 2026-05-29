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
Schema::create('receiving_orders', function (Blueprint $table) {
    $table->id();
    $table->string('document_number', 50)->unique();
    $table->string('batch_number')->nullable();
    $table->foreignId('warehouse_id')->constrained();
    $table->foreignId('product_id')->constrained();
    $table->foreignId('supplier_id')->constrained(); // ✅ ربط بالموردين
    $table->unsignedInteger('quantity')->default(0); // ✅ عدد صحيح (600)
    $table->unsignedInteger('samples_quantity')->default(0); // ✅ عدد صحيح (0)
    
    // ✅ وقت الإضافة هو وقت تسجيل الشحنة (نفس created_at)
    $table->dateTime('arrival_time')->nullable(); // هنضبطه في الكود
    $table->dateTime('departure_time')->nullable(); // هنضبطه في الكود
                                                                                                      
    $table->text('notes')->nullable();
    $table->foreignId('user_id')->constrained();
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receiving_orders');
    }
};
