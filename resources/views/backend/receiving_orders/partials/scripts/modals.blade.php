// حفظ شحنة جديدة (إذن استلام)
$('#submitBtn').click(function() {
    let btn = $(this);
    let originalHtml = btn.html();

    // تعطيل الزر فوراً لمنع الضغط المتكرر
    btn.html('<i class="fas fa-spinner fa-spin"></i> جاري الحفظ...').prop('disabled', true);

    // إرسال النموذج عبر AJAX
    $.ajax({
        url: "{{ route('admin.receiving_orders.store') }}",
        method: 'POST',
        data: new FormData($('#receivingForm')[0]),
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },

        success: function(response) {
            if (response.success === true || response.status === true) {
                
                Swal.fire({
                    icon: 'success',
                    title: 'تم!',
                    text: response.message || 'تم حفظ إذن الاستلام بنجاح',
                    timer: 2000,
                    showConfirmButton: false
                });

                // إغلاق المودال وإعادة تعيين النموذج
                $('#addReceivingOrderModal').modal('hide');
                $('#receivingForm')[0].reset();

                // تحديث جدول DataTable الخاص بأذونات الاستلام
                if (typeof table !== 'undefined' && $.fn.DataTable.isDataTable(table)) {
                    table.ajax.reload(null, false);
                } 
                else if ($.fn.DataTable.isDataTable('#receiving-orders-table')) {
                    $('#receiving-orders-table').DataTable().ajax.reload(null, false);
                }

            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'خطأ!',
                    html: response.message || 'حدث خطأ غير متوقع'
                });
            }
        },

        error: function(xhr) {
            let errorMsg = 'حدث خطأ أثناء الحفظ';
            if (xhr.status === 422) {
                // عرض أخطاء الـ Validation (بما فيها رقم التشغيلة)
                errorMsg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }

            Swal.fire({
                icon: 'error',
                title: 'خطأ!',
                html: errorMsg
            });
        },

        complete: function() {
            btn.html(originalHtml).prop('disabled', false);
        }
    });
});

// فتح مودال التعديل وجلب بيانات إذن الاستلام
$(document).on('click', '.edit-receiving-order', function() {
    let id = $(this).data('id');
    
    Swal.fire({ 
        title: 'جاري جلب البيانات...', 
        allowOutsideClick: false, 
        didOpen: () => Swal.showLoading() 
    });
    
    $.ajax({
        url: "{{ url('admin/receiving-orders') }}/" + id + "/edit",
        method: 'GET',
        success: function(data) {
            Swal.close();
            
            // تعبئة الحقول في مودال التعديل (تأكد من وجود هذه الـ IDs في مودال التعديل)
            $('#edit_id').val(data.id);
            $('#edit_document_number').val(data.document_number);
            $('#edit_supplier_id').val(data.supplier_id);
            $('#edit_warehouse_id').val(data.warehouse_id);
            $('#edit_product_id').val(data.product_id);
            $('#edit_batch_number').val(data.batch_number); // إضافة رقم التشغيلة هنا
            $('#edit_quantity').val(data.quantity);
            $('#edit_samples_quantity').val(data.samples_quantity || 0);
            $('#edit_arrival_time').val(data.arrival_time ? data.arrival_time.replace(' ', 'T') : '');
            $('#edit_departure_time').val(data.departure_time ? data.departure_time.replace(' ', 'T') : '');
            $('#edit_notes').val(data.notes || '');
            
            $('#editReceivingOrderModal').modal('show');
        },
        error: function() {
            Swal.close();
            Swal.fire('خطأ!', 'تعذر جلب البيانات', 'error');
        }
    });
});

