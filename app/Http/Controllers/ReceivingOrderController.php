<?php

namespace App\Http\Controllers;

use App\Models\ReceivingOrder;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ReceivingOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */

public function index(Request $request)
{
    if ($request->ajax()) {
        $data = ReceivingOrder::select(
                'receiving_orders.id',
                'receiving_orders.document_number',
                'receiving_orders.batch_number',
                'receiving_orders.quantity',
                'receiving_orders.samples_quantity',
                'receiving_orders.arrival_time',
                'receiving_orders.departure_time',
                'receiving_orders.notes',
                'receiving_orders.supplier_id',
                'receiving_orders.warehouse_id',
                'receiving_orders.product_id',
                'suppliers.name as supplier_name',
                'warehouses.name as warehouse_name',
                'products.name as product_name'
            )
            ->leftJoin('suppliers', 'receiving_orders.supplier_id', '=', 'suppliers.id')
            ->leftJoin('warehouses', 'receiving_orders.warehouse_id', '=', 'warehouses.id')
            ->leftJoin('products', 'receiving_orders.product_id', '=', 'products.id');
        
        // ✅ تصفية حسب المورد إذا تم إرسال supplier_id
        if ($request->has('supplier_id') && $request->supplier_id != '') {
            $data->where('receiving_orders.supplier_id', $request->supplier_id);
        }
        
        return DataTables::of($data)
            ->addIndexColumn()
            // ✅ تحديد أسماء الأعمدة بشكل صحيح لـ DataTables
            ->filterColumn('supplier_name', function($query, $keyword) {
                $query->where('suppliers.name', 'like', "%{$keyword}%");
            })
            ->filterColumn('warehouse_name', function($query, $keyword) {
                $query->where('warehouses.name', 'like', "%{$keyword}%");
            })
            ->filterColumn('product_name', function($query, $keyword) {
                $query->where('products.name', 'like', "%{$keyword}%");
            })
            ->editColumn('batch_number', function($row) {
                return $row->batch_number ? '<span class="badge badge-secondary">'.$row->batch_number.'</span>' : '-';
            })
            ->editColumn('supplier_name', function($row) {
                return $row->supplier_name ?? '<span class="text-danger">غير محدد</span>';
            })
            ->editColumn('product_name', function($row) {
                return $row->product_name ?? '<span class="text-danger">غير محدد</span>';
            })
            ->editColumn('warehouse_name', function($row) {
                return $row->warehouse_name ?? '<span class="text-danger">غير محدد</span>';
            })
            ->editColumn('arrival_time', function($row) {
                return $row->arrival_time ? \Carbon\Carbon::parse($row->arrival_time)->format('Y-m-d H:i') : '-';
            })
            ->editColumn('departure_time', function($row) {
                return $row->departure_time ? \Carbon\Carbon::parse($row->departure_time)->format('Y-m-d H:i') : '-';
            })
            ->addColumn('action', function ($row) {
                return '
                    <div class="btn-group" role="group">
                        <button class="btn btn-sm btn-primary edit-receiving-order" data-id="' . $row->id . '" title="تعديل"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-sm btn-info view-report-copy" data-id="' . $row->id . '" title="نسخ التقرير"><i class="fas fa-copy"></i></button>
                        <button class="btn btn-sm btn-danger delete-receiving-order" data-id="' . $row->id . '" data-number="' . $row->document_number . '" title="حذف"><i class="fas fa-trash"></i></button>
                    </div>';
            })
            ->rawColumns(['batch_number', 'action', 'supplier_name', 'product_name', 'warehouse_name'])
            ->make(true);
    }
    
    // باقي الكود...

    
    // باقي الكود لتحميل الصفحة الرئيسية
    $warehouses = \App\Models\Warehouse::where('status', 1)->where('type', '!=', 'dispatch_point')->get();
    $products   = \App\Models\Product::all();
    $suppliers  = \App\Models\Supplier::where('status', 1)->get();
    
    $shipmentsStats = $this->getTotalShipmentsStatistics();
    $supplierData = $this->getSupplierSummary();
    
    return view('backend.receiving_orders.index', compact(
        'warehouses', 
        'products', 
        'suppliers', 
        'shipmentsStats',
        'supplierData'
    ));
}
    


