<script>
    // 1. تعريف المتغير في النطاق العام (Global) خارج أي دالة
    var table;

  table = $('#receiving-orders-table').DataTable({
    processing: true,
    serverSide: true,
    responsive: false,
    scrollX: true,
    autoWidth: false,
    ajax: {
        url: "{{ route('admin.receiving_orders.index') }}",
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
    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, width: '50px' },
    { data: 'date', name: 'date' }, 
    { data: 'document_number', name: 'document_number' },
    { data: 'supplier_name', name: 'supplier_name' },
    { data: 'product_name', name: 'product.name' }, // تم تعديله ليطابق التعديل في الـ Controller
    { data: 'warehouse_name', name: 'warehouse.name' },
    { data: 'quantity', name: 'quantity' },
    { data: 'quantity_info', name: 'quantity_info', title: 'كمية العينات' }, // توضيح المسمى
    { data: 'action', name: 'action', orderable: false, searchable: false, width: '120px' },
],
    // باقي الكود (language, dom, buttons) يبقى كما هو...
    language: { /* ... */ },
    dom: 'Bfrtip',
    buttons: [ /* ... */ ]
});
