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
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // كود فريد للإدارة
            $table->string('name'); // اسم الإدارة
            
            // الربط الجغرافي
            $table->foreignId('governorate_id')->constrained('governorates')->onDelete('cascade');
            $table->enum('entity_type', ['education', 'azhar'])->default('education');
            // الربط المخزني
            // التوريد: المخزن الذي يغذي الإدارة
            $table->foreignId('main_warehouse_id')->constrained('warehouses')->onDelete('restrict');
            // العهدة: مخزن السحب اليومي الخاص بالإدارة
            $table->foreignId('operation_warehouse_id')->constrained('warehouses')->onDelete('restrict');

            // بيانات إضافية
            $table->string('manager_name')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('status')->default(true); // نشط أو معطل
            
            $table->timestamps();
            $table->softDeletes(); // للحذف الناعم
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
