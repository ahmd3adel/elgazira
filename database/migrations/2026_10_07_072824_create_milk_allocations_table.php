<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('milk_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')
                  ->constrained('departments')
                  ->cascadeOnDelete();

            $table->date('allocation_date');

            // المدخل: كراتين السادة
            $table->integer('biscuit_cartons');

            // المحسوب تلقائياً (نخزنه للـ performance والتقارير)
            $table->integer('biscuit_packs');      // = biscuit_cartons × 108
            $table->integer('milk_cans');          // = biscuit_packs (1:1)
            $table->integer('milk_cartons');       // = milk_cans / 27
            $table->integer('total_meals');        // = biscuit_packs

            $table->text('notes')->nullable();
            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();

            $table->index(['department_id', 'allocation_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('milk_allocations');
    }
};