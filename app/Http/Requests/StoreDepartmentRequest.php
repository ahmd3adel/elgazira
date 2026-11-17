<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // تأكد من أن المستخدم لديه صلاحية
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:50|unique:departments,code',
            'name' => 'required|string|max:255',
            'governorate_id' => 'nullable|exists:governorates,id',
            'main_warehouse_id' => 'nullable|exists:warehouses,id',
            'operation_warehouse_id' => 'nullable|exists:warehouses,id',
            'manager_name' => 'nullable|string|max:255',
            'manager_phone' => 'nullable|string|max:20',
            'status' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ];
    }
}