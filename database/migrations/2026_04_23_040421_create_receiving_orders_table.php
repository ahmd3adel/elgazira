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
            
            // ✅ تاريخ الإنتاج (جديد)
            $table->date('production_date')->nullable(); // تاريخ إنتاج المنتج
            
            // ✅ صورة إذن المورد
            $table->string('supplier_receipt')->nullable(); // مسار الصورة أو PDF
            
            // ✅ ملاحظات
            $table->text('notes')->nullable();
            
            // ✅ المستخدم الذي أضاف الشحنة
            $table->foreignId('user_id')->constrained();
            
            // ✅ وقت الإنشاء والتحديث
            $table->timestamps();
            
            // ✅ إضافة فهارس (Indexes) لتسريع البحث
            $table->index('document_number');
            $table->index('supplier_id');
            $table->index('warehouse_id');
            $table->index('product_id');
            $table->index('production_date'); // ✅ فهرس لتاريخ الإنتاج
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