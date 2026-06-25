<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWarehouseRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:warehouses,name',
            'code' => 'required|string|max:50|unique:warehouses,code',
            'type' => 'required|in:main,sub,dispatch_point',
            'governorate_id' => 'required_if:type,main|nullable|exists:governorates,id',
            'parent_id' => 'required_if:type,sub,dispatch_point|nullable|exists:warehouses,id',
            'manager_name' => 'nullable|string|max:255',
            'manager_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'status' => 'boolean',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'اسم المخزن مطلوب',
            'name.unique' => 'اسم المخزن موجود بالفعل',
            'code.required' => 'كود المخزن مطلوب',
            'code.unique' => 'كود المخزن موجود بالفعل',
            'governorate_id.required_if' => 'يرجى اختيار المحافظة للمخزن الرئيسي',
            'parent_id.required_if' => 'يرجى اختيار المخزن الرئيسي للمخزن الفرعي',
        ];
    }
}