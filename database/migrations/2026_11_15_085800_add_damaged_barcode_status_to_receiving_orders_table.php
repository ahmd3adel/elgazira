<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('receiving_orders', function (Blueprint $table) {
            // ✅ حالة تسليم الباركود التالف
            $table->enum('damaged_barcode_status', ['pending', 'delivered', 'replaced'])
                  ->default('pending')
                  ->after('damaged_barcode');

            // ✅ تاريخ تسليم/استبدال الباركود التالف
            $table->dateTime('damaged_barcode_delivered_at')
                  ->nullable()
                  ->after('damaged_barcode_status');

            // ✅ ملاحظات خاصة بالباركود التالف
            $table->text('damaged_barcode_notes')
                  ->nullable()
                  ->after('damaged_barcode_delivered_at');

            $table->index('damaged_barcode_status');
        });
    }

    public function down(): void
    {
        Schema::table('receiving_orders', function (Blueprint $table) {
            $table->dropIndex(['damaged_barcode_status']);
            $table->dropColumn([
                'damaged_barcode_status',
                'damaged_barcode_delivered_at',
                'damaged_barcode_notes',
            ]);
        });
    }
};