/**
 * Get detailed summary statistics grouped by supplier and samples distribution
 */

/**
 * Get supplier summary with shipments and quantities
 */
private function getSupplierSummary()
{
    // 1️⃣ ملخص كل مورد (إجمالي الكرتونات + عدد الشحنات)
    $supplierSummary = DB::table('receiving_orders as ro')
        ->join('suppliers as s', 'ro.supplier_id', '=', 's.id')
        ->select(
            's.id as supplier_id',
            's.name as supplier_name',
            DB::raw('SUM(ro.quantity) as total_quantity'),
            DB::raw('COUNT(ro.id) as shipment_count')
        )
        ->groupBy('s.id', 's.name')
        ->orderBy('total_quantity', 'DESC')
        ->get()
        ->map(function ($item) {
            return [
                'supplier_id' => $item->supplier_id,
                'supplier_name' => $item->supplier_name,
                'total_quantity' => (int) $item->total_quantity,
                'shipment_count' => (int) $item->shipment_count,
                'avg_per_shipment' => $item->shipment_count > 0 
                    ? round($item->total_quantity / $item->shipment_count, 1) 
                    : 0,
            ];
        });

    // 2️⃣ توزيع العينات حسب المخازن
    $samplesDistribution = DB::table('receiving_orders as ro')
        ->join('warehouses as w', 'ro.warehouse_id', '=', 'w.id')
        ->select(
            'w.id as warehouse_id',
            'w.name as warehouse_name',
            DB::raw('SUM(ro.samples_quantity) as samples_count')
        )
        ->where('ro.samples_quantity', '>', 0)
        ->groupBy('w.id', 'w.name')
        ->orderBy('samples_count', 'DESC')
        ->get()
        ->map(function ($item) {
            return [
                'warehouse_name' => $item->warehouse_name,
                'samples_count' => (int) $item->samples_count,
            ];
        });

    // 3️⃣ الإجماليات العامة
    $totalSummary = [
        'grand_total_quantity' => ReceivingOrder::sum('quantity'),
        'total_shipments' => ReceivingOrder::count(),
        'total_samples' => ReceivingOrder::sum('samples_quantity'),
    ];

    return [
        'supplierSummary' => $supplierSummary,
        'samplesDistribution' => $samplesDistribution,
        'totalSummary' => $totalSummary,
    ];
}
private function getDetailedSummary()
{
    // 1️⃣ إجمالي الكرتونات وعدد الشحنات لكل مورد
    $supplierSummary = DB::table('receiving_orders as ro')
        ->join('suppliers as s', 'ro.supplier_id', '=', 's.id')
        ->select(
            's.id as supplier_id',
            's.name as supplier_name',
            DB::raw('SUM(ro.quantity) as total_quantity'),
            DB::raw('COUNT(ro.id) as shipment_count')
        )
        ->groupBy('s.id', 's.name')
        ->orderBy('total_quantity', 'DESC')
        ->get()
        ->map(function ($item) {
            return [
                'supplier_name' => $item->supplier_name,
                'total_quantity' => (int) $item->total_quantity,
                'shipment_count' => (int) $item->shipment_count,
            ];
        });

    // 2️⃣ توزيع العينات حسب المخازن
    $samplesDistribution = DB::table('receiving_orders as ro')
        ->join('warehouses as w', 'ro.warehouse_id', '=', 'w.id')
        ->select(
            'w.id as warehouse_id',
            'w.name as warehouse_name',
            DB::raw('SUM(ro.samples_quantity) as samples_count')
        )
        ->where('ro.samples_quantity', '>', 0)
        ->groupBy('w.id', 'w.name')
        ->orderBy('samples_count', 'DESC')
        ->get()
        ->map(function ($item) {
            return [
                'warehouse_name' => $item->warehouse_name,
                'samples_count' => (int) $item->samples_count,
            ];
        });

    // 3️⃣ الإجمالي العام للعينات
    $totalSamples = ReceivingOrder::sum('samples_quantity');

    return [
        'supplierSummary' => $supplierSummary,
        'samplesDistribution' => $samplesDistribution,
        'totalSamples' => $totalSamples,
    ];
}
/**
 * Get total shipments statistics (grouped by warehouse and product)
 * عرض إجمالي الشحنات المستلمة لكل مخزن وكل منتج
 */
