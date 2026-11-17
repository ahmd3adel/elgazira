<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DiagnoseInventory extends Command
{
    protected $signature = 'inventories:diagnose {--product= : ID منتج محدد}';
    protected $description = 'تشخيص مصادر الأرصدة وكشف التكرار';

    public function handle()
    {
        $productId = $this->option('product');
        $this->info('🔍 تشخيص مصادر الأرصدة');
        $this->newLine();
        // ==========================================
        // 1. فحص inventory_transactions
        // ==========================================
        $this->info('=== 1. فحص inventory_transactions ===');

        $query = DB::table('inventory_transactions');
        if ($productId) $query->where('product_id', $productId);

        $total = (clone $query)->count();
        $this->line("  إجمالي الحركات: {$total}");

        // قيم type
        $types = (clone $query)->distinct()->pluck('type');
        $this->line("  أنواع الحركات: " . $types->implode(', '));

        // حركات مكررة (نفس product + warehouse + type + reference_number)
        $duplicates = (clone $query)
            ->select('product_id', 'warehouse_id', 'type', 'reference_number', DB::raw('COUNT(*) as cnt'))
            ->whereNotNull('reference_number')
            ->groupBy('product_id', 'warehouse_id', 'type', 'reference_number')
            ->having('cnt', '>', 1)
            ->get();

        if ($duplicates->isEmpty()) {
            $this->info("  ✅ لا توجد حركات مكررة");
        } else {
            $this->warn("  ⚠️  {$duplicates->count()} مجموعة مكررة!");
            $this->newLine();
            $this->line("  أول 10 مجموعات:");
            foreach ($duplicates->take(10) as $d) {
                $this->line("    منتج #{$d->product_id} | مخزن #{$d->warehouse_id} | {$d->type} | ref={$d->reference_number} | عدد: {$d->cnt}");
            }
        }
        $this->newLine();

        // ==========================================
        // 2. حساب من inventory_transactions
        // ==========================================
        $this->info('=== 2. الحساب من inventory_transactions ===');

        $calcQuery = DB::table('inventory_transactions')
            ->select('product_id', 'warehouse_id', 'type', DB::raw('SUM(quantity) as total'))
            ->groupBy('product_id', 'warehouse_id', 'type');

        if ($productId) $calcQuery->where('product_id', $productId);

        $transTotals = $calcQuery->get();

        $fromTransactions = [];
        foreach ($transTotals as $t) {
            $key = "{$t->product_id}_{$t->warehouse_id}";
            if (!isset($fromTransactions[$key])) $fromTransactions[$key] = 0;

            // in / transfer_in = إضافة
            // out / transfer_out = خصم
            $isIn = in_array($t->type, ['in', 'transfer_in']);
            $fromTransactions[$key] += $isIn ? $t->total : -$t->total;
        }

        $this->line("  عدد (منتج × مخزن) محسوبة: " . count($fromTransactions));
        $this->newLine();

        // ==========================================
        // 3. الحساب من المصادر الأصلية
        // ==========================================
        $this->info('=== 3. الحساب من المصادر الأصلية ===');

        $fromSources = [];

        // 3.1 الشحنات (in)
        $receivedQuery = DB::table('receiving_orders')
            ->select('product_id', 'warehouse_id', DB::raw('SUM(quantity) as total'))
            ->groupBy('product_id', 'warehouse_id');
        if ($productId) $receivedQuery->where('product_id', $productId);

        foreach ($receivedQuery->get() as $r) {
            $key = "{$r->product_id}_{$r->warehouse_id}";
            $fromSources[$key] = ($fromSources[$key] ?? 0) + $r->total;
        }
        $this->line("  ✅ شحنات واردة");

        // 3.2 التحويلات (out من المصدر)
        $transfersOutQuery = DB::table('stock_transfer_items as sti')
            ->join('stock_transfers as st', 'sti.stock_transfer_id', '=', 'st.id')
            ->select('sti.product_id', 'st.from_warehouse_id as warehouse_id', DB::raw('SUM(sti.quantity) as total'))
            ->groupBy('sti.product_id', 'st.from_warehouse_id');
        if ($productId) $transfersOutQuery->where('sti.product_id', $productId);

        foreach ($transfersOutQuery->get() as $t) {
            $key = "{$t->product_id}_{$t->warehouse_id}";
            $fromSources[$key] = ($fromSources[$key] ?? 0) - $t->total;
        }
        $this->line("  ✅ تحويلات خارجة");

        // 3.3 التحويلات (in للوجهة)
        $transfersInQuery = DB::table('stock_transfer_items as sti')
            ->join('stock_transfers as st', 'sti.stock_transfer_id', '=', 'st.id')
            ->select('sti.product_id', 'st.to_warehouse_id as warehouse_id', DB::raw('SUM(sti.quantity) as total'))
            ->groupBy('sti.product_id', 'st.to_warehouse_id');
        if ($productId) $transfersInQuery->where('sti.product_id', $productId);

        foreach ($transfersInQuery->get() as $t) {
            $key = "{$t->product_id}_{$t->warehouse_id}";
            $fromSources[$key] = ($fromSources[$key] ?? 0) + $t->total;
        }
        $this->line("  ✅ تحويلات داخلة");
        $this->newLine();

        // ==========================================
        // 4. المقارنة
        // ==========================================
        $this->info('=== 4. المقارنة ===');

        $allKeys = array_unique(array_merge(array_keys($fromTransactions), array_keys($fromSources)));
        sort($allKeys);

        $this->table(
            ['منتج', 'مخزن', 'من الحركات', 'من المصادر', 'الفرق'],
            collect($allKeys)->map(function ($key) use ($fromTransactions, $fromSources) {
                [$pid, $wid] = explode('_', $key);
                $a = $fromTransactions[$key] ?? 0;
                $b = $fromSources[$key] ?? 0;
                return [
                    $pid,
                    $wid,
                    $a,
                    $b,
                    $a - $b,
                ];
            })->toArray()
        );

        // ==========================================
        // 5. حركات الأصناف (عينات)
        // ==========================================
        $this->info('=== 5. العينات ===');

        $samplesQuery = DB::table('receiving_orders')
            ->select('product_id', 'warehouse_id', DB::raw('SUM(samples_quantity) as total'))
            ->where('samples_quantity', '>', 0)
            ->groupBy('product_id', 'warehouse_id');
        if ($productId) $samplesQuery->where('product_id', $productId);

        $samples = $samplesQuery->get();

        if ($samples->isEmpty()) {
            $this->warn("  لا توجد عينات واردة");
        } else {
            $this->table(
                ['منتج', 'مخزن', 'عينات واردة'],
                $samples->map(fn($s) => [$s->product_id, $s->warehouse_id, $s->total])->toArray()
            );
        }

        $this->newLine();
        $this->info('✅ اكتمل التشخيص');

        return 0;
    }
}