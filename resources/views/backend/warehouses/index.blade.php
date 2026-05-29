@extends('backend.app')
@section('title', 'إدارة المخازن')
@section('breadcrumb-title', 'إدارة الموقع')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">المخازن</li>
@endsection

@push('custom-css')
    {{-- يفضل مستقبلاً نقل هذه الملفات لمجلد خاص بـ warehouses --}}
    @include('backend.warehouses.partials.styles')
    <style>
        .card-title i {
            color: #17a2b8;
        }

        /* تغيير اللون للأزرق لتمييز قسم المناطق */
        .btn-sm {
            border-radius: 4px;
            font-weight: 600;
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

        <div class="card card-outline card-info"> {{-- لون info (أزرق) مناسب للمناطق الجغرافية --}}
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-map-marker-alt ml-2"></i>
                    قائمة المخازن المسجلة
                </h3>
                <div class="card-tools d-flex">
                    @include('backend.warehouses.partials.export-buttons')
                    <button type="button" class="btn btn-info btn-sm mr-2" data-toggle="modal"
                        data-target="#addWarehouseModal">
                        <i class="fas fa-plus"></i> إضافة مخزن جديدة
                    </button>
                </div>
            </div>
            <div class="card-body">
                @include('backend.warehouses.partials.table')
            </div>
        </div>
    </div>

    {{-- المودالات الخاصة بالمخازن --}}
    @include('backend.warehouses.partials.modals.add')
    @include('backend.warehouses.partials.modals.edit')
@endsection

@push('custom-js')
    {{-- لا تضع روابط بوتستراب هنا لأننا وضعناها في الـ Layout --}}

    {{-- 1. استدعاء ملفات السكريبت الخاصة بالمخازن فقط --}}
    @include('backend.warehouses.partials.scripts.datatable')
    @include('backend.warehouses.partials.scripts.modals')
    @include('backend.warehouses.partials.scripts.exports')

    {{-- 2. كود الصفحة الصغير --}}
    <script>
        $(document).ready(function() {
            // كود المودال في حالة وجود أخطاء Validation
            @if ($errors->any())
                $('#addGovernorateModal').modal('show');
            @endif
        });
    </script>
@endpush
