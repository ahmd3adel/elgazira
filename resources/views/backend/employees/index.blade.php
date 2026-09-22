@extends('backend.app')
@section('title', 'إدارة العاملين')
@section('breadcrumb-title', 'إدارة النظام')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">العاملين</li>
@endsection

@push('custom-css')
    <style>
        .card-title i {
            color: #17a2b8;
        }
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
                    <i class="fas fa-users ml-2"></i>
                    قائمة العاملين المسجلين
                </h3>
                <div class="card-tools d-flex align-items-center">
                    <button type="button" class="btn btn-outline-info btn-sm ml-2"
                        onclick="table.ajax.reload(null, false)">
                        <i class="fas fa-sync-alt"></i> تحديث
                    </button>
                    <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#addEmployeeModal">
                        <i class="fas fa-plus"></i> إضافة عامل جديد
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="employees-table" class="table table-bordered table-striped nowrap" style="width:100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>اسم العامل</th>
                                <th>رقم الهاتف</th>
                                <th>المسمى الوظيفي</th>
                                <th>المخزن التابع</th>
                                <th>الشهادة الصحية</th>
                                <th>الحالة</th>
                                <th>العمليات</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- Modal: إضافة عامل جديد --}}
    {{-- ============================================================ --}}
    <div class="modal fade" id="addEmployeeModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-user-plus ml-2"></i> إضافة عامل جديد
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="addEmployeeForm" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>اسم العامل <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>رقم الهاتف</label>
                                    <input type="text" class="form-control" id="phone" name="phone">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>المسمى الوظيفي</label>
                                    <input type="text" class="form-control" id="job_title" name="job_title">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>المخزن التابع</label>
                                    <select class="form-control" id="warehouse_id" name="warehouse_id">
                                        <option value="">-- بدون مخزن --</option>
                                        @foreach ($warehouses as $warehouse)
                                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        {{-- حقول الشهادة الصحية الجديدة --}}
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>صورة الشهادة الصحية</label>
                                    <input type="file" class="form-control-file" id="health_certificate_image" name="health_certificate_image" accept="image/*">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>تاريخ إصدار الشهادة</label>
                                    <input type="date" class="form-control" id="health_certificate_issued_at" name="health_certificate_issued_at">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>تاريخ انتهاء الشهادة</label>
                                    <input type="date" class="form-control" id="health_certificate_expires_at" name="health_certificate_expires_at">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>الحالة</label>
                                    <select class="form-control" id="is_active" name="is_active">
                                        <option value="1">نشط</option>
                                        <option value="0">غير نشط</option>
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
                    <button type="button" class="btn btn-info px-4" id="btnSaveEmployee">
                        <i class="fas fa-save ml-1"></i> حفظ
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- Modal: تعديل بيانات العامل --}}
    {{-- ============================================================ --}}
    <div class="modal fade" id="editEmployeeModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-user-edit ml-2"></i> تعديل بيانات العامل
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editEmployeeForm" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" id="edit_id" name="id">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>اسم العامل <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="edit_name" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>رقم الهاتف</label>
                                    <input type="text" class="form-control" id="edit_phone" name="phone">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>المسمى الوظيفي</label>
                                    <input type="text" class="form-control" id="edit_job_title" name="job_title">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>المخزن التابع</label>
                                    <select class="form-control" id="edit_warehouse_id" name="warehouse_id">
                                        <option value="">-- بدون مخزن --</option>
                                        @foreach ($warehouses as $warehouse)
                                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        {{-- حقول الشهادة الصحية الجديدة للتعديل --}}
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>صورة الشهادة الصحية</label>
                                    <input type="file" class="form-control-file" id="edit_health_certificate_image" name="health_certificate_image" accept="image/*">
                                    <small class="form-text text-muted" id="current_certificate_wrapper"></small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>تاريخ إصدار الشهادة</label>
                                    <input type="date" class="form-control" id="edit_health_certificate_issued_at" name="health_certificate_issued_at">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>تاريخ انتهاء الشهادة</label>
                                    <input type="date" class="form-control" id="edit_health_certificate_expires_at" name="health_certificate_expires_at">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>الحالة</label>
                                    <select class="form-control" id="edit_is_active" name="is_active">
                                        <option value="1">نشط</option>
                                        <option value="0">غير نشط</option>
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
                    <button type="button" class="btn btn-primary px-4" id="btnUpdateEmployee">
                        <i class="fas fa-save ml-1"></i> حفظ التعديلات
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('custom-js')
    <script>
        $(document).ready(function() {

            // ============================================================
            // DataTable
            // ============================================================
            var table = $('#employees-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: false,
                scrollX: true,
                autoWidth: false,

                ajax: {
                    url: "{{ route('admin.employees.index') }}",
                    type: 'GET',
                    error: function() {
                        $('#employees-table tbody').html(
                            '<tr><td colspan="8" class="text-center text-danger py-3">' +
                            '<i class="fas fa-exclamation-triangle ml-1"></i> حدث خطأ أثناء تحميل البيانات. ' +
                            '<a href="#" onclick="table.ajax.reload()">أعد المحاولة</a>' +
                            '</td></tr>'
                        );
                    }
                },

                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, width: '50px' },
                    { data: 'name', name: 'name' },
                    { data: 'phone', name: 'phone', defaultContent: '<i class="text-muted small">—</i>' },
                    { data: 'job_title', name: 'job_title', defaultContent: '<i class="text-muted small">غير محدد</i>' },
                    { data: 'warehouse_name', name: 'warehouse_id', className: 'text-center' },
                    { data: 'health_certificate', name: 'health_certificate_expires_at', className: 'text-center', orderable: false, searchable: false },
                    { data: 'is_active', name: 'is_active', className: 'text-center', orderable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center', width: '100px' },
                ],

                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100], ['10', '25', '50', '100']],
                searchDelay: 400,
                order: [[0, 'asc']],

                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/ar.json',
                    processing: '<div class="d-flex align-items-center justify-content-center py-2">' +
                        '<span class="spinner-border spinner-border-sm text-info ml-2"></span>' +
                        '<span>جاري التحميل...</span></div>',
                },
                
                dom: "<'row align-items-center mb-3'<'col-sm-4'l><'col-sm-4 text-center'B><'col-sm-4'f>>" +
                     "<'row'<'col-12'tr>>" +
                     "<'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",

                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel ml-1"></i> تصدير Excel',
                        className: 'btn btn-success btn-sm',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 6] // تم تعديل الفهارس لتناسب إضافة عمود الشهادة الصحية
                        }
                    },
                    {
                        extend: 'copyHtml5',
                        text: '<i class="fas fa-copy ml-1"></i> نسخ',
                        className: 'btn btn-secondary btn-sm',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 6]
                        }
                    }
                ],

                drawCallback: function() {
                    setTimeout(function() {
                        table.columns.adjust();
                    }, 10);
                },

                initComplete: function() {
                    $(window).on('resize', function() {
                        table.columns.adjust();
                    });
                    $('.nav-link').on('click', function() {
                        setTimeout(function() {
                            table.columns.adjust();
                        }, 300);
                    });
                }
            });

            window.table = table;

            // ============================================================
            // إضافة عامل
            // ============================================================
            $('#btnSaveEmployee').click(function() {
                let name = $('#name').val().trim();

                if (!name) {
                    return Swal.fire('تنبيه', 'يرجى إدخال اسم العامل', 'warning');
                }

                let btn = $(this);
                let originalHtml = btn.html();
                btn.html('<i class="fas fa-spinner fa-spin"></i> جاري الحفظ...').prop('disabled', true);

                $.ajax({
                    url: "{{ route('admin.employees.store') }}",
                    method: 'POST',
                    data: new FormData($('#addEmployeeForm')[0]),
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('تم!', response.message, 'success');
                            $('#addEmployeeModal').modal('hide');
                            $('#addEmployeeForm')[0].reset();
                            table.ajax.reload(null, false);
                        } else {
                            Swal.fire('خطأ!', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = xhr.responseJSON?.message || 'حدث خطأ أثناء الحفظ';
                        if (xhr.status === 422) {
                            errorMsg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'خطأ!',
                            html: errorMsg
                        });
                    },
                    complete: function() {
                        btn.html(originalHtml).prop('disabled', false);
                    }
                });
            });

            $('#addEmployeeModal').on('hidden.bs.modal', function() {
                $('#addEmployeeForm')[0].reset();
            });

            // ============================================================
            // جلب بيانات التعديل
            // ============================================================
            $(document).on('click', '.edit-employee', function() {
                let id = $(this).data('id');

                Swal.fire({
                    title: 'جاري جلب البيانات...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                $.ajax({
                    url: "{{ url('admin/employees') }}/" + id,
                    method: 'GET',
                    success: function(data) {
                        Swal.close();
                        $('#edit_id').val(data.id);
                        $('#edit_name').val(data.name);
                        $('#edit_phone').val(data.phone || '');
                        $('#edit_job_title').val(data.job_title || '');
                        $('#edit_warehouse_id').val(data.warehouse_id || '');
                        $('#edit_health_certificate_issued_at').val(data.health_certificate_issued_at || '');
                        $('#edit_health_certificate_expires_at').val(data.health_certificate_expires_at || '');
                        $('#edit_is_active').val(data.is_active ? 1 : 0);
                        $('#edit_notes').val(data.notes || '');

                        // عرض رابط لمعاينة الصورة الحالية إن وجدت
                        if (data.health_certificate_image) {
                            $('#current_certificate_wrapper').html(
                                '<a href="' + data.health_certificate_image_url + '" target="_blank" class="text-info"><i class="fas fa-image ml-1"></i>عرض الشهادة الحالية</a>'
                            );
                        } else {
                            $('#current_certificate_wrapper').html('لا توجد صورة مرفقة');
                        }

                        $('#editEmployeeModal').modal('show');
                    },
                    error: function() {
                        Swal.close();
                        Swal.fire('خطأ!', 'تعذر جلب بيانات العامل', 'error');
                    }
                });
            });

            // ============================================================
            // حفظ التعديل
            // ============================================================
            $('#btnUpdateEmployee').click(function() {
                let id = $('#edit_id').val();
                let name = $('#edit_name').val().trim();

                if (!name) {
                    return Swal.fire('تنبيه', 'يرجى إدخال اسم العامل', 'warning');
                }

                let btn = $(this);
                let originalHtml = btn.html();
                btn.html('<i class="fas fa-spinner fa-spin"></i> جاري التحديث...').prop('disabled', true);

                let formData = new FormData($('#editEmployeeForm')[0]);
                formData.append('_method', 'PUT');

                $.ajax({
                    url: "{{ url('admin/employees') }}/" + id,
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
                            $('#editEmployeeModal').modal('hide');
                            table.ajax.reload(null, false);
                        } else {
                            Swal.fire('خطأ!', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = xhr.responseJSON?.message || 'حدث خطأ أثناء التحديث';
                        if (xhr.status === 422) {
                            errorMsg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'خطأ!',
                            html: errorMsg
                        });
                    },
                    complete: function() {
                        btn.html(originalHtml).prop('disabled', false);
                    }
                });
            });

            // ============================================================
            // حذف عامل
            // ============================================================
            $(document).on('click', '.delete-employee', function() {
                let id = $(this).data('id');
                let name = $(this).data('name');

                Swal.fire({
                    title: 'هل أنت متأكد؟',
                    html: 'هل تريد حذف العامل <strong>' + name + '</strong>؟',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'نعم، احذفه',
                    cancelButtonText: 'إلغاء'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'جاري الحذف...',
                            allowOutsideClick: false,
                            didOpen: () => Swal.showLoading()
                        });

                        $.ajax({
                            url: "{{ url('admin/employees') }}/" + id,
                            method: 'DELETE',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire('تم الحذف!', response.message, 'success');
                                    table.ajax.reload(null, false);
                                } else {
                                    Swal.fire('خطأ!', response.message, 'error');
                                }
                            },
                            error: function(xhr) {
                                Swal.close();
                                let errorMsg = xhr.responseJSON?.message || 'حدث خطأ أثناء الحذف';
                                Swal.fire('خطأ!', errorMsg, 'error');
                            }
                        });
                    }
                });
            });

        });
    </script>
@endpush