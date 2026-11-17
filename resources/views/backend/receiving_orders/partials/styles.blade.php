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
/* تحسين شكل المودال */
#addReceivingOrderModal .modal-content {
    border-radius: 16px;
    border: none;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    overflow: hidden;
}

#addReceivingOrderModal .modal-header {
    background: linear-gradient(135deg, #0d6efd, #0b5ed7);
    border-bottom: none;
    padding: 1rem 1.5rem;
}

#addReceivingOrderModal .modal-header .modal-title {
    font-weight: 600;
    font-size: 1.2rem;
}

#addReceivingOrderModal .modal-body {
    padding: 1.75rem;
    background-color: #f8f9fc;
}

#addReceivingOrderModal .form-label {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

#addReceivingOrderModal .form-control,
#addReceivingOrderModal .form-select {
    border-radius: 10px;
    border: 1px solid #dee2e6;
    padding: 0.6rem 0.8rem;
    transition: all 0.3s ease;
}

#addReceivingOrderModal .form-control:focus,
#addReceivingOrderModal .form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
}

#addReceivingOrderModal .form-text {
    font-size: 0.75rem;
    color: #6c757d;
    margin-top: 0.25rem;
}

#addReceivingOrderModal .modal-footer {
    background-color: white;
    border-top: 1px solid #e9ecef;
    padding: 1rem 1.5rem;
}

#addReceivingOrderModal .modal-footer .btn {
    border-radius: 10px;
    padding: 0.5rem 1.2rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

#addReceivingOrderModal .modal-footer .btn-primary {
    background: linear-gradient(135deg, #0d6efd, #0b5ed7);
    border: none;
}

#addReceivingOrderModal .modal-footer .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
}

#addReceivingOrderModal .modal-footer .btn-secondary:hover {
    transform: translateY(-2px);
}

/* تحسين الحقول التي بها خطأ */
#addReceivingOrderModal .is-invalid {
    border-color: #dc3545;
    background-image: none;
}

/* أنيميشن للمودال */
#addReceivingOrderModal.fade .modal-dialog {
    transform: translateY(-50px);
    transition: transform 0.3s ease-out;
}

#addReceivingOrderModal.show .modal-dialog {
    transform: translateY(0);
}

/* تحسين الأيقونات */
#addReceivingOrderModal .modal-header i,
#addReceivingOrderModal .modal-footer i {
    margin-left: 0.5rem;
}

/* جعل الحقل المطلوب يظهر بشكل أوضح */
#addReceivingOrderModal .form-label .text-danger {
    font-size: 1rem;
    margin-right: 0.2rem;
}

/* تحسين شكل الحقول على الشاشات الصغيرة */
@media (max-width: 768px) {
    #addReceivingOrderModal .modal-body {
        padding: 1rem;
    }
    
    #addReceivingOrderModal .modal-footer .btn {
        padding: 0.4rem 0.8rem;
        font-size: 0.85rem;
    }
}
</style>