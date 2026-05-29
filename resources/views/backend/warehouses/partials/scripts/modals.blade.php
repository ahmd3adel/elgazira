$('#btnSaveWarehouse').click(function() {
    let name = $('#name').val().trim();
    let code = $('#code').val().trim();
    
    {{-- if (!name || !code) return Swal.fire('تنبيه', 'يرجى إدخال اسم المحافظة وكودها', 'warning'); --}}
    
    let btn = $(this);
    let originalHtml = btn.html();
    
    // تعطيل الزر فوراً
    btn.html('<i class="fas fa-spinner fa-spin"></i> جاري الحفظ...').prop('disabled', true);
    
    $.ajax({
        url: "{{ route('admin.warehouses.store') }}",
        method: 'POST',
        data: new FormData($('#addWarehouseForm')[0]),
        processData: false,
        contentType: false,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function(response) {
            if (response.success) {
                Swal.fire('تم!', response.message, 'success');
                $('#addWarehouseForm').modal('hide');
                $('#addWarehouseForm')[0].reset();
                
                // تحديث الجدول بأمان (Checking if table exists)
                if (typeof table !== 'undefined') {
                    table.ajax.reload(null, false); 
                } else {
                    // حل بديل إذا كان المتغير غير مرئي
                    $('#warehouses-table').DataTable().ajax.reload(null, false);
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
        url: "{{ url('admin/warehouses') }}/" + id,
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
$('#addWarehouseModal').on('hidden.bs.modal', function() {
    $('#addWarehouseForm')[0].reset();
});

// تحسين مظهر صندوق البحث في DataTable
$('.dataTables_filter input').addClass('form-control').attr('placeholder', '🔍 ابحث عن محافظة...');


$(document).ready(function() {
    // مراقبة التغيير في قائمة "نوع المخزن"
    $('#warehouse_type').on('change', function() {
        let type = $(this).val();

        if (type === 'main') {
            // إذا كان رئيسي: اظهر المحافظة وأخفِ المخزن الأب
            $('#gov_group').fadeIn();
            $('#parent_group').fadeOut();
            
            // تصفير قيمة المخزن الأب منعاً للتداخل
            $('#parent_id').val('').trigger('change');
            
            // جعل المحافظة مطلوبة والمخزن الأب اختياري
            $('#governorate_id').prop('required', true);
            $('#parent_id').prop('required', false);
        } else {
            // إذا كان فرعي أو نقطة توزيع: اظهر المخزن الأب وأخفِ المحافظة
            $('#gov_group').fadeOut();
            $('#parent_group').fadeIn();
            
            // تصفير قيمة المحافظة
            $('#governorate_id').val('').trigger('change');
            
            // جعل المخزن الأب مطلوباً والمحافظة اختيارية
            $('#parent_id').prop('required', true);
            $('#governorate_id').prop('required', false);
        }
    });

    // تنفيذ الكود مرة واحدة عند فتح المودال للتأكد من الحالة الافتراضية
    $('#addWarehouseModal').on('shown.bs.modal', function () {
        $('#warehouse_type').trigger('change');
    });
});


$('.select2').select2({
    theme: 'bootstrap4',
    dropdownParent: $('#addWarehouseModal')
});

$(document).on('click', '.delete-warehouse', function (e) {
    e.preventDefault(); // منع أي سلوك افتراضي للزر

    var id = $(this).data('id');
    var name = $(this).data('name');
    var url = "{{ route('admin.warehouses.destroy', ':id') }}";
    url = url.replace(':id', id);

    // 1. إظهار رسالة التأكيد الجميلة
    Swal.fire({
        title: 'هل أنت متأكد؟',
        text: "سيتم حذف مخزن (" + name + ") نهائياً!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3d3d3d',
        confirmButtonText: 'نعم، احذف الآن!',
        cancelButtonText: 'إلغاء',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // 2. إذا ضغط "نعم"، نرسل طلب Ajax
            $.ajax({
                url: url,
                type: 'POST', // نستخدم POST مع محاكاة DELETE
                data: {
                    _method: 'DELETE',
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
                    if (response.success) {
                        // 3. رسالة نجاح بدون ريفريش
                        Swal.fire({
                            title: 'تم الحذف!',
                            text: response.message,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        });

                        // 4. تحديث الجدول تلقائياً (تأكد أن اسم المتغير table)
                        if (typeof table !== 'undefined') {
                            table.ajax.reload(null, false); // false تحافظ على الصفحة الحالية
                        } else {
                            location.reload(); // حل احتياطي لو المتغير غير معروف
                        }
                    }
                },
                error: function (xhr) {
                    let msg = xhr.responseJSON ? xhr.responseJSON.message : 'حدث خطأ أثناء الحذف';
                    Swal.fire('خطأ!', msg, 'error');
                }
            });
        }
    });
});