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
        // ========== 1. التحقق الأساسي من الطلب ==========
        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'order_date'    => 'required|date',
            'items'         => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
        ]);

        $department = Department::with('operationWarehouse')->findOrFail($request->department_id);
        $warehouseId = $department->operation_warehouse_id;

        if (!$warehouseId) {
            throw new \Exception("الإدارة {$department->name} ليس لديها مخزن تشغيل مرتبط");
        }

        // ========== 2. التحقق من التوازن (الصنف الأساسي = مجموع التامة) ==========
        $baseQuantity  = 0;
        $otherTotal    = 0;
        $hasBaseProduct = false;
        $baseProductId  = null;

        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);
            if (!$product) continue;

            if ($product->is_base) {
                $hasBaseProduct = true;
                $baseProductId  = $product->id;
                $baseQuantity   = (int) $item['quantity'];
            } else {
                $otherTotal += (int) $item['quantity'];
            }
        }

        if (!$hasBaseProduct) {
            return response()->json([
                'success' => false,
                'message' => 'يجب إضافة الصنف الأساسي (سادة 40) إلى إذن الصرف'
            ], 422);
        }

        if ($baseQuantity !== $otherTotal) {
            $difference = abs($baseQuantity - $otherTotal);
            $message = "⚠️ عدم توازن: كمية الصنف الأساسي ($baseQuantity) " .
                       ($baseQuantity > $otherTotal ? "أكبر من" : "أقل من") .
                       " مجموع الأصناف التامة ($otherTotal) بفارق $difference";
            return response()->json([
                'success' => false,
                'message' => $message
            ], 422);
        }

        // ========== 3. التحقق من الرصيد الكافي قبل البدء (نفس سياسة التحويل) ==========
        foreach ($request->items as $item) {
            $sourceStock = Inventory::where('warehouse_id', $warehouseId)
                ->where('product_id', $item['product_id'])
                ->first();

            if (!$sourceStock || $sourceStock->quantity < $item['quantity']) {
                $product = Product::find($item['product_id']);
                throw new \Exception("الرصيد غير كافٍ للمنتج: {$product->name} في مخزن الإدارة");
            }
        }

        // ========== 4. تنفيذ العملية داخل Transaction ==========
        DB::beginTransaction();

        $allocation = DepartmentAllocation::create([
            'receite_date'   => $request->order_date,
            'department_id'  => $request->department_id,
            'created_by'     => auth()->id() ?? 1,
            'notes'          => $request->notes,
            'total_meals'    => 0,
        ]);

        $totalMealsSum = 0;

        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);
            $conversionFactor = $product->conversion_factor ?? 1;
            $totalMeals = $item['quantity'] * $conversionFactor;

            // الخصم من المخزون
            Inventory::where('warehouse_id', $warehouseId)
                ->where('product_id', $item['product_id'])
                ->decrement('quantity', $item['quantity']);

            // تسجيل حركة المخزون
            InventoryTransaction::create([
                'department_allocation_id' => $allocation->id,
                'product_id'   => $product->id,
                'warehouse_id' => $warehouseId,
                'type'         => 'out',
                'quantity'     => $item['quantity'],
                'total_meals'  => $totalMeals,
                'user_id'      => auth()->id() ?? 1,
                'notes'        => "صرف لإدارة: {$department->name}",
            ]);

            // تسجيل تفاصيل الإذن
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
            'message' => "تم تسجيل إذن الصرف وخصم المخزون بنجاح. إجمالي الوجبات: {$totalMealsSum}",
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
