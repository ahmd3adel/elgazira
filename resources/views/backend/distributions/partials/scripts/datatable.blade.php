<script>
    // 1. تعريف المتغير في النطاق العام
    var table;

    $(function() {
        table = $('#distribution-orders-table').DataTable({
            processing: true,
            serverSide: true,
            responsive: false,
            scrollX: true,
            autoWidth: false,
            ajax: {
                // تأكد من صحة اسم الراوت الخاص بالمنصرف
                url: "{{ route('admin.distribution_orders.index') }}",
                type: "GET",
            },
            // --- الإضافة السحرية لحل مشكلة المحاذاة ---
            drawCallback: function(settings) {
                setTimeout(function() {
                    table.columns.adjust();
                }, 10);
            },
            // ------------------------------------------
            initComplete: function() {
                // تعديل العرض عند تغيير حجم النافذة
                $(window).on('resize', function () {
                    table.columns.adjust();
                });
                
                // إذا كان لديك Menu جانبي يفتح ويغلق (AdminLTE)
                $('[data-widget="pushmenu"]').on('click', function() {
                    setTimeout(function() {
                        table.columns.adjust();
                    }, 300);
                });
            },
columns: [
    { data: 'DT_RowIndex', name: 'DT_RowIndex' , orderable: false,},
                { data: 'order_date', name: 'order_date' },  // ✅ غيرت من distribution_date لـ order_date
    { data: 'school_name', name: 'school_name' },
    { data: 'department_name', name: 'department_name' },

    // أعمدة المنتجات الديناميكية
    @foreach($products as $product)
    { data: 'prod_{{ $product->id }}', name: 'prod_{{ $product->id }}', orderable: false, searchable: false },
    @endforeach

    { data: 'total_qty', name: 'total_qty' },
    { data: 'user_name', name: 'user_name', defaultContent: '---' },
    { data: 'action', name: 'action', orderable: false }
],
            language: {
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Arabic.json" // لغة عربية
            },
            dom: 'Bfrtip',
            buttons: [
                { extend: 'excel', text: '<i class="fas fa-file-excel"></i> إكسيل', className: 'btn-success' },
                { extend: 'print', text: '<i class="fas fa-print"></i> طباعة', className: 'btn-info' }
            ]
        });
    });


    $(document).ready(function() {
    let itemIndex = 1; // عداد للأسطر الجديدة

    // إضافة سطر جديد
    $('#addItem').click(function() {
        let row = `
            <tr class="item-row">
                <td>
                    <select name="items[${itemIndex}][product_id]" class="form-control product-select" required>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}">
                                {{ $product->name }} ({{ number_format($product->price) }} ج.م)
                            </option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <input type="number" name="items[${itemIndex}][quantity]" class="form-control text-center quantity-input" placeholder="0" min="1" step="1" required>
                </td>
                <td>
                    <input type="text" class="form-control text-center" value="كرتونة" readonly disabled>
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm remove-row">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>`;
        
        $('#itemsContainer').append(row);
        itemIndex++;
    });

    // حذف سطر
    $(document).on('click', '.remove-row', function() {
        $(this).closest('tr').remove();
    });
});

$('#addDistributionForm').on('submit', function(e) {
    e.preventDefault();
    
    let form = $(this);
    let submitBtn = form.find('button[type="submit"]');
    
    // التحقق من وجود كميات قبل الإرسال
    let hasQuantity = false;
    $('.quantity-input').each(function() {
        if ($(this).val() > 0) hasQuantity = true;
    });
    
    if (!hasQuantity) {
        toastr.error('يرجى إدخال كميات للأصناف');
        return;
    }
    
    // تغيير حالة الزر
    submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> جاري الحفظ...');
    
    // ✅ استخدم FormData بدلاً من serialize
    let formData = new FormData(this);
    
        
        $.ajax({
            url: "{{ route('admin.distribution_orders.store') }}",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        success: function(response) {
            if (response.success) {
                $('#addDistributionModal').modal('hide');
                form[0].reset();
                
                // إعادة تعيين الجدول لسطر واحد فقط
                $('#itemsContainer').html(`
                    <tr class="item-row">
                        <td>
                            <select name="items[0][product_id]" class="form-control product-select" required>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }} ({{ number_format($product->price) }} ج.م)</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="number" name="items[0][quantity]" class="form-control text-center quantity-input" placeholder="0" min="0" step="1" required>
                        </td>
                        <td>
                            <input type="text" class="form-control text-center" value="كرتونة" readonly disabled>
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm remove-row" disabled>
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `);
                
                // تحديث الجدول
                if (typeof table !== 'undefined') {
                    table.ajax.reload();
                }
                
                toastr.success('تم تسجيل إذن الصرف وخصم الكميات من المخزن بنجاح');
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
            submitBtn.prop('disabled', false).html('<i class="fas fa-save"></i> اعتماد الصرف والخصم');
        }
    });
});