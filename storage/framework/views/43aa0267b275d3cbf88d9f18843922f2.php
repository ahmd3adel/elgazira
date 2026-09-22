<!DOCTYPE html>
<html dir="rtl">
<?php echo $__env->make('backend.layouts.partials.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

<?php echo $__env->make('backend.layouts.partials.navbar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="index3.html" class="brand-link">
        <img src="<?php echo e(asset('assets/backend/dist/img/logo.png')); ?>" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light"><?php echo e(config('app.name')); ?></span>
    </a>
    <?php echo $__env->make('backend.layouts.partials.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</aside>

<div class="content-wrapper" style="min-height: calc(100vh - 120px); display: flex; flex-direction: column;">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"> <?php echo $__env->yieldContent('breadcrumb-title'); ?></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <?php echo $__env->yieldContent('breadcrumb'); ?>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards (اختيارية) -->
    <?php if (! empty(trim($__env->yieldContent('stats_title')))): ?>
    <div class="row mb-3" id="statsCards">
        <div class="col-12 col-sm-6 col-md-4">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-primary elevation-1">
                    <?php echo $__env->yieldContent('stats_icon', '<i class="fas fa-chart-line"></i>'); ?> 
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">إجمالي <?php echo $__env->yieldContent('stats_title'); ?></span>
                    <span class="info-box-number" id="totalCount">0</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-success elevation-1"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text"><?php echo $__env->yieldContent('stats_title'); ?> النشطة</span>
                    <span class="info-box-number" id="activeCount">0</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-ban"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text"><?php echo $__env->yieldContent('stats_title'); ?> المعطلة</span>
                    <span class="info-box-number" id="inactiveCount">0</span>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Main content - ياخذ المساحة المتبقية -->
    <section class="content" style="flex: 1;">
        <?php echo $__env->yieldContent('content'); ?>
    </section>
</div>

<footer class="main-footer" style="position: relative; margin-top: 0; background: #fff; border-top: 1px solid #dee2e6;">
    <strong>Copyright &copy; 2014-2019 <a href="http://adminlte.io">AdminLTE.io</a>.</strong>
    All rights reserved.
    <div class="float-right d-none d-sm-inline-block">
        <b>Version</b> 3.0.0-rc.1
    </div>
</footer>

<aside class="control-sidebar control-sidebar-dark"></aside>
</div>

<!-- ========== الـ Scripts الأساسية ========== -->
<script src="<?php echo e(asset('assets/backend/plugins/jquery/jquery.min.js')); ?>"></script>
<script src="<?php echo e(asset('assets/backend/plugins/jquery-ui/jquery-ui.min.js')); ?>"></script>
<script>
    $.widget.bridge('uibutton', $.ui.button)
</script>

<script src="<?php echo e(asset('assets/backend/plugins/bootstrap/js/bootstrap.bundle.min.js')); ?>"></script>

<!-- ========== DataTables وملحقاته ========== -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>

<!-- ========== DataTables Buttons ========== -->
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap4.min.js"></script>

<!-- ========== مكتبات الأزرار الفردية ========== -->
<!-- JSZip (مطلوب لـ Excel) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<!-- pdfmake (مطلوب لـ PDF) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<!-- HTML5 أزرار (CSV, Excel, PDF) -->
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
<!-- ✅ تمت إضافة مكتبة الطباعة -->
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
<!-- أزرار إظهار/إخفاء الأعمدة -->
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.colVis.min.js"></script>

<!-- ========== مكتبات إضافية ========== -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- ========== Toastr (تم نقله للأعلى قبل AdminLTE) ========== -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

<script src="<?php echo e(asset('assets/backend/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js')); ?>"></script>
<script src="<?php echo e(asset('assets/backend/dist/js/adminlte.js')); ?>"></script>
<script src="<?php echo e(asset('assets/backend/dist/js/demo.js')); ?>"></script>

<?php if(Route::currentRouteName() == 'admin.dashboard' || request()->is('admin/dashboard*')): ?>
    <?php endif; ?>

<script>
    // إعدادات toastr
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-left",
        "timeOut": "5000",
        "extendedTimeOut": "2000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };
    
    // عرض رسائل الجلسة (Session)
    <?php if(session('success')): ?>
        toastr.success('<?php echo e(session('success')); ?>');
    <?php endif; ?>
    
    <?php if(session('error')): ?>
        toastr.error('<?php echo e(session('error')); ?>');
    <?php endif; ?>
    
    <?php if($errors->any()): ?>
        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            toastr.error('<?php echo e($error); ?>');
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php endif; ?>
</script>

<?php echo $__env->yieldPushContent('custom-js'); ?>
</body>
</html><?php /**PATH C:\xampp\htdocs\updating lum\resources\views/backend/app.blade.php ENDPATH**/ ?>