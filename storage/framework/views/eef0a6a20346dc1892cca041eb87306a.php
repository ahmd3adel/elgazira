
<?php $__env->startSection('title', ' الشحنات'); ?>
<?php $__env->startSection('breadcrumb-title', 'إدارة الموقع'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">الرئيسية</a></li>
    <li class="breadcrumb-item active">الارصدة</li>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('custom-css'); ?>
    
    <?php echo $__env->make('backend.inventories.partials.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
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
                    قائمة الارصدة
                </h3>
                
            </div>
            <div class="card-body">
                <table class="table table-bordered text-center align-middle">
    <thead class="bg-dark text-white">
        <tr>
            <th rowspan="2">المخازن</th>
            <th colspan="<?php echo e($products->count()); ?>">الأصناف والمنتجات (بالكرتونة)</th>
            <th rowspan="2" class="bg-info">إجمالي الرصيد</th>
            <th rowspan="2">ميزان الوجبة</th>
        </tr>
        <tr>
            <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <th><?php echo e($product->name); ?></th>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tr>
    </thead>
    <tbody>
        <?php $grandTotal = 0; ?>
        <?php $__currentLoopData = $warehouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $warehouse): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php $rowTotal = 0; ?>
            <tr>
                <td class="text-right">
                    <strong><?php echo e($warehouse->name); ?></strong>
                    <?php if($warehouse->type == 'main'): ?>
                        <span class="badge badge-primary float-left">رئيسي</span>
                    <?php else: ?>
                        <span class="badge badge-secondary float-left">فرعي</span>
                    <?php endif; ?>
                </td>

                
                <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php 
                        $qty = $inventoryMap[$warehouse->id][$product->id] ?? 0; 
                        $rowTotal += $qty;
                    ?>
                    <td class="<?php echo e($qty > 0 ? 'text-primary font-weight-bold' : 'text-muted'); ?>">
                        <?php echo e($qty > 0 ? number_format($qty) : '-'); ?>

                    </td>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                
                <td class="bg-light font-weight-bold"><?php echo e(number_format($rowTotal)); ?></td>

                
                <td>
                    <?php if($rowTotal > 0): ?>
                        <span class="badge badge-warning p-2">⚠️ فائض</span>
                    <?php else: ?>
                        <span class="badge badge-success p-2">✅ متوازن</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php $grandTotal += $rowTotal; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
    
    
    <tfoot class="bg-light font-weight-bold">
        <tr>
            <td>إجمالي المنتج (كل المخازن)</td>
            <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php 
                    $colTotal = \App\Models\Inventory::where('product_id', $product->id)->sum('quantity');
                ?>
                <td><?php echo e(number_format($colTotal)); ?></td>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <td class="bg-info text-white"><?php echo e(number_format($grandTotal)); ?></td>
            <td>--</td>
        </tr>
    </tfoot>
</table>
        </div>
    </div>

    
    <?php echo $__env->make('backend.inventories.partials.modals.add', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->make('backend.inventories.partials.modals.edit', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('custom-js'); ?>
    

    
    <?php echo $__env->make('backend.inventories.partials.scripts.datatable', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->make('backend.inventories.partials.scripts.modals', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->make('backend.inventories.partials.scripts.exports', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    
    <script>
        $(document).ready(function() {
            // كود المودال في حالة وجود أخطاء Validation
            <?php if($errors->any()): ?>
                $('#addGovernorateModal').modal('show');
            <?php endif; ?>
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('backend.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\updating lum\resources\views/backend/inventories/all_transactions.blade.php ENDPATH**/ ?>