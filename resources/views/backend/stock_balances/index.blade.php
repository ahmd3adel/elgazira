@extends('backend.app')
@section('title', 'أرصدة المخزون')
@section('breadcrumb-title', 'إدارة المخازن')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">أرصدة المخزون</li>
@endsection

@push('custom-css')
<style>
    /* ============================================================
       ✅ الجدول الأساسي - حجم أكبر
       ============================================================ */
    #stock-balances-table {
        width: 100% !important;
        border-collapse: separate !important;
        border-spacing: 0 !important;
        font-size: 20px !important;
        font-family: 'Tahoma', 'Arial', sans-serif !important;
    }

    /* ============================================================
       ✅ رأس الجدول
       ============================================================ */
    #stock-balances-table thead th {
        background: linear-gradient(135deg, #4e73df, #224abe) !important;
        color: white !important;
        font-weight: 800 !important;
        font-size: 22px !important;
        white-space: nowrap;
        text-align: center;
        vertical-align: middle;
        padding: 20px 15px !important;
        border: 2px solid #ffffff !important;
        text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3);
    }

    /* ✅ عمود المخزن في الرأس */
    #stock-balances-table thead th:first-child {
        text-align: right !important;
        padding-right: 30px !important;
    }

    /* ============================================================
       ✅ صفوف الجدول
       ============================================================ */
    #stock-balances-table tbody td {
        vertical-align: middle !important;
        white-space: nowrap;
        text-align: center;
        padding: 18px 15px !important;
        border: 1px solid #dee2e6 !important;
        font-size: 20px !important;
        font-weight: 600;
    }

    /* ✅ ✅ ✅ عمود اسم الإدارة - يتسع حسب المحتوى */
    #stock-balances-table tbody td.warehouse-cell {
        text-align: right !important;
        font-weight: 800 !important;
        font-size: 22px !important;
        background-color: #f8f9fc !important;
        width: auto !important;
        min-width: 450px !important;        /* ✅ عرض أدنى أكبر */
        max-width: 700px !important;        /* ✅ عرض أقصى */
        padding-right: 30px !important;
        padding-left: 20px !important;
        border-right: 4px solid #4e73df !important;
        color: #2c3e50 !important;
        line-height: 1.5 !important;
    }

    /* ✅ أيقونة المخزن الرئيسي */
    #stock-balances-table tbody td.warehouse-cell .fa-building {
        color: #4e73df !important;
        font-size: 24px;
        margin-left: 10px;
    }

    /* ✅ أيقونة الفرع */
    #stock-balances-table tbody td.warehouse-cell .fa-store {
        color: #17a2b8 !important;
        font-size: 22px;
        margin-left: 10px;
    }

    /* ============================================================
       ✅ صف المخزن الرئيسي
       ============================================================ */
    #stock-balances-table tbody tr.main-warehouse-row {
        background: linear-gradient(90deg, #e7f1ff 0%, #f0f7ff 100%) !important;
        border-top: 4px solid #4e73df !important;
        border-bottom: 4px solid #4e73df !important;
    }

    #stock-balances-table tbody tr.main-warehouse-row:hover {
        background: linear-gradient(90deg, #d0e3ff 0%, #e0edff 100%) !important;
    }

    #stock-balances-table tbody tr.main-warehouse-row td {
        font-weight: 800 !important;
        font-size: 22px !important;
    }

    /* ============================================================
       ✅ صف الإجمالي
       ============================================================ */
    #stock-balances-table tfoot tr.total-row td {
        background: linear-gradient(135deg, #28a745, #1e7e34) !important;
        color: white !important;
        font-weight: 900 !important;
        font-size: 24px !important;
        padding: 22px 15px !important;
        border: 2px solid #ffffff !important;
        text-shadow: 2px 2px 3px rgba(0, 0, 0, 0.3);
    }

    #stock-balances-table tfoot tr.total-row td:first-child {
        text-align: right !important;
        padding-right: 30px !important;
        font-size: 26px !important;
    }

    /* ============================================================
       ✅ ✅ ✅ الأرقام - كبيرة وثقيلة
       ============================================================ */
    .balance-number {
        font-size: 24px !important;              /* ✅ أكبر */
        font-weight: 900 !important;             /* ✅ أثقل */
        padding: 12px 24px !important;           /* ✅ حشوة أكبر */
        border-radius: 10px !important;
        display: inline-block;
        min-width: 130px;                         /* ✅ عرض أدنى */
        letter-spacing: 1px;
        box-shadow: 0 3px 6px rgba(0, 0, 0, 0.15);
        transition: all 0.2s ease;
        font-family: 'Tahoma', 'Arial', sans-serif !important;
    }

    .balance-number:hover {
        transform: scale(1.08);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
    }

    /* ✅ الرصيد السابق - رمادي */
    .balance-opening {
        background: linear-gradient(135deg, #e2e3e5, #d6d8db) !important;
        color: #383d41 !important;
        border: 3px solid #c8cbcf !important;
    }

    /* ✅ المنصرف - أحمر */
    .balance-out {
        background: linear-gradient(135deg, #f8d7da, #f5c6cb) !important;
        color: #721c24 !important;
        border: 3px solid #f1b0b7 !important;
    }

    /* ✅ الرصيد الحالي - أخضر */
    .balance-current {
        background: linear-gradient(135deg, #d4edda, #c3e6cb) !important;
        color: #155724 !important;
        border: 3px solid #b1dfbb !important;
    }

    /* ✅ تحذير - أصفر */
    .balance-current.warning {
        background: linear-gradient(135deg, #fff3cd, #ffeaa7) !important;
        color: #856404 !important;
        border: 3px solid #ffe08a !important;
    }

    /* ✅ خطر - أحمر */
    .balance-current.danger {
        background: linear-gradient(135deg, #f8d7da, #f5c6cb) !important;
        color: #721c24 !important;
        border: 3px solid #f1b0b7 !important;
    }

    /* ============================================================
       ✅ شارات النوع (رئيسي/فرعي)
       ============================================================ */
    #stock-balances-table .badge {
        font-size: 17px !important;
        padding: 9px 18px !important;
        font-weight: 800 !important;
        border-radius: 25px;
        letter-spacing: 0.5px;
        vertical-align: middle;
        margin-right: 8px;
    }

    /* ============================================================
       ✅ عدد نقاط التوزيع
       ============================================================ */
    #stock-balances-table .dispatch-points-info {
        display: block;
        margin-top: 8px;
        font-size: 17px !important;
        color: #6c757d !important;
        font-weight: 600;
    }

    #stock-balances-table .dispatch-points-info i {
        color: #ffc107 !important;
        font-size: 18px;
    }

    /* ============================================================
       ✅ بطاقة الفلتر
       ============================================================ */
    .filter-card {
        background: #f8f9fc;
        border: 1px solid #e3e6f0;
        border-radius: 8px;
    }

    .filter-card label {
        font-size: 17px;
        font-weight: 700;
        color: #2c3e50;
    }

    .filter-card .form-control {
        font-size: 17px;
        padding: 12px 15px;
        height: auto;
        border-radius: 6px;
        border: 2px solid #ced4da;
        font-weight: 600;
    }

    /* ============================================================
       ✅ بطاقات الإحصائيات
       ============================================================ */
    .small-box .inner h3 {
        font-size: 2.5rem !important;
        font-weight: 900 !important;
        letter-spacing: 1px;
    }

    .small-box .inner p {
        font-size: 18px !important;
        font-weight: 700;
    }

    /* ============================================================
       ✅ أزرار التصدير
       ============================================================ */
    #exportExcel, #exportPdf, #printTable {
        font-size: 17px !important;
        padding: 10px 22px !important;
        font-weight: 700;
    }

    /* ============================================================
       ✅ تحسينات للطباعة
       ============================================================ */
    @media print {
        #stock-balances-table {
            font-size: 16px !important;
        }

        #stock-balances-table thead th {
            font-size: 18px !important;
            padding: 14px 10px !important;
        }

        #stock-balances-table tbody td.warehouse-cell {
            font-size: 18px !important;
            min-width: 350px !important;
        }

        .balance-number {
            font-size: 18px !important;
            padding: 10px 16px !important;
            min-width: 100px;
        }

        #stock-balances-table tfoot tr.total-row td {
            font-size: 20px !important;
        }
    }
    /* ✅ ✅ ✅ عمود اسم الإدارة - على قد الاسم */
