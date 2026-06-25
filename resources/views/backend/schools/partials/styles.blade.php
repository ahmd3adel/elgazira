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
    * { font-family: 'Cairo', sans-serif !important; }

    /* ===== DataTable تعريب ===== */
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        font-family: 'Cairo', sans-serif !important;
        font-size: 13px;
        color: #6c757d;
        margin-bottom: 8px;
    }
    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 4px 10px;
        font-size: 13px;
        margin-right: 6px;
        outline: none;
        transition: border-color .2s;
    }
    .dataTables_wrapper .dataTables_filter input:focus {
        border-color: #17a2b8;
        box-shadow: 0 0 0 2px rgba(23,162,184,.15);
    }
    .dataTables_wrapper .dataTables_length select {
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 3px 8px;
        font-size: 13px;
        margin: 0 4px;
    }

    /* ===== Pagination ===== */
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 4px 10px !important;
        border-radius: 4px !important;
        font-size: 13px !important;
        border: 1px solid #dee2e6 !important;
        margin: 0 2px !important;
        color: #495057 !important;
        background: #fff !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #e9ecef !important;
        border-color: #adb5bd !important;
        color: #212529 !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: #17a2b8 !important;
        border-color: #17a2b8 !important;
        color: #fff !important;
        font-weight: 600 !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
        color: #adb5bd !important;
        cursor: not-allowed !important;
    }

    /* ===== الجدول ===== */
    .table thead th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        border-top: none;
        color: #495057;
        font-weight: 700;
        font-size: 13px;
        padding: 10px 12px;
        white-space: nowrap;
    }
    .table tbody td {
        font-size: 13px;
        padding: 9px 12px;
        color: #444;
        vertical-align: middle;
        border-bottom: 1px solid #f1f1f1;
    }
    .table tbody tr:hover td {
        background-color: #f0fafc;
    }

    /* ===== Badges ===== */
    .badge {
        font-size: 11px;
        padding: 4px 10px;
        border-radius: 10px;
        font-weight: 600;
    }
    .badge-ابتدائي        { background: #e8f4fd; color: #0c5499; }
    .badge-اعدادي         { background: #fff8e1; color: #7a5c00; }
    .badge-حضانة          { background: #e6f4ea; color: #256029; }
    .badge-تعليم-مجتمعي   { background: #fdecea; color: #8b1a1a; }

    /* ===== أزرار العمليات ===== */
    .action-buttons {
        display: flex;
        gap: 4px;
        justify-content: center;
        flex-wrap: nowrap;
    }
    .action-buttons .btn {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 12px;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 4px;
        white-space: nowrap;
    }

    /* ===== Card ===== */
    .card {
        border: none;
        border-radius: 8px;
        box-shadow: 0 1px 4px rgba(0,0,0,.1);
    }
    .card-header {
        background: #fff;
        border-bottom: 1px solid #f0f0f0;
        padding: 12px 16px;
    }
    .card-title {
        font-size: 15px;
        font-weight: 700;
        color: #333;
    }

    /* ===== Responsive ===== */
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .table-responsive table td,
    .table-responsive table th {
        white-space: nowrap;
    }
    .table-responsive::-webkit-scrollbar { height: 6px; }
    .table-responsive::-webkit-scrollbar-thumb {
        background: #17a2b820;
        border-radius: 10px;
    }
    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #17a2b8;
    }

    /* ===== Modal ===== */
    .modal-backdrop { z-index: 1040 !important; }
    .modal          { z-index: 1050 !important; }
    .modal-header .close {
        margin: -1rem auto -1rem -1rem !important;
    }

    /* ===== Select2 ===== */
    .select2-container--bootstrap4 .select2-selection--single
    .select2-selection__rendered { text-align: right; }

    /* ===== icon-circle ===== */
    .icon-circle {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        transition: transform .2s;
    }
    .icon-circle:hover { transform: scale(1.1); }
</style>