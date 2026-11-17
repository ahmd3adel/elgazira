<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:products,sku', // التحقق من عدم تكرار الـ sku
            'quantity' => 'nullable|integer|min:0',
            'price' => 'nullable|numeric|min:0',
            'purchase_unit' => 'nullable|string|max:50',
            'issue_unit' => 'nullable|string|max:50',
            'conversion_factor' => 'nullable|integer|min:1',
            'expiry_duration' => 'nullable|integer|min:0',
            'expiry_date' => 'nullable|date',
            'companion_product_id' => 'nullable|exists:products,id',
            'status' => 'nullable|boolean',
            'suppliers' => 'nullable|array',
            'suppliers.*' => 'exists:suppliers,id',
        ];
    }

    public function messages()
    {
        return [
            'code.required' => 'كود المنتج مطلوب',
            'code.unique' => 'هذا الكود مستخدم بالفعل',
            'name.required' => 'اسم المنتج مطلوب',
            'expiry_date.after' => 'تاريخ الصلاحية يجب أن يكون بعد اليوم',
            'suppliers.*.exists' => 'المورد غير موجود',
        ];
    }
}