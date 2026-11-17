<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\Department;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class SchoolController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = School::with('department');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('department_name', function($row) {
                    return $row->department ? $row->department->name : '<span class="badge badge-warning">غير محدد</span>';
                })
                ->addColumn('action', function($row) {
                    return '
                        <div class="btn-group" role="group">
                            <button class="btn btn-sm btn-warning edit-school" data-id="' . $row->id . '" title="تعديل">
                                <i class="fas fa-edit"></i> تعديل
                            </button>
                            <button class="btn btn-sm btn-danger delete-school" data-id="' . $row->id . '" data-name="' . e($row->name) . '" title="حذف">
                                <i class="fas fa-trash"></i> حذف
                            </button>
                        </div>';
                })
                ->rawColumns(['department_name', 'action'])
                ->make(true);
        }

        $departments = Department::orderBy('name')->get();
        return view('backend.schools.index', compact('departments'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'          => 'required|string|max:255|unique:schools,name',
            'type'          => 'required|string',
            'department_id' => 'required|exists:departments,id',
            'address'       => 'nullable|string',
        ], [
            'name.required'          => 'اسم المدرسة مطلوب',
            'name.unique'            => 'اسم المدرسة موجود مسبقاً',
            'type.required'          => 'نوع المدرسة مطلوب',
            'department_id.required' => 'القسم مطلوب',
            'department_id.exists'   => 'القسم المختار غير موجود',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            School::create($request->only(['name', 'type', 'department_id', 'address']));

            return response()->json([
                'success' => true,
                'message' => 'تم إضافة المدرسة بنجاح'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit($id)
    {
        try {
            $school = School::find($id);

            if (!$school) {
                return response()->json([
                    'success' => false,
                    'message' => 'المدرسة غير موجودة'
                ], 404);
            }

            return response()->json([
                'success'       => true,
                'id'            => $school->id,
                'name'          => $school->name,
                'type'          => $school->type,
                'department_id' => $school->department_id,
                'address'       => $school->address,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name'          => 'required|string|max:255|unique:schools,name,' . $id,
            'type'          => 'required|string',
            'department_id' => 'required|exists:departments,id',
            'address'       => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $school = School::findOrFail($id);
            $school->update($request->only(['name', 'type', 'department_id', 'address']));

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث بيانات المدرسة بنجاح'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $school = School::find($id);

            if (!$school) {
                return response()->json([
                    'success' => false,
                    'message' => 'المدرسة غير موجودة'
                ], 404);
            }

            $school->delete();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف المدرسة بنجاح'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage()
            ], 500);
        }
    }
}