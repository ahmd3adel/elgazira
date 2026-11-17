<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('samples', function (Blueprint $table) {
            $table->id();

            // الربط بالشحنة
            $table->foreignId('receiving_order_id')
                ->constrained('receiving_orders')
                ->cascadeOnDelete();

            // المخزن (اللي الشحنة دخلت عليه)
            $table->foreignId('warehouse_id')
                ->constrained('warehouses')
                ->cascadeOnDelete();

            // الإدارة (لو عايزها منفصلة عن المخزن)
            // لو "الإدارة" = المخزن، ممكن تستغنى عن ده
            $table->foreignId('department_id')
                ->nullable()
                ->constrained('departments')
                ->nullOnDelete();

            // الصنف
            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            // عدد العينات
            $table->unsignedInteger('quantity');

            // حالة العينة
            $table->enum('status', ['pending', 'tested', 'approved', 'rejected', 'destroyed'])
                ->default('pending');

            // ملاحظات
            $table->text('notes')->nullable();

            // من سجّلها
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // تواريخ الفحص
            $table->timestamp('tested_at')->nullable();
            $table->timestamp('destroyed_at')->nullable();

            $table->timestamps();

            $table->index(['warehouse_id', 'product_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('samples');
    }
};