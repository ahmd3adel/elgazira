// حفظ محافظة جديدة
$('#btnSaveGovernorate').click(function() {
    let name = $('#name').val().trim();
    let code = $('#code').val().trim();
    
    if (!name || !code) return Swal.fire('تنبيه', 'يرجى إدخال اسم المحافظة وكودها', 'warning');
    
    let btn = $(this);
    let originalHtml = btn.html();
    
    // تعطيل الزر فوراً
    btn.html('<i class="fas fa-spinner fa-spin"></i> جاري الحفظ...').prop('disabled', true);
    
    $.ajax({
        url: "{{ route('admin.governorates.store') }}",
        method: 'POST',
        data: new FormData($('#addGovernorateForm')[0]),
        processData: false,
        contentType: false,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function(response) {
            if (response.success) {
                Swal.fire('تم!', response.message, 'success');
                $('#addGovernorateModal').modal('hide');
                $('#addGovernorateForm')[0].reset();
                
                // تحديث الجدول بأمان (Checking if table exists)
                if (typeof table !== 'undefined') {
                    table.ajax.reload(null, false); 
                } else {
                    // حل بديل إذا كان المتغير غير مرئي
                    $('#governorates-table').DataTable().ajax.reload(null, false);
                }
            } else {
                Swal.fire('خطأ!', response.message, 'error');
            }
        },
        error: function(xhr) {
            let errorMsg = xhr.responseJSON?.message || 'حدث خطأ أثناء الحفظ';
            if(xhr.status === 422) {
                errorMsg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
            }
            Swal.fire('خطأ!', errorMsg, 'error');
        },
        complete: function() {
            // هذا السطر سينفذ دائماً سواء نجح الطلب أو فشل
            btn.html(originalHtml).prop('disabled', false);
        }
    });
});

// عرض مودال تعديل المحافظة
// 1. فتح مودال التعديل وجلب البيانات (Edit)
$(document).on('click', '.edit-governorate', function() {
    let id = $(this).data('id');
    
    // إظهار مؤشر تحميل بسيط
    Swal.fire({ 
        title: 'جاري جلب البيانات...', 
        allowOutsideClick: false, 
        didOpen: () => Swal.showLoading() 
    });
    
    $.ajax({
        url: "{{ url('admin/governorates') }}/" + id + "/edit",
        method: 'GET',
        success: function(data) {
            Swal.close();
            
            // تعبئة الحقول في مودال التعديل (تأكد من مطابقة الـ IDs)
            $('#edit_id').val(data.id);
            $('#edit_name').val(data.name);
            $('#edit_code').val(data.code);
            $('#edit_manager_name').val(data.manager_name || '');
            $('#edit_manager_phone').val(data.manager_phone || '');
            $('#edit_status').val(data.status ? 1 : 0);
            $('#edit_notes').val(data.notes || '');
            
            // إظهار المودال
            $('#editGovernorateModal').modal('show');
        },
        error: function() {
            Swal.close();
            Swal.fire('خطأ!', 'تعذر جلب بيانات المحافظة', 'error');
        }
    });
});

