@extends('backend.app')
@section('title', 'إدارة المخازن')
@section('breadcrumb-title', 'إدارة المخازن')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">المخازن</li>
@endsection

@push('custom-css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css" />
    <style>
        .card-title i {
            color: #17a2b8;
        }

        .btn-sm {
            border-radius: 4px;
            font-weight: 600;
        }

        .table-container {
            position: relative;
            width: 100%;
            overflow-x: auto;
            margin: 20px 0;
            border: 1px solid #dee2e6;
            border-radius: 4px;
        }

        #warehouses-table {
            width: 100% !important;
            min-width: 1000px;
        }

        #warehouses-table thead th,
        #warehouses-table tbody td {
            vertical-align: middle;
            text-align: center;
        }

        @media (max-width: 768px) {

            #warehouses-table thead th,
            #warehouses-table tbody td {
                font-size: 12px;
                padding: 8px 4px;
            }
        }

        .select2-container--bootstrap4 .select2-selection {
            min-height: calc(2.25rem + 2px);
        }

        .select2-dropdown {
            z-index: 2050 !important;
        }

        .select2-container--bootstrap4 .select2-dropdown {
            z-index: 2050 !important;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-map-marker-alt ml-2"></i>
                    قائمة المخازن المسجلة
                </h3>
                <div class="card-tools d-flex">
                    <div class="btn-group ml-2">
                        <button type="button" class="btn btn-warning btn-sm" id="restoreDeletedBtn">
                            <i class="fas fa-trash-restore"></i> استعادة المحذوفات
                        </button>
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
                    <button type="button" class="btn btn-info btn-sm mr-2" data-toggle="modal"
                        data-target="#addWarehouseModal">
                        <i class="fas fa-plus"></i> إضافة مخزن جديد
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-container">
                    <div class="table-responsive w-100">
                        <table id="warehouses-table" class="table table-bordered table-striped table-hover">
  <thead class="thead-light">
    <tr>
        <th style="width: 5%">#</th>
        <th style="width: 12%">كود المخزن</th>
        <th style="width: 18%">اسم المخزن</th>
        <th style="width: 25%">التبعية</th>
        <th style="width: 12%">المسؤول</th>
        <th style="width: 12%">الهاتف</th>
        <th style="width: 8%">الحالة</th>
        <th style="width: 8%">الإجراءات</th>
    </tr>
</thead>
                            </thead>
                            <tbody>
                                {{-- البيانات تُحمل عبر Ajax --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- مودال إضافة مخزن -->
    <div class="modal fade" id="addWarehouseModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-warehouse ml-2"></i> إضافة مخزن جديد
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="addWarehouseForm" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>نوع المخزن <span class="text-danger">*</span></label>
                                    <select name="type" id="warehouse_type" class="form-control select2" required>
                                        <option value="main">رئيسي</option>
                                        <option value="sub">فرعي</option>
                                        <option value="dispatch_point">نقطة توزيع</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>اسم المخزن <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="name" class="form-control"
                                        placeholder="مثال: مخزن المنزلة" required>
                                </div>
                            </div>

                            <div class="col-md-6" id="gov_group">
                                <div class="form-group">
                                    <label>المحافظة <span class="text-danger">*</span></label>
                                    <select name="governorate_id" id="governorate_id" class="form-control select2">
                                        <option value="">اختر المحافظة...</option>
                                        @foreach ($governorates as $gov)
                                            <option value="{{ $gov->id }}">{{ $gov->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6" id="parent_group" style="display: none;">
                                <div class="form-group">
                                    <label>المخزن الرئيسي <span class="text-danger">*</span></label>
                                    <select name="parent_id" id="parent_id" class="form-control select2">
                                        <option value="">اختر المخزن الرئيسي...</option>
                                        @foreach ($mainWarehouses as $main)
                                            <option value="{{ $main->id }}">{{ $main->name }}
({{ $main->governorate->name ?? '' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>كود المخزن <span class="text-danger">*</span></label>
                                    <input type="text" name="code" id="code" class="form-control"
                                        placeholder="مثال: 101" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>اسم المسؤول</label>
                                    <input type="text" name="manager_name" id="manager_name" class="form-control"
                                        placeholder="اسم الشخص المسؤول">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>رقم هاتف المسؤول</label>
                                    <input type="text" name="manager_phone" id="manager_phone" class="form-control"
                                        placeholder="010xxxxxxx">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>الحالة</label>
                                    <select class="form-control" name="status">
                                        <option value="1" selected>نشط</option>
                                        <option value="0">غير نشط</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>العنوان التفصيلي</label>
                                    <textarea name="address" class="form-control" rows="2" placeholder="العنوان بالكامل..."></textarea>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn btn-success px-4" id="btnSaveWarehouse">
                        <i class="fas fa-save ml-1"></i> حفظ البيانات
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- مودال تعديل مخزن -->
    <div class="modal fade" id="editWarehouseModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="fas fa-edit ml-2"></i> تعديل بيانات المخزن</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="editWarehouseForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_warehouse_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>نوع المخزن</label>
                                    <select name="type" id="edit_type" class="form-control select2" required>
                                        <option value="main">رئيسي</option>
                                        <option value="sub">فرعي</option>
                                        <option value="dispatch_point">نقطة توزيع</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>اسم المخزن</label>
                                    <input type="text" name="name" id="edit_name" class="form-control" required>
                                </div>
                            </div>

                            <div class="col-md-6" id="edit_gov_group">
                                <div class="form-group">
                                    <label>المحافظة</label>
                                    <select name="governorate_id" id="edit_governorate_id" class="form-control select2">
                                        <option value="">اختر المحافظة...</option>
                                        @foreach ($governorates as $gov)
                                            <option value="{{ $gov->id }}">{{ $gov->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6" id="edit_parent_group" style="display:none;">
                                <div class="form-group">
                                    <label>المخزن الرئيسي</label>
                                    <select name="parent_id" id="edit_parent_id" class="form-control select2">
                                        <option value="">اختر المخزن الرئيسي...</option>
                                        @foreach ($mainWarehouses as $main)
                                            <option value="{{ $main->id }}">{{ $main->name }}
                                                ({{ $main->governorate->name  ?? ''}})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>كود المخزن</label>
                                    <input type="text" name="code" id="edit_code" class="form-control" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>اسم المسؤول</label>
                                    <input type="text" name="manager_name" id="edit_manager_name"
                                        class="form-control">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>رقم الهاتف</label>
                                    <input type="text" name="manager_phone" id="edit_manager_phone"
                                        class="form-control">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>العنوان</label>
                                    <input type="text" name="address" id="edit_address" class="form-control">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>الحالة</label>
                                    <select name="status" id="edit_status" class="form-control">
                                        <option value="1">نشط</option>
                                        <option value="0">غير نشط</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-info shadow">تحديث البيانات</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- مودال استعادة المحذوفات -->
    <div class="modal fade" id="restoreDeletedModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">
                        <i class="fas fa-trash-restore ml-2"></i> استعادة المخازن المحذوفة
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table id="deleted-warehouses-table" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th width="5%"><input type="checkbox" id="selectAllDeleted"></th>
                                    <th>#</th>
                                    <th>كود المخزن</th>
                                    <th>اسم المخزن</th>
                                    <th>النوع</th>
                                    <th>تاريخ الحذف</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn btn-warning" id="btnRestoreSelected">
                        <i class="fas fa-trash-restore"></i> استعادة المحدد
                    </button>
                    <button type="button" class="btn btn-danger" id="btnForceDeleteSelected">
                        <i class="fas fa-trash-alt"></i> حذف نهائي للمحدد
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('custom-js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.29/jspdf.plugin.autotable.min.js"></script>

    <script>
        $(document).ready(function() {
            // تهيئة Select2
            $('.select2').select2({
                theme: 'bootstrap4'
            });

            // تهيئة DataTable
            var table = $('#warehouses-table').DataTable({
                processing: true,
                serverSide: true,
                ordering: true,
                scrollX: true,
                autoWidth: false,
                ajax: {
                    url: "{{ route('admin.warehouses.index') }}",
                    type: "GET",
                },
                drawCallback: function(settings) {
                    setTimeout(function() {
                        table.columns.adjust();
                    }, 10);
                },
                initComplete: function() {
                    $(window).on('resize', function() {
                        table.columns.adjust();
                    });
                },
columns: [
    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false }, // Column 0: #
    { data: 'code', name: 'code' }, // Column 1: كود المخزن
    { data: 'name', name: 'name' }, // Column 2: اسم المخزن
    { 
        data: null, // Column 3: التبعية (uses combined data)
        name: 'governorate_name',
        render: function(data, type, row) {
            if (row.type === 'main') {
                return `
                <div class="d-flex flex-column">
                    <span class="text-primary font-weight-bold">
                        <i class="fas fa-map-marker-alt"></i> ${row.governorate_name || 'غير محدد'}
                    </span>
                    <small class="text-muted">مخزن رئيسي</small>
                </div>`;
            } else {
                let typeLabel = row.type === 'sub' ? 'مخزن فرعي' : 'نقطة توزيع';
                let parent = row.parent_name && row.parent_name !== '---' ? row.parent_name : 'غير محدد';
                return `
                <div class="d-flex flex-column">
                    <span class="text-info font-weight-bold">
                        <i class="fas fa-warehouse"></i> ${parent}
                    </span>
                    <small class="text-muted">${typeLabel}</small>
                </div>`;
            }
        }
    },
    { data: 'manager_name', name: 'manager_name', defaultContent: '<i class="text-muted">غير محدد</i>' }, // Column 4: المسؤول
    { data: 'manager_phone', name: 'manager_phone', defaultContent: '-' }, // Column 5: الهاتف
    { data: 'status', name: 'status', className: 'text-center' }, // Column 6: الحالة
    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' } // Column 7: الإجراءات
],
                language: {
                    processing: "جاري التحميل...",
                    lengthMenu: "عرض _MENU_ سجلات",
                    zeroRecords: "لم يعثر على أية سجلات",
                    info: "إظهار _START_ إلى _END_ من أصل _TOTAL_ سجل",
                    infoEmpty: "يعرض 0 إلى 0 من أصل 0 سجل",
                    infoFiltered: "(منتقاة من مجموع _MAX_ سجل)",
                    search: "بحث:",
                    paginate: {
                        first: "الأول",
                        previous: "السابق",
                        next: "التالي",
                        last: "الأخير"
                    }
                },
                dom: 'Bfrtip',
                buttons: []
            });

            // التحكم في ظهور/اختفاء الحقول حسب نوع المخزن
            function toggleWarehouseFields(type, isEdit = false) {
                let govGroup = isEdit ? '#edit_gov_group' : '#gov_group';
                let parentGroup = isEdit ? '#edit_parent_group' : '#parent_group';
                let govSelect = isEdit ? '#edit_governorate_id' : '#governorate_id';
                let parentSelect = isEdit ? '#edit_parent_id' : '#parent_id';

                if (type === 'main') {
                    $(govGroup).fadeIn();
                    $(parentGroup).fadeOut();
                    $(govSelect).prop('required', true);
                    $(parentSelect).prop('required', false);
                    $(parentSelect).val('').trigger('change');
                } else {
                    $(govGroup).fadeOut();
                    $(parentGroup).fadeIn();
                    $(govSelect).prop('required', false);
                    $(parentSelect).prop('required', true);
                    $(govSelect).val('').trigger('change');
                }
            }

            $('#warehouse_type').on('change', function() {
                toggleWarehouseFields($(this).val(), false);
            });

            $('#edit_type').on('change', function() {
                toggleWarehouseFields($(this).val(), true);
            });

            $('#addWarehouseModal').on('shown.bs.modal', function() {
                $('#warehouse_type').trigger('change');
                $('.select2').select2({
                    theme: 'bootstrap4',
                    dropdownParent: $('#addWarehouseModal')
                });
            });

            $('#editWarehouseModal').on('shown.bs.modal', function() {
                $('.select2').select2({
                    theme: 'bootstrap4',
                    dropdownParent: $('#editWarehouseModal')
                });
            });

            // حفظ مخزن جديد
            $('#btnSaveWarehouse').click(function() {
                let formData = new FormData($('#addWarehouseForm')[0]);
                let btn = $(this);
                let originalHtml = btn.html();

                btn.html('<i class="fas fa-spinner fa-spin"></i> جاري الحفظ...').prop('disabled', true);

                $.ajax({
                    url: "{{ route('admin.warehouses.store') }}",
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
                            $('#addWarehouseModal').modal('hide');
                            $('#addWarehouseForm')[0].reset();
                            table.ajax.reload(null, false);
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = xhr.responseJSON?.message || 'حدث خطأ أثناء الحفظ';
                        if (xhr.status === 422 && xhr.responseJSON?.errors) {
                            errorMsg = Object.values(xhr.responseJSON.errors).flat().join(
                                '<br>');
                        }
                        Swal.fire('خطأ!', errorMsg, 'error');
                    },
                    complete: function() {
                        btn.html(originalHtml).prop('disabled', false);
                    }
                });
            });

$(document).on('click', '.edit-warehouse', function() {
    let id = $(this).data('id');
    
    Swal.fire({
        title: 'جاري التحميل...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    $.ajax({
        url: `/admin/warehouses/${id}/edit`,
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            Swal.close();
            
            if (response.success) {
                console.log('Data received:', response.data); // للتأكد من البيانات
                
                // تعبئة البيانات في المودال مع التحقق من وجود القيم
                $('#edit_warehouse_id').val(response.data.id || '');
                $('#edit_name').val(response.data.name || '');
                $('#edit_code').val(response.data.code || '');
                $('#edit_type').val(response.data.type || 'main').trigger('change');
                $('#edit_governorate_id').val(response.data.governorate_id || '').trigger('change');
                $('#edit_parent_id').val(response.data.parent_id || '').trigger('change');
                $('#edit_manager_name').val(response.data.manager_name || '');
                $('#edit_manager_phone').val(response.data.manager_phone || '');
                $('#edit_address').val(response.data.address || '');
                $('#edit_status').val(response.data.status ? '1' : '0');
                
                // إظهار المودال
                $('#editWarehouseModal').modal('show');
            } else {
                Swal.fire('خطأ!', response.message || 'حدث خطأ', 'error');
            }
        },
        error: function(xhr) {
            Swal.close();
            let errorMsg = xhr.responseJSON?.message || 'تعذر جلب بيانات المخزن';
            Swal.fire('خطأ!', errorMsg, 'error');
            console.log('Error details:', xhr);
        }
    });
});

            // تحديث مخزن
            $('#editWarehouseForm').submit(function(e) {
                e.preventDefault();
                let id = $('#edit_warehouse_id').val();
                let formData = new FormData(this);
                formData.append('_method', 'PUT');

                let btn = $(this).find('button[type="submit"]');
                let originalHtml = btn.html();

                btn.html('<i class="fas fa-spinner fa-spin"></i> جاري التحديث...').prop('disabled', true);

                $.ajax({
                    url: "{{ url('admin/warehouses') }}/" + id,
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
                            $('#editWarehouseModal').modal('hide');
                            table.ajax.reload(null, false);
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = xhr.responseJSON?.message || 'حدث خطأ أثناء التحديث';
                        if (xhr.status === 422 && xhr.responseJSON?.errors) {
                            errorMsg = Object.values(xhr.responseJSON.errors).flat().join(
                                '<br>');
                        }
                        Swal.fire('خطأ!', errorMsg, 'error');
                    },
                    complete: function() {
                        btn.html(originalHtml).prop('disabled', false);
                    }
                });
            });

            // حذف مخزن
            $(document).on('click', '.delete-warehouse', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                var name = $(this).data('name');
                var url = "{{ route('admin.warehouses.destroy', ':id') }}";
                url = url.replace(':id', id);

                Swal.fire({
                    title: 'هل أنت متأكد؟',
                    text: "سيتم نقل مخزن (" + name + ") إلى سلة المحذوفات!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3d3d3d',
                    confirmButtonText: 'نعم، احذف الآن!',
                    cancelButtonText: 'إلغاء'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            type: 'POST',
                            data: {
                                _method: 'DELETE',
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire('تم الحذف!', response.message, 'success');
                                    table.ajax.reload(null, false);
                                }
                            },
                            error: function(xhr) {
                                let msg = xhr.responseJSON?.message ||
                                    'حدث خطأ أثناء الحذف';
                                Swal.fire('خطأ!', msg, 'error');
                            }
                        });
                    }
                });
            });

            // إعادة تعيين النموذج عند إغلاق المودال
            $('#addWarehouseModal, #editWarehouseModal').on('hidden.bs.modal', function() {
                $(this).find('form')[0]?.reset();
                $(this).find('.select2').val('').trigger('change');
            });

            // ========== دوال استعادة المحذوفات ==========

            // فتح مودال المحذوفات
            $('#restoreDeletedBtn').click(function() {
                $('#restoreDeletedModal').modal('show');
                loadDeletedWarehouses();
            });

// تحميل المخازن المحذوفة
function loadDeletedWarehouses() {
    // دمر الجدول القديم إذا كان موجوداً
    if ($.fn.DataTable.isDataTable('#deleted-warehouses-table')) {
        $('#deleted-warehouses-table').DataTable().destroy();
        $('#deleted-warehouses-table tbody').empty();
    }
    
    // استخدم AJAX عادي
    $.ajax({
        url: "{{ route('admin.warehouses.trashed') }}",
        method: 'GET',
        data: { ajax: true },
        dataType: 'json',
        success: function(response) {
            let tbody = $('#deleted-warehouses-table tbody');
            tbody.empty();
            
            if (response.data && response.data.length > 0) {
                response.data.forEach(function(warehouse) {
                    let row = `
                        <tr>
                            <td class="text-center"><input type="checkbox" class="deleted-checkbox" value="${warehouse.id}"></td>
                            <td class="text-center">${warehouse.DT_RowIndex}</td>
                            <td class="text-center">${warehouse.code}</td>
                            <td class="text-center">${warehouse.name}</td>
                            <td class="text-center">${warehouse.type_label}</td>
                            <td class="text-center">${warehouse.deleted_date}</td>
                        </tr>
                    `;
                    tbody.append(row);
                });
                
                // تهيئة DataTable بسيطة
                $('#deleted-warehouses-table').DataTable({
                    language: {
                        processing: "جاري التحميل...",
                        lengthMenu: "عرض _MENU_ سجلات",
                        zeroRecords: "لا توجد مخازن محذوفة",
                        info: "إظهار _START_ إلى _END_ من أصل _TOTAL_ سجل",
                        search: "بحث:",
                        paginate: {
                            first: "الأول",
                            previous: "السابق",
                            next: "التالي",
                            last: "الأخير"
                        }
                    }
                });
            } else {
                tbody.html('<tr><td colspan="6" class="text-center">لا توجد مخازن محذوفة</td></tr>');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', xhr.responseText);
            Swal.fire('خطأ!', 'حدث خطأ في تحميل البيانات: ' + error, 'error');
        }
    });
    
    // تحديد كل العناصر
    $('#selectAllDeleted').off('click').on('click', function() {
        var isChecked = $(this).prop('checked');
        $('.deleted-checkbox').prop('checked', isChecked);
    });
    
    // تحديث حالة select all
    $(document).off('change', '.deleted-checkbox').on('change', '.deleted-checkbox', function() {
        var allChecked = $('.deleted-checkbox:checked').length === $('.deleted-checkbox').length;
        $('#selectAllDeleted').prop('checked', allChecked);
    });
}
            // استعادة المحدد
            $('#btnRestoreSelected').click(function() {
                let selectedIds = [];
                $('.deleted-checkbox:checked').each(function() {
                    selectedIds.push($(this).val());
                });

                if (selectedIds.length === 0) {
                    Swal.fire('تنبيه', 'الرجاء تحديد المخازن المراد استعادتها', 'warning');
                    return;
                }

                Swal.fire({
                    title: 'تأكيد الاستعادة',
                    text: `هل أنت متأكد من استعادة ${selectedIds.length} مخزن/مخازن؟`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#ffc107',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'نعم، استعيد الآن',
                    cancelButtonText: 'إلغاء'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('admin.warehouses.restore') }}",
                            method: 'POST',
                            data: {
                                ids: selectedIds,
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire('تم!', response.message, 'success');
                                    $('#restoreDeletedModal').modal('hide');
                                    table.ajax.reload();
                                    // إعادة تحميل جدول المحذوفات إذا كان مفتوحاً
                                    if ($('#restoreDeletedModal').hasClass('show')) {
                                        loadDeletedWarehouses();
                                    }
                                } else {
                                    Swal.fire('خطأ!', response.message, 'error');
                                }
                            },
                            error: function(xhr) {
                                let msg = xhr.responseJSON?.message ||
                                    'حدث خطأ أثناء الاستعادة';
                                Swal.fire('خطأ!', msg, 'error');
                            }
                        });
                    }
                });
            });

            // حذف نهائي للمحدد
            $('#btnForceDeleteSelected').click(function() {
                let selectedIds = [];
                $('.deleted-checkbox:checked').each(function() {
                    selectedIds.push($(this).val());
                });

                if (selectedIds.length === 0) {
                    Swal.fire('تنبيه', 'الرجاء تحديد المخازن المراد حذفها نهائياً', 'warning');
                    return;
                }

                Swal.fire({
                    title: 'تحذير!',
                    text: `سيتم حذف ${selectedIds.length} مخزن/مخازن بشكل نهائي ولا يمكن استعادتها!`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'نعم، احذف نهائياً',
                    cancelButtonText: 'إلغاء'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('admin.warehouses.force-delete') }}",
                            method: 'DELETE',
                            data: {
                                ids: selectedIds,
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire('تم الحذف!', response.message, 'success');
                                    $('#restoreDeletedModal').modal('hide');
                                    table.ajax.reload();
                                } else {
                                    Swal.fire('خطأ!', response.message, 'error');
                                }
                            },
                            error: function(xhr) {
                                let msg = xhr.responseJSON?.message ||
                                    'حدث خطأ أثناء الحذف النهائي';
                                Swal.fire('خطأ!', msg, 'error');
                            }
                        });
                    }
                });
            });

            // وظائف التصدير
            $('#exportExcel').click(function(e) {
                e.preventDefault();
                let data = table.rows({
                    search: 'applied'
                }).data().toArray();
                if (!data.length) return Swal.fire('تنبيه', 'لا توجد بيانات للتصدير', 'warning');

                let csv = [
                    ['#', 'كود المخزن', 'اسم المخزن', 'التبعية', 'المسؤول', 'الهاتف', 'الحالة']
                ];
                data.forEach((row, index) => {
                    let dependency = '';
                    if (row.type === 'main') {
                        dependency = 'مخزن رئيسي - ' + (row.governorate_name || '');
                    } else {
                        let typeLabel = row.type === 'sub' ? 'مخزن فرعي' : 'نقطة توزيع';
                        let parent = row.parent_name && row.parent_name !== '---' ? row
                            .parent_name : 'غير محدد';
                        dependency = typeLabel + ' - ' + parent;
                    }

                    csv.push([
                        index + 1,
                        row.code || '',
                        row.name || '',
                        dependency,
                        row.manager_name || 'غير محدد',
                        row.manager_phone || '',
                        row.status == 1 ? 'نشط' : 'معطل'
                    ]);
                });

                let csvContent = "\uFEFF" + csv.map(row => row.map(cell =>
                    `"${String(cell).replace(/"/g, '""')}"`).join(',')).join('\n');
                let blob = new Blob([csvContent], {
                    type: 'text/csv;charset=utf-8;'
                });
                let link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = `warehouses_${new Date().toISOString().slice(0, 10)}.csv`;
                link.click();
                URL.revokeObjectURL(link.href);
                Swal.fire('تم التصدير', `تم تصدير ${data.length} مخزن بنجاح`, 'success');
            });

            $('#exportPrint').click(function(e) {
                e.preventDefault();
                let data = table.rows({
                    search: 'applied'
                }).data().toArray();
                if (!data.length) return Swal.fire('تنبيه', 'لا توجد بيانات للطباعة', 'warning');

                let rows = '';
                data.forEach((row, index) => {
                    let dependency = '';
                    if (row.type === 'main') {
                        dependency = 'رئيسي - ' + (row.governorate_name || '');
                    } else {
                        let typeLabel = row.type === 'sub' ? 'فرعي' : 'نقطة توزيع';
                        let parent = row.parent_name && row.parent_name !== '---' ? row
                            .parent_name : 'غير محدد';
                        dependency = typeLabel + ' - ' + parent;
                    }

                    rows += `
                    <tr>
                        <td style="border:1px solid #ddd;padding:8px;text-align:center">${index + 1}</td>
                        <td style="border:1px solid #ddd;padding:8px;text-align:center">${row.code}</td>
                        <td style="border:1px solid #ddd;padding:8px;text-align:right">${row.name}</td>
                        <td style="border:1px solid #ddd;padding:8px;text-align:center">${dependency}</td>
                        <td style="border:1px solid #ddd;padding:8px;text-align:center">${row.manager_name || '-'}</td>
                        <td style="border:1px solid #ddd;padding:8px;text-align:center">${row.manager_phone || '-'}</td>
                        <td style="border:1px solid #ddd;padding:8px;text-align:center">${row.status == 1 ? 'نشط' : 'معطل'}</td>
                    </tr>
                `;
                });

                let printWindow = window.open('', '_blank');
                printWindow.document.write(`
                <html dir="rtl">
                <head>
                    <title>تقرير المخازن</title>
                    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600&display=swap" rel="stylesheet">
                    <style>
                        *{font-family:'Cairo',sans-serif;} 
                        body{padding:20px;} 
                        table{width:100%;border-collapse:collapse;margin-top:20px;} 
                        th,td{border:1px solid #ddd;padding:10px;text-align:center;} 
                        th{background:#f2f2f2;font-weight:bold;}
                        h1{text-align:center;color:#333;}
                    </style>
                </head>
                <body>
                    <h1>📋 قائمة المخازن المسجلة</h1>
                    <div style="text-align:center">تاريخ التقرير: ${new Date().toLocaleDateString('ar-EG')}</div>
                    <table>
                        <thead>
                            <tr><th>#</th><th>الكود</th><th>المخزن</th><th>التبعية</th><th>المسؤول</th><th>الهاتف</th><th>الحالة</th></tr>
                        </thead>
                        <tbody>${rows}</tbody>
                    </table>
                </body>
                </html>
            `);
                printWindow.document.close();
                setTimeout(() => {
                    printWindow.print();
                }, 500);
            });

            $('#exportPDF').click(function(e) {
                e.preventDefault();
                let data = table.rows({
                    search: 'applied'
                }).data().toArray();
                if (!data.length) return Swal.fire('تنبيه', 'لا توجد بيانات للتصدير', 'warning');

                Swal.fire({
                    title: 'جاري إنشاء PDF...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                const {
                    jsPDF
                } = window.jspdf;
                let doc = new jsPDF({
                    orientation: 'landscape',
                    unit: 'mm',
                    format: 'a4'
                });

                let tableData = data.map((row, index) => {
                    let dependency = '';
                    if (row.type === 'main') {
                        dependency = 'رئيسي - ' + (row.governorate_name || '');
                    } else {
                        let typeLabel = row.type === 'sub' ? 'فرعي' : 'نقطة توزيع';
                        let parent = row.parent_name && row.parent_name !== '---' ? row
                            .parent_name : 'غير محدد';
                        dependency = typeLabel + ' - ' + parent;
                    }

                    return [
                        index + 1,
                        row.code,
                        row.name,
                        dependency,
                        row.manager_name || '-',
                        row.manager_phone || '-',
                        row.status == 1 ? 'نشط' : 'معطل'
                    ];
                });

                doc.autoTable({
                    head: [
                        ['#', 'الكود', 'اسم المخزن', 'التبعية', 'المسؤول', 'الهاتف', 'الحالة']
                    ],
                    body: tableData,
                    theme: 'striped',
                    headStyles: {
                        fillColor: [23, 162, 184],
                        textColor: 255,
                        halign: 'center'
                    },
                    styles: {
                        halign: 'center'
                    },
                    margin: {
                        top: 25
                    }
                });

                doc.setFontSize(18);
                doc.text('قائمة المخازن', doc.internal.pageSize.getWidth() / 2, 15, {
                    align: 'center'
                });
                doc.save(`warehouses_${new Date().toISOString().slice(0, 10)}.pdf`);

                Swal.close();
                Swal.fire('تم التصدير', `تم إنشاء ملف PDF لعدد ${data.length} مخزن`, 'success');
            });
        });
    </script>
@endpush
