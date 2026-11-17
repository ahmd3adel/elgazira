@extends('backend.app')
@section('title', ' الأرصدة')
@section('breadcrumb-title', 'إدارة الموقع')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">الأرصدة</li>
@endsection

@push('custom-css')
    {{-- يفضل مستقبلاً نقل هذه الملفات لمجلد خاص بـ inventories --}}
    @include('backend.inventories.partials.styles')
    <style>
        .card-title i {
            color: #17a2b8;
        }

        .btn-sm {
            border-radius: 4px;
            font-weight: 600;
        }

        /* تمييز السادة والتام */
        .col-base {
            background-color: #6c757d !important; /* رمادي للسادة */
        }
        .col-finished {
            background-color: #007bff !important; /* أزرق للتام */
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
                    <i class="fas fa-boxes ml-2"></i>
                    قائمة الأرصدة
                </h3>
            </div>
            <div class="card-body">

                @php
                    // ============================================================
                    // 1. فصل الأصناف: السادة والتام
                    // ============================================================
                    $baseProducts = $products->where('is_base', true);
                    $finishedProducts = $products->where('is_base', false);

                    // ============================================================
                    // 2. تجميع الأصناف التامة حسب السادة المرتبطة بها
                    //    (مع تحويل المفاتيح لـ int لضمان المطابقة)
                    // ============================================================
                    $finishedByBase = $finishedProducts
                        ->filter(function ($p) {
                            return !is_null($p->companion_product_id) && $p->companion_product_id > 0;
                        })
                        ->groupBy(function ($p) {
                            return (int) $p->companion_product_id;
                        });

                    // ============================================================
                    // 3. الأصناف التامة "اليتيمة" (بدون سادة مرتبطة)
                    // ============================================================
                    $orphanFinished = $finishedProducts->filter(function ($p) {
                        return is_null($p->companion_product_id) || $p->companion_product_id <= 0;
                    });
                @endphp

                <table class="table table-bordered table-striped table-hover text-center align-middle">
                    <thead class="bg-dark text-white">
                        <tr>
                            <th rowspan="2" style="vertical-align: middle; min-width: 180px;">المخازن</th>
                            <th colspan="{{ $products->count() }}" class="text-center border-bottom">
                                الأصناف والمنتجات (بالكرتونة)
                            </th>
                            <th rowspan="2" style="vertical-align: middle; min-width: 100px;" class="bg-info">
                                إجمالي الرصيد
                            </th>
                            <th rowspan="2" style="vertical-align: middle; min-width: 200px;" class="bg-secondary">
                                ميزان الوجبة (سادة vs تام)
                            </th>
                        </tr>
                        <tr>
                            @foreach($products as $product)
                                <th class="{{ $product->is_base ? 'col-base' : 'col-finished' }}"
                                    style="font-size: 0.85rem; min-width: 100px;">
                                    {{ $product->name }}
                                    @if($product->is_base)
                                        <small class="d-block">(سادة)</small>
                                    @else
                                        <small class="d-block">(تام)</small>
                                    @endif
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @php $grandTotal = 0; @endphp

                        @foreach($warehouses as $warehouse)
                            @php
                                $rowTotal = 0;
                                $isBalanced = true;
                                $balanceDetails = [];
                                $hasAnyBase = false;
                            @endphp
                            <tr>
                                {{-- اسم المخزن --}}
                                <td class="text-right">
                                    <strong>{{ $warehouse->name }}</strong>
                                    @if($warehouse->type == 'main')
                                        <span class="badge badge-primary float-left">رئيسي</span>
                                    @elseif($warehouse->type == 'sub')
                                        <span class="badge badge-secondary float-left">فرعي</span>
                                    @else
                                        <span class="badge badge-warning float-left">نقطة صرف</span>
                                    @endif
                                </td>

                                {{-- كميات كل صنف --}}
                                @foreach($products as $product)
                                    @php
                                        $qty = $inventoryMap[$warehouse->id][$product->id] ?? 0;
                                        $rowTotal += $qty;
                                    @endphp
                                    <td class="{{ $qty > 0 ? 'text-primary font-weight-bold' : 'text-muted' }}">
                                        {{ $qty > 0 ? number_format($qty) : '-' }}
                                    </td>
                                @endforeach

                                {{-- إجمالي رصيد المخزن --}}
                                <td class="bg-light font-weight-bold">
                                    {{ number_format($rowTotal) }}
                                </td>

                                {{-- ============================================================
                                     ميزان الوجبة (سادة vs تام) لكل سادة على حدة
                                ============================================================ --}}
                                <td>
                                    @foreach($baseProducts as $baseProduct)
                                        @php
                                            $baseId = (int) $baseProduct->id;

                                            // تجاهل السادة اللي ملهاش أصناف تامة
                                            if (!isset($finishedByBase[$baseId])) {
                                                continue;
                                            }

                                            $hasAnyBase = true;

                                            // كمية السادة في المخزن
                                            $baseQty = $inventoryMap[$warehouse->id][$baseProduct->id] ?? 0;

                                            // مجموع الأصناف التامة المرتبطة
                                            $finishedQty = 0;
                                            foreach ($finishedByBase[$baseId] as $fp) {
                                                $finishedQty += $inventoryMap[$warehouse->id][$fp->id] ?? 0;
                                            }

                                            $diff = $baseQty - $finishedQty;

                                            if ($diff != 0) {
                                                $isBalanced = false;
                                                $balanceDetails[] = [
                                                    'name'     => $baseProduct->name,
                                                    'base'     => $baseQty,
                                                    'finished' => $finishedQty,
                                                    'diff'     => $diff,
                                                ];
                                            }
                                        @endphp
                                    @endforeach

                                    {{-- عرض الحالة --}}
                                    @if(!$hasAnyBase)
                                        <span class="badge badge-secondary p-2">— لا يوجد سادة</span>
                                    @elseif($isBalanced)
                                        <span class="badge badge-success p-2">
                                            <i class="fas fa-check-circle"></i> متوازن
                                        </span>
                                    @else
                                        <span class="badge badge-danger p-2"
                                              data-toggle="tooltip"
                                              data-html="true"
                                              title="@foreach($balanceDetails as $detail){{ $detail['name'] }}: سادة {{ number_format($detail['base']) }} / تام {{ number_format($detail['finished']) }} (فرق {{ $detail['diff'] > 0 ? '+' : '' }}{{ number_format($detail['diff']) }})<br>@endforeach">
                                            <i class="fas fa-times-circle"></i> غير متوازن
                                        </span>

                                        {{-- تفاصيل الفرق --}}
                                        <div class="mt-1" style="font-size: 11px; line-height: 1.4;">
                                            @foreach($balanceDetails as $detail)
                                                <div class="{{ $detail['diff'] > 0 ? 'text-warning' : 'text-danger' }}">
                                                    <strong>{{ $detail['name'] }}:</strong>
                                                    سادة {{ number_format($detail['base']) }}
                                                    / تام {{ number_format($detail['finished']) }}
                                                    <span class="badge badge-{{ $detail['diff'] > 0 ? 'warning' : 'danger' }} badge-sm">
                                                        {{ $detail['diff'] > 0 ? '+' : '' }}{{ number_format($detail['diff']) }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    {{-- تحذير لو فيه أصناف تامة يتيمة --}}
                                    @if($orphanFinished->count() > 0)
                                        <div class="mt-1">
                                            <small class="text-muted">
                                                <i class="fas fa-exclamation-triangle text-warning"></i>
                                                {{ $orphanFinished->count() }} صنف تام بدون سادة
                                            </small>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                            @php $grandTotal += $rowTotal; @endphp
                        @endforeach
                    </tbody>

                    {{-- ============================================================
                         السطر الأخير: الإجماليات
                    ============================================================ --}}
                    <tfoot class="bg-light font-weight-bold">
                        <tr>
                            <td>إجمالي المنتج (كل المخازن)</td>
                            @foreach($products as $product)
                                <td>
                                    {{ number_format($productTotals[$product->id] ?? 0) }}
                                </td>
                            @endforeach
                            <td class="bg-info text-white">{{ number_format($grandTotal) }}</td>
                            <td>
                                @php
                                    $grandBalanced = true;
                                    $grandHasAny = false;
                                    $grandDetails = [];

                                    foreach ($baseProducts as $baseProduct) {
                                        $baseId = (int) $baseProduct->id;
                                        if (!isset($finishedByBase[$baseId])) continue;

                                        $grandHasAny = true;

                                        $totalBase = $productTotals[$baseProduct->id] ?? 0;
                                        $totalFinished = 0;
                                        foreach ($finishedByBase[$baseId] as $fp) {
                                            $totalFinished += $productTotals[$fp->id] ?? 0;
                                        }

                                        if ($totalBase != $totalFinished) {
                                            $grandBalanced = false;
                                            $grandDetails[] = $baseProduct->name
                                                . ': سادة ' . number_format($totalBase)
                                                . ' / تام ' . number_format($totalFinished);
                                        }
                                    }
                                @endphp

                                @if(!$grandHasAny)
                                    <span class="badge badge-secondary p-2">—</span>
                                @elseif($grandBalanced)
                                    <span class="badge badge-success p-2">
                                        <i class="fas fa-check-circle"></i> متوازن
                                    </span>
                                @else
                                    <span class="badge badge-danger p-2"
                                          data-toggle="tooltip"
                                          data-html="true"
                                          title="{{ implode('<br>', $grandDetails) }}">
                                        <i class="fas fa-times-circle"></i> غير متوازن
                                    </span>
                                @endif
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- المودالات الخاصة بالمحافظات --}}
        @include('backend.inventories.partials.modals.add')
        @include('backend.inventories.partials.modals.edit')
    </div>
@endsection

@push('custom-js')
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

            // تفعيل الـ Tooltips
            $('[data-toggle="tooltip"]').tooltip({
                html: true,
                placement: 'top',
                container: 'body'
            });
        });
    </script>
@endpush