// 2. تنفيذ عملية التحديث (Update)
$('#btnUpdateGovernorate').click(function() {
    let id = $('#edit_id').val();
    let name = $('#edit_name').val().trim();
    
    if (!name) return Swal.fire('تنبيه', 'يرجى إدخال اسم المحافظة', 'warning');
    
    let btn = $(this);
    let originalHtml = btn.html();
    
    // تغيير حالة الزر
    btn.html('<i class="fas fa-spinner fa-spin"></i> جاري التحديث...').prop('disabled', true);
    
    let formData = new FormData($('#editGovernorateForm')[0]);
    formData.append('_method', 'PUT'); // مهم جداً لارافل للتعرف على عملية التحديث

    $.ajax({
        url: "{{ url('admin/governorates') }}/" + id,
        method: 'POST', // نستخدم POST مع append _method PUT
        data: formData,
        processData: false,
        contentType: false,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function(response) {
            if (response.success) {
                Swal.fire('تم!', response.message, 'success');
                $('#editGovernorateModal').modal('hide');
                
                // تحديث الجدول بأمان
                if (typeof table !== 'undefined') {
                    table.ajax.reload(null, false);
                } else {
                    $('#governorates-table').DataTable().ajax.reload(null, false);
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
            // إعادة الزر لحالته الطبيعية دائماً
            btn.html(originalHtml).prop('disabled', false);
        }
    });
});

// تحديث بيانات المحافظة
{{-- $('#btnUpdateGovernorate').click(function() {
    let id = $('#edit_id').val();
    let name = $('#edit_name').val().trim();
    
    if (!name) return Swal.fire('تنبيه', 'يرجى إدخال اسم المحافظة', 'warning');
    
    let btn = $(this);
    let originalHtml = btn.html();
    btn.html('<i class="fas fa-spinner fa-spin"></i> جاري التحديث...').prop('disabled', true);
    
    let formData = new FormData($('#editGovernorateForm')[0]);
    formData.append('_method', 'PUT'); // لارافل يحتاج PUT للتحديث

    $.ajax({
        url: "{{ url('admin/governorates') }}/" + id,
        method: 'POST', // نستخدم POST مع _method PUT
        data: formData,
        processData: false,
        contentType: false,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function(response) {
            if (response.success) {
                Swal.fire('تم!', response.message, 'success');
                $('#editGovernorateModal').modal('hide');
                table.ajax.reload();
            } else {
                Swal.fire('خطأ!', response.message, 'error');
            }
        },
        error: function(xhr) {
            Swal.fire('خطأ!', xhr.responseJSON?.message || 'حدث خطأ', 'error');
        },
        complete: function() {
            btn.html(originalHtml).prop('disabled', false);
        }
    });
}); --}}

// حذف محافظة
$(document).on('click', '.delete-governorate', function() {
    let id = $(this).data('id');
    let name = $(this).data('name');
    
    Swal.fire({
        title: 'هل أنت متأكد؟',
        html: `هل تريد حذف محافظة <strong>${name}</strong>؟`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'نعم، احذفها',
        cancelButtonText: 'إلغاء'
    }).then((result) => {
        if (result.isConfirmed) {
            // إظهار مؤشر التحميل
            Swal.fire({ 
                title: 'جاري الحذف...', 
                allowOutsideClick: false, 
                didOpen: () => Swal.showLoading() 
            });

            $.ajax({
                url: "{{ url('admin/governorates') }}/" + id,
                method: 'DELETE',
                data: { 
                    _token: "{{ csrf_token() }}" 
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('تم الحذف!', response.message, 'success');
                        
                        // التحديث الآمن للجدول
                        if (typeof table !== 'undefined') {
                            table.ajax.reload(null, false);
                        } else {
                            // استخدام المعرف المباشر للجدول كخطة بديلة
                            $('#governorates-table').DataTable().ajax.reload(null, false);
                        }
                    } else {
                        Swal.fire('خطأ!', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    // إغلاق مؤشر التحميل قبل إظهار الخطأ
                    Swal.close(); 
                    let errorMsg = xhr.responseJSON?.message || 'حدث خطأ أثناء الحذف';
                    Swal.fire('خطأ!', errorMsg, 'error');
                }
            });
        }
    });
});

// إعادة تعيين النماذج عند الإغلاق
$('#addGovernorateModal').on('hidden.bs.modal', function() {
    $('#addGovernorateForm')[0].reset();
});

// تحسين مظهر صندوق البحث في DataTable
$('.dataTables_filter input').addClass('form-control').attr('placeholder', '🔍 ابحث عن محافظة...');

$(function () {
    $('#inventoryTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        autoWidth: false,
        ajax: "{{ route('admin.inventories.index') }}",
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'product_name', name: 'product_name'},
            {data: 'warehouse_name', name: 'warehouse_name'},
            {data: 'quantity', name: 'quantity'},
            {{-- {data: 'last_movement', name: 'last_movement'}, --}}
            {{-- {
                // عمود إضافي "Status" يُستنتج من الكمية لإعطاء لمحة سريعة
                data: 'quantity',
                render: function(data, type, row) {
                    var text = parseInt(data);
                    if (text <= 0) {
                        return '<span class="badge badge-pill badge-danger">نفدت الكمية</span>';
                    } else if (text <= 10) {
                        return '<span class="badge badge-pill badge-warning">طلب توريد</span>';
                    } else {
                        return '<span class="badge badge-pill badge-success">متوفر</span>';
                    }
                }
            } --}}
        ],
        language: {
            "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/ar.json"
        },
        dom: 'Bfrtip', // لإضافة أزرار التصدير (اختياري)
        buttons: ["copy", "csv", "excel", "pdf", "print", "colvis"]
    });
});