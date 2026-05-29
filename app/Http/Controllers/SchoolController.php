<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\Department;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SchoolController extends Controller
{
public function index(Request $request)
{
    // إذا كان الطلب AJAX (من DataTable)
    if ($request->ajax()) {
        $schools = School::with('department')->latest()->get();
        
        return DataTables::of($schools)
            ->addIndexColumn() // يضيف ترقيم تلقائي (DT_RowIndex)
            ->addColumn('department_id', function($row) {
                return $row->department ? $row->department->name : 'لا يوجد';
            })
            ->rawColumns(['action']) // إذا كان لديك عمود أزرار
            ->make(true);
    }
    
    // إذا كان طلب عادي (تحميل الصفحة لأول مرة)
    $departments = Department::all();
    return view('backend.schools.index', compact('departments'));
}

    public function store(Request $request)
    {
        $request->validate(['name' => 'required', 'department_id' => 'required']);
        School::create($request->all());
        return back()->with('success', 'تم إضافة المدرسة بنجاح');
    }
}