/**
 * Get total shipments statistics (grouped by warehouse and product)
 * عرض إجمالي الشحنات المستلمة لكل مخزن وكل منتج
 * ✅ عرض جميع المخازن وجميع المنتجات حتى لو بدون شحنات
 */
private function getTotalShipmentsStatistics()
{
    // جلب جميع المخازن النشطة
    $warehouses = \App\Models\Warehouse::where('status', 1)
        ->where('type', '!=', 'dispatch_point')
        ->orderBy('name')
        ->get();
    
    // جلب جميع المنتجات النشطة
    $products = \App\Models\Product::orderBy('name')->get();
    
    // جلب إجمالي الشحنات لكل مخزن وكل منتج
    $shipmentsData = DB::table('receiving_orders as ro')
        ->select(
            'ro.warehouse_id',
            'ro.product_id',
            DB::raw('SUM(ro.quantity) as total_quantity')
        )
        ->groupBy('ro.warehouse_id', 'ro.product_id')
        ->get()
        ->keyBy(function ($item) {
            return $item->warehouse_id . '_' . $item->product_id;
        });
    
    // بناء المصفوفة الكاملة (جميع المخازن × جميع المنتجات)
    $matrix = [];
    $warehouseNames = [];
    $productDetails = [];
    
    foreach ($warehouses as $warehouse) {
        $warehouseNames[$warehouse->id] = $warehouse->name;
        
        foreach ($products as $product) {
            $key = $warehouse->id . '_' . $product->id;
            $quantity = isset($shipmentsData[$key]) ? (int) $shipmentsData[$key]->total_quantity : 0;
            
            $matrix[$warehouse->id][$product->id] = $quantity;
            
            // تخزين تفاصيل المنتج
            if (!isset($productDetails[$product->id])) {
                $productDetails[$product->id] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                ];
            }
        }
    }
    
    return [
        'warehouses' => $warehouses,      // Collection كاملة للمخازن
        'products' => $productDetails,    // تفاصيل المنتجات
        'matrix' => $matrix,              // المصفوفة [مخزن][منتج] = الكمية
    ];
}
/**
 * Get inventory statistics grouped by product and warehouse
 */
private function getInventoryStatistics()
{
    $inventoryStats = DB::table('inventories as i')
        ->join('products as p', 'i.product_id', '=', 'p.id')
        ->join('warehouses as w', 'i.warehouse_id', '=', 'w.id')
        ->select(
            'p.id as product_id',
            'p.name as product_name',
            'p.sku as product_code',  // ✅ استخدام SKU بدلاً من code
            'w.id as warehouse_id',
            'w.name as warehouse_name',
            'i.quantity',
            'i.last_movement_at'
        )
        ->orderBy('p.name')
        ->orderBy('w.name')
        ->get();

    // تنسيق البيانات بشكل مناسب للـ view
    $formattedStats = $inventoryStats->map(function ($item) {
        return [
            'product_name' => $item->product_name,
            'product_code' => $item->product_code ?? '-',
            'warehouse_name' => $item->warehouse_name,
            'quantity' => (int) $item->quantity,
            'last_movement' => $item->last_movement_at 
                ? \Carbon\Carbon::parse($item->last_movement_at)->format('Y-m-d H:i')
                : '-',
        ];
    });

    return $formattedStats;
}

/**
 * Get summary statistics for dashboard cards
 */
