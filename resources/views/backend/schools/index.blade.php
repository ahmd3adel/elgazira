@extends('backend.app')
@section('title', 'إدارة المدارس')
@section('breadcrumb-title', 'إدارة الموقع')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">المدارس</li>
@endsection

@push('custom-css')
    {{-- يفضل مستقبلاً نقل هذه الملفات لمجلد خاص بـ schools --}}
    @include('backend.schools.partials.styles')
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
                    قائمة المدارس المسجلة
                </h3>
                <div class="card-tools d-flex">
                    @include('backend.schools.partials.export-buttons')
                    <button type="button" class="btn btn-info btn-sm mr-2" data-toggle="modal"
                        data-target="#addSchoolModal">
                        <i class="fas fa-plus"></i> إضافة مدرسة جديدة
                    </button>
                </div>
            </div>
            <div class="card-body">
                @include('backend.schools.partials.table')
            </div>
        </div>
    </div>

    {{-- المودالات الخاصة بالمدارس --}}
    @include('backend.schools.partials.modals.add')
    @include('backend.schools.partials.modals.edit')
@endsection

@push('custom-js')
    {{-- لا تضع روابط بوتستراب هنا لأننا وضعناها في الـ Layout --}}

    {{-- 1. استدعاء ملفات السكريبت الخاصة بالمدارس فقط --}}
    @include('backend.schools.partials.scripts.datatable')
    @include('backend.schools.partials.scripts.modals')
    @include('backend.schools.partials.scripts.exports')

    {{-- 2. كود الصفحة الصغير --}}
    <script>
        $(document).ready(function() {
            // كود المودال في حالة وجود أخطاء Validation
            @if ($errors->any())
                $('#addProductModal').modal('show');
            @endif
        });
    </script>
@endpush
