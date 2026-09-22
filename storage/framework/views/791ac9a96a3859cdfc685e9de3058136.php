<div class="table-responsive w-100">
<table id="distribution-orders-table" class="table table-bordered table-striped nowrap text-center">
    <thead class="bg-dark text-white">
        <tr>
            <th>#</th>
            <th>التاريخ</th>
            <th>المدرسة</th>
            <th>الإدارة</th>
            
            
            <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <th class="bg-secondary"><?php echo e($product->name); ?></th>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <th class="bg-primary">إجمالي الصرف</th>
            <th>المسؤول</th>
            <th>العمليات</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>
</div><?php /**PATH C:\xampp\htdocs\updating lum\resources\views/backend/distributions/partials/table.blade.php ENDPATH**/ ?>