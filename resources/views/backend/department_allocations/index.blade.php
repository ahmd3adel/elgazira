@extends('backend.app')
@section('title', 'إدارة التوزيعات')
@section('breadcrumb-title', 'إدارة الموقع')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">التوزيعات</li>
@endsection

@push('custom-css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    
    <style>
        .card-title i {
            color: #17a2b8;
        }

        .btn-sm {
            border-radius: 4px;
            font-weight: 600;
        }
        
        .filter-section {
            background-color: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }
        
        .filter-section label {
            margin-bottom: 5px;
            font-size: 14px;
        }
        
        .select2-container--default .select2-selection--multiple {
            border-color: #ced4da;
        }
        
        .grand-total-row {
            background-color: #d4edda !important;
            font-weight: bold;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle ml-1"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-map-marker-alt ml-2"></i>
                    قائمة التوزيعات 
                </h3>
                <div class="card-tools d-flex">
                    <button type="button" class="btn btn-info btn-sm mr-2" data-toggle="modal" data-target="#addDistributionAllocationsModal">
                        <i class="fas fa-plus"></i> إضافة توزيع جديد
                    </button>
                </div>
            </div>
            
            <div class="card-body">
                {{-- قسم الفلتر --}}
                <div class="filter-section">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label class="font-weight-bold">الإدارة:</label>
                            <select id="department_filter" class="form-control filter-input select2" multiple="multiple">
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="font-weight-bold">من تاريخ:</label>
                            <input type="date" id="from_date" class="form-control filter-input">
                        </div>

                        <div class="col-md-3">
                            <label class="font-weight-bold">إلى تاريخ:</label>
                            <input type="date" id="to_date" class="form-control filter-input">
                        </div>

                        <div class="col-md-3">
                            <button id="reset_button" class="btn btn-outline-danger btn-block">
                                <i class="fas fa-sync-alt"></i> إعادة تعيين
                            </button>
                        </div>
                    </div>
                </div>

                {{-- الجدول --}}
                <div class="table-responsive">
                    <table id="distribution_allocations_table" class="table table-bordered table-striped table-hover w-100">
                        <thead class="bg-dark text-white">
                            <tr>
                                <th>#</th>
                                <th>تاريخ الصرف</th>
                                <th>الإدارة</th>
                                <th>نوع الجهة</th>
                                @foreach($products as $product)
                                    <th>{{ $product->name }}</th>
                                @endforeach
                                <th>إجمالي الكمية</th>
                                <th>العمليات</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                        <tfoot class="bg-light">
                            <!-- سيتم إضافة صف الإجمالي بواسطة JavaScript -->
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- مودال إضافة توزيع جديد --}}
    <div class="modal fade" id="addDistributionAllocationsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-truck-loading"></i> تسجيل إذن صرف (يومي)
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="addDistributionForm">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>الإدارة <span class="text-danger">*</span></label>
                                    <select name="department_id" class="form-control" id="department_id" required>
                                        <option value="">اختر الإدارة</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>تاريخ الصرف <span class="text-danger">*</span></label>
                                    <input type="date" name="order_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                </div>
                            </div>
                        </div>

                        <hr>
                        <h6 class="font-weight-bold"><i class="fas fa-boxes"></i> تفاصيل الكميات المنصرفة:</h6>

                        <div class="table-responsive">
                            <table class="table table-bordered bg-light">
                                <thead>
                                    <tr class="text-center">
                                        <th width="40%">الصنف (المنتج)</th>
                                        <th width="30%">الكمية (كرتونة)</th>
                                        <th width="10%"></th>
                                    </tr>
                                </thead>
                                <tbody id="itemsContainer">
                                    <tr class="item-row">
                                        <td>
                                            <select name="items[0][product_id]" class="form-control product-select" required>
                                                <option value="">اختر المنتج</option>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->id }}">
                                                        {{ $product->name }} ({{ number_format($product->price) }} ج.م)
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="items[0][quantity]"
                                                class="form-control text-center quantity-input" 
                                                placeholder="0" min="1" step="1" required>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-danger btn-sm remove-row" disabled>
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="addItem">
                                <i class="fas fa-plus"></i> إضافة صنف آخر
                            </button>
                        </div>

                        <div class="form-group mt-3">
                            <label>ملاحظات</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="أي ملاحظات إضافية..."></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">إغلاق</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> اعتماد الصرف
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('custom-js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
    $(document).ready(function() {
        let itemIndex = 1;
        var table;

        // دالة التحقق من وجود فلاتر نشطة
        function hasActiveFilters() {
            return $('#from_date').val() || $('#to_date').val() || ($('#department_filter').val() && $('#department_filter').val().length > 0);
        }

        // دالة عرض صف الإجماليات
        function displayTotalsRow(totals) {
            $('.grand-total-row').remove();
            let totalRow = '<tr class="grand-total-row">';
            totalRow += '<td colspan="4" class="text-center font-weight-bold">الإجمالي العام</td>';
            
            @foreach($products as $product)
                totalRow += `<td class="text-center font-weight-bold">${(totals.product_totals[{{ $product->id }}] || 0).toLocaleString()}</td>`;
            @endforeach

            totalRow += `<td class="text-center font-weight-bold bg-success text-white">${(totals.total_quantity || 0).toLocaleString()}</td>`;
            totalRow += '<td></td>';
            totalRow += '</tr>';
            $('#distribution_allocations_table tbody').append(totalRow);
        }

        // 1. تهيئة DataTable
        table = $('#distribution_allocations_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.department_allocations.index') }}",
                type: "GET",
                data: function (d) {
                    d.from_date = $('#from_date').val();
                    d.to_date = $('#to_date').val();
                    d.department_id = $('#department_filter').val();
                },
                dataSrc: function(json) {
                    window.tableTotals = json.totals || null;
                    return json.data;
                }
            },
            drawCallback: function(settings) {
                if (window.tableTotals && hasActiveFilters()) {
                    displayTotalsRow(window.tableTotals);
                } else {
                    $('.grand-total-row').remove();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'order_date', name: 'order_date' },
                { data: 'department_name', name: 'department_name' },
                { data: 'entity_type', name: 'entity_type' },
                @foreach($products as $product)
                { 
                    data: 'prod_{{ $product->id }}', 
                    name: 'prod_{{ $product->id }}',
                    render: function(data) { return data || 0; },
                    className: 'text-center'
                },
                @endforeach
                { data: 'total_qty', name: 'total_qty', className: 'text-center font-weight-bold' },
                { 
                    data: null,
                    name: 'action', 
                    orderable: false, 
                    searchable: false,
                    className: 'text-center',
                    render: function(data, type, row) {
                        return '<div class="btn-group btn-group-sm" role="group">' +
                               '<button type="button" class="btn btn-info btn-sm edit-btn" data-id="' + row.id + '">' +
                               '<i class="fas fa-edit"></i></button> ' +
                               '<button type="button" class="btn btn-danger btn-sm delete-btn" data-id="' + row.id + '">' +
                               '<i class="fas fa-trash"></i></button>' +
                               '</div>';
                    }
                }
            ],
            language: { 
                url: "//cdn.datatables.net/plug-ins/1.10.25/i18n/Arabic.json"
            },
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "الكل"]],
            dom: 'Bfrtip',
            buttons: [
                { extend: 'excel', text: '<i class="fas fa-file-excel"></i> إكسيل', className: 'btn-success btn-sm' },
                { extend: 'print', text: '<i class="fas fa-print"></i> طباعة', className: 'btn-info btn-sm' }
            ]
        });

        // 2. تهيئة Select2 للمنتجات
        function initProductSelect2(selector) {
            $(selector).select2({
                dropdownParent: $('#addDistributionAllocationsModal'),
                placeholder: "اختر المنتج",
                width: '100%',
                allowClear: true
            });
        }

        // تهيئة Select2 للفلاتر
        $('#department_filter').select2({
            theme: 'bootstrap4',
            placeholder: 'اختر الإدارات',
            allowClear: true
        });

        // تهيئة أولية للمنتجات
        initProductSelect2('.product-select');

        // 3. إضافة سطر صنف جديد
        $('#addItem').click(function() {
            let newRow = `
                <tr class="item-row">
                    <td>
                        <select name="items[${itemIndex}][product_id]" class="form-control product-select" required>
                            <option value="">اختر المنتج</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">
                                    {{ $product->name }} ({{ number_format($product->price) }} ج.م)
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="number" name="items[${itemIndex}][quantity]" class="form-control text-center quantity-input" placeholder="0" min="1" required>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-danger btn-sm remove-row">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>`;
            $('#itemsContainer').append(newRow);
            initProductSelect2(`.product-select:last`);
            itemIndex++;
        });

        // 4. حذف سطر
        $(document).on('click', '.remove-row', function() {
            if ($('.item-row').length > 1) {
                $(this).closest('.item-row').remove();
            } else {
                toastr.warning('لا يمكن حذف الصنف الوحيد المتبقي');
            }
        });

        // 5. حفظ النموذج
        $('#addDistributionForm').on('submit', function(e) {
            e.preventDefault();
            
            // التحقق من وجود كميات
            let hasQuantity = false;
            $('.quantity-input').each(function() {
                let val = parseInt($(this).val());
                if (val > 0) hasQuantity = true;
            });
            
            if (!hasQuantity) {
                toastr.error('يرجى إدخال كميات للأصناف');
                return;
            }
            
            let submitBtn = $(this).find('button[type="submit"]');
            let originalText = submitBtn.html();
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> جاري الحفظ...');

            $.ajax({
                url: "{{ route('admin.department_allocations.store') }}",
                type: "POST",
                data: new FormData(this),
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        $('#addDistributionAllocationsModal').modal('hide');
                        $('#addDistributionForm')[0].reset();
                        
                        // إعادة تعيين الجدول
                        $('#itemsContainer').html(`
                            <tr class="item-row">
                                <td>
                                    <select name="items[0][product_id]" class="form-control product-select" required>
                                        <option value="">اختر المنتج</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}">
                                                {{ $product->name }} ({{ number_format($product->price) }} ج.م)
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="items[0][quantity]" class="form-control text-center quantity-input" placeholder="0" min="1" required>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-danger btn-sm remove-row" disabled>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `);
                        
                        initProductSelect2('.product-select');
                        itemIndex = 1;
                        table.ajax.reload();
                        toastr.success('تم التسجيل بنجاح');
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    let errorMsg = 'حدث خطأ ما!';
                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        let errors = xhr.responseJSON.errors;
                        errorMsg = Object.values(errors).flat().join('\n');
                    } else if (xhr.responseJSON?.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    toastr.error(errorMsg);
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });

        // 6. إعادة تعيين الفلاتر
        $('#reset_button').click(function() {
            $('#from_date').val('');
            $('#to_date').val('');
            $('#department_filter').val(null).trigger('change');
            table.ajax.reload();
        });

        // 7. تحديث الجدول عند تغيير الفلاتر
        $('#from_date, #to_date, #department_filter').on('change', function() {
            table.ajax.reload();
        });

        // 8. فتح المودال في حالة وجود أخطاء Validation
        @if ($errors->any())
            $('#addDistributionAllocationsModal').modal('show');
        @endif
    });
    </script>
@endpush