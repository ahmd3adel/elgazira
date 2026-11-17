@extends('backend.app')
@section('title', 'إدارة المحافظات')
@section('breadcrumb-title', 'إدارة الموقع')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">المحافظات</li>
@endsection

@push('custom-css')
    <style>
        .card-title i { color: #17a2b8; }
    </style>
@endpush

@section('content')
    <div class="container-fluid">

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle ml-1"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-times-circle ml-1"></i> {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        @endif

        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-map-marker-alt ml-2"></i>
                    قائمة المحافظات المسجلة
                </h3>
<div class="card-tools d-flex align-items-center">
    <button type="button" class="btn btn-outline-info btn-sm ml-2" onclick="table.ajax.reload(null, false)">
        <i class="fas fa-sync-alt"></i> تحديث
    </button>
    <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#addGovernorateModal">
        <i class="fas fa-plus"></i> إضافة محافظة جديدة
    </button>
</div>
            </div>
            <div class="card-body">
                @if ($governorates->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-map-marked-alt fa-3x mb-3"></i>
                        <p class="mb-0">لا توجد محافظات مسجلة بعد</p>
                        <button type="button" class="btn btn-outline-info btn-sm mt-3"
                            data-toggle="modal" data-target="#addGovernorateModal">
                            <i class="fas fa-plus"></i> أضف أول محافظة
                        </button>
                    </div>
                @else
                    <div class="table-responsive">
                        <table id="governorates-table" class="table table-bordered table-striped nowrap">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>كود المحافظة</th>
                                    <th>اسم المحافظة</th>
                                    <th>المسؤول</th>
                                    <th>الهاتف</th>
                                    <th>الحالة</th>
                                    <th>العمليات</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- Modal: إضافة محافظة --}}
    {{-- ============================================================ --}}
    <div class="modal fade" id="addGovernorateModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-plus-circle ml-2"></i> إضافة محافظة جديدة
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="addGovernorateForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>اسم المحافظة <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>كود المحافظة <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="code" name="code" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>اسم المسؤول</label>
                                    <input type="text" class="form-control" id="manager_name" name="manager_name">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>رقم الهاتف</label>
                                    <input type="text" class="form-control" id="manager_phone" name="manager_phone">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>الحالة</label>
                                    <select class="form-control" id="status" name="status">
                                        <option value="1">نشط</option>
                                        <option value="0">معطل</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>ملاحظات</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="1"></textarea>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn btn-info px-4" id="btnSaveGovernorate">
                        <i class="fas fa-save ml-1"></i> حفظ
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- Modal: تعديل محافظة --}}
    {{-- ============================================================ --}}
    <div class="modal fade" id="editGovernorateModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-edit ml-2"></i> تعديل بيانات المحافظة
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editGovernorateForm">
                        @csrf
                        <input type="hidden" id="edit_id" name="id">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>اسم المحافظة <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="edit_name" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>كود المحافظة <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="edit_code" name="code" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>اسم المسؤول</label>
                                    <input type="text" class="form-control" id="edit_manager_name" name="manager_name">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>رقم الهاتف</label>
                                    <input type="text" class="form-control" id="edit_manager_phone" name="manager_phone">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>الحالة</label>
                                    <select class="form-control" id="edit_status" name="status">
                                        <option value="1">نشط</option>
                                        <option value="0">معطل</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>ملاحظات</label>
                                    <textarea class="form-control" id="edit_notes" name="notes" rows="1"></textarea>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn btn-primary px-4" id="btnUpdateGovernorate">
                        <i class="fas fa-save ml-1"></i> حفظ التعديلات
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('custom-js')
<script>
$(document).ready(function () {

    // ============================================================
    // DataTable
    // ============================================================
    var table = $('#governorates-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: false,
        scrollX: true,
        autoWidth: false,

        ajax: {
            url: "{{ route('admin.governorates.index') }}",
            type: 'GET',
            error: function () {
                $('#governorates-table tbody').html(
                    '<tr><td colspan="7" class="text-center text-danger py-3">' +
                    '<i class="fas fa-exclamation-triangle ml-1"></i> حدث خطأ أثناء تحميل البيانات. ' +
                    '<a href="#" onclick="table.ajax.reload()">أعد المحاولة</a>' +
                    '</td></tr>'
                );
            }
        },

        columns: [
            { data: 'DT_RowIndex',   name: 'DT_RowIndex',   orderable: false, searchable: false, width: '50px' },
            { data: 'code',          name: 'code',           className: 'text-center', width: '100px' },
            { data: 'name',          name: 'name' },
            { data: 'manager_name',  name: 'manager_name',  defaultContent: '<i class="text-muted small">غير محدد</i>' },
            { data: 'manager_phone', name: 'manager_phone', defaultContent: '<i class="text-muted small">—</i>' },
            { data: 'status',        name: 'status',        className: 'text-center', orderable: false },
            { data: 'action',        name: 'action',        orderable: false, searchable: false, className: 'text-center', width: '100px' },
        ],

        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], ['10', '25', '50', '100']],
        searchDelay: 400,
        order: [[0, 'asc']],

        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/ar.json',
            processing:
                '<div class="d-flex align-items-center justify-content-center py-2">' +
                '<span class="spinner-border spinner-border-sm text-info ml-2"></span>' +
                '<span>جاري التحميل...</span></div>',
        },
dom: "<'row align-items-center mb-3'<'col-sm-4'l><'col-sm-4 text-center'B><'col-sm-4'f>>" +
     "<'row'<'col-12'tr>>" +
     "<'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",

