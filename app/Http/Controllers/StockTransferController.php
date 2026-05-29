<?php

namespace App\Http\Controllers;

use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Http\Request;  // استخدام Request العادي
use Illuminate\Support\Facades\DB;

class StockTransferController extends Controller
{
    public function index()
    {
        $products = Product::orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();
        $baseProduct = Product::where('is_base', true)->first();
        $transfers = StockTransfer::with(['fromWarehouse', 'toWarehouse', 'items.product'])->latest()->get();

        return view('backend.transfers.index', compact('products', 'warehouses', 'transfers', 'baseProduct'));
    }

    /**
     * حفظ وتنفيذ عملية التحويل مع التحقق من التوازن
     */
    public function store(Request $request)  // استخدام Request العادي
    {
        // التحقق الأساسي
        $request->validate([
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
            'type' => 'required|in:permanent,custody',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);
        
        // التحقق من التوازن
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
        
        try {
            return DB::transaction(function () use ($request) {
                
                // 1. التحقق من صحة المخزون قبل البدء
                $sourceWarehouseId = $request->from_warehouse_id;
                $items = $request->items;
                
                foreach ($items as $item) {
                    $sourceStock = Inventory::where('warehouse_id', $sourceWarehouseId)
                        ->where('product_id', $item['product_id'])
                        ->first();
                    
                    if (!$sourceStock || $sourceStock->quantity < $item['quantity']) {
                        $product = Product::find($item['product_id']);
                        throw new \Exception("الرصيد غير كافٍ للمنتج: {$product->name}");
                    }
                }
                
                // 2. إنشاء سجل التحويل الرئيسي
                $transfer = StockTransfer::create([
                    'from_warehouse_id' => $request->from_warehouse_id,
                    'to_warehouse_id'   => $request->to_warehouse_id,
                    'transfer_number'   => 'TRF-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                    'type'              => $request->type,
                    'status'            => 'completed',
                ]);
                
                $baseProductId = null;
                $baseQuantity = 0;
                
                // 3. تنفيذ التحويل للأصناف
                foreach ($items as $itemData) {
                    $productId = $itemData['product_id'];
                    $qty = $itemData['quantity'];
                    
                    $product = Product::find($productId);
                    
                    // تتبع الصنف الأساسي
                    if ($product->is_base) {
                        $baseProductId = $productId;
                        $baseQuantity = $qty;
                    }
                    
                    // الخصم من المخزن المصدر
                    Inventory::where('warehouse_id', $request->from_warehouse_id)
                        ->where('product_id', $productId)
                        ->decrement('quantity', $qty);
                    
                    // الإضافة للمخزن المستلم
                    Inventory::updateOrCreate(
                        [
                            'warehouse_id' => $request->to_warehouse_id, 
                            'product_id' => $productId
                        ],
                        ['quantity' => DB::raw("quantity + $qty")]
                    );
                    
                    // تسجيل تفاصيل التحويل
                    StockTransferItem::create([
                        'stock_transfer_id' => $transfer->id,
                        'product_id'        => $productId,
                        'quantity'          => $qty,
                    ]);
                }
                
                // 4. تحديث إجمالي الوجبات في جدول التحويل
                $totalMeals = $this->calculateTotalMeals($items);
                $transfer->update(['total_meals' => $totalMeals]);
                
                return redirect()->route('admin.transfers.index')
                    ->with('success', "تم تنفيذ التحويل بنجاح. كمية الأساسي: {$baseQuantity} | إجمالي الوجبات: {$totalMeals}");
                    
            });
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
    
    /**
     * حساب إجمالي الوجبات (باستخدام معامل التحويل)
     */
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