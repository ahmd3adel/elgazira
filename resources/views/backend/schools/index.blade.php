@extends('backend.app')
@section('title', 'إدارة المدارس')
@section('breadcrumb-title', 'إدارة المدارس')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">المدارس</li>
@endsection

@push('custom-css')
<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">
<!-- Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.2/dist/select2-bootstrap4.min.css" rel="stylesheet">
<!-- SweetAlert2 -->
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<!-- Font Awesome 6 -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    * {
        font-family: 'Cairo', sans-serif !important;
    }

    /* ===== تنسيق عام ===== */
    .content-wrapper {
        background: #f4f6f9;
    }
    
    /* ===== DataTable تعريب ===== */
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        font-family: 'Cairo', sans-serif !important;
        font-size: 13px;
        color: #6c757d;
        margin-bottom: 15px;
    }
    
    .dataTables_wrapper .dataTables_filter {
        float: left !important;
        text-align: left;
    }
    
    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 5px 12px;
        font-size: 13px;
        margin-right: 6px;
        outline: none;
        transition: border-color .2s;
    }
    
    .dataTables_wrapper .dataTables_filter input:focus {
        border-color: #17a2b8;
        box-shadow: 0 0 0 2px rgba(23,162,184,.15);
    }
    
    .dataTables_wrapper .dataTables_length {
        float: right !important;
        text-align: right;
    }
    
    .dataTables_wrapper .dataTables_length select {
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 5px 8px;
        font-size: 13px;
        margin: 0 4px;
    }

    /* ===== Pagination ===== */
    .dataTables_wrapper .dataTables_paginate {
        margin-top: 15px;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 6px 12px !important;
        border-radius: 4px !important;
        font-size: 13px !important;
        border: 1px solid #dee2e6 !important;
        margin: 0 3px !important;
        color: #495057 !important;
        background: #fff !important;
        cursor: pointer;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #e9ecef !important;
        border-color: #adb5bd !important;
        color: #212529 !important;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: #17a2b8 !important;
        border-color: #17a2b8 !important;
        color: #fff !important;
        font-weight: 600 !important;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
        color: #adb5bd !important;
        cursor: not-allowed !important;
        background: #fff !important;
    }

    /* ===== الجدول ===== */
    .card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,.08);
        margin-bottom: 25px;
    }
    
    .card-header {
        background: #fff;
        border-bottom: 1px solid #eef2f7;
        padding: 16px 20px;
        border-radius: 12px 12px 0 0 !important;
    }
    
    .card-title {
        font-size: 16px;
        font-weight: 700;
        color: #2c3e50;
    }
    
    .table {
        margin-bottom: 0;
    }
    
    .table thead th {
        background-color: #f8f9fc;
        border-bottom: 2px solid #e3e6f0;
        border-top: none;
        color: #4e73df;
        font-weight: 700;
        font-size: 13px;
        padding: 12px 15px;
        white-space: nowrap;
    }
    
    .table tbody td {
        font-size: 13px;
        padding: 11px 15px;
        color: #5a5c69;
        vertical-align: middle;
        border-bottom: 1px solid #e3e6f0;
    }
    
    .table tbody tr:hover td {
        background-color: #f8f9fc;
    }

    /* ===== أفاتار اسم المدرسة ===== */
    .school-name-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .school-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        font-weight: 700;
        flex-shrink: 0;
    }
    
    .school-name {
        font-weight: 600;
        color: #2c3e50;
    }

    /* ===== بادجات النوع ===== */
    .badge-type {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        text-align: center;
    }
    
    .badge-type.ابتدائي {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .badge-type.اعدادي {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
    }
    
    .badge-type.حضانة {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: white;
    }
    
    .badge-type.تعليم-مجتمعي {
        background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        color: #333;
    }

    /* ===== أزرار العمليات ===== */
    .action-buttons {
        display: flex;
        gap: 6px;
        justify-content: center;
        flex-wrap: wrap;
    }
    
    .action-buttons .btn {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 11px;
        font-weight: 600;
        padding: 5px 12px;
        border-radius: 6px;
        transition: all 0.2s;
    }
    
    .action-buttons .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 2px 6px rgba(0,0,0,.15);
    }
    
    .btn-edit {
        background: #ffc107;
        color: #856404;
        border: none;
    }
    
    .btn-edit:hover {
        background: #e0a800;
        color: #856404;
    }
    
    .btn-delete {
        background: #dc3545;
        color: white;
        border: none;
    }
    
    .btn-delete:hover {
        background: #c82333;
        color: white;
    }

    /* ===== معلومات الجدول ===== */
    .dataTables_info {
        padding-top: 15px !important;
        font-size: 13px;
    }
    
    /* ===== أزرار التصدير ===== */
    .dt-buttons {
        margin-bottom: 15px;
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    
    .dt-buttons .btn {
        border-radius: 6px;
        font-size: 12px;
        padding: 5px 12px;
        margin: 0;
    }
    
    .btn-excel {
        background: #28a745;
        color: white;
        border: none;
    }
    
    .btn-pdf {
        background: #dc3545;
        color: white;
        border: none;
    }
    
    .btn-print {
        background: #17a2b8;
        color: white;
        border: none;
    }

    /* ===== Modal ===== */
    .modal-backdrop {
        z-index: 1040 !important;
    }
    
    .modal {
        z-index: 1050 !important;
    }
    
    .modal-header .close {
        margin: -1rem auto -1rem -1rem !important;
        padding: 1rem;
    }
    
    .modal-content {
        border-radius: 12px;
        border: none;
    }
    
    .modal-header {
        border-radius: 12px 12px 0 0;
    }
    
    .modal-footer {
        border-top: 1px solid #eef2f7;
    }

    /* ===== تنسيق الفورم ===== */
    .form-group label {
        font-weight: 600;
        font-size: 13px;
        color: #4a5568;
        margin-bottom: 6px;
    }
    
    .form-control {
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        font-size: 13px;
        padding: 8px 12px;
    }
    
    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102,126,234,.1);
    }
    
    textarea.form-control {
        resize: vertical;
    }

    /* ===== Select2 تنسيق ===== */
    .select2-container--bootstrap4 .select2-selection--single {
        height: calc(1.5em + 0.75rem + 2px);
        border-radius: 8px;
        border: 1px solid #e2e8f0;
    }
    
    .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
        line-height: calc(1.5em + 0.75rem);
        padding-right: 12px;
        color: #4a5568;
    }
    
    .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
        height: calc(1.5em + 0.75rem);
    }

    /* ===== Responsive ===== */
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    .table-responsive::-webkit-scrollbar {
        height: 8px;
    }
    
    .table-responsive::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    
    .table-responsive::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 10px;
    }
    
    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
    
    @media (max-width: 768px) {
        .action-buttons .btn span {
            display: none;
        }
        
        .action-buttons .btn {
            padding: 5px 8px;
        }
        
        .card-tools {
            margin-top: 10px;
        }
    }
