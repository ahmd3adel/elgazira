{{-- ============================================================ --}}
{{-- تقرير إجمالي الشحنات حسب المخازن والمنتجات (جميع المخازن والمنتجات حتى الصفر) --}}
{{-- ============================================================ --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-gradient-success text-white py-3">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-bar ml-2"></i>
                    إجمالي الشحنات المستلمة (جميع المخازن وجميع المنتجات)
                </h5>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool text-white" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                    <button type="button" class="btn btn-tool text-white" id="printMatrixBtn">
                        <i class="fas fa-print"></i>
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="overflow-x: auto; max-height: 500px;">
                    <table class="table table-bordered table-hover mb-0" id="shipments-matrix-table" style="min-width: 800px;">
                        <thead class="bg-light" style="position: sticky; top: 0; z-index: 20;">
                            <tr>
                                <th class="text-center align-middle" style="background-color: #f8f9fa; position: sticky; right: 0; background-color: white; z-index: 30; min-width: 150px;">
                                    <i class="fas fa-warehouse"></i> المخازن / <i class="fas fa-box"></i> المنتجات
                                </th>
                                @foreach($shipmentsStats['products'] as $product)
                                    <th class="text-center align-middle" style="min-width: 130px; background-color: #f8f9fa;">
                                        <div>{{ $product['name'] }}</div>
                                        <small class="text-muted">{{ $product['sku'] }}</small>
                                    </th>
                                @endforeach
                                <th class="text-center align-middle bg-light" style="background-color: #e9ecef; position: sticky; left: 0; min-width: 100px;">
                                    <i class="fas fa-chart-line"></i> الإجمالي
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $grandTotal = 0;
                                $colTotals = [];
                                foreach($shipmentsStats['products'] as $productId => $product) {
                                    $colTotals[$productId] = 0;
                                }
                            @endphp
                            
                            @foreach($shipmentsStats['warehouses'] as $warehouse)
                                <tr>
                                    <th class="text-center align-middle bg-light" style="background-color: #f8f9fa; position: sticky; right: 0; background-color: white; z-index: 5;">
                                        <i class="fas fa-building ml-1"></i> {{ $warehouse->name }}
                                        @if($warehouse->code)
                                            <small class="d-block text-muted">{{ $warehouse->code }}</small>
                                        @endif
                                    </th>
                                    @php
                                        $rowTotal = 0;
                                    @endphp
                                    @foreach($shipmentsStats['products'] as $productId => $product)
                                        @php
                                            $quantity = $shipmentsStats['matrix'][$warehouse->id][$productId] ?? 0;
                                            $rowTotal += $quantity;
                                            $colTotals[$productId] += $quantity;
                                            $grandTotal += $quantity;
                                            
                                            // تنسيق الألوان حسب الكمية
                                            if ($quantity == 0) {
                                                $bgColor = 'bg-light text-muted';
                                                $fontWeight = 'normal';
                                            } elseif ($quantity < 100) {
                                                $bgColor = 'bg-warning-light';
                                                $fontWeight = 'bold';
                                            } elseif ($quantity < 500) {
                                                $bgColor = 'bg-info-light';
                                                $fontWeight = 'bold';
                                            } else {
                                                $bgColor = 'bg-success-light';
                                                $fontWeight = 'bold';
                                            }
                                        @endphp
                                        <td class="text-center {{ $bgColor }}" style="font-weight: {{ $fontWeight }}; font-size: 1rem;">
                                            @if($quantity > 0)
                                                {{ number_format($quantity) }}
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="text-center font-weight-bold bg-light" style="background-color: #e9ecef;">
                                        {{ number_format($rowTotal) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light" style="position: sticky; bottom: 0; z-index: 20;">
                            <tr>
                                <th class="text-center bg-light" style="background-color: #e9ecef;">
                                    <i class="fas fa-calculator"></i> الإجمالي الكلي
                                </th>
                                @foreach($shipmentsStats['products'] as $productId => $product)
                                    <th class="text-center bg-light" style="background-color: #e9ecef;">
                                        @if($colTotals[$productId] > 0)
                                            <span class="badge badge-primary badge-lg">{{ number_format($colTotals[$productId]) }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </th>
                                @endforeach
                                <th class="text-center bg-light" style="background-color: #dee2e6;">
                                    <span class="badge badge-success badge-lg">{{ number_format($grandTotal) }}</span>
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ============================================================ --}}
{{-- بطاقات الموردين (كل مورد في بطاقة منفصلة مع إمكانية الفلترة) --}}
{{-- ============================================================ --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-gradient-primary text-white py-2">
                <h5 class="card-title mb-0">
                    <i class="fas fa-truck ml-2"></i>
                    ملخص الشحنات حسب الموردين
                </h5>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool text-white" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    @forelse($supplierSummary as $supplier)
                    <div class="col-md-3 col-6 mb-3">
                        <div class="small-box 
                            @if($loop->index % 4 == 0) bg-gradient-success
                            @elseif($loop->index % 4 == 1) bg-gradient-info
                            @elseif($loop->index % 4 == 2) bg-gradient-warning
                            @else bg-gradient-danger
                            @endif
                        " style="cursor: pointer;" onclick="filterBySupplier({{ $supplier['supplier_id'] }}, '{{ addslashes($supplier['supplier_name']) }}')">
                            <div class="inner">
                                <h3>{{ number_format($supplier['total_quantity']) }} <small style="font-size: 12px;">كرتونة</small></h3>
                                <p style="font-size: 14px; margin-bottom: 5px;">{{ $supplier['supplier_name'] }}</p>
                                <div class="d-flex justify-content-between mt-2">
                                    <span class="badge badge-light">
                                        <i class="fas fa-truck"></i> {{ $supplier['shipment_count'] }} شحنة
                                    </span>
                                    <span class="badge badge-light">
                                        <i class="fas fa-chart-line"></i> {{ number_format($supplier['avg_per_shipment']) }} ك/شحنة
                                    </span>
                                </div>
                            </div>
                            <div class="icon">
                                <i class="fas fa-store"></i>
                            </div>
                            <div class="small-box-footer">
                                <i class="fas fa-search"></i> عرض الشحنات
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
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ============================================================ --}}
{{-- توزيع العينات حسب المخازن (شريط معلومات) --}}
{{-- ============================================================ --}}
@if(count($samplesDistribution) > 0)
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-warning alert-dismissible fade show mb-0" role="alert" style="border-right: 4px solid #ffc107;">
            <div class="d-flex flex-wrap align-items-center">
                <div class="ml-3 mb-2 mb-md-0">
                    <i class="fas fa-flask fa-lg text-warning ml-2"></i>
                    <strong>🧪 توزيع العينات حسب المخازن:</strong>
                </div>
                @foreach($samplesDistribution as $sample)
                    <span class="badge badge-dark ml-2 p-2 mb-1">
                        <i class="fas fa-warehouse"></i> {{ $sample['warehouse_name'] }}: 
                        <strong class="text-warning">{{ number_format($sample['samples_count']) }}</strong> عينة
                    </span>
                @endforeach
                <div class="mr-auto">
                    <span class="badge badge-success p-2">
                        <i class="fas fa-chart-line"></i> الإجمالي: <strong>{{ number_format($totalSummary['total_samples'] ?? 0) }}</strong>
                    </span>
                </div>
            </div>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    </div>
</div>
@endif

{{-- إذا لم تكن هناك عينات، نعرض رسالة --}}
@if(count($samplesDistribution) == 0 && ($totalSummary['total_samples'] ?? 0) > 0)
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> توزيع العينات غير متوفر حالياً
        </div>
    </div>
</div>
@endif