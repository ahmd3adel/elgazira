<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class EmployeeController extends Controller
{
    /**
     * عرض الصفحة الرئيسية وجلب بيانات الأجاكس للجدول
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Employee::with('warehouse')->latest()->select([
                'id', 'name', 'phone', 'job_title', 'warehouse_id', 'is_active', 
                'notes', 'health_certificate_image', 'health_certificate_issued_at', 
                'health_certificate_expires_at', 'created_at'
            ]);

            return DataTables::eloquent($data)
                ->addIndexColumn()
                ->addColumn('warehouse_name', function (Employee $row) {
                    return $row->warehouse ? $row->warehouse->name : '<span class="text-muted">بدون مخزن</span>';
                })
                ->addColumn('health_certificate', function (Employee $row) {
                    if (!$row->health_certificate_expires_at) {
                        return '<span class="text-muted small">غير محدد</span>';
                    }

                    $isExpired = Carbon::parse($row->health_certificate_expires_at)->isPast();
                    $badgeClass = $isExpired ? 'bg-danger' : 'bg-success';
                    $statusText = $isExpired ? 'منتهية' : 'سارية';

                    $imgBtn = '';
                    if ($row->health_certificate_image) {
                        $url = asset('storage/' . $row->health_certificate_image);
                        $imgBtn = '<a href="' . $url . '" target="_blank" class="btn btn-xs btn-outline-info ml-1" title="عرض الشهادة"><i class="fas fa-image"></i></a>';
                    }

                    return '<span class="badge ' . $badgeClass . '">' . $statusText . '</span> ' .
                           '<div class="small text-muted">انتهاء: ' . $row->health_certificate_expires_at . '</div>' . $imgBtn;
                })
                ->addColumn('is_active', function (Employee $row) {
                    return $row->is_active
                        ? '<span class="badge bg-success">نشط</span>'
                        : '<span class="badge bg-danger">غير نشط</span>';
                })
                ->addColumn('action', function (Employee $row) {
                    $buttons = '
                        <button class="btn btn-sm btn-info edit-employee"
                            data-id="' . $row->id . '"
                            title="تعديل">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger delete-employee"
                            data-id="' . $row->id . '"
                            data-name="' . e($row->name) . '"
                            title="حذف">
                            <i class="fas fa-trash"></i>
                        </button>';

                    return $buttons;
                })
                ->rawColumns(['warehouse_name', 'health_certificate', 'is_active', 'action'])
                ->make(true);
        }

        $warehouses = Warehouse::where('type', 'main')->get();
        return view('backend.employees.index', compact('warehouses'));
    }

    /**
     * حفظ عامل جديد
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'                         => 'required|string|max:255',
            'phone'                        => 'nullable|string|max:20',
            'job_title'                    => 'nullable|string|max:255',
            'warehouse_id'                 => 'nullable|exists:warehouses,id',
            'is_active'                    => 'boolean',
            'notes'                        => 'nullable|string',
            'health_certificate_image'     => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'health_certificate_issued_at' => 'nullable|date',
            'health_certificate_expires_at'=> 'nullable|date',
        ]);

        try {
            if ($request->hasFile('health_certificate_image')) {
                $validated['health_certificate_image'] = $request->file('health_certificate_image')->store('certificates', 'public');
            }

            Employee::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'تم إضافة العامل بنجاح',
            ]);
        } catch (\Exception $e) {
            Log::error('Employee store failed', [
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
     * جلب بيانات عامل واحد
     */
    public function show(Employee $employee): JsonResponse
    {
        $data = $employee->only([
            'id', 'name', 'phone', 'job_title', 'warehouse_id', 'is_active', 'notes',
            'health_certificate_issued_at', 'health_certificate_expires_at', 'health_certificate_image'
        ]);

        $data['health_certificate_image_url'] = $employee->health_certificate_image 
            ? asset('storage/' . $employee->health_certificate_image) 
            : null;

        return response()->json($data);
    }

    /**
     * تحديث بيانات العامل
     */
    public function update(Request $request, Employee $employee): JsonResponse
    {
        $validated = $request->validate([
            'name'                         => 'required|string|max:255',
            'phone'                        => 'nullable|string|max:20',
            'job_title'                    => 'nullable|string|max:255',
            'warehouse_id'                 => 'nullable|exists:warehouses,id',
            'is_active'                    => 'boolean',
            'notes'                        => 'nullable|string',
            'health_certificate_image'     => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'health_certificate_issued_at' => 'nullable|date',
            'health_certificate_expires_at'=> 'nullable|date',
        ]);

        try {
            if ($request->hasFile('health_certificate_image')) {
                // حذف الصورة القديمة إن وجدت لتوفير مساحة التخزين
                if ($employee->health_certificate_image && Storage::disk('public')->exists($employee->health_certificate_image)) {
                    Storage::disk('public')->delete($employee->health_certificate_image);
                }
                $validated['health_certificate_image'] = $request->file('health_certificate_image')->store('certificates', 'public');
            }

            $employee->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث بيانات العامل بنجاح',
            ]);
        } catch (\Exception $e) {
            Log::error('Employee update failed', [
                'error' => $e->getMessage(),
                'id'    => $employee->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء التحديث، يرجى المحاولة مرة أخرى',
            ], 500);
        }
    }

    /**
     * حذف العامل
     */
    public function destroy(Employee $employee): JsonResponse
    {
        try {
            // حذف ملف الشهادة الصحية المرتبط بالعامل عند الحذف (اختياري ولكن يفضل)
            if ($employee->health_certificate_image && Storage::disk('public')->exists($employee->health_certificate_image)) {
                Storage::disk('public')->delete($employee->health_certificate_image);
            }

            $employee->delete();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف العامل بنجاح',
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::warning('Employee delete blocked by foreign key', [
                'id'    => $employee->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'لا يمكن حذف هذا العامل لارتباطه ببيانات أخرى',
            ], 409);
        } catch (\Exception $e) {
            Log::error('Employee delete failed', [
                'id'    => $employee->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء الحذف، يرجى المحاولة مرة أخرى',
            ], 500);
        }
    }
}