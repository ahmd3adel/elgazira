<?php

namespace App\Http\Controllers;

use App\Models\DistributionPointStock;
use App\Models\Product;
use App\Models\School;
use Illuminate\Http\Request;

class DistributionPointStockController extends Controller
{
    /**
     * عرض أرصدة جميع نقاط التوزيع (المدارس)
     */
    public function index()
    {
        // 1. جلب كافة المنتجات مرتبة أبجدياً
        $products = Product::orderBy('name')->get();

        // 2. جلب المدارس التي تعتبر نقاط توزيع
        $distributionPoints = School::orderBy('name')->get();

        // 3. بناء خريطة الأرصدة: [school_id][product_id] = quantity
        // نستخدم eager loading لتحسين الأداء إذا لزم الأمر، لكن هنا نجلب الكل لتجهيز المصفوفة
        $stockMap = DistributionPointStock::all()
            ->groupBy('school_id')
            ->map(function ($items) {
                return $items->keyBy('product_id')->map->current_stock;
            });

        // 4. التوجه لصفحة العرض مع البيانات
        return view('backend.distribution_point_stocks.index', compact(
            'products', 
            'distributionPoints', 
            'stockMap'
        ));
    }

    /**
     * عرض تفاصيل رصيد نقطة توزيع محددة (اختياري)
     */
    public function show($id)
    {
        $school = School::with(['distributionPointStocks.product'])->findOrFail($id);
        return view('backend.distribution_point_stocks.show', compact('school'));
    }

    // ملاحظة: دوال (store, update, destroy) غالباً لن تحتاجها هنا 
    // لأن الأرصدة تُحدث تلقائياً عند تنفيذ أوامر التوزيع (Distribution Orders)
}