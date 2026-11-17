<?php
// app/Http/Controllers/DriverController.php

namespace App\Http\Controllers;

use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class DriverController extends Controller
{
// app/Http/Controllers/DriverController.php

public function index(Request $request)
{
    if ($request->ajax()) {
        $data = Driver::select('*');
        
        // التصفيات...
        
        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('health_certificate_badge', function($row) {
                return $row->health_certificate_status_badge;
            })
            ->addColumn('training_status_badge', function($row) {
                return $row->training_status_badge;
            })
            ->addColumn('status_badge', function($row) {
                return $row->status_badge;
            })
            ->addColumn('health_certificate_image', function($row) {
                if ($row->health_certificate_image && $row->health_certificate_image_url) {
                    return '<img src="'.$row->health_certificate_image_url.'" 
                                   class="driver-certificate-thumb" 
                                   style="width: 45px; height: 45px; object-fit: cover; border-radius: 8px; cursor: pointer; border: 1px solid #ddd;" 
                                   onclick="showImageModal(\''.$row->health_certificate_image_url.'\', \''.addslashes($row->name).'\')" 
                                   alt="شهادة '.e($row->name).'"
                                   title="اضغط للتكبير">';
                }
                return '<span class="badge badge-secondary">لا توجد صورة</span>';
            })
            ->addColumn('action', function($row) {
                return '
                    <div class="btn-group" role="group">
                        <button class="btn btn-sm btn-primary edit-driver" data-id="' . $row->id . '" title="تعديل">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger delete-driver" data-id="' . $row->id . '" data-name="' . $row->name . '" title="حذف">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>';
            })
            ->editColumn('training_date', function($row) {
                return $row->training_date ? $row->training_date->format('Y-m-d') : '-';
            })
            ->rawColumns(['health_certificate_badge', 'training_status_badge', 'status_badge', 'health_certificate_image', 'action'])
            ->make(true);
 
        }
        
        // إحصائيات للكروت
        $stats = [
            'total' => Driver::count(),
            'active' => Driver::where('status', 'active')->count(),
            'inactive' => Driver::where('status', 'inactive')->count(),
            'training_completed' => Driver::where('training_status', 'completed')->count(),
            'training_pending' => Driver::where('training_status', 'pending')->count(),
            'health_valid' => Driver::where('health_certificate_status', 'valid')->count(),
            'health_expired' => Driver::where('health_certificate_status', 'expired')->count(),
        ];
        
        return view('backend.drivers.index', compact('stats'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'line_number' => 'nullable|string|max:50',
            'national_id' => 'required|string|max:20|unique:drivers,national_id',
            'health_certificate_status' => 'required|in:pending,valid,expired,not_required',
            'health_certificate_image' => 'nullable|image|mimes:jpeg,png,jpg,pdf|max:2048',
            'phone' => 'nullable|string|max:20',
            'training_status' => 'required|in:pending,completed,failed,not_scheduled',
            'training_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        try {
            $data = $request->except('health_certificate_image');
            
            // رفع الصورة إذا وجدت
            if ($request->hasFile('health_certificate_image')) {
                $path = $request->file('health_certificate_image')->store('drivers/certificates', 'public');
                $data['health_certificate_image'] = $path;
            }
            
            $data['created_by'] = auth()->id();
            
            Driver::create($data);
            
            return response()->json([
                'success' => true,
                'message' => 'تم إضافة المندوب بنجاح'
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
    public function edit($id)
    {
        try {
            $driver = Driver::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'id' => $driver->id,
                'name' => $driver->name,
                'line_number' => $driver->line_number,
                'national_id' => $driver->national_id,
                'health_certificate_status' => $driver->health_certificate_status,
                'health_certificate_image' => $driver->health_certificate_image,
                'health_certificate_image_url' => $driver->health_certificate_image_url,
                'phone' => $driver->phone,
                'training_status' => $driver->training_status,
                'training_date' => $driver->training_date ? $driver->training_date->format('Y-m-d') : null,
                'notes' => $driver->notes,
                'status' => $driver->status,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'لم يتم العثور على المندوب'
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'line_number' => 'nullable|string|max:50',
            'national_id' => 'required|string|max:20|unique:drivers,national_id,' . $id,
            'health_certificate_status' => 'required|in:pending,valid,expired,not_required',
            'health_certificate_image' => 'nullable|image|mimes:jpeg,png,jpg,pdf|max:2048',
            'phone' => 'nullable|string|max:20',
            'training_status' => 'required|in:pending,completed,failed,not_scheduled',
            'training_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        try {
            $driver = Driver::findOrFail($id);
            $data = $request->except('health_certificate_image');
            
            // رفع الصورة الجديدة إذا وجدت
            if ($request->hasFile('health_certificate_image')) {
                // حذف الصورة القديمة
                if ($driver->health_certificate_image) {
                    Storage::disk('public')->delete($driver->health_certificate_image);
                }
                $path = $request->file('health_certificate_image')->store('drivers/certificates', 'public');
                $data['health_certificate_image'] = $path;
            }
            
            $data['updated_by'] = auth()->id();
            
            $driver->update($data);
            
            return response()->json([
                'success' => true,
                'message' => 'تم تحديث بيانات المندوب بنجاح'
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
    public function destroy($id)
    {
        try {
            $driver = Driver::findOrFail($id);
            
            // حذف الصورة المرتبطة
            if ($driver->health_certificate_image) {
                Storage::disk('public')->delete($driver->health_certificate_image);
            }
            
            $driver->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'تم حذف المندوب بنجاح'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Export drivers to Excel/CSV
     */
    public function export()
    {
        // يمكن إضافة تصدير Excel هنا
    }
}