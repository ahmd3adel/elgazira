<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $productId = $this->route('product');

        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('products')->ignore($productId)],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'quantity' => 'nullable|integer|min:0',
            'price' => 'nullable|numeric|min:0',
            'expiry_date' => 'nullable|date',
            'companion_product_id' => 'nullable|exists:products,id',
            'suppliers' => 'nullable|array',
            'suppliers.*' => 'exists:suppliers,id',
            'status' => 'nullable|boolean',
        ];
    }

    public function messages()
    {
        return [
            'code.required' => 'كود المنتج مطلوب',
            'code.unique' => 'هذا الكود مستخدم بالفعل',
            'name.required' => 'اسم المنتج مطلوب',
            'suppliers.*.exists' => 'المورد غير موجود',
        ];
    }
}