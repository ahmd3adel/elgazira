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
Schema::create('inventory_transactions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_id')->constrained()->cascadeOnDelete();
    $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
    
    // ربط الاختيارات بالجهات المستفيدة حسب نوع الحركة
    $table->foreignId('school_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
    
    $table->enum('type', ['in', 'out', 'transfer_in', 'transfer_out']);
    $table->string('reference_number', 100)->nullable(); // رقم إذن الصرف أو الإضافة
    $table->integer('quantity'); // الكمية المتحركة
    $table->integer('balance_after')->nullable(); // الرصيد بعد الحركة (اختياري للتدقيق)
    
    $table->text('notes')->nullable();
    $table->foreignId('user_id')->constrained(); // المستخدم المسؤول عن الحركة
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};
