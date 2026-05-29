<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index(Request $request)
{
    if ($request->ajax()) {
        // 1. استخدمنا with('suppliers') لتحميل الموردين مع المنتجات بسرعة
        $data = Product::with('suppliers')->latest()->get();

        return DataTables::of($data)
            ->addIndexColumn()
            // عمود الحالة
            ->addColumn('status', function ($row) {
                return $row->status
                    ? '<span class="badge badge-success">نشط</span>'
                    : '<span class="badge badge-danger">غير نشط</span>';
            })
            // عمود الموردين (تعديل التسمية لتكون منطقية)
            ->addColumn('suppliers_names', function ($product) {
                $labels = '';
                foreach ($product->suppliers as $supplier) {
                    $labels .= '<span class="badge badge-info">' . $supplier->name . '</span> ';
                }
                return $labels ?: '<span class="text-muted">غير محدد</span>';
            })
            ->rawColumns(['status', 'suppliers_names']) 
            ->make(true);
    }

    return view('backend.products.index');
}

    public function getSuppliers()
    {
        $suppliers = Supplier::with('suppliers')->get();

        return datatables()->of($suppliers)
            ->addColumn('products_names', function ($suppliers) {
                // هنا بنجمع أسماء الموردين ونفصل بينهم بفاصلة
                return $suppliers->productss->pluck('name')->implode(', ') ?: 'لا يوجد منتجات';
            })
            ->make(true);
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
    public function store(StoreProductRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        //
    }
}
