<?php
// database/migrations/2026_05_31_000001_create_drivers_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->comment('الاسم الكامل');
            $table->string('line_number', 50)->nullable()->comment('رقم الخط');
            $table->string('national_id', 20)->unique()->comment('رقم البطاقة الشخصية');
            $table->enum('health_certificate_status', ['pending', 'valid', 'expired', 'not_required'])->default('pending')->comment('موقف الشهادة الصحية');
            $table->string('health_certificate_image', 255)->nullable()->comment('صورة الشهادة الصحية');
            $table->string('phone', 20)->nullable()->comment('رقم الموبايل');
            $table->enum('training_status', ['pending', 'completed', 'failed', 'not_scheduled'])->default('pending')->comment('حالة التدريب');
            $table->date('training_date')->nullable()->comment('تاريخ التدريب');
            $table->text('notes')->nullable()->comment('ملاحظات إضافية');
            $table->enum('status', ['active', 'inactive'])->default('active')->comment('الحالة العامة');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            
            // إندكسات للبحث السريع
            $table->index('name');
            $table->index('national_id');
            $table->index('phone');
            $table->index('line_number');
            $table->index('training_status');
            $table->index('health_certificate_status');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('drivers');
    }
};