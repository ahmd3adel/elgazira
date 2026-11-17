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
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique()->nullable();
            
            $table->foreignId('governorate_id')
                  ->nullable()
                  ->constrained('governorates')
                  ->onDelete('restrict');
            
            $table->enum('type', ['main', 'sub', 'dispatch_point'])->default('main');
            
            // الربط الهرمي
            $table->unsignedBigInteger('parent_id')->nullable();
           $table->foreign('parent_id')
      ->references('id')
      ->on('warehouses')
      ->onDelete('restrict'); // ← بدل set null
            
            $table->string('manager_name')->nullable();
            $table->string('manager_phone')->nullable();
            $table->text('address')->nullable();
            $table->boolean('status')->default(true);
            
            $table->timestamps();
            $table->softDeletes(); // Soft Deletes موجود هنا مباشرة
            
            // إضافة فهارس
            $table->index(['type', 'status']);
            $table->index('parent_id');
            $table->index('governorate_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};