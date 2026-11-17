<!-- Modal: إضافة إذن صرف وجبات -->
<!-- Modal: إضافة إذن صرف وجبات -->
<div class="modal fade" id="addDistributionAllocationsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-truck-loading"></i> تسجيل إذن صرف (يومي)
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="addDistributionForm">
                @csrf
                <div class="modal-body">
                    <!-- الإدارة والتاريخ في نفس الصف -->
                    <div class="row">
                        <!-- اختيار الإدارة -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>الإدارة <span class="text-danger">*</span></label>
                                <select name="department_id" class="form-control" id="department_id" required>
                                    <option value="">اختر الإدارة</option>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- تاريخ الصرف -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>تاريخ الصرف <span class="text-danger">*</span></label>
                                <input type="date" name="order_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h6 class="font-weight-bold"><i class="fas fa-boxes"></i> تفاصيل الكميات المنصرفة:</h6>

                    <div class="table-responsive">
                        <table class="table table-bordered bg-light" id="items-table">
                            <thead>
                                <tr class="text-center">
                                    <th width="40%">الصنف (المنتج)</th>
                                    <th width="30%">الكمية (كرتونة)</th>
                                    <th width="10%"></th>
                                 </tr>
                            </thead>
                            <tbody id="itemsContainer">
                                <tr class="item-row">
                                    <td>
                                        <select name="items[0][product_id]" class="form-control product-select" required>
                                            @foreach ($products as $product)
                                                <option value="{{ $product->id }}" {{ $loop->first ? 'selected' : '' }}>
                                                    {{ $product->name }} ({{ number_format($product->price) }} )
                                                </option>
                                            @endforeach
                                        </select>
                                     </td>
                                    <td>
                                        <input type="number" name="items[0][quantity]"
                                            class="form-control text-center quantity-input" 
                                            placeholder="0" min="1" step="1" required>
                                     </td>
                                   
                                    <td>
                                        <button type="button" class="btn btn-danger btn-sm remove-row" disabled>
                                            <i class="fas fa-trash"></i>
                                        </button>
                                     </td>
                                 </tr>
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="addItem">
                            <i class="fas fa-plus"></i> إضافة صنف آخر
                        </button>
                    </div>

                    <div class="form-group mt-3">
                        <label>ملاحظات</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="أي ملاحظات إضافية..."></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">إغلاق</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> اعتماد الصرف
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
$(document).ready(function() {
    let itemIndex = 1;

    // إضافة سطر جديد
    $('#addItem').click(function() {
        let newRow = `
            <tr class="item-row">
                <td>
                    <select name="items[${itemIndex}][product_id]" class="form-control product-select" required>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }} ({{ number_format($product->price) }} ج.م)</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <input type="number" name="items[${itemIndex}][quantity]" class="form-control text-center quantity-input" placeholder="0" min="1" step="1" required>
                </td>
                <td>
                    <input type="text" class="form-control text-center" value="كرتونة" readonly disabled>
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm remove-row">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        $('#itemsContainer').append(newRow);
        itemIndex++;
        
        // تحديث select2 للصف الجديد إذا كان موجود
        $('.product-select').select2({
            dropdownParent: $('#addDistributionAllocationsModal')
        });
    });

    // حذف سطر
    $(document).on('click', '.remove-row', function() {
        if ($('.item-row').length > 1) {
            $(this).closest('.item-row').remove();
        } else {
            toastr.warning('لا يمكن حذف الصنف الوحيد المتبقي');
        }
    });

    // تحسين select2 للـ product-select
    $('.product-select').select2({
        dropdownParent: $('#addDistributionAllocationsModal'),
        placeholder: "اختر المنتج",
        allowClear: true
    });

$('#addDistributionForm').on('submit', function(e) {
    e.preventDefault();
    
    let form = $(this);
    let submitBtn = form.find('button[type="submit"]');
    let originalText = submitBtn.html(); // حفظ النص الأصلي
    
    // التحقق من وجود كميات
    let hasQuantity = false;
    $('.quantity-input').each(function() {
        let val = parseInt($(this).val());
        if (val > 0) hasQuantity = true;
    });
    
    if (!hasQuantity) {
        toastr.error('يرجى إدخال كميات للأصناف');
        return; // ✅ مهم: عدم تعطيل الزر إذا كان التحقق فاشل
    }
    
    // ✅ تعطيل الزر وتغيير نصه
    submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> جاري الحفظ...');
    
    let formData = new FormData(this);
    
    $.ajax({
        url: "{{ route('admin.distribution_orders.store') }}",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                $('#addDistributionAllocationsModal').modal('hide');
                form[0].reset();
                
                // إعادة تعيين الجدول
                $('#itemsContainer').html(`
                    <tr class="item-row">
                        <td>
                            <select name="items[0][product_id]" class="form-control product-select" required>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }} ({{ number_format($product->price) }} ج.م)</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="number" name="items[0][quantity]" class="form-control text-center quantity-input" placeholder="0" min="1" step="1" required>
                        </td>
                        <td>
                            <input type="text" class="form-control text-center" value="كرتونة" readonly disabled>
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm remove-row" disabled>
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `);
                
                if (typeof table !== 'undefined') {
                    table.ajax.reload();
                }
                
                toastr.success('تم تسجيل أمر الصرف بنجاح');
            } else {
                toastr.error(response.message);
            }
        },
        error: function(xhr) {
            let errorMsg = 'حدث خطأ ما!';
            
            if (xhr.status === 422 && xhr.responseJSON?.errors) {
                let errors = xhr.responseJSON.errors;
                errorMsg = Object.values(errors).flat().join('\n');
            } else if (xhr.responseJSON?.message) {
                errorMsg = xhr.responseJSON.message;
            } else if (xhr.status === 0) {
                errorMsg = 'لا يوجد اتصال بالخادم';
            } else if (xhr.status === 500) {
                errorMsg = 'خطأ في الخادم الداخلي';
            }
            
            toastr.error(errorMsg);
        },
        complete: function() {
            // ✅✅✅ هذا هو الحل: إعادة الزر إلى حالته الأصلية ✅✅✅
            submitBtn.prop('disabled', false).html(originalText);
        }
    });
});
});
</script>