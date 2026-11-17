<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMilkAllocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'department_id'    => 'required|exists:departments,id',
            'allocation_date'  => 'required|date',
            'biscuit_cartons'  => 'required|integer|min:1|max:100000',
            'notes'            => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'department_id.required'   => 'يجب اختيار الإدارة',
            'department_id.exists'     => 'الإدارة غير موجودة',
            'allocation_date.required' => 'يجب إدخال التاريخ',
            'biscuit_cartons.required' => 'يجب إدخال كمية السادة',
            'biscuit_cartons.min'      => 'الكمية يجب أن تكون 1 على الأقل',
            'biscuit_cartons.integer'  => 'الكمية يجب أن تكون رقم صحيح',
        ];
    }
}