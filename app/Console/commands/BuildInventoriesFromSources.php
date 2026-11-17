<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BuildInventoriesFromSources extends Command
{
    protected $signature = 'inventories:build-from-sources
                            {--dry-run : عرض النتائج بدون حفظ}
                            {--fix-negative : تصفير الأرقام السالبة (مع تسجيل)}';
    protected $description = 'بناء inventories من الشحنات والتحويلات';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $fixNegative = $this->option('fix-negative');

        $this->info('🔄 بناء الأرصدة من المصادر الأصلية');
        if ($dryRun) $this->warn('⚠️ وضع الاختبار — لن يتم الحفظ');
        $this->newLine();

        // ==========================================
        // 1. حساب الرصيد لكل (منتج × مخزن)
        // ==========================================
        $balances = [];

        // 1.1 الشحنات
        foreach (DB::table('receiving_orders')
            ->select('product_id', 'warehouse_id', DB::raw('SUM(quantity) as total'))
            ->groupBy('product_id', 'warehouse_id')
            ->get() as $r) {
            $key = "{$r->product_id}_{$r->warehouse_id}";
            $balances[$key] = ($balances[$key] ?? 0) + (int) $r->total;
        }

        // 1.2 التحويلات الخارجة
        foreach (DB::table('stock_transfer_items as sti')
            ->join('stock_transfers as st', 'sti.stock_transfer_id', '=', 'st.id')
            ->select('sti.product_id', 'st.from_warehouse_id as warehouse_id', DB::raw('SUM(sti.quantity) as total'))
            ->groupBy('sti.product_id', 'st.from_warehouse_id')
            ->get() as $t) {
            $key = "{$t->product_id}_{$t->warehouse_id}";
            $balances[$key] = ($balances[$key] ?? 0) - (int) $t->total;
        }

        // 1.3 التحويلات الداخلة
        foreach (DB::table('stock_transfer_items as sti')
            ->join('stock_transfers as st', 'sti.stock_transfer_id', '=', 'st.id')
            ->select('sti.product_id', 'st.to_warehouse_id as warehouse_id', DB::raw('SUM(sti.quantity) as total'))
            ->groupBy('sti.product_id', 'st.to_warehouse_id')
            ->get() as $t) {
            $key = "{$t->product_id}_{$t->warehouse_id}";
            $balances[$key] = ($balances[$key] ?? 0) + (int) $t->total;
        }

        // 1.4 العينات (من الشحنات)
        $samples = [];
        foreach (DB::table('receiving_orders')
            ->where('samples_quantity', '>', 0)
            ->select('product_id', 'warehouse_id', DB::raw('SUM(samples_quantity) as total'))
            ->groupBy('product_id', 'warehouse_id')
            ->get() as $s) {
            $key = "{$s->product_id}_{$s->warehouse_id}";
            $samples[$key] = (int) $s->total;
        }

        // ==========================================
        // 2. عرض النتائج
        // ==========================================
        $this->info('=== الأرصدة المحسوبة ===');

        $rows = [];
        $negative = [];
        $toSave = [];

        foreach ($balances as $key => $qty) {
            [$pid, $wid] = explode('_', $key);
            $rawQty = $qty;
            $finalQty = max(0, $qty);
            $smpQty = $samples[$key] ?? 0;

            $rows[] = [
                $pid,
                $wid,
                $rawQty,
                $finalQty,
                $smpQty,
                $rawQty < 0 ? '⚠️ سالب' : '',
            ];

            if ($rawQty < 0) {
                $negative[] = ['product' => $pid, 'warehouse' => $wid, 'qty' => $rawQty];
            }

            if ($finalQty > 0 || $smpQty > 0) {
                $toSave[] = [
                    'product_id' => $pid,
                    'warehouse_id' => $wid,
                    'quantity' => $finalQty,
                    'samples_quantity' => $smpQty,
                ];
            }
        }

        $this->table(
            ['منتج', 'مخزن', 'الرصيد الخام', 'الرصيد النهائي', 'عينات', 'ملاحظة'],
            $rows
        );

        // ==========================================
        // 3. تقرير الأرقام السالبة
        // ==========================================
        if (!empty($negative)) {
            $this->newLine();
            $this->error('⚠️  ' . count($negative) . ' سجل برصيد سالب (مشكلة بيانات):');
            foreach ($negative as $n) {
                $this->line("  منتج #{$n['product']} مخزن #{$n['warehouse']}: {$n['qty']}");
            }
            $this->newLine();
            $this->warn('  الأرقام السالبة تعني: تحويلات خارجة أكثر من الشحنات الواردة.');
            $this->warn('  الأسباب المحتملة: شحنة محذوفة، تحويل بكمية أكبر من المتاح، أو حركة مكررة.');
        }

        // ==========================================
        // 4. الحفظ
        // ==========================================
        if ($dryRun) {
            $this->newLine();
// السطر 131
$this->warn("🔍 سيتم حفظ " . count($toSave) . " سجل (dry-run)");            return 0;
        }

        if (!$fixNegative && !empty($negative)) {
            $this->newLine();
            $this->error('❌ فيه أرقام سالبة. استخدم --fix-negative لتصفيرها، أو راجع البيانات أولاً.');
            return 1;
        }

        $this->newLine();
        if (!$this->confirm("هل تريد حفظ " . count($toSave) . " سجل في inventories؟ (سيتم مسح القديم)", true)) {
            $this->warn('تم الإلغاء');
            return 0;
        }

        DB::transaction(function () use ($toSave, $samples) {
            // امسح القديم
DB::table('inventories')->delete();
            // أدخل الجديد
            foreach ($toSave as $row) {
                DB::table('inventories')->insert([
                    'product_id' => $row['product_id'],
                    'warehouse_id' => $row['warehouse_id'],
                    'quantity' => $row['quantity'],
                    'samples_quantity' => $row['samples_quantity'],
                    'last_movement_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // حدّث العينات في السجلات اللي مش فيها quantity
            foreach ($samples as $key => $smpQty) {
                [$pid, $wid] = explode('_', $key);
                $exists = DB::table('inventories')
                    ->where('product_id', $pid)
                    ->where('warehouse_id', $wid)
                    ->exists();

                if (!$exists && $smpQty > 0) {
                    DB::table('inventories')->insert([
                        'product_id' => $pid,
                        'warehouse_id' => $wid,
                        'quantity' => 0,
                        'samples_quantity' => $smpQty,
                        'last_movement_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        });

        $this->info('✅ تم بناء inventories بنجاح (' . count($toSave) . ' سجل)');
        return 0;
    }
}