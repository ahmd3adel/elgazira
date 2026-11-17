// تحميل الأقسام الرئيسية
function loadParentSelects() {
    $.ajax({
        url: "{{ route('admin.categories.parents') }}",
        method: 'GET',
        success: function(response) {
            let options = '<option value="">-- قسم رئيسي (بدون أب) --</option>';
            if (response.success && response.data) {
                $.each(response.data, function(i, cat) {
                    options += `<option value="${cat.id}">${cat.text}</option>`;
                });
            }
            $('.select2-parent, .select2-parent-edit').html(options).trigger('change');
        },
        error: function() {
            console.error('خطأ في تحميل الأقسام');
        }
    });
}

// تهيئة Select2
$('.select2-parent, .select2-parent-edit').select2({
    theme: 'bootstrap4',
    placeholder: 'اختر القسم الأب',
    allowClear: true,
    width: '100%',
    dir: "rtl"
});

// عرض الأيقونات
renderIcons('.icon-selector', 'fa-briefcase');
renderIcons('.icon-selector-edit', 'fa-briefcase');

// اختيار الأيقونة
$(document).on('click', '.icon-selector .icon-item', function() {
    $('.icon-selector .icon-item').removeClass('active');
    $(this).addClass('active');
    $('#selectedIcon').val($(this).data('icon'));
});

$(document).on('click', '.icon-selector-edit .icon-item', function() {
    $('.icon-selector-edit .icon-item').removeClass('active');
    $(this).addClass('active');
    $('#edit_selectedIcon').val($(this).data('icon'));
});

// Slug تلقائي
$('#name_ar, #edit_name_ar').on('keyup', function() {
    let target = $(this).attr('id') === 'name_ar' ? '#slug_ar' : '#edit_slug_ar';
    if ($(target).val() === '') $(target).val(cleanSlug($(this).val()));
});

$('#name_en, #edit_name_en').on('keyup', function() {
    let target = $(this).attr('id') === 'name_en' ? '#slug_en' : '#edit_slug_en';
    if ($(target).val() === '') $(target).val(cleanSlug($(this).val()));
});

// حفظ جديد
$('#btnSaveProfession').click(function() {
    let nameAr = $('#name_ar').val().trim();
    if (!nameAr) return Swal.fire('تنبيه', 'يرجى إدخال اسم المهنة', 'warning');
    
    let btn = $(this);
    let originalHtml = btn.html();
    btn.html('<i class="fas fa-spinner fa-spin"></i> جاري الحفظ...').prop('disabled', true);
    
    $.ajax({
        url: "{{ route('admin.categories.store') }}",
        method: 'POST',
        data: new FormData($('#addProfessionForm')[0]),
        processData: false,
        contentType: false,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function(response) {
            if (response.success) {
                Swal.fire('تم!', response.message, 'success');
                $('#addProfessionModal').modal('hide');
                $('#addProfessionForm')[0].reset();
                table.ajax.reload();
                loadParentSelects();
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
});

// عرض مودال التعديل
$(document).on('click', '.edit-profession', function() {
    let id = $(this).data('id');
    Swal.fire({ title: 'جاري التحميل...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    
    $.ajax({
        url: "{{ url('admin/categories') }}/" + id,
        success: function(data) {
            Swal.close();
            $('#edit_id').val(data.id);
            $('#edit_name_ar').val(data.name_ar);
            $('#edit_name_en').val(data.name_en || '');
            $('#edit_slug_ar').val(data.slug_ar || '');
            $('#edit_slug_en').val(data.slug_en || '');
            $('#edit_parent_id').val(data.parent_id || '').trigger('change');
            $('#edit_is_active').val(data.is_active ? 1 : 0);
            $('#edit_order').val(data.order || 0);
            $('#edit_icon_color').val(data.icon_color || '#0066cc');
            $('#edit_description').val(data.description || '');
            $('#edit_selectedIcon').val(data.icon || 'fa-briefcase');
            $(`.icon-selector-edit .icon-item[data-icon="${data.icon || 'fa-briefcase'}"]`).addClass('active');
            $('#editProfessionModal').modal('show');
        },
        error: function() {
            Swal.close();
            Swal.fire('خطأ!', 'حدث خطأ أثناء تحميل البيانات', 'error');
        }
    });
});

// تحديث
$('#btnUpdateProfession').click(function() {
    let id = $('#edit_id').val();
    let nameAr = $('#edit_name_ar').val().trim();
    if (!nameAr) return Swal.fire('تنبيه', 'يرجى إدخال اسم المهنة', 'warning');
    
    let btn = $(this);
    let originalHtml = btn.html();
    btn.html('<i class="fas fa-spinner fa-spin"></i> جاري التحديث...').prop('disabled', true);
    
    let formData = new FormData($('#editProfessionForm')[0]);
    $.ajax({
        url: "{{ url('admin/categories') }}/" + id,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'X-HTTP-Method-Override': 'PUT'
        },
        success: function(response) {
            if (response.success) {
                Swal.fire('تم!', response.message, 'success');
                $('#editProfessionModal').modal('hide');
                table.ajax.reload();
                loadParentSelects();
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
});

// حذف
$(document).on('click', '.delete-profession', function() {
    let id = $(this).data('id');
    let name = $(this).data('name');
    
    Swal.fire({
        title: 'هل أنت متأكد؟',
        html: `هل تريد حذف المهنة <strong>${name}</strong>؟`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'نعم، احذفها',
        cancelButtonText: 'إلغاء'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({ title: 'جاري الحذف...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            $.ajax({
                url: "{{ url('admin/categories') }}/" + id,
                method: 'DELETE',
                data: { _token: "{{ csrf_token() }}" },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('تم الحذف!', response.message, 'success');
                        table.ajax.reload();
                        loadParentSelects();
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

// إعادة تعيين النماذج
$('#addProfessionModal').on('hidden.bs.modal', function() {
    $('#addProfessionForm')[0].reset();
    $('#selectedIcon').val('fa-briefcase');
    $('.icon-selector .icon-item').removeClass('active').first().addClass('active');
    $('#parent_id').val('').trigger('change');
});

$('#editProfessionModal').on('hidden.bs.modal', function() {
    $('#editProfessionForm')[0].reset();
    $('#edit_parent_id').val('').trigger('change');
});

loadParentSelects();
$('.dataTables_filter input').addClass('form-control').attr('placeholder', '🔍 ابحث هنا...');