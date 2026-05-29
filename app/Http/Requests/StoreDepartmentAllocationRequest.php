<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreDepartmentAllocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'department_id' => 'required|exists:departments,id',
            'order_date' => 'required|date|before_or_equal:today',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1|max:999999',
        ];
    }

    public function messages(): array
    {
        return [
            'department_id.required' => 'يرجى اختيار الإدارة',
            'department_id.exists' => 'الإدارة المحددة غير موجودة',
            'order_date.required' => 'يرجى تحديد تاريخ الصرف',
            'order_date.before_or_equal' => 'لا يمكن اختيار تاريخ مستقبلي',
            'items.required' => 'يجب إضافة صنف واحد على الأقل',
            'items.min' => 'يجب إضافة صنف واحد على الأقل',
            'items.*.product_id.required' => 'يرجى اختيار المنتج',
            'items.*.quantity.required' => 'يرجى إدخال الكمية',
            'items.*.quantity.min' => 'الكمية يجب أن تكون 1 على الأقل',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'يرجى التحقق من البيانات المدخلة',
            'errors' => $validator->errors()
        ], 422));
    }
}