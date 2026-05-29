<script>
    // 1. تعريف المتغير في النطاق العام (Global) خارج أي دالة
    var table;

    table = $('#warehouses-table').DataTable({
        processing: true,
        serverSide: true,
        ordering: true,
        responsive: false,
        scrollX: true,
        autoWidth: false,
        ajax: {
            url: "{{ route('admin.warehouses.index') }}",
            type: "GET",
        },
        // --- الإضافة السحرية لحل مشكلة المحاذاة ---
        drawCallback: function(settings) {
            // نستخدم setTimeout لضمان أن الجدول قد تم رنده بالكامل في الـ DOM
            setTimeout(function() {
                table.columns.adjust();
            }, 10);
        },
        // ------------------------------------------
        initComplete: function() {
            // تعديل العرض عند تغيير حجم النافذة
            $(window).on('resize', function() {
                table.columns.adjust();
            });

            // إذا كان لديك Menu جانبي يفتح ويغلق (AdminLTE)
            $('.nav-link').on('click', function() {
                setTimeout(function() {
                    table.columns.adjust();
                }, 300);
            });
        },
        columns: [{
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                orderable: false,
                searchable: false
            },
            {
                data: 'name',
                name: 'name',
                orderable: true
            },
            {
                data: 'code',
                name: 'code'
            },



            // عمود الحالة (رئيسي/فرعي) - إذا كنت لا تزال تريده كعمود منفصل
            {
                data: null,
                name: 'governorate_name', // اسم الحقل الأساسي للبحث
                className: 'text-center',
                render: function(data, type, row) {
                    if (row.type === 'main') {
                        // الحالة 1: مخزن رئيسي -> نعرض المحافظة
                        return `
                <div class="d-flex flex-column">
                    <span class="text-primary font-weight-bold">
                        <i class="fas fa-map-marker-alt"></i> ${row.governorate_name || 'غير محدد'}
                    </span>
                    <small class="text-muted">المحافظة</small>
                </div>`;
                    } else {
    // الحالة 2: فرعي أو نقطة توزيع -> نعرض المخزن الأب
    let parent = row.parent_name && row.parent_name !== '---' ? row.parent_name : 'غير محدد';
    
    // نحدد النص الذي سيظهر بناءً على نوع المخزن
    let typeLabel = '';
    if (row.type === 'sub') {
        typeLabel = 'مخزن فرعي';
    } else if (row.type === 'dispatch_point') {
        typeLabel = 'نقطة توزيع';
    } else {
        typeLabel = 'مخزن تابع'; // حالة احتياطية
    }

    return `
        <div class="d-flex flex-column">
            <span class="text-info font-weight-bold">
                <i class="fas fa-warehouse"></i> ${parent}
            </span>
            <small class="text-muted">${typeLabel}</small>
        </div>`;
}
                }
            },

            {
                data: 'manager_name',
                name: 'manager_name',
                defaultContent: '<i class="text-muted">غير محدد</i>'
            },
            {
                data: 'manager_phone',
                name: 'manager_phone'
            },
            {
                data: 'status',
                name: 'status',
                className: 'text-center'
            },

        ],
        // باقي الكود (language, dom, buttons) يبقى كما هو...
       language: {
    "sProcessing":   "جاري التحميل...",
    "sLengthMenu":   "عرض _MENU_ سجلات",
    "sZeroRecords":  "لم يعثر على أية سجلات",
    "sInfo":         "إظهار _START_ إلى _END_ من أصل _TOTAL_ سجل",
    "sInfoEmpty":    "يعرض 0 إلى 0 من أصل 0 سجل",
    "sInfoFiltered": "(منتقاة من مجموع _MAX_ سجل)",
    "sInfoPostFix":  "",
    "sSearch":       "بحث:",
    "sUrl":          "",
    "oPaginate": {
        "sFirst":    "الأول",
        "sPrevious": "السابق",
        "sNext":     "التالي",
        "sLast":     "الأخير"
    },
    "buttons": {
        "copy": "نسخ",
        "excel": "إكسل",
        "pdf": "PDF",
        "print": "طباعة",
        "colvis": "الأعمدة الظاهرة"
    }
},
        dom: 'Bfrtip',
        buttons: [ /* ... */ ]
    });
