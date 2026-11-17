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
use Yajra\DataTables\Facades\DataTables;

class InventoryTransactionController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = InventoryTransaction::with(['product', 'warehouse', 'user'])
                ->select('inventory_transactions.*');

            // ✅ التصفية
            if ($request->filled('product_id')) {
                $query->where('product_id', $request->product_id);
            }
            if ($request->filled('warehouse_id')) {
                $query->where('warehouse_id', $request->warehouse_id);
            }
            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }
            if ($request->filled('from_date')) {
                $query->whereDate('created_at', '>=', $request->from_date);
            }
            if ($request->filled('to_date')) {
                $query->whereDate('created_at', '<=', $request->to_date);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('created_at', function ($row) {
                    return $row->movement_date 
                        ? \Carbon\Carbon::parse($row->movement_date)->format('Y-m-d H:i')
                        : $row->created_at->format('Y-m-d H:i');
                })
                ->addColumn('product_name', function ($row) {
                    return $row->product->name ?? '<span class="text-muted">---</span>';
                })
                ->addColumn('warehouse_name', function ($row) {
                    return $row->warehouse->name ?? '<span class="text-muted">---</span>';
                })
                ->addColumn('user_name', function ($row) {
                    return $row->user->name ?? 'مدير النظام';
                })
                ->addColumn('type_badge', function ($row) {
                    $badges = [
                        'in' => '<span class="badge badge-success">وارد (In)</span>',
                        'out' => '<span class="badge badge-danger">صادر (Out)</span>',
                        'transfer_in' => '<span class="badge badge-info">تحويل وارد</span>',
                        'transfer_out' => '<span class="badge badge-warning">تحويل صادر</span>',
                    ];
                    return $badges[$row->type] ?? '<span class="badge badge-secondary">' . $row->type . '</span>';
                })
                ->addColumn('action', function ($row) {
                    return '
                        <button type="button" class="btn btn-sm btn-info view-transaction" 
                                data-id="' . $row->id . '" title="عرض">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-danger delete-transaction" 
                                data-id="' . $row->id . '" title="حذف">
                            <i class="fas fa-trash"></i>
                        </button>
                    ';
                })
                ->filterColumn('product_name', function ($query, $keyword) {
                    $query->whereHas('product', function ($q) use ($keyword) {
                        $q->where('name', 'LIKE', "%{$keyword}%");
                    });
                })
                ->filterColumn('warehouse_name', function ($query, $keyword) {
                    $query->whereHas('warehouse', function ($q) use ($keyword) {
                        $q->where('name', 'LIKE', "%{$keyword}%");
                    });
                })
                ->filterColumn('user_name', function ($query, $keyword) {
                    $query->whereHas('user', function ($q) use ($keyword) {
                        $q->where('name', 'LIKE', "%{$keyword}%");
                    });
                })
                ->rawColumns(['product_name', 'warehouse_name', 'type_badge', 'action'])
                ->make(true);
        }

        $products = Product::orderBy('name')->get();
        $warehouses = Warehouse::all();
        
        return view('backend.inventory_transactions.index', compact('products', 'warehouses'));
    }

    public function showJson($id)
    {
        $transaction = InventoryTransaction::with(['product', 'warehouse', 'user'])->findOrFail($id);
        
        return response()->json([
            'id' => $transaction->id,
            'product' => $transaction->product->name ?? 'غير محدد',
            'warehouse' => $transaction->warehouse->name ?? 'غير محدد',
            'type' => $transaction->type,
            'quantity' => $transaction->quantity,
            'quantity_before' => $transaction->quantity_before,
            'quantity_after' => $transaction->quantity_after,
            'reference' => $transaction->reference_number ?? 'لا يوجد',
            'notes' => $transaction->notes ?? 'لا توجد ملاحظات',
            'user' => $transaction->user->name ?? 'مدير النظام',
            'date' => $transaction->movement_date 
                ? \Carbon\Carbon::parse($transaction->movement_date)->format('Y-m-d H:i')
                : $transaction->created_at->format('Y-m-d H:i'),
        ]);
    }

    public function store(StoreInventoryTransactionRequest $request)
    {
        try {
            DB::beginTransaction();
            
            // 1. جلب المخزون الحالي (قبل الحركة)
            $inventory = Inventory::where([
                'product_id' => $request->product_id,
                'warehouse_id' => $request->warehouse_id
            ])->first();
            
            $quantityBefore = $inventory ? $inventory->quantity : 0;
            
            // 2. التحقق من الرصيد في حالة الصادر
            if ($request->type === 'out' && $quantityBefore < $request->quantity) {
                throw new \Exception('الرصيد غير كافٍ لهذه العملية');
            }
            
            // 3. حساب الرصيد بعد الحركة
            if (in_array($request->type, ['in', 'transfer_in'])) {
                $quantityAfter = $quantityBefore + $request->quantity;
            } else {
                $quantityAfter = $quantityBefore - $request->quantity;
            }
            
            // 4. إنشاء الحركة
            $transaction = InventoryTransaction::create([
                'product_id' => $request->product_id,
                'warehouse_id' => $request->warehouse_id,
                'school_id' => $request->school_id,
                'department_id' => $request->department_id,
                'type' => $request->type,
                'reference_number' => $request->reference_number,
                'quantity' => $request->quantity,
                'quantity_before' => $quantityBefore,
                'quantity_after' => $quantityAfter,
                'balance_after' => $quantityAfter,
                'movement_date' => $request->movement_date ?? now(),
                'notes' => $request->notes,
                'user_id' => auth()->id(),  // ✅ user_id بدلاً من created_by
            ]);
            
            // 5. تحديث جدول المخزون
            if (in_array($request->type, ['in', 'transfer_in'])) {
                if ($inventory) {
                    $inventory->increment('quantity', $request->quantity);
                } else {
                    Inventory::create([
                        'product_id' => $request->product_id,
                        'warehouse_id' => $request->warehouse_id,
                        'quantity' => $request->quantity,
                    ]);
                }
            } else {
                $inventory->decrement('quantity', $request->quantity);
            }
            
            if ($inventory) {
                $inventory->update(['last_movement_at' => now()]);
            }
            
            DB::commit();
            
            return redirect()->route('admin.inventory_transactions.index')
                ->with('success', 'تم إضافة حركة المخزون بنجاح');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    // ... باقي الدوال
}