buttons: [],

        drawCallback: function () {
            setTimeout(function () { table.columns.adjust(); }, 10);
        },

        initComplete: function () {
            $(window).on('resize', function () { table.columns.adjust(); });
            $('.nav-link').on('click', function () {
                setTimeout(function () { table.columns.adjust(); }, 300);
            });
        }
    });

    window.table = table;

    // ============================================================
    // إضافة محافظة
    // ============================================================
    $('#btnSaveGovernorate').click(function () {
        let name = $('#name').val().trim();
        let code = $('#code').val().trim();

        if (!name || !code) {
            return Swal.fire('تنبيه', 'يرجى إدخال اسم المحافظة وكودها', 'warning');
        }

        let btn = $(this);
        let originalHtml = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin"></i> جاري الحفظ...').prop('disabled', true);

        $.ajax({
            url: "{{ route('admin.governorates.store') }}",
            method: 'POST',
            data: new FormData($('#addGovernorateForm')[0]),
            processData: false,
            contentType: false,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (response) {
                if (response.success) {
                    Swal.fire('تم!', response.message, 'success');
                    $('#addGovernorateModal').modal('hide');
                    $('#addGovernorateForm')[0].reset();
                    table.ajax.reload(null, false);
                } else {
                    Swal.fire('خطأ!', response.message, 'error');
                }
            },
            error: function (xhr) {
                let errorMsg = xhr.responseJSON?.message || 'حدث خطأ أثناء الحفظ';
                if (xhr.status === 422) {
                    errorMsg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                }
                Swal.fire({ icon: 'error', title: 'خطأ!', html: errorMsg });
            },
            complete: function () {
                btn.html(originalHtml).prop('disabled', false);
            }
        });
    });

    $('#addGovernorateModal').on('hidden.bs.modal', function () {
        $('#addGovernorateForm')[0].reset();
    });

    // ============================================================
    // جلب بيانات التعديل
    // ============================================================
    $(document).on('click', '.edit-governorate', function () {
        let id = $(this).data('id');

        Swal.fire({ title: 'جاري جلب البيانات...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        $.ajax({
            url: "{{ url('admin/governorates') }}/" + id,
            method: 'GET',
            success: function (data) {
                Swal.close();
                $('#edit_id').val(data.id);
                $('#edit_name').val(data.name);
                $('#edit_code').val(data.code);
                $('#edit_manager_name').val(data.manager_name || '');
                $('#edit_manager_phone').val(data.manager_phone || '');
                $('#edit_status').val(data.status ? 1 : 0);
                $('#edit_notes').val(data.notes || '');
                $('#editGovernorateModal').modal('show');
            },
            error: function () {
                Swal.close();
                Swal.fire('خطأ!', 'تعذر جلب بيانات المحافظة', 'error');
            }
        });
    });

    // ============================================================
    // حفظ التعديل
    // ============================================================
    $('#btnUpdateGovernorate').click(function () {
        let id   = $('#edit_id').val();
        let name = $('#edit_name').val().trim();

        if (!name) {
            return Swal.fire('تنبيه', 'يرجى إدخال اسم المحافظة', 'warning');
        }

        let btn = $(this);
        let originalHtml = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin"></i> جاري التحديث...').prop('disabled', true);

        let formData = new FormData($('#editGovernorateForm')[0]);
        formData.append('_method', 'PUT');

        $.ajax({
            url: "{{ url('admin/governorates') }}/" + id,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (response) {
                if (response.success) {
                    Swal.fire('تم!', response.message, 'success');
                    $('#editGovernorateModal').modal('hide');
                    table.ajax.reload(null, false);
                } else {
                    Swal.fire('خطأ!', response.message, 'error');
                }
            },
            error: function (xhr) {
                let errorMsg = xhr.responseJSON?.message || 'حدث خطأ أثناء التحديث';
                if (xhr.status === 422) {
                    errorMsg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                }
                Swal.fire({ icon: 'error', title: 'خطأ!', html: errorMsg });
            },
            complete: function () {
                btn.html(originalHtml).prop('disabled', false);
            }
        });
    });

    // ============================================================
    // حذف محافظة
    // ============================================================
    $(document).on('click', '.delete-governorate', function () {
        let id   = $(this).data('id');
        let name = $(this).data('name');

        Swal.fire({
            title: 'هل أنت متأكد؟',
            html: 'هل تريد حذف محافظة <strong>' + name + '</strong>؟',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'نعم، احذفها',
            cancelButtonText: 'إلغاء'
        }).then(function (result) {
            if (result.isConfirmed) {
                Swal.fire({ title: 'جاري الحذف...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                $.ajax({
                    url: "{{ url('admin/governorates') }}/" + id,
                    method: 'DELETE',
                    data: { _token: "{{ csrf_token() }}" },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire('تم الحذف!', response.message, 'success');
                            table.ajax.reload(null, false);
                        } else {
                            Swal.fire('خطأ!', response.message, 'error');
                        }
                    },
                    error: function (xhr) {
                        Swal.close();
                        let errorMsg = xhr.responseJSON?.message || 'حدث خطأ أثناء الحذف';
                        Swal.fire('خطأ!', errorMsg, 'error');
                    }
                });
            }
        });
    });

    // ============================================================
    // Validation errors — فتح الـ modal الصح
    // ============================================================
    @if ($errors->any())
        $('#addGovernorateModal').modal('show');
    @endif

});
</script>
@endpush
