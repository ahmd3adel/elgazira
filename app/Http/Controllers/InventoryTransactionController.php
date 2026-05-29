<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use App\Http\Requests\StoreInventoryTransactionRequest;
use App\Http\Requests\UpdateInventoryTransactionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryTransactionController extends Controller
{
    /**
     * عرض قائمة حركات المخزون
     */
    public function index(Request $request)
    {
        $transactions = InventoryTransaction::with(['product', 'warehouse'])
            ->when($request->product_id, function($query, $productId) {
                $query->where('product_id', $productId);
            })
            ->when($request->warehouse_id, function($query, $warehouseId) {
                $query->where('warehouse_id', $warehouseId);
            })
            ->when($request->type, function($query, $type) {
                $query->where('type', $type);
            })
            ->when($request->from_date, function($query, $date) {
                $query->whereDate('movement_date', '>=', $date);
            })
            ->when($request->to_date, function($query, $date) {
                $query->whereDate('movement_date', '<=', $date);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(50);
        
        $products = Product::orderBy('name')->get();
        $warehouses = Warehouse::all();
        
        return view('backend.inventory-transactions.index', compact('transactions', 'products', 'warehouses'));
    }

    /**
     * عرض نموذج إضافة حركة جديدة
     */
    public function create()
    {
        $products = Product::orderBy('name')->get();
        $warehouses = Warehouse::all();
        
        return view('backend.inventory-transactions.create', compact('products', 'warehouses'));
    }

    /**
     * حفظ حركة مخزنية جديدة مع تحديث الرصيد
     */
    public function store(StoreInventoryTransactionRequest $request)
    {
        try {
            DB::beginTransaction();
            
            // 1. إنشاء الحركة
            $transaction = InventoryTransaction::create([
                'product_id' => $request->product_id,
                'warehouse_id' => $request->warehouse_id,
                'type' => $request->type, // 'in' أو 'out'
                'quantity' => $request->quantity,
                'reference_type' => $request->reference_type,
                'reference_id' => $request->reference_id,
                'movement_date' => $request->movement_date ?? now(),
                'notes' => $request->notes,
                'created_by' => auth()->id(),
            ]);
            
            // 2. تحديث جدول المخزون (inventories)
            $inventory = Inventory::where([
                'product_id' => $request->product_id,
                'warehouse_id' => $request->warehouse_id
            ])->first();
            
            if ($request->type === 'in') {
                // إضافة كمية
                if ($inventory) {
                    $inventory->increment('quantity', $request->quantity);
                } else {
                    $inventory = Inventory::create([
                        'product_id' => $request->product_id,
                        'warehouse_id' => $request->warehouse_id,
                        'quantity' => $request->quantity,
                    ]);
                }
            } elseif ($request->type === 'out') {
                // خصم كمية مع التحقق من الرصيد
                if (!$inventory || $inventory->quantity < $request->quantity) {
                    throw new \Exception('الرصيد غير كافٍ لهذه العملية');
                }
                $inventory->decrement('quantity', $request->quantity);
            }
            
            // تحديث تاريخ آخر حركة
            if ($inventory) {
                $inventory->update(['last_movement_at' => now()]);
            }
            
            DB::commit();
            
            return redirect()->route('inventory-transactions.index')
                ->with('success', 'تم إضافة حركة المخزون بنجاح');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * عرض تفاصيل حركة محددة
     */
    public function show(InventoryTransaction $inventoryTransaction)
    {
        $inventoryTransaction->load(['product', 'warehouse', 'createdBy']);
        
        return view('backend.inventory-transactions.show', compact('inventoryTransaction'));
    }

    /**
     * عرض نموذج تعديل الحركة
     */
    public function edit(InventoryTransaction $inventoryTransaction)
    {
        // فقط الحركات الحديثة (آخر 24 ساعة) يمكن تعديلها
        if ($inventoryTransaction->created_at < now()->subDay()) {
            return back()->with('error', 'لا يمكن تعديل الحركات القديمة');
        }
        
        $products = Product::orderBy('name')->get();
        $warehouses = Warehouse::all();
        
        return view('backend.inventory-transactions.edit', compact('inventoryTransaction', 'products', 'warehouses'));
    }

    /**
     * تحديث حركة مخزنية (مع عكس التأثير القديم وتطبيق الجديد)
     */
    public function update(UpdateInventoryTransactionRequest $request, InventoryTransaction $inventoryTransaction)
    {
        try {
            DB::beginTransaction();
            
            // 1. عكس تأثير الحركة القديمة
            $inventory = Inventory::where([
                'product_id' => $inventoryTransaction->product_id,
                'warehouse_id' => $inventoryTransaction->warehouse_id
            ])->first();
            
            if ($inventoryTransaction->type === 'in') {
                $inventory->decrement('quantity', $inventoryTransaction->quantity);
            } else {
                $inventory->increment('quantity', $inventoryTransaction->quantity);
            }
            
            // 2. تحديث بيانات الحركة
            $oldProductId = $inventoryTransaction->product_id;
            $oldWarehouseId = $inventoryTransaction->warehouse_id;
            
            $inventoryTransaction->update([
                'product_id' => $request->product_id,
                'warehouse_id' => $request->warehouse_id,
                'type' => $request->type,
                'quantity' => $request->quantity,
                'movement_date' => $request->movement_date,
                'notes' => $request->notes,
            ]);
            
            // 3. تطبيق تأثير الحركة الجديدة
            $newInventory = Inventory::firstOrCreate([
                'product_id' => $request->product_id,
                'warehouse_id' => $request->warehouse_id
            ]);
            
            if ($request->type === 'in') {
                $newInventory->increment('quantity', $request->quantity);
            } else {
                if ($newInventory->quantity < $request->quantity) {
                    throw new \Exception('الرصيد غير كافٍ للتعديل');
                }
                $newInventory->decrement('quantity', $request->quantity);
            }
            
            $newInventory->update(['last_movement_at' => now()]);
            
            // 4. تنظيف المخزون الفارغ (اختياري)
            if ($oldProductId != $request->product_id || $oldWarehouseId != $request->warehouse_id) {
                $oldInventory = Inventory::where([
                    'product_id' => $oldProductId,
                    'warehouse_id' => $oldWarehouseId
                ])->first();
                
                if ($oldInventory && $oldInventory->quantity == 0) {
                    $oldInventory->delete();
                }
            }
            
            DB::commit();
            
            return redirect()->route('inventory-transactions.index')
                ->with('success', 'تم تعديل الحركة بنجاح');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * حذف حركة مخزنية (مع عكس تأثيرها)
     */
    public function destroy(InventoryTransaction $inventoryTransaction)
    {
        try {
            DB::beginTransaction();
            
            // عكس تأثير الحركة على المخزون
            $inventory = Inventory::where([
                'product_id' => $inventoryTransaction->product_id,
                'warehouse_id' => $inventoryTransaction->warehouse_id
            ])->first();
            
            if ($inventory) {
                if ($inventoryTransaction->type === 'in') {
                    $inventory->decrement('quantity', $inventoryTransaction->quantity);
                } else {
                    $inventory->increment('quantity', $inventoryTransaction->quantity);
                }
                
                $inventory->update(['last_movement_at' => now()]);
                
                // حذف سجل المخزون إذا أصبح الكمية صفر
                if ($inventory->quantity == 0) {
                    $inventory->delete();
                }
            }
            
            // حذف الحركة
            $inventoryTransaction->delete();
            
            DB::commit();
            
            return redirect()->route('inventory-transactions.index')
                ->with('success', 'تم حذف الحركة وعكس تأثيرها بنجاح');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }
    
    /**
     * API: جرد رصيد المنتج في مستودع معين
     */
    public function getCurrentStock($productId, $warehouseId)
    {
        $inventory = Inventory::where([
            'product_id' => $productId,
            'warehouse_id' => $warehouseId
        ])->first();
        
        return response()->json([
            'quantity' => $inventory ? $inventory->quantity : 0,
            'last_movement_at' => $inventory ? $inventory->last_movement_at : null
        ]);
    }
}