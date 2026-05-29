<script>
    // 1. تعريف المتغير في النطاق العام (Global) خارج أي دالة
    var table;

  table = $('#schools-table').DataTable({
    processing: true,
    serverSide: true,
    responsive: false,
    scrollX: true,
    autoWidth: false,
    ajax: {
        url: "{{ route('admin.schools.index') }}",
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
        { data: 'silocode', name: 'silocode', orderable: true, searchable: true },
        { data: 'name', name: 'name' },
                { data: 'department_id', name: 'department_id' },
                { data: 'address', name: 'address' },
                { data: 'type', name: 'type' },

    ],
    language: { /* ... */ },
    dom: 'Bfrtip',
    buttons: [ /* ... */ ]
});
