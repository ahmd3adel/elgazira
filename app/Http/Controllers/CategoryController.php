<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class CategoryController extends Controller
{
    /**
     * Display a listing of the categories
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $categories = Category::withCount('subCategories')
                ->orderBy('order', 'asc')
                ->orderBy('id', 'desc')
                ->select('categories.*');
            
            return DataTables::of($categories)
                ->addColumn('name_display', function($row) {
                    // عرض اسم المهنة مع مسافة بادئة إذا كانت فرعية
                    $prefix = '';
                    if ($row->parent_id) {
                        $prefix = '&nbsp;&nbsp;&nbsp;↳ ';
                    }
                    return $prefix . ($row->name_ar ?? $row->name_en);
                })
                ->addColumn('icon_html', function($row) {
                    $icon = $row->icon ?? 'fa-briefcase';
                    $color = $row->icon_color ?? '#0066cc';
                    return '<div class="icon-circle" style="background-color: ' . $color . '; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                                <i class="fas ' . $icon . '" style="color: white; font-size: 1.2rem;"></i>
                            </div>';
                })
                ->addColumn('sub_professions_count', function($row) {
                    $count = $row->sub_categories_count ?? $row->subCategories()->count();
                    if ($count > 0) {
                        return '<span class="badge badge-info" style="font-size: 14px; padding: 6px 12px;">
                                    <i class="fas fa-folder-open"></i> ' . $count . ' تخصص
                                </span>';
                    }
                    return '<span class="badge badge-secondary" style="font-size: 14px; padding: 6px 12px;">
                                <i class="fas fa-folder"></i> لا يوجد
                            </span>';
                })
                ->addColumn('status_html', function($row) {
                    if ($row->is_active) {
                        return '<span class="badge badge-success" style="font-size: 13px; padding: 6px 14px; border-radius: 20px;">
                                    <i class="fas fa-check-circle"></i> نشط
                                </span>';
                    }
                    return '<span class="badge badge-danger" style="font-size: 13px; padding: 6px 14px; border-radius: 20px;">
                                <i class="fas fa-times-circle"></i> معطل
                            </span>';
                })
                ->addColumn('action', function($row) {
                    $editBtn = '<button class="btn btn-sm btn-info edit-profession" data-id="'.$row->id.'" data-name="'.e($row->name_ar).'" style="margin: 0 3px;">
                                    <i class="fas fa-edit"></i>
                                </button>';
                    $deleteBtn = '<button class="btn btn-sm btn-danger delete-profession" data-id="'.$row->id.'" data-name="'.e($row->name_ar).'" style="margin: 0 3px;">
                                    <i class="fas fa-trash"></i>
                                </button>';
                    return '<div class="btn-group" role="group">' . $editBtn . $deleteBtn . '</div>';
                })
                ->addColumn('created_at_formatted', function($row) {
                    return $row->created_at ? $row->created_at->format('Y-m-d') : '—';
                })
                ->rawColumns(['icon_html', 'sub_professions_count', 'status_html', 'action', 'name_display'])
                ->make(true);
        }
        
        return view('backend.categories.index');
    }

    /**
     * Get parent categories for select dropdown
     */

    public function showParentCategories()
{
    return view('backend.subcategories'); // اسم ملف الـ Blade الخاص بالمهن الرئيسية
}

public function getParentCategories()
{
    // التأكد من جلب الفئات الرئيسية فقط
    $categories = Category::where(function($query) {
            $query->whereNull('parent_id')
                  ->orWhere('parent_id', 0)
                  ->orWhere('parent_id', '')
                  ->orWhereRaw('CAST(parent_id AS UNSIGNED) = 0');
        })
        ->where('name_ar', '!=', '') // استبعاد الأسماء الفارغة
        ->orderByRaw('CAST(`order` AS UNSIGNED) ASC')
        ->orderBy('name_ar', 'asc')
        ->get(['id', 'name_ar as text']);
    
    // فلترة إضافية لإزالة الأسماء الإنجليزية المشبوهة
    $filtered = $categories->filter(function($category) {
        // الاحتفاظ فقط بالأسماء التي تحتوي على أحرف عربية أو أسماء معقولة
        return preg_match('/[\x{0600}-\x{06FF}]/u', $category->text) 
               || strlen($category->text) < 20; // أو تصفية حسب الطول
    });
    
    return response()->json([
        'success' => true,
        'data' => $filtered->values()
    ]);
}

    /**
     * Store a newly created category
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name_ar' => 'required|string|max:255',
                'name_en' => 'nullable|string|max:255',
                'slug_ar' => 'nullable|string|unique:categories,slug_ar',
                'slug_en' => 'nullable|string|unique:categories,slug_en',
                'icon' => 'nullable|string',
                'icon_color' => 'nullable|string',
                'parent_id' => 'nullable|exists:categories,id',
                'is_active' => 'boolean',
                'order' => 'integer',
                'description' => 'nullable|string'
            ]);

            // إنشاء slug تلقائي إذا لم يتم إدخاله
            if (!$request->slug_ar && $request->name_ar) {
                $request->merge(['slug_ar' => Str::slug($request->name_ar)]);
            }
            
            if (!$request->slug_en && $request->name_en) {
                $request->merge(['slug_en' => Str::slug($request->name_en)]);
            }

            $category = Category::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'تم إضافة المهنة بنجاح',
                'data' => $category
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified category
     */
    public function show($id)
    {
        try {
            $category = Category::with('parent')->findOrFail($id);
            return response()->json($category);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'المهنة غير موجودة'
            ], 404);
        }
    }

    /**
     * Update the specified category
     */
    public function update(Request $request, $id)
    {
        try {
            $category = Category::findOrFail($id);
            
            $request->validate([
                'name_ar' => 'required|string|max:255',
                'name_en' => 'nullable|string|max:255',
                'slug_ar' => 'nullable|string|unique:categories,slug_ar,'.$id,
                'slug_en' => 'nullable|string|unique:categories,slug_en,'.$id,
                'icon' => 'nullable|string',
                'icon_color' => 'nullable|string',
                'parent_id' => 'nullable|exists:categories,id',
                'is_active' => 'boolean',
                'order' => 'integer',
                'description' => 'nullable|string'
            ]);

            // منع جعل القسم نفسه أباً لنفسه
            if ($request->parent_id == $id) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن جعل القسم أباً لنفسه'
                ], 422);
            }

            $category->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث المهنة بنجاح',
                'data' => $category
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified category
     */
    public function destroy($id)
    {
        try {
            $category = Category::findOrFail($id);
            
            // التحقق من وجود تخصصات فرعية
            if ($category->subCategories()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن حذف هذه المهنة لأنها تحتوي على ' . $category->subCategories()->count() . ' تخصصات فرعية'
                ], 400);
            }
            
            $category->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'تم حذف المهنة بنجاح'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage()
            ], 500);
        }
    }
}