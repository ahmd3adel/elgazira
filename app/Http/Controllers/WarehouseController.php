<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\Governorate;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class WarehouseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            try {
                $data = Warehouse::with(['governorate', 'parentMainWarehouse'])
                    ->orderBy('created_at', 'desc')
                    ->get();
                    
                return DataTables::of($data)
                    ->addIndexColumn()
                    ->addColumn('governorate_name', function ($warehouse) {
                        if ($warehouse->type === 'main') {
                            return $warehouse->governorate?->name ?? 'غير محدد';
                        }
                        return $warehouse->parentMainWarehouse?->governorate?->name ?? 'غير محدد';
                    })
                    ->addColumn('parent_name', function ($row) {
                        return $row->parentMainWarehouse ? $row->parentMainWarehouse->name : '---';
                    })
                    ->addColumn('products_count', function ($row) {
                        return '<span class="badge badge-secondary">0 منتج</span>';
                    })
                    ->addColumn('status', function ($row) {
                        return $row->status
                            ? '<span class="badge badge-success">نشط</span>'
                            : '<span class="badge badge-danger">غير نشط</span>';
                    })
                    ->addColumn('actions', function ($row) {
                        return '
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-info btn-sm edit-warehouse" data-id="'.$row->id.'" title="تعديل">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-danger btn-sm delete-warehouse" 
                                    data-id="'.$row->id.'" 
                                    data-name="'.$row->name.'" 
                                    title="حذف">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        ';
                    })
                    ->rawColumns(['status', 'actions', 'products_count'])
                    ->make(true);
                    
            } catch (\Exception $e) {
                return response()->json([
                    'error' => true,
                    'message' => $e->getMessage()
                ], 500);
            }
        }
        
        $governorates = Governorate::where('status', 1)->orderBy('name')->get();
        $mainWarehouses = Warehouse::where('type', 'main')
            ->where('status', 1)
            ->with('governorate')
            ->orderBy('name')
            ->get();
        
        return view('backend.warehouses.index', compact('governorates', 'mainWarehouses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:warehouses,name',
                'code' => 'required|string|max:50|unique:warehouses,code',
                'type' => 'required|in:main,sub,dispatch_point',
                'governorate_id' => 'required_if:type,main|nullable|exists:governorates,id',
                'parent_id' => 'required_if:type,sub,dispatch_point|nullable|exists:warehouses,id',
                'manager_name' => 'nullable|string|max:255',
                'manager_phone' => 'nullable|string|max:20',
                'address' => 'nullable|string',
                'status' => 'boolean',
            ]);

            if ($validated['type'] !== 'main') {
                $validated['governorate_id'] = null;
            } else {
                $validated['parent_id'] = null;
            }

            $warehouse = Warehouse::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'تم إضافة المخزن بنجاح',
                'data' => $warehouse
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        if (request()->ajax()) {
            try {
                $warehouse = Warehouse::with(['governorate', 'parentMainWarehouse'])->findOrFail($id);
                return response()->json([
                    'success' => true,
                    'data' => $warehouse
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'المخزن غير موجود'
                ], 404);
            }
        }
        
        abort(404, 'Page not found');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $warehouse = Warehouse::with(['governorate', 'parentMainWarehouse'])->findOrFail($id);
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $warehouse
                ]);
            }
            
            $governorates = Governorate::where('status', 1)->orderBy('name')->get();
            $mainWarehouses = Warehouse::where('type', 'main')
                ->where('status', 1)
                ->where('id', '!=', $id)
                ->orderBy('name')
                ->get();
                
            return view('backend.warehouses.edit', compact('warehouse', 'governorates', 'mainWarehouses'));
            
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'المخزن غير موجود'
                ], 404);
            }
            abort(404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $warehouse = Warehouse::findOrFail($id);
            
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:warehouses,name,' . $id,
                'code' => 'nullable|string|max:50|unique:warehouses,code,' . $id, // changed to nullable
                'type' => 'required|in:main,sub,dispatch_point',
                'governorate_id' => 'required_if:type,main|nullable|exists:governorates,id',
                'parent_id' => 'required_if:type,sub,dispatch_point|nullable|exists:warehouses,id',
                'manager_name' => 'nullable|string|max:255',
                'manager_phone' => 'nullable|string|max:20',
                'address' => 'nullable|string',
                'status' => 'boolean',
            ]);

            if ($validated['type'] !== 'main') {
                $validated['governorate_id'] = null;
            } else {
                $validated['parent_id'] = null;
            }

            if (isset($validated['parent_id']) && $validated['parent_id'] == $id) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن جعل المخزن أباً لنفسه'
                ], 422);
            }

            $warehouse->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث المخزن بنجاح',
                'data' => $warehouse
            ]);
            
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
public function destroy(Warehouse $warehouse)
{
    try {
        if (!$warehouse->canBeDeleted()) {
            return response()->json([
                'success' => false,
                'message' => $warehouse->getDeleteRestrictionMessage()
            ], 422);
        }
        
        $warehouse->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'تم حذف المخزن بنجاح'
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'حدث خطأ: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Display a listing of trashed warehouses.
 */
public function trashed(Request $request)
{
    // تأكد من أن هذا الـ route يعمل لطلبات AJAX
    if ($request->ajax()) {
        try {
            // جلب المخازن المحذوفة فقط
            $warehouses = Warehouse::onlyTrashed()
                ->select('id', 'code', 'name', 'type', 'deleted_at')
                ->orderBy('deleted_at', 'desc')
                ->get();
            
            $types = [
                'main' => 'رئيسي',
                'sub' => 'فرعي',
                'dispatch_point' => 'نقطة توزيع'
            ];
            
            $data = [];
            foreach ($warehouses as $index => $warehouse) {
                $data[] = [
                    'id' => $warehouse->id,
                    'DT_RowIndex' => $index + 1,
                    'code' => $warehouse->code ?? '-',
                    'name' => $warehouse->name,
                    'type_label' => $types[$warehouse->type] ?? $warehouse->type,
                    'deleted_date' => $warehouse->deleted_at ? $warehouse->deleted_at->format('Y-m-d H:i:s') : '-'
                ];
            }
            
            return response()->json([
                'data' => $data
            ]);
                
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    // إذا لم يكن AJAX، ارجع خطأ
    return response()->json(['error' => 'Invalid request'], 400);
}

    /**
     * Restore soft deleted warehouses.
     */
    public function restore(Request $request)
    {
        try {
            $ids = $request->ids;
            
            if (empty($ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'الرجاء تحديد المخازن المراد استعادتها'
                ], 422);
            }
            
            $restored = Warehouse::onlyTrashed()
                ->whereIn('id', $ids)
                ->restore();
                
            return response()->json([
                'success' => true,
                'message' => "تم استعادة {$restored} مخزن/مخازن بنجاح"
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage()
            ], 500);
        }
    }

 /**
 * Force delete warehouses permanently.
 */
public function forceDelete(Request $request)
{
    try {
        $ids = $request->ids;
        
        if (empty($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'الرجاء تحديد المخازن المراد حذفها نهائياً'
            ], 422);
        }
        
        // فحص ما إذا كانت المخازن المراد حذفها تحتوي على مخازن فرعية
        $warehouses = Warehouse::onlyTrashed()->whereIn('id', $ids)->get();
        $hasSubWarehouses = false;
        $hasProducts = false;
        
        foreach ($warehouses as $warehouse) {
            // فحص المخازن الفرعية حتى المحذوفة
            $subCount = Warehouse::withTrashed()
                ->where('parent_id', $warehouse->id)
                ->count();
                
            if ($subCount > 0) {
                $hasSubWarehouses = true;
                break;
            }
            
            // ✅ فحص المنتجات
            $productsCount = $warehouse->products()->withTrashed()->count();
            if ($productsCount > 0) {
                $hasProducts = true;
                break;
            }
        }
        
        if ($hasSubWarehouses) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن حذف هذه المخازن نهائياً لأن بعضها يحتوي على مخازن فرعية'
            ], 422);
        }
        
        if ($hasProducts) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن حذف هذه المخازن نهائياً لأن بعضها يحتوي على منتجات'
            ], 422);
        }
        
        $deleted = Warehouse::onlyTrashed()
            ->whereIn('id', $ids)
            ->forceDelete();
            
        return response()->json([
            'success' => true,
            'message' => "تم حذف {$deleted} مخزن/مخازن نهائياً"
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'حدث خطأ: ' . $e->getMessage()
        ], 500);
    }
}
}