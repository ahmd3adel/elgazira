<?php

namespace App\Http\Controllers;

use App\Models\MilkAllocation;
use App\Models\Department;
use App\Http\Requests\StoreMilkAllocationRequest;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log;

class MilkAllocationController extends Controller
{
    /**
     * صفحة العرض + DataTable
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            try {
                $query = MilkAllocation::with(['department', 'createdBy'])
                    ->select('milk_allocations.*');

                // الفلاتر
                if ($request->filled('from_date')) {
                    $query->whereDate('allocation_date', '>=', $request->from_date);
                }
                if ($request->filled('to_date')) {
                    $query->whereDate('allocation_date', '<=', $request->to_date);
                }
                if ($request->filled('department_id')) {
                    $query->whereIn('department_id', (array) $request->department_id);
                }

                // إجماليات (تُحسب على نفس الفلاتر)
                $totalsQuery = clone $query;
                $totals = [
                    'biscuit_cartons' => (int) $totalsQuery->sum('biscuit_cartons'),
                    'biscuit_packs'   => (int) $totalsQuery->sum('biscuit_packs'),
                    'milk_cans'       => (int) $totalsQuery->sum('milk_cans'),
                    'milk_cartons'    => (int) $totalsQuery->sum('milk_cartons'),
                    'total_meals'     => (int) $totalsQuery->sum('total_meals'),
                ];

                $dataTable = DataTables::of($query->latest('allocation_date')->latest('id'))
                    ->addIndexColumn()
                    ->addColumn('date_formatted', function ($row) {
                        return $row->allocation_date
                            ? $row->allocation_date->format('Y-m-d')
                            : '---';
                    })
                    ->addColumn('department_name', function ($row) {
                        return optional($row->department)->name ?? '---';
                    })
                    ->editColumn('biscuit_cartons', fn($row) => number_format($row->biscuit_cartons))
                    ->editColumn('biscuit_packs',   fn($row) => number_format($row->biscuit_packs))
                    ->editColumn('milk_cans',       fn($row) => number_format($row->milk_cans))
                    ->editColumn('milk_cartons',    fn($row) => number_format($row->milk_cartons))
                    ->editColumn('total_meals',     fn($row) => number_format($row->total_meals))
                    ->addColumn('action', function ($row) {
                        return '<div class="btn-group btn-group-sm" role="group">' .
                               '<button type="button" class="btn btn-info btn-sm edit-btn" data-id="' . $row->id . '" title="تعديل">' .
                               '<i class="fas fa-edit"></i></button> ' .
                               '<button type="button" class="btn btn-danger btn-sm delete-btn" data-id="' . $row->id . '" title="حذف">' .
                               '<i class="fas fa-trash"></i></button>' .
                               '</div>';
                    })
                    ->rawColumns(['action']);

                // ✅ نرجع الإجماليات مع البيانات
                return $dataTable
                    ->with([
                        'totals' => $totals,
                    ])
                    ->make(true);

            } catch (\Exception $e) {
                Log::error('MilkAllocation index error: ' . $e->getMessage());
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }

        $departments = Department::orderBy('name')->get();
        return view('backend.milk_allocations.index', compact('departments'));
    }

    /**
     * حفظ سجل جديد
     */
    public function store(StoreMilkAllocationRequest $request)
    {
        try {
            $allocation = MilkAllocation::create([
                'department_id'   => $request->department_id,
                'allocation_date' => $request->allocation_date,
                'biscuit_cartons' => $request->biscuit_cartons,
                'notes'           => $request->notes,
                'created_by'      => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => "تم تسجيل التوزيع بنجاح. إجمالي الوجبات: {$allocation->total_meals}",
                'data'    => $allocation,
            ], 201);

        } catch (\Exception $e) {
            Log::error('MilkAllocation store error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * جلب بيانات سجل للتعديل
     */
    public function editData($id)
    {
        try {
            $allocation = MilkAllocation::with('department')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id'              => $allocation->id,
                    'department_id'   => $allocation->department_id,
                    'department_name' => optional($allocation->department)->name,
                    'allocation_date' => $allocation->allocation_date
                                            ? $allocation->allocation_date->format('Y-m-d')
                                            : null,
                    'biscuit_cartons' => $allocation->biscuit_cartons,
                    'biscuit_packs'   => $allocation->biscuit_packs,
                    'milk_cans'       => $allocation->milk_cans,
                    'milk_cartons'    => $allocation->milk_cartons,
                    'total_meals'     => $allocation->total_meals,
                    'notes'           => $allocation->notes,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'السجل غير موجود',
            ], 404);
        }
    }

    /**
     * تحديث سجل
     */
    public function update(StoreMilkAllocationRequest $request, $id)
    {
        try {
            $allocation = MilkAllocation::findOrFail($id);

            $allocation->update([
                'department_id'   => $request->department_id,
                'allocation_date' => $request->allocation_date,
                'biscuit_cartons' => $request->biscuit_cartons,
                'notes'           => $request->notes,
            ]);

            return response()->json([
                'success' => true,
                'message' => "تم تحديث السجل بنجاح. إجمالي الوجبات: {$allocation->fresh()->total_meals}",
            ]);

        } catch (\Exception $e) {
            Log::error('MilkAllocation update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * حذف سجل
     */
    public function destroy($id)
    {
        try {
            $allocation = MilkAllocation::findOrFail($id);
            $allocation->delete();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف السجل بنجاح',
            ]);

        } catch (\Exception $e) {
            Log::error('MilkAllocation destroy error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'تعذر حذف السجل',
            ], 500);
        }
    }
}