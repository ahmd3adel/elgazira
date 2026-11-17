<?php
// database/migrations/xxxx_xx_xx_xxxxxx_add_samples_tracking_columns.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. أعمدة samples
        Schema::table('samples', function (Blueprint $table) {
            $table->unsignedInteger('received_quantity')->default(0)->after('quantity');
            $table->unsignedInteger('consumed_quantity')->default(0)->after('received_quantity');
        });

        // ترحيل البيانات: received_quantity = quantity للصفوف الموجودة
        DB::table('samples')->update([
            'received_quantity' => DB::raw('quantity'),
        ]);

        // 2. عمود samples_deducted في stock_transfer_items
        Schema::table('stock_transfer_items', function (Blueprint $table) {
            $table->unsignedInteger('samples_deducted')->default(0)->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('samples', function (Blueprint $table) {
            $table->dropColumn(['received_quantity', 'consumed_quantity']);
        });

        Schema::table('stock_transfer_items', function (Blueprint $table) {
            $table->dropColumn('samples_deducted');
        });
    }
};