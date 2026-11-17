<?php
// app/Http/Controllers/StockTransferController.php

namespace App\Http\Controllers;

use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Sample;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockTransferController extends Controller
{
    public function index()
    {
        $products = Product::orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();
        $baseProduct = Product::where('is_base', true)->first();
        $transfers = StockTransfer::with(['fromWarehouse', 'toWarehouse', 'items.product'])->latest()->get();

        $stocks = Inventory::select('warehouse_id', 'product_id', 'quantity', 'samples_quantity')
            ->get()
            ->groupBy('warehouse_id')
            ->map(function ($items) {
                return $items->mapWithKeys(function ($item) {
                    return [$item->product_id => [
                        'main'    => (int) $item->quantity,
                        'samples' => (int) $item->samples_quantity,
                        'total'   => (int) ($item->quantity + $item->samples_quantity),
                    ]];
                });
            });

        return view('backend.transfers.index', compact('products', 'warehouses', 'transfers', 'baseProduct', 'stocks'));
    }

    /**
     * endpoint: بيرجع الأرصدة الحالية لكل item
     * GET /admin/transfers/check-stock?from_warehouse_id=X&items[0][product_id]=Y&items[0][quantity]=Z
     */
    public function checkStock(Request $request)
    {
        $request->validate([
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $warehouseId = $request->from_warehouse_id;
        $result = [];

        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);
            if (!$product) continue;

            $stock = Inventory::where('warehouse_id', $warehouseId)
                ->where('product_id', $item['product_id'])
                ->first();

            $main    = (int) ($stock->quantity ?? 0);
            $samples = (int) ($stock->samples_quantity ?? 0);
            $available = $main + $samples;
            $requested = (int) $item['quantity'];

            $result[] = [
                'product_id'     => $item['product_id'],
                'product_name'   => $product->name,
                'main'           => $main,
                'samples'        => $samples,
                'available'      => $available,
                'requested'      => $requested,
                'needs_samples'  => max(0, $requested - $main),
                'is_available'   => $available >= $requested,
            ];
        }

        return response()->json(['items' => $result]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
            'type' => 'required|in:permanent,custody',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'confirm_samples' => 'nullable|in:0,1',
        ]);

        // ✅ تحديد نوع الوجهة
        $toWarehouse = Warehouse::findOrFail($request->to_warehouse_id);
$isSubWarehouse = ($toWarehouse->type === 'sub');

        // ✅ التحقق من التوازن — فقط لو الوجهة ليست مخزن فرعي
        if (!$isSubWarehouse) {
            $baseQuantity = 0;
            $otherTotal = 0;
            $hasBaseProduct = false;

            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                if ($product && $product->is_base) {
                    $hasBaseProduct = true;
                    $baseQuantity = $item['quantity'];
                } elseif ($product) {
                    $otherTotal += $item['quantity'];
                }
            }

            if (!$hasBaseProduct) {
                return back()->withInput()->with('error', 'يجب إضافة الصنف الأساسي (سادة 40) إلى عملية التحويل');
            }

            if ($baseQuantity != $otherTotal) {
                $difference = abs($baseQuantity - $otherTotal);
                $message = "⚠️ عدم توازن: كمية الصنف الأساسي ($baseQuantity) " .
                          ($baseQuantity > $otherTotal ? "أكبر من" : "أقل من") .
                          " مجموع الأصناف التامة ($otherTotal) بفارق $difference";
                return back()->withInput()->with('error', $message);
            }
        }
        // لو مخزن فرعي: مسموح صنف واحد أو أي مزيج من غير شرط توازن

        // ✅ server-side safety net: لو محتاج سحب من العينات وما فيش تأكيد
        $confirmSamples = $request->boolean('confirm_samples');
        if (!$confirmSamples) {
            foreach ($request->items as $item) {
                $stock = Inventory::where('warehouse_id', $request->from_warehouse_id)
                    ->where('product_id', $item['product_id'])
                    ->first();

                $main = (int) ($stock->quantity ?? 0);
                if ($item['quantity'] > $main) {
                    return back()->withInput()->with(
                        'error',
                        'الرصيد الأساسي غير كافٍ لبعض الأصناف. لازم تأكيد السحب من العينات أولاً.'
                    );
                }
            }
        }

        try {
            return DB::transaction(function () use ($request) {

                $sourceWarehouseId = $request->from_warehouse_id;
                $targetWarehouseId = $request->to_warehouse_id;

                // ✅ 1. التحقق من الرصيد (أساسي + عينات)
                foreach ($request->items as $item) {
                    $product = Product::find($item['product_id']);

                    $stock = Inventory::where('warehouse_id', $sourceWarehouseId)
                        ->where('product_id', $item['product_id'])
                        ->lockForUpdate()
                        ->first();

                    $mainQty    = $stock->quantity ?? 0;
                    $samplesQty = $stock->samples_quantity ?? 0;
                    $available  = $mainQty + $samplesQty;

                    if ($available < $item['quantity']) {
                        throw new \Exception(
                            "الرصيد غير كافٍ للمنتج: {$product->name} — " .
                            "المتاح: {$mainQty} أساسي + {$samplesQty} عينات = {$available}، " .
                            "المطلوب: {$item['quantity']}"
                        );
                    }
                }

                // ✅ 2. إنشاء سجل التحويل
                $transfer = StockTransfer::create([
                    'from_warehouse_id' => $sourceWarehouseId,
                    'to_warehouse_id'   => $targetWarehouseId,
                    'type'              => $request->type,
                ]);

                $referenceNumber = $transfer->transfer_number ?? ('TRF-' . $transfer->id);

                $baseProductId = null;
                $baseQty = 0;

                // ✅ 3. تنفيذ الخصم
                foreach ($request->items as $itemData) {
                    $productId = $itemData['product_id'];
                    $qty = $itemData['quantity'];
                    $product = Product::find($productId);

                    if ($product->is_base) {
                        $baseProductId = $productId;
                        $baseQty = $qty;
                    }

                    $stock = Inventory::where('warehouse_id', $sourceWarehouseId)
                        ->where('product_id', $productId)
                        ->lockForUpdate()
                        ->first();

                    $mainQty    = $stock->quantity;
                    $samplesQty = $stock->samples_quantity ?? 0;

                    $deductFromMain    = min($mainQty, $qty);
                    $deductFromSamples = $qty - $deductFromMain;

                    $stock->update([
                        'quantity'         => max(0, $mainQty - $deductFromMain),
                        'samples_quantity' => max(0, $samplesQty - $deductFromSamples),
                        'last_movement_at' => now(),
                    ]);

                    // تسجيل حركة الخصم من الأساسي
                    if ($deductFromMain > 0) {
                        InventoryTransaction::create([
                            'product_id'       => $productId,
                            'warehouse_id'     => $sourceWarehouseId,
                            'type'             => 'out',
                            'reference_number' => $referenceNumber,
                            'quantity'         => $deductFromMain,
                            'notes'            => "تحويل مخزني - خصم من الرصيد الأساسي",
                            'user_id'          => auth()->id() ?? 1,
                        ]);
                    }

                    // تسجيل حركة الخصم من العينات + خصم من samples table
                    if ($deductFromSamples > 0) {
                        InventoryTransaction::create([
                            'product_id'       => $productId,
                            'warehouse_id'     => $sourceWarehouseId,
                            'type'             => 'out',
                            'reference_number' => $referenceNumber,
                            'quantity'         => $deductFromSamples,
                            'notes'            => "تحويل مخزني - خصم من رصيد العينات",
                            'user_id'          => auth()->id() ?? 1,
                        ]);

                        $this->deductFromSamplesTable(
                            $sourceWarehouseId,
                            $productId,
                            $deductFromSamples
                        );
                    }

                    // الإضافة للوجهة (كله في الرصيد الأساسي)
                    $target = Inventory::firstOrCreate(
                        [
                            'warehouse_id' => $targetWarehouseId,
                            'product_id'   => $productId,
                        ],
                        [
                            'quantity'         => 0,
                            'samples_quantity' => 0,
                        ]
                    );

                    $target->increment('quantity', $qty);
                    $target->update(['last_movement_at' => now()]);

                    InventoryTransaction::create([
                        'product_id'       => $productId,
                        'warehouse_id'     => $targetWarehouseId,
                        'type'             => 'in',
                        'reference_number' => $referenceNumber,
                        'quantity'         => $qty,
                        'notes'            => "تحويل مخزني - إضافة",
                        'user_id'          => auth()->id() ?? 1,
                    ]);

                    // حفظ samples_deducted في الـ item
                    StockTransferItem::create([
                        'stock_transfer_id' => $transfer->id,
                        'product_id'        => $productId,
                        'quantity'          => $qty,
                        'samples_deducted'  => $deductFromSamples,
                    ]);
                }

                $totalMeals = $this->calculateTotalMeals($request->items);

                return redirect()->route('admin.transfers.index')
                    ->with('success', "تم تنفيذ التحويل بنجاح. كمية الأساسي: {$baseQty} | إجمالي الوجبات: {$totalMeals}");

            });
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * خصم FIFO من جدول samples
     */
    private function deductFromSamplesTable(int $warehouseId, int $productId, int $qtyToDeduct): int
    {
        $remaining = $qtyToDeduct;

        $samples = Sample::where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->whereIn('status', ['pending', 'tested', 'approved'])
            ->whereColumn('consumed_quantity', '<', 'received_quantity')
            ->orderBy('created_at', 'ASC')
            ->lockForUpdate()
            ->get();

        foreach ($samples as $sample) {
            if ($remaining <= 0) break;

            $available = $sample->received_quantity - $sample->consumed_quantity;
            if ($available <= 0) continue;

            $deduct = min($available, $remaining);

            $sample->increment('consumed_quantity', $deduct);
            $remaining -= $deduct;
        }

        if ($remaining > 0) {
            \Log::warning("Samples table out of sync", [
                'warehouse_id' => $warehouseId,
                'product_id'   => $productId,
                'requested'    => $qtyToDeduct,
                'deducted'     => $qtyToDeduct - $remaining,
                'remaining'    => $remaining,
            ]);
        }

        return $qtyToDeduct - $remaining;
    }

    private function calculateTotalMeals($items)
    {
        $total = 0;
        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            if ($product && !$product->is_base) {
                $total += $item['quantity'];
            }
        }
        return $total;
    }
}