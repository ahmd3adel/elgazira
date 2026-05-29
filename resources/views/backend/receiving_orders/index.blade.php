@php
    $supplierSummary = $supplierData['supplierSummary'] ?? collect();
    $samplesDistribution = $supplierData['samplesDistribution'] ?? collect();
    $totalSummary = $supplierData['totalSummary'] ?? ['grand_total_quantity' => 0, 'total_shipments' => 0, 'total_samples' => 0];
    
    if (!isset($shipmentsStats) || !is_array($shipmentsStats)) {
        $shipmentsStats = [
            'warehouses' => collect(),
            'products' => [],
            'matrix' => []
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
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    
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
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        /* شريط العينات */
        .samples-bar {
            background: #fff3cd;
            border-right: 3px solid #ffc107;
            border-radius: 8px;
        }
        
        @media (max-width: 768px) {
            .small-box h3 {
                font-size: 1.2rem;
            }
            .info-box-content .info-box-number {
                font-size: 1rem;
            }
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
                <div class="small-box bg-secondary" style="cursor: pointer;" onclick="$('#matrixCard').find('[data-card-widget=\"collapse\"]').click();">
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
                                <div class="info-box shadow-sm supplier-mini-card" onclick="filterBySupplier({{ $supplier['supplier_id'] }}, '{{ addslashes($supplier['supplier_name']) }}')">
                                    <div class="info-box-icon bg-primary rounded">
                                        <i class="fas fa-store"></i>
                                    </div>
                                    <div class="info-box-content">
                                        <span class="info-box-text font-weight-bold">{{ Str::limit($supplier['supplier_name'], 25) }}</span>
                                        <span class="info-box-number">{{ number_format($supplier['total_quantity']) }} <small>كرتونة</small></span>
                                        <div class="mt-1">
                                            <small class="text-muted">
                                                <i class="fas fa-ship"></i> {{ $supplier['shipment_count'] }} شحنة
                                                &nbsp;|&nbsp;
                                                <i class="fas fa-chart-line"></i> {{ number_format($supplier['avg_per_shipment']) }} ك/شحنة
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

                            @if(count($supplierSummary) > 0)
                            <div class="col-lg-3 col-md-4 col-6 mb-3">
                                <div class="info-box bg-gradient-dark shadow-sm">
                                    <div class="info-box-icon bg-white">
                                        <i class="fas fa-chart-pie text-dark"></i>
                                    </div>
                                    <div class="info-box-content">
                                        <span class="info-box-text text-white">الإجمالي العام</span>
                                        <span class="info-box-number text-white">{{ number_format($totalSummary['grand_total_quantity'] ?? 0) }}</span>
                                        <div class="mt-1">
                                            <small class="text-white-50">
                                                <i class="fas fa-trucks"></i> {{ number_format($totalSummary['total_shipments'] ?? 0) }} شحنة
                                                &nbsp;|&nbsp;
                                                <i class="fas fa-flask"></i> {{ number_format($totalSummary['total_samples'] ?? 0) }} عينة
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
{{-- بعد كروت الموردين --}}
@if(count($supplierSummary) > 0)
<div class="col-lg-3 col-md-4 col-6 mb-3">
    <div class="info-box bg-gradient-info shadow-sm" onclick="resetFilter()" style="cursor: pointer;">
        <div class="info-box-icon bg-white">
            <i class="fas fa-eye text-info"></i>
        </div>
        <div class="info-box-content">
            <span class="info-box-text text-white">عرض الكل</span>
            <span class="info-box-number text-white">
                <i class="fas fa-list"></i> {{ number_format($totalSummary['total_shipments'] ?? 0) }}
            </span>
            <div class="mt-1">
                <small class="text-white-50">
                    <i class="fas fa-chart-line"></i> إلغاء التصفية
                </small>
            </div>
        </div>
    </div>
</div>
@endif
        {{-- توزيع العينات --}}
        @if(count($samplesDistribution) > 0)
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-warning samples-bar mb-0">
                    <div class="d-flex flex-wrap align-items-center justify-content-between">
                        <div class="mb-2 mb-md-0">
                            <i class="fas fa-flask fa-lg ml-2"></i>
                            <strong>🧪 توزيع العينات حسب المخازن:</strong>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($samplesDistribution as $sample)
                                <span class="badge badge-light p-2">
                                    <i class="fas fa-warehouse text-info"></i>
                                    {{ $sample['warehouse_name'] }}: 
                                    <strong class="text-warning">{{ number_format($sample['samples_count']) }}</strong>
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
        @if($warehouses->count() > 0 && count($productsMatrix) > 0)
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
                            <table class="table table-bordered table-hover matrix-table mb-0" style="min-width: 600px;">
                                <thead>
                                    <tr>
                                        <th style="position: sticky; right: 0; background: #28a745; z-index: 20; min-width: 140px;">
                                            <i class="fas fa-warehouse"></i> المخزن / المنتج
                                        </th>
                                        @foreach($productsMatrix as $product)
                                            <th style="min-width: 100px; text-align: center;">
                                                {{ \Illuminate\Support\Str::limit($product['name'] ?? 'منتج', 20) }}
                                                <small class="d-block text-white-50">{{ $product['sku'] ?? '' }}</small>
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
                                        foreach($productsMatrix as $productId => $product) { $colTotals[$productId] = 0; }
                                    @endphp
                                    
                                    @foreach($warehouses as $warehouse)
                                        @php
                                            $warehouseId = is_object($warehouse) ? $warehouse->id : $warehouse['id'];
                                            $warehouseName = is_object($warehouse) ? $warehouse->name : $warehouse['name'];
                                            $warehouseCode = is_object($warehouse) ? ($warehouse->code ?? '') : ($warehouse['code'] ?? '');
                                        @endphp
                                        <tr>
                                            <th style="position: sticky; right: 0; background: #f8f9fa;">
                                                <i class="fas fa-building ml-1 text-primary"></i> {{ $warehouseName }}
                                                @if($warehouseCode)
                                                    <small class="d-block text-muted">{{ $warehouseCode }}</small>
                                                @endif
                                            </th>
                                            @php $rowTotal = 0; @endphp
                                            @foreach($productsMatrix as $productId => $product)
                                                @php
                                                    $quantity = $matrix[$warehouseId][$productId] ?? 0;
                                                    $rowTotal += $quantity;
                                                    $colTotals[$productId] += $quantity;
                                                    $grandTotal += $quantity;
                                                    $badgeClass = $quantity == 0 ? 'secondary' : ($quantity < 100 ? 'warning' : ($quantity < 500 ? 'info' : 'success'));
                                                @endphp
                                                <td class="text-center">
                                                    @if($quantity > 0)
                                                        <span class="badge badge-{{ $badgeClass }} badge-pill px-3 py-2">{{ number_format($quantity) }}</span>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                            <td class="text-center font-weight-bold bg-light">{{ number_format($rowTotal) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr style="background: #e9ecef;">
                                        <th class="text-center">الإجمالي الكلي</th>
                                        @foreach($productsMatrix as $productId => $product)
                                            <th class="text-center">
                                                @if($colTotals[$productId] > 0)
                                                    <span class="badge badge-primary badge-pill px-3 py-2">{{ number_format($colTotals[$productId]) }}</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </th>
                                        @endforeach
                                        <th class="text-center bg-success text-white">{{ number_format($grandTotal) }}</th>
                                    </td>
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
                            <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#addReceivingOrderModal">
                                <i class="fas fa-plus"></i> إضافة شحنة
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="receiving-orders-table" class="table table-bordered table-striped table-hover w-100">
                                <thead>
                                    <tr>
                                        <th style="width: 50px">#</th>
                                        <th>رقم الإذن</th>
                                        <th>رقم التشغيلة</th>
                                        <th>المورد</th>
                                        <th>المنتج</th>
                                        <th>المخزن</th>
                                        <th style="width: 100px">الكمية</th>
                                        <th style="width: 100px">العينات</th>
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

    {{-- المودالات --}}
    <!-- مودال الإضافة -->
    <div class="modal fade" id="addReceivingOrderModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-truck ml-1"></i> إضافة إذن استلام جديد
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="receivingForm" method="POST" action="{{ route('admin.receiving_orders.store') }}">
                    @csrf
                    <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="document_number">رقم الإذن <span class="text-danger">*</span></label>
                                <input type="text" name="document_number" id="document_number" class="form-control" placeholder="PO-2026-001" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="supplier_id">المورد <span class="text-danger">*</span></label>
                                <select name="supplier_id" id="supplier_id" class="form-control select2-dropdown" required>
                                    <option value="">-- اختر المورد --</option>
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="warehouse_id">المخزن <span class="text-danger">*</span></label>
                                <select name="warehouse_id" id="warehouse_id" class="form-control select2-dropdown" required>
                                    <option value="">-- اختر المخزن --</option>
                                    @foreach ($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="product_id">المنتج <span class="text-danger">*</span></label>
                                <select name="product_id" id="product_id" class="form-control select2-dropdown" required>
                                    <option value="">-- اختر المنتج --</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="batch_number">رقم التشغيلة</label>
                                <input type="text" name="batch_number" id="batch_number" class="form-control" placeholder="Batch No.">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="quantity">الكمية <span class="text-danger">*</span></label>
                                <input type="number" name="quantity" id="quantity" class="form-control" value="600" min="1" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="samples_quantity">عدد العينات</label>
                                <input type="number" name="samples_quantity" id="samples_quantity" class="form-control" value="0" min="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="arrival_time">وقت الوصول</label>
                                <input type="datetime-local" name="arrival_time" id="arrival_time" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="departure_time">وقت المغادرة</label>
                                <input type="datetime-local" name="departure_time" id="departure_time" class="form-control">
                            </div>
                            <div class="col-12 mb-3">
                                <label for="notes">ملاحظات</label>
                                <input type="text" name="notes" id="notes" class="form-control" placeholder="رقم الخطة...">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> إلغاء</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn"><i class="fas fa-save"></i> حفظ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- مودال التعديل -->
    <div class="modal fade" id="editReceivingOrderModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title"><i class="fas fa-edit"></i> تعديل إذن الاستلام</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                </div>
                <form id="editReceivingOrderForm">
                    @csrf
                    <input type="hidden" id="edit_id" name="id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>رقم الإذن</label>
                                <input type="text" id="edit_document_number" name="document_number" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>المورد</label>
                                <select id="edit_supplier_id" name="supplier_id" class="form-control select2-dropdown" required>
                                    <option value="">-- اختر المورد --</option>
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>المخزن</label>
                                <select id="edit_warehouse_id" name="warehouse_id" class="form-control select2-dropdown" required>
                                    <option value="">-- اختر المخزن --</option>
                                    @foreach ($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>المنتج</label>
                                <select id="edit_product_id" name="product_id" class="form-control select2-dropdown" required>
                                    <option value="">-- اختر المنتج --</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>رقم التشغيلة</label>
                                <input type="text" id="edit_batch_number" name="batch_number" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>الكمية</label>
                                <input type="number" id="edit_quantity" name="quantity" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>عدد العينات</label>
                                <input type="number" id="edit_samples_quantity" name="samples_quantity" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>وقت الوصول</label>
                                <input type="datetime-local" id="edit_arrival_time" name="arrival_time" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>وقت المغادرة</label>
                                <input type="datetime-local" id="edit_departure_time" name="departure_time" class="form-control">
                            </div>
                            <div class="col-12 mb-3">
                                <label>ملاحظات</label>
                                <textarea id="edit_notes" name="notes" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                        <button type="button" class="btn btn-primary" id="btnUpdateReceivingOrder">تحديث</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- مودال نسخ التقرير -->
    <div class="modal fade" id="reportCopyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title">تقرير الاستلام</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <textarea id="reportTextarea" class="form-control" rows="12" readonly style="background: #f8f9fa;"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">إغلاق</button>
                    <button type="button" class="btn btn-success" id="copyFinalBtn"><i class="fas fa-copy"></i> نسخ</button>
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

            table = $('#receiving-orders-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: false,
                scrollX: true,
                autoWidth: false,
                ajax: {
                    url: "{{ route('admin.receiving_orders.index') }}",
                    type: "GET"
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'id', orderable: false, searchable: false, className: 'text-center', width: '50px' },
                    { data: 'document_number', name: 'document_number', className: 'text-center' },
                    { data: 'batch_number', name: 'batch_number', className: 'text-center' },
                    { data: 'supplier_name', name: 'supplier_name' },
                    { data: 'product_name', name: 'product_name' },
                    { data: 'warehouse_name', name: 'warehouse_name' },
                    { data: 'quantity', name: 'quantity', className: 'text-center' },
                    { data: 'samples_quantity', name: 'samples_quantity', className: 'text-center' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center', width: '130px' }
                ],
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/ar.json",
                    search: "بحث:",
                    searchPlaceholder: "بحث...",
                    lengthMenu: "عرض _MENU_ سجل",
                    info: "عرض _START_ إلى _END_ من أصل _TOTAL_ سجل",
                    infoEmpty: "لا توجد سجلات",
                    zeroRecords: "لم يتم العثور على سجلات",
                    paginate: {
                        first: "الأول",
                        last: "الأخير",
                        next: "التالي",
                        previous: "السابق"
                    }
                },
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "الكل"]],
                order: [[1, 'asc']]
            });
            
            $('#addReceivingOrderModal, #editReceivingOrderModal').on('shown.bs.modal', function() { initSelect2(); });
            $('#addReceivingOrderModal').on('hidden.bs.modal', function() {
                $('#receivingForm')[0].reset();
                $('#supplier_id, #warehouse_id, #product_id').val(null).trigger('change');
                $('#quantity').val('600');
                $('#samples_quantity').val('0');
            });
            initSelect2();
        });

        function filterBySupplier(supplierId, supplierName) {
            table.ajax.url("{{ route('admin.receiving_orders.index') }}?supplier_id=" + supplierId).load();
            Swal.fire({ title: 'تم التصفية', text: 'عرض شحنات المورد: ' + supplierName, icon: 'info', timer: 1500, showConfirmButton: false });
            setTimeout(() => scrollToTable(), 500);
        }

        $('#submitBtn').click(function(e) {
            e.preventDefault();
            let btn = $(this);
            btn.html('<i class="fas fa-spinner fa-spin"></i> جاري الحفظ...').prop('disabled', true);
            
            $.ajax({
                url: "{{ route('admin.receiving_orders.store') }}",
                method: 'POST',
                data: new FormData($('#receivingForm')[0]),
                processData: false,
                contentType: false,
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
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

        $(document).on('click', '.edit-receiving-order', function() {
            let id = $(this).data('id');
            Swal.fire({ title: 'جاري التحميل...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            
            $.ajax({
                url: "{{ url('admin/receiving_orders') }}/" + id + "/edit",
                method: 'GET',
                success: function(data) {
                    Swal.close();
                    $('#edit_id').val(data.id);
                    $('#edit_document_number').val(data.document_number);
                    $('#edit_batch_number').val(data.batch_number);
                    $('#edit_quantity').val(data.quantity);
                    $('#edit_samples_quantity').val(data.samples_quantity || 0);
                    $('#edit_arrival_time').val(data.arrival_time ? moment(data.arrival_time).format('YYYY-MM-DDTHH:mm') : '');
                    $('#edit_departure_time').val(data.departure_time ? moment(data.departure_time).format('YYYY-MM-DDTHH:mm') : '');
                    $('#edit_notes').val(data.notes || '');
                    $('#edit_supplier_id').val(data.supplier_id).trigger('change');
                    $('#edit_warehouse_id').val(data.warehouse_id).trigger('change');
                    $('#edit_product_id').val(data.product_id).trigger('change');
                    $('#editReceivingOrderModal').modal('show');
                },
                error: function() {
                    Swal.close();
                    Swal.fire('خطأ!', 'تعذر جلب البيانات', 'error');
                }
            });
        });

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
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
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
                    Swal.fire('خطأ!', errorMsg, 'error');
                },
                complete: function() {
                    btn.html('تحديث').prop('disabled', false);
                }
            });
        });

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
                    Swal.fire({ title: 'جاري الحذف...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                    
                    $.ajax({
                        url: "{{ url('admin/receiving_orders') }}/" + id,
                        method: 'DELETE',
                        data: { _token: "{{ csrf_token() }}" },
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
// دالة لعرض الكل (إلغاء التصفية)
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

// دالة التصفية حسب المورد (موجودة بالفعل)
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
        $(document).on('click', '.view-report-copy', function() {
            var data = table.row($(this).parents('tr')).data();
            var report = `1- رقم الإذن الورقي: ${data.document_number}
2- مكان الوصول: ${data.warehouse_name}
3- ساعة وصول العربة: ${data.arrival_time_formatted || '-'}
4- تم استلام عدد (${data.quantity}) كرتونة بسكويت نوع (${data.product_name}) وارد من مصنع (${data.supplier_name})
5- ساعة مغادرة العربة: ${data.departure_time_formatted || '-'}
6- ملاحظات: ${data.notes || '-'}
تم استلام عدد ${data.samples_quantity || 0} عينة`;
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