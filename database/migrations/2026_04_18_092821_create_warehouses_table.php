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
    $table->unsignedBigInteger('code')->unique()->nullable(); 
    
    $table->foreignId('governorate_id')
          ->nullable()
          ->constrained('governorates')
          ->onDelete('cascade');
    
    $table->enum('type', ['main', 'sub', 'dispatch_point']);
    
    // الربط الهرمي
    $table->unsignedBigInteger('parent_id')->nullable();
    $table->foreign('parent_id')->references('id')->on('warehouses')->onDelete('set null');
    
    $table->string('manager_name')->nullable();
    $table->string('manager_phone')->nullable();
    $table->text('address')->nullable();
    $table->boolean('status')->default(true); 
    
    $table->timestamps();
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
