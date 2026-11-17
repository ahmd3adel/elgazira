{{-- resources/views/backend/subcategories/index.blade.php --}}
@extends('backend.app')
@section('title', 'إدارة التخصصات الفرعية')
@section('breadcrumb-title', 'إدارة التخصصات')
@section('breadcrumb')
    <li class="breadcrumb-item active">التخصصات الفرعية</li>
@endsection

@push('custom-css')
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap4.min.css">
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.2/dist/select2-bootstrap4.min.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- خط كايرو -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        * {
            font-family: 'Cairo', sans-serif;
        }

        body {
            background-color: #f8f9fc;
        }

        /* تحسين شكل الجدول */
        .table {
            width: 100% !important;
            margin-bottom: 0 !important;
        }
        
        .table thead th {
            background-color: #f8f9fc;
            border-bottom: 2px solid #dee2e6;
            color: #4e73df;
            font-weight: 600;
            font-size: 14px;
            white-space: nowrap;
        }
        
        .table tbody td {
            vertical-align: middle;
            padding: 12px 8px;
            font-size: 14px;
        }

        /* تحسين DataTables */
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 15px;
            padding: 0 15px;
        }
        
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            margin-top: 15px;
            padding: 0 15px;
        }
        
        .dataTables_filter input {
            border-radius: 20px;
            padding: 6px 12px;
            border: 1px solid #d1d3e2;
        }
        
        .dataTables_length select {
            border-radius: 20px;
            padding: 5px 10px;
        }
        
        /* أيقونات المهن */
        .icon-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s;
            margin: 0 auto;
        }
        
        .icon-circle:hover {
            transform: scale(1.1);
        }
        
        /* محدد الأيقونات */
        .icon-selector .icon-item,
        .icon-selector-edit .icon-item {
            cursor: pointer;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            transition: all 0.2s ease;
            text-align: center;
        }
        
        .icon-selector .icon-item:hover,
        .icon-selector-edit .icon-item:hover {
            border-color: #007bff;
            background-color: #e7f1ff;
            transform: translateY(-2px);
        }
        
        .icon-selector .icon-item.active,
        .icon-selector-edit .icon-item.active {
            border-color: #007bff;
            background-color: #007bff20;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);
        }
        
        /* تحسين RTL */
        .modal-header .close {
            margin: -1rem auto -1rem -1rem !important;
            padding: 1rem;
        }
        
        .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
            text-align: right;
        }
        
        /* أزرار الإجراءات */
        .btn-action {
            margin: 0 3px;
            padding: 4px 8px;
            border-radius: 6px;
        }
        
        /* تحسين البطاقات */
        .card {
            border-radius: 10px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        
        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
            padding: 1rem 1.35rem;
        }
        
        /* التجاوب */
        @media (max-width: 768px) {
            .table-responsive {
                overflow-x: auto;
            }
            
            .btn-group {
                display: flex;
                gap: 5px;
            }
        }
        
        /* ألوان الشارات */
        .badge {
            font-size: 12px;
            padding: 5px 10px;
            border-radius: 20px;
        }
        
        /* مسار التنقل (Breadcrumb) */
        .breadcrumb-item.active {
            color: #6c757d;
        }
        
        /* تحسين عرض المهنة الأم */
        .parent-category-badge {
            background-color: #eef2ff;
            color: #4e73df;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .parent-category-badge i {
            margin-left: 5px;
        }
        
        /* مؤشر السكرول الأفقي */
        .scroll-hint {
            text-align: center;
            padding: 8px;
            background: linear-gradient(90deg, #f8f9fc, #e9ecef);
            color: #4e73df;
            font-size: 12px;
            border-radius: 0 0 10px 10px;
            margin-top: -1px;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-code-branch ml-2"></i>
                    قائمة التخصصات الفرعية
                </h3>
                <div class="card-tools">
                    <div class="btn-group ml-2">
                        <button type="button" class="btn btn-info btn-sm dropdown-toggle" data-toggle="dropdown">
                            <i class="fas fa-download"></i> تصدير
                        </button>
                        <div class="dropdown-menu dropdown-menu-left">
                            <a class="dropdown-item" href="#" id="exportExcel">
                                <i class="fas fa-file-excel text-success"></i> Excel (CSV)
                            </a>
                            <a class="dropdown-item" href="#" id="exportPrint">
                                <i class="fas fa-print text-info"></i> طباعة
                            </a>
                            <a class="dropdown-item" href="#" id="exportPDF">
                                <i class="fas fa-file-pdf text-danger"></i> PDF
                            </a>
                        </div>
                    </div>
                    <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#addSubCategoryModal">
                        <i class="fas fa-plus"></i> إضافة تخصص فرعي جديد
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive-wrapper">
                    <div class="table-responsive">
                        <table id="subcategoriesTable" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 5%">#</th>
                                    <th style="width: 8%">الأيقونة</th>
                                    <th style="width: 20%">اسم التخصص</th>
                                    <th style="width: 20%">المهنة الرئيسية</th>
                                    <th style="width: 10%">الحالة</th>
                                    <th style="width: 15%">تاريخ الإضافة</th>
                                    <th style="width: 15%">العمليات</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal إضافة تخصص فرعي -->
    <div class="modal fade" id="addSubCategoryModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-plus-circle ml-2"></i> إضافة تخصص فرعي جديد
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="addSubCategoryForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>اسم التخصص (بالعربي) <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name_ar" name="name_ar" required>
                                    <small class="text-muted">مثال: تطوير الويب</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>اسم التخصص (English)</label>
                                    <input type="text" class="form-control" id="name_en" name="name_en">
                                    <small class="text-muted">مثال: Web Development</small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>الرابط المخصص (عربي)</label>
                                    <input type="text" class="form-control" id="slug_ar" name="slug_ar">
                                    <small class="text-muted">سيظهر في الرابط</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>الرابط المخصص (English)</label>
                                    <input type="text" class="form-control" id="slug_en" name="slug_en">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label><i class="fas fa-briefcase"></i> المهنة الرئيسية <span class="text-danger">*</span></label>
                                    <select class="form-control select2-parent" id="parent_id" name="parent_id" required>
                                        <option value="">-- اختر المهنة الرئيسية --</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><i class="fas fa-toggle-on"></i> الحالة</label>
                                    <select class="form-control" name="is_active">
                                        <option value="1" selected>نشط</option>
                                        <option value="0">معطل</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><i class="fas fa-sort-numeric-down"></i> ترتيب العرض</label>
                                    <input type="number" class="form-control" name="order" value="0">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><i class="fas fa-palette"></i> لون الأيقونة</label>
                                    <input type="color" class="form-control" name="icon_color" value="#0066cc">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-icons"></i> اختر أيقونة التخصص</label>
                            <div class="row icon-selector border rounded p-3 bg-light" style="max-height: 180px; overflow-y: auto;">
                            </div>
                            <input type="hidden" id="selectedIcon" name="icon" value="fa-code-branch">
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-align-right"></i> الوصف</label>
                            <textarea class="form-control" name="description" rows="3" placeholder="وصف مختصر يظهر للمستخدمين..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> إلغاء
                    </button>
                    <button type="button" class="btn btn-success px-4" id="btnSaveSubCategory">
                        <i class="fas fa-save"></i> حفظ التخصص
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal تعديل تخصص فرعي -->
    <div class="modal fade" id="editSubCategoryModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-edit ml-2"></i> تعديل التخصص الفرعي
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editSubCategoryForm">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="edit_id" name="id">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>اسم التخصص (بالعربي) <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="edit_name_ar" name="name_ar" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>اسم التخصص (English)</label>
                                    <input type="text" class="form-control" id="edit_name_en" name="name_en">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>الرابط المخصص (عربي)</label>
                                    <input type="text" class="form-control" id="edit_slug_ar" name="slug_ar">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>الرابط المخصص (English)</label>
                                    <input type="text" class="form-control" id="edit_slug_en" name="slug_en">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label><i class="fas fa-briefcase"></i> المهنة الرئيسية <span class="text-danger">*</span></label>
                                    <select class="form-control select2-parent-edit" id="edit_parent_id" name="parent_id" required>
                                        <option value="">-- اختر المهنة الرئيسية --</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>الحالة</label>
                                    <select class="form-control" id="edit_is_active" name="is_active">
                                        <option value="1">نشط</option>
                                        <option value="0">معطل</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>ترتيب العرض</label>
                                    <input type="number" class="form-control" id="edit_order" name="order">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>لون الأيقونة</label>
                                    <input type="color" class="form-control" id="edit_icon_color" name="icon_color" value="#0066cc">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>اختر أيقونة التخصص</label>
                            <div class="row icon-selector-edit border rounded p-3 bg-light" style="max-height: 180px; overflow-y: auto;">
                            </div>
                            <input type="hidden" id="edit_selectedIcon" name="icon">
                        </div>
                        <div class="form-group">
                            <label>الوصف</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> إلغاء
                    </button>
                    <button type="button" class="btn btn-primary px-4" id="btnUpdateSubCategory">
                        <i class="fas fa-save"></i> تحديث التخصص
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('custom-js')
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>

    <script>
        $(document).ready(function() {
            // قائمة الأيقونات المتاحة
            const iconsList = [
                'fa-code-branch', 'fa-laptop-code', 'fa-mobile-alt', 'fa-database', 'fa-cloud',
                'fa-shield-alt', 'fa-chart-line', 'fa-paint-brush', 'fa-search', 'fa-cogs',
                'fa-robot', 'fa-brain', 'fa-microchip', 'fa-network-wired', 'fa-server',
                'fa-lock', 'fa-key', 'fa-chart-pie', 'fa-chart-bar', 'fa-chart-simple',
                'fa-file-code', 'fa-globe', 'fa-envelope', 'fa-users', 'fa-user-tie',
                'fa-bullhorn', 'fa-camera', 'fa-video', 'fa-headset', 'fa-plug'
            ];

            // دالة تنظيف النص للـ slug
            function cleanSlug(str) {
                if (!str) return '';
                return str.toString().trim().toLowerCase()
                    .replace(/[^\u0600-\u06FF\u0750-\u077Fa-zA-Z0-9\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-')
                    .replace(/^-|-$/g, '');
            }

            // دالة تحميل المهن الرئيسية للـ Select
            function loadParentCategories() {
                $.ajax({
                    url: "{{ route('admin.categories.parents') }}",
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        let options = '<option value="">-- اختر المهنة الرئيسية --</option>';
                        if (response.success && response.data && response.data.length > 0) {
                            $.each(response.data, function(index, cat) {
                                options += `<option value="${cat.id}">${cat.text}</option>`;
                            });
                        }
                        $('.select2-parent').html(options).trigger('change');
                        $('.select2-parent-edit').html(options).trigger('change');
                    },
                    error: function(xhr) {
                        console.error('خطأ في تحميل المهن الرئيسية:', xhr);
                        // محاولة بديلة
                        $.ajax({
                            url: "{{ url('admin/categories/parents') }}",
                            method: 'GET',
                            success: function(res) {
                                let options = '<option value="">-- اختر المهنة الرئيسية --</option>';
                                if (res.data && res.data.length) {
                                    $.each(res.data, function(index, cat) {
                                        options += `<option value="${cat.id}">${cat.text || cat.name}</option>`;
                                    });
                                }
                                $('.select2-parent').html(options).trigger('change');
                                $('.select2-parent-edit').html(options).trigger('change');
                            }
                        });
                    }
                });
            }

            // دالة عرض الأيقونات
            function renderIcons(container, selectedIcon = 'fa-code-branch') {
                let html = '';
                iconsList.forEach(icon => {
                    html += `
                        <div class="col-2 col-md-1 mb-2">
                            <div class="icon-item ${icon === selectedIcon ? 'active' : ''}" data-icon="${icon}">
                                <i class="fas ${icon} fa-2x"></i>
                            </div>
                        </div>
                    `;
                });
                $(container).html(html);
            }

            // تهيئة DataTable
            let table = $('#subcategoriesTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: false,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/ar.json"
                },
                ajax: {
                    url: "{{ route('admin.subcategories.index') }}",
                    type: "GET",
                    error: function(xhr, error, code) {
                        console.error('DataTable Error:', error);
                        Swal.fire('خطأ!', 'حدث خطأ في تحميل البيانات', 'error');
                    }
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'icon_html', name: 'icon_html', orderable: false, searchable: false },
                    { data: 'name_display', name: 'name_ar' },
                    { data: 'parent_name', name: 'parent_name', orderable: false },
                    { data: 'status_html', name: 'status_html', orderable: false },
                    { data: 'created_at_formatted', name: 'created_at' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                order: [[0, 'desc']],
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "الكل"]]
            });

            // تهيئة Select2
            $('.select2-parent, .select2-parent-edit').select2({
                theme: 'bootstrap4',
                placeholder: 'اختر المهنة الرئيسية',
                allowClear: true,
                width: '100%',
                dir: "rtl"
            });

            // عرض الأيقونات
            renderIcons('.icon-selector', 'fa-code-branch');
            renderIcons('.icon-selector-edit', 'fa-code-branch');

            // اختيار الأيقونة
            $(document).on('click', '.icon-selector .icon-item', function() {
                $('.icon-selector .icon-item').removeClass('active');
                $(this).addClass('active');
                $('#selectedIcon').val($(this).data('icon'));
            });

            $(document).on('click', '.icon-selector-edit .icon-item', function() {
                $('.icon-selector-edit .icon-item').removeClass('active');
                $(this).addClass('active');
                $('#edit_selectedIcon').val($(this).data('icon'));
            });

            // إنشاء slug تلقائي
            $('#name_ar, #edit_name_ar').on('keyup', function() {
                let targetId = $(this).attr('id') === 'name_ar' ? '#slug_ar' : '#edit_slug_ar';
                if ($(targetId).val() === '') {
                    $(targetId).val(cleanSlug($(this).val()));
                }
            });

            $('#name_en, #edit_name_en').on('keyup', function() {
                let targetId = $(this).attr('id') === 'name_en' ? '#slug_en' : '#edit_slug_en';
                if ($(targetId).val() === '') {
                    $(targetId).val(cleanSlug($(this).val()));
                }
            });

            // حفظ تخصص فرعي جديد
            $('#btnSaveSubCategory').on('click', function() {
                let nameAr = $('#name_ar').val().trim();
                if (!nameAr) {
                    Swal.fire('تنبيه', 'يرجى إدخال اسم التخصص بالعربية', 'warning');
                    return;
                }
                
                let parentId = $('#parent_id').val();
                if (!parentId) {
                    Swal.fire('تنبيه', 'يرجى اختيار المهنة الرئيسية', 'warning');
                    return;
                }

                let btn = $(this);
                let originalHtml = btn.html();
                btn.html('<i class="fas fa-spinner fa-spin"></i> جاري الحفظ...').prop('disabled', true);

                let formData = new FormData($('#addSubCategoryForm')[0]);

                $.ajax({
                    url: "{{ route('admin.subcategories.store') }}",
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
                            $('#addSubCategoryModal').modal('hide');
                            $('#addSubCategoryForm')[0].reset();
                            $('#selectedIcon').val('fa-code-branch');
                            $('.icon-selector .icon-item').removeClass('active').first().addClass('active');
                            table.ajax.reload();
                            loadParentCategories();
                        } else {
                            Swal.fire('خطأ!', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = xhr.responseJSON?.message || 'حدث خطأ أثناء حفظ البيانات';
                        if (xhr.responseJSON?.errors) {
                            let errors = Object.values(xhr.responseJSON.errors).flat();
                            errorMsg = errors.join('\n');
                        }
                        Swal.fire('خطأ!', errorMsg, 'error');
                    },
                    complete: function() {
                        btn.html(originalHtml).prop('disabled', false);
                    }
                });
            });

            // عرض مودال التعديل
            $(document).on('click', '.edit-subcategory', function(e) {
                e.preventDefault();
                let id = $(this).data('id');

                Swal.fire({
                    title: 'جاري التحميل...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                $.ajax({
                    url: "{{ url('admin/subcategories') }}/" + id,
                    method: 'GET',
                    success: function(data) {
                        Swal.close();

                        $('#edit_id').val(data.id);
                        $('#edit_name_ar').val(data.name_ar);
                        $('#edit_name_en').val(data.name_en || '');
                        $('#edit_slug_ar').val(data.slug_ar || '');
                        $('#edit_slug_en').val(data.slug_en || '');
                        $('#edit_parent_id').val(data.parent_id || '').trigger('change');
                        $('#edit_is_active').val(data.is_active ? 1 : 0);
                        $('#edit_order').val(data.order || 0);
                        $('#edit_icon_color').val(data.icon_color || '#0066cc');
                        $('#edit_description').val(data.description || '');
                        $('#edit_selectedIcon').val(data.icon || 'fa-code-branch');

                        $('.icon-selector-edit .icon-item').removeClass('active');
                        $(`.icon-selector-edit .icon-item[data-icon="${data.icon || 'fa-code-branch'}"]`).addClass('active');

                        $('#editSubCategoryModal').modal('show');
                    },
                    error: function(xhr) {
                        Swal.close();
                        Swal.fire('خطأ!', 'حدث خطأ أثناء تحميل البيانات', 'error');
                    }
                });
            });

            // تحديث التخصص الفرعي
            $('#btnUpdateSubCategory').on('click', function() {
                let id = $('#edit_id').val();
                let nameAr = $('#edit_name_ar').val().trim();

                if (!nameAr) {
                    Swal.fire('تنبيه', 'يرجى إدخال اسم التخصص بالعربية', 'warning');
                    return;
                }
                
                let parentId = $('#edit_parent_id').val();
                if (!parentId) {
                    Swal.fire('تنبيه', 'يرجى اختيار المهنة الرئيسية', 'warning');
                    return;
                }

                let btn = $(this);
                let originalHtml = btn.html();
                btn.html('<i class="fas fa-spinner fa-spin"></i> جاري التحديث...').prop('disabled', true);

                let formData = new FormData($('#editSubCategoryForm')[0]);

                $.ajax({
                    url: "{{ url('admin/subcategories') }}/" + id,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'X-HTTP-Method-Override': 'PUT'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('تم!', response.message, 'success');
                            $('#editSubCategoryModal').modal('hide');
                            table.ajax.reload();
                            loadParentCategories();
                        } else {
                            Swal.fire('خطأ!', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = xhr.responseJSON?.message || 'حدث خطأ أثناء تحديث البيانات';
                        Swal.fire('خطأ!', errorMsg, 'error');
                    },
                    complete: function() {
                        btn.html(originalHtml).prop('disabled', false);
                    }
                });
            });

            // حذف التخصص الفرعي
            $(document).on('click', '.delete-subcategory', function() {
                let id = $(this).data('id');
                let name = $(this).data('name');

                Swal.fire({
                    title: 'هل أنت متأكد؟',
                    html: `هل تريد حذف التخصص <strong>${name}</strong>؟`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'نعم، احذفه',
                    cancelButtonText: 'إلغاء'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'جاري الحذف...',
                            allowOutsideClick: false,
                            didOpen: () => Swal.showLoading()
                        });

                        $.ajax({
                            url: "{{ url('admin/subcategories') }}/" + id,
                            method: 'DELETE',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire('تم الحذف!', response.message, 'success');
                                    table.ajax.reload();
                                    loadParentCategories();
                                } else {
                                    Swal.fire('خطأ!', response.message, 'error');
                                }
                            },
                            error: function(xhr) {
                                Swal.close();
                                Swal.fire('خطأ!', xhr.responseJSON?.message || 'حدث خطأ أثناء الحذف', 'error');
                            }
                        });
                    }
                });
            });

            // تصدير Excel
            $('#exportExcel').on('click', function(e) {
                e.preventDefault();
                let data = table.rows({ search: 'applied' }).data().toArray();
                
                if (data.length === 0) {
                    Swal.fire('تنبيه', 'لا توجد بيانات للتصدير', 'warning');
                    return;
                }

                let csv = [['#', 'اسم التخصص', 'الاسم بالإنجليزية', 'المهنة الرئيسية', 'الحالة', 'تاريخ الإضافة']];
                
                data.forEach(row => {
                    csv.push([
                        row.id,
                        row.name_display || '',
                        row.name_en || '',
                        row.parent_name || '',
                        row.status_html?.includes('نشط') ? 'نشط' : 'معطل',
                        row.created_at_formatted || ''
                    ]);
                });

                let csvContent = "\uFEFF" + csv.map(row => row.map(cell => `"${String(cell).replace(/"/g, '""')}"`).join(',')).join('\n');
                let blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                let link = document.createElement('a');
                let url = URL.createObjectURL(blob);
                
                link.href = url;
                link.setAttribute('download', `subcategories_${new Date().toISOString().slice(0, 19).replace(/:/g, '-')}.csv`);
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                URL.revokeObjectURL(url);
                
                Swal.fire('تم التصدير', `تم تصدير ${data.length} سجل بنجاح`, 'success');
            });

            // طباعة
            $('#exportPrint').on('click', function(e) {
                e.preventDefault();
                let data = table.rows({ search: 'applied' }).data().toArray();
                
                if (data.length === 0) {
                    Swal.fire('تنبيه', 'لا توجد بيانات للطباعة', 'warning');
                    return;
                }

                let printWindow = window.open('', '_blank');
                let rows = '';
                
                data.forEach(row => {
                    rows += `
                        <tr>
                            <td style="border:1px solid #ddd;padding:8px;text-align:center">${row.id}</td>
                            <td style="border:1px solid #ddd;padding:8px;text-align:right">${row.name_display || ''}</td>
                            <td style="border:1px solid #ddd;padding:8px;text-align:center">${row.parent_name || ''}</td>
                            <td style="border:1px solid #ddd;padding:8px;text-align:center">${row.status_html?.includes('نشط') ? 'نشط' : 'معطل'}</td>
                            <td style="border:1px solid #ddd;padding:8px;text-align:center">${row.created_at_formatted || ''}</td>
                        </tr>
                    `;
                });

                printWindow.document.write(`
                    <html dir="rtl">
                    <head>
                        <title>تقرير التخصصات الفرعية</title>
                        <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&display=swap" rel="stylesheet">
                        <style>
                            * { font-family: 'Cairo', sans-serif; }
                            body { padding: 20px; }
                            h1 { text-align: center; color: #333; }
                            .date { text-align: center; color: #666; margin-bottom: 20px; }
                            table { width: 100%; border-collapse: collapse; }
                            th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
                            th { background-color: #f2f2f2; }
                            .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
                        </style>
                    </head>
                    <body>
                        <h1>📋 قائمة التخصصات الفرعية</h1>
                        <div class="date">تاريخ الطباعة: ${new Date().toLocaleDateString('ar-EG')}</div>
                        <table>
                            <thead>
                                <tr><th>#</th><th>اسم التخصص</th><th>المهنة الرئيسية</th><th>الحالة</th><th>تاريخ الإضافة</th></tr>
                            </thead>
                            <tbody>${rows}</tbody>
                        </table>
                        <div class="footer">تم التصدير من نظام إدارة المهن - ${new Date().toLocaleString('ar-EG')}</div>
                    </body>
                    </html>
                `);
                
                printWindow.document.close();
                printWindow.print();
            });

            // تصدير PDF
            $('#exportPDF').on('click', function(e) {
                e.preventDefault();
                let data = table.rows({ search: 'applied' }).data().toArray();
                
                if (data.length === 0) {
                    Swal.fire('تنبيه', 'لا توجد بيانات للتصدير', 'warning');
                    return;
                }

                Swal.fire({ title: 'جاري إنشاء PDF...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                const { jsPDF } = window.jspdf;
                let doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });
                
                let tableData = data.map(row => [
                    row.id,
                    row.name_display || '',
                    row.parent_name || '',
                    row.status_html?.includes('نشط') ? 'نشط' : 'معطل',
                    row.created_at_formatted || ''
                ]);

                doc.autoTable({
                    head: [['#', 'اسم التخصص', 'المهنة الرئيسية', 'الحالة', 'تاريخ الإضافة']],
                    body: tableData,
                    theme: 'striped',
                    headStyles: { fillColor: [0, 102, 204], textColor: 255, halign: 'center' },
                    bodyStyles: { halign: 'right' },
                    margin: { top: 30 }
                });

                doc.setFontSize(18);
                doc.setTextColor(0, 102, 204);
                doc.text('قائمة التخصصات الفرعية', doc.internal.pageSize.getWidth() / 2, 15, { align: 'center' });
                doc.setFontSize(10);
                doc.setTextColor(100);
                doc.text(`تاريخ التصدير: ${new Date().toLocaleDateString('ar-EG')}`, doc.internal.pageSize.getWidth() - 20, 22, { align: 'right' });
                doc.save(`subcategories_${new Date().toISOString().slice(0, 10)}.pdf`);
                
                Swal.fire('تم التصدير', `تم تصدير ${data.length} سجل إلى PDF`, 'success');
            });

            // تحسين التجاوب للجداول
            function adjustTableResponsive() {
                var $wrapper = $('.table-responsive-wrapper');
                var $table = $('#subcategoriesTable');
                
                if ($(window).width() <= 767) {
                    $wrapper.css({
                        'overflow-x': 'auto',
                        '-webkit-overflow-scrolling': 'touch'
                    });
                    $table.css('min-width', '650px');
                } else if ($(window).width() <= 991) {
                    $table.css('min-width', '750px');
                } else {
                    $table.css('min-width', '');
                }
            }

            $(window).on('resize', function() {
                adjustTableResponsive();
            }).trigger('resize');

            table.on('draw.dt', function() {
                adjustTableResponsive();
            });

            // إضافة تلميح السكرول للموبايل
            function showScrollHint() {
                if ($(window).width() <= 767) {
                    var $wrapper = $('.table-responsive-wrapper');
                    if ($wrapper.length && $wrapper[0].scrollWidth > $wrapper[0].clientWidth) {
                        $('.scroll-hint').remove();
                        var hint = $('<div class="scroll-hint"><i class="fas fa-arrows-alt-h"></i> اسحب لليمين لعرض جميع الأعمدة <i class="fas fa-arrow-right"></i></div>');
                        $wrapper.after(hint);
                        setTimeout(function() {
                            hint.fadeOut(1000, function() { $(this).remove(); });
                        }, 3000);
                    }
                }
            }

            setTimeout(showScrollHint, 1500);
            $(window).on('resize', showScrollHint);
            table.on('draw.dt', showScrollHint);

            if ('ontouchstart' in window) {
                $('.table-responsive-wrapper').css('-webkit-overflow-scrolling', 'touch');
            }

            // إعادة تعيين النماذج
            $('#addSubCategoryModal').on('hidden.bs.modal', function() {
                $('#addSubCategoryForm')[0].reset();
                $('#selectedIcon').val('fa-code-branch');
                $('.icon-selector .icon-item').removeClass('active').first().addClass('active');
                $('#parent_id').val('').trigger('change');
            });

            $('#editSubCategoryModal').on('hidden.bs.modal', function() {
                $('#editSubCategoryForm')[0].reset();
                $('#edit_parent_id').val('').trigger('change');
            });

            function loadParentCategories() {
    $.ajax({
        url: "{{ route('admin.categories.parents') }}",
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            let options = '<option value="">-- اختر المهنة الرئيسية --</option>';
            if (response.success && response.data && response.data.length > 0) {
                $.each(response.data, function(index, cat) {
                    // فلترة إضافية على مستوى JavaScript
                    if (cat.text && cat.text.match(/[\u0600-\u06FF]/)) {
                        options += `<option value="${cat.id}">${cat.text}</option>`;
                    }
                });
            }
            // إذا لم يتبقى خيارات بعد الفلترة، أضف رسالة
            if ($(options).find('option').length === 1) {
                options += '<option disabled>لا توجد مهن رئيسية متاحة</option>';
            }
            $('.select2-parent').html(options).trigger('change');
            $('.select2-parent-edit').html(options).trigger('change');
        },
        error: function(xhr) {
            console.error('خطأ في تحميل المهن الرئيسية:', xhr);
        }
    });
}

            // التحميل الأولي
            loadParentCategories();
            
            // تحسين حقل البحث
            $('.dataTables_filter input').addClass('form-control').attr('placeholder', '🔍 ابحث هنا...');
        });
    </script>
@endpush