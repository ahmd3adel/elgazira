{{-- resources/views/backend/drivers/index.blade.php --}}

@extends('backend.app')
@section('title', 'إدارة المناديب')
@section('breadcrumb-title', 'إدارة المناديب')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">المناديب</li>
@endsection

@push('custom-css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css" rel="stylesheet" />
    
    <style>
        /* ========== إصلاح مشكلة الفوتر ========== */
        .content-wrapper {
            min-height: auto !important;
            padding-bottom: 0 !important;
            margin-bottom: 0 !important;
        }
        
        .main-footer {
            position: relative !important;
            margin-top: 0 !important;
            clear: both !important;
            background: #fff !important;
            border-top: 1px solid #dee2e6 !important;
            padding: 15px !important;
        }
        
        /* ========== تنسيق الجدول ========== */
        #drivers-table_wrapper {
            overflow-x: auto;
        }
        
        #drivers-table {
            width: 100% !important;
            border-collapse: collapse !important;
        }
        
        #drivers-table thead th {
            background-color: #4e73df !important;
            color: white !important;
            font-weight: 600 !important;
            padding: 12px 8px !important;
            white-space: nowrap !important;
            border: 1px solid #3a5ec7 !important;
        }
        
        #drivers-table tbody td {
            padding: 10px 8px !important;
            vertical-align: middle !important;
            border: 1px solid #e3e6f0 !important;
        }
        
        /* أزرار التصدير */
        .dt-buttons {
            margin-bottom: 15px;
            float: left;
        }
        
        .dt-buttons .btn {
            margin-left: 5px;
            border-radius: 4px;
            font-size: 12px;
            padding: 5px 12px;
        }
        
        /* شريط البحث */
        .dataTables_filter {
            float: right !important;
            margin-bottom: 15px !important;
        }
        
        .dataTables_filter input {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 5px 10px;
            margin-right: 5px;
        }
        
        /* الصورة المصغرة */
        .driver-certificate-thumb {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 6px;
            cursor: pointer;
            border: 1px solid #ddd;
        }
        
        .driver-certificate-thumb:hover {
            transform: scale(1.1);
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        
        /* كروت الإحصائيات */
        .small-box {
            border-radius: 8px;
            box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        }
        
        /* إصلاح عرض الصفوف */
        #drivers-table tbody tr {
            background-color: #fff;
        }
        
        #drivers-table tbody tr:hover {
            background-color: #f5f5f5;
        }
    </style>
@endpush

