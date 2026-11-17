@extends('backend.app')
@section('title', ' الادارات')
@section('breadcrumb-title', 'إدارة الموقع')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">الادارات</li>
@endsection

@push('custom-css')
    <style>
        .card-title i {
            color: #17a2b8;
        }
        .btn-sm {
            border-radius: 4px;
            font-weight: 600;
        }
        .action-buttons .btn {
            margin: 0 2px;
        }
        /* تحسين ظهور الأزرار */
        .table td {
            vertical-align: middle;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        {{-- تنبيهات العمليات --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle ml-1"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle ml-1"></i> {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-map-marker-alt ml-2"></i>
                    قائمة الادارات المسجلة
                </h3>
                <div class="card-tools d-flex">
                    <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#addDepartmentModal">
                        <i class="fas fa-plus"></i> إضافة ادارة جديدة
                    </button>
                </div>
            </div>
            <div class="card-body">
                <table id="departments-table" class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>الكود</th>
                            <th>اسم الإدارة</th>
                            <th>المحافظة</th>
                            <th>المخزن الرئيسي</th>
                            <th>مخزن السحب</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- مودال إضافة إدارة جديدة --}}
    {{-- ============================================================ --}}
    <div class="modal fade" id="addDepartmentModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="fas fa-plus-circle ml-2"></i> إضافة إدارة جديدة</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form id="addDepartmentForm" action="{{ route('admin.departments.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>كود الإدارة <span class="text-danger">*</span></label>
                                    <input type="text" name="code" class="form-control" placeholder="مثال: IT-001" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>اسم الإدارة <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" placeholder="مثال: تقنية المعلومات" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>المحافظة التابعة لها</label>
                                    <select name="governorate_id" class="form-control">
                                        <option value="">-- اختر --</option>
                                        @foreach(\App\Models\Governorate::where('status', 1)->get() as $gov)
                                            <option value="{{ $gov->id }}">{{ $gov->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>المخزن الرئيسي</label>
                                    <select name="main_warehouse_id" class="form-control">
                                        <option value="">-- اختر --</option>
                                        @foreach(\App\Models\Warehouse::where('status', 1)->get() as $wh)
                                            <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>مخزن السحب (التشغيل)</label>
                                    <select name="operation_warehouse_id" class="form-control">
                                        <option value="">-- اختر --</option>
                                        @foreach(\App\Models\Warehouse::where('status', 1)->get() as $wh)
                                            <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>اسم المسؤول</label>
                                    <input type="text" name="manager_name" class="form-control" placeholder="اسم المسؤول">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>رقم هاتف المسؤول</label>
                                    <input type="text" name="manager_phone" class="form-control" placeholder="010xxxxxxx">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>الحالة</label>
                                    <select name="status" class="form-control">
                                        <option value="1">نشط</option>
                                        <option value="0">غير نشط</option>
                                    </select>
                                </div>
                            </div>
                            {{-- <div class="col-md-12">
                                <div class="form-group">
                                    <label>ملاحظات</label>
                                    <textarea name="notes" class="form-control" rows="2"></textarea>
                                </div>
                            </div> --}}
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-info px-4">
                            <i class="fas fa-save ml-1"></i> حفظ الإدارة
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- مودال تعديل إدارة --}}
    {{-- ============================================================ --}}
    <div class="modal fade" id="editDepartmentModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-edit ml-2"></i> تعديل بيانات الإدارة</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form id="editDepartmentForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <input type="hidden" id="edit_id" name="id">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>كود الإدارة <span class="text-danger">*</span></label>
                                    <input type="text" id="edit_code" name="code" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>اسم الإدارة <span class="text-danger">*</span></label>
                                    <input type="text" id="edit_name" name="name" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>المحافظة التابعة لها</label>
                                    <select id="edit_governorate_id" name="governorate_id" class="form-control">
                                        <option value="">-- اختر --</option>
                                        @foreach(\App\Models\Governorate::where('status', 1)->get() as $gov)
                                            <option value="{{ $gov->id }}">{{ $gov->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>المخزن الرئيسي</label>
                                    <select id="edit_main_warehouse_id" name="main_warehouse_id" class="form-control">
                                        <option value="">-- اختر --</option>
                                        @foreach(\App\Models\Warehouse::where('status', 1)->get() as $wh)
                                            <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>مخزن السحب (التشغيل)</label>
                                    <select id="edit_operation_warehouse_id" name="operation_warehouse_id" class="form-control">
                                        <option value="">-- اختر --</option>
                                        @foreach(\App\Models\Warehouse::where('status', 1)->get() as $wh)
                                            <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>اسم المسؤول</label>
                                    <input type="text" id="edit_manager_name" name="manager_name" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>رقم هاتف المسؤول</label>
                                    <input type="text" id="edit_manager_phone" name="manager_phone" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>الحالة</label>
                                    <select id="edit_status" name="status" class="form-control">
                                        <option value="1">نشط</option>
                                        <option value="0">غير نشط</option>
                                    </select>
                                </div>
                            </div>
                            {{-- <div class="col-md-12">
                                <div class="form-group">
                                    <label>ملاحظات</label>
                                    <textarea id="edit_notes" name="notes" class="form-control" rows="2"></textarea>
                                </div>
                            </div> --}}
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save ml-1"></i> حفظ التعديلات
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('custom-js')
<script>
    $(document).ready(function() {
        // ============================================================
        // 1. تعريف DataTable
        // ============================================================
        var table = $('#departments-table').DataTable({
            processing: true,
            serverSide: true,
            responsive: false,
            scrollX: true,
            autoWidth: false,
            ajax: {
                url: "{{ route('admin.departments.index') }}",
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
                $('.nav-link').on('click', function() {
                    setTimeout(function() {
                        table.columns.adjust();
                    }, 300);
                });
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'code', name: 'code', className: 'text-center' },
                { data: 'name', name: 'name' },
                { data: 'governorate_name', name: 'governorate.name' },
                { data: 'main_warehouse', name: 'mainWarehouse.name' },
                { data: 'operation_warehouse', name: 'operationWarehouse.name' },
                { 
                    data: 'status', 
                    name: 'status',
                    className: 'text-center',
                    render: function(data) {
                        return data == 1 
                            ? '<span class="badge badge-success">نشط</span>' 
                            : '<span class="badge badge-danger">غير نشط</span>';
                    }
                },
                { 
                    data: 'action', 
                    name: 'action', 
                    orderable: false, 
                    searchable: false,
                    className: 'text-center action-buttons'
                }
            ],
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json"
            },
            dom: 'Bfrtip',
            buttons: [
                { extend: 'copy', text: 'نسخ', className: 'btn-sm btn-secondary' },
                { extend: 'csv', text: 'CSV', className: 'btn-sm btn-secondary' },
                { extend: 'excel', text: 'Excel', className: 'btn-sm btn-secondary' },
                { extend: 'pdf', text: 'PDF', className: 'btn-sm btn-secondary' },
                { extend: 'print', text: 'طباعة', className: 'btn-sm btn-secondary' }
            ]
        });

        // ============================================================
        // 2. زر التعديل - جلب البيانات وعرضها في المودال
        // ============================================================
        $(document).on('click', '.edit-department', function() {
            var id = $(this).data('id');
            
            $.ajax({
                url: "{{ route('admin.departments.edit', ':id') }}".replace(':id', id),
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        var data = response.data;
                        
                        // تعبئة الحقول
                        $('#edit_id').val(data.id);
                        $('#edit_code').val(data.code);
                        $('#edit_name').val(data.name);
                        $('#edit_governorate_id').val(data.governorate_id);
                        $('#edit_main_warehouse_id').val(data.main_warehouse_id);
                        $('#edit_operation_warehouse_id').val(data.operation_warehouse_id);
                        $('#edit_manager_name').val(data.manager_name);
                        $('#edit_manager_phone').val(data.phone); // تم التصحيح: استخدام 'phone'
                        $('#edit_status').val(data.status);
                        // $('#edit_notes').val(data.notes); // تمت إضافته
                        
                        // تحديث action في الفورم
                        var updateUrl = "{{ route('admin.departments.update', ':id') }}".replace(':id', data.id);
                        $('#editDepartmentForm').attr('action', updateUrl);
                        
                        // عرض المودال
                        $('#editDepartmentModal').modal('show');
                    } else {
                        toastr.error('حدث خطأ أثناء جلب البيانات');
                    }
                },
                error: function(xhr) {
                    toastr.error('حدث خطأ أثناء جلب البيانات');
                }
            });
        });

        // ============================================================
        // 3. زر الحذف - مع تأكيد
        // ============================================================
        $(document).on('click', '.delete-department', function() {
            var id = $(this).data('id');
            var name = $(this).data('name') || 'هذه الإدارة';
            
            Swal.fire({
                title: 'هل أنت متأكد؟',
                html: `سيتم حذف <strong>${name}</strong> بشكل نهائي!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'نعم، احذف!',
                cancelButtonText: 'إلغاء'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('admin.departments.destroy', ':id') }}".replace(':id', id),
                        type: 'DELETE',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if (response.success) {
                                toastr.success(response.message || 'تم الحذف بنجاح');
                                table.ajax.reload();
                            } else {
                                toastr.error(response.message || 'حدث خطأ أثناء الحذف');
                            }
                        },
                        error: function(xhr) {
                            var message = xhr.responseJSON?.message || 'حدث خطأ أثناء الحذف';
                            toastr.error(message);
                        }
                    });
                }
            });
        });

        // ============================================================
        // 4. عرض رسائل Toastr عند نجاح أو فشل العمليات
        // ============================================================
        @if (session('success'))
            toastr.success('{{ session('success') }}');
        @endif

        @if (session('error'))
            toastr.error('{{ session('error') }}');
        @endif
    });
</script>
@endpush