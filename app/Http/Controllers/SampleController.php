<?php

namespace App\Http\Controllers;

use App\Models\Sample;
use App\Models\ReceivingOrder;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SampleController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Sample::with(['receivingOrder', 'warehouse', 'product', 'department'])
                ->select('samples.*');

            // فلترة اختيارية
            if ($request->filled('warehouse_id')) {
                $data->where('warehouse_id', $request->warehouse_id);
            }
            if ($request->filled('product_id')) {
                $data->where('product_id', $request->product_id);
            }

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('document_number', fn($row) => $row->receivingOrder->document_number ?? '-')
                ->addColumn('warehouse_name', fn($row) => $row->warehouse->name ?? '-')
                ->addColumn('department_name', fn($row) => $row->department->name ?? '-')
                ->addColumn('product_name', fn($row) => $row->product->name ?? '-')
                ->addColumn('received_qty', function ($row) {
    return $row->received_quantity; // الكمية الواردة الأصلية
})
->addColumn('consumed_qty', function ($row) {
    return $row->consumed_quantity; // اللي اتصرف
})
->addColumn('available_qty', function ($row) {
    $available = $row->received_quantity - $row->consumed_quantity;
    return $available > 0 ? '<span class="badge badge-success">' . $available . '</span>' : '<span class="badge badge-danger">0</span>';
})
                ->addColumn('status_badge', function ($row) {
                    $colors = [
                        'pending'   => 'warning',
                        'tested'    => 'info',
                        'approved'  => 'success',
                        'rejected'  => 'danger',
                        'destroyed' => 'secondary',
                    ];
                    $labels = [
                        'pending'   => 'قيد الانتظار',
                        'tested'    => 'تم الفحص',
                        'approved'  => 'معتمد',
                        'rejected'  => 'مرفوض',
                        'destroyed' => 'متلف',
                    ];
                    $color = $colors[$row->status] ?? 'secondary';
                    $label = $labels[$row->status] ?? $row->status;
                    return "<span class='badge badge-{$color}'>{$label}</span>";
                })
                ->addColumn('action', function ($row) {
                    return '<button class="btn btn-sm btn-danger delete-sample" data-id="' . $row->id . '">
                                <i class="fas fa-trash"></i>
                            </button>';
                })
                ->rawColumns(['status_badge', 'action','available_qty'])
                ->make(true);
        }

        $warehouses = Warehouse::orderBy('name')->get();
        $products = Product::orderBy('name')->get();
        $totalSamples = Sample::sum('quantity');

        return view('backend.samples.index', compact('warehouses', 'products', 'totalSamples'));
    }
}