@extends('backend.app')
@section('title', 'إدارة المنتجات')
@section('breadcrumb-title', 'إدارة الموقع')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">المنتجات</li>
@endsection
@push('custom-css')
    {{-- تضمين Select2 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    
    <style>
        /* ... الكود السابق ... */
        
        /* تحسين شكل Select2 */
        .select2-container--bootstrap-5 .select2-selection {
            min-height: 38px;
        }
        .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__rendered {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }
        .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice {
            background-color: #17a2b8;
            color: white;
            border: none;
            padding: 2px 10px;
            border-radius: 4px;
        }
        .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice__remove {
            color: white;
            margin-right: 5px;
        }
    </style>
@endpush
@push('custom-css')
    {{-- يفضل مستقبلاً نقل هذه الملفات لمجلد خاص بـ products --}}
    @include('backend.products.partials.styles')
    <style>
        .card-title i {
            color: #17a2b8;
        }

        /* تغيير اللون للأزرق لتمييز قسم المناطق */
        .btn-sm {
            border-radius: 4px;
            font-weight: 600;
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

        <div class="card card-outline card-info"> {{-- لون info (أزرق) مناسب للمناطق الجغرافية --}}
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-map-marker-alt ml-2"></i>
                    قائمة المنتجات المسجلة
                </h3>
                <div class="card-tools d-flex">
                    @include('backend.products.partials.export-buttons')
                    <button type="button" class="btn btn-info btn-sm mr-2" data-toggle="modal"
                        data-target="#addProductModal">
                        <i class="fas fa-plus"></i> إضافة منتج جديدة
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive w-100">
                    <table id="products-table" class="table table-bordered table-striped nowrap">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>كود المنتج</th>
                                <th>اسم المنتج</th>
                                <th>عدد البواكي</th> <!-- بدلاً من عدد البواكي فقط لتوضيح المعامل -->
                                <th>الموردين </th>
                                <th> الصلاحية</th> <!-- بدلاً من عدد البواكي فقط لتوضيح المعامل -->
                                <th> الإجراءات</th> <!-- بدلاً من عدد البواكي فقط لتوضيح المعامل -->
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

    {{-- مودال إضافة منتج --}}
    <div class="modal fade" id="addProductModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="fas fa-plus-circle ml-2"></i> إضافة منتج جديد</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form id="addProductForm" action="{{ route('admin.products.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>كود المنتج <span class="text-danger">*</span></label>
                                    <input type="text" name="code" class="form-control" placeholder="مثال: PRD-001"
                                        required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>اسم المنتج <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" placeholder="مثال: شامبو"
                                        required>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>الوصف</label>
                                    <textarea name="description" class="form-control" rows="2"></textarea>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>الكمية</label>
                                    <input type="number" name="quantity" class="form-control" placeholder="0"
                                        min="0">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>السعر</label>
                                    <input type="number" name="price" class="form-control" placeholder="0.00"
                                        min="0" step="0.01">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>تاريخ الصلاحية</label>
                                    <input type="date" name="expiry_date" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>الموردين</label>
                                    <select name="suppliers[]" class="form-control select2" multiple>
                                        @foreach ($suppliers ?? [] as $supplier)
                                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>منتج مرافق</label>
                                    <select name="companion_product_id" class="form-control">
                                        <option value="">-- اختر --</option>
                                        @foreach (\App\Models\Product::all() as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                                        @endforeach
                                    </select>
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
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-info px-4">
                            <i class="fas fa-save ml-1"></i> حفظ المنتج
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- مودال تعديل منتج --}}
    <div class="modal fade" id="editProductModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-edit ml-2"></i> تعديل المنتج</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form id="editProductForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <input type="hidden" id="edit_id" name="id">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>كود المنتج <span class="text-danger">*</span></label>
                                    <input type="text" id="edit_code" name="code" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>اسم المنتج <span class="text-danger">*</span></label>
                                    <input type="text" id="edit_name" name="name" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>الوصف</label>
                                    <textarea id="edit_description" name="description" class="form-control" rows="2"></textarea>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>الكمية</label>
                                    <input type="number" id="edit_quantity" name="quantity" class="form-control"
                                        min="0">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>السعر</label>
                                    <input type="number" id="edit_price" name="price" class="form-control"
                                        min="0" step="0.01">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>تاريخ الصلاحية</label>
                                    <input type="date" id="edit_expiry_date" name="expiry_date" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>الموردين</label>
                                    <select id="edit_suppliers" name="suppliers[]" class="form-control select2" multiple>
                                        @foreach ($suppliers ?? [] as $supplier)
                                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>منتج مرافق</label>
                                    <select id="edit_companion_product_id" name="companion_product_id"
                                        class="form-control">
                                        <option value="">-- اختر --</option>
                                        @foreach (\App\Models\Product::all() as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                                        @endforeach
                                    </select>
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
        var table = $('#products-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.products.index') }}",
                type: "GET",
            },
           columns: [
    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
    { data: 'sku', name: 'sku' },
    { data: 'name', name: 'name' },
    // { data: 'quantity', name: 'quantity' },
    { data: 'suppliers_names', name: 'suppliers_names', orderable: false, searchable: false },
    { data: 'expiry_duration', name: 'expiry_duration', orderable: false, searchable: false },
        { data: 'expiry_duration', name: 'expiry_duration', orderable: false, searchable: false },

    { 
        data: 'action',  // ✅ تأكد من وجود هذا العمود
        name: 'action', 
        orderable: false, 
        searchable: false,
        className: 'text-center'
    }
],
            language: {
    url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json"
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
        // 2. زر التعديل
        // ============================================================
        $(document).on('click', '.edit-product', function() {
            var id = $(this).data('id');
            
            $.ajax({
                url: "{{ route('admin.products.edit', ':id') }}".replace(':id', id),
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        var data = response.data;
                        
                        $('#edit_id').val(data.id);
                        $('#edit_code').val(data.code);
                        $('#edit_name').val(data.name);
                        $('#edit_description').val(data.description);
                        $('#edit_quantity').val(data.quantity);
                        $('#edit_price').val(data.price);
                        $('#edit_expiry_date').val(data.expiry_date);
                        $('#edit_companion_product_id').val(data.companion_product_id);
                        $('#edit_status').val(data.status);
                        
                        // تحديث الموردين (Select2)
                        if (response.supplier_ids) {
                            $('#edit_suppliers').val(response.supplier_ids).trigger('change');
                        }
                        
                        var updateUrl = "{{ route('admin.products.update', ':id') }}".replace(':id', data.id);
                        $('#editProductForm').attr('action', updateUrl);
                        
                        $('#editProductModal').modal('show');
                    }
                },
                error: function(xhr) {
                    toastr.error('حدث خطأ أثناء جلب البيانات');
                }
            });
        });

        // ============================================================
        // 3. زر الحذف
        // ============================================================
        $(document).on('click', '.delete-product', function() {
            var id = $(this).data('id');
            var name = $(this).data('name') || 'هذا المنتج';
            
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
                        url: "{{ route('admin.products.destroy', ':id') }}".replace(':id', id),
                        type: 'DELETE',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if (response.success) {
                                toastr.success(response.message);
                                table.ajax.reload();
                            } else {
                                toastr.error(response.message);
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
        // 4. تفعيل Select2
        // ============================================================
        $('.select2').select2({
            width: '100%',
            placeholder: 'اختر الموردين',
            allowClear: true
        });

        // ============================================================
        // 5. رسائل Toastr
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
