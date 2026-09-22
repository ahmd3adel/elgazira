<table id="inventoryMatrixTable" class="table table-bordered table-striped table-hover w-100 text-center">
    <thead class="bg-dark text-white">
        <!-- الصف الأول -->
        <tr>
            <th rowspan="2" style="vertical-align: middle; width: 180px;">المخازن</th>
            <th colspan="<?php echo e($products->count()); ?>" class="text-center border-bottom">الأصناف والمنتجات (بالكرتونة)</th>
            <th rowspan="2" style="vertical-align: middle;" class="bg-info">إجمالي الرصيد</th>
            <th rowspan="2" style="vertical-align: middle;" class="bg-secondary">ميزان الوجبة (سادة vs تام)</th>
        </tr>
        <!-- الصف الثاني: أسماء المنتجات -->
        <tr>
            <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <th style="font-size: 0.85rem; min-width: 100px;"><?php echo e($product->name); ?></th>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tr>
    </thead>
    
    <tbody>
        <?php $__currentLoopData = $warehouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $warehouse): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $totalInWarehouse = 0;
                $mainProductID = 1; // ID المنتج السادة 40جم
                $mainQty = $inventoryMap[$warehouse->id][$mainProductID] ?? 0;
                $otherProductsQty = 0;
            ?>

            <tr>
                <!-- اسم المخزن -->
                <td class="text-right font-weight-bold">
                    <?php echo e($warehouse->name); ?>

                    <span class="badge <?php echo e($warehouse->type == 'main' ? 'badge-primary' : 'badge-secondary'); ?> float-left ml-2">
                        <?php echo e($warehouse->type == 'main' ? 'رئيسي' : 'فرعي'); ?>

                    </span>
                </td>

                <!-- خلايا الأصناف -->
                <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $qty = $inventoryMap[$warehouse->id][$product->id] ?? 0;
                        $totalInWarehouse += $qty;
                        
                        // حساب إجمالي الأصناف التامة (كل شيء ماعدا السادة 40)
                        if($product->id != $mainProductID) {
                            $otherProductsQty += $qty;
                        }
                    ?>
                    <td class="<?php echo e($qty == 0 ? 'text-muted' : 'font-weight-bold text-primary'); ?>">
                        <?php echo e($qty > 0 ? number_format($qty) : '-'); ?>

                    </td>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                <!-- إجمالي الكمية في المخزن -->
                <td class="bg-light font-weight-bold">
                    <?php echo e(number_format($totalInWarehouse)); ?>

                </td>

                <!-- عمود الميزان الذكي -->
                <?php
                    $balance = $mainQty - $otherProductsQty;
                ?>
                <td style="background-color: #f4f6f9;">
                    <?php if(abs($balance) <= 5): ?> 
                        <span class="badge badge-success px-3">✅ متوازن</span>
                    <?php elseif($balance > 0): ?>
                        <div class="text-warning font-weight-bold" style="font-size: 0.8rem;">
                            ⚠️ فائض سادة (+<?php echo e(number_format($balance)); ?>)
                            <br><small class="text-dark">يحتاج صنف تام</small>
                        </div>
                    <?php else: ?>
                        <div class="text-danger font-weight-bold" style="font-size: 0.8rem;">
                            🚨 نقص سادة (<?php echo e(number_format($balance)); ?>)
                            <br><small class="text-dark">يحتاج سادة 40جم</small>
                        </div>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>

   <tfoot class="bg-light font-weight-bold text-dark">
    <tr>
        <td>إجمالي المنتج (كل المخازن)</td>
        <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <td class="text-primary">
                <?php echo e(number_format($productTotals[$product->id] ?? 0)); ?>

            </td>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <td class="bg-info text-white">
            <?php echo e(number_format(collect($productTotals)->sum())); ?>

        </td>
        <td class="bg-secondary text-white">--</td>
    </tr>
</tfoot>
</table><?php /**PATH C:\xampp\htdocs\updating lum\resources\views/backend/inventories/partials/table.blade.php ENDPATH**/ ?>