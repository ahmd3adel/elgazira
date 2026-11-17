<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // اسم المنتج
            $table->string('sku')->unique(); // كود أو SKU المنتج
            $table->text('description')->nullable(); // الوصف
            
            // ربط المنتج بالمخزن
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->onDelete('set null');
            
            // الوحدات والكميات
            $table->string('purchase_unit')->default('كرتونة');
            $table->string('issue_unit')->default('وجبة');
            $table->integer('conversion_factor')->default(1); // الكرتونة فيها كام وجبة؟
            $table->integer('quantity')->default(0); // الكمية المبدئية
            $table->integer('total_quantity_pax')->default(0); // إجمالي الكمية بالوجبات
            
            // السعر والصلاحية والحالة
            $table->decimal('price', 10, 2)->default(0.00); // السعر
            $table->integer('expiry_duration')->default(6); // مدة الصلاحية بالأشهر
            $table->date('expiry_date')->nullable(); // تاريخ الصلاحية الفعلي إن وجد
            
            // الأصناف المرتبطة
            $table->unsignedBigInteger('companion_product_id')->nullable();
            $table->foreign('companion_product_id')->references('id')->on('products')->onDelete('set null');
            $table->boolean('is_base')->default(false); // هل هو الصنف الأساسي
            
            $table->boolean('status')->default(true); // نشط / غير نشط
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};