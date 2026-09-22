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
    Schema::create('employees', function (Blueprint $table) {
        $table->id();
        $table->string('name'); // اسم العامل
        $table->string('phone', 20)->nullable(); // رقم الهاتف
        $table->string('job_title')->nullable(); // المسمى الوظيفي
        
        // ربط العامل بمخزن معين (علاقة اختيارية)
        $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->onDelete('set null');
        
        // الحقول الجديدة الخاصة بالشهادة الصحية
        $table->string('health_certificate_image')->nullable(); // مسار صورة الشهادة
        $table->date('health_certificate_issued_at')->nullable(); // تاريخ إصدار الشهادة
        $table->date('health_certificate_expires_at')->nullable(); // تاريخ انتهاء الشهادة

        $table->boolean('is_active')->default(true); // حالة العامل (نشط/غير نشط)
        $table->text('notes')->nullable(); // ملاحظات
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
