<!-- Modal التعديل -->
<div class="modal fade" id="editReceivingOrderModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-edit ml-2"></i> تعديل إذن استلام شحنة
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="editReceivingOrderForm">
                    @csrf
                    @method('PUT')
                    <!-- الحقل المخفي للمعرف -->
                    <input type="hidden" id="edit_id" name="id">

                    <div class="row">
                        <!-- رقم المستند -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>رقم المستند / الفاتورة <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_document_number" name="document_number" required>
                            </div>
                        </div>
                        <!-- رقم التشغيلة -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>رقم التشغيلة (Batch Number)</label>
                                <input type="text" class="form-control" id="edit_batch_number" name="batch_number">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- المورد -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>المورد <span class="text-danger">*</span></label>
                                <select class="form-control" id="edit_supplier_id" name="supplier_id" required>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <!-- المخزن -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>المخزن المستلم <span class="text-danger">*</span></label>
                                <select class="form-control" id="edit_warehouse_id" name="warehouse_id" required>
                                    @foreach($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- المنتج -->
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>المنتج <span class="text-danger">*</span></label>
                                <select class="form-control" id="edit_product_id" name="product_id" required>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <!-- الكمية -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>الكمية المستلمة <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="edit_quantity" name="quantity" min="1" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- كمية العينات -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>كمية العينات (إن وجدت)</label>
                                <input type="number" class="form-control" id="edit_samples_quantity" name="samples_quantity" min="0">
                            </div>
                        </div>
                        <!-- وقت الوصول -->
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>وقت وتاريخ الوصول</label>
                                <input type="datetime-local" class="form-control" id="edit_arrival_time" name="arrival_time">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>ملاحظات إضافية</label>
                                <textarea class="form-control" id="edit_notes" name="notes" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-primary px-4" id="btnUpdateReceivingOrder">
                    <i class="fas fa-save ml-1"></i> حفظ التعديلات
                </button>
            </div>
        </div>
    </div>
</div>