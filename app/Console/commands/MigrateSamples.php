<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateSamples extends Command
{
    protected $signature = 'samples:migrate';
    protected $description = 'ترحيل العينات من receiving_orders إلى inventories';

    public function handle()
    {
        $this->info('قاعدة البيانات: ' . DB::connection()->getDatabaseName());
        $this->newLine();

        // 1) تحقق من العمود
        $columns = collect(DB::select('SHOW COLUMNS FROM inventories'))->pluck('Field');
        if (!$columns->contains('samples_quantity')) {
            $this->error('❌ عمود samples_quantity غير موجود في inventories!');
            $this->warn('نفّذ أولاً: ALTER TABLE inventories ADD samples_quantity INT UNSIGNED DEFAULT 0;');
            return 1;
        }
        $this->info('✅ عمود samples_quantity موجود');

        // 2) عدد الشحنات اللي فيها عينات
        $count = DB::table('receiving_orders')->where('samples_quantity', '>', 0)->count();
        $this->info("📦 عدد الشحنات فيها عينات: {$count}");

        if ($count === 0) {
            $this->error('❌ مفيش شحنات فيها عينات على القاعدة دي!');
            return 1;
        }

        // 3) اجمع العينات
        $samples = DB::table('receiving_orders')
            ->select('warehouse_id', 'product_id', DB::raw('SUM(samples_quantity) as total_samples'))
            ->where('samples_quantity', '>', 0)
            ->groupBy('warehouse_id', 'product_id')
            ->get();

        $this->info("📊 عدد المجموعات (مخزن × منتج): " . $samples->count());
        $this->newLine();

        $updated = 0;
        $inserted = 0;

        foreach ($samples as $row) {
            $line = "→ مخزن {$row->warehouse_id} / منتج {$row->product_id} / عينات {$row->total_samples}";

            $existing = DB::table('inventories')
                ->where('warehouse_id', $row->warehouse_id)
                ->where('product_id', $row->product_id)
                ->first();

            if ($existing) {
                DB::table('inventories')
                    ->where('id', $existing->id)
                    ->update([
                        'samples_quantity' => $row->total_samples,
                        'updated_at' => now(),
                    ]);
                $this->line($line . " ← تحديث (id={$existing->id})");
                $updated++;
            } else {
                DB::table('inventories')->insert([
                    'warehouse_id' => $row->warehouse_id,
                    'product_id' => $row->product_id,
                    'quantity' => 0,
                    'samples_quantity' => $row->total_samples,
                    'last_movement_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $this->line($line . " ← إدراج جديد");
                $inserted++;
            }
        }

        $this->newLine();
        $this->info("✅ تم تحديث {$updated} سجل، وإضافة {$inserted} سجل جديد");
        return 0;
    }
}