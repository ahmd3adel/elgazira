@extends('backend.app')
@section('title', 'إدارة الأجهزة')
@section('breadcrumb-title', 'إدارة الأجهزة')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">الأجهزة</li>
@endsection

@push('custom-css')
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    
    <style>
        .badge-working { background: #28a745; color: white; padding: 5px 10px; border-radius: 20px; }
        .badge-maintenance { background: #ffc107; color: #856404; padding: 5px 10px; border-radius: 20px; }
        .badge-broken { background: #dc3545; color: white; padding: 5px 10px; border-radius: 20px; }
        .badge-retired { background: #6c757d; color: white; padding: 5px 10px; border-radius: 20px; }
        .badge-active { background: #28a745; color: white; padding: 5px 10px; border-radius: 20px; }
        .badge-inactive { background: #dc3545; color: white; padding: 5px 10px; border-radius: 20px; }
        
        .small-box { border-radius: 8px; transition: 0.3s; }
        .small-box:hover { transform: translateY(-5px); }
        .action-buttons .btn { margin: 0 2px; }
        .password-cell { font-family: monospace; direction: ltr; }
        
        /* إصلاح مشكلة الطبقة السوداء */
        .modal-backdrop {
            z-index: 1040 !important;
        }
        .modal {
            z-index: 1050 !important;
        }
    </style>
@endpush

@section('content')
    {{-- كروت الإحصائيات --}}
    <div class="row">
        <div class="col-lg-3 col-12">
            <div class="small-box bg-info">
                <div class="inner"><h3>{{ $stats['total'] ?? 0 }}</h3><p>إجمالي الأجهزة</p></div>
                <div class="icon"><i class="fas fa-microchip"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-12">
            <div class="small-box bg-success">
                <div class="inner"><h3>{{ $stats['active'] ?? 0 }}</h3><p>أجهزة نشطة</p></div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-12">
            <div class="small-box bg-warning">
                <div class="inner"><h3>{{ $stats['working'] ?? 0 }}</h3><p>تعمل بكفاءة</p></div>
                <div class="icon"><i class="fas fa-charging-station"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-12">
            <div class="small-box bg-danger">
                <div class="inner"><h3>{{ $stats['maintenance'] ?? 0 }}</h3><p>تحت الصيانة</p></div>
                <div class="icon"><i class="fas fa-tools"></i></div>
            </div>
        </div>
    </div>

    {{-- فلتر --}}
    <div class="card card-primary">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-filter"></i> فلتر البحث</h3></div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <label>الإدارة</label>
                    <select id="filter_department" class="form-control">
                        <option value="">الكل</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label>رقم الخط</label>
                    <input type="text" id="filter_line" class="form-control" placeholder="بحث...">
                </div>
                <div class="col-md-4">
                    <label>الحالة الفنية</label>
                    <select id="filter_status" class="form-control">
                        <option value="">الكل</option>
                        <option value="working">يعمل</option>
                        <option value="maintenance">صيانة</option>
                        <option value="broken">عاطل</option>
                        <option value="retired">مستبعد</option>
                    </select>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-12">
                    <button class="btn btn-primary" id="btn_search"><i class="fas fa-search"></i> بحث</button>
                    <button class="btn btn-secondary" id="btn_reset"><i class="fas fa-undo"></i> إعادة تعيين</button>
                </div>
            </div>
        </div>
    </div>

    {{-- جدول الأجهزة --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-microchip"></i> قائمة الأجهزة</h3>
            <div class="card-tools">
                <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addModal">
                    <i class="fas fa-plus"></i> إضافة جهاز
                </button>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped" id="devicesTable" width="100%">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الإدارة</th>
                        <th>رقم الخط</th>
                        <th>POS Username</th>
                        <th>POS Password</th>
                        <th>السيريال</th>
                        <th>الحالة الفنية</th>
                        <th>العمليات</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    {{-- مودال الإضافة --}}
    <div class="modal fade" id="addModal" tabindex="-1" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title">إضافة جهاز جديد</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="addForm">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label>الإدارة *</label>
                                <select name="department_id" class="form-control" required>
                                    <option value="">اختر</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>رقم الخط *</label>
                                <input type="text" name="line_number" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label>POS Username *</label>
                                <input type="text" name="pos_username" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label>POS Password</label>
                                <input type="text" name="pos_password" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label>السيريال *</label>
                                <input type="text" name="serial_number" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label>الحالة الفنية *</label>
                                <select name="technical_status" class="form-control" required>
                                    <option value="working">يعمل بكفاءة</option>
                                    <option value="maintenance">تحت الصيانة</option>
                                    <option value="broken">عاطل</option>
                                    <option value="retired">مستبعد</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>الحالة العامة *</label>
                                <select name="status" class="form-control" required>
                                    <option value="active">نشط</option>
                                    <option value="inactive">غير نشط</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label>ملاحظات</label>
                                <textarea name="notes" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="col-md-12">
                                <label>المواصفات</label>
                                <textarea name="specifications" class="form-control" rows="2"></textarea>
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

    {{-- مودال التعديل --}}
    <div class="modal fade" id="editModal" tabindex="-1" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">تعديل الجهاز</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="editForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_id" name="id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label>الإدارة *</label>
                                <select name="department_id" id="edit_dept" class="form-control" required>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>رقم الخط *</label>
                                <input type="text" name="line_number" id="edit_line" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label>POS Username *</label>
                                <input type="text" name="pos_username" id="edit_username" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label>POS Password</label>
                                <input type="text" name="pos_password" id="edit_password" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label>السيريال *</label>
                                <input type="text" name="serial_number" id="edit_serial" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label>الحالة الفنية *</label>
                                <select name="technical_status" id="edit_tech" class="form-control" required>
                                    <option value="working">يعمل بكفاءة</option>
                                    <option value="maintenance">تحت الصيانة</option>
                                    <option value="broken">عاطل</option>
                                    <option value="retired">مستبعد</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>الحالة العامة *</label>
                                <select name="status" id="edit_status" class="form-control" required>
                                    <option value="active">نشط</option>
                                    <option value="inactive">غير نشط</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label>ملاحظات</label>
                                <textarea name="notes" id="edit_notes" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="col-md-12">
                                <label>المواصفات</label>
                                <textarea name="specifications" id="edit_specs" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">تحديث</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('custom-js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // إعداد CSRF
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    // تهيئة DataTable
    var table = $('#devicesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.devices.index') }}",
            data: function(d) {
                d.department_id = $('#filter_department').val();
                d.line_number = $('#filter_line').val();
                d.technical_status = $('#filter_status').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'id', orderable: false, searchable: false },
            { data: 'department_name', name: 'department.name' },
            { data: 'line_number', name: 'line_number' },
            { data: 'pos_username', name: 'pos_username' },
            { data: 'pos_password', name: 'pos_password' },
            { data: 'serial_number', name: 'serial_number' },
            { data: 'technical_status_badge', name: 'technical_status' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        language: { url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/ar.json" },
        pageLength: 10
    });

    // بحث
    $('#btn_search').click(() => table.ajax.reload());
    $('#btn_reset').click(() => {
        $('#filter_department, #filter_status').val('');
        $('#filter_line').val('');
        table.ajax.reload();
    });

    // إضافة جهاز
    $('#addForm').submit(function(e) {
        e.preventDefault();
        var submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> جاري الحفظ...');
        
        $.ajax({
            url: "{{ route('admin.devices.store') }}",
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                if(res.success) {
                    Swal.fire('تم', res.message, 'success');
                    // إغلاق المودال بشكل صحيح
                    $('#addModal').modal('hide');
                    // إزالة الطبقة السوداء يدوياً
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open');
                    // إعادة تعيين الفورم
                    $('#addForm')[0].reset();
                    // تحديث الجدول
                    table.ajax.reload();
                } else {
                    Swal.fire('خطأ', res.message, 'error');
                }
            },
            error: function(xhr) {
                Swal.fire('خطأ', xhr.responseJSON?.message || 'حدث خطأ', 'error');
            },
            complete: function() {
                submitBtn.prop('disabled', false).html('حفظ');
            }
        });
    });

    // فتح مودال التعديل
    $(document).on('click', '.edit-device', function() {
        var id = $(this).data('id');
        
        $.ajax({
            url: '/admin/devices/' + id + '/edit',
            method: 'GET',
            success: function(res) {
                if(res.success) {
                    $('#edit_id').val(res.id);
                    $('#edit_dept').val(res.department_id);
                    $('#edit_line').val(res.line_number);
                    $('#edit_username').val(res.pos_username);
                    $('#edit_password').val(res.pos_password);
                    $('#edit_serial').val(res.serial_number);
                    $('#edit_tech').val(res.technical_status);
                    $('#edit_status').val(res.status);
                    $('#edit_notes').val(res.notes || '');
                    $('#edit_specs').val(res.specifications || '');
                    $('#editModal').modal('show');
                } else {
                    Swal.fire('خطأ', res.message, 'error');
                }
            },
            error: function() {
                Swal.fire('خطأ', 'فشل تحميل بيانات الجهاز', 'error');
            }
        });
    });

    // تحديث جهاز
    $('#editForm').submit(function(e) {
        e.preventDefault();
        var id = $('#edit_id').val();
        var submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> جاري التحديث...');
        
        $.ajax({
            url: '/admin/devices/' + id,
            method: 'PUT',
            data: $(this).serialize(),
            success: function(res) {
                if(res.success) {
                    Swal.fire('تم', res.message, 'success');
                    $('#editModal').modal('hide');
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open');
                    table.ajax.reload();
                } else {
                    Swal.fire('خطأ', res.message, 'error');
                }
            },
            error: function(xhr) {
                Swal.fire('خطأ', xhr.responseJSON?.message || 'حدث خطأ', 'error');
            },
            complete: function() {
                submitBtn.prop('disabled', false).html('تحديث');
            }
        });
    });

    // حذف جهاز
    $(document).on('click', '.delete-device', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        
        Swal.fire({
            title: 'تأكيد الحذف',
            text: `هل تريد حذف الجهاز ${name}؟`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'نعم، احذف',
            cancelButtonText: 'إلغاء'
        }).then((result) => {
            if(result.isConfirmed) {
                Swal.fire({ title: 'جاري الحذف...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                
                $.ajax({
                    url: '/admin/devices/' + id,
                    method: 'DELETE',
                    data: { _token: $('meta[name="csrf-token"]').attr('content') },
                    success: function(res) {
                        Swal.close();
                        if(res.success) {
                            Swal.fire('تم الحذف', res.message, 'success');
                            table.ajax.reload();
                        } else {
                            Swal.fire('خطأ', res.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.close();
                        Swal.fire('خطأ', 'حدث خطأ أثناء الحذف', 'error');
                    }
                });
            }
        });
    });
});
</script>
@endpush