@section('content')
    {{-- كروت الإحصائيات --}}
    <div class="row">
        <div class="col-lg-3 col-md-6 col-12 mb-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($stats['total'] ?? 0) }}</h3>
                    <p>إجمالي المناديب</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 col-12 mb-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format($stats['active'] ?? 0) }}</h3>
                    <p>مندوب نشط</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-check"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 col-12 mb-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ number_format($stats['training_completed'] ?? 0) }}</h3>
                    <p>مكتملي التدريب</p>
                </div>
                <div class="icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 col-12 mb-3">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ number_format($stats['health_expired'] ?? 0) }}</h3>
                    <p>شهادات منتهية</p>
                </div>
                <div class="icon">
                    <i class="fas fa-file-alt"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- فلتر البحث --}}
    <div class="row">
        <div class="col-12">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-filter ml-1"></i>
                        تصفية البيانات
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <label>حالة التدريب</label>
                            <select id="filter_training" class="form-control">
                                <option value="">الكل</option>
                                <option value="pending">قيد الانتظار</option>
                                <option value="completed">مكتمل</option>
                                <option value="failed">راسب</option>
                                <option value="not_scheduled">غير مجدول</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label>موقف الشهادة الصحية</label>
                            <select id="filter_health" class="form-control">
                                <option value="">الكل</option>
                                <option value="pending">قيد الانتظار</option>
                                <option value="valid">سارية</option>
                                <option value="expired">منتهية</option>
                                <option value="not_required">غير مطلوبة</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label>الحالة العامة</label>
                            <select id="filter_status" class="form-control">
                                <option value="">الكل</option>
                                <option value="active">نشط</option>
                                <option value="inactive">غير نشط</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-12">
                            <button class="btn btn-primary" id="applyFilters">
                                <i class="fas fa-search"></i> بحث
                            </button>
                            <button class="btn btn-secondary" id="resetFilters">
                                <i class="fas fa-undo"></i> إعادة تعيين
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- جدول المناديب --}}
    <div class="row">
        <div class="col-12">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-truck ml-1"></i>
                        قائمة المناديب
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#addDriverModal">
                            <i class="fas fa-plus"></i> إضافة مندوب
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="drivers-table" class="table table-bordered table-hover" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>الاسم</th>
                                    <th>رقم الخط</th>
                                    <th>رقم البطاقة</th>
                                    <th>موقف الشهادة الصحية</th>
                                    <th>صورة الشهادة</th>
                                    <th>رقم الموبايل</th>
                                    <th>حالة التدريب</th>
                                    <th>تاريخ التدريب</th>
                                    <th>الحالة</th>
                                    <th>العمليات</th>
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

    {{-- مودال إضافة مندوب --}}
    <div class="modal fade" id="addDriverModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-user-plus ml-1"></i> إضافة مندوب جديد</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <form id="driverForm" method="POST" action="{{ route('admin.drivers.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>الاسم <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>رقم الخط</label>
                                <input type="text" name="line_number" id="line_number" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>رقم البطاقة <span class="text-danger">*</span></label>
                                <input type="text" name="national_id" id="national_id" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>رقم الموبايل</label>
                                <input type="text" name="phone" id="phone" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>موقف الشهادة الصحية</label>
                                <select name="health_certificate_status" id="health_certificate_status" class="form-control">
                                    <option value="pending">قيد الانتظار</option>
                                    <option value="valid">سارية</option>
                                    <option value="expired">منتهية</option>
                                    <option value="not_required">غير مطلوبة</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>صورة الشهادة</label>
                                <input type="file" name="health_certificate_image" id="health_certificate_image" class="form-control-file" accept="image/*">
                                <div id="imagePreviewContainer" class="mt-2" style="display:none">
                                    <img id="imagePreview" style="max-width:150px" class="img-thumbnail">
                                    <button type="button" class="btn btn-sm btn-danger" id="removePreview">حذف</button>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>حالة التدريب</label>
                                <select name="training_status" id="training_status" class="form-control">
                                    <option value="pending">قيد الانتظار</option>
                                    <option value="completed">مكتمل</option>
                                    <option value="failed">راسب</option>
                                    <option value="not_scheduled">غير مجدول</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>تاريخ التدريب</label>
                                <input type="date" name="training_date" id="training_date" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>الحالة العامة</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="active">نشط</option>
                                    <option value="inactive">غير نشط</option>
                                </select>
                            </div>
                            <div class="col-12 mb-3">
                                <label>ملاحظات</label>
                                <textarea name="notes" id="notes" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">حفظ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- مودال عرض الصورة --}}
    <div class="modal fade" id="imageModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">الشهادة الصحية</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" style="max-width:100%; max-height:70vh;">
                </div>
            </div>
        </div>
    </div>

    {{-- مودال تعديل مندوب --}}
    <div class="modal fade" id="editDriverModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title">تعديل بيانات المندوب</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <form id="editDriverForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="edit_id" name="id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>الاسم</label>
                                <input type="text" id="edit_name" name="name" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>رقم الخط</label>
                                <input type="text" id="edit_line_number" name="line_number" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>رقم البطاقة</label>
                                <input type="text" id="edit_national_id" name="national_id" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>رقم الموبايل</label>
                                <input type="text" id="edit_phone" name="phone" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>موقف الشهادة الصحية</label>
                                <select id="edit_health_certificate_status" name="health_certificate_status" class="form-control">
                                    <option value="pending">قيد الانتظار</option>
                                    <option value="valid">سارية</option>
                                    <option value="expired">منتهية</option>
                                    <option value="not_required">غير مطلوبة</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>الصورة الحالية</label>
                                <div id="current_image_container"></div>
                                <label>تغيير الصورة</label>
                                <input type="file" id="edit_health_certificate_image" name="health_certificate_image" class="form-control-file" accept="image/*">
                                <div id="editImagePreviewContainer" class="mt-2" style="display:none">
                                    <img id="editImagePreview" style="max-width:150px" class="img-thumbnail">
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>حالة التدريب</label>
                                <select id="edit_training_status" name="training_status" class="form-control">
                                    <option value="pending">قيد الانتظار</option>
                                    <option value="completed">مكتمل</option>
                                    <option value="failed">راسب</option>
                                    <option value="not_scheduled">غير مجدول</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>تاريخ التدريب</label>
                                <input type="date" id="edit_training_date" name="training_date" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>الحالة العامة</label>
                                <select id="edit_status" name="status" class="form-control">
                                    <option value="active">نشط</option>
                                    <option value="inactive">غير نشط</option>
                                </select>
                            </div>
                            <div class="col-12 mb-3">
                                <label>ملاحظات</label>
                                <textarea id="edit_notes" name="notes" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                        <button type="button" class="btn btn-primary" id="btnUpdateDriver">تحديث</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('custom-js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    
    <script>
    var table;
    
    function showImageModal(url) {
        $('#modalImage').attr('src', url);
        $('#imageModal').modal('show');
    }
    
    $(document).ready(function() {
        table = $('#drivers-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.drivers.index') }}",
                data: function(d) {
                    d.training_status = $('#filter_training').val();
                    d.health_status = $('#filter_health').val();
                    d.status = $('#filter_status').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'id', orderable: false, searchable: false },
                { data: 'name', name: 'name' },
                { data: 'line_number', name: 'line_number' },
                { data: 'national_id', name: 'national_id' },
                { data: 'health_certificate_badge', name: 'health_certificate_status' },
                { data: 'health_certificate_image', name: 'health_certificate_image', orderable: false },
                { data: 'phone', name: 'phone' },
                { data: 'training_status_badge', name: 'training_status' },
                { data: 'training_date', name: 'training_date' },
                { data: 'status_badge', name: 'status' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            language: { url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/ar.json" },
            order: [[1, 'asc']],
            dom: 'Bfrtip',
            buttons: [
                { extend: 'excelHtml5', text: '<i class="fas fa-file-excel"></i> Excel', className: 'btn btn-success btn-sm', exportOptions: { columns: [0,1,2,3,4,6,7,8,9] } },
                { extend: 'pdfHtml5', text: '<i class="fas fa-file-pdf"></i> PDF', className: 'btn btn-danger btn-sm', exportOptions: { columns: [0,1,2,3,4,6,7,8,9] } },
                { extend: 'copyHtml5', text: '<i class="fas fa-copy"></i> نسخ', className: 'btn btn-secondary btn-sm', exportOptions: { columns: [0,1,2,3,4,6,7,8,9] } },
                { extend: 'print', text: '<i class="fas fa-print"></i> طباعة', className: 'btn btn-info btn-sm', exportOptions: { columns: [0,1,2,3,4,6,7,8,9] } }
            ]
        });
        
        // إضافة زر Word
        $('.dt-buttons').append('<button class="btn btn-primary btn-sm" id="exportWordBtn"><i class="fas fa-file-word"></i> Word</button>');
        
        $('#exportWordBtn').on('click', function() {
            var data = table.rows().data();
            var html = '<html dir="rtl"><head><meta charset="UTF-8"><title>تقرير المناديب</title><style>table{border-collapse:collapse;width:100%}th,td{border:1px solid #ddd;padding:8px;text-align:center}th{background:#4e73df;color:#fff}</style></head><body><h1>تقرير المناديب</h1><table><thead><tr><th>#</th><th>الاسم</th><th>رقم الخط</th><th>رقم البطاقة</th><th>الشهادة</th><th>الموبايل</th><th>التدريب</th><th>تاريخ التدريب</th><th>الحالة</th></tr></thead><tbody>';
            $.each(data, function(i, row) {
                html += `<tr><td>${row.DT_RowIndex}</td><td>${row.name}</td><td>${row.line_number||'-'}</td><td>${row.national_id}</td><td>${row.health_certificate_status}</td><td>${row.phone||'-'}</td><td>${row.training_status}</td><td>${row.training_date||'-'}</td><td>${row.status}</td></tr>`;
            });
            html += '</tbody></table></body></html>';
            var blob = new Blob([html], {type: 'application/msword'});
            var link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'drivers_report.doc';
            link.click();
        });
        
        $('#applyFilters').click(function() { table.ajax.reload(); });
        $('#resetFilters').click(function() {
            $('#filter_training, #filter_health, #filter_status').val('');
            table.ajax.reload();
        });
        
        // إضافة مندوب
        $('#driverForm').submit(function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            $.ajax({
                url: "{{ route('admin.drivers.store') }}",
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    if(res.success) {
                        Swal.fire('تم', res.message, 'success');
                        $('#addDriverModal').modal('hide');
                        $('#driverForm')[0].reset();
                        table.ajax.reload();
                    }
                },
                error: function(xhr) {
                    Swal.fire('خطأ', xhr.responseJSON?.message || 'حدث خطأ', 'error');
                }
            });
        });
        
        // معاينة الصورة
        $('#health_certificate_image').on('change', function() {
            if(this.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#imagePreview').attr('src', e.target.result);
                    $('#imagePreviewContainer').show();
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
        $('#removePreview').click(function() {
            $('#health_certificate_image').val('');
            $('#imagePreviewContainer').hide();
        });
        
        // تعديل مندوب
        $(document).on('click', '.edit-driver', function() {
            var id = $(this).data('id');
            $.ajax({
                url: "{{ url('admin/drivers') }}/" + id + "/edit",
                success: function(data) {
                    $('#edit_id').val(data.id);
                    $('#edit_name').val(data.name);
                    $('#edit_line_number').val(data.line_number);
                    $('#edit_national_id').val(data.national_id);
                    $('#edit_phone').val(data.phone);
                    $('#edit_health_certificate_status').val(data.health_certificate_status);
                    $('#edit_training_status').val(data.training_status);
                    $('#edit_training_date').val(data.training_date);
                    $('#edit_notes').val(data.notes);
                    $('#edit_status').val(data.status);
                    if(data.health_certificate_image_url) {
                        $('#current_image_container').html(`<a href="${data.health_certificate_image_url}" target="_blank" class="btn btn-sm btn-info">عرض الصورة</a>`);
                    }
                    $('#editDriverModal').modal('show');
                }
            });
        });
        
        // تحديث مندوب
        $('#btnUpdateDriver').click(function() {
            var id = $('#edit_id').val();
            var formData = new FormData($('#editDriverForm')[0]);
            formData.append('_method', 'PUT');
            $.ajax({
                url: "{{ url('admin/drivers') }}/" + id,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    if(res.success) {
                        Swal.fire('تم', res.message, 'success');
                        $('#editDriverModal').modal('hide');
                        table.ajax.reload();
                    }
                }
            });
        });
        
        // حذف مندوب
        $(document).on('click', '.delete-driver', function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            Swal.fire({
                title: 'تأكيد الحذف',
                html: `هل تريد حذف المندوب: <strong>${name}</strong>؟`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'نعم',
                cancelButtonText: 'إلغاء'
            }).then((result) => {
                if(result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('admin/drivers') }}/" + id,
                        method: 'DELETE',
                        data: { _token: "{{ csrf_token() }}" },
                        success: function(res) {
                            if(res.success) {
                                Swal.fire('تم الحذف', res.message, 'success');
                                table.ajax.reload();
                            }
                        }
                    });
                }
            });
        });
    });
    </script>
@endpush