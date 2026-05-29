@extends('backend.app')
@section('title', ' الشحنات')
@section('breadcrumb-title', 'إدارة الموقع')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">الارصدة</li>
@endsection

@push('custom-css')
    {{-- يفضل مستقبلاً نقل هذه الملفات لمجلد خاص بـ inventories --}}
    @include('backend.inventories.partials.styles')
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
                    قائمة الارصدة
                </h3>
                {{-- <div class="card-tools d-flex">
                    @include('backend.inventories.partials.export-buttons')
                    <button type="button" class="btn btn-info btn-sm mr-2" data-toggle="modal"
                        data-target="#addDepartmentModal">
                        <i class="fas fa-plus"></i> إضافة شحنة جديدة
                    </button>
                </div> --}}
            </div>
            <div class="card-body">
                <table class="table table-bordered text-center align-middle">
    <thead class="bg-dark text-white">
        <tr>
            <th rowspan="2">المخازن</th>
            <th colspan="{{ $products->count() }}">الأصناف والمنتجات (بالكرتونة)</th>
            <th rowspan="2" class="bg-info">إجمالي الرصيد</th>
            <th rowspan="2">ميزان الوجبة</th>
        </tr>
        <tr>
            @foreach($products as $product)
                <th>{{ $product->name }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @php $grandTotal = 0; @endphp
        @foreach($warehouses as $warehouse)
            @php $rowTotal = 0; @endphp
            <tr>
                <td class="text-right">
                    <strong>{{ $warehouse->name }}</strong>
                    @if($warehouse->type == 'main')
                        <span class="badge badge-primary float-left">رئيسي</span>
                    @else
                        <span class="badge badge-secondary float-left">فرعي</span>
                    @endif
                </td>

                {{-- عرض كمية كل صنف --}}
                @foreach($products as $product)
                    @php 
                        $qty = $inventoryMap[$warehouse->id][$product->id] ?? 0; 
                        $rowTotal += $qty;
                    @endphp
                    <td class="{{ $qty > 0 ? 'text-primary font-weight-bold' : 'text-muted' }}">
                        {{ $qty > 0 ? number_format($qty) : '-' }}
                    </td>
                @endforeach

                {{-- إجمالي الرصيد للمخزن --}}
                <td class="bg-light font-weight-bold">{{ number_format($rowTotal) }}</td>

                {{-- ميزان الوجبة (مثال لمنطق الألوان) --}}
                <td>
                    @if($rowTotal > 0)
                        <span class="badge badge-warning p-2">⚠️ فائض</span>
                    @else
                        <span class="badge badge-success p-2">✅ متوازن</span>
                    @endif
                </td>
            </tr>
            @php $grandTotal += $rowTotal; @endphp
        @endforeach
    </tbody>
    
    {{-- السطر الأخير: إجمالي المنتج لكل المخازن --}}
    <tfoot class="bg-light font-weight-bold">
        <tr>
            <td>إجمالي المنتج (كل المخازن)</td>
            @foreach($products as $product)
                @php 
                    $colTotal = \App\Models\Inventory::where('product_id', $product->id)->sum('quantity');
                @endphp
                <td>{{ number_format($colTotal) }}</td>
            @endforeach
            <td class="bg-info text-white">{{ number_format($grandTotal) }}</td>
            <td>--</td>
        </tr>
    </tfoot>
</table>
        </div>
    </div>

    {{-- المودالات الخاصة بالمحافظات --}}
    @include('backend.inventories.partials.modals.add')
    @include('backend.inventories.partials.modals.edit')
@endsection

@push('custom-js')
    {{-- لا تضع روابط بوتستراب هنا لأننا وضعناها في الـ Layout --}}

    {{-- 1. استدعاء ملفات السكريبت الخاصة بالمحافظات فقط --}}
    @include('backend.inventories.partials.scripts.datatable')
    @include('backend.inventories.partials.scripts.modals')
    @include('backend.inventories.partials.scripts.exports')

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
