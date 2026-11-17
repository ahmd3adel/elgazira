<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DebugWafer extends Command
{
    protected $signature = 'debug:wafer';
    protected $description = 'تشخيص حركات وأرصدة الويفر';

    public function handle()
    {
        // 1. ابحث عن منتج الويفر (بأي اسم فيه "ويفر" أو "وافر")
        $wafer = DB::table('products')
            ->where('name', 'LIKE', '%ويفر%')
            ->orWhere('name', 'LIKE', '%وافر%')
            ->orWhere('name', 'LIKE', '%wafer%')
            ->first();

        if (!$wafer) {
            $this->error('❌ منتج الويفر مش موجود!');
            $this->info('كل المنتجات المتاحة:');
            DB::table('products')->select('id', 'name')->get()->each(function ($p) {
                $this->line("  #{$p->id} — {$p->name}");
            });
            return 1;
        }

        $waferId = $wafer->id;
        $this->info("✅ منتج الويفر: #{$waferId} — {$wafer->name}");
        $this->newLine();

        // 2. الأرصدة الحالية
        $this->info('=== الأرصدة الحالية في inventories ===');
        $inventories = DB::table('inventories')
            ->where('product_id', $waferId)
            ->get();

        if ($inventories->isEmpty()) {
            $this->warn('  لا توجد سجلات في inventories للويفر');
        } else {
            foreach ($inventories as $inv) {
                $this->line("  مخزن #{$inv->warehouse_id}: quantity={$inv->quantity}, samples={$inv->samples_quantity} | last_movement={$inv->last_movement_at}");
            }
        }
        $this->newLine();

        // 3. التحويلات
        $this->info('=== التحويلات اللي فيها ويفر ===');
        $transfers = DB::table('stock_transfer_items as sti')
            ->join('stock_transfers as st', 'sti.stock_transfer_id', '=', 'st.id')
            ->where('sti.product_id', $waferId)
            ->select('st.id', 'st.from_warehouse_id', 'st.to_warehouse_id', 'st.created_at', 'sti.quantity')
            ->orderBy('st.id', 'desc')
            ->get();

        if ($transfers->isEmpty()) {
            $this->warn('  لا توجد تحويلات للويفر');
        } else {
            foreach ($transfers as $t) {
                $this->line("  تحويل #{$t->id}: من مخزن #{$t->from_warehouse_id} → إلى مخزن #{$t->to_warehouse_id} | qty={$t->quantity} | {$t->created_at}");
            }
        }
        $this->newLine();

        // 4. حركات المخزون
        $this->info('=== حركات المخزون للويفر (آخر 20) ===');
        $transactions = DB::table('inventory_transactions')
            ->where('product_id', $waferId)
            ->select('id', 'warehouse_id', 'type', 'quantity', 'reference_number', 'notes', 'created_at')
            ->orderBy('id', 'desc')
            ->take(20)
            ->get();

        if ($transactions->isEmpty()) {
            $this->warn('  لا توجد حركات للويفر');
        } else {
            foreach ($transactions as $t) {
                $this->line("  #{$t->id} | مخزن #{$t->warehouse_id} | {$t->type} {$t->quantity} | ref={$t->reference_number} | {$t->created_at}");
                if ($t->notes) {
                    $this->line("     → {$t->notes}");
                }
            }
        }
        $this->newLine();

        // 5. مجموع الداخل والخارج لكل مخزن
        $this->info('=== مجموع الداخل/الخارج لكل مخزن ===');
        $totals = DB::table('inventory_transactions')
            ->where('product_id', $waferId)
            ->select('warehouse_id', 'type', DB::raw('SUM(quantity) as total'))
            ->groupBy('warehouse_id', 'type')
            ->get();

        if ($totals->isEmpty()) {
            $this->warn('  لا توجد حركات');
        } else {
            $grouped = [];
            foreach ($totals as $t) {
                $grouped[$t->warehouse_id][$t->type] = (int) $t->total;
            }
            foreach ($grouped as $wid => $types) {
                $in = $types['in'] ?? 0;
                $out = $types['out'] ?? 0;
                $net = $in - $out;
                $this->line("  مخزن #{$wid}: IN={$in}, OUT={$out}, Net={$net}");
            }
        }
        $this->newLine();

        // 6. الشحنات الواردة (المصدر الأصلي)
        $this->info('=== الشحنات الواردة للويفر ===');
        $receivings = DB::table('receiving_orders')
            ->where('product_id', $waferId)
            ->select('id', 'document_number', 'warehouse_id', 'quantity', 'samples_quantity', 'created_at')
            ->orderBy('id')
            ->get();

        if ($receivings->isEmpty()) {
            $this->warn('  لا توجد شحنات واردة للويفر');
        } else {
            foreach ($receivings as $r) {
                $this->line("  شحنة #{$r->id} ({$r->document_number}) | مخزن #{$r->warehouse_id} | qty={$r->quantity}, samples={$r->samples_quantity} | {$r->created_at}");
            }
        }

        return 0;
    }
}