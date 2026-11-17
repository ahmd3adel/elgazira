<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateSamplesToTable extends Command
{
    protected $signature = 'samples:migrate-to-table
                            {--force : حذف العينات المرحّلة سابقاً وإعادة الترحيل}';

    protected $description = 'ترحيل العينات من receiving_orders إلى جدول samples';

    public function handle()
    {
        $this->info('قاعدة البيانات: ' . DB::connection()->getDatabaseName());
        $this->newLine();

        // 1) تحقق من وجود الجدول
        if (!DB::getSchemaBuilder()->hasTable('samples')) {
            $this->error('❌ جدول samples غير موجود!');
            $this->warn('نفّذ أولاً: php artisan migrate');
            return 1;
        }
        $this->info('✅ جدول samples موجود');

        // 2) تحقق من الأعمدة المطلوبة
        $requiredColumns = ['receiving_order_id', 'warehouse_id', 'product_id', 'quantity', 'status'];
        $columns = collect(DB::select('SHOW COLUMNS FROM samples'))->pluck('Field');
        $missing = collect($requiredColumns)->diff($columns);

        if ($missing->isNotEmpty()) {
            $this->error('❌ أعمدة ناقصة في جدول samples: ' . $missing->implode(', '));
            return 1;
        }
        $this->info('✅ كل الأعمدة المطلوبة موجودة');

        // 3) عدد الشحنات اللي فيها عينات
        $ordersCount = DB::table('receiving_orders')->where('samples_quantity', '>', 0)->count();
        $this->info("📦 عدد الشحنات فيها عينات: {$ordersCount}");

        if ($ordersCount === 0) {
            $this->error('❌ مفيش شحنات فيها عينات على القاعدة دي!');
            return 1;
        }

        // 4) لو --force، احذف العينات المرحّلة سابقاً (اللي department_id = null)
        if ($this->option('force')) {
            $deleted = DB::table('samples')->whereNull('department_id')->delete();
            $this->warn("🗑️  تم حذف {$deleted} عينة مرحّلة سابقاً (force)");
        }

        // 5) تحقق من التكرار (لو مش force)
        if (!$this->option('force')) {
            $alreadyMigrated = DB::table('samples')
                ->whereNotNull('receiving_order_id')
                ->count();

            if ($alreadyMigrated > 0) {
                $this->error("❌ يوجد {$alreadyMigrated} عينة في الجدول بالفعل.");
                $this->warn('لو عايز تعيد الترحيل، استخدم: php artisan samples:migrate-to-table --force');
                return 1;
            }
        }

        // 6) نفّذ الترحيل
        $this->newLine();
        $this->info('⏳ بدء الترحيل...');

        try {
            $migrated = DB::transaction(function () {
                $orders = DB::table('receiving_orders')
                    ->where('samples_quantity', '>', 0)
                    ->orderBy('id')
                    ->get();

                $count = 0;

                foreach ($orders as $order) {
                    DB::table('samples')->insert([
                        'receiving_order_id' => $order->id,
                        'warehouse_id'       => $order->warehouse_id,
                        'department_id'      => null,
                        'product_id'         => $order->product_id,
                        'quantity'           => $order->samples_quantity,
                        'status'             => 'pending',
                        'user_id'            => $order->user_id,
                        'created_at'         => $order->created_at,
                        'updated_at'         => now(),
                    ]);

                    $count++;

                    $this->line("  → شحنة #{$order->id} ({$order->document_number}) | مخزن {$order->warehouse_id} | منتج {$order->product_id} | عينات {$order->samples_quantity}");
                }

                return $count;
            });

            $this->newLine();
            $this->info("✅ تم ترحيل {$migrated} عينة بنجاح");

            // 7) إحصائيات نهائية
            $totalQty = DB::table('samples')->sum('quantity');
            $this->info("📊 إجمالي العينات في الجدول: {$totalQty}");

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ فشل الترحيل: ' . $e->getMessage());
            return 1;
        }
    }
}