private function getSummaryStatistics()
{
    // إجمالي العينات (مجموع samples_quantity من جميع الشحنات)
    $totalSamples = ReceivingOrder::sum('samples_quantity');
    
    // إجمالي الشحنات (عدد السجلات في جدول receiving_orders)
    $grandTotalShipments = ReceivingOrder::count();
    
    // إجمالي كل شحنة من كل صنف (متوسط الكمية لكل شحنة × إجمالي الشحنات)
    // أو يمكنك حسابه كمجموع الكميات لجميع الشحنات
    $totalPerProduct = ReceivingOrder::sum('quantity');
    
    return [
        'totalSamples' => $totalSamples,
        'grandTotalShipments' => $grandTotalShipments,
        'totalPerProduct' => $totalPerProduct,
    ];
}
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'document_number'  => 'required|unique:receiving_orders,document_number',
            'supplier_id'      => 'required|exists:suppliers,id',
            'warehouse_id'     => 'required|exists:warehouses,id',
            'product_id'       => 'required|exists:products,id',
            'batch_number'     => 'nullable|string|max:255',
            'quantity'         => 'required|integer|min:1',
            'samples_quantity' => 'nullable|integer|min:0',
            'arrival_time'     => 'nullable|date',
            'departure_time'   => 'nullable|date',
            'notes'            => 'nullable|string',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                $userId = 1;

                // 1. إنشاء أمر الاستلام
                $order = ReceivingOrder::create([
                    'document_number'  => $request->document_number,
                    'batch_number'     => $request->batch_number,
                    'warehouse_id'     => $request->warehouse_id,
                    'product_id'       => $request->product_id,
                    'supplier_id'      => $request->supplier_id,
                    'quantity'         => $request->quantity,
                    'samples_quantity' => $request->samples_quantity ?? 0,
                    'arrival_time'     => $request->arrival_time,
                    'departure_time'   => $request->departure_time,
                    'notes'            => $request->notes,
                    'user_id'          => 1,
                ]);

                // 2. تحديث المخزون (بدون receiving_order_id)
                $this->updateInventory($order, $request->quantity, $userId);

                return response()->json([
                    'success' => true,
                    'message' => "تم تسجيل الشحنة بنجاح"
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشلت العملية: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update inventory and record transaction
     */
private function updateInventory($order, $quantity, $userId)
{
    $productId = $order->product_id;
    $warehouseId = $order->warehouse_id;
    $currentTime = now();

    // ✅ الحصول على المخزون الحالي أو إنشاؤه
    $inventory = Inventory::firstOrCreate(
        ['product_id' => $productId, 'warehouse_id' => $warehouseId],
        ['quantity' => 0, 'last_movement_at' => $currentTime]
    );

    $oldQuantity = $inventory->quantity;
    $newQuantity = $oldQuantity + $quantity; // ✅ إضافة الكمية الجديدة

    // ✅ تحديث المخزون
    $inventory->update([
        'quantity' => $newQuantity,
        'last_movement_at' => $currentTime
    ]);

    // ✅ تسجيل حركة الدخول
    InventoryTransaction::create([
        'product_id'       => $productId,
        'warehouse_id'     => $warehouseId,
        'type'             => 'in',
        'reference_number' => $order->document_number,
        'quantity'         => $quantity,
        'notes'            => "استلام شحنة - {$order->document_number} (كانت: {$oldQuantity}، أصبحت: {$newQuantity})",
        'user_id'          => $userId,
    ]);
}

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $order = ReceivingOrder::findOrFail($id);

            return response()->json([
                'success' => true,
                'id' => $order->id,
                'document_number' => $order->document_number,
                'supplier_id' => $order->supplier_id,
                'warehouse_id' => $order->warehouse_id,
                'product_id' => $order->product_id,
                'batch_number' => $order->batch_number,
                'quantity' => $order->quantity,
                'samples_quantity' => $order->samples_quantity,
                'arrival_time' => $order->arrival_time ? $order->arrival_time->format('Y-m-d\TH:i') : null,
                'departure_time' => $order->departure_time ? $order->departure_time->format('Y-m-d\TH:i') : null,
                'notes' => $order->notes,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'لم يتم العثور على الشحنة'
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'document_number'  => 'required|unique:receiving_orders,document_number,' . $id,
            'supplier_id'      => 'required|exists:suppliers,id',
            'warehouse_id'     => 'required|exists:warehouses,id',
            'product_id'       => 'required|exists:products,id',
            'batch_number'     => 'nullable|string|max:255',
            'quantity'         => 'required|integer|min:1',
            'samples_quantity' => 'nullable|integer|min:0',
            'arrival_time'     => 'nullable|date',
            'departure_time'   => 'nullable|date',
            'notes'            => 'nullable|string',
        ]);

        try {
            return DB::transaction(function () use ($request, $id) {
                $order = ReceivingOrder::findOrFail($id);
                $userId = 1;

                $oldQuantity = $order->quantity;
                $newQuantity = $request->quantity;
                $quantityDiff = $newQuantity - $oldQuantity;

                // تحديث بيانات الشحنة
                $order->update($request->only([
                    'document_number', 'batch_number', 'warehouse_id', 'product_id',
                    'supplier_id', 'quantity', 'samples_quantity', 'arrival_time',
                    'departure_time', 'notes'
                ]));

                // إذا تغيرت الكمية، قم بتحديث المخزون
                if ($quantityDiff != 0) {
                    $inventory = Inventory::where('product_id', $order->product_id)
                        ->where('warehouse_id', $order->warehouse_id)
                        ->first();

                    if ($inventory) {
                        $oldInventoryQty = $inventory->quantity;
                        $newInventoryQty = $inventory->quantity + $quantityDiff;

                        $inventory->update([
                            'quantity' => $newInventoryQty,
                            'last_movement_at' => now()
                        ]);

                        // ✅ تسجيل حركة تعديل المخزون (بدون receiving_order_id)
                        InventoryTransaction::create([
                            'product_id'       => $order->product_id,
                            'warehouse_id'     => $order->warehouse_id,
                            'type'             => $quantityDiff > 0 ? 'in' : 'out',
                            'reference_number' => $order->document_number,
                            'quantity'         => abs($quantityDiff),
                            'notes'            => "تعديل كمية الشحنة (من {$oldQuantity} إلى {$newQuantity})",
                            'user_id'          => $userId,
                        ]);
                    }
                }

                return response()->json([
                    'success' => true,
                    'message' => 'تم تحديث الشحنة بنجاح'
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
/**
 * Remove the specified resource from storage.
 */
public function destroy($id)
{
    try {
        DB::transaction(function () use ($id) {
            $order = ReceivingOrder::findOrFail($id);
            
            // ✅ الحصول على حركات المخزون المرتبطة بهذه الشحنة
            $transactions = InventoryTransaction::where('reference_number', $order->document_number)->get();
            
            foreach ($transactions as $transaction) {
                // ✅ استعادة المخزون إلى حالته قبل الحركة
                $inventory = Inventory::where('product_id', $transaction->product_id)
                    ->where('warehouse_id', $transaction->warehouse_id)
                    ->first();
                
                if ($inventory) {
                    // ✅ طرح الكمية التي تم إضافتها سابقاً
                    $originalQuantity = $inventory->quantity;
                    $newQuantity = $originalQuantity - $transaction->quantity;
                    
                    // ✅ التأكد من عدم وصول الكمية إلى أقل من صفر
                    $newQuantity = max(0, $newQuantity);
                    
                    $inventory->update([
                        'quantity' => $newQuantity,
                        'last_movement_at' => now()
                    ]);
                    
                    // ✅ تسجيل حركة عكسية (خروج) لتوثيق العملية
                    InventoryTransaction::create([
                        'product_id'       => $transaction->product_id,
                        'warehouse_id'     => $transaction->warehouse_id,
                        'type'             => 'out',
                        'reference_number' => $order->document_number . '_deleted',
                        'quantity'         => $transaction->quantity,
                        'notes'            => "حذف شحنة - استرجاع الكمية (كانت: {$originalQuantity}، أصبحت: {$newQuantity})",
                        'user_id'          => 1,
                    ]);
                }
                
                // ✅ حذف الحركة الأصلية
                $transaction->delete();
            }
            
            // ✅ حذف أمر الاستلام
            $order->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الشحنة واسترجاع الكمية من المخزون بنجاح'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'حدث خطأ: ' . $e->getMessage()
        ], 500);
    }
}
}