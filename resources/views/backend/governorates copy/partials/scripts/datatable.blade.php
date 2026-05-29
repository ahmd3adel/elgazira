<script>
    // 1. تعريف المتغير في النطاق العام (Global) خارج أي دالة
    var table;

  table = $('#governorates-table').DataTable({
    processing: true,
    serverSide: true,
    responsive: false,
    scrollX: true,
    autoWidth: false,
    ajax: {
        url: "{{ route('admin.governorates.index') }}",
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
        { data: 'manager_name', name: 'manager_name', defaultContent: '<i class="text-muted">غير محدد</i>' },
        { data: 'manager_phone', name: 'manager_phone' },
        { data: 'status', name: 'status', className: 'text-center' },
        { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' },
    ],
    // باقي الكود (language, dom, buttons) يبقى كما هو...
    language: { /* ... */ },
    dom: 'Bfrtip',
    buttons: [ /* ... */ ]
});
