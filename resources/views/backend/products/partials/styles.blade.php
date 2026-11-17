<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap4.min.css">
<!-- Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.2/dist/select2-bootstrap4.min.css" rel="stylesheet">
<!-- SweetAlert2 -->
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<!-- Font Awesome 6 -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    * { font-family: 'Cairo', sans-serif; }
    body { background-color: #f8f9fc; }
    
    .table thead th {
        background-color: #f8f9fc;
        border-bottom: 2px solid #dee2e6;
        color: #4e73df;
        font-weight: 600;
    }
    
    .icon-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        transition: transform 0.2s;
    }
    
    .icon-circle:hover { transform: scale(1.1); }
    
    .icon-selector .icon-item,
    .icon-selector-edit .icon-item {
        cursor: pointer;
        padding: 10px;
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        transition: all 0.2s ease;
        text-align: center;
    }
    
    .icon-selector .icon-item:hover,
    .icon-selector-edit .icon-item:hover {
        border-color: #007bff;
        background-color: #e7f1ff;
    }
    
    .icon-selector .icon-item.active,
    .icon-selector-edit .icon-item.active {
        border-color: #007bff;
        background-color: #007bff20;
    }
    
    .card { border-radius: 10px; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15); }
    
    @media (max-width: 768px) {
        .table-responsive { overflow-x: auto; }
        .btn-group { display: flex; gap: 5px; }
    }
    
    .badge { font-size: 12px; padding: 5px 10px; border-radius: 20px; }
    .modal-header .close { margin: -1rem auto -1rem -1rem !important; }
    .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered { text-align: right; }

    @media (max-width: 992px) {
    .table-responsive {
        border: 0;
        margin-bottom: 0;
        /* إضافة سكرول ناعم للموبايل */
        overflow-x: auto;
        -webkit-overflow-scrolling: touch; 
    }
    
    /* منع التفاف النص في الخلايا لضمان تمدد الجدول وعمل السكرول */
    .table-responsive table td, 
    .table-responsive table th {
        white-space: nowrap;
    }
}

/* تحسين شكل شريط السكرول (اختياري) */
.table-responsive::-webkit-scrollbar {
    height: 6px;
}
.table-responsive::-webkit-scrollbar-thumb {
    background: #4e73df50;
    border-radius: 10px;
}
.table-responsive::-webkit-scrollbar-thumb:hover {
    background: #4e73df;
}
/* إجبار الجدول على عدم الانهيار في الشاشات الصغيرة */
#governorates-table {
    width: 100% !important;
    margin: 0 !important;
}

/* حل مشكلة الاختفاء والظهور */
.dataTables_wrapper {
    display: block;
    width: 100%;
    overflow-x: auto;
}

/* تنسيق شكل السكرول ليكون واضحاً للمستخدم */
.table-responsive::-webkit-scrollbar {
    height: 8px;
}
.table-responsive::-webkit-scrollbar-thumb {
    background: #4e73df; /* لون التصميم الخاص بك */
    border-radius: 10px;
}
table.dataTable thead th {
    position: relative;
}

table.dataTable thead .sorting:after {
    content: " ⇅";
}
table.dataTable thead .sorting_asc:after {
    content: " ↑";
}
table.dataTable thead .sorting_desc:after {
    content: " ↓";
}
</style>