<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('receiving_orders', function (Blueprint $table) {
            // ✅ الباركود التالف (عدد صحيح، افتراضي 0)
            $table->unsignedInteger('damaged_barcode')->default(0)->after('samples_quantity');
        });
    }

    public function down(): void
    {
        Schema::table('receiving_orders', function (Blueprint $table) {
            $table->dropColumn('damaged_barcode');
        });
    }
};