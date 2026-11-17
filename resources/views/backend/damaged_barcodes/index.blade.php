@extends('backend.app')
@section('title', 'إدارة الباركود التالف')
@section('breadcrumb-title', 'إدارة المخزون')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">الباركود التالف</li>
@endsection

@push('custom-css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    :root {
        --db-primary: #4e73df;
        --db-danger: #e74a3b;
        --db-success: #1cc88a;
        --db-warning: #f6c23e;
        --db-info: #36b9cc;
    }

    /* ✅ كروت الإحصائيات */
    .stat-card {
        border-radius: 14px;
        border: none;
        overflow: hidden;
        position: relative;
        transition: transform .25s ease, box-shadow .25s ease;
    }
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0,0,0,.12);
    }
    .stat-card .stat-icon {
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 3.2rem;
        opacity: .18;
    }
    .stat-card .stat-body { padding: 22px 24px; }
    .stat-card .stat-value {
        font-size: 2rem;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 6px;
    }
    .stat-card .stat-label {
        font-size: .85rem;
        opacity: .9;
        font-weight: 600;
        letter-spacing: .3px;
    }
    .stat-card.gradient-danger  { background: linear-gradient(135deg, #e74a3b, #c0392b); color: #fff; }
    .stat-card.gradient-warning { background: linear-gradient(135deg, #f6c23e, #d4a017); color: #fff; }
    .stat-card.gradient-success { background: linear-gradient(135deg, #1cc88a, #13855c); color: #fff; }
    .stat-card.gradient-info    { background: linear-gradient(135deg, #36b9cc, #258391); color: #fff; }

    /* ✅ فلتر */
    .filter-card {
        border-radius: 12px;
        border: 1px solid #e3e6f0;
        box-shadow: 0 2px 8px rgba(0,0,0,.04);
    }
    .filter-card .form-label {
        font-size: .8rem;
        font-weight: 600;
        color: #5a5c69;
        margin-bottom: 4px;
    }

    /* ✅ الجدول */
    #damaged-barcode-table thead th {
        background: #f8f9fc;
        color: #4e73df;
        font-weight: 700;
        font-size: .82rem;
        text-transform: uppercase;
        letter-spacing: .3px;
        border-bottom: 2px solid #e3e6f0;
        padding: 14px 10px;
        white-space: nowrap;
    }
    #damaged-barcode-table tbody td {
        vertical-align: middle;
        padding: 12px 10px;
        font-size: .88rem;
    }
    #damaged-barcode-table tbody tr:hover { background: #f8f9fc; }

    /* ✅ شارة الحالة */
    .status-badge { font-size: .78rem; font-weight: 700; }

    /* ✅ زر الإجراء */
    .btn-group .btn { border-radius: 6px !important; margin-left: 2px; }

    /* ✅ Responsive */
    @media (max-width: 768px) {
        .stat-card .stat-value { font-size: 1.5rem; }
        .stat-card .stat-icon { font-size: 2.2rem; }
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-2 px-md-3">

    {{-- ✅ Header --}}
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
        <div>
            <h4 class="mb-1 fw-bold text-dark">
                <i class="fas fa-barcode text-danger ml-2"></i>
                إدارة الباركود التالف
            </h4>
            <p class="text-muted mb-0 small">متابعة حالة الباركود التالف وتتبع تسليمه واستبداله</p>
        </div>
        <div class="mt-2 mt-md-0">
            <button class="btn btn-success btn-sm" id="exportBtn">
                <i class="fas fa-file-excel"></i> تصدير Excel
            </button>
            <button class="btn btn-outline-primary btn-sm" id="refreshBtn">
                <i class="fas fa-sync-alt"></i> تحديث
            </button>
        </div>
    </div>

    {{-- ✅ كروت الإحصائيات --}}
    <div class="row mb-3">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card gradient-danger">
                <div class="stat-icon"><i class="fas fa-barcode"></i></div>
                <div class="stat-body">
                    <div class="stat-value">{{ number_format($stats['total_quantity']) }}</div>
                    <div class="stat-label">إجمالي الباركود التالف</div>
                    <small class="d-block mt-2 opacity-75">{{ $stats['total_orders'] }} شحنة متأثرة</small>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card gradient-warning">
                <div class="stat-icon"><i class="fas fa-hourglass-half"></i></div>
                <div class="stat-body">
                    <div class="stat-value">{{ number_format($stats['pending_qty']) }}</div>
                    <div class="stat-label">قيد الانتظار</div>
                    <small class="d-block mt-2 opacity-75">{{ $stats['pending'] }} شحنة لسه ما وصلتش</small>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card gradient-success">
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                <div class="stat-body">
                    <div class="stat-value">{{ number_format($stats['delivered_qty'] + $stats['replaced_qty']) }}</div>
                    <div class="stat-label">تم التسليم / الاستبدال</div>
                    <small class="d-block mt-2 opacity-75">
                        {{ $stats['delivered'] }} مسلّم · {{ $stats['replaced'] }} مستبدل
                    </small>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card gradient-info">
                <div class="stat-icon"><i class="fas fa-percentage"></i></div>
                <div class="stat-body">
                    <div class="stat-value">{{ $stats['delivery_rate'] }}%</div>
                    <div class="stat-label">نسبة الإنجاز</div>
                    <div class="progress mt-2" style="height:6px; background:rgba(255,255,255,.3);">
                        <div class="progress-bar bg-white" style="width: {{ $stats['delivery_rate'] }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ✅ الفلتر المتقدم --}}
    <div class="card filter-card mb-3">
        <div class="card-body">
            <form id="filterForm">
                <div class="row g-2">
                    <div class="col-md-2 col-6">
                        <label class="form-label">الحالة</label>
                        <select class="form-select form-select-sm" name="status" id="filterStatus">
                            <option value="">الكل</option>
                            <option value="pending">قيد الانتظار</option>
                            <option value="delivered">تم التسليم</option>
                            <option value="replaced">تم الاستبدال</option>
                        </select>
                    </div>
                    <div class="col-md-2 col-6">
                        <label class="form-label">المورد</label>
                        <select class="form-select form-select-sm select2" name="supplier_id" id="filterSupplier">
                            <option value="">الكل</option>
                            @foreach($suppliers as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-6">
                        <label class="form-label">المنتج</label>
                        <select class="form-select form-select-sm select2" name="product_id" id="filterProduct">
                            <option value="">الكل</option>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-6">
                        <label class="form-label">المخزن</label>
                        <select class="form-select form-select-sm select2" name="warehouse_id" id="filterWarehouse">
                            <option value="">الكل</option>
                            @foreach($warehouses as $w)
                                <option value="{{ $w->id }}">{{ $w->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-6">
                        <label class="form-label">من تاريخ</label>
                        <input type="date" class="form-control form-control-sm" name="date_from" id="filterDateFrom">
                    </div>
                    <div class="col-md-2 col-6">
                        <label class="form-label">إلى تاريخ</label>
                        <input type="date" class="form-control form-control-sm" name="date_to" id="filterDateTo">
                    </div>
                </div>
                <div class="row g-2 mt-2">
                    <div class="col-md-4">
                        <input type="text" class="form-control form-control-sm" name="search_term"
                               id="filterSearch" placeholder="🔍 بحث برقم الإذن أو التشغيلة...">
                    </div>
                    <div class="col-md-8 d-flex justify-content-end align-items-end gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-filter"></i> تطبيق
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="resetFilterBtn">
                            <i class="fas fa-undo"></i> إعادة تعيين
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ✅ الجدول --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="damaged-barcode-table" class="table table-hover mb-0 w-100">
                    <thead>
                        <tr>
                            <th style="width:50px">#</th>
                            <th>رقم الإذن</th>
                            <th>التشغيلة</th>
                            <th>المورد</th>
                            <th>المنتج</th>
                            <th>المخزن</th>
                            <th class="text-center">التالف</th>
                            <th class="text-center">الحالة</th>
                            <th class="text-center">تاريخ التسليم</th>
                            <th class="text-center">تاريخ الشحنة</th>
                            <th class="text-center" style="width:130px">العمليات</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ✅ مودال تغيير الحالة --}}
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-gradient-primary text-white border-0">
                <h5 class="modal-title">
                    <i class="fas fa-exchange-alt ml-1"></i> تغيير حالة الباركود التالف
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="statusForm">
                @csrf
                <input type="hidden" id="statusOrderId">
                <div class="modal-body">
                    <div class="alert alert-light border mb-3">
                        <strong>رقم الإذن:</strong>
                        <span id="statusDocNumber" class="text-primary fw-bold"></span>
                    </div>

                    <label class="form-label fw-semibold">الحالة الجديدة</label>
                    <div class="row g-2 mb-3">
                        <div class="col-4">
                            <input type="radio" class="btn-check" name="status" id="stPending" value="pending">
                            <label class="btn btn-outline-warning w-100 py-3" for="stPending">
                                <i class="fas fa-hourglass-half d-block mb-1"></i>
                                <small>قيد الانتظار</small>
                            </label>
                        </div>
                        <div class="col-4">
                            <input type="radio" class="btn-check" name="status" id="stDelivered" value="delivered">
                            <label class="btn btn-outline-success w-100 py-3" for="stDelivered">
                                <i class="fas fa-check-circle d-block mb-1"></i>
                                <small>تم التسليم</small>
                            </label>
                        </div>
                        <div class="col-4">
                            <input type="radio" class="btn-check" name="status" id="stReplaced" value="replaced">
                            <label class="btn btn-outline-primary w-100 py-3" for="stReplaced">
                                <i class="fas fa-sync-alt d-block mb-1"></i>
                                <small>تم الاستبدال</small>
                            </label>
                        </div>
                    </div>

                    <label class="form-label fw-semibold">ملاحظات (اختياري)</label>
                    <textarea class="form-control" name="notes" id="statusNotes" rows="3"
                              placeholder="مثال: تم الاستلام من المندوب..."></textarea>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary" id="saveStatusBtn">
                        <i class="fas fa-save"></i> حفظ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ✅ مودال الملاحظات --}}
<div class="modal fade" id="notesModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-gradient-info text-white border-0">
                <h5 class="modal-title"><i class="fas fa-sticky-note ml-1"></i> ملاحظات الباركود التالف</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="notesForm">
                @csrf
                <input type="hidden" id="notesOrderId">
                <div class="modal-body">
                    <div class="alert alert-light border mb-3">
                        <strong>رقم الإذن:</strong>
                        <span id="notesDocNumber" class="text-primary fw-bold"></span>
                    </div>
                    <textarea class="form-control" name="notes" id="notesText" rows="5"
                              placeholder="اكتب ملاحظاتك هنا..."></textarea>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-info" id="saveNotesBtn">
                        <i class="fas fa-save"></i> حفظ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('custom-js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<script>
$(function () {
    // ✅ Select2 للفلاتر
    $('.select2').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'الكل',
        allowClear: true
    });

    // ✅ DataTable
    const table = $('#damaged-barcode-table').DataTable({
        processing: true,
        serverSide: true,
        autoWidth: false,
        pageLength: 25,
        order: [[0, 'desc']],
        ajax: {
            url: "{{ route('admin.damaged-barcodes.index') }}",
            data: function (d) {
                d.status       = $('#filterStatus').val();
                d.supplier_id  = $('#filterSupplier').val();
                d.product_id   = $('#filterProduct').val();
                d.warehouse_id = $('#filterWarehouse').val();
                d.date_from    = $('#filterDateFrom').val();
                d.date_to      = $('#filterDateTo').val();
                d.search_term  = $('#filterSearch').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
            { data: 'document_number', className: 'text-center' },
            { data: 'batch_number', className: 'text-center' },
            { data: 'supplier_name', defaultContent: '<span class="text-muted">—</span>' },
            { data: 'product_name', defaultContent: '<span class="text-muted">—</span>' },
            { data: 'warehouse_name', defaultContent: '<span class="text-muted">—</span>' },
            { data: 'damaged_barcode', className: 'text-center' },
            { data: 'status_badge', className: 'text-center' },
            { data: 'damaged_barcode_delivered_at', className: 'text-center' },
            { data: 'created_at', className: 'text-center' },
            { data: 'action', orderable: false, searchable: false, className: 'text-center' }
        ],
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/ar.json",
            processing: '<i class="fas fa-spinner fa-spin fa-2x text-primary"></i>'
        }
    });

    // ✅ تطبيق الفلتر
    $('#filterForm').on('submit', function (e) {
        e.preventDefault();
        table.ajax.reload();
    });

    // ✅ إعادة تعيين
    $('#resetFilterBtn').on('click', function () {
        $('#filterForm')[0].reset();
        $('.select2').val(null).trigger('change');
        table.ajax.reload();
    });

    // ✅ تحديث
    $('#refreshBtn').on('click', () => table.ajax.reload());

    // ✅ تغيير الحالة
    $(document).on('click', '.btn-change-status', function () {
        const id = $(this).data('id');
        const status = $(this).data('status');
        const number = $(this).data('number');

        $('#statusOrderId').val(id);
        $('#statusDocNumber').text(number);
        $('#statusNotes').val('');
        $(`input[name="status"][value="${status}"]`).prop('checked', true);

        $('#statusModal').modal('show');
    });

    $('#statusForm').on('submit', function (e) {
        e.preventDefault();
        const id = $('#statusOrderId').val();
        const btn = $('#saveStatusBtn');
        const original = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin"></i> جاري الحفظ...').prop('disabled', true);

        $.post(`{{ url('admin/damaged-barcodes') }}/${id}/status`, {
            _token: '{{ csrf_token() }}',
            status: $('input[name="status"]:checked').val(),
            notes: $('#statusNotes').val()
        })
        .done(res => {
            if (res.success) {
                Swal.fire({ icon: 'success', title: 'تم!', text: res.message, timer: 1800, showConfirmButton: false });
                $('#statusModal').modal('hide');
                table.ajax.reload(null, false);
                setTimeout(() => location.reload(), 800);
            } else {
                Swal.fire('خطأ!', res.message, 'error');
            }
        })
        .fail(xhr => Swal.fire('خطأ!', xhr.responseJSON?.message || 'تعذر الحفظ', 'error'))
        .always(() => btn.html(original).prop('disabled', false));
    });

    // ✅ ملاحظات
    $(document).on('click', '.btn-view-notes', function () {
        $('#notesOrderId').val($(this).data('id'));
        $('#notesDocNumber').text($(this).data('number'));
        $('#notesText').val($(this).data('notes') || '');
        $('#notesModal').modal('show');
    });

    $('#notesForm').on('submit', function (e) {
        e.preventDefault();
        const id = $('#notesOrderId').val();
        const btn = $('#saveNotesBtn');
        const original = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);

        $.post(`{{ url('admin/damaged-barcodes') }}/${id}/notes`, {
            _token: '{{ csrf_token() }}',
            notes: $('#notesText').val()
        })
        .done(res => {
            if (res.success) {
                Swal.fire({ icon: 'success', title: 'تم!', text: res.message, timer: 1500, showConfirmButton: false });
                $('#notesModal').modal('hide');
            } else {
                Swal.fire('خطأ!', res.message, 'error');
            }
        })
        .fail(() => Swal.fire('خطأ!', 'تعذر الحفظ', 'error'))
        .always(() => btn.html(original).prop('disabled', false));
    });

    // ✅ تصدير Excel
    $('#exportBtn').on('click', function () {
        const btn = $(this);
        const original = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin"></i> جاري التصدير...').prop('disabled', true);

        const params = {
            status: $('#filterStatus').val(),
            supplier_id: $('#filterSupplier').val(),
            product_id: $('#filterProduct').val(),
            warehouse_id: $('#filterWarehouse').val(),
            date_from: $('#filterDateFrom').val(),
            date_to: $('#filterDateTo').val()
        };

        $.get("{{ route('admin.damaged-barcodes.export') }}", params)
        .done(res => {
            const rows = res.data || [];
            if (!rows.length) {
                Swal.fire('تنبيه', 'لا توجد بيانات للتصدير', 'warning');
                return;
            }

            const statusMap = {
                pending: 'قيد الانتظار',
                delivered: 'تم التسليم',
                replaced: 'تم الاستبدال'
            };

            const data = rows.map((r, i) => ({
                '#': i + 1,
                'رقم الإذن': r.document_number,
                'التشغيلة': r.batch_number || '',
                'المورد': r.supplier_name || '',
                'المنتج': r.product_name || '',
                'المخزن': r.warehouse_name || '',
                'الباركود التالف': r.damaged_barcode,
                'الحالة': statusMap[r.damaged_barcode_status] || r.damaged_barcode_status,
                'تاريخ التسليم': r.damaged_barcode_delivered_at
                    ? r.damaged_barcode_delivered_at.substring(0, 16).replace('T', ' ') : '',
                'ملاحظات': r.damaged_barcode_notes || '',
                'تاريخ الشحنة': r.created_at ? r.created_at.substring(0, 10) : ''
            }));

            const ws = XLSX.utils.json_to_sheet(data);
            ws['!cols'] = [
                {wch:6},{wch:16},{wch:14},{wch:22},{wch:22},{wch:18},
                {wch:14},{wch:16},{wch:18},{wch:30},{wch:14}
            ];
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, 'الباركود التالف');
            XLSX.writeFile(wb, 'الباركود_التالف_' + new Date().toISOString().slice(0, 10) + '.xlsx');

            Swal.fire({ icon: 'success', title: 'تم!', text: `تم تصدير ${rows.length} سجل`, timer: 2000, showConfirmButton: false });
        })
        .fail(() => Swal.fire('خطأ!', 'تعذر التصدير', 'error'))
        .always(() => btn.html(original).prop('disabled', false));
    });
});
</script>
@endpush