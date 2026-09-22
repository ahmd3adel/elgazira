@php
    $supplierSummary = $supplierData['supplierSummary'] ?? collect();
    $samplesDistribution = $supplierData['samplesDistribution'] ?? collect();
    $totalSummary = $supplierData['totalSummary'] ?? [
        'grand_total_quantity' => 0,
        'total_shipments' => 0,
        'total_samples' => 0,
    ];

    if (!isset($shipmentsStats) || !is_array($shipmentsStats)) {
        $shipmentsStats = [
            'warehouses' => collect(),
            'products' => [],
            'matrix' => [],
        ];
    }

    $warehouses = $shipmentsStats['warehouses'] ?? collect();
    $productsMatrix = $shipmentsStats['products'] ?? [];
    $matrix = $shipmentsStats['matrix'] ?? [];
@endphp

@extends('backend.app')
@section('title', 'إدارة الشحنات')
@section('breadcrumb-title', 'إدارة الموقع')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">الشحنات</li>
@endsection

@push('custom-css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />

    <style>
        .content-wrapper {
            overflow-x: hidden;
        }

        /* تنسيق موحد لجدول DataTable */
        #receiving-orders-table {
            width: 100% !important;
            margin-bottom: 0 !important;
        }

        #receiving-orders-table thead th {
            background-color: #f8f9fc !important;
            border-bottom: 2px solid #e3e6f0 !important;
            color: #4e73df !important;
            font-weight: 600 !important;
            vertical-align: middle !important;
            padding: 12px 8px !important;
        }

        #receiving-orders-table tbody td {
            vertical-align: middle !important;
            padding: 10px 8px !important;
        }

        #receiving-orders-table tbody tr:hover {
            background-color: #f8f9fc !important;
        }

        /* تنسيق المصفوفة */
        .matrix-table thead th {
            background-color: #28a745 !important;
            color: white !important;
            position: sticky;
            top: 0;
            z-index: 10;
            white-space: nowrap;
        }

        .matrix-table tbody th {
            background-color: #f8f9fc !important;
            position: sticky;
            right: 0;
            z-index: 5;
        }

        .bg-warning-light {
            background-color: #fff3cd !important;
        }

        .bg-info-light {
            background-color: #d1ecf1 !important;
        }

        .bg-success-light {
            background-color: #d4edda !important;
        }

        /* كروت الموردين */
        .supplier-mini-card {
            cursor: pointer;
            transition: all 0.2s ease;
            border-radius: 8px;
        }

        .supplier-mini-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        /* شريط العينات */
        .samples-bar {
            background: #fff3cd;
            border-right: 3px solid #ffc107;
            border-radius: 8px;
        }

        /* منع تغيير عرض الجدول عند collapse */
        #receiving-orders-table_wrapper {
            overflow-x: auto;
            width: 100% !important;
        }

        #receiving-orders-table {
            width: 100% !important;
            min-width: 1000px;
        }

        .dataTables_scroll {
            overflow: visible !important;
        }

        .card-body .table-responsive {
            overflow-x: auto !important;
        }

        /* تنسيق معاينة الصورة */
        .receipt-preview {
            max-width: 100px;
            max-height: 60px;
            border-radius: 4px;
            border: 1px solid #ddd;
            padding: 2px;
        }

        .receipt-preview:hover {
            transform: scale(1.1);
            transition: transform 0.2s;
        }

        @media (max-width: 768px) {
            .small-box h3 {
                font-size: 1.2rem;
            }

            .info-box-content .info-box-number {
                font-size: 1rem;
            }
        }

        /* ✅ تنسيق مودال التعديل */
        .modal-header.bg-gradient-warning {
            background: linear-gradient(135deg, #f39c12, #e67e22) !important;
        }

        #editReceivingOrderModal .custom-file-label::after {
            content: "استعراض" !important;
        }

        #editReceivingOrderModal .form-control:focus {
            border-color: #f39c12;
            box-shadow: 0 0 0 0.2rem rgba(243, 156, 18, 0.25);
        }

        #editReceivingOrderModal .form-label {
            font-weight: 600;
            color: #2c3e50;
        }

        /* تنسيق معاينة الصورة */
        .receipt-preview {
            max-width: 120px;
            max-height: 80px;
            border-radius: 6px;
            border: 2px solid #ddd;
            padding: 3px;
            object-fit: cover;
        }

        .receipt-preview:hover {
            transform: scale(1.1);
            transition: transform 0.3s ease;
            border-color: #f39c12;
        }

        /* تحسين عرض الأخطاء */
        .invalid-feedback {
            display: block;
            font-size: 0.875rem;
        }

        /* تنسيق زر الإلغاء */
        .btn-outline-secondary:hover {
            background-color: #6c757d;
            color: white;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid px-2 px-md-3">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle ml-1"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        {{-- إحصائيات سريعة --}}
        <div class="row">
            <div class="col-lg-3 col-md-6 col-12 mb-3">
                <div class="small-box bg-info" style="cursor: pointer;" onclick="scrollToTable()">
                    <div class="inner">
                        <h3>{{ number_format($totalSummary['grand_total_quantity'] ?? 0) }}</h3>
                        <p>إجمالي الكميات المستلمة</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div class="small-box-footer">
                        <i class="fas fa-chart-line"></i> كرتونة
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-12 mb-3">
                <div class="small-box bg-success" style="cursor: pointer;" onclick="scrollToTable()">
                    <div class="inner">
                        <h3>{{ number_format($totalSummary['total_shipments'] ?? 0) }}</h3>
                        <p>إجمالي الشحنات</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-truck"></i>
                    </div>
                    <div class="small-box-footer">
                        <i class="fas fa-calendar-alt"></i> شحنة
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-12 mb-3">
                <div class="small-box bg-warning" style="cursor: pointer;" onclick="scrollToTable()">
                    <div class="inner">
                        <h3>{{ number_format($totalSummary['total_samples'] ?? 0) }}</h3>
                        <p>إجمالي العينات</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-flask"></i>
                    </div>
                    <div class="small-box-footer">
                        <i class="fas fa-vial"></i> عينة
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-12 mb-3">
                <div class="small-box bg-secondary" style="cursor: pointer;"
                    onclick="$('#matrixCard').find('[data-card-widget=\"collapse\"]').click();">
                    <div class="inner">
                        <h3>{{ number_format($warehouses->count() ?? 0) }}</h3>
                        <p>المخازن النشطة</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-warehouse"></i>
                    </div>
                    <div class="small-box-footer">
                        <i class="fas fa-building"></i> {{ number_format(count($productsMatrix)) }} منتج
                    </div>
                </div>
            </div>
        </div>

        {{-- ملخص الموردين --}}
        <div class="row mb-3">
            <div class="col-12">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-truck-moving ml-1"></i>
                            ملخص الشحنات حسب الموردين
                        </h3>
                        <div class="card-tools">
                            <span class="badge badge-primary">{{ count($supplierSummary) }} مورد</span>
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @forelse($supplierSummary as $supplier)
                                <div class="col-lg-3 col-md-4 col-6 mb-3">
                                    <div class="info-box shadow-sm supplier-mini-card"
                                        onclick="filterBySupplier({{ $supplier['supplier_id'] }}, '{{ addslashes($supplier['supplier_name']) }}')">
                                        <div class="info-box-icon bg-primary rounded">
                                            <i class="fas fa-store"></i>
                                        </div>
                                        <div class="info-box-content">
                                            <span
                                                class="info-box-text font-weight-bold">{{ Str::limit($supplier['supplier_name'], 25) }}</span>
                                            <span class="info-box-number">{{ number_format($supplier['total_quantity']) }}
                                                <small>كرتونة</small></span>
                                            <div class="mt-1">
                                                <small class="text-muted">
                                                    <i class="fas fa-ship"></i> {{ $supplier['shipment_count'] }} شحنة
                                                    &nbsp;|&nbsp;
                                                    <i class="fas fa-chart-line"></i>
                                                    {{ number_format($supplier['avg_per_shipment']) }} ك/شحنة
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="alert alert-info text-center mb-0">
                                        <i class="fas fa-info-circle"></i> لا توجد شحنات مسجلة حتى الآن
                                    </div>
                                </div>
                            @endforelse

                            @if (count($supplierSummary) > 0)
                                <div class="col-lg-3 col-md-4 col-6 mb-3">
                                    <div class="info-box bg-gradient-info shadow-sm" onclick="resetFilter()"
                                        style="cursor: pointer;">
                                        <div class="info-box-icon bg-white">
                                            <i class="fas fa-eye text-info"></i>
                                        </div>
                                        <div class="info-box-content">
                                            <span class="info-box-text text-white">عرض الكل</span>
                                            <span class="info-box-number text-white">
                                                <i class="fas fa-list"></i>
                                                {{ number_format($totalSummary['total_shipments'] ?? 0) }}
                                            </span>
                                            <div class="mt-1">
                                                <small class="text-white-50">
                                                    <i class="fas fa-chart-line"></i> إلغاء التصفية
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-4 col-6 mb-3">
                                    <div class="info-box bg-gradient-dark shadow-sm">
                                        <div class="info-box-icon bg-white">
                                            <i class="fas fa-chart-pie text-dark"></i>
                                        </div>
                                        <div class="info-box-content">
                                            <span class="info-box-text text-white">الإجمالي العام</span>
                                            <span
                                                class="info-box-number text-white">{{ number_format($totalSummary['grand_total_quantity'] ?? 0) }}</span>
                                            <div class="mt-1">
                                                <small class="text-white-50">
                                                    <i class="fas fa-trucks"></i>
                                                    {{ number_format($totalSummary['total_shipments'] ?? 0) }} شحنة
                                                    &nbsp;|&nbsp;
                                                    <i class="fas fa-flask"></i>
                                                    {{ number_format($totalSummary['total_samples'] ?? 0) }} عينة
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- توزيع العينات --}}
        @if (count($samplesDistribution) > 0)
            <div class="row mb-3">
                <div class="col-12">
                    <div class="alert alert-warning samples-bar mb-0">
                        <div class="d-flex flex-wrap align-items-center justify-content-between">
                            <div class="mb-2 mb-md-0">
                                <i class="fas fa-flask fa-lg ml-2"></i>
                                <strong>🧪 توزيع العينات حسب المخازن:</strong>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach ($samplesDistribution as $sample)
                                    <span class="badge badge-light p-2">
                                        <i class="fas fa-warehouse text-info"></i>
                                        {{ $sample['warehouse_name'] }}:
                                        <strong
                                            class="text-warning">{{ number_format($sample['samples_count']) }}</strong>
                                    </span>
                                @endforeach
                                <span class="badge badge-success p-2">
                                    <i class="fas fa-chart-line"></i>
                                    الإجمالي: <strong>{{ number_format($totalSummary['total_samples'] ?? 0) }}</strong>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- مصفوفة المخازن × المنتجات --}}
        @if ($warehouses->count() > 0 && count($productsMatrix) > 0)
            <div class="row mb-3">
                <div class="col-12">
                    <div class="card card-success card-outline" id="matrixCard">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-bar ml-1"></i>
                                توزيع الشحنات (المخازن × المنتجات)
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <button type="button" class="btn btn-tool" id="printMatrixBtn">
                                    <i class="fas fa-print"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height: 450px;">
                                <table class="table table-bordered table-hover matrix-table mb-0"
                                    style="min-width: 600px;">
                                    <thead>
                                        <tr>
                                            <th
                                                style="position: sticky; right: 0; background: #28a745; z-index: 20; min-width: 140px;">
                                                <i class="fas fa-warehouse"></i> المخزن / المنتج
                                            </th>
                                            @foreach ($productsMatrix as $product)
                                                <th style="min-width: 100px; text-align: center;">
                                                    {{ \Illuminate\Support\Str::limit($product['name'] ?? 'منتج', 20) }}
                                                    <small
                                                        class="d-block text-white-50">{{ $product['sku'] ?? '' }}</small>
                                                </th>
                                            @endforeach
                                            <th style="min-width: 90px; text-align: center;">
                                                <i class="fas fa-chart-line"></i> الإجمالي
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $grandTotal = 0;
                                            $colTotals = [];
                                            foreach ($productsMatrix as $productId => $product) {
                                                $colTotals[$productId] = 0;
                                            }
                                        @endphp

                                        @foreach ($warehouses as $warehouse)
                                            @php
                                                $warehouseId = is_object($warehouse)
                                                    ? $warehouse->id
                                                    : $warehouse['id'];
                                                $warehouseName = is_object($warehouse)
                                                    ? $warehouse->name
                                                    : $warehouse['name'];
                                                $warehouseCode = is_object($warehouse)
                                                    ? $warehouse->code ?? ''
                                                    : $warehouse['code'] ?? '';
                                            @endphp
                                            <tr>
                                                <th style="position: sticky; right: 0; background: #f8f9fa;">
                                                    <i class="fas fa-building ml-1 text-primary"></i> {{ $warehouseName }}
                                                    @if ($warehouseCode)
                                                        <small class="d-block text-muted">{{ $warehouseCode }}</small>
                                                    @endif
                                                </th>
                                                @php $rowTotal = 0; @endphp
                                                @foreach ($productsMatrix as $productId => $product)
                                                    @php
                                                        $quantity = $matrix[$warehouseId][$productId] ?? 0;
                                                        $rowTotal += $quantity;
                                                        $colTotals[$productId] += $quantity;
                                                        $grandTotal += $quantity;
                                                        $badgeClass =
                                                            $quantity == 0
                                                                ? 'secondary'
                                                                : ($quantity < 100
                                                                    ? 'warning'
                                                                    : ($quantity < 500
                                                                        ? 'info'
                                                                        : 'success'));
                                                    @endphp
                                                    <td class="text-center">
                                                        @if ($quantity > 0)
                                                            <span
                                                                class="badge badge-{{ $badgeClass }} badge-pill px-3 py-2">{{ number_format($quantity) }}</span>
                                                        @else
                                                            <span class="text-muted">—</span>
                                                        @endif
                                                    </td>
                                                @endforeach
                                                <td class="text-center font-weight-bold bg-light">
                                                    {{ number_format($rowTotal) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr style="background: #e9ecef;">
                                            <th class="text-center">الإجمالي الكلي</th>
                                            @foreach ($productsMatrix as $productId => $product)
                                                <th class="text-center">
                                                    @if ($colTotals[$productId] > 0)
                                                        <span
                                                            class="badge badge-primary badge-pill px-3 py-2">{{ number_format($colTotals[$productId]) }}</span>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </th>
                                            @endforeach
                                            <th class="text-center bg-success text-white">{{ number_format($grandTotal) }}
                                            </th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- جدول الشحنات التفصيلي --}}
        <div class="row">
            <div class="col-12">
                <div class="card card-info">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-list-alt ml-1"></i>
                            قائمة الشحنات التفصيلية
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-info btn-sm" data-toggle="modal"
                                data-target="#addReceivingOrderModal">
                                <i class="fas fa-plus"></i> إضافة شحنة
                            </button>
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                            <button type="button" class="btn btn-tool" data-card-widget="remove">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="receiving-orders-table"
                                class="table table-bordered table-striped table-hover w-100">
                                <thead>
                                    <tr>
                                        <th style="width: 50px">#</th>
                                        <th style="width: 110px">تاريخ الوصول</th>
                                        <th>رقم الإذن</th>
                                        <th>رقم التشغيلة</th>
                                        <th style="width: 120px">تاريخ الإنتاج</th>
                                        <th style="width: 120px">تاريخ الانتهاء</th> {{-- ✅ --}}
                                        <th>المورد</th>
                                        <th>المنتج</th>
                                        <th>المخزن</th>
                                        <th style="width: 100px">الكمية</th>
                                        <th style="width: 100px">العينات</th>
                                        <th style="width: 120px">إذن المورد</th>
                                        <th style="width: 130px">العمليات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- مودال الإضافة --}}
    {{-- ============================================ --}}
    <div class="modal fade" id="addReceivingOrderModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-gradient-primary text-white rounded-top">
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-truck me-2"></i> إضافة إذن استلام جديد
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <form id="receivingForm" method="POST" action="{{ route('admin.receiving_orders.store') }}"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-4" style="max-height: 70vh; overflow-y: auto; background: #f8fafc;">
                        <div class="row g-3">

                            <!-- رقم الإذن -->
                            <div class="col-md-6">
                                <label for="document_number" class="form-label fw-semibold">رقم الإذن <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="document_number" id="document_number"
                                    class="form-control shadow-sm" placeholder="PO-2026-001" required>
                            </div>

                            <!-- المورد -->
                            <div class="col-md-6">
                                <label for="supplier_id" class="form-label fw-semibold">المورد <span
                                        class="text-danger">*</span></label>
                                <select name="supplier_id" id="supplier_id"
                                    class="form-select shadow-sm select2-dropdown" required>
                                    <option value="">-- اختر المورد --</option>
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- المخزن -->
                            <div class="col-md-6">
                                <label for="warehouse_id" class="form-label fw-semibold">المخزن <span
                                        class="text-danger">*</span></label>
                                <select name="warehouse_id" id="warehouse_id"
                                    class="form-select shadow-sm select2-dropdown" required>
                                    <option value="">-- اختر المخزن --</option>
                                    @foreach ($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- المنتج -->
                            <div class="col-md-6">
                                <label for="product_id" class="form-label fw-semibold">المنتج <span
                                        class="text-danger">*</span></label>
                                <select name="product_id" id="product_id" class="form-select shadow-sm select2-dropdown"
                                    required>
                                    <option value="">-- اختر المنتج --</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- رقم التشغيلة -->
                            <div class="col-md-6">
                                <label for="batch_number" class="form-label fw-semibold">رقم التشغيلة</label>
                                <input type="text" name="batch_number" id="batch_number"
                                    class="form-control shadow-sm" placeholder="مثال: B-2026-01">
                            </div>

                            <!-- الكمية -->
                            <div class="col-md-3">
                                <label for="quantity" class="form-label fw-semibold">الكمية <span
                                        class="text-danger">*</span></label>
                                <input type="number" name="quantity" id="quantity" class="form-control shadow-sm"
                                    value="600" min="1" required>
                            </div>
                            <!-- تاريخ الإنتاج -->
                            <!-- تاريخ الإنتاج -->
                            <div class="col-md-6">
                                <label for="production_date" class="form-label fw-semibold">
                                    <i class="fas fa-calendar-alt ml-1"></i> تاريخ الإنتاج
                                </label>
                                <input type="date" name="production_date" id="production_date"
                                    class="form-control shadow-sm" value="{{ date('Y-m-d') }}">
                            </div>

                            <!-- تاريخ الانتهاء (للعرض فقط - يُحسب تلقائياً) -->
                            <div class="col-md-6">
                                <label for="expiry_date_display" class="form-label fw-semibold">
                                    <i class="fas fa-hourglass-end ml-1 text-danger"></i> تاريخ الانتهاء
                                </label>
                                <input type="text" id="expiry_date_display" class="form-control shadow-sm bg-light"
                                    readonly placeholder="يُحسب تلقائياً من مدة صلاحية المنتج">
                                <small class="text-muted">يُحسب من: تاريخ الإنتاج + مدة صلاحية المنتج</small>
                            </div>
                            <!-- عدد العينات -->
                            <div class="col-md-3">
                                <label for="samples_quantity" class="form-label fw-semibold">عدد العينات</label>
                                <input type="number" name="samples_quantity" id="samples_quantity"
                                    class="form-control shadow-sm" value="0" min="0">
                            </div>

                            <!-- وقت الوصول -->
                            <div class="col-md-6">
                                <label for="arrival_time" class="form-label fw-semibold">وقت الوصول</label>
                                <input type="datetime-local" name="arrival_time" id="arrival_time"
                                    class="form-control shadow-sm">
                            </div>

                            <!-- وقت المغادرة -->
                            <div class="col-md-6">
                                <label for="departure_time" class="form-label fw-semibold">وقت المغادرة</label>
                                <input type="datetime-local" name="departure_time" id="departure_time"
                                    class="form-control shadow-sm">
                            </div>

                            <!-- ✅ صورة إذن المورد -->
                            <div class="col-12">
                                <label for="supplier_receipt" class="form-label fw-semibold">
                                    <i class="fas fa-file-upload ml-1"></i> صورة إذن المورد
                                </label>
                                <div class="custom-file">
                                    <input type="file" name="supplier_receipt" id="supplier_receipt"
                                        class="custom-file-input form-control shadow-sm" accept=".jpg,.jpeg,.png,.pdf">
                                    <label class="custom-file-label" for="supplier_receipt">اختر ملف</label>
                                </div>
                                <small class="text-muted">الصيغ المدعومة: JPG, PNG, PDF (الحجم الأقصى: 2MB)</small>
                            </div>

                            <!-- الملاحظات -->
                            <div class="col-12">
                                <label for="notes" class="form-label fw-semibold">ملاحظات</label>
                                <input type="text" name="notes" id="notes" class="form-control shadow-sm"
                                    placeholder="أي ملاحظات إضافية...">
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer bg-light border-top d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary px-4" data-dismiss="modal">
                            <i class="fas fa-times me-1"></i> إلغاء
                        </button>
                        <button type="submit" class="btn btn-primary px-4 shadow-sm" id="submitBtn">
                            <i class="fas fa-save me-1"></i> حفظ
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- مودال التعديل --}}
    {{-- ============================================ --}}
    <div class="modal fade" id="editReceivingOrderModal" tabindex="-1" aria-labelledby="editReceivingOrderModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-gradient-warning text-white rounded-top">
                    <h5 class="modal-title fw-bold" id="editReceivingOrderModalLabel">
                        <i class="fas fa-edit me-2"></i> تعديل إذن الاستلام
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <form id="editReceivingOrderForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="edit_id" name="id">
                    <input type="hidden" name="_method" value="PUT">

                    <div class="modal-body p-4" style="max-height: 70vh; overflow-y: auto; background: #f8fafc;">

                        {{-- الصف الأول: رقم الإذن + المورد --}}
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="edit_document_number" class="form-label fw-semibold">
                                    <i class="fas fa-hashtag text-primary ml-1"></i> رقم الإذن <span
                                        class="text-danger">*</span>
                                </label>
                                <input type="text" id="edit_document_number" name="document_number"
                                    class="form-control form-control-lg shadow-sm" placeholder="PO-2026-001" required>
                            </div>

                            <div class="col-md-6">
                                <label for="edit_supplier_id" class="form-label fw-semibold">
                                    <i class="fas fa-store text-success ml-1"></i> المورد <span
                                        class="text-danger">*</span>
                                </label>
                                <select id="edit_supplier_id" name="supplier_id"
                                    class="form-select form-select-lg shadow-sm select2-dropdown" required>
                                    <option value="">-- اختر المورد --</option>
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- الصف الثاني: المخزن + المنتج --}}
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="edit_warehouse_id" class="form-label fw-semibold">
                                    <i class="fas fa-warehouse text-info ml-1"></i> المخزن <span
                                        class="text-danger">*</span>
                                </label>
                                <select id="edit_warehouse_id" name="warehouse_id"
                                    class="form-select form-select-lg shadow-sm select2-dropdown" required>
                                    <option value="">-- اختر المخزن --</option>
                                    @foreach ($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="edit_product_id" class="form-label fw-semibold">
                                    <i class="fas fa-box text-warning ml-1"></i> المنتج <span class="text-danger">*</span>
                                </label>
                                <select id="edit_product_id" name="product_id"
                                    class="form-select form-select-lg shadow-sm select2-dropdown" required>
                                    <option value="">-- اختر المنتج --</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- الصف الثالث: رقم التشغيلة + تاريخ الإنتاج --}}
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="edit_batch_number" class="form-label fw-semibold">
                                    <i class="fas fa-tags text-secondary ml-1"></i> رقم التشغيلة
                                </label>
                                <input type="text" id="edit_batch_number" name="batch_number"
                                    class="form-control form-control-lg shadow-sm" placeholder="مثال: B-2026-01">
                            </div>

                            <div class="col-md-6">
                                <label for="edit_production_date" class="form-label fw-semibold">
                                    <i class="fas fa-calendar-alt text-danger ml-1"></i> تاريخ الإنتاج
                                </label>
                                <input type="date" id="edit_production_date" name="production_date"
                                    class="form-control form-control-lg shadow-sm">
                            </div>

                            <div class="col-md-6">
                                <label for="edit_expiry_date_display" class="form-label fw-semibold">
                                    <i class="fas fa-hourglass-end text-danger ml-1"></i> تاريخ الانتهاء
                                </label>
                                <input type="text" id="edit_expiry_date_display"
                                    class="form-control form-control-lg shadow-sm bg-light" readonly
                                    placeholder="يُحسب تلقائياً">
                            </div>
                        </div>

                        {{-- الصف الرابع: الكمية + العينات --}}
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label for="edit_quantity" class="form-label fw-semibold">
                                    <i class="fas fa-weight-hanging text-success ml-1"></i> الكمية <span
                                        class="text-danger">*</span>
                                </label>
                                <input type="number" id="edit_quantity" name="quantity"
                                    class="form-control form-control-lg shadow-sm" min="1" required>
                            </div>

                            <div class="col-md-4">
                                <label for="edit_samples_quantity" class="form-label fw-semibold">
                                    <i class="fas fa-flask text-warning ml-1"></i> عدد العينات
                                </label>
                                <input type="number" id="edit_samples_quantity" name="samples_quantity"
                                    class="form-control form-control-lg shadow-sm" min="0" value="0">
                            </div>
                        </div>

                        {{-- الصف الخامس: وقت الوصول + وقت المغادرة --}}
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="edit_arrival_time" class="form-label fw-semibold">
                                    <i class="fas fa-clock text-info ml-1"></i> وقت الوصول
                                </label>
                                <input type="datetime-local" id="edit_arrival_time" name="arrival_time"
                                    class="form-control form-control-lg shadow-sm">
                            </div>

                            <div class="col-md-6">
                                <label for="edit_departure_time" class="form-label fw-semibold">
                                    <i class="fas fa-clock text-danger ml-1"></i> وقت المغادرة
                                </label>
                                <input type="datetime-local" id="edit_departure_time" name="departure_time"
                                    class="form-control form-control-lg shadow-sm">
                            </div>
                        </div>

                        {{-- الصف السادس: صورة إذن المورد --}}
                        <div class="row g-3 mb-3">
                            <div class="col-12">
                                <label for="edit_supplier_receipt" class="form-label fw-semibold">
                                    <i class="fas fa-file-upload text-primary ml-1"></i> صورة إذن المورد
                                </label>
                                <div class="input-group">
                                    <input type="file" name="supplier_receipt" id="edit_supplier_receipt"
                                        class="form-control form-control-lg shadow-sm" accept=".jpg,.jpeg,.png,.pdf">
                                    <label class="input-group-text" for="edit_supplier_receipt">
                                        <i class="fas fa-upload"></i>
                                    </label>
                                </div>
                                <small class="text-muted">الصيغ المدعومة: JPG, PNG, PDF (الحجم الأقصى: 2MB)</small>

                                {{-- عرض الصورة الحالية --}}
                                <div id="currentReceiptPreview" class="mt-2" style="display: none;">
                                    <div class="alert alert-info d-flex align-items-center justify-content-between p-2">
                                        <div>
                                            <i class="fas fa-file-image ml-1"></i>
                                            <span id="currentReceiptName" class="fw-bold"></span>
                                        </div>
                                        <div>
                                            <button type="button" class="btn btn-sm btn-outline-primary me-1"
                                                id="viewReceiptBtn">
                                                <i class="fas fa-eye"></i> عرض
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger" id="removeReceiptBtn">
                                                <i class="fas fa-trash"></i> حذف
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                {{-- معاينة الصورة --}}
                                <div id="receiptPreviewContainer" class="mt-2" style="display: none;">
                                    <img id="receiptPreviewImg" class="receipt-preview" />
                                    <a id="receiptPreviewLink" href="#" target="_blank"
                                        class="btn btn-sm btn-info ms-2">
                                        <i class="fas fa-eye"></i> عرض
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- الصف السابع: الملاحظات --}}
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="edit_notes" class="form-label fw-semibold">
                                    <i class="fas fa-sticky-note text-secondary ml-1"></i> ملاحظات
                                </label>
                                <textarea id="edit_notes" name="notes" class="form-control form-control-lg shadow-sm" rows="3"
                                    placeholder="أي ملاحظات إضافية..."></textarea>
                            </div>
                        </div>

                    </div>

                    {{-- Footer --}}
                    <div class="modal-footer bg-light border-top d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary btn-lg px-4" data-dismiss="modal">
                            <i class="fas fa-times me-1"></i> إلغاء
                        </button>
                        <button type="button" class="btn btn-primary btn-lg px-4 shadow-sm"
                            id="btnUpdateReceivingOrder">
                            <i class="fas fa-save me-1"></i> تحديث
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- مودال نسخ التقرير --}}
    {{-- ============================================ --}}
    <div class="modal fade" id="reportCopyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title">تقرير الاستلام</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <textarea id="reportTextarea" class="form-control" rows="12" readonly style="background: #f8f9fa;"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">إغلاق</button>
                    <button type="button" class="btn btn-success" id="copyFinalBtn"><i class="fas fa-copy"></i>
                        نسخ</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('custom-js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        var table;

        function scrollToTable() {
            $('html, body').animate({
                scrollTop: $("#receiving-orders-table").closest('.card').offset().top - 100
            }, 500);
        }

        $(document).ready(function() {
            function initSelect2() {
                $('.select2-dropdown').each(function() {
                    if (!$(this).data('select2')) {
                        $(this).select2({
                            theme: 'bootstrap-5',
                            width: '100%',
                            dropdownParent: $(this).closest('.modal'),
                            placeholder: '-- اختر --',
                            allowClear: true
                        });
                    }
                });
            }

            // ==========================================
            // تهيئة DataTable
            // ==========================================
            table = $('#receiving-orders-table').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                 pageLength: 100,
                ajax: {
                    url: "{{ route('admin.receiving_orders.index') }}",
                    type: "GET"
                },
  columns: [
    { 
        data: 'DT_RowIndex', 
        name: 'id', 
        orderable: false, 
        searchable: false, 
        className: 'text-center',
        render: function(data) {
            return '<span style="white-space: nowrap;">' + data + '</span>';
        }
    },
    { 
        data: 'arrival_date', 
        name: 'arrival_time', 
        className: 'text-center',
        render: function(data) {
            if (!data || data === '-') return '<span class="text-muted">-</span>';
            return '<span style="white-space: nowrap;">' + data + '</span>';
        }
    },
    { 
        data: 'document_number', 
        name: 'document_number', 
        className: 'text-center',
        render: function(data) {
            if (!data || data === '-') return '<span class="text-muted">-</span>';
            return '<span style="white-space: nowrap;">' + data + '</span>';
        }
    },
    { 
        data: 'batch_number', 
        name: 'batch_number', 
        className: 'text-center',
        render: function(data) {
            if (!data || data === '-') return '<span class="text-muted">-</span>';
            return '<span style="white-space: nowrap;">' + data + '</span>';
        }
    },
    { 
        data: 'production_date', 
        name: 'production_date', 
        className: 'text-center',
        render: function(data) {
            if (!data || data === '-') return '<span class="text-muted">-</span>';
            return '<span style="white-space: nowrap;">' + data + '</span>';
        }
    },
    { 
        data: 'expiry_date', 
        name: 'expiry_date', 
        className: 'text-center',
        render: function(data) {
            if (!data || data === '-') return '<span class="text-muted">-</span>';
            return '<span style="white-space: nowrap;">' + data + '</span>';
        }
    },
    { 
        data: 'supplier_name', 
        name: 'supplier_name',
        render: function(data) {
            if (!data || data === '-') return '<span class="text-muted">-</span>';
            return '<span style="white-space: nowrap;">' + data + '</span>';
        }
    },
    { 
        data: 'product_name', 
        name: 'product_name',
        render: function(data) {
            if (!data || data === '-') return '<span class="text-muted">-</span>';
            return '<span style="white-space: nowrap;">' + data + '</span>';
        }
    },
    { 
        data: 'warehouse_name', 
        name: 'warehouse_name',
        render: function(data) {
            if (!data || data === '-') return '<span class="text-muted">-</span>';
            return '<span style="white-space: nowrap;">' + data + '</span>';
        }
    },
    { 
        data: 'quantity', 
        name: 'quantity', 
        className: 'text-center',
        render: function(data) {
            if (!data || data === '-') return '<span class="text-muted">-</span>';
            return '<span style="white-space: nowrap;">' + data + '</span>';
        }
    },
    { 
        data: 'samples_quantity', 
        name: 'samples_quantity', 
        className: 'text-center',
        render: function(data) {
            if (!data || data === '-') return '<span class="text-muted">-</span>';
            return '<span style="white-space: nowrap;">' + data + '</span>';
        }
    },
    { 
        data: 'supplier_receipt', 
        name: 'supplier_receipt', 
        className: 'text-center', 
        orderable: false, 
        searchable: false
    },
    { 
        data: 'action', 
        name: 'action', 
        orderable: false, 
        searchable: false, 
        className: 'text-center'
    }
],
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/ar.json",
                    search: "بحث:",
                    lengthMenu: "عرض _MENU_ سجل",
                    info: "عرض _START_ إلى _END_ من _TOTAL_ سجل",
                    infoEmpty: "لا توجد سجلات",
                    infoFiltered: "(مرشح من _MAX_ إجمالي السجلات)",
                    loadingRecords: "جاري التحميل...",
                    zeroRecords: "لم يتم العثور على سجلات",
                    emptyTable: "لا توجد بيانات متاحة"
                },
                // ✅ إصلاح مشكلة collapse
                initComplete: function() {
                    $('.dataTables_scrollHead').css('width', '100%');
                    $('.dataTables_scrollHeadInner').css('width', '100%');
                    $('.dataTables_scrollHeadInner table').css('width', '100%');
                    this.api().columns.adjust();
                },
                drawCallback: function() {
                    $('.dataTables_scrollHead').css('width', '100%');
                    $('.dataTables_scrollHeadInner').css('width', '100%');
                    $('.dataTables_scrollHeadInner table').css('width', '100%');
                }
            });

            // ==========================================
            // تهيئة Select2
            // ==========================================
            $('#addReceivingOrderModal, #editReceivingOrderModal').on('shown.bs.modal', function() {
                initSelect2();
            });

            $('#addReceivingOrderModal').on('hidden.bs.modal', function() {
                $('#receivingForm')[0].reset();
                $('#supplier_id, #warehouse_id, #product_id').val(null).trigger('change');
                $('#quantity').val('600');
                $('#samples_quantity').val('0');
                $('#supplier_receipt').siblings('.custom-file-label').addClass('selected').html('اختر ملف');
            });

            initSelect2();

            // ==========================================
            // عرض اسم الملف عند اختياره
            // ==========================================
            $(document).on('change', '#supplier_receipt, #edit_supplier_receipt', function() {
                var fileName = $(this).val().split('\\').pop();
                $(this).siblings('.custom-file-label').addClass('selected').html(fileName);
            });

            // ==========================================
            // إصلاح مشكلة collapse
            // ==========================================
            $(document).on('click', '[data-card-widget="collapse"]', function() {
                setTimeout(function() {
                    if (table) {
                        table.columns.adjust().draw();
                    }
                    $('#receiving-orders-table').css('width', '100%');
                }, 400);
            });
            // ==========================================
            // تعديل شحنة
            // ==========================================
            $(document).on('click', '.edit-receiving-order', function() {
                let id = $(this).data('id');

                // إظهار مؤشر التحميل
                Swal.fire({
                    title: 'جاري التحميل...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                $.ajax({
                    url: "{{ url('admin/receiving_orders') }}/" + id + "/edit",
                    method: 'GET',
                    success: function(data) {
                        Swal.close();

                        // تعبئة البيانات الأساسية
                        $('#edit_id').val(data.id);
                        $('#edit_document_number').val(data.document_number);
                        $('#edit_batch_number').val(data.batch_number || '');
                        $('#edit_production_date').val(data.production_date || '');
                        $('#edit_quantity').val(data.quantity);
                        $('#edit_samples_quantity').val(data.samples_quantity || 0);

                        // تنسيق الوقت
                        $('#edit_arrival_time').val(data.arrival_time ? moment(data
                            .arrival_time).format('YYYY-MM-DDTHH:mm') : '');
                        $('#edit_departure_time').val(data.departure_time ? moment(data
                            .departure_time).format('YYYY-MM-DDTHH:mm') : '');
                        $('#edit_notes').val(data.notes || '');

                        // تعبئة الـ Select2
                        $('#edit_supplier_id').val(data.supplier_id).trigger('change');
                        $('#edit_warehouse_id').val(data.warehouse_id).trigger('change');
                        $('#edit_product_id').val(data.product_id).trigger('change');

                        // ✅ عرض صورة إذن المورد إذا وجدت
                        if (data.supplier_receipt_url) {
                            $('#currentReceiptPreview').show();
                            $('#currentReceiptName').text('الملف الحالي: ' + data
                                .supplier_receipt.split('/').pop());
                            $('#receiptPreviewContainer').show();
                            $('#receiptPreviewImg').attr('src', data.supplier_receipt_url);
                            $('#receiptPreviewLink').attr('href', data.supplier_receipt_url);
                        } else {
                            $('#currentReceiptPreview').hide();
                            $('#receiptPreviewContainer').hide();
                        }

                        // عرض المودال
                        $('#editReceivingOrderModal').modal('show');
                    },
                    error: function(xhr) {
                        Swal.close();
                        let errorMsg = 'تعذر جلب البيانات';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        Swal.fire('خطأ!', errorMsg, 'error');
                    }
                });
            });
            $(window).on('resize', function() {
                if (table) {
                    clearTimeout(window.resizeTimer);
                    window.resizeTimer = setTimeout(function() {
                        table.columns.adjust();
                    }, 250);
                }
            });
        });

        // ==========================================
        // دالة التصفية حسب المورد
        // ==========================================
        function filterBySupplier(supplierId, supplierName) {
            table.ajax.url("{{ route('admin.receiving_orders.index') }}?supplier_id=" + supplierId).load();
            Swal.fire({
                title: 'تم التصفية',
                text: 'عرض شحنات المورد: ' + supplierName,
                icon: 'info',
                timer: 1500,
                showConfirmButton: false
            });
            setTimeout(() => scrollToTable(), 500);
        }

        // ==========================================
        // دالة إلغاء التصفية
        // ==========================================
        function resetFilter() {
            table.ajax.url("{{ route('admin.receiving_orders.index') }}").load();
            Swal.fire({
                title: 'تم',
                text: 'تم إلغاء التصفية وعرض جميع الشحنات',
                icon: 'success',
                timer: 1500,
                showConfirmButton: false
            });
            setTimeout(() => scrollToTable(), 500);
        }

        // ==========================================
        // إضافة شحنة جديدة
        // ==========================================
        $('#submitBtn').click(function(e) {
            e.preventDefault();
            let btn = $(this);
            btn.html('<i class="fas fa-spinner fa-spin"></i> جاري الحفظ...').prop('disabled', true);

            let formData = new FormData($('#receivingForm')[0]);

            $.ajax({
                url: "{{ route('admin.receiving_orders.store') }}",
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('تم!', response.message, 'success');
                        $('#addReceivingOrderModal').modal('hide');
                        table.ajax.reload();
                        setTimeout(() => location.reload(), 500);
                    } else {
                        Swal.fire('خطأ!', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    let errorMsg = 'حدث خطأ أثناء الحفظ';
                    if (xhr.status === 422) {
                        errorMsg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    Swal.fire('خطأ!', errorMsg, 'error');
                },
                complete: function() {
                    btn.html('<i class="fas fa-save"></i> حفظ').prop('disabled', false);
                }
            });
        });

        // ==========================================
        // تعديل شحنة
        // ==========================================
        $(document).on('click', '.edit-receiving-order', function() {
            let id = $(this).data('id');
            Swal.fire({
                title: 'جاري التحميل...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            $.ajax({
                url: "{{ url('admin/receiving_orders') }}/" + id + "/edit",
                method: 'GET',
                success: function(data) {
                    Swal.close();

                    // تعبئة البيانات الأساسية
                    $('#edit_id').val(data.id);
                    $('#edit_document_number').val(data.document_number);
                    $('#edit_batch_number').val(data.batch_number || '');
                    $('#edit_production_date').val(data.production_date || '');
                    $('#edit_quantity').val(data.quantity);
                    $('#edit_samples_quantity').val(data.samples_quantity || 0);
                    $('#edit_arrival_time').val(data.arrival_time ? moment(data.arrival_time).format(
                        'YYYY-MM-DDTHH:mm') : '');
                    $('#edit_departure_time').val(data.departure_time ? moment(data.departure_time)
                        .format('YYYY-MM-DDTHH:mm') : '');
                    $('#edit_notes').val(data.notes || '');

                    // تعبئة الـ Select2
                    $('#edit_supplier_id').val(data.supplier_id).trigger('change');
                    $('#edit_warehouse_id').val(data.warehouse_id).trigger('change');
                    $('#edit_product_id').val(data.product_id).trigger('change');

                    // ✅ عرض صورة إذن المورد إذا وجدت
                    if (data.supplier_receipt_url) {
                        $('#currentReceiptPreview').show();
                        $('#currentReceiptName').text('الملف الحالي: ' + data.supplier_receipt.split(
                            '/').pop());
                        $('#receiptPreviewContainer').show();
                        $('#receiptPreviewImg').attr('src', data.supplier_receipt_url);
                        $('#receiptPreviewLink').attr('href', data.supplier_receipt_url);
                    } else {
                        $('#currentReceiptPreview').hide();
                        $('#receiptPreviewContainer').hide();
                    }

                    // عرض المودال
                    $('#editReceivingOrderModal').modal('show');
                },
                error: function(xhr) {
                    Swal.close();
                    let errorMsg = 'تعذر جلب البيانات';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    Swal.fire('خطأ!', errorMsg, 'error');
                }
            });
        });

        // ==========================================
        // تحديث شحنة
        // ==========================================
        $('#btnUpdateReceivingOrder').click(function() {
            let id = $('#edit_id').val();
            let btn = $(this);
            btn.html('<i class="fas fa-spinner fa-spin"></i> جاري التحديث...').prop('disabled', true);

            let formData = new FormData($('#editReceivingOrderForm')[0]);
            formData.append('_method', 'PUT');

            $.ajax({
                url: "{{ url('admin/receiving_orders') }}/" + id,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('تم!', response.message, 'success');
                        $('#editReceivingOrderModal').modal('hide');
                        table.ajax.reload();
                        setTimeout(() => location.reload(), 500);
                    } else {
                        Swal.fire('خطأ!', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    let errorMsg = xhr.responseJSON?.message || 'حدث خطأ أثناء التحديث';
                    if (xhr.status === 422) {
                        errorMsg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                    }
                    Swal.fire('خطأ!', errorMsg, 'error');
                },
                complete: function() {
                    btn.html('تحديث').prop('disabled', false);
                }
            });
        });


        // ==========================================
        // دالة حساب تاريخ الانتهاء (AJAX)
        // ==========================================
        function fetchExpiryDate(productionDate, productId, callback) {
            if (!productionDate || !productId) {
                callback(null);
                return;
            }
            $.ajax({
                url: "{{ route('admin.receiving_orders.calculate-expiry') }}",
                method: 'GET',
                data: {
                    production_date: productionDate,
                    product_id: productId
                },
                success: function(res) {
                    callback(res.expiry_date);
                },
                error: function() {
                    callback(null);
                }
            });
        }

        // في مودال الإضافة
        $('#production_date, #product_id').on('change', function() {
            fetchExpiryDate(
                $('#production_date').val(),
                $('#product_id').val(),
                function(expiry) {
                    $('#expiry_date_display').val(expiry || '');
                }
            );
        });

        // في مودال التعديل
        $('#edit_production_date, #edit_product_id').on('change', function() {
            fetchExpiryDate(
                $('#edit_production_date').val(),
                $('#edit_product_id').val(),
                function(expiry) {
                    $('#edit_expiry_date_display').val(expiry || '');
                }
            );
        });

        // داخل success الخاص بـ edit-receiving-order (بعد تعبئة البيانات)
        $('#edit_expiry_date_display').val(data.expiry_date || '');

        // ==========================================
        // حذف صورة إذن المورد
        // ==========================================
        $('#removeReceiptBtn').click(function() {
            let id = $('#edit_id').val();

            Swal.fire({
                title: 'تأكيد الحذف',
                text: 'هل أنت متأكد من حذف صورة إذن المورد؟',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'نعم، احذف',
                cancelButtonText: 'إلغاء'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'جاري الحذف...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });

                    $.ajax({
                        url: "{{ url('admin/receiving_orders') }}/" + id + "/remove-receipt",
                        method: 'DELETE',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            Swal.close();
                            if (response.success) {
                                Swal.fire('تم!', 'تم حذف الصورة', 'success');
                                $('#currentReceiptPreview').hide();
                                $('#receiptPreviewContainer').hide();
                                table.ajax.reload();
                            } else {
                                Swal.fire('خطأ!', response.message, 'error');
                            }
                        },
                        error: function() {
                            Swal.close();
                            Swal.fire('خطأ!', 'حدث خطأ أثناء حذف الصورة', 'error');
                        }
                    });
                }
            });
        });

        // ==========================================
        // حذف شحنة
        // ==========================================
        $(document).on('click', '.delete-receiving-order', function() {
            let id = $(this).data('id');
            let docNum = $(this).data('number');

            Swal.fire({
                title: 'تأكيد الحذف',
                html: `هل أنت متأكد من حذف إذن الاستلام رقم: <strong>${docNum}</strong>؟`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'نعم، احذف',
                cancelButtonText: 'إلغاء'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'جاري الحذف...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });

                    $.ajax({
                        url: "{{ url('admin/receiving_orders') }}/" + id,
                        method: 'DELETE',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('تم الحذف!', response.message, 'success');
                                table.ajax.reload();
                                setTimeout(() => location.reload(), 500);
                            } else {
                                Swal.fire('خطأ!', response.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            Swal.close();
                            Swal.fire('خطأ!', xhr.responseJSON?.message || 'حدث خطأ', 'error');
                        }
                    });
                }
            });
        });

        // ==========================================
        // نسخ التقرير
        // ==========================================
        $(document).on('click', '.view-report-copy', function() {
            var data = table.row($(this).parents('tr')).data();

            // ✅ دالة تنسيق التاريخ والوقت بالعربية
            function formatDateTime(dateTime) {
                if (!dateTime) return '-';
                try {
                    var date = new Date(dateTime);
                    if (isNaN(date.getTime())) return '-';

                    var year = date.getFullYear();
                    var month = String(date.getMonth() + 1).padStart(2, '0');
                    var day = String(date.getDate()).padStart(2, '0');
                    var hours = date.getHours();
                    var minutes = String(date.getMinutes()).padStart(2, '0');

                    // ✅ تحديد صباحاً أو مساءً بالعربية
                    var period = hours >= 12 ? 'مساءً' : 'صباحاً';

                    // ✅ تحويل الساعة إلى نظام 12 ساعة
                    var hours12 = hours % 12;
                    hours12 = hours12 === 0 ? 12 : hours12;

                    return `${year}-${month}-${day} ${hours12}:${minutes} ${period}`;
                } catch (e) {
                    return '-';
                }
            }

            var arrivalTime = formatDateTime(data.arrival_time);
            var departureTime = formatDateTime(data.departure_time);

            // ✅ بناء التقرير
            var report = `1- رقم الإذن الورقي: ${data.document_number}
2- مكان الوصول: ${data.warehouse_name}
3- ساعة وصول العربة: ${arrivalTime}
4- تم استلام عدد (${data.quantity}) كرتونة بسكويت نوع (${data.product_name}) وارد من مصنع (${data.supplier_name})
5- ساعة مغادرة العربة: ${departureTime}`;

            // ✅ إضافة الملاحظات
            report += `\n6- ملاحظات: ${(data.notes && data.notes.trim() !== '') ? data.notes : 'لا يوجد'}`;

            // ✅ إضافة العينات فقط إذا كانت أكبر من 0
            if (data.samples_quantity && parseInt(data.samples_quantity) > 0) {
                report += `\nتم استلام عدد ${data.samples_quantity} عينة`;
            }

            $('#reportTextarea').val(report);
            $('#reportCopyModal').modal('show');
        });

        $('#copyFinalBtn').click(function() {
            var copyText = document.getElementById("reportTextarea");
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(copyText.value);

            $(this).html('<i class="fas fa-check"></i> تم النسخ!');
            setTimeout(() => $(this).html('<i class="fas fa-copy"></i> نسخ'), 2000);
        });

        // ==========================================
        // طباعة مصفوفة الشحنات
        // ==========================================
        $('#printMatrixBtn').click(function() {
            var printContent = $('#matrixCard .table-responsive').html();
            var printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html dir="rtl">
                <head>
                    <title>تقرير توزيع الشحنات</title>
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
                    <style>
                        body { padding: 20px; font-family: Tahoma, Arial; }
                        table { width: 100%; border-collapse: collapse; }
                        th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
                        th { background-color: #28a745; color: white; }
                    </style>
                </head>
                <body>
                    <h4 class="text-center mb-4">تقرير توزيع الشحنات</h4>
                    ${printContent}
                </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.print();
        });
    </script>
@endpush