</style>
@endpush

@section('content')
    <div class="container-fluid">
        {{-- كروت إحصائيات سريعة --}}
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $stats['total'] ?? 0 }}</h3>
                        <p>إجمالي المدارس</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-school"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $stats['elementary'] ?? 0 }}</h3>
                        <p>مدارس ابتدائي</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-book"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $stats['middle'] ?? 0 }}</h3>
                        <p>مدارس اعدادي</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $stats['kindergarten'] ?? 0 }}</h3>
                        <p>حضانات</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-child"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- جدول المدارس --}}
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-school ml-2"></i>
                    قائمة المدارس المسجلة
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#addSchoolModal">
                        <i class="fas fa-plus"></i>
                        <span>إضافة مدرسة جديدة</span>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="schools-table" class="table table-bordered table-hover w-100">
                        <thead>
                            <tr>
                                <th style="width: 5%">#</th>
                                <th style="width: 25%">اسم المدرسة</th>
                                <th style="width: 12%">النوع</th>
                                <th style="width: 18%">القسم/المنطقة</th>
                                <th style="width: 25%">العنوان</th>
                                <th style="width: 15%">العمليات</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- البيانات تُحمل عبر Ajax --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- مودال إضافة مدرسة --}}
    <div class="modal fade" id="addSchoolModal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-plus-circle ml-2"></i> إضافة مدرسة جديدة
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="addSchoolForm">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>اسم المدرسة <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="أدخل اسم المدرسة" required>
                            <div class="invalid-feedback name-error"></div>
                        </div>

                        <div class="form-group">
                            <label>نوع المدرسة <span class="text-danger">*</span></label>
                            <select name="type" class="form-control" required>
                                <option value="">اختر النوع</option>
                                <option value="ابتدائي">ابتدائي</option>
                                <option value="اعدادي">اعدادي</option>
                                <option value="حضانة">حضانة</option>
                                <option value="تعليم مجتمعي">تعليم مجتمعي</option>
                            </select>
                            <div class="invalid-feedback type-error"></div>
                        </div>

                        <div class="form-group">
                            <label>القسم/المنطقة <span class="text-danger">*</span></label>
                            <select name="department_id" class="form-control" required>
                                <option value="">اختر القسم</option>
                                @foreach ($departments ?? [] as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback department_id-error"></div>
                        </div>

                        <div class="form-group">
                            <label>العنوان</label>
                            <textarea name="address" class="form-control" rows="3" placeholder="أدخل عنوان المدرسة"></textarea>
                            <div class="invalid-feedback address-error"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> إلغاء
                        </button>
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-save"></i> حفظ
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- مودال تعديل مدرسة --}}
    <div class="modal fade" id="editSchoolModal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">
                        <i class="fas fa-edit ml-2"></i> تعديل المدرسة
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="editSchoolForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id" id="edit_id">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>اسم المدرسة <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                            <div class="invalid-feedback name-error"></div>
                        </div>

                        <div class="form-group">
                            <label>نوع المدرسة <span class="text-danger">*</span></label>
                            <select name="type" id="edit_type" class="form-control" required>
                                <option value="">اختر النوع</option>
                                <option value="ابتدائي">ابتدائي</option>
                                <option value="اعدادي">اعدادي</option>
                                <option value="حضانة">حضانة</option>
                                <option value="تعليم مجتمعي">تعليم مجتمعي</option>
                            </select>
                            <div class="invalid-feedback type-error"></div>
                        </div>

                        <div class="form-group">
                            <label>القسم/المنطقة <span class="text-danger">*</span></label>
                            <select name="department_id" id="edit_department_id" class="form-control" required>
                                <option value="">اختر القسم</option>
                                @foreach ($departments ?? [] as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback department_id-error"></div>
                        </div>

                        <div class="form-group">
                            <label>العنوان</label>
                            <textarea name="address" id="edit_address" class="form-control" rows="3"></textarea>
                            <div class="invalid-feedback address-error"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> إلغاء
                        </button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save"></i> تحديث
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('custom-js')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // إعداد CSRF لجميع طلبات AJAX
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    // تهيئة DataTable
    var table = $('#schools-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: false,
        scrollX: true,
        ajax: {
            url: "{{ route('admin.schools.index') }}",
            type: "GET",
        },
        columns: [
            { data: 'DT_RowIndex', name: 'id', orderable: false, searchable: false, width: '5%' },
            { data: 'name', name: 'name', width: '25%' },
            { data: 'type', name: 'type', width: '12%' },
            { data: 'department_name', name: 'department.name', width: '18%' },
            { data: 'address', name: 'address', width: '25%' },
            { data: 'action', name: 'action', orderable: false, searchable: false, width: '15%' }
        ],
        language: { 
            url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/ar.json"
        },
        order: [[1, 'asc']],
        dom: '<"row"<"col-sm-12 col-md-6"B><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        buttons: [
            { extend: 'excelHtml5', text: '<i class="fas fa-file-excel"></i> Excel', className: 'btn btn-success btn-sm btn-excel' },
            { extend: 'pdfHtml5', text: '<i class="fas fa-file-pdf"></i> PDF', className: 'btn btn-danger btn-sm btn-pdf' },
            { extend: 'print', text: '<i class="fas fa-print"></i> طباعة', className: 'btn btn-info btn-sm btn-print' }
        ],
        drawCallback: function() {
            setTimeout(function() { table.columns.adjust(); }, 100);
        }
    });

    // ==================== إضافة مدرسة ====================
    $('#addSchoolForm').on('submit', function(e) {
        e.preventDefault();
        var submitBtn = $(this).find('button[type="submit"]');
        var originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> جاري الحفظ...');

        $.ajax({
            url: "{{ route('admin.schools.store') }}",
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'تم بنجاح',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    $('#addSchoolModal').modal('hide');
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open');
                    $('#addSchoolForm')[0].reset();
                    table.ajax.reload();
                } else {
                    Swal.fire('خطأ', response.message, 'error');
                }
            },
            error: function(xhr) {
                let errorMsg = 'حدث خطأ أثناء إضافة المدرسة';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                Swal.fire('خطأ', errorMsg, 'error');
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    // ==================== فتح مودال التعديل ====================
    $(document).on('click', '.edit-school', function() {
        var id = $(this).data('id');
        
        Swal.fire({
            title: 'جاري التحميل...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        $.ajax({
            url: '/admin/schools/' + id + '/edit',
            method: 'GET',
            success: function(response) {
                Swal.close();
                if (response.success) {
                    $('#edit_id').val(response.id);
                    $('#edit_name').val(response.name);
                    $('#edit_type').val(response.type);
                    $('#edit_department_id').val(response.department_id);
                    $('#edit_address').val(response.address || '');
                    $('#editSchoolModal').modal('show');
                } else {
                    Swal.fire('خطأ', response.message, 'error');
                }
            },
            error: function() {
                Swal.close();
                Swal.fire('خطأ', 'فشل تحميل بيانات المدرسة', 'error');
            }
        });
    });

    // ==================== تحديث مدرسة ====================
    $('#editSchoolForm').on('submit', function(e) {
        e.preventDefault();
        var id = $('#edit_id').val();
        var submitBtn = $(this).find('button[type="submit"]');
        var originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> جاري التحديث...');

        $.ajax({
            url: '/admin/schools/' + id,
            method: 'PUT',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'تم التحديث',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    $('#editSchoolModal').modal('hide');
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open');
                    table.ajax.reload();
                } else {
                    Swal.fire('خطأ', response.message, 'error');
                }
            },
            error: function(xhr) {
                let errorMsg = 'حدث خطأ أثناء التحديث';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                Swal.fire('خطأ', errorMsg, 'error');
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    // ==================== حذف مدرسة ====================
    $(document).on('click', '.delete-school', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');

        Swal.fire({
            title: 'تأكيد الحذف',
            html: `هل تريد حذف مدرسة <strong>${name}</strong>؟`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'نعم، احذف',
            cancelButtonText: 'إلغاء',
            confirmButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'جاري الحذف...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: '/admin/schools/' + id,
                    method: 'DELETE',
                    data: { _token: $('meta[name="csrf-token"]').attr('content') },
                    success: function(response) {
                        Swal.close();
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'تم الحذف',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                            table.ajax.reload();
                        } else {
                            Swal.fire('خطأ', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.close();
                        Swal.fire('خطأ', 'حدث خطأ أثناء حذف المدرسة', 'error');
                    }
                });
            }
        });
    });
});
</script>
@endpush