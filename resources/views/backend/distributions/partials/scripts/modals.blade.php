// عند تغيير الإدارة
$('#department_id').on('change', function() {
    let departmentId = $(this).val();
    let schoolSelect = $('select[name="school_id"]');
    
    // تفريغ القائمة الحالية
    schoolSelect.empty();
    schoolSelect.append('<option value="">جاري تحميل المدارس...</option>');
    
    if (departmentId) {
        $.ajax({
            url: "{{ route('admin.getSchoolsByDepartment') }}",
            type: "GET",
            data: { department_id: departmentId },
            success: function(response) {
                schoolSelect.empty();
                schoolSelect.append('<option value="">اختر المدرسة</option>');
                
                if (response.schools && response.schools.length > 0) {
                    $.each(response.schools, function(key, school) {
                        schoolSelect.append(`<option value="${school.id}">${school.name}</option>`);
                    });
                } else {
                    schoolSelect.append('<option value="">لا توجد مدارس في هذه الإدارة</option>');
                }
                
                // تحديث select2
                schoolSelect.trigger('change');
            },
            error: function() {
                schoolSelect.empty();
                schoolSelect.append('<option value="">حدث خطأ في تحميل المدارس</option>');
            }
        });
    } else {
        schoolSelect.empty();
        schoolSelect.append('<option value="">اختر المدرسة أولاً</option>');
    }
});