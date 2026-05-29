<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Product;

class StockTransferRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
            'type' => 'required|in:permanent,custody',
            'items' => 'required|array|min:2', // على الأقل صنفين (أساسي وتام)
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ];
    }

    /**
     * التحقق الإضافي بعد القواعد الأساسية
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $this->validateProductBalance($validator);
        });
    }

    /**
     * التحقق من توازن الأصناف
     */
    private function validateProductBalance($validator)
    {
        $items = $this->input('items', []);
        $baseProductId = null;
        $baseQuantity = 0;
        $otherProductsTotal = 0;
        $otherProductsDetails = [];

        // 1. البحث عن الصنف الأساسي وجمع بيانات الأصناف الأخرى
        foreach ($items as $index => $item) {
            $product = Product::find($item['product_id']);
            
            if (!$product) {
                $validator->errors()->add('items', "المنتج غير موجود");
                return;
            }

            if ($product->is_base) {
                $baseProductId = $product->id;
                $baseQuantity = $item['quantity'];
            } else {
                // تحويل كمية الأصناف الأخرى من وجبات إلى كراتين (إلى ما يعادلها من الأساسي)
                // إذا كان الصنف الآخر يحتاج إلى رغيف سادة واحد لكل وجبة، فالكمية تساوي عدد الوجبات
                $otherProductsTotal += $item['quantity'];
                $otherProductsDetails[] = [
                    'name' => $product->name,
                    'quantity' => $item['quantity']
                ];
            }
        }

        // 2. التأكد من وجود صنف أساسي في التحويل
        if (!$baseProductId) {
            $validator->errors()->add('items', 'يجب إضافة الصنف الأساسي (سادة 40) إلى عملية التحويل');
            return;
        }

        // 3. التأكد من وجود أصناف أخرى
        if (empty($otherProductsDetails)) {
            $validator->errors()->add('items', 'يجب إضافة صنف تام واحد على الأقل مع الصنف الأساسي');
            return;
        }

        // 4. مقارنة الكميات
        if ($baseQuantity != $otherProductsTotal) {
            $difference = $baseQuantity - $otherProductsTotal;
            $differenceAbs = abs($difference);
            
            if ($difference > 0) {
                $message = "⚠️ عدم توازن في الكميات: كمية الصنف الأساسي ($baseQuantity) أكبر من إجمالي الأصناف التامة ($otherProductsTotal) بزيادة قدرها $differenceAbs";
            } else {
                $message = "⚠️ عدم توازن في الكميات: كمية الصنف الأساسي ($baseQuantity) أقل من إجمالي الأصناف التامة ($otherProductsTotal) بنقص قدره $differenceAbs";
            }
            
            $validator->errors()->add('items', $message);
        }
    }

    /**
     * تخصيص رسائل الخطأ
     */
    public function messages(): array
    {
        return [
            'items.required' => 'يجب إضافة منتج واحد على الأقل',
            'items.min' => 'يجب إضافة على الأقل صنفين (أساسي وتام)',
            'items.*.product_id.required' => 'اختر المنتج',
            'items.*.quantity.min' => 'الكمية يجب أن تكون 1 على الأقل',
            'from_warehouse_id.required' => 'اختر المخزن المصدر',
            'to_warehouse_id.required' => 'اختر المخزن المستلم',
            'to_warehouse_id.different' => 'المخزن المصدر والمستلم يجب أن يكونا مختلفين',
        ];
    }
}