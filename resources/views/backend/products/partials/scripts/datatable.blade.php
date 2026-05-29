<script>
    // 1. تعريف المتغير في النطاق العام (Global) خارج أي دالة
    var table;

var table = $('#products-table').DataTable({
    processing: true,
    serverSide: true,
    responsive: false,
    scrollX: true,
    autoWidth: false,
    searchDelay: 500,

    ajax: {
        url: "{{ route('admin.products.index') }}",
        type: "GET",
        error: function(xhr) {
            console.log(xhr.responseText);
            alert('حصل خطأ أثناء تحميل البيانات');
        }
    },

    order: [[1, 'asc']],

    drawCallback: function() {
        setTimeout(function() {
            table.columns.adjust();
        }, 10);
    },

    initComplete: function() {
        $(window).on('resize', function () {
            table.columns.adjust();
        });

        $('.nav-link').on('click', function() {
            setTimeout(function() {
                table.columns.adjust();
            }, 300);
        });

        $('.dataTables_filter input')
            .attr('placeholder', '🔍 ابحث هنا...')
            .css('width', '250px');
    },

    columns: [
        { data: 'DT_RowIndex', name: 'DT_RowIndex' },
        { data: 'sku', name: 'sku', className: 'text-center' },
        { data: 'name', name: 'name' },
        { data: 'conversion_factor', name: 'conversion_factor' },
        { data: 'suppliers_names', name: 'suppliers_names' },
        { 
            data: 'expiry_duration',
            name: 'expiry_duration',
            render: function(data) {
                if (!data) return '-';
                if (data == 1) return 'شهر';
                if (data == 2) return 'شهرين';
                if (data >= 12) return (data / 12) + ' سنة';
                return data + ' شهور';
            }
        },
    ],

    columnDefs: [
        { targets: 0, orderable: false, searchable: false },
        { targets: '_all', className: 'align-middle' }
    ],

    language: {
        "sProcessing": "جاري المعالجة...",
        "sSearch": "بحث:",
        "sLengthMenu": "أظهر _MENU_",
        "sInfo": "عرض _START_ إلى _END_ من _TOTAL_",
        "sZeroRecords": "لا توجد بيانات",
        "oPaginate": {
            "sNext": "التالي",
            "sPrevious": "السابق"
        }
    },

    dom: 'Bfrtip',
    buttons: [
        'copy', 'excel', 'print'
    ]
});
