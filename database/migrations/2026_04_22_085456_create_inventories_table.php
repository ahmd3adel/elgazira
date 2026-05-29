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
Schema::create('inventories', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_id')->constrained()->onDelete('cascade');
    $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
    
    $table->unsignedInteger('quantity'); // ✅ عدد صحيح
    
    $table->timestamp('last_movement_at')->nullable();
    
    $table->unique(['product_id', 'warehouse_id']); 
    $table->timestamps();
});


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
