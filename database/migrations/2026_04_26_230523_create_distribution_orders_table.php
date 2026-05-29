<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::create('distribution_orders', function (Blueprint $table) {
        $table->id();
        
        // رقم أمر الصرف (رقم آلي أو يدوي)
        $table->string('receite_number')->nullable();
        
        // الجهة المستفيدة (مدرسة)
        $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
        
        // تاريخ أمر الصرف
        $table->date('receite_date');
        
       
        
        // الموافقات
        $table->foreignId('created_by')->constrained('users');
        
        // ملاحظات
        $table->text('notes')->nullable();
        
        // المسؤول عن التوصيل
        $table->string('delivery_agent')->nullable();
        
        $table->timestamps();
        
        // إضافة indexes للبحث السريع
        $table->index('receite_number');
        $table->index('receite_date');
        $table->index('school_id');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distribution_orders');
    }
};