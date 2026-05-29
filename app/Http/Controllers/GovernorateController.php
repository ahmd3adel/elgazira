<?php

namespace App\Http\Controllers;

use App\Models\Governorate;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log;

class GovernorateController extends Controller
{
    /**
     * عرض الصفحة الرئيسية وجلب بيانات الأجاكس للجدول
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Governorate::latest()->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('status', function ($row) {
                    return $row->status
                        ? '<span class="badge badge-success">نشط</span>'
                        : '<span class="badge badge-danger">غير نشط</span>';
                })
                ->addColumn('action', function ($row) {
                    return '
                        <button class="btn btn-sm btn-info edit-governorate" data-id="' . $row->id . '">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger delete-governorate" data-id="' . $row->id . '" data-name="' . $row->name . '">
                            <i class="fas fa-trash"></i>
                        </button>
                    ';
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('backend.governorates.index');
    }

    /**
     * حفظ محافظة جديدة
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:governorates,code',
            'manager_name' => 'nullable|string|max:255',
            'manager_phone' => 'nullable|string|max:20',
        ]);

        try {
            Governorate::create($request->all());
            return response()->json(['success' => true, 'message' => 'تم إضافة المحافظة بنجاح']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'حدث خطأ أثناء الحفظ'], 500);
        }
    }

    /**
     * جلب بيانات محافظة واحدة للتعديل
     */
    public function edit(Governorate $governorate)
    {
        // بما أننا نستخدم Route Model Binding، المحافظة تأتي جاهزة كـ Object
        return response()->json($governorate);
    }

    /**
     * تحديث بيانات المحافظة
     */
    public function update(Request $request, Governorate $governorate)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:governorates,code,' . $governorate->id,
            'manager_name' => 'nullable|string|max:255',
            'manager_phone' => 'nullable|string|max:20',
        ]);

        try {
            $governorate->update($request->all());
            return response()->json(['success' => true, 'message' => 'تم تحديث البيانات بنجاح']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'حدث خطأ أثناء التحديث'], 500);
        }
    }

    /**
     * حذف المحافظة
     */
    public function destroy(Governorate $governorate)
    {
        try {
            $governorate->delete();
            return response()->json(['success' => true, 'message' => 'تم حذف المحافظة بنجاح']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'لا يمكن حذف هذه المحافظة لارتباطها ببيانات أخرى'], 500);
        }
    }
}
