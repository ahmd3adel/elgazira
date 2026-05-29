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
    Schema::create('department_allocations', function (Blueprint $table) {
        $table->id();
        
        // التاريخ هو العنصر الأساسي في التوزيع اليومي
        $table->date('receite_date'); 
        
        // ربط مع الإدارة (من هنا بنعرف هي تعليم ولا أزهر تلقائياً)
        $table->foreignId('department_id')->constrained('departments')->onDelete('cascade');
        
        // المستخدم اللي سجل العملية (للمراقبة)
        $table->foreignId('created_by')->constrained('users')->default(1);
                    $table->integer('total_meals')->default(0);

        $table->text('notes')->nullable();
        $table->timestamps();

        // Indexes لتحسين سرعة التقارير اليومية وللبحث بالإدارة
        $table->index('receite_date');
        $table->index('department_id');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('department_allocations');
    }
};
