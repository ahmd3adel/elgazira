<script>
$(document).ready(function() {
    let itemIndex = 1;
    var table;

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
    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false },
    { data: 'order_date', name: 'order_date' },
    { data: 'department_name', name: 'department_name' },
    { data: 'entity_type', name: 'entity_type' },
    @foreach($products as $product)
    { 
        data: 'prod_{{ $product->id }}', 
        render: function(data) { return data || 0; }
    },
    @endforeach
    { data: 'total_qty', name: 'total_qty' },
    { 
        data: null,  // تغيير من 'action' إلى null
        name: 'action', 
        orderable: false, 
        searchable: false,
        defaultContent: '',  // إضافة هذا السطر
        render: function(data, type, row) {
            // يمكنك إضافة أزرار الإجراءات هنا إذا أردت
            return '<div class="btn-group btn-group-sm" role="group">' +
                   '<button type="button" class="btn btn-info btn-sm edit-btn" data-id="' + row.id + '">' +
                   '<i class="fas fa-edit"></i> تعديل</button> ' +
                   '<button type="button" class="btn btn-danger btn-sm delete-btn" data-id="' + row.id + '">' +
                   '<i class="fas fa-trash"></i> حذف</button>' +
                   '</div>';
        }
    }
],
        language: { "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Arabic.json" },
        dom: 'Bfrtip',
        buttons: [
            { extend: 'excel', text: '<i class="fas fa-file-excel"></i> إكسيل', className: 'btn-success' },
            { extend: 'print', text: '<i class="fas fa-print"></i> طباعة', className: 'btn-info' }
        ]
    });

    // 2. وظيفة إضافة سطر صنف جديد في المودال
    function initSelect2(selector) {
        $(selector).select2({
            dropdownParent: $('#addDistributionAllocationsModal'),
            placeholder: "اختر المنتج",
            width: '100%'
        });
    }

    initSelect2('.product-select');

    $('#addItem').click(function() {
        let newRow = `
            <tr class="item-row">
                <td>
                    <select name="items[${itemIndex}][product_id]" class="form-control product-select" required>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }} ({{ number_format($product->price) }} ج.م)</option>
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
        initSelect2(`.product-select:last`); // تهيئة آخر عنصر فقط
        itemIndex++;
    });

    // 3. حذف سطر
    $(document).on('click', '.remove-row', function() {
        $(this).closest('.item-row').remove();
    });

    // 4. حفظ النموذج عبر Ajax
    $('#addDistributionForm').on('submit', function(e) {
        e.preventDefault();
        let submitBtn = $(this).find('button[type="submit"]');
        let originalText = submitBtn.html();

        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> جاري الحفظ...');

        $.ajax({
            url: "{{ route('admin.department_allocations.store') }}", // ✅ لازم يكون كدة
            type: "POST",
            
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('#addDistributionAllocationsModal').modal('hide');
                    $('#addDistributionForm')[0].reset();
                    table.ajax.reload();
                    toastr.success('تم التسجيل بنجاح');
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr) {
                toastr.error('خطأ في البيانات المرسلة');
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    // 5. دوال مساعدة للفلترة والإجماليات
    function hasActiveFilters() {
        return $('#from_date').val() || $('#to_date').val() || ($('#department_filter').val() && $('#department_filter').val().length > 0);
    }

    function displayTotalsRow(totals) {
        $('.grand-total-row').remove();
        let totalRow = '<tr class="grand-total-row table-success font-weight-bold">';
        totalRow += '<td colspan="4" class="text-center">الإجمالي العام</td>';
        
        @foreach($products as $product)
            totalRow += `<td class="text-center">${(totals.product_totals[{{ $product->id }}] || 0).toLocaleString()}</td>`;
        @endforeach

        totalRow += `<td class="text-center">${(totals.total_quantity || 0).toLocaleString()}</td><td></td></tr>`;
        $('#distribution_allocations_table tbody').append(totalRow);
    }

    // إعادة تعيين الفلاتر
    $('#reset_button').click(function() {
        $('.filter-input').val('');
        $('#department_filter').val(null).trigger('change');
        table.ajax.reload();
    });

    $('#from_date, #to_date, #department_filter').on('change', function() {
        table.ajax.reload();
    });
});