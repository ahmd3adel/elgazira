
<?php $__env->startSection('title', ' الأرصدة'); ?>
<?php $__env->startSection('breadcrumb-title', 'إدارة الموقع'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">الرئيسية</a></li>
    <li class="breadcrumb-item active">الأرصدة</li>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('custom-css'); ?>
    
    <?php echo $__env->make('backend.inventories.partials.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <style>
        .card-title i {
            color: #17a2b8;
        }

        .btn-sm {
            border-radius: 4px;
            font-weight: 600;
        }

        /* تمييز السادة والتام */
        .col-base {
            background-color: #6c757d !important; /* رمادي للسادة */
        }
        .col-finished {
            background-color: #007bff !important; /* أزرق للتام */
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
                    <i class="fas fa-boxes ml-2"></i>
                    قائمة الأرصدة
                </h3>
            </div>
            <div class="card-body">

                <?php
                    // ============================================================
                    // 1. فصل الأصناف: السادة والتام
                    // ============================================================
                    $baseProducts = $products->where('is_base', true);
                    $finishedProducts = $products->where('is_base', false);

                    // ============================================================
                    // 2. تجميع الأصناف التامة حسب السادة المرتبطة بها
                    //    (مع تحويل المفاتيح لـ int لضمان المطابقة)
                    // ============================================================
                    $finishedByBase = $finishedProducts
                        ->filter(function ($p) {
                            return !is_null($p->companion_product_id) && $p->companion_product_id > 0;
                        })
                        ->groupBy(function ($p) {
                            return (int) $p->companion_product_id;
                        });

                    // ============================================================
                    // 3. الأصناف التامة "اليتيمة" (بدون سادة مرتبطة)
                    // ============================================================
                    $orphanFinished = $finishedProducts->filter(function ($p) {
                        return is_null($p->companion_product_id) || $p->companion_product_id <= 0;
                    });
                ?>

                <table class="table table-bordered table-striped table-hover text-center align-middle">
                    <thead class="bg-dark text-white">
                        <tr>
                            <th rowspan="2" style="vertical-align: middle; min-width: 180px;">المخازن</th>
                            <th colspan="<?php echo e($products->count()); ?>" class="text-center border-bottom">
                                الأصناف والمنتجات (بالكرتونة)
                            </th>
                            <th rowspan="2" style="vertical-align: middle; min-width: 100px;" class="bg-info">
                                إجمالي الرصيد
                            </th>
                            <th rowspan="2" style="vertical-align: middle; min-width: 200px;" class="bg-secondary">
                                ميزان الوجبة (سادة vs تام)
                            </th>
                        </tr>
                        <tr>
                            <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <th class="<?php echo e($product->is_base ? 'col-base' : 'col-finished'); ?>"
                                    style="font-size: 0.85rem; min-width: 100px;">
                                    <?php echo e($product->name); ?>

                                    <?php if($product->is_base): ?>
                                        <small class="d-block">(سادة)</small>
                                    <?php else: ?>
                                        <small class="d-block">(تام)</small>
                                    <?php endif; ?>
                                </th>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $grandTotal = 0; ?>

                        <?php $__currentLoopData = $warehouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $warehouse): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $rowTotal = 0;
                                $isBalanced = true;
                                $balanceDetails = [];
                                $hasAnyBase = false;
                            ?>
                            <tr>
                                
                                <td class="text-right">
                                    <strong><?php echo e($warehouse->name); ?></strong>
                                    <?php if($warehouse->type == 'main'): ?>
                                        <span class="badge badge-primary float-left">رئيسي</span>
                                    <?php elseif($warehouse->type == 'sub'): ?>
                                        <span class="badge badge-secondary float-left">فرعي</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning float-left">نقطة صرف</span>
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

                                
                                <td class="bg-light font-weight-bold">
                                    <?php echo e(number_format($rowTotal)); ?>

                                </td>

                                
                                <td>
                                    <?php $__currentLoopData = $baseProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $baseProduct): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $baseId = (int) $baseProduct->id;

                                            // تجاهل السادة اللي ملهاش أصناف تامة
                                            if (!isset($finishedByBase[$baseId])) {
                                                continue;
                                            }

                                            $hasAnyBase = true;

                                            // كمية السادة في المخزن
                                            $baseQty = $inventoryMap[$warehouse->id][$baseProduct->id] ?? 0;

                                            // مجموع الأصناف التامة المرتبطة
                                            $finishedQty = 0;
                                            foreach ($finishedByBase[$baseId] as $fp) {
                                                $finishedQty += $inventoryMap[$warehouse->id][$fp->id] ?? 0;
                                            }

                                            $diff = $baseQty - $finishedQty;

                                            if ($diff != 0) {
                                                $isBalanced = false;
                                                $balanceDetails[] = [
                                                    'name'     => $baseProduct->name,
                                                    'base'     => $baseQty,
                                                    'finished' => $finishedQty,
                                                    'diff'     => $diff,
                                                ];
                                            }
                                        ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                    
                                    <?php if(!$hasAnyBase): ?>
                                        <span class="badge badge-secondary p-2">— لا يوجد سادة</span>
                                    <?php elseif($isBalanced): ?>
                                        <span class="badge badge-success p-2">
                                            <i class="fas fa-check-circle"></i> متوازن
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-danger p-2"
                                              data-toggle="tooltip"
                                              data-html="true"
                                              title="<?php $__currentLoopData = $balanceDetails; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php echo e($detail['name']); ?>: سادة <?php echo e(number_format($detail['base'])); ?> / تام <?php echo e(number_format($detail['finished'])); ?> (فرق <?php echo e($detail['diff'] > 0 ? '+' : ''); ?><?php echo e(number_format($detail['diff'])); ?>)<br><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>">
                                            <i class="fas fa-times-circle"></i> غير متوازن
                                        </span>

                                        
                                        <div class="mt-1" style="font-size: 11px; line-height: 1.4;">
                                            <?php $__currentLoopData = $balanceDetails; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <div class="<?php echo e($detail['diff'] > 0 ? 'text-warning' : 'text-danger'); ?>">
                                                    <strong><?php echo e($detail['name']); ?>:</strong>
                                                    سادة <?php echo e(number_format($detail['base'])); ?>

                                                    / تام <?php echo e(number_format($detail['finished'])); ?>

                                                    <span class="badge badge-<?php echo e($detail['diff'] > 0 ? 'warning' : 'danger'); ?> badge-sm">
                                                        <?php echo e($detail['diff'] > 0 ? '+' : ''); ?><?php echo e(number_format($detail['diff'])); ?>

                                                    </span>
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
                                    <?php endif; ?>

                                    
                                    <?php if($orphanFinished->count() > 0): ?>
                                        <div class="mt-1">
                                            <small class="text-muted">
                                                <i class="fas fa-exclamation-triangle text-warning"></i>
                                                <?php echo e($orphanFinished->count()); ?> صنف تام بدون سادة
                                            </small>
                                        </div>
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
                                <td>
                                    <?php echo e(number_format($productTotals[$product->id] ?? 0)); ?>

                                </td>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <td class="bg-info text-white"><?php echo e(number_format($grandTotal)); ?></td>
                            <td>
                                <?php
                                    $grandBalanced = true;
                                    $grandHasAny = false;
                                    $grandDetails = [];

                                    foreach ($baseProducts as $baseProduct) {
                                        $baseId = (int) $baseProduct->id;
                                        if (!isset($finishedByBase[$baseId])) continue;

                                        $grandHasAny = true;

                                        $totalBase = $productTotals[$baseProduct->id] ?? 0;
                                        $totalFinished = 0;
                                        foreach ($finishedByBase[$baseId] as $fp) {
                                            $totalFinished += $productTotals[$fp->id] ?? 0;
                                        }

                                        if ($totalBase != $totalFinished) {
                                            $grandBalanced = false;
                                            $grandDetails[] = $baseProduct->name
                                                . ': سادة ' . number_format($totalBase)
                                                . ' / تام ' . number_format($totalFinished);
                                        }
                                    }
                                ?>

                                <?php if(!$grandHasAny): ?>
                                    <span class="badge badge-secondary p-2">—</span>
                                <?php elseif($grandBalanced): ?>
                                    <span class="badge badge-success p-2">
                                        <i class="fas fa-check-circle"></i> متوازن
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-danger p-2"
                                          data-toggle="tooltip"
                                          data-html="true"
                                          title="<?php echo e(implode('<br>', $grandDetails)); ?>">
                                        <i class="fas fa-times-circle"></i> غير متوازن
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        
        <?php echo $__env->make('backend.inventories.partials.modals.add', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php echo $__env->make('backend.inventories.partials.modals.edit', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </div>
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

            // تفعيل الـ Tooltips
            $('[data-toggle="tooltip"]').tooltip({
                html: true,
                placement: 'top',
                container: 'body'
            });
        });
    </script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('backend.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\updating lum\resources\views/backend/inventories/all_transactions.blade.php ENDPATH**/ ?>