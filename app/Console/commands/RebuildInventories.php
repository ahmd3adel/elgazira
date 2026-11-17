<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RebuildInventories extends Command
{
    protected $signature = 'inventories:rebuild {--product= : ID منتج محدد}';
    protected $description = 'إعادة بناء أرصدة inventories من inventory_transactions';

    public function handle()
    {
        $productId = $this->option('product');

        if ($productId) {
            $products = collect([(object)['id' => $productId]]);
            $this->info("🔄 إعادة بناء رصيد المنتج #{$productId} فقط");
        } else {
            $products = DB::table('products')->select('id', 'name')->get();
            $this->info("🔄 إعادة بناء أرصدة كل المنتجات (" . $products->count() . " منتج)");
        }

        $totalFixed = 0;

        foreach ($products as $product) {
            // احسب صافي الرصيد لكل مخزن من الحركات
            $netByWarehouse = [];

            $transactions = DB::table('inventory_transactions')
                ->where('product_id', $product->id)
                ->select('warehouse_id', 'type', DB::raw('SUM(quantity) as total'))
                ->groupBy('warehouse_id', 'type')
                ->get();

            foreach ($transactions as $t) {
                if (!isset($netByWarehouse[$t->warehouse_id])) {
                    $netByWarehouse[$t->warehouse_id] = 0;
                }
                $netByWarehouse[$t->warehouse_id] += ($t->type === 'in') ? $t->total : -$t->total;
            }

            // حدّث inventories
            foreach ($netByWarehouse as $warehouseId => $net) {
                $existing = DB::table('inventories')
                    ->where('warehouse_id', $warehouseId)
                    ->where('product_id', $product->id)
                    ->first();

                $newQty = max(0, (int) $net);

                if ($existing) {
                    if ((int) $existing->quantity !== $newQty) {
                        DB::table('inventories')
                            ->where('id', $existing->id)
                            ->update([
                                'quantity' => $newQty,
                                'last_movement_at' => now(),
                                'updated_at' => now(),
                            ]);

                        $this->line("  منتج #{$product->id} مخزن #{$warehouseId}: {$existing->quantity} → {$newQty}");
                        $totalFixed++;
                    }
                } elseif ($newQty > 0) {
                    DB::table('inventories')->insert([
                        'warehouse_id' => $warehouseId,
                        'product_id' => $product->id,
                        'quantity' => $newQty,
                        'samples_quantity' => 0,
                        'last_movement_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $this->line("  منتج #{$product->id} مخزن #{$warehouseId}: +{$newQty} (سجل جديد)");
                    $totalFixed++;
                }
            }
        }

        $this->newLine();
        $this->info("✅ تم تحديث {$totalFixed} سجل");
        return 0;
    }
}