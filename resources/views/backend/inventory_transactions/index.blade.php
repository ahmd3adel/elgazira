@extends('backend.app')
@section('title', 'حركة المخزون')
@section('breadcrumb-title', 'إدارة المخازن')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">حركة المخزون</li>
@endsection

@push('custom-css')
    {{-- DataTables CSS --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap4.min.css">
    
    <style>
        #transactions-table {
            width: 100% !important;
        }
        
        #transactions-table thead th {
            background-color: #f8f9fc !important;
            color: #4e73df !important;
            font-weight: 600 !important;
            white-space: nowrap;
        }
        
        #transactions-table tbody td {
            vertical-align: middle !important;
            white-space: nowrap;
        }
        
        .dt-buttons {
            margin-bottom: 10px;
        }
        
        .dt-buttons .btn {
            margin-left: 5px;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        {{-- تنبيهات --}}
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

        {{-- فلاتر البحث --}}
        <div class="card card-outline card-info mb-3">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-filter ml-2"></i> تصفية الحركات</h3>
            </div>
            <div class="card-body">
                <form id="filterForm">
                    <div class="row">
                        <div class="col-md-3 form-group">
                            <label>الصنف</label>
                            <select name="product_id" class="form-control select2">
                                <option value="">كل الأصناف</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 form-group">
                            <label>المستودع</label>
                            <select name="warehouse_id" class="form-control select2">
                                <option value="">كل المستودعات</option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2 form-group">
                            <label>نوع الحركة</label>
                            <select name="type" class="form-control">
                                <option value="">الكل</option>
                                <option value="in">وارد (In)</option>
                                <option value="out">صادر (Out)</option>
                            </select>
                        </div>

                        <div class="col-md-2 form-group">
                            <label>من تاريخ</label>
                            <input type="date" name="from_date" class="form-control">
                        </div>

                        <div class="col-md-2 form-group">
                            <label>إلى تاريخ</label>
                            <input type="date" name="to_date" class="form-control">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 text-right">
                            <button type="submit" class="btn btn-info btn-sm">
                                <i class="fas fa-search ml-1"></i> بحث
                            </button>
                            <button type="button" id="resetFilter" class="btn btn-secondary btn-sm">
                                <i class="fas fa-redo ml-1"></i> إعادة تعيين
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- جدول الحركات --}}
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-exchange-alt ml-2"></i> سجل حركات المخزون
                </h3>
                <div class="card-tools">
                    <a href="{{ route('admin.inventory_transactions.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus ml-1"></i> إضافة حركة جديدة
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="transactions-table" class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>التاريخ</th>
                                <th>الصنف</th>
                                <th>المستودع</th>
                                <th>نوع الحركة</th>
                                <th>الكمية</th>
                                <th>المسؤول</th>
                                <th>ملاحظات</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal تفاصيل الحركة --}}
    <div class="modal fade" id="showTransactionModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">
                        تفاصيل حركة المخزون رقم: <span id="modal-id"></span>
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th style="width: 30%;">المنتج</th>
                            <td id="modal-product"></td>
                        </tr>
                        <tr>
                            <th>المخزن</th>
                            <td id="modal-warehouse"></td>
                        </tr>
                        <tr>
                            <th>نوع الحركة</th>
                            <td><span id="modal-type" class="badge"></span></td>
                        </tr>
                        <tr>
                            <th>الكمية</th>
                            <td id="modal-quantity"></td>
                        </tr>
                        <tr>
                            <th>الكمية قبل</th>
                            <td id="modal-before"></td>
                        </tr>
                        <tr>
                            <th>الكمية بعد</th>
                            <td id="modal-after"></td>
                        </tr>
                        <tr>
                            <th>رقم المرجع</th>
                            <td id="modal-reference"></td>
                        </tr>
                        <tr>
                            <th>ملاحظات</th>
                            <td id="modal-notes"></td>
                        </tr>
                        <tr>
                            <th>المسؤول</th>
                            <td id="modal-user"></td>
                        </tr>
                        <tr>
                            <th>تاريخ الحركة</th>
                            <td id="modal-date"></td>
                        </tr>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">إغلاق</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('custom-js')
    {{-- DataTables JS --}}
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // ==========================================
            // تهيئة DataTable
            // ==========================================
            var table = $('#transactions-table').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                order: [[1, 'desc']],
                ajax: {
                    url: "{{ route('admin.inventory_transactions.index') }}",
                    type: "GET",
                    data: function(d) {
                        d.product_id = $('select[name="product_id"]').val();
                        d.warehouse_id = $('select[name="warehouse_id"]').val();
                        d.type = $('select[name="type"]').val();
                        d.from_date = $('input[name="from_date"]').val();
                        d.to_date = $('input[name="to_date"]').val();
                    }
                },
                columns: [
                    { 
                        data: 'DT_RowIndex', 
                        name: 'DT_RowIndex', 
                        orderable: false, 
                        searchable: false, 
                        className: 'text-center',
                        width: '50px'
                    },
                   {
    data: 'created_at',
    name: 'created_at',
    className: 'text-center',
    width: '140px'
},
                    { 
                        data: 'product_name', 
                        name: 'product_name'
                    },
                    { 
                        data: 'warehouse_name', 
                        name: 'warehouse_name'
                    },
                    { 
                        data: 'type_badge', 
                        name: 'type', 
                        className: 'text-center',
                        width: '100px'
                    },
                    { 
                        data: 'quantity', 
                        name: 'quantity', 
                        className: 'text-center',
                        width: '80px'
                    },
                    { 
                        data: 'user_name', 
                        name: 'user_name'
                    },
                    { 
                        data: 'notes', 
                        name: 'notes',
                        orderable: false,
                        render: function(data) {
                            if (!data) return '<span class="text-muted">-</span>';
                            return data.length > 30 ? data.substring(0, 30) + '...' : data;
                        }
                    },
                    { 
                        data: 'action', 
                        name: 'action', 
                        orderable: false, 
                        searchable: false, 
                        className: 'text-center',
                        width: '120px'
                    }
                ],
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/ar.json",
                    search: "بحث:",
                    lengthMenu: "عرض _MENU_ سجل",
                    info: "عرض _START_ إلى _END_ من _TOTAL_ سجل",
                    infoEmpty: "لا توجد سجلات",
                    infoFiltered: "(مرشح من _MAX_ إجمالي السجلات)",
                    loadingRecords: "جاري التحميل...",
                    zeroRecords: "لم يتم العثور على سجلات",
                    emptyTable: "لا توجد بيانات متاحة",
                },
                    pageLength: 100,

                dom: '<"row"<"col-md-6"B><"col-md-6"f>>' +
                     '<"row"<"col-md-12"tr>>' +
                     '<"row"<"col-md-5"i><"col-md-7"p>>',
                buttons: [
                    {
                        extend: 'copy',
                        text: '<i class="fas fa-copy"></i> نسخ',
                        className: 'btn btn-sm btn-secondary'
                    },
                    {
                        extend: 'excel',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn btn-sm btn-success'
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'btn btn-sm btn-danger'
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i> طباعة',
                        className: 'btn btn-sm btn-info'
                    }
                ],
                initComplete: function() {
                    this.api().columns.adjust();
                }
            });

            // ==========================================
            // فلترة النموذج
            // ==========================================
            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
                table.ajax.reload();
            });

            $('#resetFilter').on('click', function() {
                $('#filterForm')[0].reset();
                $('.select2').val(null).trigger('change');
                table.ajax.reload();
            });

            // ==========================================
            // عرض تفاصيل الحركة
            // ==========================================
            $(document).on('click', '.view-transaction', function() {
                var id = $(this).data('id');
                
                Swal.fire({
                    title: 'جاري التحميل...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                $.ajax({
                    url: "{{ url('admin/inventory_transactions') }}/" + id + "/json",
                    type: 'GET',
                    success: function(data) {
                        Swal.close();
                        
                        $('#modal-id').text(data.id);
                        $('#modal-product').text(data.product);
                        $('#modal-warehouse').text(data.warehouse);
                        $('#modal-quantity').text(data.quantity);
                        $('#modal-before').text(data.quantity_before ?? '---');
                        $('#modal-after').text(data.quantity_after ?? '---');
                        $('#modal-reference').text(data.reference);
                        $('#modal-notes').text(data.notes);
                        $('#modal-user').text(data.user);
                        $('#modal-date').text(data.date);
                        
                        if (data.type === 'in') {
                            $('#modal-type').text('وارد (In)').attr('class', 'badge badge-success');
                        } else {
                            $('#modal-type').text('صادر (Out)').attr('class', 'badge badge-danger');
                        }
                        
                        $('#showTransactionModal').modal('show');
                    },
                    error: function() {
                        Swal.close();
                        Swal.fire('خطأ!', 'تعذر جلب البيانات', 'error');
                    }
                });
            });

            // ==========================================
            // حذف الحركة
            // ==========================================
            $(document).on('click', '.delete-transaction', function() {
                var id = $(this).data('id');
                
                Swal.fire({
                    title: 'تأكيد الحذف',
                    text: 'هل أنت متأكد من حذف الحركة؟ سيتم عكس تأثيرها على المخزون تلقائياً.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'نعم، احذف',
                    cancelButtonText: 'إلغاء'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('admin/inventory_transactions') }}/" + id,
                            type: 'DELETE',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                Swal.fire('تم الحذف!', 'تم حذف الحركة وعكس تأثيرها', 'success');
                                table.ajax.reload();
                            },
                            error: function(xhr) {
                                Swal.fire('خطأ!', xhr.responseJSON?.message || 'حدث خطأ', 'error');
                            }
                        });
                    }
                });
            });

            // ==========================================
            // Select2
            // ==========================================
            $('.select2').select2({
                theme: 'bootstrap4',
                width: '100%',
                placeholder: '-- اختر --',
                allowClear: true
            });
        });
    </script>
@endpush