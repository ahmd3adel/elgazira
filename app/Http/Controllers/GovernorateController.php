<?php

namespace App\Http\Controllers;

use App\Models\Governorate;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
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
            $data = Governorate::latest()->select([
                'id', 'name', 'code', 'manager_name', 'manager_phone', 'status', 'created_at'
            ]);

            return DataTables::eloquent($data)
                ->addIndexColumn()
                ->addColumn('status', function (Governorate $row) {
                    return $row->status
                        ? '<span class="badge bg-success">نشط</span>'
                        : '<span class="badge bg-danger">غير نشط</span>';
                })
                ->addColumn('action', function (Governorate $row) {
                    $buttons = '
                        <button class="btn btn-sm btn-info edit-governorate"
                            data-id="' . $row->id . '"
                            title="تعديل">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger delete-governorate"
                            data-id="' . $row->id . '"
                            data-name="' . e($row->name) . '"
                            title="حذف">
                            <i class="fas fa-trash"></i>
                        </button>';

                    return $buttons;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        $governorates = Governorate::latest()->get();

        return view('backend.governorates.index', compact('governorates'));
    }

    /**
     * حفظ محافظة جديدة
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'code'          => 'required|string|max:50|unique:governorates,code',
            'manager_name'  => 'nullable|string|max:255',
            'manager_phone' => 'nullable|string|max:20',
            'status'        => 'boolean',
        ]);

        try {
            Governorate::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'تم إضافة المحافظة بنجاح',
            ]);
        } catch (\Exception $e) {
            Log::error('Governorate store failed', [
                'error' => $e->getMessage(),
                'data'  => $validated,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء الحفظ، يرجى المحاولة مرة أخرى',
            ], 500);
        }
    }

    /**
     * جلب بيانات محافظة واحدة
     */
    public function show(Governorate $governorate): JsonResponse
    {
        return response()->json($governorate->only([
            'id', 'name', 'code', 'manager_name', 'manager_phone', 'status',
        ]));
    }

    /**
     * تحديث بيانات المحافظة
     */
    public function update(Request $request, Governorate $governorate): JsonResponse
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'code'          => 'required|string|max:50|unique:governorates,code,' . $governorate->id,
            'manager_name'  => 'nullable|string|max:255',
            'manager_phone' => 'nullable|string|max:20',
            'status'        => 'boolean',
        ]);

        try {
            $governorate->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث البيانات بنجاح',
            ]);
        } catch (\Exception $e) {
            Log::error('Governorate update failed', [
                'error' => $e->getMessage(),
                'id'    => $governorate->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء التحديث، يرجى المحاولة مرة أخرى',
            ], 500);
        }
    }

    /**
     * حذف المحافظة
     */
    public function destroy(Governorate $governorate): JsonResponse
    {
        try {
            $governorate->delete();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف المحافظة بنجاح',
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::warning('Governorate delete blocked by foreign key', [
                'id'    => $governorate->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'لا يمكن حذف هذه المحافظة لارتباطها ببيانات أخرى',
            ], 409);
        } catch (\Exception $e) {
            Log::error('Governorate delete failed', [
                'id'    => $governorate->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء الحذف، يرجى المحاولة مرة أخرى',
            ], 500);
        }
    }
}