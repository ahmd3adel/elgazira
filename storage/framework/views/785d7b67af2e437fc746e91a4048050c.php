
<?php $__env->startSection('title', 'إدارة الموردين'); ?>
<?php $__env->startSection('breadcrumb-title', 'إدارة الموقع'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">الرئيسية</a></li>
    <li class="breadcrumb-item active">الموردين</li>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('custom-css'); ?>
    
    <?php echo $__env->make('backend.suppliers.partials.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <style>
        .card-title i {
            color: #17a2b8;
        }

        /* تغيير اللون للأزرق لتمييز قسم المناطق */
        .btn-sm {
            border-radius: 4px;
            font-weight: 600;
        }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <div class="container-fluid">
        
        <?php if(session('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle ml-1"></i> <?php echo e(session('success')); ?>

                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <div class="card card-outline card-info"> 
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-map-marker-alt ml-2"></i>
                    قائمة الموردين المسجلة
                </h3>
                <div class="card-tools d-flex">
                    <?php echo $__env->make('backend.suppliers.partials.export-buttons', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                    <button type="button" class="btn btn-info btn-sm mr-2" data-toggle="modal"
                        data-target="#addGovernorateModal">
                        <i class="fas fa-plus"></i> إضافة مورد جديدة
                    </button>
                </div>
            </div>
            <div class="card-body">
                <?php echo $__env->make('backend.suppliers.partials.table', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            </div>
        </div>
    </div>

    
    <?php echo $__env->make('backend.suppliers.partials.modals.add', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->make('backend.suppliers.partials.modals.edit', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('custom-js'); ?>
    

    
    <?php echo $__env->make('backend.suppliers.partials.scripts.datatable', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->make('backend.suppliers.partials.scripts.modals', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->make('backend.suppliers.partials.scripts.exports', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    
    <script>
        $(document).ready(function() {
            // كود المودال في حالة وجود أخطاء Validation
            <?php if($errors->any()): ?>
                $('#addGovernorateModal').modal('show');
            <?php endif; ?>
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('backend.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\updating lum\resources\views/backend/suppliers/index.blade.php ENDPATH**/ ?>