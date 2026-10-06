<?php

namespace App\Http\Controllers;

use App\Models\DepartmentAllocation;
use App\Http\Requests\StoreDepartmentAllocationRequest;
use App\Http\Requests\UpdateDepartmentAllocationRequest;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\DepartmentAllocationStoreRequest;
use App\Models\Department;
use App\Models\DepartmentAllocationItem;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use App\Models\Warehouse;

class DepartmentAllocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index(Request $request)
{
    if ($request->ajax()) {
        try {
            // قاعدة البيانات الأساسية
            $query = \App\Models\DepartmentAllocation::with(['department', 'items.product', 'createdBy'])
                ->select('department_allocations.*');
            
            // تطبيق الفلاتر (إن وجدت)
            $hasFilters = false;
            
            if ($request->from_date) {
                $query->whereDate('receite_date', '>=', $request->from_date);
                $hasFilters = true;
            }
            if ($request->to_date) {
                $query->whereDate('receite_date', '<=', $request->to_date);
                $hasFilters = true;
            }
            if ($request->filled('department_id')) {
                $query->whereIn('department_id', (array)$request->department_id);
                $hasFilters = true;
            }
            
            $allProducts = \App\Models\Product::orderBy('name')->get();
            
            // ✅ حالة وجود فلتر: تجميع البيانات حسب الإدارة
            if ($hasFilters) {
                return $this->getGroupedData($query, $allProducts, $request);
            }
            
            // ✅ حالة عدم وجود فلتر: عرض البيانات العادية (كل صف على حدة)
            return $this->getNormalData($query, $allProducts, $request);
            
        } catch (\Exception $e) {
            \Log::error('Error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    // عرض الصفحة (GET عادي)
    $products = \App\Models\Product::orderBy('name')->get();
    $departments = \App\Models\Department::all();
    return view('backend.department_allocations.index', compact('products', 'departments'));
}
/**
 * جلب بيانات إذن صرف للتعديل (AJAX)
 */
public function editData($id)
{
    try {
        $allocation = DepartmentAllocation::with(['items.product', 'department'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id'             => $allocation->id,
                'department_id'  => $allocation->department_id,
                'department_name'=> optional($allocation->department)->name,
                'receite_date'   => $allocation->receite_date
                                        ? $allocation->receite_date->format('Y-m-d')
                                        : null,
                'notes'          => $allocation->notes,
                'items'          => $allocation->items->map(function ($item) {
                    return [
                        'id'         => $item->id,
                        'product_id' => $item->product_id,
                        'product_name' => optional($item->product)->name,
                        'quantity'   => (int) $item->quantity,
                        'is_base'    => (bool) optional($item->product)->is_base,
                    ];
                })->values(),
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
    }
}

/**
 * تحديث إذن الصرف (مع إعادة احتساب المخزون)
 */
public function updateAllocation(Request $request, $id)
{
    try {
        DB::beginTransaction();

        $allocation = DepartmentAllocation::with('items')->findOrFail($id);
        $department = Department::with('operationWarehouse')->findOrFail($request->department_id);
        $warehouseId = $department->operation_warehouse_id;

        if (!$warehouseId) {
            throw new \Exception("الإدارة {$department->name} ليس لديها مخزن تشغيل مرتبط.");
        }

        $warehouse = Warehouse::find($warehouseId);
        if (!$warehouse || !$warehouse->status) {
            throw new \Exception("مخزن التشغيل غير نشط أو غير موجود");
        }

        // ========== 1. إرجاع الكميات القديمة أولاً ==========
        foreach ($allocation->items as $oldItem) {
            // إرجاع الكمية لمخزن التشغيل
            Inventory::where('warehouse_id', $warehouseId)
                ->where('product_id', $oldItem->product_id)
                ->increment('quantity', $oldItem->quantity);

            // حذف حركة المخزون القديمة
// ✅ صح — نستخدم reference_number
InventoryTransaction::where('reference_number', 'DA-' . $allocation->id)
    ->where('product_id', $oldItem->product_id)
    ->where('type', 'out')
    ->delete();
        }

        // حذف العناصر القديمة
        DepartmentAllocationItem::where('allocation_id', $allocation->id)->delete();

        // ========== 2. التحقق من التوازن ==========
        $baseQuantity = 0;
        $otherTotal = 0;
        $hasBaseProduct = false;

        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);
            if (!$product) continue;

            if ($product->is_base) {
                $hasBaseProduct = true;
                $baseQuantity = (int) $item['quantity'];
            } else {
                $otherTotal += (int) $item['quantity'];
            }
        }

        if (!$hasBaseProduct) {
            throw new \Exception('يجب إضافة الصنف الأساسي إلى إذن الصرف');
        }

        if ($baseQuantity !== $otherTotal) {
            $diff = abs($baseQuantity - $otherTotal);
            throw new \Exception(
                "⚠️ عدم توازن: كمية الصنف الأساسي ($baseQuantity) " .
                ($baseQuantity > $otherTotal ? "أكبر من" : "أقل من") .
                " مجموع الأصناف التامة ($otherTotal) بفارق $diff"
            );
        }

        // ========== 3. التحقق من الرصيد الجديد ==========
        $stockErrors = [];
        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);
            $stock = Inventory::where('warehouse_id', $warehouseId)
                ->where('product_id', $item['product_id'])
                ->first();
            $available = $stock ? $stock->quantity : 0;

            if ($available < $item['quantity']) {
                $stockErrors[] = "• {$product->name}: متاح {$available} / مطلوب {$item['quantity']}";
            }
        }

        if (!empty($stockErrors)) {
            throw new \Exception(
                "❌ الرصيد غير كافٍ في مخزن الإدارة ({$warehouse->name}):\n" .
                implode("\n", $stockErrors)
            );
        }

        // ========== 4. تحديث الإذن ==========
        $allocation->update([
            'receite_date'  => $request->order_date,
            'department_id' => $request->department_id,
            'notes'         => $request->notes,
        ]);

        $totalMealsSum = 0;

        // ========== 5. الصرف الجديد ==========
        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);
            $conversionFactor = $product->conversion_factor ?? 1;
            $totalMeals = $item['quantity'] * $conversionFactor;

            Inventory::where('warehouse_id', $warehouseId)
                ->where('product_id', $item['product_id'])
                ->decrement('quantity', $item['quantity']);

            Inventory::where('warehouse_id', $warehouseId)
                ->where('product_id', $item['product_id'])
                ->update(['last_movement_at' => now()]);
InventoryTransaction::create([
    'product_id'       => $product->id,
    'warehouse_id'     => $warehouseId,
    'type'             => 'out',
    'reference_number' => 'DA-' . $allocation->id,
    'quantity'         => $item['quantity'],
    'user_id'          => auth()->id() ?? 1,
    'notes'            => "تعديل صرف لإدارة: {$department->name}",
]);

            DepartmentAllocationItem::create([
                'allocation_id' => $allocation->id,
                'product_id'    => $item['product_id'],
                'quantity'      => $item['quantity'],
                'total_meals'   => $totalMeals,
            ]);

            $totalMealsSum += $totalMeals;
        }

