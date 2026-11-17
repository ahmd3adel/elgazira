<?php

namespace App\Http\Controllers;

use App\Models\ReceivingOrder;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\Sample;
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
                'receiving_orders.damaged_barcode',
                'products.expiry_duration as product_expiry_duration'
            )
                ->leftJoin('suppliers', 'receiving_orders.supplier_id', '=', 'suppliers.id')
                ->leftJoin('warehouses', 'receiving_orders.warehouse_id', '=', 'warehouses.id')
                ->leftJoin('products', 'receiving_orders.product_id', '=', 'products.id');

            if ($request->has('supplier_id') && $request->supplier_id != '') {
                $data->where('receiving_orders.supplier_id', $request->supplier_id);
            }

            // ✅ الترتيب الافتراضي: آخر شحنة أولاً (يُلغى لو المستخدم طلب ترتيب مخصص)
      if (!$request->filled('order')) {
    $data->orderBy('receiving_orders.created_at', 'desc');
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
                ->editColumn('damaged_barcode', function ($row) {
    if ($row->damaged_barcode > 0) {
        return '<span class="badge badge-danger">' 
            . $row->damaged_barcode . ' تالف</span>';
    }
    return '<span class="text-muted">—</span>';
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
                ->addColumn('arrival_date', function ($row) {
                    if ($row->arrival_time) {
                        return \Carbon\Carbon::parse($row->arrival_time)->format('Y-m-d');
                    }
                    return '-';
                })
                ->orderColumn('id', function ($query, $order) {
                    $query->orderBy('receiving_orders.id', $order);
                })
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
                ->rawColumns(['batch_number', 'action', 'supplier_name', 'product_name', 'warehouse_name', 'supplier_receipt', 'expiry_date' ,  'damaged_barcode'])
                ->orderColumn('expiry_date', function ($query, $order) {
                    $query->orderByRaw('DATE_ADD(receiving_orders.production_date, INTERVAL products.expiry_duration MONTH) ' . $order);
                })
                ->make(true);
        }

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
            ->leftJoin('suppliers', 'receiving_orders.supplier_id', '=', 'suppliers.id')
            ->leftJoin('warehouses', 'receiving_orders.warehouse_id', '=', 'warehouses.id')
            ->leftJoin('products', 'receiving_orders.product_id', '=', 'products.id')
            ->where('receiving_orders.id', $id)
            ->firstOrFail();

        return view('backend.receiving_orders.inspection', compact('order'));
    }

    private function getTotalShipmentsStatistics()
    {
        $warehouses = \App\Models\Warehouse::where('status', 1)
            ->where('type', '!=', 'dispatch_point')
            ->orderBy('name')
            ->get();

        $products = \App\Models\Product::orderBy('name')->get();

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

        $matrix = [];
        $warehouseNames = [];
        $productDetails = [];

        foreach ($warehouses as $warehouse) {
            $warehouseNames[$warehouse->id] = $warehouse->name;

            foreach ($products as $product) {
                $key = $warehouse->id . '_' . $product->id;
                $quantity = isset($shipmentsData[$key]) ? (int) $shipmentsData[$key]->total_quantity : 0;

                $matrix[$warehouse->id][$product->id] = $quantity;

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
            'damaged_barcode'  => 'nullable|integer|min:0',
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
            return DB::transaction(function () use ($request) {
                $userId = auth()->id() ?? 1;

                // رفع صورة إذن المورد
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
                    'damaged_barcode'  => $request->damaged_barcode ?? 0,
                ]);

                // 2. تحديث المخزون (الأساسي + العينات)
                $this->updateInventory($order, $request->quantity, $userId);

                // 3. مزامنة جدول العينات
                $this->syncSamples($order, $userId);

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
     * تحديث المخزون (الأساسي + العينات) وتسجيل الحركات
     */
    private function updateInventory($order, $quantity, $userId)
    {
        $productId = $order->product_id;
        $warehouseId = $order->warehouse_id;
        $samplesQty = (int) ($order->samples_quantity ?? 0);
        $currentTime = now();

        $inventory = Inventory::firstOrCreate(
            ['product_id' => $productId, 'warehouse_id' => $warehouseId],
            ['quantity' => 0, 'samples_quantity' => 0, 'last_movement_at' => $currentTime]
        );

        $oldQuantity = $inventory->quantity;
        $oldSamples = $inventory->samples_quantity ?? 0;

        $inventory->update([
            'quantity'         => $oldQuantity + $quantity,
            'samples_quantity' => $oldSamples + $samplesQty,
            'last_movement_at' => $currentTime,
        ]);

        // حركة دخول الكمية الأساسية
        InventoryTransaction::create([
            'product_id'       => $productId,
            'warehouse_id'     => $warehouseId,
            'type'             => 'in',
            'reference_number' => $order->document_number,
            'quantity'         => $quantity,
            'notes'            => "استلام شحنة - {$order->document_number} (كانت: {$oldQuantity}، أصبحت: " . ($oldQuantity + $quantity) . ")",
            'user_id'          => $userId,
        ]);

        // حركة دخول العينات (منفصلة)
        if ($samplesQty > 0) {
            InventoryTransaction::create([
                'product_id'       => $productId,
                'warehouse_id'     => $warehouseId,
                'type'             => 'in',
                'reference_number' => $order->document_number . '_samples',
                'quantity'         => $samplesQty,
                'notes'            => "استلام عينات - {$order->document_number} (كانت: {$oldSamples}، أصبحت: " . ($oldSamples + $samplesQty) . ")",
                'user_id'          => $userId,
            ]);
        }
    }

    /**
     * مزامنة جدول العينات
     * - إنشاء سجل جديد لو مفيش
     * - تحديث السجل لو موجود
     * - حذف السجل لو الكمية بقت 0
     */
    private function syncSamples(ReceivingOrder $order, int $userId): void
    {
        $samplesQty = (int) ($order->samples_quantity ?? 0);
        $existing = Sample::where('receiving_order_id', $order->id)->first();

        // مفيش عينات
        if ($samplesQty <= 0) {
            if ($existing) $existing->delete();
            return;
        }

        // فيه عينات
        if ($existing) {
            $existing->update([
                'warehouse_id' => $order->warehouse_id,
                'product_id'   => $order->product_id,
                'quantity'     => $samplesQty,
                'user_id'      => $order->user_id ?? $userId,
            ]);
        } else {
            Sample::create([
                'receiving_order_id' => $order->id,
                'warehouse_id'       => $order->warehouse_id,
                'department_id'      => null,
                'product_id'         => $order->product_id,
                'quantity'           => $samplesQty,
                'status'             => 'pending',
                'user_id'            => $order->user_id ?? $userId,
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $order = ReceivingOrder::with('product')->findOrFail($id);

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
                'expiry_date' => $expiryDate,
                'quantity' => $order->quantity,
                'samples_quantity' => $order->samples_quantity,
                'arrival_time' => $order->arrival_time ? $order->arrival_time->format('Y-m-d\TH:i') : null,
                'departure_time' => $order->departure_time ? $order->departure_time->format('Y-m-d\TH:i') : null,
                'notes' => $order->notes,
                'damaged_barcode' => $order->damaged_barcode ?? 0,
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
    public function update(Request $request, $id)
    {
        $request->validate([
            'document_number'  => 'required|unique:receiving_orders,document_number,' . $id,
            'damaged_barcode'  => 'nullable|integer|min:0',
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

                // البيانات القديمة
                $oldProductId    = $order->product_id;
                $oldWarehouseId  = $order->warehouse_id;
                $oldQuantity     = $order->quantity;
                $oldSamples      = (int) ($order->samples_quantity ?? 0);

                // البيانات الجديدة
                $newQuantity     = (int) $request->quantity;
                $newSamples      = (int) ($request->samples_quantity ?? 0);

                // رفع صورة جديدة
                $receiptPath = $order->supplier_receipt;
                if ($request->hasFile('supplier_receipt')) {
                    if ($order->supplier_receipt && Storage::disk('public')->exists($order->supplier_receipt)) {
                        Storage::disk('public')->delete($order->supplier_receipt);
                    }
                    $file = $request->file('supplier_receipt');
                    $filename = time() . '_' . $request->document_number . '.' . $file->getClientOriginalExtension();
                    $receiptPath = $file->storeAs('receipts/supplier', $filename, 'public');
                }

                // تحديث بيانات الشحنة
                $order->update([
                    'document_number'  => $request->document_number,
                    'batch_number'     => $request->batch_number,
                    'production_date'  => $request->production_date,
                    'warehouse_id'     => $request->warehouse_id,
                    'product_id'       => $request->product_id,
                    'supplier_id'      => $request->supplier_id,
                    'quantity'         => $newQuantity,
                    'samples_quantity' => $newSamples,
                    'arrival_time'     => $request->arrival_time,
                    'departure_time'   => $request->departure_time,
                    'notes'            => $request->notes,
                    'supplier_receipt' => $receiptPath,
                    'damaged_barcode' => $request->damaged_barcode ?? 0,
                ]);

                // ==========================================
                // تحديث المخزون
                // ==========================================

                // 1️⃣ تغير المنتج أو المخزن
                if ($oldProductId != $request->product_id || $oldWarehouseId != $request->warehouse_id) {

                    // خصم من المخزن القديم (أساسي + عينات)
                    if ($oldProductId && $oldWarehouseId) {
                        $oldInventory = Inventory::where('product_id', $oldProductId)
                            ->where('warehouse_id', $oldWarehouseId)
                            ->first();

                        if ($oldInventory) {
                            $oldInventory->update([
                                'quantity'         => max(0, $oldInventory->quantity - $oldQuantity),
                                'samples_quantity' => max(0, ($oldInventory->samples_quantity ?? 0) - $oldSamples),
                                'last_movement_at' => now(),
                            ]);

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

                    // إضافة للمخزن الجديد (أساسي + عينات)
                    $newInventory = Inventory::firstOrCreate(
                        ['product_id' => $request->product_id, 'warehouse_id' => $request->warehouse_id],
                        ['quantity' => 0, 'samples_quantity' => 0, 'last_movement_at' => now()]
                    );

                    $newInventory->update([
                        'quantity'         => $newInventory->quantity + $newQuantity,
                        'samples_quantity' => ($newInventory->samples_quantity ?? 0) + $newSamples,
                        'last_movement_at' => now(),
                    ]);

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
                // 2️⃣ نفس المنتج والمخزن، لكن تغيرت الكمية أو العينات
                else if ($oldQuantity != $newQuantity || $oldSamples != $newSamples) {
                    $quantityDiff = $newQuantity - $oldQuantity;
                    $samplesDiff  = $newSamples - $oldSamples;

                    $inventory = Inventory::where('product_id', $request->product_id)
                        ->where('warehouse_id', $request->warehouse_id)
                        ->first();

                    if ($inventory) {
                        $inventory->update([
                            'quantity'         => max(0, $inventory->quantity + $quantityDiff),
                            'samples_quantity' => max(0, ($inventory->samples_quantity ?? 0) + $samplesDiff),
                            'last_movement_at' => now(),
                        ]);

                        if ($quantityDiff != 0) {
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

                        if ($samplesDiff != 0) {
                            InventoryTransaction::create([
                                'product_id'       => $request->product_id,
                                'warehouse_id'     => $request->warehouse_id,
                                'type'             => $samplesDiff > 0 ? 'in' : 'out',
                                'reference_number' => $order->document_number . '_samples',
                                'quantity'         => abs($samplesDiff),
                                'notes'            => "تعديل كمية العينات (من {$oldSamples} إلى {$newSamples})",
                                'user_id'          => $userId,
                            ]);
                        }
                    }
                }

                // ✅ مزامنة جدول العينات
                $this->syncSamples($order, $userId);

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

                // حذف صورة إذن المورد
                if ($order->supplier_receipt && Storage::disk('public')->exists($order->supplier_receipt)) {
                    Storage::disk('public')->delete($order->supplier_receipt);
                }

                // ✅ استرجاع العينات من inventories (قبل حذف السجل)
                $samplesQty = (int) ($order->samples_quantity ?? 0);
                if ($samplesQty > 0) {
                    $inventory = Inventory::where('product_id', $order->product_id)
                        ->where('warehouse_id', $order->warehouse_id)
                        ->first();
                    if ($inventory) {
                        $inventory->update([
                            'samples_quantity' => max(0, ($inventory->samples_quantity ?? 0) - $samplesQty),
                            'last_movement_at' => now(),
                        ]);

                        InventoryTransaction::create([
                            'product_id'       => $order->product_id,
                            'warehouse_id'     => $order->warehouse_id,
                            'type'             => 'out',
                            'reference_number' => $order->document_number . '_deleted_samples',
                            'quantity'         => $samplesQty,
                            'notes'            => "حذف شحنة - استرجاع العينات",
                            'user_id'          => auth()->id() ?? 1,
                        ]);
                    }
                }

                // ✅ حذف سجلات العينات المرتبطة بالشحنة
                Sample::where('receiving_order_id', $order->id)->delete();

                // استعادة المخزون (الكمية الأساسية) من الحركات
                $transactions = InventoryTransaction::where('reference_number', $order->document_number)->get();

                foreach ($transactions as $transaction) {
                    $inventory = Inventory::where('product_id', $transaction->product_id)
                        ->where('warehouse_id', $transaction->warehouse_id)
                        ->first();

                    if ($inventory) {
                        $originalQuantity = $inventory->quantity;
                        $newQuantity = max(0, $originalQuantity - $transaction->quantity);

                        if ($newQuantity == 0) {
                            $inventory->delete();
                        } else {
                            $inventory->update([
                                'quantity' => $newQuantity,
                                'last_movement_at' => now()
                            ]);
                        }

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

                    $transaction->delete();
                }

                // حذف أمر الاستلام
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
