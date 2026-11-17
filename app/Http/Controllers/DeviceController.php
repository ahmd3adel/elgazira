<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Department;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class DeviceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index(Request $request)
{
    if ($request->ajax()) {
        $query = Device::with('department');
        
        // تطبيق الفلاتر
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        
        if ($request->filled('line_number')) {
            $query->where('line_number', 'like', '%' . $request->line_number . '%');
        }
        
        if ($request->filled('technical_status')) {
            $query->where('technical_status', $request->technical_status);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('department_name', function($row) {
                return $row->department ? $row->department->name : '<span class="badge badge-warning">غير محدد</span>';
            })
            ->addColumn('technical_status_badge', function($row) {
                return $row->technical_status_badge;
            })
            ->addColumn('status_badge', function($row) {
                return $row->status_badge;
            })
            ->addColumn('action', function($row) {
                // تأكد من استخدام $row->id (الـ ID الحقيقي من قاعدة البيانات)
                $deviceId = $row->id; // هذا هو الـ ID الحقيقي (2, 3, ...)
                
                $actions = '
                    <div class="btn-group action-buttons" role="group">
                        <button class="btn btn-sm btn-info edit-device" data-id="' . $deviceId . '" title="تعديل">
                            <i class="fas fa-edit"></i> تعديل
                        </button>
                        <button class="btn btn-sm btn-danger delete-device" data-id="' . $deviceId . '" data-name="' . e($row->line_number) . '" title="حذف">
                            <i class="fas fa-trash"></i> حذف
                        </button>
                    </div>';
                
                return $actions;
            })
            ->rawColumns(['department_name', 'technical_status_badge', 'status_badge', 'action'])
            ->make(true);
    }
    


        // إحصائيات للكروت
        $stats = [
            'total' => Device::count(),
            'active' => Device::where('status', 'active')->count(),
            'inactive' => Device::where('status', 'inactive')->count(),
            'working' => Device::where('technical_status', 'working')->count(),
            'maintenance' => Device::where('technical_status', 'maintenance')->count(),
            'broken' => Device::where('technical_status', 'broken')->count(),
            'retired' => Device::where('technical_status', 'retired')->count(),
        ];

        // جلب الإدارات للفلتر
        $departments = Department::orderBy('name')->get();

        return view('backend.devices.index', compact('stats', 'departments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'department_id' => 'required|exists:departments,id',
            'line_number' => 'required|string|max:50|unique:devices,line_number',
            'pos_username' => 'required|string|max:100|unique:devices,pos_username',
            'pos_password' => 'nullable|string|max:255',
            'serial_number' => 'required|string|max:100|unique:devices,serial_number',
            'technical_status' => 'required|in:working,maintenance,broken,retired',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string',
            'specifications' => 'nullable|string'
        ], [
            'department_id.required' => 'الإدارة مطلوبة',
            'line_number.required' => 'رقم الخط مطلوب',
            'line_number.unique' => 'رقم الخط موجود مسبقاً',
            'pos_username.required' => 'POS Username مطلوب',
            'pos_username.unique' => 'POS Username موجود مسبقاً',
            'serial_number.required' => 'السيريال نمبر مطلوب',
            'serial_number.unique' => 'السيريال نمبر موجود مسبقاً'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $data = $request->all();
            $data['created_by'] = auth()->id();

            Device::create($data);

            return response()->json([
                'success' => true,
                'message' => 'تم إضافة الجهاز بنجاح'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        \Log::info('Edit function called with ID: ' . $id);

        try {
            // استخدام find بدلاً من findOrFail مؤقتاً للتشخيص
            $device = Device::find($id);

            if (!$device) {
                \Log::error('Device not found with ID: ' . $id);
                \Log::info('Available devices IDs: ' . Device::pluck('id')->implode(', '));

                return response()->json([
                    'success' => false,
                    'message' => 'الجهاز غير موجود (ID: ' . $id . ')'
                ], 404);
            }

            \Log::info('Device found: ', $device->toArray());

            return response()->json([
                'success' => true,
                'id' => $device->id,
                'department_id' => $device->department_id,
                'line_number' => $device->line_number,
                'pos_username' => $device->pos_username,
                'pos_password' => $device->pos_password,
                'serial_number' => $device->serial_number,
                'technical_status' => $device->technical_status,
                'status' => $device->status,
                'notes' => $device->notes,
                'specifications' => $device->specifications
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in edit: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'خطأ: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'department_id' => 'required|exists:departments,id',
            'line_number' => 'required|string|max:50|unique:devices,line_number,' . $id,
            'pos_username' => 'required|string|max:100|unique:devices,pos_username,' . $id,
            'pos_password' => 'nullable|string|max:255',
            'serial_number' => 'required|string|max:100|unique:devices,serial_number,' . $id,
            'technical_status' => 'required|in:working,maintenance,broken,retired',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string',
            'specifications' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $device = Device::findOrFail($id);
            $data = $request->all();
            $data['updated_by'] = auth()->id();

            $device->update($data);

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث بيانات الجهاز بنجاح'
            ]);
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
        try {
            $device = Device::with('department')->findOrFail($id);

            // إذا كان طلب AJAX
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $device
                ]);
            }

            // إذا كنت تريد عرض صفحة مفصلة للجهاز
            return view('backend.devices.show', compact('device'));
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'الجهاز غير موجود'
                ], 404);
            }

            abort(404, 'الجهاز غير موجود');
        }
    }
    /**
     * Update technical status (temporary)
     */
    public function updateTechnicalStatus(Request $request, $id)
    {
        return response()->json([
            'success' => true,
            'message' => 'تم تحديث الحالة الفنية'
        ]);
    }

    /**
     * Get maintenance details (temporary)
     */
    public function getMaintenanceDetails($id)
    {
        return response()->json([
            'success' => true,
            'data' => []
        ]);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $device = Device::find($id);

            if (!$device) {
                return response()->json([
                    'success' => false,
                    'message' => 'الجهاز غير موجود'
                ], 404);
            }

            $device->delete();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف الجهاز بنجاح'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage()
            ], 500);
        }
    }
}