#stock-balances-table tbody td.warehouse-cell {
    text-align: right !important;
    font-weight: 800 !important;
    font-size: 24px !important;           /* ✅ خط أكبر */
    background-color: #f8f9fc !important;
    width: 1% !important;                  /* ✅ يتقلص لأقل عرض ممكن */
    white-space: nowrap !important;        /* ✅ يمنع اللف */
    min-width: unset !important;            /* ✅ إلغاء الحد الأدنى */
    max-width: unset !important;            /* ✅ إلغاء الحد الأقصى */
    padding: 20px 35px !important;          /* ✅ حشوة أكبر */
    border-right: 5px solid #4e73df !important;
    color: #2c3e50 !important;
    line-height: 1.6 !important;
}

#stock-balances-table thead th {
    background: linear-gradient(135deg, #4e73df, #224abe) !important;
    color: white !important;
    font-weight: 800 !important;
    font-size: 24px !important;           /* ✅ خط أكبر */
    white-space: nowrap;
    text-align: center;
    vertical-align: middle;
    padding: 22px 18px !important;          /* ✅ حشوة أكبر */
    border: 2px solid #ffffff !important;
    text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3);
}

</style>
@endpush

@section('content')
    <div class="container-fluid">
        {{-- إحصائيات سريعة --}}
        <div class="row mb-3">
            <div class="col-lg-4 col-md-6 col-12">
                <div class="small-box bg-secondary">
                    <div class="inner">
                        <h3 id="stat-opening">0</h3>
                        <p>إجمالي الرصيد السابق</p>
                    </div>
                    <div class="icon"><i class="fas fa-history"></i></div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-12">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3 id="stat-out">0</h3>
                        <p>إجمالي المنصرف</p>
                    </div>
                    <div class="icon"><i class="fas fa-arrow-up"></i></div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-12">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3 id="stat-current">0</h3>
                        <p>إجمالي الرصيد الحالي</p>
                    </div>
                    <div class="icon"><i class="fas fa-warehouse"></i></div>
                </div>
            </div>
        </div>

        {{-- فلاتر البحث --}}
        <div class="card filter-card mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-filter ml-2"></i> تصفية الأرصدة
                </h3>
            </div>
            <div class="card-body">
                <form id="filterForm">
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label>المخزن الرئيسي</label>
                            <select name="warehouse_id" class="form-control select2">
                                <option value="">كل المخازن الرئيسية</option>
                                @foreach($mainWarehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}">
                                        {{ $warehouse->name }}
                                        @if($warehouse->code) ({{ $warehouse->code }}) @endif
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">سيتم عرض الفروع التابعة للمخزن المختار</small>
                        </div>

                        <div class="col-md-2 form-group">
                            <label>الرصيد السابق (قبل)</label>
                            <input type="date" name="opening_date" class="form-control"
                                value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                        </div>

                        <div class="col-md-2 form-group">
                            <label>من تاريخ</label>
                            <input type="date" name="from_date" class="form-control"
                                value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                        </div>

                        <div class="col-md-2 form-group">
                            <label>إلى تاريخ</label>
                            <input type="date" name="to_date" class="form-control"
                                value="{{ now()->format('Y-m-d') }}">
                        </div>

                        <div class="col-md-2 form-group d-flex align-items-end">
                            <button type="submit" class="btn btn-info btn-sm mr-1">
                                <i class="fas fa-search"></i> بحث
                            </button>
                            <button type="button" id="resetFilter" class="btn btn-secondary btn-sm">
                                <i class="fas fa-redo"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- جدول الأرصدة --}}
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-bar ml-2"></i> تقرير أرصدة المخزون
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-success btn-sm" id="exportExcel">
                        <i class="fas fa-file-excel"></i> Excel
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" id="exportPdf">
                        <i class="fas fa-file-pdf"></i> PDF
                    </button>
                    <button type="button" class="btn btn-info btn-sm" id="printTable">
                        <i class="fas fa-print"></i> طباعة
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="stock-balances-table" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th style="width: 40%; text-align: right;">
                                    <i class="fas fa-warehouse ml-1"></i> المخزن
                                </th>
                                <th style="width: 20%;">
                                    <i class="fas fa-history ml-1"></i> الرصيد السابق
                                </th>
                                <th style="width: 20%;">
                                    <i class="fas fa-arrow-up ml-1"></i> المنصرف
                                </th>
                                <th style="width: 20%;">
                                    <i class="fas fa-boxes ml-1"></i> الرصيد الحالي
                                </th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    <i class="fas fa-spinner fa-spin"></i> جاري التحميل...
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="total-row">
                                <td style="text-align: right;">
                                    <i class="fas fa-calculator ml-1"></i> الإجمالي
                                </td>
                                <td id="total-opening">0</td>
                                <td id="total-out">0</td>
                                <td id="total-current">0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('custom-js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.29/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // ✅ تحميل البيانات
            loadData();

            function loadData() {
                var btn = $('#filterForm button[type="submit"]');
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                $.ajax({
                    url: "{{ route('admin.stock_balances.index') }}",
                    type: "GET",
                    data: {
                        warehouse_id: $('select[name="warehouse_id"]').val(),
                        opening_date: $('input[name="opening_date"]').val(),
                        from_date: $('input[name="from_date"]').val(),
                        to_date: $('input[name="to_date"]').val(),
                    },
                    success: function(response) {
                        renderTable(response.data, response.totals);
                        updateStats(response.totals);
                    },
                    error: function() {
                        Swal.fire('خطأ!', 'حدث خطأ أثناء تحميل البيانات', 'error');
                        $('#tableBody').html('<tr><td colspan="4" class="text-center text-danger py-4">حدث خطأ</td></tr>');
                    },
                    complete: function() {
                        btn.prop('disabled', false).html('<i class="fas fa-search"></i> بحث');
                    }
                });
            }

            // ✅ رسم الجدول
            function renderTable(data, totals) {
                var tbody = $('#tableBody');
                tbody.empty();

                if (!data || data.length === 0) {
                    tbody.html('<tr><td colspan="4" class="text-center text-muted py-4">لا توجد بيانات</td></tr>');
                    return;
                }

                data.forEach(function(row) {
                    var currentClass = 'balance-current';
                    if (row.current_balance === 0) currentClass += ' danger';
                    else if (row.current_balance < 100) currentClass += ' warning';

                    var rowClass = row.warehouse_type === 'main' ? 'main-warehouse-row' : '';

                    var html = '<tr class="' + rowClass + '">' +
                        '<td class="warehouse-cell">' +
                            '<i class="fas fa-' + (row.warehouse_type === 'main' ? 'building' : 'store') + ' ml-1 text-primary"></i> ' +
                            row.warehouse_name + ' ' + row.warehouse_type_badge +
                        '</td>' +
                        '<td><span class="balance-number balance-opening">' + 
                            parseInt(row.opening_balance).toLocaleString('ar-EG') + 
                        '</span></td>' +
                        '<td><span class="balance-number balance-out">' + 
                            parseInt(row.total_out).toLocaleString('ar-EG') + 
                        '</span></td>' +
                        '<td><span class="balance-number ' + currentClass + '">' + 
                            parseInt(row.current_balance).toLocaleString('ar-EG') + 
                        '</span></td>' +
                    '</tr>';

                    tbody.append(html);
                });

                // ✅ تحديث الإجماليات
                $('#total-opening').text(parseInt(totals.opening_balance).toLocaleString('ar-EG'));
                $('#total-out').text(parseInt(totals.total_out).toLocaleString('ar-EG'));
                $('#total-current').text(parseInt(totals.current_balance).toLocaleString('ar-EG'));
            }

            // ✅ تحديث الإحصائيات
            function updateStats(totals) {
                $('#stat-opening').text(parseInt(totals.opening_balance).toLocaleString('ar-EG'));
                $('#stat-out').text(parseInt(totals.total_out).toLocaleString('ar-EG'));
                $('#stat-current').text(parseInt(totals.current_balance).toLocaleString('ar-EG'));
            }

            // ✅ الفلترة
            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
                loadData();
            });

            // ✅ إعادة تعيين
            $('#resetFilter').on('click', function() {
                $('#filterForm')[0].reset();
                $('.select2').val(null).trigger('change');
                $('input[name="opening_date"]').val('{{ now()->startOfMonth()->format('Y-m-d') }}');
                $('input[name="from_date"]').val('{{ now()->startOfMonth()->format('Y-m-d') }}');
                $('input[name="to_date"]').val('{{ now()->format('Y-m-d') }}');
                loadData();
            });

            // ✅ Select2
            $('.select2').select2({
                theme: 'bootstrap4',
                width: '100%',
                placeholder: '-- اختر --',
                allowClear: true
            });

            // ✅ تصدير Excel
            $('#exportExcel').on('click', function() {
                var table = document.getElementById('stock-balances-table');
                var wb = XLSX.utils.table_to_book(table, { sheet: "أرصدة المخزون" });
                XLSX.writeFile(wb, 'stock_balances_' + new Date().toISOString().slice(0, 10) + '.xlsx');
            });

            // ✅ تصدير PDF
            $('#exportPdf').on('click', function() {
                var { jsPDF } = window.jspdf;
                var doc = new jsPDF('p', 'mm', 'a4');
                
                doc.setFontSize(18);
                doc.text('تقرير أرصدة المخزون', 105, 15, { align: 'center' });
                
                doc.setFontSize(10);
                doc.text('من: ' + $('input[name="from_date"]').val() + ' إلى: ' + $('input[name="to_date"]').val(), 105, 22, { align: 'center' });
                
                doc.autoTable({
                    html: '#stock-balances-table',
                    startY: 30,
                    styles: { font: 'helvetica', halign: 'center', fontSize: 9 },
                    headStyles: { fillColor: [78, 115, 223], textColor: 255, halign: 'center' },
                    footStyles: { fillColor: [212, 237, 218], textColor: [21, 87, 36], fontStyle: 'bold' },
                    theme: 'grid'
                });
                
                doc.save('stock_balances_' + new Date().toISOString().slice(0, 10) + '.pdf');
            });

            // ✅ طباعة
            $('#printTable').on('click', function() {
                var printContent = document.getElementById('stock-balances-table').outerHTML;
                var printWindow = window.open('', '_blank');
                printWindow.document.write(`
                    <html dir="rtl">
                    <head>
                        <title>تقرير أرصدة المخزون</title>
                        <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
                        <style>
                            body { padding: 20px; font-family: Tahoma, Arial; }
                            table { width: 100%; border-collapse: collapse; }
                            th, td { border: 1px solid #000; padding: 8px; text-align: center; }
                            th { background-color: #4e73df; color: white; }
                            .total-row { background-color: #d4edda; font-weight: bold; }
                            .warehouse-cell { text-align: right; }
                        </style>
                    </head>
                    <body>
                        <h3 style="text-align: center;">تقرير أرصدة المخزون</h3>
                        ${printContent}
                    </body>
                    </html>
                `);
                printWindow.document.close();
                setTimeout(() => printWindow.print(), 500);
            });
        });
    </script>
@endpush