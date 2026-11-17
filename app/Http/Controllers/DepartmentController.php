<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Governorate;
use App\Models\Warehouse;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Department::with(['governorate', 'mainWarehouse', 'operationWarehouse'])->latest()->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('governorate_name', function ($row) {
                    return $row->governorate ? $row->governorate->name : '<span class="text-muted">غير محدد</span>';
                })
                ->addColumn('main_warehouse', function ($row) {
                    return $row->mainWarehouse ? $row->mainWarehouse->name : '<span class="text-muted">غير محدد</span>';
                })
                ->addColumn('operation_warehouse', function ($row) {
                    return $row->operationWarehouse ? $row->operationWarehouse->name : '<span class="text-muted">غير محدد</span>';
                })
                ->addColumn('action', function ($row) {
                    return '
                        <button class="btn btn-sm btn-primary edit-department" data-id="' . $row->id . '" title="تعديل">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger delete-department" data-id="' . $row->id . '" data-name="' . $row->name . '" title="حذف">
                            <i class="fas fa-trash"></i>
                        </button>
                    ';
                })
                ->rawColumns(['governorate_name', 'main_warehouse', 'operation_warehouse', 'action'])
                ->make(true);
        }

        $governorates = Governorate::where('status', 1)->get();
        $warehouses = Warehouse::where('status', 1)->get();

        return view('backend.departments.index', compact('governorates', 'warehouses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDepartmentRequest $request)
    {
        try {
            Department::create([
                'code' => $request->code,
                'name' => $request->name,
                'governorate_id' => $request->governorate_id,
                'main_warehouse_id' => $request->main_warehouse_id,
                'operation_warehouse_id' => $request->operation_warehouse_id,
                'manager_name' => $request->manager_name,
                'phone' => $request->manager_phone, // تم التصحيح: استخدام 'phone' كما هو في قاعدة البيانات
                'status' => $request->status ?? 1,
                'entity_type' => $request->entity_type ?? 'education', // تمت إضافته لأن الحقل موجود في الـ Migration
                // 'notes' => $request->notes, // تمت إضافته لأن الحقل موجود في الـ Migration
            ]);

            return redirect()->route('admin.departments.index')
                ->with('success', 'تم إضافة الإدارة بنجاح.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'حدث خطأ: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Department $department)
    {
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $department
            ]);
        }

        return redirect()->route('admin.departments.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDepartmentRequest $request, Department $department)
    {
        try {
            $department->update([
                'code' => $request->code,
                'name' => $request->name,
                'governorate_id' => $request->governorate_id,
                'main_warehouse_id' => $request->main_warehouse_id,
                'operation_warehouse_id' => $request->operation_warehouse_id,
                'manager_name' => $request->manager_name,
                'phone' => $request->manager_phone, // تم التصحيح: استخدام 'phone' كما هو في قاعدة البيانات
                'status' => $request->status,
                'entity_type' => $request->entity_type ?? 'education', // تمت إضافته
            ]);

            return redirect()->route('admin.departments.index')
                ->with('success', 'تم تحديث بيانات الإدارة بنجاح.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'حدث خطأ: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Department $department)
    {
        try {
            // التحقق من وجود مدارس تابعة
            if ($department->schools()->count() > 0) {
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'لا يمكن حذف الإدارة لأنها تحتوي على مدارس مسجلة.'
                    ], 422);
                }
                return redirect()->back()->with('error', 'لا يمكن حذف الإدارة لأنها تحتوي على مدارس مسجلة.');
            }

            $department->delete();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم حذف الإدارة بنجاح.'
                ]);
            }

            return redirect()->route('admin.departments.index')
                ->with('success', 'تم حذف الإدارة بنجاح.');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }
}