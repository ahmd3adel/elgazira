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
    Schema::create('governorates', function (Blueprint $table) {
        $table->id();
        $table->string('name')->index(); // اسم المحافظة مع فهرسة للبحث السريع
        $table->string('code')->unique(); // كود فريد (DK, CA, AS)
        $table->string('manager_name')->nullable(); 
        $table->string('manager_phone', 20)->nullable(); // تحديد طول منطقي لرقم الهاتف
        $table->boolean('status')->default(true); 
        $table->integer('sort_order')->default(0); // للتحكم في ترتيب العرض
        $table->text('notes')->nullable(); 
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('governorates');
    }
};
