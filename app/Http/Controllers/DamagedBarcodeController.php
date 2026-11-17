<?php

namespace App\Http\Controllers;

use App\Models\ReceivingOrder;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class DamagedBarcodeController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = ReceivingOrder::select(
                'receiving_orders.*',
                'suppliers.name as supplier_name',
                'warehouses.name as warehouse_name',
                'products.name as product_name',
                'products.sku as product_sku',
            )
                ->leftJoin('suppliers', 'receiving_orders.supplier_id', '=', 'suppliers.id')
                ->leftJoin('warehouses', 'receiving_orders.warehouse_id', '=', 'warehouses.id')
                ->leftJoin('products', 'receiving_orders.product_id', '=', 'products.id')
                ->where('receiving_orders.damaged_barcode', '>', 0);

            // ✅ فلاتر
            if ($request->filled('status')) {
                $query->where('receiving_orders.damaged_barcode_status', $request->status);
            }
            if ($request->filled('supplier_id')) {
                $query->where('receiving_orders.supplier_id', $request->supplier_id);
            }
            if ($request->filled('product_id')) {
                $query->where('receiving_orders.product_id', $request->product_id);
            }
            if ($request->filled('warehouse_id')) {
                $query->where('receiving_orders.warehouse_id', $request->warehouse_id);
            }
            if ($request->filled('date_from')) {
                $query->whereDate('receiving_orders.created_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('receiving_orders.created_at', '<=', $request->date_to);
            }
            if ($request->filled('search_term')) {
                $term = $request->search_term;
                $query->where(function ($q) use ($term) {
                    $q->where('receiving_orders.document_number', 'like', "%{$term}%")
                      ->orWhere('receiving_orders.batch_number', 'like', "%{$term}%");
                });
            }

            if (!$request->filled('order')) {
                $query->orderByDesc('receiving_orders.created_at');
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('document_number', fn($r) =>
                    '<span class="fw-bold text-primary">' . e($r->document_number) . '</span>'
                )
                ->editColumn('batch_number', fn($r) =>
                    $r->batch_number
                        ? '<span class="badge badge-secondary">' . e($r->batch_number) . '</span>'
                        : '<span class="text-muted">—</span>'
                )
                ->editColumn('damaged_barcode', fn($r) =>
                    '<span class="badge badge-danger badge-pill px-3 py-2">'
                    . number_format($r->damaged_barcode) . '</span>'
                )
                ->addColumn('status_badge', function ($r) {
                    $s = $r->damaged_barcode_status_label;
                    return '<span class="badge badge-' . $s['color'] . ' px-3 py-2">'
                        . '<i class="fas ' . $s['icon'] . ' ml-1"></i>' . $s['label'] . '</span>';
                })
                ->editColumn('damaged_barcode_delivered_at', fn($r) =>
                    $r->damaged_barcode_delivered_at
                        ? '<span class="text-success"><i class="fas fa-check-circle ml-1"></i>'
                          . $r->damaged_barcode_delivered_at->format('Y-m-d H:i') . '</span>'
                        : '<span class="text-muted">—</span>'
                )
                ->editColumn('created_at', fn($r) =>
                    $r->created_at->format('Y-m-d')
                )
                ->addColumn('action', function ($r) {
                    $actions = '<div class="btn-group" role="group">';

                    $actions .= '<button class="btn btn-sm btn-outline-primary btn-change-status"
                        data-id="' . $r->id . '"
                        data-status="' . $r->damaged_barcode_status . '"
                        data-number="' . e($r->document_number) . '"
                        title="تغيير الحالة">
                        <i class="fas fa-exchange-alt"></i></button>';

                    $actions .= '<button class="btn btn-sm btn-outline-info btn-view-notes"
                        data-id="' . $r->id . '"
                        data-notes="' . e($r->damaged_barcode_notes ?? '') . '"
                        data-number="' . e($r->document_number) . '"
                        title="ملاحظات">
                        <i class="fas fa-sticky-note"></i></button>';

                    $actions .= '<a href="' . route('admin.receiving-orders.inspection', $r->id) . '"
                        target="_blank" class="btn btn-sm btn-outline-success"
                        title="محضر الفحص"><i class="fas fa-clipboard-check"></i></a>';

                    $actions .= '</div>';
                    return $actions;
                })
                ->rawColumns([
                    'document_number', 'batch_number', 'damaged_barcode',
                    'status_badge', 'damaged_barcode_delivered_at', 'action',
                ])
                ->make(true);
        }

        // ✅ إحصائيات الكروت
        $stats = $this->getStats();

        $suppliers  = Supplier::where('status', 1)->orderBy('name')->get();
        $products   = Product::orderBy('name')->get();
        $warehouses = Warehouse::where('status', 1)
            ->where('type', '!=', 'dispatch_point')
            ->orderBy('name')->get();

        return view('backend.damaged_barcodes.index', compact(
            'stats', 'suppliers', 'products', 'warehouses'
        ));
    }

    private function getStats(): array
    {
        $base = ReceivingOrder::where('damaged_barcode', '>', 0);

        return [
            'total_orders'   => (clone $base)->count(),
            'total_quantity' => (clone $base)->sum('damaged_barcode'),
            'pending'        => (clone $base)->where('damaged_barcode_status', 'pending')->count(),
            'pending_qty'    => (clone $base)->where('damaged_barcode_status', 'pending')->sum('damaged_barcode'),
            'delivered'      => (clone $base)->where('damaged_barcode_status', 'delivered')->count(),
            'delivered_qty'  => (clone $base)->where('damaged_barcode_status', 'delivered')->sum('damaged_barcode'),
            'replaced'       => (clone $base)->where('damaged_barcode_status', 'replaced')->count(),
            'replaced_qty'   => (clone $base)->where('damaged_barcode_status', 'replaced')->sum('damaged_barcode'),
            'delivery_rate'  => (clone $base)->count() > 0
                ? round(((clone $base)->whereIn('damaged_barcode_status', ['delivered', 'replaced'])->count()
                    / (clone $base)->count()) * 100, 1)
                : 0,
        ];
    }

    /** ✅ تغيير الحالة */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status'  => 'required|in:pending,delivered,replaced',
            'notes'   => 'nullable|string|max:500',
        ]);

        try {
            $order = ReceivingOrder::findOrFail($id);

            $data = ['damaged_barcode_status' => $request->status];

            if (in_array($request->status, ['delivered', 'replaced'])) {
                $data['damaged_barcode_delivered_at'] = now();
            } else {
                $data['damaged_barcode_delivered_at'] = null;
            }

            if ($request->filled('notes')) {
                $data['damaged_barcode_notes'] = $request->notes;
            }

            $order->update($data);

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث حالة الباركود التالف بنجاح',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage(),
            ], 500);
        }
    }

    /** ✅ حفظ ملاحظات فقط */
    public function updateNotes(Request $request, $id)
    {
        $request->validate(['notes' => 'nullable|string|max:1000']);

        try {
            $order = ReceivingOrder::findOrFail($id);
            $order->update(['damaged_barcode_notes' => $request->notes]);

            return response()->json(['success' => true, 'message' => 'تم حفظ الملاحظات']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /** ✅ تصدير Excel */
    public function export(Request $request)
    {
        $query = ReceivingOrder::select(
            'receiving_orders.*',
            'suppliers.name as supplier_name',
            'warehouses.name as warehouse_name',
            'products.name as product_name',
        )
            ->leftJoin('suppliers', 'receiving_orders.supplier_id', '=', 'suppliers.id')
            ->leftJoin('warehouses', 'receiving_orders.warehouse_id', '=', 'warehouses.id')
            ->leftJoin('products', 'receiving_orders.product_id', '=', 'products.id')
            ->where('receiving_orders.damaged_barcode', '>', 0);

        if ($request->filled('status')) {
            $query->where('receiving_orders.damaged_barcode_status', $request->status);
        }

        $rows = $query->orderByDesc('receiving_orders.created_at')->get();

        return response()->json(['data' => $rows]);
    }
}