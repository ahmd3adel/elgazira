<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $departmentId = $this->route('department'); // الحصول على ID الإدارة الحالية

        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('departments')->ignore($departmentId)],
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