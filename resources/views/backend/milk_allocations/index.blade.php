@extends('backend.app')
@section('title', 'توزيع الألبان')
@section('breadcrumb-title', 'إدارة التوزيعات')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">توزيع الألبان</li>
@endsection

@push('custom-css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .filter-section {
            background-color: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }
        .grand-total-row {
            background-color: #d4edda !important;
            font-weight: bold;
            font-size: 15px;
        }
        .preview-box {
            background: #e7f3ff;
            border: 2px dashed #17a2b8;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
        }
        .preview-box .item {
            text-align: center;
            padding: 8px;
            border-right: 1px solid #bee5eb;
        }
        .preview-box .item:last-child { border-right: none; }
        .preview-box .item label {
            font-size: 12px;
            color: #555;
            display: block;
            margin-bottom: 3px;
        }
        .preview-box .item .value {
            font-size: 20px;
            font-weight: bold;
            color: #17a2b8;
        }
    </style>
@endpush

@section('content')
<div class="container-fluid">

    <div class="card card-outline card-info">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-glass-whiskey ml-2"></i>
                توزيع الألبان على الإدارات
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-info btn-sm"
                        data-toggle="modal" data-target="#addMilkAllocationModal">
                    <i class="fas fa-plus"></i> إضافة توزيع جديد
                </button>
            </div>
        </div>

        <div class="card-body">
            {{-- الفلاتر --}}
            <div class="filter-section">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label class="font-weight-bold">الإدارة:</label>
                        <select id="department_filter" class="form-control" multiple="multiple"></select>
                    </div>
                    <div class="col-md-3">
                        <label class="font-weight-bold">من تاريخ:</label>
                        <input type="date" id="from_date" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="font-weight-bold">إلى تاريخ:</label>
                        <input type="date" id="to_date" class="form-control">
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
                <table id="milk_allocations_table" class="table table-bordered table-striped table-hover w-100">
                    <thead class="bg-dark text-white">
                        <tr>
                            <th>#</th>
                            <th>التاريخ</th>
                            <th>الإدارة</th>
                            <th>كراتين السادة</th>
                            <th>باكو السادة</th>
                            <th>علب اللبن</th>
                            <th>كراتين اللبن</th>
                            <th>إجمالي الوجبات</th>
                            <th>العمليات</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- مودال الإضافة --}}
<div class="modal fade" id="addMilkAllocationModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-plus-circle"></i> إضافة توزيع جديد</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="addMilkForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>الإدارة <span class="text-danger">*</span></label>
                                <select name="department_id" class="form-control" required>
                                    <option value="">اختر الإدارة</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>التاريخ <span class="text-danger">*</span></label>
                                <input type="date" name="allocation_date" class="form-control"
                                       value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>كمية السادة (كرتونة) <span class="text-danger">*</span></label>
                        <input type="number" name="biscuit_cartons" id="add_biscuit_cartons"
                               class="form-control form-control-lg text-center"
                               placeholder="مثال: 100" min="1" required>
                    </div>

                    {{-- معاينة تلقائية --}}
                    <div class="preview-box">
                        <div class="row">
                            <div class="col-3 item">
                                <label>باكو السادة</label>
                                <div class="value" id="preview_biscuit_packs">0</div>
                            </div>
                            <div class="col-3 item">
                                <label>علب اللبن</label>
                                <div class="value" id="preview_milk_cans">0</div>
                            </div>
                            <div class="col-3 item">
                                <label>كراتين اللبن</label>
                                <div class="value" id="preview_milk_cartons">0</div>
                            </div>
                            <div class="col-3 item">
                                <label>إجمالي الوجبات</label>
                                <div class="value text-success" id="preview_total_meals">0</div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mt-3">
                        <label>ملاحظات</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">إغلاق</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> حفظ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- مودال التعديل --}}
