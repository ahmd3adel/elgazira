@extends('backend.app')
@section('title', 'إدارة العينات')
@section('breadcrumb-title', 'إدارة الموقع')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">العينات</li>
@endsection

@push('custom-css')
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
@endpush

@section('content')
<div class="container-fluid">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-flask ml-2"></i>
                سجل العينات
            </h3>
            <div class="card-tools d-flex">
                <span class="badge badge-info ml-2 align-self-center">
                    إجمالي العينات: {{ number_format($totalSamples) }}
                </span>
                <button id="export-excel" class="btn btn-success btn-sm ml-2">
                    <i class="fas fa-file-excel"></i> تصدير الكل Excel
                </button>
            </div>
        </div>
        <div class="card-body">
            {{-- فلاتر --}}
            <div class="row mb-3">
                <div class="col-md-4">
                    <select id="filter-warehouse" class="form-control select2">
                        <option value="">كل المخازن</option>
                        @foreach($warehouses as $w)
                            <option value="{{ $w->id }}">{{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <select id="filter-product" class="form-control select2">
                        <option value="">كل الأصناف</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <button id="reset-filters" class="btn btn-secondary btn-block">
                        <i class="fas fa-redo"></i> إعادة تعيين
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table id="samples-table" class="table table-bordered table-striped">
                    <thead class="bg-dark text-white">
                        <tr>
                            <th>#</th>
                            <th>رقم الشحنة</th>
                            <th>المخزن</th>
                            <th>الإدارة</th>
                            <th>الصنف</th>
<th>الكمية الواردة</th>
<th>الرصيد بعد الصرف</th>
<th>المتاح حالياً</th>                            <th>الحالة</th>
                            <th>التاريخ</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('custom-js')
{{-- مكتبات DataTables Buttons --}}
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

<script>
$(document).ready(function () {
    let table = $('#samples-table').DataTable({
        processing: true,
        serverSide: true,

        // ✅ الطول هنا (مش جوه الأزرار)
        pageLength: 100,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "الكل"]],

        ajax: {
            url: "{{ route('admin.samples.index') }}",
            data: function (d) {
                d.warehouse_id = $('#filter-warehouse').val();
                d.product_id   = $('#filter-product').val();
            }
        },
columns: [
    { data: 'DT_RowIndex',     name: 'id',              orderable: false, searchable: false },
    { data: 'document_number', name: 'document_number' },
    { data: 'warehouse_name',  name: 'warehouse_name' },
    { data: 'department_name', name: 'department_name' },
    { data: 'product_name',    name: 'product_name' },
    { data: 'received_qty',    name: 'received_quantity', className: 'text-center font-weight-bold text-primary' }, // ✅ جديد
    { data: 'consumed_qty',    name: 'consumed_quantity', className: 'text-center text-danger' }, // ✅ جديد
    { data: 'available_qty',   name: 'available', className: 'text-center' }, // ✅ جديد
    { data: 'status_badge',    name: 'status',  orderable: false, searchable: false },
    { data: 'created_at',      name: 'created_at', render: d => new Date(d).toLocaleDateString('ar-EG') },
    { data: 'action',          name: 'action',  orderable: false, searchable: false }
],
        language: { url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/ar.json" },

        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="fas fa-file-excel"></i> تصدير الصفحة الحالية',
                className: 'btn btn-outline-success btn-sm',
                title: 'سجل العينات',
                filename: 'samples_' + new Date().toISOString().slice(0, 10),
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6, 7],
                    format: {
                        body: (data) => $('<div>').html(data).text().trim()
                    }
                }
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> طباعة',
                className: 'btn btn-outline-secondary btn-sm',
                title: 'سجل العينات',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6, 7],
                    format: {
                        body: (data) => $('<div>').html(data).text().trim()
                    }
                },
                customize: function (win) {
                    // ✅ دعم العربي في الطباعة
                    $(win.document.body).css('direction', 'rtl');
                    $(win.document.body).find('table')
                        .addClass('compact')
                        .css('font-size', '12px')
                        .css('text-align', 'right');
                    $(win.document.body).find('h1')
                        .css('text-align', 'center')
                        .css('font-family', 'Tahoma, Arial, sans-serif');
                }
            }
        ]
    });

    $('#filter-warehouse, #filter-product').on('change', () => table.ajax.reload());
    $('#reset-filters').on('click', function () {
        $('#filter-warehouse, #filter-product').val(null).trigger('change');
        table.ajax.reload();
    });

    // ✅ زرار تصدير الكل (خارج الجدول) - يبعت الفلاتر للـ server
    $('#export-excel').on('click', function () {
        let params = new URLSearchParams({
            warehouse_id: $('#filter-warehouse').val() || '',
            product_id:   $('#filter-product').val()   || ''
        });
        window.location.href = "" + params.toString();
    });
});
</script>
@endpush