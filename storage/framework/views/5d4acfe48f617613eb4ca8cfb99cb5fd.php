
<?php $__env->startSection('title', 'إدارة التحويلات المخزنية'); ?>
<?php $__env->startSection('breadcrumb-title', 'إدارة الموقع'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">الرئيسية</a></li>
    <li class="breadcrumb-item active">التحويلات بين المخازن</li>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('custom-css'); ?>
    <style>
        .card-title i { color: #007bff; }
        .item-row { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 10px; border: 1px solid #dee2e6; }
        .btn-remove { margin-top: 32px; }
        
        .balance-warning {
            background-color: #fff3cd;
            border-right: 4px solid #ffc107;
        }
        .balance-info {
            background-color: #d1ecf1;
            border-right: 4px solid #17a2b8;
        }
        .balance-matched {
            background-color: #d4edda;
            border-right: 4px solid #28a745;
        }
        .balance-error {
            background-color: #f8d7da;
            border-right: 4px solid #dc3545;
        }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <div class="container-fluid">
        
        
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-exchange-alt ml-2"></i>
                    إنشاء أمر تحويل جديد
                </h3>
            </div>
            
            <form action="<?php echo e(route('admin.transfers.store')); ?>" method="POST" id="transfer-form">
                <?php echo csrf_field(); ?>
                <div class="card-body">
                    
                    
                    <div class="alert alert-info balance-info">
                        <i class="fas fa-info-circle ml-2"></i>
                        <strong>تنبيه هام:</strong> يجب أن تتساوى كمية <strong>الصنف الأساسي (سادة 40)</strong> مع <strong>مجموع كميات الأصناف التامة</strong>.
                        <br>
                        <small class="text-muted">مثال: إذا أرسلت 100 كرتونة سادة 40، يجب أن ترسل 100 وجبة تامة موزعة على الأصناف التامة.</small>
                    </div>
                    
                    
                    <div id="balance-status" class="alert alert-secondary d-none">
                        <i class="fas fa-balance-scale ml-2"></i>
                        <span id="balance-message">...</span>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>من مخزن (المصدر) <span class="text-danger">*</span></label>
                                <select name="from_warehouse_id" class="form-control select2" required>
                                    <option value="">اختر المخزن</option>
                                    <?php $__currentLoopData = $warehouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $w): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($w->id); ?>"><?php echo e($w->name); ?> (<?php echo e(__($w->type)); ?>)</option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>إلى (الوجهة) <span class="text-danger">*</span></label>
                                <select name="to_warehouse_id" class="form-control select2" required>
                                    <option value="">اختر الوجهة (مخزن أو مدرسة)</option>
                                    <?php $__currentLoopData = $warehouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $w): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($w->id); ?>"><?php echo e($w->name); ?> (<?php echo e(__($w->type)); ?>)</option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>نوع التحويل <span class="text-danger">*</span></label>
                                <select name="type" class="form-control" required>
                                    <option value="permanent">تحويل مخزني (تغيير رصيد نهائي)</option>
                                    <option value="custody">نقل عهدة (نقطة توزيع/مدرسة)</option>
                                </select>
                                <small class="text-muted">نقل العهدة لا يخصم من رصيد المخزن في التقرير الرسمي.</small>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h5 class="border-bottom pb-2 mb-3"><i class="fas fa-list ml-1"></i> الأصناف والكميات</h5>
                        <div id="items-container">
                            <div class="row item-row">
                                <div class="col-md-7">
                                    <div class="form-group">
                                        <label>الصنف</label>
                                        <select name="items[0][product_id]" class="form-control" required>
                                            <option value="">اختر المنتج</option>
                                            <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($p->id); ?>" data-is-base="<?php echo e($p->is_base ? 'true' : 'false'); ?>">
                                                    <?php echo e($p->name); ?> <?php if($p->is_base): ?>[أساسي]<?php endif; ?>
                                                </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>الكمية (كرتونة)</label>
                                        <input type="number" name="items[0][quantity]" class="form-control quantity-input" min="1" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-success btn-block add-item-btn">
                                        <i class="fas fa-plus"></i> إضافة
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-left">
                    <button type="submit" class="btn btn-primary px-5" id="submit-btn">
                        <i class="fas fa-exchange-alt"></i> حفظ وتنفيذ التحويل
                    </button>
                </div>
            </form>
        </div>

        
        <div class="card card-outline card-secondary mt-4">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-history ml-2"></i>
                    سجل التحويلات الأخيرة
                </h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped text-center datatable">
                        <thead class="bg-dark text-white">
                            <tr>
                                <th>رقم العملية</th>
                                <th>من (المصدر)</th>
                                <th>إلى (الوجهة)</th>
                                <th>النوع</th>
                                <th>التاريخ</th>
                                <th>الأصناف والكميات</th>
                                <th>إجمالي الكميات</th>
                                <th>الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $transfers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transfer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e($transfer->transfer_number); ?></td>
                                    <td><?php echo e($transfer->fromWarehouse->name ?? '---'); ?></td>
                                    <td><?php echo e($transfer->toWarehouse->name ?? '---'); ?></td>
                                    <td>
                                        <span class="badge <?php echo e($transfer->type == 'permanent' ? 'badge-info' : 'badge-warning'); ?>">
                                            <?php echo e($transfer->type == 'permanent' ? 'تحويل نهائي' : 'نقل عهدة'); ?>

                                        </span>
                                    </td>
                                    <td><?php echo e($transfer->created_at->format('Y-m-d H:i')); ?></td>
                                    
                                    
                                    <td class="text-right">
                                        <?php if($transfer->items && $transfer->items->count() > 0): ?>
                                            <div style="min-width: 200px;">
                                                <?php $__currentLoopData = $transfer->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <div class="d-flex justify-content-between border-bottom py-1">
                                                        <span class="font-weight-bold"><?php echo e($item->product->name ?? 'منتج غير معروف'); ?></span>
                                                        <span class="badge badge-primary">
                                                            <?php echo e(number_format($item->quantity)); ?> 
                                                            <small>كرتونة</small>
                                                        </span>
                                                    </div>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">لا توجد أصناف</span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    
                                    <td class="font-weight-bold bg-light">
                                        <?php echo e(number_format($transfer->items->sum('quantity') ?? 0)); ?>

                                        <small class="text-muted">كرتونة</small>
                                    </td>
                                    
                                    <td><span class="badge badge-success">مكتمل</span></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted">لا توجد تحويلات حتى الآن</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <?php if($transfers->count() > 0): ?>
                        <tfoot class="bg-light">
                            <tr class="font-weight-bold">
                                <td colspan="5" class="text-center">الإجمالي العام</td>
                                <td class="text-right">--</td>
                                <td class="bg-info text-white">
                                    <?php echo e(number_format($transfers->sum(function($t) { return $t->items->sum('quantity'); }))); ?>

                                    <small class="text-white">كرتونة</small>
                                </td>
                                <td>--</td>
                            </tr>
                        </tfoot>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('custom-js'); ?>
<script>
    $(document).ready(function() {
        let itemIdx = 1;
        
        // دالة حساب التوازن
        function calculateBalance() {
            let baseQuantity = 0;
            let otherTotal = 0;
            
            $('.item-row').each(function() {
                let select = $(this).find('select[name*="[product_id]"]');
                let quantityInput = $(this).find('input[name*="[quantity]"]');
                
                let productId = select.val();
                let quantity = parseInt(quantityInput.val()) || 0;
                
                if (productId && quantity > 0) {
                    let isBase = select.find('option:selected').data('is-base') || false;
                    
                    if (isBase) {
                        baseQuantity = quantity;
                    } else {
                        otherTotal += quantity;
                    }
                }
            });
            
            let statusDiv = $('#balance-status');
            let messageSpan = $('#balance-message');
            
            if (baseQuantity === 0 && otherTotal === 0) {
                statusDiv.addClass('d-none');
                return true;
            }
            
            statusDiv.removeClass('d-none');
            
            if (baseQuantity === otherTotal) {
                statusDiv.removeClass('alert-warning alert-danger').addClass('alert-success balance-matched');
                messageSpan.html(`
                    <strong>✓ متوازن:</strong> كمية الأساسي (${baseQuantity}) = مجموع الأصناف التامة (${otherTotal})
                `);
                return true;
            } else {
                let difference = Math.abs(baseQuantity - otherTotal);
                if (baseQuantity > otherTotal) {
                    statusDiv.removeClass('alert-success alert-danger').addClass('alert-warning balance-warning');
                    messageSpan.html(`
                        <strong>⚠️ غير متوازن:</strong> كمية الأساسي (${baseQuantity}) أكبر من مجموع الأصناف التامة (${otherTotal}) 
                        بفارق ${difference}
                    `);
                } else {
                    statusDiv.removeClass('alert-success alert-warning').addClass('alert-danger balance-error');
                    messageSpan.html(`
                        <strong>❌ غير متوازن:</strong> كمية الأساسي (${baseQuantity}) أقل من مجموع الأصناف التامة (${otherTotal}) 
                        بنقص ${difference}
                    `);
                }
                return false;
            }
        }
        
        // إضافة صف صنف جديد
        $('.add-item-btn').click(function() {
            let newItem = `
                <div class="row item-row">
                    <div class="col-md-7">
                        <div class="form-group">
                            <select name="items[${itemIdx}][product_id]" class="form-control" required>
                                <option value="">اختر المنتج</option>
                                <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($p->id); ?>" data-is-base="<?php echo e($p->is_base ? 'true' : 'false'); ?>">
                                        <?php echo e($p->name); ?> <?php if($p->is_base): ?>[أساسي]<?php endif; ?>
                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <input type="number" name="items[${itemIdx}][quantity]" class="form-control quantity-input" min="1" required>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger btn-block remove-item-btn">
                            <i class="fas fa-trash"></i> حذف
                        </button>
                    </div>
                </div>`;
            $('#items-container').append(newItem);
            itemIdx++;
        });
        
        // حذف صف صنف وإعادة حساب التوازن
        $(document).on('click', '.remove-item-btn', function() {
            $(this).closest('.item-row').remove();
            calculateBalance();
        });
        
        // ربط أحداث التغيير لحساب التوازن
        $(document).on('change', 'select[name*="[product_id]"]', calculateBalance);
        $(document).on('input', '.quantity-input', calculateBalance);
        
        // التحقق قبل إرسال الفورم
        $('#transfer-form').on('submit', function(e) {
            if (!calculateBalance()) {
                e.preventDefault();
                // استخدام toastr إذا كان موجوداً
                if (typeof toastr !== 'undefined') {
                    toastr.error('⚠️ لا يمكن تنفيذ التحويل: كمية الصنف الأساسي لا تتساوى مع مجموع الأصناف التامة');
                } else {
                    alert('⚠️ لا يمكن تنفيذ التحويل: كمية الصنف الأساسي لا تتساوى مع مجموع الأصناف التامة');
                }
                return false;
            }
        });
        
        // تهيئة أولية
        calculateBalance();
    });
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('backend.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\updating lum\resources\views/backend/transfers/index.blade.php ENDPATH**/ ?>