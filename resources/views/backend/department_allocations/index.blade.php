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
        .card-title i { color: #17a2b8; }
        .btn-sm { border-radius: 4px; font-weight: 600; }

        .filter-section {
            background-color: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }
        .filter-section label { margin-bottom: 5px; font-size: 14px; }

        .select2-container--default .select2-selection--multiple { border-color: #ced4da; }

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
                <button type="button" class="btn btn-info btn-sm mr-2"
                        data-toggle="modal" data-target="#addDistributionAllocationsModal">
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
                    <tbody></tbody>
                    <tfoot class="bg-light"></tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ============================================================ --}}
{{-- مودال إضافة توزيع جديد --}}
{{-- ============================================================ --}}
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
                                <input type="date" name="order_date" class="form-control"
                                       value="{{ date('Y-m-d') }}" required>
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
                                                <option value="{{ $product->id }}"
                                                        data-is-base="{{ $product->is_base ? '1' : '0' }}">
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

{{-- ============================================================ --}}
{{-- مودال تعديل إذن الصرف --}}
{{-- ============================================================ --}}
<div class="modal fade" id="editAllocationModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-edit"></i> تعديل إذن الصرف
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="editAllocationForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="allocation_id" id="edit_allocation_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>الإدارة <span class="text-danger">*</span></label>
                                <select name="department_id" id="edit_department_id" class="form-control" required>
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
                                <input type="date" name="order_date" id="edit_order_date" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h6 class="font-weight-bold"><i class="fas fa-boxes"></i> تفاصيل الكميات:</h6>

                    <div class="table-responsive">
                        <table class="table table-bordered bg-light">
                            <thead>
                                <tr class="text-center">
                                    <th width="40%">الصنف (المنتج)</th>
                                    <th width="30%">الكمية (كرتونة)</th>
                                    <th width="10%"></th>
                                </tr>
                            </thead>
                            <tbody id="editItemsContainer"></tbody>
                        </table>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="addEditItem">
                            <i class="fas fa-plus"></i> إضافة صنف آخر
                        </button>
                    </div>

                    <div class="form-group mt-3">
                        <label>ملاحظات</label>
                        <textarea name="notes" id="edit_notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">إغلاق</button>
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-save"></i> حفظ التعديلات
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
        let editItemIndex = 0;
        let table;

        // ==================== Helpers ====================
        function hasActiveFilters() {
            return $('#from_date').val()
                || $('#to_date').val()
                || ($('#department_filter').val() && $('#department_filter').val().length > 0);
        }

        function buildProductOptions(selectedId) {
            let options = '<option value="">اختر المنتج</option>';
            @foreach($products as $product)
                options += `<option value="{{ $product->id }}"
                                    data-is-base="{{ $product->is_base ? '1' : '0' }}"
                                    ${selectedId == {{ $product->id }} ? 'selected' : ''}>
                                {{ $product->name }} ({{ number_format($product->price) }} ج.م)
                            </option>`;
            @endforeach
            return options;
        }

        function buildEditRow(index, productId, quantity) {
            return `
                <tr class="edit-item-row">
                    <td>
                        <select name="items[${index}][product_id]" class="form-control product-select-edit" required>
                            ${buildProductOptions(productId)}
                        </select>
                    </td>
                    <td>
                        <input type="number" name="items[${index}][quantity]" value="${quantity}"
                               class="form-control text-center quantity-input-edit" min="1" required>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-danger btn-sm remove-edit-row">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>`;
        }

        // ==================== Totals Row ====================
        function displayTotalsRow(totals) {
            $('.grand-total-row').remove();
            let totalRow = '<tr class="grand-total-row">';
            totalRow += '<td colspan="4" class="text-center font-weight-bold">الإجمالي العام</td>';

            @foreach($products as $product)
                totalRow += `<td class="text-center font-weight-bold">${(totals.product_totals[{{ $product->id }}] || 0).toLocaleString()}</td>`;
            @endforeach

            totalRow += `<td class="text-center font-weight-bold bg-success text-white">${(totals.total_quantity || 0).toLocaleString()}</td>`;
            totalRow += '<td></td></tr>';
            $('#distribution_allocations_table tbody').append(totalRow);
        }

        // ==================== DataTable ====================
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
            drawCallback: function() {
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
                        // صف مجمّع (بدون id) → لا تعرض أزرار
                        if (row.id === undefined || row.id === null) {
                            return '<span class="badge badge-info">مجمع</span>';
                        }
                        return '<div class="btn-group btn-group-sm" role="group">' +
                               '<button type="button" class="btn btn-info btn-sm edit-btn" data-id="' + row.id + '" title="تعديل">' +
                               '<i class="fas fa-edit"></i></button> ' +
                               '<button type="button" class="btn btn-warning btn-sm refund-btn" data-id="' + row.id + '" title="استرداد">' +
                               '<i class="fas fa-undo"></i></button>' +
                               '</div>';
                    }
                }
            ],
            language: { url: "//cdn.datatables.net/plug-ins/1.10.25/i18n/Arabic.json" },
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "الكل"]],
            dom: 'Bfrtip',
            buttons: [
                { extend: 'excel', text: '<i class="fas fa-file-excel"></i> إكسيل', className: 'btn-success btn-sm' },
                { extend: 'print', text: '<i class="fas fa-print"></i> طباعة', className: 'btn-info btn-sm' }
            ]
        });

        // ==================== Select2 ====================
        function initAddSelect2(selector) {
            $(selector).select2({
                dropdownParent: $('#addDistributionAllocationsModal'),
                placeholder: "اختر المنتج",
                width: '100%',
                allowClear: true
            });
        }
        function initEditSelect2(selector) {
            $(selector).select2({
                dropdownParent: $('#editAllocationModal'),
                placeholder: "اختر المنتج",
                width: '100%',
                allowClear: true
            });
        }

        $('#department_filter').select2({
            theme: 'bootstrap4',
            placeholder: 'اختر الإدارات',
            allowClear: true
        });

        initAddSelect2('.product-select');

        // ==================== Add Item Row ====================
        $('#addItem').click(function() {
            let newRow = `
                <tr class="item-row">
                    <td>
                        <select name="items[${itemIndex}][product_id]" class="form-control product-select" required>
                            ${buildProductOptions(null)}
                        </select>
                    </td>
                    <td>
                        <input type="number" name="items[${itemIndex}][quantity]"
                               class="form-control text-center quantity-input" placeholder="0" min="1" required>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-danger btn-sm remove-row">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>`;
            $('#itemsContainer').append(newRow);
            initAddSelect2('.product-select:last');
            itemIndex++;
        });

        $(document).on('click', '.remove-row', function() {
            if ($('.item-row').length > 1) {
                $(this).closest('.item-row').remove();
            } else {
                toastr.warning('لا يمكن حذف الصنف الوحيد المتبقي');
            }
        });

        // ==================== Balance Check (Add) ====================
        function calculateBalance(container) {
            let baseQuantity = 0;
            let otherTotal = 0;
            let hasBase = false;

            $(container + ' .item-row, ' + container + ' .edit-item-row').each(function() {
                let select = $(this).find('select[name*="[product_id]"]');
                let qty = parseInt($(this).find('input[type="number"]').val()) || 0;

                if (!select.val() || qty <= 0) return;

                let isBase = select.find('option:selected').data('is-base') == 1;

                if (isBase) {
                    hasBase = true;
                    baseQuantity = qty;
                } else {
                    otherTotal += qty;
                }
            });

            if (!hasBase && otherTotal === 0) return true;
            return hasBase && baseQuantity === otherTotal;
        }

        $(document).on('input change', '#itemsContainer .quantity-input, #itemsContainer .product-select',
            function() { calculateBalance('#itemsContainer'); });

        // ==================== Submit Add Form ====================
        $('#addDistributionForm').on('submit', function(e) {
            e.preventDefault();

            if (!calculateBalance('#itemsContainer')) {
                toastr.error('⚠️ كمية الصنف الأساسي لا تتساوى مع مجموع الأصناف التامة');
                return false;
            }

            let hasQuantity = false;
            $('.quantity-input').each(function() {
                if (parseInt($(this).val()) > 0) hasQuantity = true;
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
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    if (response.success) {
                        $('#addDistributionAllocationsModal').modal('hide');
                        $('#addDistributionForm')[0].reset();

                        // reset items container
                        $('#itemsContainer').html(`
                            <tr class="item-row">
                                <td>
                                    <select name="items[0][product_id]" class="form-control product-select" required>
                                        ${buildProductOptions(null)}
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="items[0][quantity]"
                                           class="form-control text-center quantity-input"
                                           placeholder="0" min="1" required>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-danger btn-sm remove-row" disabled>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>`);
                        initAddSelect2('.product-select');
                        itemIndex = 1;
                        table.ajax.reload();
                        toastr.success(response.message || 'تم التسجيل بنجاح');
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    let errorMsg = 'حدث خطأ ما!';
                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        errorMsg = Object.values(xhr.responseJSON.errors).flat().join('\n');
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

        // ==================== Edit Modal: Open ====================
        $(document).on('click', '.edit-btn', function() {
            let id = $(this).data('id');

            $.ajax({
                url: "{{ route('admin.department_allocations.editData', ':id') }}".replace(':id', id),
                type: "GET",
                success: function(res) {
                    if (!res.success) {
                        toastr.error(res.message);
                        return;
                    }
                    let d = res.data;

                    $('#edit_allocation_id').val(d.id);
                    $('#edit_department_id').val(d.department_id);
                    $('#edit_order_date').val(d.receite_date);
                    $('#edit_notes').val(d.notes || '');

                    let html = '';
                    d.items.forEach(function(item, idx) {
                        html += buildEditRow(idx, item.product_id, item.quantity);
                    });
                    if (!html) html = buildEditRow(0, null, '');
                    $('#editItemsContainer').html(html);

                    // init select2 for all selects inside edit modal
                    initEditSelect2('#editItemsContainer .product-select-edit');

                    editItemIndex = d.items.length || 1;

                    $('#editAllocationModal').modal('show');
                },
                error: function() {
                    toastr.error('تعذر جلب البيانات');
                }
            });
        });

        // ==================== Edit Modal: Add / Remove Row ====================
        $('#addEditItem').click(function() {
            $('#editItemsContainer').append(buildEditRow(editItemIndex, null, ''));
            initEditSelect2('#editItemsContainer .product-select-edit:last');
            editItemIndex++;
        });

        $(document).on('click', '.remove-edit-row', function() {
            if ($('#editItemsContainer .edit-item-row').length > 1) {
                $(this).closest('.edit-item-row').remove();
            } else {
                toastr.warning('لا يمكن حذف الصنف الوحيد');
            }
        });

        // ==================== Edit Modal: Submit ====================
        $('#editAllocationForm').on('submit', function(e) {
            e.preventDefault();

            if (!calculateBalance('#editItemsContainer')) {
                toastr.error('⚠️ كمية الصنف الأساسي لا تتساوى مع مجموع الأصناف التامة');
                return false;
            }

            let id = $('#edit_allocation_id').val();
            let submitBtn = $(this).find('button[type="submit"]');
            let originalText = submitBtn.html();
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> جاري الحفظ...');

            $.ajax({
                url: "{{ route('admin.department_allocations.updateAllocation', ':id') }}".replace(':id', id),
                type: "POST", // نستخدم POST لأننا نرسل FormData مع _method=PUT
                data: new FormData(this),
                processData: false,
                contentType: false,
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(res) {
                    if (res.success) {
                        $('#editAllocationModal').modal('hide');
                        table.ajax.reload();
                        toastr.success(res.message);
                    } else {
                        toastr.error(res.message);
                    }
                },
                error: function(xhr) {
                    let msg = xhr.responseJSON?.message || 'حدث خطأ!';
                    toastr.error(msg);
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });

        // ==================== Refund (استرداد) ====================
        $(document).on('click', '.refund-btn', function() {
            let id = $(this).data('id');

            Swal.fire({
                title: 'تأكيد الاسترداد',
                html: 'سيتم <b>إرجاع الكميات</b> لمخزن الإدارة وحذف الإذن نهائياً.<br>هل أنت متأكد؟',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'نعم، استرداد',
                cancelButtonText: 'إلغاء'
            }).then((result) => {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: "{{ route('admin.department_allocations.refund', ':id') }}".replace(':id', id),
                    type: "POST",
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        if (res.success) {
                            Swal.fire('تم!', res.message, 'success');
                            table.ajax.reload();
                        } else {
                            Swal.fire('خطأ!', res.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        let msg = xhr.responseJSON?.message || 'تعذر تنفيذ الاسترداد';
                        Swal.fire('خطأ!', msg, 'error');
                    }
                });
            });
        });

        // ==================== Filters ====================
        $('#reset_button').click(function() {
            $('#from_date').val('');
            $('#to_date').val('');
            $('#department_filter').val(null).trigger('change');
            table.ajax.reload();
        });

        $('#from_date, #to_date, #department_filter').on('change', function() {
            table.ajax.reload();
        });

        // فتح مودال الإضافة في حالة أخطاء validation
        @if ($errors->any())
            $('#addDistributionAllocationsModal').modal('show');
        @endif
    });
    </script>
@endpush