@extends('backend.app')
@section('title', 'إدارة الادارات')
@section('breadcrumb-title', 'إدارة الموقع')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">الادارات</li>
@endsection

@push('custom-css')
    {{-- يفضل مستقبلاً نقل هذه الملفات لمجلد خاص بـ department_allocations --}}
    @include('backend.department_allocations.partials.styles')
    <style>
        .card-title i {
            color: #17a2b8;
        }

        /* تغيير اللون للأزرق لتمييز قسم المناطق */
        .btn-sm {
            border-radius: 4px;
            font-weight: 600;
        }
        
        /* تحسين مظهر الفلتر */
        .filter-section {
            background-color: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }
        
        .filter-section label {
            margin-bottom: 5px;
            font-size: 14px;
        }
        
        .select2-container--default .select2-selection--multiple {
            border-color: #ced4da;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        {{-- تنبيهات العمليات --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle ml-1"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-map-marker-alt ml-2"></i>
                    قائمة التوزيعات 
                </h3>
                <div class="card-tools d-flex">
                    @include('backend.department_allocations.partials.export-buttons')
                    <button type="button" class="btn btn-info btn-sm mr-2" data-toggle="modal"
                        data-target="#addDistributionAllocationsModal">
                        <i class="fas fa-plus"></i> إضافة توزيع جديد
                    </button>
                </div>
            </div>
            
            <div class="card-body">
                {{-- قسم الفلتر --}}
                <div class="filter-section">
                    <div class="row align-items-end">
                        <!-- فلتر الإدارة -->
                        <div class="col-md-3">
                            <label class="font-weight-bold">الإدارة:</label>
                            <select id="department_filter" class="form-control filter-input select2" multiple="multiple">
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- فلتر من تاريخ -->
                        <div class="col-md-3">
                            <label class="font-weight-bold">من تاريخ:</label>
                            <input type="date" id="from_date" class="form-control filter-input">
                        </div>

                        <!-- فلتر إلى تاريخ -->
                        <div class="col-md-3">
                            <label class="font-weight-bold">إلى تاريخ:</label>
                            <input type="date" id="to_date" class="form-control filter-input">
                        </div>

                        <!-- زرار المسح -->
                        <div class="col-md-3">
                            <button id="reset_button" class="btn btn-outline-danger btn-block">
                                <i class="fas fa-sync-alt"></i> إعادة تعيين
                            </button>
                        </div>
                    </div>
                </div>

                {{-- الجدول --}}
                @include('backend.department_allocations.partials.table')
            </div>
        </div>
    </div>

    {{-- المودالات الخاصة بالادارات --}}
    @include('backend.department_allocations.partials.modals.add')
    {{-- @include('backend.department_allocations.partials.modals.edit') --}}
@endsection

@push('custom-js')
    {{-- 1. استدعاء ملفات السكريبت الخاصة بالادارات فقط --}}
    @include('backend.department_allocations.partials.scripts.datatable')
    @include('backend.department_allocations.partials.scripts.modals')
    @include('backend.department_allocations.partials.scripts.exports')

    {{-- 2. كود الصفحة الصغير --}}
    <script>
        $(document).ready(function() {
            // كود المودال في حالة وجود أخطاء Validation
            @if ($errors->any())
                $('#addDistributionAllocationsModal').modal('show');
            @endif
            
            // تحسين ظهور الـ Select2
            $('.select2').select2({
                theme: 'bootstrap4',
                placeholder: 'اختر الإدارات',
                allowClear: true
            });
        });
    </script>
@endpush