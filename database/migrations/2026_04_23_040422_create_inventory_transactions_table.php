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
Schema::create('inventory_transactions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_id')->constrained();
    $table->foreignId('warehouse_id')->constrained();
    $table->enum('type', ['in', 'out', 'transfer_in', 'transfer_out']);
    $table->string('reference_number', 100)->nullable();
    $table->integer('quantity'); // الكمية المتحركة
    $table->text('notes')->nullable();
    $table->foreignId('user_id')->constrained()->default(1);
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};
