<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWarehouseRequest extends FormRequest
{
    /**
     * تحديد ما إذا كان المستخدم مصرح له بإجراء هذا الطلب.
     */
    public function authorize(): bool
    {
        return true; // اجعلها true لتفعيل الـ Validation
    }

    /**
     * قواعد التحقق من البيانات.
     */
    public function rules(): array
    {
        return [
            'name'           => 'required|string|max:255',
            
            
            // نوع المخزن يجب أن يكون واحداً من الثلاثة المتفق عليها
            'type'           => 'in:main,sub,dispatch_point',
            
            /**
             * المنطق الشرطي:
             * 1. المحافظة مطلوبة فقط إذا كان نوع المخزن "رئيسي" (main)
             */
            'governorate_id' => 'required_if:type,main|nullable|exists:governorates,id',
            
            /**
             * 2. المخزن الأب مطلوب إذا كان النوع "فرعي" (sub) أو "نقطة توزيع" (dispatch_point)
             * أي: مطلوب طالما أن النوع "ليس" رئيسياً.
             */
            'parent_id'      => 'required_unless:type,main|nullable|exists:warehouses,id',
            
            // باقي الحقول اختيارية
            'manager_name'   => 'nullable|string|max:255',
            'manager_phone'  => 'nullable|string|max:20',
            'address'        => 'nullable|string',
            'status'         => 'required|boolean',
        ];
    }

    /**
     * تخصيص رسائل الخطأ لتظهر بالعربية.
     */
    public function messages(): array
    {
        return [
            'name.required'             => 'يرجى إدخال اسم المخزن.',
            'code.required'             => 'كود المخزن حقل إلزامي.',
            'code.unique'               => 'كود المخزن هذا مسجل مسبقاً، يرجى استخدام كود فريد.',
            'type.required'             => 'يرجى تحديد نوع المخزن.',
            'governorate_id.required_if' => 'المخزن الرئيسي يجب أن يتبع محافظة.',
            'parent_id.required_unless'  => 'المخزن الفرعي أو نقطة التوزيع يجب ربطها بمخزن رئيسي.',
            'governorate_id.exists'      => 'المحافظة المختارة غير موجودة.',
            'parent_id.exists'           => 'المخزن الرئيسي المختار غير موجود.',
        ];
    }
}