        $allocation->update(['total_meals' => $totalMealsSum]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => "تم تحديث إذن الصرف بنجاح. إجمالي الوجبات: {$totalMealsSum}",
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error updating department allocation: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
}

/**
 * استرداد إذن الصرف (إرجاع الكميات للمخزون + حذف الإذن)
 */
public function refund($id)
{
    try {
        DB::beginTransaction();

        $allocation = DepartmentAllocation::with(['items.product', 'department'])
            ->findOrFail($id);

        $department = $allocation->department;
        $warehouseId = $department ? $department->operation_warehouse_id : null;

        if (!$warehouseId) {
            throw new \Exception("لا يوجد مخزن تشغيل مرتبط بالإدارة");
        }

        $warehouse = Warehouse::find($warehouseId);
        if (!$warehouse) {
            throw new \Exception("مخزن التشغيل غير موجود");
        }

        // ========== إرجاع الكميات للمخزون ==========
        foreach ($allocation->items as $item) {
            // إرجاع الكمية لمخزن التشغيل
            Inventory::where('warehouse_id', $warehouseId)
                ->where('product_id', $item->product_id)
                ->increment('quantity', $item->quantity);

            // تحديث آخر حركة
            Inventory::where('warehouse_id', $warehouseId)
                ->where('product_id', $item->product_id)
                ->update(['last_movement_at' => now()]);

            // تسجيل حركة إرجاع
// عند تسجيل حركة الإرجاع
InventoryTransaction::create([
    'product_id'       => $item->product_id,
    'warehouse_id'     => $warehouseId,
    'type'             => 'in',
    'reference_number' => 'DA-REFUND-' . $allocation->id,   // 👈 مرجع مميز للاسترداد
    'quantity'         => $item->quantity,
    'user_id'          => auth()->id() ?? 1,
    'notes'            => "استرداد إذن صرف رقم #{$allocation->id} - إدارة: {$department->name}",
]);
        }

        // حذف تفاصيل الإذن
        DepartmentAllocationItem::where('allocation_id', $allocation->id)->delete();

        // حذف الإذن
        $allocation->delete();

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => "تم استرداد الإذن وإرجاع الكميات لمخزن الإدارة بنجاح.",
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error refunding department allocation: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
}
/**
 * عرض البيانات العادية (كل صف على حدة)
 */
private function getNormalData($query, $allProducts, $request)
{
    // ✅ إضافة with(['department']) وتحميل العلاقات بشكل صريح
    $orders = $query->with(['department', 'items', 'items.product'])->latest('receite_date');
    
    $dataTable = DataTables::of($orders)
        ->addIndexColumn()
        ->addColumn('order_date', function($order) {
            return $order->receite_date ? $order->receite_date->format('Y-m-d') : '---';
        })
        ->addColumn('department_name', function($order) {
            // ✅ استخدام optional لمنع الأخطاء
            return optional($order->department)->name ?? '---';
        })
        ->addColumn('entity_type', function($order) {
            $entityType = optional($order->department)->entity_type;
            return $entityType == 'education' ? 'تربية وتعليم' : ($entityType == 'azhar' ? 'أزهر شريف' : '---');
        })
        ->addColumn('total_qty', function($order) {
            return $order->items ? $order->items->sum('quantity') : 0;
        });
    
    // ✅ طريقة آمنة لإضافة الأعمدة الديناميكية
    foreach ($allProducts as $product) {
        $columnName = 'prod_' . $product->id;
        $dataTable->addColumn($columnName, function($order) use ($product) {
            if (!$order->items) {
                return 0;
            }
            $item = $order->items->firstWhere('product_id', $product->id);
            return $item ? (int)$item->quantity : 0;
        });
    }
    
    return $dataTable->rawColumns(['action'])->make(true);
}

/**
 * عرض البيانات المجمعة (عند وجود فلتر)
 */
/**
 * عرض البيانات المجمعة (عند وجود فلتر)
 */
private function getGroupedData($query, $allProducts, $request)
{
    // جلب جميع السجلات بعد تطبيق الفلتر
    $filteredOrders = $query->with(['department', 'items'])->get();
    
    // تجميع البيانات حسب الإدارة
    $groupedData = [];
    $grandTotals = [];
    
    foreach ($filteredOrders as $order) {
        $deptId = $order->department_id;
        $deptName = $order->department->name ?? '---';
        $entityType = $order->department->entity_type ?? 'education';
        
        if (!isset($groupedData[$deptId])) {
            $groupedData[$deptId] = [
                'DT_RowIndex' => 0, // سيتم تعيينه لاحقاً
                'order_date' => '', // ✅ إضافة حقل order_date (فارغ لأن البيانات مجمعة)
                'department_id' => $deptId,
                'department_name' => $deptName,
                'entity_type' => $entityType == 'education' ? 'تربية وتعليم' : 'أزهر شريف',
                'items' => [],
                'total_qty' => 0,
                'action' => '' // ✅ إضافة عمود action فارغ
            ];
            
            // تهيئة إجماليات المنتجات لهذه الإدارة
            foreach ($allProducts as $product) {
                $groupedData[$deptId]['prod_' . $product->id] = 0;
            }
        }
        
        // إضافة كميات هذا الأمر إلى إجماليات الإدارة
        foreach ($order->items as $item) {
            $productId = $item->product_id;
            $groupedData[$deptId]['prod_' . $productId] += $item->quantity;
            $groupedData[$deptId]['total_qty'] += $item->quantity;
            
            // تجميع الإجمالي العام
            if (!isset($grandTotals[$productId])) {
                $grandTotals[$productId] = 0;
            }
            $grandTotals[$productId] += $item->quantity;
        }
    }
    
    // تحويل المجموعة إلى مصفوفة وإضافة أرقام الصفوف
    $data = array_values($groupedData);
    foreach ($data as $index => &$row) {
        $row['DT_RowIndex'] = $index + 1;
        // ✅ إضافة تاريخ تجميعي (مثلاً نطاق التواريخ)
        if ($request->from_date || $request->to_date) {
            $fromDate = $request->from_date ?? 'البداية';
            $toDate = $request->to_date ?? 'النهاية';
            $row['order_date'] = "فترة: {$fromDate} إلى {$toDate}";
        } else {
            $row['order_date'] = 'جميع التواريخ';
        }
        
        // ✅ إضافة أزرار الإجراءات (إذا لزم الأمر)
        $row['action'] = '<button type="button" class="btn btn-info btn-sm view-group" data-department="' . $row['department_id'] . '">
                            <i class="fas fa-eye"></i> عرض التفاصيل
                          </button>';
    }
    
    // حساب الإجمالي العام الكلي
    $grandTotalQuantity = array_sum($grandTotals);
    
    // ✅ إرجاع البيانات بصيغة DataTables متوافقة
    return response()->json([
        'draw' => intval($request->draw),
        'recordsTotal' => count($data),
        'recordsFiltered' => count($data),
        'data' => $data,
        'totals' => [
            'product_totals' => $grandTotals,
            'total_quantity' => $grandTotalQuantity
        ]
    ]);
}

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */

public function store(StoreDepartmentAllocationRequest $request)
{
    try {
        DB::beginTransaction();

        // ========== 1. جلب الإدارة ومخزن التشغيل ==========
        $department = Department::with('operationWarehouse')
            ->findOrFail($request->department_id);
        $warehouseId = $department->operation_warehouse_id;

        if (!$warehouseId) {
            throw new \Exception(
                "الإدارة {$department->name} ليس لديها مخزن تشغيل مرتبط. " .
                "يرجى ضبط مخزن التشغيل أولاً من إعدادات الإدارة."
            );
        }

        // التحقق إن مخزن التشغيل نشط
        $warehouse = Warehouse::find($warehouseId);
        if (!$warehouse || !$warehouse->status) {
            throw new \Exception("مخزن التشغيل غير نشط أو غير موجود");
        }

        // ========== 2. التحقق من التوازن (الأساسي = التامة) ==========
        $baseQuantity   = 0;
        $otherTotal     = 0;
        $hasBaseProduct = false;
        $baseProductName = '';

        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);
            if (!$product) continue;

            if ($product->is_base) {
                $hasBaseProduct  = true;
                $baseQuantity    = (int) $item['quantity'];
                $baseProductName = $product->name;
            } else {
                $otherTotal += (int) $item['quantity'];
            }
        }

        if (!$hasBaseProduct) {
            throw new \Exception(
                'يجب إضافة الصنف الأساسي (سادة 40) إلى إذن الصرف'
            );
        }

        if ($baseQuantity !== $otherTotal) {
            $difference = abs($baseQuantity - $otherTotal);
            $message = "⚠️ عدم توازن: كمية الصنف الأساسي ($baseQuantity) " .
                       ($baseQuantity > $otherTotal ? "أكبر من" : "أقل من") .
                       " مجموع الأصناف التامة ($otherTotal) بفارق $difference";
            throw new \Exception($message);
        }

        // ========== 3. التحقق من الرصيد في مخزن التشغيل قبل البدء ==========
        $stockErrors = [];

        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);
            $stock = Inventory::where('warehouse_id', $warehouseId)
                ->where('product_id', $item['product_id'])
                ->first();