// تنفيذ عملية التحديث
$('#btnUpdateReceivingOrder').click(function() {
    let id = $('#edit_id').val();
    let btn = $(this);
    let originalHtml = btn.html();
    
    btn.html('<i class="fas fa-spinner fa-spin"></i> جاري التحديث...').prop('disabled', true);
    
    let formData = new FormData($('#editReceivingOrderForm')[0]);
    formData.append('_method', 'PUT'); 

    $.ajax({
        url: "{{ url('admin/receiving-orders') }}/" + id,
        method: 'POST', 
        data: formData,
        processData: false,
        contentType: false,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function(response) {
            if (response.success) {
                Swal.fire('تم!', response.message, 'success');
                $('#editReceivingOrderModal').modal('hide');
                
                // تحديث الجدول
                if ($.fn.DataTable.isDataTable('#receiving-orders-table')) {
                    $('#receiving-orders-table').DataTable().ajax.reload(null, false);
                }
            } else {
                Swal.fire('خطأ!', response.message, 'error');
            }
        },
        error: function(xhr) {
            let errorMsg = xhr.responseJSON?.message || 'حدث خطأ أثناء التحديث';
            if(xhr.status === 422) {
                errorMsg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
            }
            Swal.fire('خطأ!', errorMsg, 'error');
        },
        complete: function() {
            btn.html(originalHtml).prop('disabled', false);
        }
    });
});

// حذف إذن استلام
$(document).on('click', '.delete-receiving-order', function() {
    let id = $(this).data('id');
    let docNum = $(this).data('number');
    
    Swal.fire({
        title: 'هل أنت متأكد؟',
        html: `هل تريد حذف إذن استلام رقم: <strong>${docNum}</strong>؟`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'نعم، احذفه',
        cancelButtonText: 'إلغاء'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({ title: 'جاري الحذف...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            $.ajax({
                url: "{{ url('admin/receiving-orders') }}/" + id,
                method: 'DELETE',
                data: { _token: "{{ csrf_token() }}" },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('تم الحذف!', response.message, 'success');
                        $('#receiving-orders-table').DataTable().ajax.reload(null, false);
                    } else {
                        Swal.fire('خطأ!', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    Swal.close(); 
                    Swal.fire('خطأ!', xhr.responseJSON?.message || 'حدث خطأ', 'error');
                }
            });
        }
    });
});

// تحسين مظهر البحث
$('.dataTables_filter input').addClass('form-control form-control-sm').attr('placeholder', '🔍 بحث سريع...');

$(document).on('click', '.view-report-copy', function() {
    // جلب بيانات الصف من DataTable
    var table = $('#receiving-orders-table').DataTable();
    var data = table.row($(this).parents('tr')).data();

    // تنسيق الأوقات باستخدام Moment.js (تأكد من تضمين المكتبة في مشروعك)
    var arrivalDate = moment(data.arrival_time_raw).format('YYYY-MM-DD');
    var arrivalTime = moment(data.arrival_time_raw).format('hh:mm A');
    var departureDate = moment(data.departure_time_raw).format('YYYY-MM-DD');
    var departureTime = moment(data.departure_time_raw).format('hh:mm A');

    // بناء النص المطلوب بالظبط
    var report = `1- رقم الإذن الورقي ${data.document_number}
2- مكان الوصول: ${data.warehouse_name}
3- ساعة وصول العربة: ${arrivalTime}
   تاريخ الوصول: ${arrivalDate}

4- تم استلام عدد (${data.quantity}) كرتونة بسكويت نوع (${data.product_name})
   وارد من مصنع (${data.supplier_name})

5- ساعة مغادرة العربة: ${departureTime}
   تاريخ المغادرة: ${departureDate}

6- ملاحظات:
${data.notes}
تم استلام عدد ${data.quantity_info} عينة`;

    // عرض النص في المودال
    $('#reportTextarea').val(report);
    $('#reportCopyModal').modal('show');
});

// تنفيذ عملية النسخ فعلياً
$('#copyFinalBtn').click(function() {
    var copyText = document.getElementById("reportTextarea");
    copyText.select();
    copyText.setSelectionRange(0, 99999); // للهواتف
    navigator.clipboard.writeText(copyText.value);
    
    // تنبيه بسيط
    $(this).html('<i class="fas fa-check"></i> تم النسخ!').removeClass('btn-success').addClass('btn-dark');
    setTimeout(() => {
        $(this).html('<i class="fas fa-copy"></i> نسخ التقرير الآن').removeClass('btn-dark').addClass('btn-success');
    }, 2000);
});