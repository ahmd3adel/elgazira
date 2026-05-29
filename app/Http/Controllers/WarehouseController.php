<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Http\Requests\StoreWarehouseRequest;
use App\Http\Requests\UpdateWarehouseRequest;
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
            $data = Warehouse::with('governorate', 'parentMainWarehouse')->latest()->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('governorate_name', function ($warehouse) {
                    // إذا كان له محافظة مباشرة (رئيسي)
                    if ($warehouse->governorate) {
                        return $warehouse->governorate->name;
                    }
                    // إذا كان فرعي، نجلب محافظة الأب
                    return $warehouse->parentMainWarehouse?->governorate?->name ?? 'غير محدد';
                })
                ->addColumn('parent_name', function ($row) {
                    // إذا كان له أب، هات اسمه، وإلا ارجع نص فارغ
                    return $row->parentMainWarehouse ? $row->parentMainWarehouse->name : '---';
                })
                ->addColumn('status', function ($row) {
                    return $row->status
                        ? '<span class="badge badge-success">نشط</span>'
                        : '<span class="badge badge-danger">غير نشط</span>';
                })

                ->rawColumns(['status'])
                ->make(true);
        }
        $governorates = Governorate::all();
        $mainWarehouses = Warehouse::where('type', 'main')
            ->where('status', 1)  // يفضل جلب النشطة فقط
            ->with('governorate')  // لتحسين الأداء وجلب اسم المحافظة
            ->get();
        return view('backend.warehouses.index', compact('governorates', 'mainWarehouses'));
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
    public function store(StoreWarehouseRequest $request)
    {
        try {
            // 1. استلام البيانات المعتمدة من الـ Request
            $data = $request->validated();

            // 2. منطق التأكد من التبعية (Business Logic)
            // إذا كان المخزن فرعياً أو نقطة توزيع، يجب تصفير المحافظة 
            // لأننا سنعتمد على محافظة "الأب" برمجياً كما اتفقنا
            if ($data['type'] !== 'main') {
                $data['governorate_id'] = null;
            } else {
                // أما إذا كان رئيسياً، فيجب تصفير الـ parent_id لضمان نظافة البيانات
                $data['parent_id'] = null;
            }

            // 3. إنشاء السجل في قاعدة البيانات
            $warehouse = Warehouse::create($data);

            // 4. الرد على الـ Ajax بسلسلة نجاح
            return response()->json([
                'success' => true,
                'message' => 'تم إضافة المخزن بنجاح',
                'data'    => $warehouse
            ], 201);
        } catch (\Exception $e) {
            // في حالة حدوث أي خطأ غير متوقع
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء الحفظ: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Warehouse $warehouse)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Warehouse $warehouse)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateWarehouseRequest $request, Warehouse $warehouse)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
public function destroy(Warehouse $warehouse)
{
    try {
        // نفس التحقق من المخازن الفرعية...
        $subWarehouses = Warehouse::where('parent_id', $warehouse->id)->count();
        
        if ($subWarehouses > 0) {
            return response()->json([
                'success' => false,
                'message' => "لا يمكن حذف هذا المخزن لأنه يحتوي على {$subWarehouses} مخازن فرعية."
            ], 422);
        }
        
        // Soft Delete (يضيف تاريخ في deleted_at)
        $warehouse->delete();
        
        return response()->json([
            'success' => true,
            'message' => "تم حذف المخزن '{$warehouse->name}' بنجاح"
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'خطأ: ' . $e->getMessage()
        ], 500);
    }
}

// دالة لاستعادة المخزن المحذوف
public function restore($id)
{
    try {
        $warehouse = Warehouse::withTrashed()->findOrFail($id);
        $warehouse->restore();
        
        return response()->json([
            'success' => true,
            'message' => "تم استعادة المخزن '{$warehouse->name}' بنجاح"
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'خطأ: ' . $e->getMessage()
        ], 500);
    }
}

// دالة للحذف النهائي
public function forceDelete($id)
{
    try {
        $warehouse = Warehouse::withTrashed()->findOrFail($id);
        $warehouse->forceDelete();
        
        return response()->json([
            'success' => true,
            'message' => "تم حذف المخزن نهائياً"
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'خطأ: ' . $e->getMessage()
        ], 500);
    }
}
}
