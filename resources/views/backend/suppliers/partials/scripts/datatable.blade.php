<script>
    // 1. تعريف المتغير في النطاق العام (Global) خارج أي دالة
    var table;

  table = $('#suppliers-table').DataTable({
    processing: true,
    serverSide: true,
    responsive: false,
    scrollX: true,
    autoWidth: false,
    ajax: {
        url: "{{ route('admin.suppliers.index') }}",
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
        $(window).on('resize', function () {
            table.columns.adjust();
        });
        
        // إذا كان لديك Menu جانبي يفتح ويغلق (AdminLTE)
        $('.nav-link').on('click', function() {
            setTimeout(function() {
                table.columns.adjust();
            }, 300);
        });
    },
    columns: [
        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
        { data: 'code', name: 'code', className: 'text-center' },
        { data: 'name', name: 'name' },
        { data: 'products_names', name: 'products_names' },
        // { data: 'contact_person', name: 'contact_person', defaultContent: '<i class="text-muted">غير محدد</i>' },
        // { data: 'phone', name: 'phone' },
    ],
    // باقي الكود (language, dom, buttons) يبقى كما هو...
    language: { /* ... */ },
    dom: 'Bfrtip',
    buttons: [ /* ... */ ]
});