<div class="modal fade" id="editMilkAllocationModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-edit"></i> تعديل التوزيع</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="editMilkForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="allocation_id" id="edit_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>الإدارة <span class="text-danger">*</span></label>
                                <select name="department_id" id="edit_department_id" class="form-control" required>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>التاريخ <span class="text-danger">*</span></label>
                                <input type="date" name="allocation_date" id="edit_allocation_date"
                                       class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>كمية السادة (كرتونة) <span class="text-danger">*</span></label>
                        <input type="number" name="biscuit_cartons" id="edit_biscuit_cartons"
                               class="form-control form-control-lg text-center" min="1" required>
                    </div>

                    <div class="preview-box">
                        <div class="row">
                            <div class="col-3 item">
                                <label>باكو السادة</label>
                                <div class="value" id="edit_preview_biscuit_packs">0</div>
                            </div>
                            <div class="col-3 item">
                                <label>علب اللبن</label>
                                <div class="value" id="edit_preview_milk_cans">0</div>
                            </div>
                            <div class="col-3 item">
                                <label>كراتين اللبن</label>
                                <div class="value" id="edit_preview_milk_cartons">0</div>
                            </div>
                            <div class="col-3 item">
                                <label>إجمالي الوجبات</label>
                                <div class="value text-success" id="edit_preview_total_meals">0</div>
                            </div>
                        </div>
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
$(document).ready(function () {

    // ============ الثوابت ============
    const BISCUIT_PACKS_PER_CARTON = 108;
    const MILK_CANS_PER_CARTON     = 27;

    // ============ دالة حساب ============
    function calculate(cartons) {
        cartons = parseInt(cartons) || 0;
        const biscuitPacks = cartons * BISCUIT_PACKS_PER_CARTON;
        const milkCans     = biscuitPacks;
        const milkCartons  = Math.ceil(milkCans / MILK_CANS_PER_CARTON);
        const totalMeals   = biscuitPacks;

        return { biscuitPacks, milkCans, milkCartons, totalMeals };
    }

    function formatNumber(n) {
        return Number(n || 0).toLocaleString('en-US');
    }

    // ============ Select2 للفلتر ============
    $('#department_filter').select2({
        placeholder: 'اختر الإدارات',
        allowClear: true,
        width: '100%',
        ajax: {
            url: "{{ route('admin.departments.index') }}",
            dataType: 'json',
            delay: 250,
            data: params => ({ search: params.term, page: params.page || 1, per_page: 30 }),
            processResults: function (data) {
                let items = data.data || data;
                return {
                    results: items.map(d => ({ id: d.id, text: d.name }))
                };
            },
            cache: true
        }
    });

    // ============ DataTable ============
    let table = $('#milk_allocations_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.milk_allocations.index') }}",
            type: "GET",
            data: function (d) {
                d.from_date     = $('#from_date').val();
                d.to_date       = $('#to_date').val();
                d.department_id = $('#department_filter').val();
            },
            dataSrc: function (json) {
                window.totals = json.totals || null;
                return json.data;
            }
        },
        drawCallback: function () {
            $('.grand-total-row').remove();
            if (window.totals) {
                let t = window.totals;
                let row = '<tr class="grand-total-row">';
                row += '<td colspan="3" class="text-center">الإجمالي العام</td>';
                row += `<td class="text-center">${formatNumber(t.biscuit_cartons)}</td>`;
                row += `<td class="text-center">${formatNumber(t.biscuit_packs)}</td>`;
                row += `<td class="text-center">${formatNumber(t.milk_cans)}</td>`;
                row += `<td class="text-center">${formatNumber(t.milk_cartons)}</td>`;
                row += `<td class="text-center bg-success text-white">${formatNumber(t.total_meals)}</td>`;
                row += '<td></td></tr>';
                $('#milk_allocations_table tbody').append(row);
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'date_formatted', name: 'date_formatted' },
            { data: 'department_name', name: 'department_name' },
            { data: 'biscuit_cartons', name: 'biscuit_cartons', className: 'text-center' },
            { data: 'biscuit_packs',   name: 'biscuit_packs',   className: 'text-center' },
            { data: 'milk_cans',       name: 'milk_cans',       className: 'text-center' },
            { data: 'milk_cartons',    name: 'milk_cartons',    className: 'text-center' },
            { data: 'total_meals',     name: 'total_meals',     className: 'text-center font-weight-bold text-success' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
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

    // ============ معاينة إضافة ============
    $('#add_biscuit_cartons').on('input', function () {
        const r = calculate($(this).val());
        $('#preview_biscuit_packs').text(formatNumber(r.biscuitPacks));
        $('#preview_milk_cans').text(formatNumber(r.milkCans));
        $('#preview_milk_cartons').text(formatNumber(r.milkCartons));
        $('#preview_total_meals').text(formatNumber(r.totalMeals));
    });

    // ============ معاينة تعديل ============
    $('#edit_biscuit_cartons').on('input', function () {
        const r = calculate($(this).val());
        $('#edit_preview_biscuit_packs').text(formatNumber(r.biscuitPacks));
        $('#edit_preview_milk_cans').text(formatNumber(r.milkCans));
        $('#edit_preview_milk_cartons').text(formatNumber(r.milkCartons));
        $('#edit_preview_total_meals').text(formatNumber(r.totalMeals));
    });

    // ============ إضافة ============
    $('#addMilkForm').on('submit', function (e) {
        e.preventDefault();
        let btn = $(this).find('button[type="submit"]');
        let old = btn.html();
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> جاري الحفظ...');

        $.ajax({
            url: "{{ route('admin.milk_allocations.store') }}",
            type: "POST",
            data: new FormData(this),
            processData: false,
            contentType: false,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (res) {
                if (res.success) {
                    $('#addMilkAllocationModal').modal('hide');
                    $('#addMilkForm')[0].reset();
                    $('#preview_biscuit_packs, #preview_milk_cans, #preview_milk_cartons, #preview_total_meals').text('0');
                    table.ajax.reload();
                    toastr.success(res.message);
                } else {
                    toastr.error(res.message);
                }
            },
            error: function (xhr) {
                let msg = xhr.responseJSON?.message || 'حدث خطأ';
                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    msg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                }
                toastr.error(msg);
            },
            complete: () => btn.prop('disabled', false).html(old)
        });
    });

    // ============ فتح التعديل ============
    $(document).on('click', '.edit-btn', function () {
        let id = $(this).data('id');
        $.ajax({
            url: "{{ route('admin.milk_allocations.editData', ':id') }}".replace(':id', id),
            type: "GET",
            success: function (res) {
                if (!res.success) return toastr.error(res.message);
                let d = res.data;
                $('#edit_id').val(d.id);
                $('#edit_department_id').val(d.department_id);
                $('#edit_allocation_date').val(d.allocation_date);
                $('#edit_biscuit_cartons').val(d.biscuit_cartons).trigger('input');
                $('#edit_notes').val(d.notes || '');
                $('#editMilkAllocationModal').modal('show');
            },
            error: () => toastr.error('تعذر جلب البيانات')
        });
    });

    // ============ حفظ التعديل ============
    $('#editMilkForm').on('submit', function (e) {
        e.preventDefault();
        let id = $('#edit_id').val();
        let btn = $(this).find('button[type="submit"]');
        let old = btn.html();
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> جاري الحفظ...');

        $.ajax({
            url: "{{ route('admin.milk_allocations.update', ':id') }}".replace(':id', id),
            type: "POST",
            data: new FormData(this),
            processData: false,
            contentType: false,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (res) {
                if (res.success) {
                    $('#editMilkAllocationModal').modal('hide');
                    table.ajax.reload();
                    toastr.success(res.message);
                } else {
                    toastr.error(res.message);
                }
            },
            error: function (xhr) {
                toastr.error(xhr.responseJSON?.message || 'حدث خطأ');
            },
            complete: () => btn.prop('disabled', false).html(old)
        });
    });

    // ============ حذف ============
    $(document).on('click', '.delete-btn', function () {
        let id = $(this).data('id');
        Swal.fire({
            title: 'تأكيد الحذف',
            text: 'سيتم حذف السجل نهائياً. هل أنت متأكد؟',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'نعم، احذف',
            cancelButtonText: 'إلغاء'
        }).then((result) => {
            if (!result.isConfirmed) return;
            $.ajax({
                url: "{{ route('admin.milk_allocations.destroy', ':id') }}".replace(':id', id),
                type: "POST",
                data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                success: function (res) {
                    if (res.success) {
                        table.ajax.reload();
                        toastr.success(res.message);
                    } else {
                        toastr.error(res.message);
                    }
                },
                error: () => toastr.error('تعذر الحذف')
            });
        });
    });

    // ============ الفلاتر ============
    $('#from_date, #to_date, #department_filter').on('change', () => table.ajax.reload());
    $('#reset_button').on('click', function () {
        $('#from_date').val('');
        $('#to_date').val('');
        $('#department_filter').val(null).trigger('change');
        table.ajax.reload();
    });

});
</script>
@endpush