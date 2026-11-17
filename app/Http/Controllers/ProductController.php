<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Supplier;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
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
            $data = Product::with('suppliers')->latest()->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('status', function ($row) {
                    return $row->status
                        ? '<span class="badge badge-success">نشط</span>'
                        : '<span class="badge badge-danger">غير نشط</span>';
                })
                ->addColumn('suppliers_names', function ($product) {
                    $labels = '';
                    foreach ($product->suppliers as $supplier) {
                        $labels .= '<span class="badge badge-info">' . $supplier->name . '</span> ';
                    }
                    return $labels ?: '<span class="text-muted">غير محدد</span>';
                })
                ->addColumn('expiry_status', function ($product) {
                    if (!$product->expiry_date) {
                        return '<span class="badge badge-secondary">غير محدد</span>';
                    }
                    
                    $now = now();
                    $expiry = \Carbon\Carbon::parse($product->expiry_date);
                    $daysLeft = $now->diffInDays($expiry, false);
                    
                    if ($daysLeft < 0) {
                        return '<span class="badge badge-danger">منتهي الصلاحية</span>';
                    } elseif ($daysLeft <= 30) {
                        return '<span class="badge badge-warning">ينتهي خلال ' . $daysLeft . ' يوم</span>';
                    } else {
                        return '<span class="badge badge-success">صالحة</span>';
                    }
                })
                ->addColumn('action', function ($row) {
                    return '
                        <button class="btn btn-sm btn-primary edit-product" data-id="' . $row->id . '" title="تعديل">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger delete-product" data-id="' . $row->id . '" data-name="' . $row->name . '" title="حذف">
                            <i class="fas fa-trash"></i>
                        </button>
                    ';
                })
                ->rawColumns(['status', 'suppliers_names', 'expiry_status', 'action'])
                ->make(true);
        }

        $suppliers = Supplier::where('status', 1)->get();
        return view('backend.products.index', compact('suppliers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        try {
            // إنشاء المنتج مع مطابقة الأعمدة الجديدة في الـ Migration
            $product = Product::create([
                'name' => $request->name,
                'sku' => $request->code, // ربط حقل الـ code القادم من الفورم بعمود sku في الجدول
                'description' => $request->description,
                'quantity' => $request->quantity ?? 0,
                'price' => $request->price ?? 0,
                'purchase_unit' => $request->purchase_unit ?? 'كرتونة',
                'issue_unit' => $request->issue_unit ?? 'وجبة',
                'conversion_factor' => $request->conversion_factor ?? 1,
                'expiry_duration' => $request->expiry_duration ?? 6,
                'expiry_date' => $request->expiry_date,
                'companion_product_id' => $request->companion_product_id,
                'status' => $request->status ?? 1,
            ]);

            // ربط الموردين بالمنتج (علاقة Many-to-Many)
            if ($request->has('suppliers') && is_array($request->suppliers)) {
                $product->suppliers()->sync($request->suppliers);
            }

            return redirect()->route('admin.products.index')
                ->with('success', 'تم إضافة المنتج بنجاح.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'حدث خطأ: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        if (request()->ajax()) {
            $product->load('suppliers');
            
            // تحويل البيانات لملء حقل الـ code في الـ Modal من عمود sku
            $data = $product->toArray();
            $data['code'] = $product->sku; 

            return response()->json([
                'success' => true,
                'data' => $data,
                'supplier_ids' => $product->suppliers->pluck('id')
            ]);
        }

        $suppliers = Supplier::where('status', 1)->get();
        return view('backend.products.edit', compact('product', 'suppliers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        try {
            // تحديث المنتج
            $product->update([
                'name' => $request->name,
                'sku' => $request->code, // تحديث الـ sku بناءً على الـ code المُدخل
                'description' => $request->description,
                'quantity' => $request->quantity ?? 0,
                'price' => $request->price ?? 0,
                'purchase_unit' => $request->purchase_unit ?? 'كرتونة',
                'issue_unit' => $request->issue_unit ?? 'وجبة',
                'conversion_factor' => $request->conversion_factor ?? 1,
                'expiry_duration' => $request->expiry_duration ?? 6,
                'expiry_date' => $request->expiry_date,
                'companion_product_id' => $request->companion_product_id,
                'status' => $request->status ?? 1,
            ]);

            // تحديث الموردين
            if ($request->has('suppliers') && is_array($request->suppliers)) {
                $product->suppliers()->sync($request->suppliers);
            } else {
                $product->suppliers()->detach();
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم تحديث المنتج بنجاح.'
                ]);
            }

            return redirect()->route('admin.products.index')
                ->with('success', 'تم تحديث المنتج بنجاح.');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()
                ->with('error', 'حدث خطأ: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        try {
            $product->suppliers()->detach();
            $product->delete();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم حذف المنتج بنجاح.'
                ]);
            }

            return redirect()->route('admin.products.index')
                ->with('success', 'تم حذف المنتج بنجاح.');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }
}