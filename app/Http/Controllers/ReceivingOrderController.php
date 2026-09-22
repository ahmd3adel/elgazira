<?php

namespace App\Http\Controllers;

use App\Models\ReceivingOrder;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
                'receiving_orders.production_date',
                'receiving_orders.quantity',
                'receiving_orders.samples_quantity',
                'receiving_orders.arrival_time',
                'receiving_orders.departure_time',
                'receiving_orders.notes',
                'receiving_orders.supplier_receipt',
                'receiving_orders.supplier_id',
                'receiving_orders.warehouse_id',
                'receiving_orders.product_id',
                'receiving_orders.created_at',
                'suppliers.name as supplier_name',
                'warehouses.name as warehouse_name',
                'products.name as product_name',
                'products.expiry_duration as product_expiry_duration'  // ✅ أضفناها
            )
                ->leftJoin('suppliers', 'receiving_orders.supplier_id', '=', 'suppliers.id')
                ->leftJoin('warehouses', 'receiving_orders.warehouse_id', '=', 'warehouses.id')
                ->leftJoin('products', 'receiving_orders.product_id', '=', 'products.id');

            if ($request->has('supplier_id') && $request->supplier_id != '') {
                $data->where('receiving_orders.supplier_id', $request->supplier_id);
            }

            return DataTables::of($data)
                ->addIndexColumn()
                ->filterColumn('supplier_name', function ($query, $keyword) {
                    $query->where('suppliers.name', 'like', "%{$keyword}%");
                })
                ->filterColumn('warehouse_name', function ($query, $keyword) {
                    $query->where('warehouses.name', 'like', "%{$keyword}%");
                })
                ->filterColumn('product_name', function ($query, $keyword) {
                    $query->where('products.name', 'like', "%{$keyword}%");
                })
                ->editColumn('batch_number', function ($row) {
                    return $row->batch_number ? '<span class="badge badge-secondary">' . $row->batch_number . '</span>' : '-';
                })
                ->editColumn('supplier_name', function ($row) {
                    return $row->supplier_name ?? '<span class="text-danger">غير محدد</span>';
                })
                ->editColumn('product_name', function ($row) {
                    return $row->product_name ?? '<span class="text-danger">غير محدد</span>';
                })
                ->editColumn('warehouse_name', function ($row) {
                    return $row->warehouse_name ?? '<span class="text-danger">غير محدد</span>';
                })
                ->editColumn('arrival_time', function ($row) {
                    return $row->arrival_time ? \Carbon\Carbon::parse($row->arrival_time)->format('Y-m-d H:i') : '-';
                })
                ->editColumn('departure_time', function ($row) {
                    return $row->departure_time ? \Carbon\Carbon::parse($row->departure_time)->format('Y-m-d H:i') : '-';
                })
                // ✅ عمود تاريخ الوصول
                ->addColumn('arrival_date', function ($row) {
                    if ($row->arrival_time) {
                        return \Carbon\Carbon::parse($row->arrival_time)->format('Y-m-d');
                    }
                    return '-';
                })
                // ✅ عمود صورة إذن المورد
                ->addColumn('supplier_receipt', function ($row) {
                    if ($row->supplier_receipt) {
                        return '<a href="' . asset('storage/' . $row->supplier_receipt) . '" target="_blank" class="btn btn-sm btn-success">
                                <i class="fas fa-file-image"></i> عرض
                            </a>';
                    }
                    return '<span class="text-muted">لا يوجد</span>';
                })
                ->addColumn('action', function ($row) {
                    return '
        <div class="btn-group" role="group">

            <button class="btn btn-sm btn-primary edit-receiving-order"
                    data-id="' . $row->id . '"
                    title="تعديل">
                <i class="fas fa-edit"></i>
            </button>

            <a href="' . route('admin.receiving-orders.inspection', $row->id) . '"
               target="_blank"
               class="btn btn-sm btn-success"
               title="محضر فحص">
                <i class="fas fa-clipboard-check"></i>
            </a>

            <button class="btn btn-sm btn-info view-report-copy"
                    data-id="' . $row->id . '"
                    title="نسخ التقرير">
                <i class="fas fa-copy"></i>
            </button>

            <button class="btn btn-sm btn-danger delete-receiving-order"
                    data-id="' . $row->id . '"
                    data-number="' . $row->document_number . '"
                    title="حذف">
                <i class="fas fa-trash"></i>
            </button>

        </div>';
                })
                ->addColumn('production_date', function ($row) {
                    if ($row->production_date) {
                        return \Carbon\Carbon::parse($row->production_date)->format('Y-m-d');
                    }
                    return '-';
                })

                ->addColumn('expiry_date', function ($row) {
                    if (!$row->production_date || !$row->product_expiry_duration) {
                        return '<span class="text-muted">-</span>';
                    }

                    $expiry = \Carbon\Carbon::parse($row->production_date)
                        ->addMonths((int) $row->product_expiry_duration);

                    $daysLeft = now()->diffInDays($expiry, false);

                    // تحديد لون البادج حسب الأيام المتبقية
                    if ($daysLeft < 0) {
                        $badgeClass = 'danger';
                        $icon = 'fa-times-circle';
                        $tooltip = 'منتهي الصلاحية';
                    } elseif ($daysLeft <= 30) {
                        $badgeClass = 'warning';
                        $icon = 'fa-exclamation-triangle';
                        $tooltip = 'ينتهي خلال ' . $daysLeft . ' يوم';
                    } elseif ($daysLeft <= 90) {
                        $badgeClass = 'info';
                        $icon = 'fa-clock';
                        $tooltip = 'ينتهي خلال ' . $daysLeft . ' يوم';
                    } else {
                        $badgeClass = 'success';
                        $icon = 'fa-check-circle';
                        $tooltip = 'صالح لـ ' . $daysLeft . ' يوم';
                    }

                    return '<span class="badge badge-' . $badgeClass . '" title="' . $tooltip . '">'
                        . '<i class="fas ' . $icon . '"></i> '
                        . $expiry->format('Y-m-d')
                        . '</span>';
                })

                ->rawColumns(['batch_number', 'action', 'supplier_name', 'product_name', 'warehouse_name', 'supplier_receipt', 'expiry_date'])
                ->orderColumn('expiry_date', function ($query, $order) {
    // ترتيب حسب: production_date + expiry_duration (بالأشهر)
    $query->orderByRaw('DATE_ADD(receiving_orders.production_date, INTERVAL products.expiry_duration MONTH) ' . $order);
})
                ->make(true);
        }

        // باقي الكود لتحميل الصفحة الرئيسية...
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

    /**
     * حساب تاريخ انتهاء الصلاحية للشحنة
     * = تاريخ الإنتاج + مدة صلاحية المنتج (بالشهور)
     */
    private function calculateExpiryDate($productionDate, $product)
    {
        if (!$productionDate || !$product || !$product->expiry_duration) {
            return null;
        }

        return \Carbon\Carbon::parse($productionDate)
            ->addMonths((int) $product->expiry_duration)
            ->format('Y-m-d');
    }


    public function inspection($id)
    {
        $order = ReceivingOrder::select(
            'receiving_orders.*',
            'suppliers.name as supplier_name',
            'warehouses.name as warehouse_name',
            'products.name as product_name'
        )
            ->leftJoin(
                'suppliers',
                'receiving_orders.supplier_id',
                '=',
                'suppliers.id'
            )
            ->leftJoin(
                'warehouses',
                'receiving_orders.warehouse_id',
                '=',
                'warehouses.id'
            )
            ->leftJoin(
                'products',
                'receiving_orders.product_id',
                '=',
                'products.id'
            )
            ->where('receiving_orders.id', $id)
            ->firstOrFail();

        return view(
            'backend.receiving_orders.inspection',
            compact('order')
        );
    }
    /**
     * Get total shipments statistics (grouped by warehouse and product)
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
            'warehouses' => $warehouses,
            'products' => $productDetails,
            'matrix' => $matrix,
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
            'production_date'  => 'nullable|date',
            'quantity'         => 'required|integer|min:1',
            'samples_quantity' => 'nullable|integer|min:0',
            'arrival_time'     => 'nullable|date',
            'departure_time'   => 'nullable|date',
            'notes'            => 'nullable|string',
            'supplier_receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048', // ✅ 2MB
        ]);

        try {
            return DB::transaction(function () use ($request) {
                $userId = auth()->id() ?? 1;

                // ✅ رفع صورة إذن المورد
                $receiptPath = null;
                if ($request->hasFile('supplier_receipt')) {
                    $file = $request->file('supplier_receipt');
                    $filename = time() . '_' . $request->document_number . '.' . $file->getClientOriginalExtension();
                    $receiptPath = $file->storeAs('receipts/supplier', $filename, 'public');
                }

                // 1. إنشاء أمر الاستلام
                $order = ReceivingOrder::create([
                    'document_number'  => $request->document_number,
                    'batch_number'     => $request->batch_number,
                    'production_date'  => $request->production_date,
                    'warehouse_id'     => $request->warehouse_id,
                    'product_id'       => $request->product_id,
                    'supplier_id'      => $request->supplier_id,
                    'quantity'         => $request->quantity,
                    'samples_quantity' => $request->samples_quantity ?? 0,
                    'arrival_time'     => $request->arrival_time,
                    'departure_time'   => $request->departure_time,
                    'notes'            => $request->notes,
                    'supplier_receipt' => $receiptPath,
                    'user_id'          => $userId,
                ]);

                // 2. تحديث المخزون
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
        $newQuantity = $oldQuantity + $quantity;

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
            $order = ReceivingOrder::with('product')->findOrFail($id);

            // ✅ حساب تاريخ الانتهاء
            $expiryDate = null;
            if ($order->production_date && $order->product && $order->product->expiry_duration) {
                $expiryDate = \Carbon\Carbon::parse($order->production_date)
                    ->addMonths((int) $order->product->expiry_duration)
                    ->format('Y-m-d');
            }

            return response()->json([
                'success' => true,
                'id' => $order->id,
                'document_number' => $order->document_number,
                'supplier_id' => $order->supplier_id,
                'warehouse_id' => $order->warehouse_id,
                'product_id' => $order->product_id,
                'batch_number' => $order->batch_number,
                'production_date' => $order->production_date ? $order->production_date->format('Y-m-d') : null,
                'expiry_date' => $expiryDate,   // ✅
                'quantity' => $order->quantity,
                'samples_quantity' => $order->samples_quantity,
                'arrival_time' => $order->arrival_time ? $order->arrival_time->format('Y-m-d\TH:i') : null,
                'departure_time' => $order->departure_time ? $order->departure_time->format('Y-m-d\TH:i') : null,
                'notes' => $order->notes,
                'supplier_receipt' => $order->supplier_receipt,
                'supplier_receipt_url' => $order->supplier_receipt ? asset('storage/' . $order->supplier_receipt) : null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'لم يتم العثور على الشحنة'
            ], 404);
        }
    }

    /**
     * حساب تاريخ الانتهاء (AJAX) — يُستخدم في الواجهة لتحديث الحقل فوراً
     */
    public function calculateExpiry(Request $request)
    {
        $productionDate = $request->production_date;
        $productId = $request->product_id;

        if (!$productionDate || !$productId) {
            return response()->json(['expiry_date' => null]);
        }

        $product = Product::find($productId);

        if (!$product || !$product->expiry_duration) {
            return response()->json(['expiry_date' => null]);
        }

        $expiryDate = \Carbon\Carbon::parse($productionDate)
            ->addMonths((int) $product->expiry_duration)
            ->format('Y-m-d');

        return response()->json(['expiry_date' => $expiryDate]);
    }

    /**
     * Update the specified resource in storage.
     */
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
            'production_date'  => 'nullable|date',
            'quantity'         => 'required|integer|min:1',
            'samples_quantity' => 'nullable|integer|min:0',
            'arrival_time'     => 'nullable|date',
            'departure_time'   => 'nullable|date',
            'notes'            => 'nullable|string',
            'supplier_receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        try {
            return DB::transaction(function () use ($request, $id) {
                $order = ReceivingOrder::findOrFail($id);
                $userId = auth()->id() ?? 1;

                // ✅ تخزين البيانات القديمة للمقارنة
                $oldProductId = $order->product_id;
                $oldWarehouseId = $order->warehouse_id;
                $oldQuantity = $order->quantity;
                $newQuantity = $request->quantity;

                // ✅ رفع صورة جديدة إذا تم تحميلها
                $receiptPath = $order->supplier_receipt;
                if ($request->hasFile('supplier_receipt')) {
                    if ($order->supplier_receipt && Storage::disk('public')->exists($order->supplier_receipt)) {
                        Storage::disk('public')->delete($order->supplier_receipt);
                    }
                    $file = $request->file('supplier_receipt');
                    $filename = time() . '_' . $request->document_number . '.' . $file->getClientOriginalExtension();
                    $receiptPath = $file->storeAs('receipts/supplier', $filename, 'public');
                }

                // ✅ تحديث بيانات الشحنة
                $order->update([
                    'document_number'  => $request->document_number,
                    'batch_number'     => $request->batch_number,
                    'production_date'  => $request->production_date,
                    'warehouse_id'     => $request->warehouse_id,
                    'product_id'       => $request->product_id,
                    'supplier_id'      => $request->supplier_id,
                    'quantity'         => $request->quantity,
                    'samples_quantity' => $request->samples_quantity ?? 0,
                    'arrival_time'     => $request->arrival_time,
                    'departure_time'   => $request->departure_time,
                    'notes'            => $request->notes,
                    'supplier_receipt' => $receiptPath,
                ]);

                // ==========================================
                // ✅ تحديث المخزون بشكل صحيح
                // ==========================================

                // 1️⃣ حالة 1: تغير المنتج أو المخزن
                if ($oldProductId != $request->product_id || $oldWarehouseId != $request->warehouse_id) {

                    // 🔹 خصم الكمية من المخزن القديم
                    if ($oldProductId && $oldWarehouseId) {
                        $oldInventory = Inventory::where('product_id', $oldProductId)
                            ->where('warehouse_id', $oldWarehouseId)
                            ->first();

                        if ($oldInventory) {
                            $newOldQty = max(0, $oldInventory->quantity - $oldQuantity);
                            $oldInventory->update([
                                'quantity' => $newOldQty,
                                'last_movement_at' => now()
                            ]);

                            // تسجيل حركة خروج من المخزن القديم
                            InventoryTransaction::create([
                                'product_id'       => $oldProductId,
                                'warehouse_id'     => $oldWarehouseId,
                                'type'             => 'out',
                                'reference_number' => $order->document_number . '_old',
                                'quantity'         => $oldQuantity,
                                'notes'            => "تعديل شحنة - خصم من المخزن القديم (المنتج: {$oldProductId})",
                                'user_id'          => $userId,
                            ]);
                        }
                    }

                    // 🔹 إضافة الكمية إلى المخزن الجديد
                    $newInventory = Inventory::firstOrCreate(
                        ['product_id' => $request->product_id, 'warehouse_id' => $request->warehouse_id],
                        ['quantity' => 0, 'last_movement_at' => now()]
                    );

                    $newInventory->update([
                        'quantity' => $newInventory->quantity + $newQuantity,
                        'last_movement_at' => now()
                    ]);

                    // تسجيل حركة دخول إلى المخزن الجديد
                    InventoryTransaction::create([
                        'product_id'       => $request->product_id,
                        'warehouse_id'     => $request->warehouse_id,
                        'type'             => 'in',
                        'reference_number' => $order->document_number . '_new',
                        'quantity'         => $newQuantity,
                        'notes'            => "تعديل شحنة - إضافة إلى المخزن الجديد (المنتج: {$request->product_id})",
                        'user_id'          => $userId,
                    ]);
                }
                // 2️⃣ حالة 2: نفس المنتج والمخزن ولكن تغيرت الكمية
                else if ($oldQuantity != $newQuantity) {
                    $quantityDiff = $newQuantity - $oldQuantity;

                    $inventory = Inventory::where('product_id', $request->product_id)
                        ->where('warehouse_id', $request->warehouse_id)
                        ->first();

                    if ($inventory) {
                        $oldInventoryQty = $inventory->quantity;
                        $newInventoryQty = max(0, $oldInventoryQty + $quantityDiff);

                        $inventory->update([
                            'quantity' => $newInventoryQty,
                            'last_movement_at' => now()
                        ]);

                        // تسجيل حركة تعديل
                        InventoryTransaction::create([
                            'product_id'       => $request->product_id,
                            'warehouse_id'     => $request->warehouse_id,
                            'type'             => $quantityDiff > 0 ? 'in' : 'out',
                            'reference_number' => $order->document_number,
                            'quantity'         => abs($quantityDiff),
                            'notes'            => "تعديل كمية الشحنة (من {$oldQuantity} إلى {$newQuantity})",
                            'user_id'          => $userId,
                        ]);
                    }
                }
                // 3️⃣ حالة 3: لا تغيير في المنتج أو المخزن أو الكمية
                else {
                    // لا شيء يتغير، فقط تحديث البيانات الأخرى
                }

                return response()->json([
                    'success' => true,
                    'message' => 'تم تحديث الشحنة والمخزون بنجاح'
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
    public function destroy($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $order = ReceivingOrder::findOrFail($id);

                // ✅ حذف صورة إذن المورد
                if ($order->supplier_receipt && Storage::disk('public')->exists($order->supplier_receipt)) {
                    Storage::disk('public')->delete($order->supplier_receipt);
                }

                // ✅ الحصول على حركات المخزون المرتبطة بهذه الشحنة
                $transactions = InventoryTransaction::where('reference_number', $order->document_number)->get();

                foreach ($transactions as $transaction) {
                    // ✅ استعادة المخزون إلى حالته قبل الحركة
                    $inventory = Inventory::where('product_id', $transaction->product_id)
                        ->where('warehouse_id', $transaction->warehouse_id)
                        ->first();

                    if ($inventory) {
                        $originalQuantity = $inventory->quantity;
                        $newQuantity = max(0, $originalQuantity - $transaction->quantity);

                        if ($newQuantity == 0) {
                            // ✅ إذا أصبحت الكمية صفر، احذف سجل المخزون
                            $inventory->delete();
                        } else {
                            // ✅ تحديث الكمية
                            $inventory->update([
                                'quantity' => $newQuantity,
                                'last_movement_at' => now()
                            ]);
                        }

                        // ✅ تسجيل حركة عكسية
                        InventoryTransaction::create([
                            'product_id'       => $transaction->product_id,
                            'warehouse_id'     => $transaction->warehouse_id,
                            'type'             => 'out',
                            'reference_number' => $order->document_number . '_deleted',
                            'quantity'         => $transaction->quantity,
                            'notes'            => "حذف شحنة - استرجاع الكمية (كانت: {$originalQuantity}، أصبحت: {$newQuantity})" . ($newQuantity == 0 ? ' (تم حذف سجل المخزون)' : ''),
                            'user_id'          => auth()->id() ?? 1,
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

    /**
     * Remove supplier receipt image only
     */
    public function removeReceipt($id)
    {
        try {
            $order = ReceivingOrder::findOrFail($id);

            if ($order->supplier_receipt && Storage::disk('public')->exists($order->supplier_receipt)) {
                Storage::disk('public')->delete($order->supplier_receipt);
            }

            $order->update(['supplier_receipt' => null]);

            return response()->json([
                'success' => true,
                'message' => 'تم حذف الصورة بنجاح'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage()
            ], 500);
        }
    }
}
