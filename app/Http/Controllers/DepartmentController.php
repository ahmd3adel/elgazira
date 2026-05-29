<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
   public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Department::latest()->get();
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
            ->rawColumns(['governorates_name','main_warehouse'])
                ->make(true);
        }

        return view('backend.departments.index');
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
    public function store(StoreDepartmentRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Department $department)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Department $department)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDepartmentRequest $request, Department $department)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Department $department)
    {
        //
    }
}