            $available = $stock ? $stock->quantity : 0;

            if ($available < $item['quantity']) {
                $stockErrors[] = "• {$product->name}: متاح {$available} / مطلوب {$item['quantity']}";
            }
        }

        if (!empty($stockErrors)) {
            throw new \Exception(
                "❌ الرصيد غير كافٍ في مخزن الإدارة ({$warehouse->name}):\n" .
                implode("\n", $stockErrors) .
                "\n\nيرجى تحويل الكميات المطلوبة من المخزن الرئيسي أولاً."
            );
        }

        // ========== 4. إنشاء إذن الصرف ==========
        $allocation = DepartmentAllocation::create([
            'receite_date'  => $request->order_date,
            'department_id' => $request->department_id,
            'created_by'    => auth()->id() ?? 1,
            'notes'         => $request->notes,
            'total_meals'   => 0,
        ]);

        $totalMealsSum = 0;

        // ========== 5. الصرف والخصم ==========
        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);
            $conversionFactor = $product->conversion_factor ?? 1;
            $totalMeals = $item['quantity'] * $conversionFactor;

            // ✅ خصم من مخزن التشغيل
            Inventory::where('warehouse_id', $warehouseId)
                ->where('product_id', $item['product_id'])
                ->decrement('quantity', $item['quantity']);

            // ✅ تحديث last_movement_at
            Inventory::where('warehouse_id', $warehouseId)
                ->where('product_id', $item['product_id'])
                ->update(['last_movement_at' => now()]);

            // تسجيل حركة المخزون
  InventoryTransaction::create([
    'product_id'       => $product->id,
    'warehouse_id'     => $warehouseId,
    'type'             => 'out',
    'reference_number' => 'DA-' . $allocation->id,   // 👈 مرجع الإذن
    'quantity'         => $item['quantity'],
    'user_id'          => auth()->id() ?? 1,
    'notes'            => "صرف لإدارة: {$department->name}",
]);

            // تفاصيل الإذن
            DepartmentAllocationItem::create([
                'allocation_id' => $allocation->id,
                'product_id'    => $item['product_id'],
                'quantity'      => $item['quantity'],
                'total_meals'   => $totalMeals,
            ]);

            $totalMealsSum += $totalMeals;
        }

        $allocation->update(['total_meals' => $totalMealsSum]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => "تم تسجيل إذن الصرف بنجاح. إجمالي الوجبات: {$totalMealsSum}",
            'data'    => $allocation->load('items.product')
        ], 201);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error storing department allocation: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}

private function deductFromInventory($productId, $quantity)
{
    // منطق خصم المخزون حسب نظامك
    // مثال:
    // $product = Product::find($productId);
    // $product->decrement('stock', $quantity);
}

    /**
     * Display the specified resource.
     */
    public function show(DepartmentAllocation $departmentAllocation)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DepartmentAllocation $departmentAllocation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDepartmentAllocationRequest $request, DepartmentAllocation $departmentAllocation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DepartmentAllocation $departmentAllocation)
    {
        //
    }
}
