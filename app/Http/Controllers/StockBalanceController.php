<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class StockBalanceController extends Controller
{
    /**
     * عرض صفحة أرصدة المخزون
     */
    /**
 * ✅ حساب الرصيد السابق لمخزن معين (كل الأصناف الأساسية)
 */
private function calculateWarehouseOpeningBalance($warehouseId, $productIds, $beforeDate)
{
    $in = InventoryTransaction::where('warehouse_id', $warehouseId)
        ->whereIn('product_id', $productIds)
        ->whereIn('type', ['in', 'transfer_in'])
        ->where('created_at', '<', $beforeDate)
        ->sum('quantity');

    $out = InventoryTransaction::where('warehouse_id', $warehouseId)
        ->whereIn('product_id', $productIds)
        ->whereIn('type', ['out', 'transfer_out'])
        ->where('created_at', '<', $beforeDate)
        ->sum('quantity');

    return max(0, $in - $out);
}

/**
 * ✅ حساب المنصرف لمخزن معين في الفترة (كل الأصناف الأساسية)
 */
private function calculateWarehouseTotalOut($warehouseId, $productIds, $fromDate, $toDate)
{
    return InventoryTransaction::where('warehouse_id', $warehouseId)
        ->whereIn('product_id', $productIds)
        ->whereIn('type', ['out', 'transfer_out'])
        ->whereBetween('created_at', [$fromDate, $toDate])
        ->sum('quantity');
}
public function index(Request $request)
{
    if ($request->ajax()) {
        // ✅ التواريخ
        $openingDate = $request->filled('opening_date') 
            ? Carbon::parse($request->opening_date)->startOfDay() 
            : Carbon::now()->startOfMonth();

        $fromDate = $request->filled('from_date') 
            ? Carbon::parse($request->from_date)->startOfDay() 
            : Carbon::now()->startOfMonth();

        $toDate = $request->filled('to_date') 
            ? Carbon::parse($request->to_date)->endOfDay() 
            : Carbon::now()->endOfDay();

        // ✅ الصنف الأساسي
        $baseProductIds = Product::where('is_base', true)
            ->where('status', true)
            ->pluck('id')
            ->toArray();

        if (empty($baseProductIds)) {
            $baseProductIds = Product::where('status', true)->pluck('id')->toArray();
        }

        // ✅ جلب المخازن الرئيسية
        $mainWarehouses = Warehouse::where('type', 'main')
            ->where('status', 1)
            ->when($request->filled('warehouse_id'), function ($q) use ($request) {
                $q->where('id', $request->warehouse_id);
            })
            ->orderBy('name')
            ->get();

        $data = [];
        $totals = [
            'opening_balance' => 0,
            'total_out' => 0,
            'current_balance' => 0,
        ];

        foreach ($mainWarehouses as $mainWarehouse) {
            // ============================================================
            // 1️⃣ صف المخزن الرئيسي (يشمل نقاط التوزيع التابعة له)
            // ============================================================
            
            // ✅ مخازن الحساب: الرئيسي + نقاط التوزيع التابعة له مباشرة
            $mainAndDispatchIds = [$mainWarehouse->id];
            
            $dispatchPointIds = Warehouse::where('parent_id', $mainWarehouse->id)
                ->where('type', 'dispatch_point')
                ->where('status', 1)
                ->pluck('id')
                ->toArray();
            
            $mainAndDispatchIds = array_merge($mainAndDispatchIds, $dispatchPointIds);

            // ✅ الرصيد السابق للرئيسي + نقاط التوزيع
            $openingInMain = InventoryTransaction::whereIn('warehouse_id', $mainAndDispatchIds)
                ->whereIn('product_id', $baseProductIds)
                ->whereIn('type', ['in', 'transfer_in'])
                ->where('created_at', '<', $openingDate)
                ->sum('quantity');

            $openingOutMain = InventoryTransaction::whereIn('warehouse_id', $mainAndDispatchIds)
                ->whereIn('product_id', $baseProductIds)
                ->whereIn('type', ['out', 'transfer_out'])
                ->where('created_at', '<', $openingDate)
                ->sum('quantity');

            $openingBalanceMain = max(0, $openingInMain - $openingOutMain);

            // ✅ المنصرف للرئيسي + نقاط التوزيع
            $totalOutMain = InventoryTransaction::whereIn('warehouse_id', $mainAndDispatchIds)
                ->whereIn('product_id', $baseProductIds)
                ->whereIn('type', ['out', 'transfer_out'])
                ->whereBetween('created_at', [$fromDate, $toDate])
                ->sum('quantity');

            // ✅ الرصيد الحالي للرئيسي + نقاط التوزيع
            $currentBalanceMain = Inventory::whereIn('warehouse_id', $mainAndDispatchIds)
                ->whereIn('product_id', $baseProductIds)
                ->sum('quantity');

            // ✅ إضافة صف المخزن الرئيسي
            $data[] = [
                'id' => $mainWarehouse->id,
                'warehouse_name' => $mainWarehouse->name,
                'warehouse_code' => $mainWarehouse->code,
                'warehouse_type' => 'main',
                'warehouse_type_badge' => '<span class="badge badge-primary">رئيسي</span>',
                'dispatch_points_count' => count($dispatchPointIds),
                'opening_balance' => $openingBalanceMain,
                'total_out' => $totalOutMain,
                'current_balance' => $currentBalanceMain,
            ];

            $totals['opening_balance'] += $openingBalanceMain;
            $totals['total_out'] += $totalOutMain;
            $totals['current_balance'] += $currentBalanceMain;

            // ============================================================
            // 2️⃣ صف لكل فرع (sub) تابع للمخزن الرئيسي
            // ============================================================
            
            $subWarehouses = Warehouse::where('parent_id', $mainWarehouse->id)
                ->where('type', 'sub')
                ->where('status', 1)
                ->orderBy('name')
                ->get();

            foreach ($subWarehouses as $subWarehouse) {
                // ✅ مخازن الحساب: الفرع + نقاط التوزيع التابعة له
                $subAndDispatchIds = [$subWarehouse->id];
                
                $subDispatchPointIds = Warehouse::where('parent_id', $subWarehouse->id)
                    ->where('type', 'dispatch_point')
                    ->where('status', 1)
                    ->pluck('id')
                    ->toArray();
                
                $subAndDispatchIds = array_merge($subAndDispatchIds, $subDispatchPointIds);

                // ✅ الرصيد السابق للفرع
                $openingInSub = InventoryTransaction::whereIn('warehouse_id', $subAndDispatchIds)
                    ->whereIn('product_id', $baseProductIds)
                    ->whereIn('type', ['in', 'transfer_in'])
                    ->where('created_at', '<', $openingDate)
                    ->sum('quantity');

                $openingOutSub = InventoryTransaction::whereIn('warehouse_id', $subAndDispatchIds)
                    ->whereIn('product_id', $baseProductIds)
                    ->whereIn('type', ['out', 'transfer_out'])
                    ->where('created_at', '<', $openingDate)
                    ->sum('quantity');

                $openingBalanceSub = max(0, $openingInSub - $openingOutSub);

                // ✅ المنصرف للفرع
                $totalOutSub = InventoryTransaction::whereIn('warehouse_id', $subAndDispatchIds)
                    ->whereIn('product_id', $baseProductIds)
                    ->whereIn('type', ['out', 'transfer_out'])
                    ->whereBetween('created_at', [$fromDate, $toDate])
                    ->sum('quantity');

                // ✅ الرصيد الحالي للفرع
                $currentBalanceSub = Inventory::whereIn('warehouse_id', $subAndDispatchIds)
                    ->whereIn('product_id', $baseProductIds)
                    ->sum('quantity');

                // ✅ إضافة صف الفرع
                $data[] = [
                    'id' => $subWarehouse->id,
                    'warehouse_name' => $subWarehouse->name,
                    'warehouse_code' => $subWarehouse->code,
                    'warehouse_type' => 'sub',
                    'warehouse_type_badge' => '<span class="badge badge-info">فرعي</span>',
                    'dispatch_points_count' => count($subDispatchPointIds),
                    'opening_balance' => $openingBalanceSub,
                    'total_out' => $totalOutSub,
                    'current_balance' => $currentBalanceSub,
                ];

                $totals['opening_balance'] += $openingBalanceSub;
                $totals['total_out'] += $totalOutSub;
                $totals['current_balance'] += $currentBalanceSub;
            }
        }

        return response()->json([
            'data' => $data,
            'totals' => $totals,
        ]);
    }

    $mainWarehouses = Warehouse::where('type', 'main')
        ->where('status', 1)
        ->orderBy('name')
        ->get();

    return view('backend.stock_balances.index', compact('mainWarehouses'));
}
    /**
     * حساب الرصيد السابق (قبل تاريخ معين)
     */
    private function calculateOpeningBalance($productId, $warehouseId, $beforeDate)
    {
        $in = InventoryTransaction::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->whereIn('type', ['in', 'transfer_in'])
            ->where('created_at', '<', $beforeDate)
            ->sum('quantity');

        $out = InventoryTransaction::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->whereIn('type', ['out', 'transfer_out'])
            ->where('created_at', '<', $beforeDate)
            ->sum('quantity');

        return max(0, $in - $out);
    }

    /**
     * حساب مجموع الوارد في الفترة
     */
    private function calculateTotalIn($productId, $warehouseId, $fromDate, $toDate)
    {
        return InventoryTransaction::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->whereIn('type', ['in', 'transfer_in'])
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->sum('quantity');
    }

    /**
     * حساب مجموع المنصرف في الفترة
     */
    private function calculateTotalOut($productId, $warehouseId, $fromDate, $toDate)
    {
        return InventoryTransaction::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->whereIn('type', ['out', 'transfer_out'])
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->sum('quantity');
    }

    /**
     * تقرير مفصّل لمنتج معين
     */
    public function details(Request $request, $productId, $warehouseId)
    {
        $fromDate = $request->filled('from_date')
            ? Carbon::parse($request->from_date)->startOfDay()
            : Carbon::now()->startOfMonth();

        $toDate = $request->filled('to_date')
            ? Carbon::parse($request->to_date)->endOfDay()
            : Carbon::now()->endOfDay();

        $transactions = InventoryTransaction::with(['product', 'warehouse', 'user'])
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $transactions,
        ]);
    }

    /**
     * ✅ تحديد المخازن (الرئيسي + الفروع)
     */
    /**
     * ✅ تحديد المخازن (الرئيسي + الفروع فقط)
     * ملاحظة: نقاط التوزيع (dispatch_point) لا تُحتسب كمخازن
     */
    private function getWarehouseIds($request)
    {
        // إذا لم يتم تحديد مخزن، جلب كل المخازن النشطة (رئيسية + فروع)
        if (!$request->filled('warehouse_id')) {
            return Warehouse::where('status', 1)
                ->whereIn('type', ['main', 'sub'])  // ✅ استثناء dispatch_point
                ->pluck('id')
                ->toArray();
        }

        $mainWarehouseId = $request->warehouse_id;
        $mainWarehouse = Warehouse::find($mainWarehouseId);

        if (!$mainWarehouse) {
            return [];
        }

        // ✅ إذا كان المخزن المختار رئيسياً
        if ($mainWarehouse->type === 'main') {
            // جلب المخزن الرئيسي
            $warehouseIds = [$mainWarehouseId];

            // ✅ جلب الفروع فقط (sub) - استثناء dispatch_point
            $directChildren = Warehouse::where('parent_id', $mainWarehouseId)
                ->where('type', 'sub')  // ✅ فقط الفروع
                ->where('status', 1)
                ->pluck('id')
                ->toArray();

            $warehouseIds = array_merge($warehouseIds, $directChildren);

            // ✅ جلب الفروع المتداخلة (فروع لفروع) - إذا وُجدت
            foreach ($directChildren as $childId) {
                $subChildren = Warehouse::where('parent_id', $childId)
                    ->where('type', 'sub')  // ✅ فقط الفروع
                    ->where('status', 1)
                    ->pluck('id')
                    ->toArray();
                $warehouseIds = array_merge($warehouseIds, $subChildren);
            }

            return array_unique($warehouseIds);
        }

        // ✅ إذا كان المخزن المختار فرعياً (sub)
        if ($mainWarehouse->type === 'sub' && $mainWarehouse->parent_id) {
            $parentId = $mainWarehouse->parent_id;

            $warehouseIds = [$parentId];

            // جلب كل الفروع (الإخوة) - استثناء dispatch_point
            $siblings = Warehouse::where('parent_id', $parentId)
                ->where('type', 'sub')  // ✅ فقط الفروع
                ->where('status', 1)
                ->pluck('id')
                ->toArray();

            return array_unique(array_merge($warehouseIds, $siblings));
        }

        // ✅ إذا كان نقطة توزيع (dispatch_point) - نادراً ما يحدث
        if ($mainWarehouse->type === 'dispatch_point' && $mainWarehouse->parent_id) {
            // أرجع المخزن الرئيسي الأب + فروعه فقط
            $parentId = $mainWarehouse->parent_id;

            $warehouseIds = [$parentId];

            $subs = Warehouse::where('parent_id', $parentId)
                ->where('type', 'sub')
                ->where('status', 1)
                ->pluck('id')
                ->toArray();

            return array_unique(array_merge($warehouseIds, $subs));
        }

        return [$mainWarehouseId];
    }
    private function getAllRelatedWarehouseIds($mainWarehouseId)
{
    // ✅ 1. المخزن الرئيسي
    $ids = [$mainWarehouseId];

    // ✅ 2. الفروع (sub)
    $subIds = Warehouse::where('parent_id', $mainWarehouseId)
        ->where('type', 'sub')
        ->where('status', 1)
        ->pluck('id')
        ->toArray();

    $ids = array_merge($ids, $subIds);

    // ✅ 3. نقاط التوزيع (dispatch_point)
    $dispatchPointIds = Warehouse::where('parent_id', $mainWarehouseId)
        ->where('type', 'dispatch_point')
        ->where('status', 1)
        ->pluck('id')
        ->toArray();

    $ids = array_merge($ids, $dispatchPointIds);

    // ✅ 4. الفروع المتداخلة (sub of sub) - إن وجدت
    foreach ($subIds as $subId) {
        $nestedSubIds = Warehouse::where('parent_id', $subId)
            ->where('type', 'sub')
            ->where('status', 1)
            ->pluck('id')
            ->toArray();
        $ids = array_merge($ids, $nestedSubIds);
    }

    // ✅ 5. نقاط التوزيع التابعة للفروع
    foreach ($subIds as $subId) {
        $subDispatchPoints = Warehouse::where('parent_id', $subId)
            ->where('type', 'dispatch_point')
            ->where('status', 1)
            ->pluck('id')
            ->toArray();
        $ids = array_merge($ids, $subDispatchPoints);
    }

    return array_unique($ids);
}
}
