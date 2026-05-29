<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\School;
use App\Models\Inventory;
use App\Models\DistributionOrder;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class DistributionOrderController extends Controller
{

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $orders = DistributionOrder::with(['user','school.department',  'details.product'])
                ->latest('created_at')
                ->select('distribution_orders.*');

            $dataTable = DataTables::of($orders)
                ->addColumn('DT_RowIndex', function ($order) {
                    static $index = 0;
                    $index++;
                    return $index;
                })
                ->addColumn('order_date', function ($order) {
                    return $order->receite_date ?? '---';
                })
                ->addColumn('school_name', function ($order) {
                    return $order->school->name ?? '---';
                })
                ->addColumn('department_name', function ($order) {
                    return $order->school->department->name ?? '---';
                })
                ->addColumn('total_qty', function ($order) {
                    return $order->details->sum('quantity');
                })
                ->addColumn('user_name', function ($order) {
                    return $order->user->name ?? '---';
                })
                ->addColumn('action', function ($order) {
                    return '<button class="btn btn-sm btn-info view-order" data-id="' . $order->id . '">عرض</button>';
                });

            // ✅✅✅ أضف الأعمدة الديناميكية للمنتجات ✅✅✅
            $allProducts = \App\Models\Product::orderBy('name')->get();

            foreach ($allProducts as $product) {
                $dataTable->addColumn('prod_' . $product->id, function ($order) use ($product) {
                    $detail = $order->details->where('product_id', $product->id)->first();
                    return $detail ? $detail->quantity : 0;
                });
            }

            return $dataTable->rawColumns(['action'])->make(true);
        }

        // للـ view العادي
        $products = Product::orderBy('name')->get();
        $schools = School::with('department')->get();
        $departments = Department::all();

        return view('backend.distributions.index', compact('products', 'schools', 'departments'));
    }
    // البيانات المطلوبة للـ Modals (لإضافة صرف جديد)


    public function getSchoolsByDepartment(Request $request)
    {
        $departmentId = $request->department_id;

        $schools = School::where('department_id', $departmentId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
            'success' => true,
            'schools' => $schools
        ]);
    }
public function store(Request $request)
{

    try {
        DB::transaction(function () use ($request) {
            // ✅✅✅ جلب الإدارة ومخزن السحب الخاص بها ✅✅✅
            $department = \App\Models\Department::with('operationWarehouse')->findOrFail($request->department_id);
            $warehouseId = $department->operation_warehouse_id; // مخزن السحب التابع للإدارة
            
            Log::info('الإدارة:', ['id' => $department->id, 'name' => $department->name]);
            Log::info('warehouse_id من الإدارة: ' . $warehouseId);

            // جلب المدرسة
            $school = School::with('department')->findOrFail($request->school_id);
            
            // الأصناف
            $items = $request->items;

            if (empty($items)) {
                throw new \Exception("لا توجد أصناف في طلب الصرف");
            }

            // إنشاء أمر الصرف
            $order = DistributionOrder::create([
                'school_id' => $request->school_id,
                'warehouse_id' => $warehouseId, // ✅ من الإدارة
                'delivery_agent' => $request->delivery_agent,
                'car_number' => $request->car_number ?? null,
                'notes' => $request->notes,
                'created_by' => 1,
                'receite_date' => $request->order_date ?? now()->toDateString(),
            ]);

            // معالجة المنتجات
            foreach ($items as $item) {
                $quantity = (int) $item['quantity'];

                if ($quantity > 0) {
                    // تفاصيل الصرف
                    $order->details()->create([
                        'product_id' => $item['product_id'],
                        'quantity' => $quantity,
                    ]);

                    // الخصم من المخزون في مخزن الإدارة
                    $inventory = Inventory::where('warehouse_id', $warehouseId)
                        ->where('product_id', $item['product_id'])
                        ->first();

                    if (!$inventory) {
                        throw new \Exception("المنتج غير موجود في مخزن الإدارة");
                    }

                    if ($inventory->quantity < $quantity) {
                        throw new \Exception("الكمية المطلوبة للمنتج أكبر من المتوفر في المخزن. المتوفر: {$inventory->quantity}");
                    }

                    $inventory->decrement('quantity', $quantity);

                    // تسجيل حركة المخزون
                    InventoryTransaction::create([
                        'distribution_order_id' => $order->id,
                        'product_id' => $item['product_id'],
                        'warehouse_id' => $warehouseId,
                        'type' => 'out',
                        'quantity' => $quantity,
                        'user_id' =>1,
                        'notes' => "صرف لمدرسة: {$school->name} - إدارة: {$department->name}",
                        'reference_number' => $order->receite_number ?? null,
                    ]);
                }
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل أمر الصرف وخصم الكميات بنجاح'
        ]);
        
    } catch (\Exception $e) {
        Log::error('=== خطأ ===');
        Log::error($e->getMessage());

        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}
}
